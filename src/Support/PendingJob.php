<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Support;

use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Class PendingJob
 *
 * Dispatches queued jobs while preserving the
 * original job instance (including queue context).
 *
 * Avoids PendingDispatch because its __destruct()
 * or __call() magic methods can trigger
 * Dispatchable::dispatch() statically, creating a
 * new job instance that loses the attached context.
 */
final class PendingJob
{
    public function __construct(
        private readonly object $job
    ) {}


    /**
     * Set queue connection.
     *
     * @param string|null $connection
     *
     * @return static
     */
    public function onConnection(
        ?string $connection
    ): static {

        $this->job->onConnection($connection);

        return $this;
    }


    /**
     * Set queue name.
     *
     * @param string|null $queue
     *
     * @return static
     */
    public function onQueue(
        ?string $queue
    ): static {

        $this->job->onQueue($queue);

        return $this;
    }


    /**
     * Delay execution.
     *
     * @param \DateTimeInterface|\DateInterval|int|null $delay
     *
     * @return static
     */
    public function delay(
        \DateTimeInterface|\DateInterval|int|null $delay
    ): static {

        $this->job->delay($delay);

        return $this;
    }


    /**
     * Dispatch the job.
     *
     * Dispatches directly through the Bus Dispatcher
     * to preserve the original job object identity
     * and its attached queue context.
     *
     * @return mixed
     */
    public function dispatch(): mixed
    {
        return app(Dispatcher::class)
            ->dispatch($this->job);
    }
}
