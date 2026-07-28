<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Middleware;

use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Managers\QueuedJobsManager;

final class RestoreJobContext
{
    public function __construct(
        private readonly QueuedJobsManager $manager
    ) {}


    /**
     * Process the queued job.
     */
    public function handle(
        mixed $job,
        callable $next
    ): void {

        if (
            method_exists($job, 'getQueueContext')
        ) {

            $context = $job->getQueueContext();


            if (is_array($context)) {
                $context = QueueContext::fromArray($context);
            }


            if (
                $context instanceof QueueContext
                &&
                !$context->isEmpty()
            ) {

                $this->manager->restoreContext(
                    $context
                );
            }
        }


        $next($job);
    }
}
