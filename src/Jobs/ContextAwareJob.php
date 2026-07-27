<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SchoolPalm\QueuedJobs\Jobs\Concerns\HasQueueContext;

/**
 * Base job class that supports application context propagation.
 *
 * Extend this class when creating jobs that need to preserve the
 * application execution context (tenant, school, user, module, etc.)
 * when dispatched to the queue.
 *
 * The job will automatically capture the current context at dispatch
 * time and restore it when the worker processes the job.
 */
abstract class ContextAwareJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasQueueContext;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Capture the current context when the job is constructed
        // (typically at dispatch time).
        $manager = app(\SchoolPalm\QueuedJobs\Context\QueueContextManager::class);
        $contextId = $manager->capture();

        if ($contextId !== null) {
            $this->setQueueContextId($contextId);
        }
    }
}

