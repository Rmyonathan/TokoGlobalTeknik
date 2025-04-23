<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        //
        Gate::define('view-admin-dashboard', function ($user) {
            return $user->role === 'admin'; // Customize based on your role field
        });

        Gate::define('view-user-dashboard', function ($user) {
            return $user->role === 'user';
        });
    }
}
