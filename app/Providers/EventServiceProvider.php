<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\AuditAuthSubscriber;
use App\Listeners\AuditQueueSubscriber;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        AuditAuthSubscriber::class,
        AuditQueueSubscriber::class,
    ];
}
