<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\AuditLogger;

class AuditRequest
{
    use AuditLogger;

    /**
     * Logs mutating HTTP requests with status and duration.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only log mutating requests to reduce noise (tune as needed)
        $shouldLog = in_array($request->method(), ['POST','PUT','PATCH','DELETE'], true);

        $start = microtime(true);
        $response = $next($request);
        $durationMs = (int) ((microtime(true) - $start) * 1000);

        if ($shouldLog) {
            $status = $response->getStatusCode();
            $severity = $status >= 500 ? 'critical' : ($status >= 400 ? 'warning' : 'info');

            $routeName = optional($request->route())->getName();
            $module = $this->guessModuleFromRoute($routeName);

            $this->logAudit(
                action: "HTTP {$request->method()} {$status}",
                description: "Handled {$request->method()} {$request->fullUrl()}",
                module: $module,
                severity: $severity,
                context: [
                    'http' => [
                        'status'   => $status,
                        'duration_ms' => $durationMs,
                        'route_name'  => $routeName,
                    ],
                ]
            );
        }

        return $response;
    }

    protected function guessModuleFromRoute(?string $routeName): ?string
    {
        if (!$routeName) return null;
        // e.g., 'patients.show' -> 'patients'
        return explode('.', $routeName)[0] ?? null;
    }
}
