<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Exceptions;

use Throwable;

/**
 * Exception thrown when an error occurs within the queue context system.
 *
 * This exception is used for all context-related errors, including
 * storage failures, serialisation issues, and invalid context data.
 */
final class QueueContextException extends \RuntimeException
{
    /**
     * Create a new exception for when a context cannot be stored.
     *
     * @param string         $message  A human-readable message.
     * @param Throwable|null $previous The previous throwable for chain.
     *
     * @return self
     */
    public static function storageFailure(string $message = 'Failed to store queue context.', ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            code: 0,
            previous: $previous,
        );
    }

    /**
     * Create a new exception for when a context cannot be retrieved.
     *
     * @param string         $message  A human-readable message.
     * @param Throwable|null $previous The previous throwable for chain.
     *
     * @return self
     */
    public static function retrievalFailure(string $message = 'Failed to retrieve queue context.', ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            code: 1,
            previous: $previous,
        );
    }

    /**
     * Create a new exception for invalid context data.
     *
     * @param string         $message  A human-readable message.
     * @param Throwable|null $previous The previous throwable for chain.
     *
     * @return self
     */
    public static function invalidContext(string $message = 'Invalid queue context data.', ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            code: 2,
            previous: $previous,
        );
    }

    /**
     * Create a new exception for when no context resolver is configured.
     *
     * @param string         $message  A human-readable message.
     * @param Throwable|null $previous The previous throwable for chain.
     *
     * @return self
     */
    public static function missingResolver(string $message = 'No queue context resolver has been configured.', ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            code: 3,
            previous: $previous,
        );
    }
}

