<?php

namespace App\Providers;

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
        Gate::define('lender', function ($user) {
            return $user->isLender() || $user->isAdmin();
        });

        Gate::define('borrower', function ($user) {
            return $user->isBorrower() || $user->isAdmin();
        });
    }
}
