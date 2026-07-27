<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the QueuedJobs package.
 *
 * Provides a convenient static interface to the underlying
 * QueueContextManager instance.
 *
 * @method static \SchoolPalm\QueuedJobs\Context\QueueContext|null resolveCurrent()
 * @method static string|null capture()
 * @method static \SchoolPalm\QueuedJobs\Context\QueueContext|null retrieve(string $id)
 * @method static void release(string $id)
 * @method static \SchoolPalm\QueuedJobs\Contracts\QueueContextResolver getResolver()
 * @method static \SchoolPalm\QueuedJobs\Contracts\QueueContextStore getStore()
 *
 * @see \SchoolPalm\QueuedJobs\Context\QueueContextManager
 */
final class QueuedJobs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'queued-jobs.manager';
    }
}

