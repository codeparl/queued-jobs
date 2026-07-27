<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Contracts;

use SchoolPalm\QueuedJobs\Context\QueueContext;

/**
 * Interface for resolving the current application context.
 *
 * Implementations of this contract are responsible for extracting
 * the current execution context (tenant, school, user, module, etc.)
 * from the application and returning it as a QueueContext instance.
 *
 * The consuming application (e.g., SchoolPalm) must implement this
 * contract and register it in the service container so the package
 * can resolve context without knowing the application's internals.
 */
interface QueueContextResolver
{
    /**
     * Resolve the current queue context from the application.
     *
     * This method should inspect the current request, authenticated user,
     * active tenant/school, and any other relevant context to build
     * a complete QueueContext value object.
     *
     * @return QueueContext|null The resolved context, or null if no context is available.
     */
    public function resolve(): ?QueueContext;
}
