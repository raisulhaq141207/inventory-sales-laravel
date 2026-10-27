<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Dipakai middleware "can:..." pada route dan @can(...) pada Blade.
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('customer', fn (User $user) => $user->isCustomer());
        Gate::define('staff-area', fn (User $user) => ! $user->isCustomer()); // admin & staff
    }
}
