<?php

namespace App\Listeners;

use App\Traits\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;

class AuditAuthSubscriber
{
    use AuditLogger;

    public function handleLogin(Login $event): void
    {
        $this->logInfo('Login', 'User logged in', 'auth', [
            'auth' => [
                'user_id' => optional($event->user)->id,
                'provider' => config('auth.defaults.provider'),
            ],
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        $this->logInfo('Logout', 'User logged out', 'auth', [
            'auth' => [
                'user_id' => optional($event->user)->id,
            ],
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        $this->logWarning('Login failed', 'Authentication failed', 'auth', [
            'auth' => [
                'user_email' => $event->credentials['email'] ?? null,
                'user_id' => optional($event->user)->id,
            ],
        ]);
    }

    public function handleRegistered(Registered $event): void
    {
        $this->logInfo('Registered', 'New user registered', 'auth', [
            'auth' => [
                'user_id' => optional($event->user)->id,
                'email'   => optional($event->user)->email,
            ],
        ]);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->logWarning('Password reset', 'User password reset', 'auth', [
            'auth' => [
                'user_id' => optional($event->user)->id,
            ],
        ]);
    }

    public function subscribe($events): void
    {
        $events->listen(Login::class, [self::class, 'handleLogin']);
        $events->listen(Logout::class, [self::class, 'handleLogout']);
        $events->listen(Failed::class, [self::class, 'handleFailed']);
        $events->listen(Registered::class, [self::class, 'handleRegistered']);
        $events->listen(PasswordReset::class, [self::class, 'handlePasswordReset']);
    }
}
