<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
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
        Gate::define('master.manage', fn($user) => $user->hasPermission('master.manage'));
        Gate::define('purchasing.manage', fn($user) => $user->hasPermission('purchasing.manage'));
        Gate::define('receiving.manage', fn($user) => $user->hasPermission('receiving.manage'));
        Gate::define('stock.manage', fn($user) => $user->hasPermission('stock.manage'));
        Gate::define('stock.approve', fn($user) => $user->hasPermission('stock.approve'));
        Gate::define('pos.access', fn($user) => $user->hasPermission('pos.access'));
        Gate::define('sales.view', fn($user) => $user->hasPermission('sales.view'));
        Gate::define('promotions.manage', fn($user) => $user->hasPermission('promotions.manage'));
        Gate::define('members.view', fn($user) => $user->hasPermission('members.view'));
        Gate::define('members.create', fn($user) => $user->hasPermission('members.create'));
        Gate::define('expenses.manage', fn($user) => $user->hasPermission('expenses.manage'));
        Gate::define('expenses.approve', fn($user) => $user->hasPermission('expenses.approve'));
        Gate::define('reports.view', fn($user) => $user->hasPermission('reports.view'));
        Gate::define('reports.export', fn($user) => $user->hasPermission('reports.export'));
        Gate::define('settings.manage', fn($user) => $user->hasPermission('settings.manage'));
        Gate::define('suppliers.manage', fn($user) => $user->hasPermission('suppliers.manage'));
    }
}
