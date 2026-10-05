<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view the companies catalog.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the specific company.
     */
    public function view(User $user, Company $company): bool
    {
        if ($user->isAdmin()) {
            return $user->isActive();
        }

        return $user->isActive() && $company->is_active;
    }

    /**
     * Determine whether the user can create companies.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    /**
     * Permanent deletion is forbidden to safeguard historical financial records.
     */
    public function delete(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Determine whether the user can toggle company active status.
     */
    public function toggleStatus(User $user, Company $company): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}
