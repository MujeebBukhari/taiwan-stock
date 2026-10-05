<?php

namespace App\Services\Transactions;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Class BuyStock
 *
 * Core Financial Action / Service for executing BUY transactions using Weighted Average Cost (WAC).
 *
 * Mathematically:
 *  Purchase Amount           = Buy Price × Purchase Quantity
 *  Previous Inventory Amount = Previous Quantity × Previous WAC
 *  New Quantity              = Previous Quantity + Purchase Quantity
 *  New Inventory Amount      = Previous Inventory Amount + Purchase Amount
 *  New WAC                   = New Inventory Amount / New Quantity
 *  Realized Profit           = Unchanged
 */
class BuyStock
{
    /**
     * Authoritative static and instance execution method.
     *
     * @throws DomainException|InvalidArgumentException
     */
    public function execute(
        User $user,
        Company $company,
        string|int|float $quantity,
        string|int|float $price,
        string $transactionDate,
        ?string $notes = null
    ): StockTransaction {
        // 1. Validate that the company is active for trading (admins retain override if needed)
        if (! $company->is_active && ! $user->isAdmin()) {
            throw new DomainException("Cannot execute BUY order for an inactive company [{$company->symbol}].");
        }

        // Normalize numeric inputs to clean strings for BCMath
        $quantityStr = FinancialMath::normalize($quantity);
        $priceStr = FinancialMath::normalize($price);

        // Strict positive value validation
        if (! FinancialMath::greaterThan($quantityStr, '0')) {
            throw new InvalidArgumentException('Purchase quantity must be strictly greater than zero.');
        }

        if (! FinancialMath::greaterThan($priceStr, '0')) {
            throw new InvalidArgumentException('Purchase price must be strictly greater than zero.');
        }

        // 2. Execute within an atomic database transaction
        return DB::transaction(function () use ($user, $company, $quantityStr, $priceStr, $transactionDate, $notes) {
            // 3. Acquire pessimistic row lock on the user's position for this company
            $position = StockPosition::query()
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->lockForUpdate()
                ->first();

            // 4. Calculate purchase amount with 8-decimal intermediate precision: Price × Quantity
            $purchaseAmount = FinancialMath::multiply($priceStr, $quantityStr, FinancialMath::DEFAULT_SCALE);

            // 5. Create immutable StockTransaction record
            // For BUY: amount = purchase amount, cost = purchase amount, realized_profit = 0
            $transaction = StockTransaction::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'type' => TransactionType::BUY,
                'quantity' => $quantityStr,
                'price' => $priceStr,
                'amount' => $purchaseAmount,
                'cost' => $purchaseAmount,
                'realized_profit' => '0.00000000',
                'transaction_date' => $transactionDate,
                'notes' => $notes,
            ]);

            // 6 & 7. Create or update StockPosition using WAC formulas
            if (! $position) {
                // First BUY: No existing position
                StockPosition::create([
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'quantity' => FinancialMath::add($quantityStr, '0', FinancialMath::DEFAULT_SCALE),
                    'average_price' => FinancialMath::add($priceStr, '0', FinancialMath::DEFAULT_SCALE),
                    'inventory_amount' => $purchaseAmount,
                    'realized_profit' => '0.00000000',
                    'last_transaction_at' => $transactionDate,
                ]);
            } else {
                // Subsequent BUY: Existing position
                $prevQuantity = (string) $position->quantity;
                $prevInventoryAmount = (string) $position->inventory_amount;

                if (FinancialMath::lessThanOrEqual($prevQuantity, '0')) {
                    // Position had been liquidated/closed previously
                    $newQuantity = FinancialMath::add($quantityStr, '0', FinancialMath::DEFAULT_SCALE);
                    $newInventoryAmount = $purchaseAmount;
                    $newAveragePrice = FinancialMath::add($priceStr, '0', FinancialMath::DEFAULT_SCALE);
                } else {
                    // Standard WAC calculation
                    $newQuantity = FinancialMath::add($prevQuantity, $quantityStr, FinancialMath::DEFAULT_SCALE);
                    $newInventoryAmount = FinancialMath::add($prevInventoryAmount, $purchaseAmount, FinancialMath::DEFAULT_SCALE);
                    $newAveragePrice = FinancialMath::divide($newInventoryAmount, $newQuantity, FinancialMath::DEFAULT_SCALE);
                }

                $position->update([
                    'quantity' => $newQuantity,
                    'average_price' => $newAveragePrice,
                    'inventory_amount' => $newInventoryAmount,
                    'last_transaction_at' => $transactionDate,
                    // 8. Realized profit remains completely unchanged during BUY
                ]);
            }

            return $transaction;
        });
    }
}
