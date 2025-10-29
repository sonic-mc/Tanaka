<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
        'web' => [
            // ... existing web middleware
            \App\Http\Middleware\AuditRequest::class, // add near the end to capture status
        ],

        'api' => [
            // ... existing api middleware
            \App\Http\Middleware\AuditRequest::class,
        ],
    ];

    protected $routeMiddleware = [
            // other aliases...
            'role' => \App\Http\Middleware\RoleMiddleware::class,
    ],

    
}