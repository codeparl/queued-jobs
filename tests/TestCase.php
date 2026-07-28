<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use SchoolPalm\QueuedJobs\Providers\QueuedJobsServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;


    protected function getPackageProviders($app): array
    {
        return [
            QueuedJobsServiceProvider::class,
        ];
    }


    protected function getEnvironmentSetUp($app): void
    {
        /*
        |--------------------------------------------------------------------------
        | Database
        |--------------------------------------------------------------------------
        */

        $app['config']->set(
            'database.default',
            'testing'
        );


        $app['config']->set(
            'database.connections.testing',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Queue
        |--------------------------------------------------------------------------
        */

        $app['config']->set(
            'queue.default',
            'testing'
        );


        /*
        |--------------------------------------------------------------------------
        | Package Config
        |--------------------------------------------------------------------------
        */

        $app['config']->set(
            'queued-jobs.middleware',
            [
                \SchoolPalm\QueuedJobs\Middleware\RestoreJobContext::class,
            ]
        );


        $app['config']->set(
            'queued-jobs.context',
            [
                'tenant' => true,
                'school' => true,
                'user' => true,
            ]
        );
    }


    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );
    }
}
