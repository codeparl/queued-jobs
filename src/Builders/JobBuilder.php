<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Builders;

use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Managers\QueuedJobsManager;
use SchoolPalm\QueuedJobs\Support\PendingJob;

final class JobBuilder
{
    /**
     * Context specific to this job.
     */
    private QueueContext $context;

    /**
     * Queue connection for this job.
     */
    private ?string $connection = null;

    /**
     * Queue name for this job.
     */
    private ?string $queue = null;

    /**
     * Delay before the job runs.
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    private $delay = null;

    /**
     * Maximum retry attempts.
     */
    private ?int $tries = null;

    /**
     * Job timeout in seconds.
     */
    private ?int $timeout = null;

    /**
     * Retry backoff value(s).
     *
     * @var int|array|null
     */
    private $backoff = null;

    /**
     * Whether to dispatch after the database transaction commits.
     */
    private ?bool $afterCommit = null;

    /**
     * Job-specific middleware.
     *
     * @var array<int, object|string>
     */
    private array $middleware = [];

    /**
     * Whether to dispatch synchronously.
     */
    private bool $sync = false;



    public function __construct(
        private readonly object $job,
        private readonly QueuedJobsManager $manager
    ) {

        $this->context = new QueueContext();
    }



    /**
     * Add tenant context.
     */
    public function withTenant(
        string|int $tenantId
    ): self {

        $this->context = $this->context->merge(
            new QueueContext(
                tenantId: $tenantId
            )
        );

        return $this;
    }



    /**
     * Add school context.
     */
    public function withSchool(
        string|int $schoolId
    ): self {

        $this->context = $this->context->merge(
            new QueueContext(
                schoolId: $schoolId
            )
        );

        return $this;
    }



    /**
     * Add user context.
     */
    public function withUser(
        string|int $userId
    ): self {

        $this->context = $this->context->merge(
            new QueueContext(
                userId: $userId
            )
        );

        return $this;
    }



    /**
     * Add module context.
     */
    public function withModule(
        string $module
    ): self {

        $this->context = $this->context->merge(
            new QueueContext(
                module: $module
            )
        );

        return $this;
    }



    /**
     * Add metadata.
     */
    public function withMetadata(
        array $metadata
    ): self {

        $this->context = $this->context->merge(
            new QueueContext(
                metadata: $metadata
            )
        );

        return $this;
    }



    /**
     * Add custom context.
     */
    public function withContext(
        array|QueueContext $context
    ): self {

        $context = $context instanceof QueueContext
            ? $context
            : QueueContext::fromArray($context);


        $this->context = $this->context->merge(
            $context
        );

        return $this;
    }



    /**
     * Set the queue connection for this job.
     */
    public function onConnection(
        ?string $connection
    ): self {

        $this->connection = $connection;

        return $this;
    }



    /**
     * Set the queue name for this job.
     */
    public function onQueue(
        ?string $queue
    ): self {

        $this->queue = $queue;

        return $this;
    }



    /**
     * Delay execution of this job.
     *
     * @param \DateTimeInterface|\DateInterval|int|null $delay
     */
    public function delay(
        \DateTimeInterface|\DateInterval|int|null $delay
    ): self {

        $this->delay = $delay;

        return $this;
    }



    /**
     * Override maximum number of retry attempts.
     */
    public function tries(
        int $tries
    ): self {

        $this->tries = $tries;

        return $this;
    }



    /**
     * Override job timeout (in seconds).
     */
    public function timeout(
        int $seconds
    ): self {

        $this->timeout = $seconds;

        return $this;
    }


 
    /**
     * Override retry backoff.
     *
     * Examples:
     *
     *  60
     *
     *  or
     *
     *  [60, 120, 300]
     *
     * @param int|array $backoff
     */
    public function backoff(
        int|array $backoff
    ): self {

        $this->backoff = $backoff;

        return $this;
    }



    /**
     * Override database commit behavior.
     */
    public function afterCommit(
        bool $value = true
    ): self {

        $this->afterCommit = $value;

        return $this;
    }



    /**
     * Add job-specific middleware.
     */
    public function middleware(
        array $middleware
    ): self {

        $this->middleware = $middleware;

        return $this;
    }



    /**
     * Dispatch synchronously (immediate execution).
     *
     * When used, the job runs immediately on the current process
     * instead of being pushed onto a queue.
     */
    public function sync(): self
    {
        $this->sync = true;

        return $this;
    }



    /**
     * Prepare job with final context.
     *
     * Global context is applied first.
     * Job context overrides global context.
     */
    public function prepare(): PendingJob
    {
        $context = $this->manager
            ->captureContext()
            ->merge(
                $this->context
            );


        if (method_exists(
            $this->job,
            'setQueueContext'
        )) {

            $this->job->setQueueContext(
                $context->toArray()
            );
        }


        $pending = new PendingJob(
            $this->job
        );


        if ($this->connection !== null) {
            $pending->onConnection($this->connection);
        }

        if ($this->queue !== null) {
            $pending->onQueue($this->queue);
        }

        if ($this->delay !== null) {
            $pending->delay($this->delay);
        }

        if ($this->tries !== null) {
            $pending->tries($this->tries);
        }

        if ($this->timeout !== null) {
            $pending->timeout($this->timeout);
        }

        if ($this->backoff !== null) {
            $pending->backoff($this->backoff);
        }

        if ($this->afterCommit !== null) {
            $pending->afterCommit($this->afterCommit);
        }

        if ($this->middleware !== []) {
            $pending->middleware($this->middleware);
        }

        if ($this->sync) {
            $pending->sync();
        }


        return $pending;
    }



    /**
     * Dispatch the job.
     *
     * Dispatches onto the default queue connection.
     */
    public function dispatch(): mixed
    {
        return $this->prepare()
            ->dispatch();
    }



    /**
     * Dispatch the job for synchronous (immediate) execution.
     */
    public function dispatchSync(): mixed
    {
        return $this->sync()
            ->prepare()
            ->dispatch();
    }
}
