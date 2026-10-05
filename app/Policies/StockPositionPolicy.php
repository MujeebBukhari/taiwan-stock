<?php

namespace App\Policies;

use App\Models\StockPosition;
use App\Models\User;

class StockPositionPolicy
{
    /**
     * Determine whether the user can view the position.
     * Enforces user isolation: standard users can only view their own positions.
     */
    public function view(User $user, StockPosition $position): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->isAdmin() || $user->id === $position->user_id;
    }

    /**
     * Determine whether the user can view the position within their personal portfolio view.
     * Strictly requires ownership; admin privileges do not bypass this for user portfolio pages.
     */
    public function viewPortfolio(User $user, StockPosition $position): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->id === $position->user_id;
    }

    /**
     * Direct creation via HTTP is disallowed; positions are created/updated via core financial engines.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Direct modification via HTTP is disallowed.
     */
    public function update(User $user, StockPosition $position): bool
    {
        return false;
    }

    /**
     * Deletion of financial positions is disallowed.
     */
    public function delete(User $user, StockPosition $position): bool
    {
        return false;
    }
}
