<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Queue;

use SchoolPalm\QueuedJobs\Context\QueueContext;

/**
 * Value object that represents the context payload attached to a
 * queued job's serialised data.
 *
 * This class is used internally to embed context identifiers into
 * the queue payload so the worker can restore it when processing
 * the job.
 */
final class PayloadContext
{
    /**
     * @param string $contextId The unique identifier referencing the stored context.
     * @param int    $timestamp The Unix timestamp when the payload was created.
     */
    public function __construct(
        private readonly string $contextId,
        private readonly int $timestamp,
    ) {}

    /**
     * Create a new PayloadContext from the current time.
     *
     * @param string $contextId
     *
     * @return self
     */
    public static function forContextId(string $contextId): self
    {
        return new self(
            contextId: $contextId,
            timestamp: time(),
        );
    }

    /**
     * Create a PayloadContext from an array of data.
     *
     * @param array<string, mixed> $data
     *
     * @return self|null
     */
    public static function fromArray(array $data): ?self
    {
        if (! isset($data['context_id'])) {
            return null;
        }

        return new self(
            contextId: $data['context_id'],
            timestamp: (int) ($data['timestamp'] ?? time()),
        );
    }

    /**
     * Convert the payload context to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'context_id' => $this->contextId,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Get the context identifier.
     *
     * @return string
     */
    public function getContextId(): string
    {
        return $this->contextId;
    }

    /**
     * Get the creation timestamp.
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}

