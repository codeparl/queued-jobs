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


        return new PendingJob(
            $this->job
        );
    }



    /**
     * Dispatch immediately.
     */
    public function dispatch(): mixed
    {
        return $this->prepare()
            ->dispatch();
    }
}
