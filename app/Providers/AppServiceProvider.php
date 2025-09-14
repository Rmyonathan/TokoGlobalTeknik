<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Http\ViewComposers\DatabaseComposer;

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
        Paginator::useBootstrap();

        // Register view composer for database switcher
        View::composer('layout.Nav', DatabaseComposer::class);

        Gate::define('view-admin-dashboard', function ($user) {
            return $user->role === 'admin'; // Customize based on your role field
        });

        Gate::define('view-user-dashboard', function ($user) {
            return $user->role === 'user';
        });
    }
}
