<?php

namespace App\Listeners;

use App\Traits\AuditLogger;
use Illuminate\Queue\Events\JobFailed;

class AuditQueueSubscriber
{
    use AuditLogger;

    public function handleJobFailed(JobFailed $event): void
    {
        $this->logCritical('Job failed', 'Queue job failed', 'queue', [
            'job' => [
                'connection' => $event->connectionName,
                'queue'      => method_exists($event->job, 'getQueue') ? $event->job->getQueue() : null,
                'name'       => method_exists($event->job, 'resolveName') ? $event->job->resolveName() : get_class($event->job),
                'exception'  => $event->exception ? $event->exception->getMessage() : null,
            ],
        ]);
    }

    public function subscribe($events): void
    {
        $events->listen(JobFailed::class, [self::class, 'handleJobFailed']);
    }
}
