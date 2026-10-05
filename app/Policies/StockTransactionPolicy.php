<?php

namespace App\Policies;

use App\Models\StockTransaction;
use App\Models\User;

class StockTransactionPolicy
{
    /**
     * Determine whether the user can view the specific transaction.
     * Enforces user data isolation: users can only see their own transactions.
     */
    public function view(User $user, StockTransaction $transaction): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->isAdmin() || $user->id === $transaction->user_id;
    }

    /**
     * Completed financial transactions are immutable; direct HTTP updates are forbidden.
     */
    public function update(User $user, StockTransaction $transaction): bool
    {
        return false;
    }

    /**
     * Financial transactions cannot be deleted; audit trails must remain intact.
     */
    public function delete(User $user, StockTransaction $transaction): bool
    {
        return false;
    }
}
