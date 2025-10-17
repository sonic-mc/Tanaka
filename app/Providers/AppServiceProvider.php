<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Pagination\Paginator;



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
        app(Router::class)->aliasMiddleware('role', RoleMiddleware::class);
                // Use Bootstrap 5 pagination views
                Paginator::useBootstrapFive();
    }
    
}
