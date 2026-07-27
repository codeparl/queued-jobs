<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Context;

use SchoolPalm\QueuedJobs\Contracts\QueueContextResolver;
use SchoolPalm\QueuedJobs\Contracts\QueueContextStore;
use SchoolPalm\QueuedJobs\Exceptions\QueueContextException;

/**
 * Central manager for queue context resolution and storage.
 *
 * This class orchestrates the resolution of the current application
 * context and its storage for later retrieval when queued jobs are
 * processed. It acts as the primary API for interacting with the
 * queue context system.
 */
final class QueueContextManager
{
    /**
     * @param QueueContextResolver $contextResolver The resolver for extracting current context.
     * @param QueueContextStore    $contextStore    The store for persisting context.
     */
    public function __construct(
        private readonly QueueContextResolver $contextResolver,
        private readonly QueueContextStore $contextStore,
    ) {}

    /**
     * Resolve the current context from the application.
     *
     * @return QueueContext|null The resolved context, or null if unavailable.
     */
    public function resolveCurrent(): ?QueueContext
    {
        return $this->contextResolver->resolve();
    }

    /**
     * Resolve and store the current context, returning its identifier.
     *
     * @return string|null The context identifier, or null if no context could be resolved.
     *
     * @throws QueueContextException If the context could not be stored.
     */
    public function capture(): ?string
    {
        $context = $this->resolveCurrent();

        if ($context === null || $context->isEmpty()) {
            return null;
        }

        try {
            return $this->contextStore->store($context);
        } catch (\Throwable $e) {
            throw QueueContextException::storageFailure('Failed to capture queue context.', $e);
        }
    }

    /**
     * Retrieve a context by its identifier.
     *
     * @param string $id The context identifier.
     *
     * @return QueueContext|null The restored context, or null if not found.
     */
    public function retrieve(string $id): ?QueueContext
    {
        return $this->contextStore->retrieve($id);
    }

    /**
     * Release (forget) a stored context by its identifier.
     *
     * @param string $id The context identifier to release.
     */
    public function release(string $id): void
    {
        $this->contextStore->forget($id);
    }

    /**
     * Get the underlying context resolver instance.
     *
     * @return QueueContextResolver
     */
    public function getResolver(): QueueContextResolver
    {
        return $this->contextResolver;
    }

    /**
     * Get the underlying context store instance.
     *
     * @return QueueContextStore
     */
    public function getStore(): QueueContextStore
    {
        return $this->contextStore;
    }
}

