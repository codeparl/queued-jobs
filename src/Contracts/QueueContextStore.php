<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Contracts;

use SchoolPalm\QueuedJobs\Context\QueueContext;

/**
 * Contract for storing and retrieving queue context data.
 *
 * Implementations manage the persistence of QueueContext instances
 * so they can be serialised into job payloads and restored when
 * the job is processed by a worker.
 */
interface QueueContextStore
{
    /**
     * Store a queue context and return its unique identifier.
     *
     * @param QueueContext $context The context to store.
     *
     * @return string A unique identifier that can be used to retrieve the context later.
     */
    public function store(QueueContext $context): string;

    /**
     * Retrieve a queue context by its identifier.
     *
     * @param string $id The unique identifier returned by store().
     *
     * @return QueueContext|null The stored context, or null if not found.
     */
    public function retrieve(string $id): ?QueueContext;

    /**
     * Remove a stored queue context by its identifier.
     *
     * This is typically called after the context has been restored
     * during job processing to clean up.
     *
     * @param string $id The unique identifier of the context to forget.
     */
    public function forget(string $id): void;

    /**
     * Check if a context identifier exists in the store.
     *
     * @param string $id The unique identifier to check.
     *
     * @return bool True if the context exists, false otherwise.
     */
    public function exists(string $id): bool;
}

