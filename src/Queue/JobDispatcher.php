<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Queue;

use Illuminate\Contracts\Bus\Dispatcher;
use SchoolPalm\QueuedJobs\Contracts\QueueContextResolver;
use SchoolPalm\QueuedJobs\Context\QueueContextManager;

/**
 * Extended job dispatcher that automatically captures and attaches
 * the current application context when dispatching jobs.
 *
 * This class wraps Laravel's bus dispatcher and injects the current
 * queue context into any job that supports it before dispatching.
 */
final class JobDispatcher
{
    /**
     * @param Dispatcher           $busDispatcher   Laravel's bus dispatcher.
     * @param QueueContextManager  $contextManager  The queue context manager.
     * @param QueueContextResolver $contextResolver The context resolver.
     */
    public function __construct(
        private readonly Dispatcher $busDispatcher,
        private readonly QueueContextManager $contextManager,
        private readonly QueueContextResolver $contextResolver,
    ) {}

    /**
     * Dispatch a job with the current application context.
     *
     * @param object $command The job instance to dispatch.
     *
     * @return mixed The result of the dispatch.
     */
    public function dispatch(object $command): mixed
    {
        $context = $this->contextResolver->resolve();

        if ($context !== null && ! $context->isEmpty()) {
            $contextId = $this->contextManager->capture();

            if ($contextId !== null && $this->commandSupportsContext($command)) {
                /** @phpstan-ignore-next-line */
                $command->setQueueContextId($contextId);
            }
        }

        return $this->busDispatcher->dispatch($command);
    }

    /**
     * Dispatch a job to be processed after the response is sent.
     *
     * @param object $command
     *
     * @return mixed
     */
    public function dispatchAfterResponse(object $command): mixed
    {
        return $this->busDispatcher->dispatchAfterResponse($command);
    }

    /**
     * Dispatch a job to a specific queue.
     *
     * @param object $command
     * @param string $queue
     *
     * @return mixed
     */
    public function dispatchToQueue(object $command, string $queue): mixed
    {
        return $command->onQueue($queue);
    }

    /**
     * Determine if the given command supports context injection.
     *
     * @param object $command
     *
     * @return bool
     */
    private function commandSupportsContext(object $command): bool
    {
        $traits = class_uses_recursive($command);

        return in_array(
            \SchoolPalm\QueuedJobs\Jobs\Concerns\HasQueueContext::class,
            $traits,
            true,
        );
    }
}

