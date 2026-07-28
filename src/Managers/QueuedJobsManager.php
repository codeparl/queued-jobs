<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Managers;

use Closure;
use SchoolPalm\QueuedJobs\Builders\JobBuilder;
use SchoolPalm\QueuedJobs\Builders\JobResultBuilder;
use SchoolPalm\QueuedJobs\Context\QueueContext;

final class QueuedJobsManager
{
    /**
     * Current global queue context.
     */
    protected QueueContext $context;


    /**
     * Context resolver.
     *
     * Used to capture application state automatically.
     */
    private ?Closure $contextResolver = null;


    /**
     * Context restorer.
     *
     * Used when executing queued jobs.
     */
    private ?Closure $contextRestorer = null;



    public function __construct()
    {
        $this->context = new QueueContext();
    }



    /**
     * Start building a queued job.
     */
    public function job(
        object $job
    ): JobBuilder {

        return new JobBuilder(
            $job,
            $this
        );
    }



    /**
     * Set global queue context.
     *
     * Example:
     *
     * QueuedJobs::context([
     *     'tenant_id' => 10,
     *     'school_id' => 5,
     * ]);
     *
     */
    public function context(
        array|QueueContext $context
    ): static {


        $this->context =
            $context instanceof QueueContext
            ? $context
            : QueueContext::fromArray($context);


        return $this;
    }



    /**
     * Get current queue context.
     */
    public function getContext(): QueueContext
    {
        return $this->context;
    }



    /**
     * Capture application context automatically.
     *
     * This is called by JobBuilder.
     */
    public function captureContext(): QueueContext
    {
        if ($this->contextResolver) {

            $resolved = call_user_func(
                $this->contextResolver
            );


            return $this->context->merge(
                QueueContext::fromArray($resolved)
            );
        }


        return $this->context;
    }



    /**
     * Restore context when a job executes.
     */
    public function restoreContext(
        QueueContext $context
    ): void {

        if ($this->contextRestorer) {

            call_user_func(
                $this->contextRestorer,
                $context
            );
        }
    }



    /**
     * Register context resolver.
     *
     * Example:
     *
     * - Resolve tenant from tenancy package
     * - Resolve current school
     * - Resolve authenticated user
     */
    public function resolveContextUsing(
        Closure $callback
    ): static {

        $this->contextResolver = $callback;

        return $this;
    }


    /**
     * Start querying queued job results.
     */
    public function jobs(): JobResultBuilder
    {
        return new JobResultBuilder();
    }

    /**
     * Register context restoration callback.
     *
     * Example:
     *
     * - Switch tenant database
     * - Set current school
     */
    public function restoreContextUsing(
        Closure $callback
    ): static {

        $this->contextRestorer = $callback;

        return $this;
    }
}
