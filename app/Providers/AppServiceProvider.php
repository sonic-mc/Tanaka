<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(Router $router): void
    {
        // Use Bootstrap 5 pagination views
        Paginator::useBootstrapFive();

        // Force the 'role' alias to use your middleware
        $router->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
    }
}
