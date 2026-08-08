<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Facades;

use Illuminate\Support\Facades\Facade;
use SchoolPalm\QueuedJobs\Builders\JobBuilder;
use SchoolPalm\QueuedJobs\Builders\JobResultBuilder;
use SchoolPalm\QueuedJobs\Managers\QueuedJobsManager;

/**
 * QueuedJobs facade.
 *
 * Provides a fluent API for dispatching context-aware queued jobs.
 *
 * @method static JobBuilder job(object $job)
 * Start building a queued job.
 *
 * @method static JobResultBuilder jobs()
 * Start querying queued job results.
 *
 * @method static void resolveContextUsing(\Closure $callback)
 * Register a callback used to capture the current application context.
 *
 * @method static void restoreContextUsing(\Closure $callback)
 * Register a callback used to restore application context when a job executes.
 *
 * @method static array<string,mixed> captureContext()
 * Capture the current execution context.
 *
 * @method static void restoreContext(array<string,mixed> $context)
 * Restore an execution context.
 *
 * @see \SchoolPalm\QueuedJobs\Managers\QueuedJobsManager
 */
final class QueuedJobs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QueuedJobsManager::class;
    }
}
