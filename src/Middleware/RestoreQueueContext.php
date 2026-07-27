<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Middleware;

use Illuminate\Contracts\Queue\Job;
use SchoolPalm\QueuedJobs\Jobs\Concerns\HasQueueContext;

/**
 * Queue middleware that restores the application execution context
 * before a job is processed and releases it after completion.
 *
 * This middleware automatically checks if the job uses the
 * HasQueueContext trait and, if so, restores the context from
 * the stored identifier before the job runs. After the job
 * completes (or fails), the context is released.
 */
final class RestoreQueueContext
{
    /**
     * Handle the queued job.
     *
     * @param object $job    The job instance.
     * @param callable $next The next middleware / job handler.
     *
     * @return mixed
     */
    public function handle(object $job, callable $next): mixed
    {
        if ($this->jobUsesContextTrait($job)) {
            /** @var \SchoolPalm\QueuedJobs\Jobs\Concerns\HasQueueContext $job */
            $job->restoreQueueContext();
        }

        try {
            return $next($job);
        } finally {
            if ($this->jobUsesContextTrait($job) && $job->getQueueContextId() !== null) {
                $manager = app(\SchoolPalm\QueuedJobs\Context\QueueContextManager::class);
                $manager->release($job->getQueueContextId());
            }
        }
    }

    /**
     * Determine if the given job uses the HasQueueContext trait.
     *
     * @param object $job
     *
     * @return bool
     */
    private function jobUsesContextTrait(object $job): bool
    {
        $traits = class_uses_recursive($job);

        return in_array(HasQueueContext::class, $traits, true);
    }
}

