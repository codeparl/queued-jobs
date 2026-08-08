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
    /**
     * Whether to dispatch synchronously.
     */
    private bool $sync = false;

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
     * Override maximum number of retry attempts.
     *
     * If not called, the configured default
     * from queued-jobs config is used.
     *
     * @param int $tries
     *
     * @return static
     */
    public function tries(
        int $tries
    ): static {

        $this->job->tries = $tries;

        return $this;
    }


    /**
     * Override job timeout.
     *
     * If not called, the configured default
     * from queued-jobs config is used.
     *
     * @param int $seconds
     *
     * @return static
     */
    public function timeout(
        int $seconds
    ): static {

        $this->job->timeout = $seconds;

        return $this;
    }


    /**
     * Override retry backoff.
     *
     * Examples:
     *
     * 60
     *
     * or
     *
     * [60,120,300]
     *
     * @param int|array $backoff
     *
     * @return static
     */
    public function backoff(
        int|array $backoff
    ): static {

        $this->job->backoff = $backoff;

        return $this;
    }


    /**
     * Override database commit behavior.
     *
     * @param bool $value
     *
     * @return static
     */
    public function afterCommit(
        bool $value = true
    ): static {

        $this->job->afterCommit = $value;

        return $this;
    }


    /**
     * Add job-specific middleware.
     *
     * These are merged with middleware
     * configured in queued-jobs config.
     *
     * @param array $middleware
     *
     * @return static
     */
    public function middleware(
        array $middleware
    ): static {

        if (method_exists($this->job, 'addMiddleware')) {

            $this->job->addMiddleware(
                $middleware
            );
        }

        return $this;
    }


    /**
     * Dispatch the job synchronously (immediately).
     *
     * When sync mode is enabled, the job runs
     * immediately on the current process instead
     * of being pushed onto a queue.
     *
     * @return static
     */
    public function sync(): static
    {
        $this->sync = true;

        return $this;
    }



    /**
     * Dispatch the job.
     *
     * Uses Laravel dispatcher directly so the
     * original job instance and attached context
     * are preserved.
     *
     * When sync mode is enabled, the job is executed
     * immediately on the current process (via the Bus
     * dispatcher), preserving the original job instance.
     *
     * @return mixed
     */
    public function dispatch(): mixed
    {
        if ($this->sync) {

            return app(Dispatcher::class)
                ->dispatchSync($this->job);
        }


        return app(Dispatcher::class)
            ->dispatch($this->job);
    }
}
