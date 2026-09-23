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
        app()->setLocale('ar');

        Gate::define('admin-only', function (User $user) {
            return (bool) $user->PER_USER_IS_ACTIVE && $user->PER_USER_ROLE === 'ADMIN';
        });

        Gate::define('clinic-staff', function (User $user) {
            return (bool) $user->PER_USER_IS_ACTIVE && in_array($user->PER_USER_ROLE, ['ADMIN', 'NURSE'], true);
        });
    }
}
