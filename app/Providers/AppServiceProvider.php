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

        // Gate 'admin': dipakai middleware "can:admin" pada route dan @can('admin') pada Blade.
        Gate::define('admin', fn (User $user) => $user->isAdmin());
    }
}
