<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Support;

use Illuminate\Contracts\Config\Repository;
use SchoolPalm\QueuedJobs\Middleware\RestoreJobContext;

final class QueueConfiguration
{
    public function __construct(
        private readonly Repository $config,
    ) {}


    /**
     * Determine if automatic context restoration is enabled.
     */
    public function autoRestoreContext(): bool
    {
        return (bool) $this->config->get(
            'queued-jobs.auto_restore_context',
            true
        );
    }


    /**
     * Get middleware applied to context aware jobs.
     *
     * @return array<int,string>
     */
    public function middleware(): array
    {
        return $this->config->get(
            'queued-jobs.middleware',
            [
                RestoreJobContext::class,
            ]
        );
    }


    /**
     * Determine if current application context
     * should be automatically captured when dispatching.
     */
    public function captureContextAutomatically(): bool
    {
        return (bool) $this->config->get(
            'queued-jobs.capture_context',
            true
        );
    }


    /**
     * Get default queue name.
     */
    public function defaultQueue(): string
    {
        return $this->config->get(
            'queued-jobs.default_queue',
            'default'
        );
    }
}
