<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Managers\JobResultManager;
use SchoolPalm\QueuedJobs\Middleware\RestoreJobContext;

/**
 * Base class for all context-aware queued jobs.
 *
 * This class automatically integrates queued jobs with the
 * SchoolPalm Queued Jobs package by providing:
 *
 * - Queue context storage and retrieval.
 * - Automatic context restoration middleware.
 * - Job result tracking.
 * - User-friendly job metadata for dashboards.
 *
 * Package users should extend this class instead of implementing
 * Laravel's ShouldQueue interface directly.
 */
abstract class ContextAwareJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Serialized execution context.
     *
     * The context is attached when the job is dispatched and restored
     * automatically before the job is executed.
     *
     * @var array<string, mixed>
     */
    protected array $queueContext = [];

    /**
     * Identifier of the persisted job result record.
     *
     * This is assigned automatically when the job is dispatched.
     */
    protected ?string $queueJobResultId = null;

    /**
     * Attach queue execution context.
     *
     * Called internally by the package before the job is dispatched.
     *
     * @param array<string,mixed>|QueueContext $context
     *
     * @return static
     */
    public function setQueueContext(
        array|QueueContext $context
    ): static {

        $this->queueContext = $context instanceof QueueContext
            ? $context->toArray()
            : $context;

        return $this;
    }

    /**
     * Get the user-friendly title displayed in dashboards.
     *
     * By default the title is generated from the job class name.
     *
     * Examples:
     *
     * - GenerateTimetableJob → "Generate Timetable"
     * - SendEmailJob → "Send Email"
     * - ImportStudentsJob → "Import Students"
     *
     * Override this method if a more descriptive title is required.
     */
    public function title(): string
    {
        return Str::of(class_basename(static::class))
            ->replaceLast('Job', '')
            ->headline()
            ->toString();
    }

    /**
     * Get an optional description displayed alongside the job.
     *
     * Override this method to provide additional information
     * about what the job does or what it is currently processing.
     *
     * Returns null when no description is available.
     */
    public function description(): ?string
    {
        return null;
    }

 

    /**
     * Get the serialized queue context.
     *
     * @return array<string,mixed>
     */
    public function getQueueContext(): array
    {
        return $this->queueContext;
    }

    /**
     * Get the middleware executed before the job runs.
     *
     * By default this restores the captured execution context
     * such as tenant, school and authenticated user.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $middleware = config(
            'queued-jobs.middleware',
            [
                RestoreJobContext::class,
            ]
        );

        return array_map(
            fn (string $class) => app($class),
            $middleware
        );
    }

    /**
     * Attach the job result identifier.
     *
     * This is called internally after the result record
     * has been created.
     *
     * @return static
     */
    public function setJobResultId(
        string $id
    ): static {

        $this->queueJobResultId = $id;

        return $this;
    }

    /**
     * Get the associated job result identifier.
     */
    public function getJobResultId(): ?string
    {
        return $this->queueJobResultId;
    }

    /**
     * Mark the job as successfully completed.
     *
     * The supplied result payload is persisted and can later
     * be retrieved through the package API for display in
     * dashboards or other user interfaces.
     *
     * @param array<string,mixed> $result
     */
    public function completeResult(
        array $result
    ): void {

        if (! $this->queueJobResultId) {
            return;
        }

        app(JobResultManager::class)
            ->complete(
                $this->queueJobResultId,
                $result
            );
    }

    /**
     * Mark the job as failed.
     *
     * Stores the failure message in the associated job result
     * record for later inspection by users or administrators.
     */
    public function failResult(
        string $message
    ): void {

        if (! $this->queueJobResultId) {
            return;
        }

        app(JobResultManager::class)
            ->fail(
                $this->queueJobResultId,
                $message
            );
    }
}