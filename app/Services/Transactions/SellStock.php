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
 * Class SellStock
 *
 * Core Financial Action / Service for executing SELL transactions using Weighted Average Cost (WAC).
 *
 * Financial Invariants:
 *  Selling Amount             = Sell Price × Quantity Sold
 *  Cost of Sold Inventory     = Current WAC × Quantity Sold
 *  Realized Profit            = Selling Amount - Cost of Sold Inventory (supports profit or loss)
 *  Remaining Quantity         = Available Quantity - Quantity Sold
 *  Remaining Inventory Amount = Current WAC × Remaining Quantity (or 0 if quantity is 0)
 *  WAC (average_price)        = UNCHANGED during SELL
 *  Cumulative Realized Profit = Previous Realized Profit + SELL Realized Profit
 */
class SellStock
{
    /**
     * Authoritative execution method for SELL transactions.
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
            throw new DomainException("Cannot execute SELL order for an inactive company [{$company->symbol}].");
        }

        // Normalize numeric inputs to clean strings for BCMath
        $quantityStr = FinancialMath::normalize($quantity);
        $priceStr = FinancialMath::normalize($price);

        // Strict positive value validation
        if (! FinancialMath::greaterThan($quantityStr, '0')) {
            throw new InvalidArgumentException('Sell quantity must be strictly greater than zero.');
        }

        if (! FinancialMath::greaterThan($priceStr, '0')) {
            throw new InvalidArgumentException('Sell price must be strictly greater than zero.');
        }

        // 2. Execute within an atomic database transaction
        return DB::transaction(function () use ($user, $company, $quantityStr, $priceStr, $transactionDate, $notes) {
            // 3. Lock user's StockPosition row using pessimistic locking
            $position = StockPosition::query()
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->lockForUpdate()
                ->first();

            // 4. Verify position exists
            if (! $position) {
                throw new DomainException("No stock position exists for {$company->symbol}. Cannot execute SELL order.");
            }

            $availableQuantity = (string) $position->quantity;
            $currentWac = (string) $position->average_price;
            $prevRealizedProfit = (string) $position->realized_profit;

            // 5. Verify sufficient available quantity
            if (FinancialMath::lessThanOrEqual($availableQuantity, '0')) {
                throw new DomainException("Available quantity for {$company->symbol} is zero. Cannot execute SELL order.");
            }

            if (FinancialMath::greaterThan($quantityStr, $availableQuantity)) {
                throw new DomainException("Requested sell quantity [{$quantityStr}] exceeds available quantity [{$availableQuantity}].");
            }

            // 6. Calculations using FinancialMath (8 decimal places)
            // Selling Amount = Sell Price × Quantity Sold
            $sellingAmount = FinancialMath::multiply($priceStr, $quantityStr, FinancialMath::DEFAULT_SCALE);

            // Cost of Sold Inventory = Current WAC × Quantity Sold
            $costOfSoldInventory = FinancialMath::multiply($currentWac, $quantityStr, FinancialMath::DEFAULT_SCALE);

            // Realized Profit = Selling Amount - Cost of Sold Inventory (positive profit or negative loss)
            $realizedProfit = FinancialMath::subtract($sellingAmount, $costOfSoldInventory, FinancialMath::DEFAULT_SCALE);

            // Remaining Quantity = Available Quantity - Quantity Sold
            $remainingQuantity = FinancialMath::subtract($availableQuantity, $quantityStr, FinancialMath::DEFAULT_SCALE);

            // Remaining Inventory Amount = Current WAC × Remaining Quantity (or 0.00000000 if 0 remaining)
            if (FinancialMath::lessThanOrEqual($remainingQuantity, '0')) {
                $remainingInventoryAmount = '0.00000000';
            } else {
                $remainingInventoryAmount = FinancialMath::multiply($currentWac, $remainingQuantity, FinancialMath::DEFAULT_SCALE);
            }

            // Cumulative Realized Profit = Previous Realized Profit + SELL Realized Profit
            $newCumulativeRealizedProfit = FinancialMath::add($prevRealizedProfit, $realizedProfit, FinancialMath::DEFAULT_SCALE);

            // 7. Create immutable StockTransaction record
            $transaction = StockTransaction::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'type' => TransactionType::SELL,
                'quantity' => $quantityStr,
                'price' => $priceStr,
                'amount' => $sellingAmount,
                'cost' => $costOfSoldInventory,
                'realized_profit' => $realizedProfit,
                'transaction_date' => $transactionDate,
                'notes' => $notes,
            ]);

            // 8. Update StockPosition (WAC average_price remains UNCHANGED; row is NEVER deleted on full sell)
            $position->update([
                'quantity' => $remainingQuantity,
                'average_price' => $currentWac,
                'inventory_amount' => $remainingInventoryAmount,
                'realized_profit' => $newCumulativeRealizedProfit,
                'last_transaction_at' => $transactionDate,
            ]);

            return $transaction;
        });
    }
}
