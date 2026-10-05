<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user): bool {
            return $user->isAdmin() && $user->isActive();
        });

        Gate::define('user', function (User $user): bool {
            return $user->isUser() && $user->isActive();
        });

        Gate::define('manage-stocks', function (User $user): bool {
            return $user->isAdmin() && $user->isActive();
        });

        Gate::define('manage-users', function (User $user): bool {
            return $user->isAdmin() && $user->isActive();
        });

        Gate::define('access-portfolio', function (User $user): bool {
            return $user->isActive();
        });
    }
}
