<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SchoolPalm\QueuedJobs\Providers\QueuedJobsServiceProvider;

/**
 * Base test case for the QueuedJobs package.
 *
 * Extends Orchestra Testbench to provide a Laravel application
 * environment for testing.
 */
abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    /**
     * Get the service providers for the test environment.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            QueuedJobsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('queued-jobs.default_store', 'array');
    }
}

