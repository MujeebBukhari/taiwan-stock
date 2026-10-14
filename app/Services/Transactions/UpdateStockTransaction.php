<?php

namespace App\Services\Transactions;

use App\Models\Company;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateStockTransaction
{
    public function __construct(
        protected RecalculatePosition $recalculatePosition
    ) {}

    /**
     * Update an existing transaction and recalculate the position ledger.
     *
     * @throws DomainException|InvalidArgumentException
     */
    public function execute(
        User $user,
        Company $company,
        StockTransaction $transaction,
        string|int|float $quantity,
        string|int|float $price,
        string $transactionDate,
        ?string $notes = null
    ): StockTransaction {
        if ($transaction->user_id !== $user->id) {
            throw new DomainException('Unauthorized to modify this transaction.');
        }

        if ($transaction->company_id !== $company->id) {
            throw new DomainException('Transaction does not belong to the specified company.');
        }

        $quantityStr = FinancialMath::normalize($quantity);
        $priceStr = FinancialMath::normalize($price);

        if (! FinancialMath::greaterThan($quantityStr, '0')) {
            throw new InvalidArgumentException('Quantity must be strictly greater than zero.');
        }

        if (! FinancialMath::greaterThan($priceStr, '0')) {
            throw new InvalidArgumentException('Price must be strictly greater than zero.');
        }

        return DB::transaction(function () use ($user, $company, $transaction, $quantityStr, $priceStr, $transactionDate, $notes) {
            $transaction->update([
                'quantity' => $quantityStr,
                'price' => $priceStr,
                'transaction_date' => $transactionDate,
                'notes' => $notes,
            ]);

            // Re-run chronological ledger calculation across all transactions
            $this->recalculatePosition->execute($user, $company);

            return $transaction->fresh();
        });
    }

    /**
     * Delete an existing transaction and recalculate the position ledger.
     *
     * @throws DomainException
     */
    public function delete(
        User $user,
        Company $company,
        StockTransaction $transaction
    ): void {
        if ($transaction->user_id !== $user->id) {
            throw new DomainException('Unauthorized to delete this transaction.');
        }

        if ($transaction->company_id !== $company->id) {
            throw new DomainException('Transaction does not belong to the specified company.');
        }

        DB::transaction(function () use ($user, $company, $transaction) {
            $transaction->delete();

            // Re-run chronological ledger calculation across remaining transactions
            $this->recalculatePosition->execute($user, $company);
        });
    }
}
