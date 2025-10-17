<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

trait AuditLogger
{
    /**
     * Log an audit entry with normalized severity and rich request context.
     *
     * @param  string       $action     e.g., 'Created invoice', 'Login', 'Job failed'
     * @param  string|null  $description Free-text summary; context will be appended as JSON
     * @param  string|null  $module     e.g., 'billing', 'auth', 'jobs', 'patients'
     * @param  string       $severity   'info' | 'warning' | 'critical' (maps common aliases)
     * @param  array        $context    Additional context merged into request context
     */
    public function logAudit(
        string $action,
        ?string $description = null,
        ?string $module = null,
        string $severity = 'info',
        array $context = []
    ): void {
        try {
            $sev = $this->normalizeSeverity($severity);
            $ctx = $this->gatherContext($context);

            // Store combined description as JSON (human text + machine context)
            $desc = $this->composeDescription($description, $ctx);

            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => $action,
                'module'     => $module,
                'severity'   => $sev,
                'description'=> $desc,
                'timestamp'  => now(),
            ]);
        } catch (Throwable $e) {
            // Never block the main flow due to auditing failures.
            // Optionally, you could log to error log: logger()->warning('Audit failed', ['error' => $e->getMessage()]);
        }
    }

    public function logInfo(string $action, ?string $description = null, ?string $module = null, array $context = []): void
    {
        $this->logAudit($action, $description, $module, 'info', $context);
    }

    public function logWarning(string $action, ?string $description = null, ?string $module = null, array $context = []): void
    {
        $this->logAudit($action, $description, $module, 'warning', $context);
    }

    public function logCritical(string $action, ?string $description = null, ?string $module = null, array $context = []): void
    {
        $this->logAudit($action, $description, $module, 'critical', $context);
    }

    protected function normalizeSeverity(string $severity): string
    {
        $s = strtolower($severity);
        // Map common aliases to your enum set
        return match ($s) {
            'warn' => 'warning',
            'error', 'fatal', 'crit' => 'critical',
            default => in_array($s, ['info', 'warning', 'critical'], true) ? $s : 'info',
        };
    }

    protected function composeDescription(?string $description, array $context): string
    {
        // Store a structured JSON to preserve context (within your TEXT column)
        return json_encode([
            'message' => $description,
            'context' => $context,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function gatherContext(array $extra = []): array
    {
        /** @var Request|null $req */
        $req = request();
        $base = [
            'user' => [
                'id'   => auth()->id(),
                'name' => optional(auth()->user())->name,
                'email'=> optional(auth()->user())->email,
            ],
            'request' => $req ? [
                'method'      => $req->method(),
                'url'         => $req->fullUrl(),
                'route'       => optional($req->route())->getName(),
                'ip'          => $req->ip(),
                'user_agent'  => $req->userAgent(),
                'referer'     => $req->headers->get('referer'),
                'payload'     => $this->maskedInput($req),
                'headers'     => $this->safeHeaders($req),
            ] : null,
            'app' => [
                'env'         => config('app.env'),
                'name'        => config('app.name'),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        // Merge, with $extra taking precedence
        return array_replace_recursive($base, $extra);
    }

    protected function maskedInput(Request $req): array
    {
        // Exclude large/binary inputs and sensitive keys
        $input = $req->except(['password', 'password_confirmation', 'current_password', '_token', 'file', 'files']);
        $input = Arr::except($input, ['token', 'api_token', 'authorization', 'secret', 'client_secret', 'access_token']);
        // Mask known sensitive patterns
        array_walk_recursive($input, function (&$v, $k) {
            $key = strtolower((string) $k);
            if (str_contains($key, 'password') || str_contains($key, 'secret') || str_contains($key, 'token')) {
                $v = '******';
            }
        });
        return $input;
    }

    protected function safeHeaders(Request $req): array
    {
        $headers = $req->headers->all();
        $headers = Arr::except($headers, ['authorization', 'cookie', 'set-cookie', 'x-csrf-token']);
        return $headers;
    }
}
