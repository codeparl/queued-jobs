<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Providers;

use Illuminate\Support\ServiceProvider;
use SchoolPalm\QueuedJobs\Managers\QueuedJobsManager;

final class QueuedJobsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/queued-jobs.php',
            'queued-jobs'
        );


        $this->app->singleton(
            QueuedJobsManager::class,
            fn() => new QueuedJobsManager()
        );


        $this->app->alias(
            QueuedJobsManager::class,
            'queued-jobs'
        );
    }


    public function boot(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes(
                [
                    __DIR__ . '/../../config/queued-jobs.php'
                    => config_path('queued-jobs.php'),
                ],
                'queued-jobs-config'
            );
        }
    }
}
