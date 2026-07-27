<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Jobs\Concerns;

use SchoolPalm\QueuedJobs\Context\QueueContext;

/**
 * Trait for adding queue context support to job classes.
 *
 * Use this trait in your job classes to allow them to carry and
 * restore the application execution context automatically.
 *
 * @mixin \SchoolPalm\QueuedJobs\Jobs\ContextAwareJob
 */
trait HasQueueContext
{
    /**
     * The stored context identifier for this job.
     *
     * @var string|null
     */
    public ?string $queueContextId = null;

    /**
     * The resolved queue context instance.
     *
     * @var QueueContext|null
     */
    protected ?QueueContext $queueContext = null;

    /**
     * Set the queue context identifier for this job.
     *
     * @param string $contextId
     *
     * @return void
     */
    public function setQueueContextId(string $contextId): void
    {
        $this->queueContextId = $contextId;
    }

    /**
     * Get the queue context identifier for this job.
     *
     * @return string|null
     */
    public function getQueueContextId(): ?string
    {
        return $this->queueContextId;
    }

    /**
     * Set the resolved queue context.
     *
     * @param QueueContext $context
     *
     * @return void
     */
    public function setQueueContext(QueueContext $context): void
    {
        $this->queueContext = $context;
    }

    /**
     * Get the resolved queue context.
     *
     * @return QueueContext|null
     */
    public function getQueueContext(): ?QueueContext
    {
        return $this->queueContext;
    }

    /**
     * Restore the queue context from the stored identifier.
     *
     * This is typically called by the RestoreQueueContext middleware
     * before the job's handle() method is invoked.
     *
     * @return void
     */
    public function restoreQueueContext(): void
    {
        if ($this->queueContextId === null) {
            return;
        }

        $manager = app(\SchoolPalm\QueuedJobs\Context\QueueContextManager::class);
        $context = $manager->retrieve($this->queueContextId);

        if ($context !== null) {
            $this->queueContext = $context;
        }
    }
}

