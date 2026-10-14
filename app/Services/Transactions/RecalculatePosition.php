<?php

namespace App\Services\Transactions;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecalculatePosition
{
    /**
     * Recalculate the entire position and all transaction costs/profits
     * for a given user and company by chronologically replaying all transactions.
     *
     * @throws DomainException if any SELL transaction would overdraft available shares
     */
    public function execute(User $user, Company $company): ?StockPosition
    {
        return DB::transaction(function () use ($user, $company) {
            // Lock position row if exists
            $position = StockPosition::query()
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->lockForUpdate()
                ->first();

            // Fetch all transactions for this user and company ordered chronologically
            $transactions = StockTransaction::query()
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // If no transactions remain, reset position
            if ($transactions->isEmpty()) {
                if ($position) {
                    $position->update([
                        'quantity' => '0.00000000',
                        'average_price' => '0.00000000',
                        'inventory_amount' => '0.00000000',
                        'realized_profit' => '0.00000000',
                        'last_transaction_at' => null,
                    ]);
                }

                return $position;
            }

            $currentQty = '0.00000000';
            $currentWac = '0.00000000';
            $currentInvAmount = '0.00000000';
            $cumulativeRealizedProfit = '0.00000000';
            $lastTxDate = null;
            $lastPrice = null;

            foreach ($transactions as $tx) {
                $txQty = FinancialMath::normalize($tx->quantity);
                $txPrice = FinancialMath::normalize($tx->price);
                $lastTxDate = $tx->transaction_date;
                $lastPrice = $txPrice;

                if ($tx->type->isBuy()) {
                    // Purchase Amount = Price * Quantity
                    $purchaseAmount = FinancialMath::multiply($txPrice, $txQty, FinancialMath::DEFAULT_SCALE);

                    if (FinancialMath::lessThanOrEqual($currentQty, '0')) {
                        // Position was zero or freshly started
                        $currentQty = $txQty;
                        $currentInvAmount = $purchaseAmount;
                        $currentWac = $txPrice;
                    } else {
                        // Standard WAC calculation
                        $currentQty = FinancialMath::add($currentQty, $txQty, FinancialMath::DEFAULT_SCALE);
                        $currentInvAmount = FinancialMath::add($currentInvAmount, $purchaseAmount, FinancialMath::DEFAULT_SCALE);
                        $currentWac = FinancialMath::divide($currentInvAmount, $currentQty, FinancialMath::DEFAULT_SCALE);
                    }

                    // For BUY: amount = purchaseAmount, cost = purchaseAmount, realized_profit = 0
                    $tx->update([
                        'amount' => $purchaseAmount,
                        'cost' => $purchaseAmount,
                        'realized_profit' => '0.00000000',
                    ]);
                } elseif ($tx->type->isSell()) {
                    // Check overdraft
                    if (FinancialMath::lessThanOrEqual($currentQty, '0')) {
                        $dateStr = $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : 'unknown date';
                        throw new DomainException("Transaction on {$dateStr} attempted to sell {$txQty} shares when available quantity was 0.");
                    }

                    if (FinancialMath::greaterThan($txQty, $currentQty)) {
                        $dateStr = $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : 'unknown date';
                        throw new DomainException("Transaction on {$dateStr} attempted to sell {$txQty} shares, but only {$currentQty} were available.");
                    }

                    // Selling Amount = Sell Price * Quantity Sold
                    $sellingAmount = FinancialMath::multiply($txPrice, $txQty, FinancialMath::DEFAULT_SCALE);

                    // Cost of Sold Inventory = Current WAC * Quantity Sold
                    $costOfSold = FinancialMath::multiply($currentWac, $txQty, FinancialMath::DEFAULT_SCALE);

                    // Realized Profit = Selling Amount - Cost of Sold
                    $txRealizedProfit = FinancialMath::subtract($sellingAmount, $costOfSold, FinancialMath::DEFAULT_SCALE);

                    // Update cumulative realized profit
                    $cumulativeRealizedProfit = FinancialMath::add($cumulativeRealizedProfit, $txRealizedProfit, FinancialMath::DEFAULT_SCALE);

                    // Remaining quantity
                    $currentQty = FinancialMath::subtract($currentQty, $txQty, FinancialMath::DEFAULT_SCALE);

                    if (FinancialMath::lessThanOrEqual($currentQty, '0')) {
                        $currentQty = '0.00000000';
                        $currentInvAmount = '0.00000000';
                        $currentWac = '0.00000000';
                    } else {
                        $currentInvAmount = FinancialMath::multiply($currentWac, $currentQty, FinancialMath::DEFAULT_SCALE);
                        // WAC remains unchanged on SELL
                    }

                    $tx->update([
                        'amount' => $sellingAmount,
                        'cost' => $costOfSold,
                        'realized_profit' => $txRealizedProfit,
                    ]);
                }
            }

            // Persist final position
            if (! $position) {
                $position = StockPosition::create([
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'quantity' => $currentQty,
                    'average_price' => $currentWac,
                    'inventory_amount' => $currentInvAmount,
                    'realized_profit' => $cumulativeRealizedProfit,
                    'last_transaction_at' => $lastTxDate,
                ]);
            } else {
                $position->update([
                    'quantity' => $currentQty,
                    'average_price' => $currentWac,
                    'inventory_amount' => $currentInvAmount,
                    'realized_profit' => $cumulativeRealizedProfit,
                    'last_transaction_at' => $lastTxDate,
                ]);
            }

            // Update company last price if we have a last price
            if ($lastPrice !== null) {
                $company->update(['last_price' => $lastPrice]);
            }

            return $position;
        });
    }
}
