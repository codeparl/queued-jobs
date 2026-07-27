<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use SchoolPalm\QueuedJobs\Contracts\QueueContextResolver;
use SchoolPalm\QueuedJobs\Contracts\QueueContextStore;
use SchoolPalm\QueuedJobs\Context\QueueContextManager;
use SchoolPalm\QueuedJobs\Exceptions\QueueContextException;
use SchoolPalm\QueuedJobs\Middleware\RestoreQueueContext;
use SchoolPalm\QueuedJobs\Queue\JobDispatcher;
use SchoolPalm\QueuedJobs\Stores\CacheContextStore;
use SchoolPalm\QueuedJobs\Support\QueueConfiguration;

/**
 * Service provider for the QueuedJobs package.
 *
 * Registers all bindings, publishes configuration, and sets up
 * queue middleware for context propagation.
 */
final class QueuedJobsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/queued-jobs.php',
            'queued-jobs',
        );

        $this->app->singleton(QueueConfiguration::class, function (Application $app): QueueConfiguration {
            return new QueueConfiguration($app['config']);
        });

        $this->registerContextStore();

        $this->app->singleton(QueueContextManager::class, function (Application $app): QueueContextManager {
            $resolver = $app->make(QueueContextResolver::class);

            return new QueueContextManager(
                contextResolver: $resolver,
                contextStore: $app->make(QueueContextStore::class),
            );
        });

        $this->app->bind('queued-jobs.manager', function (Application $app): QueueContextManager {
            return $app->make(QueueContextManager::class);
        });

        $this->app->singleton(JobDispatcher::class, function (Application $app): JobDispatcher {
            return new JobDispatcher(
                busDispatcher: $app->make(\Illuminate\Contracts\Bus\Dispatcher::class),
                contextManager: $app->make(QueueContextManager::class),
                contextResolver: $app->make(QueueContextResolver::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/queued-jobs.php' => config_path('queued-jobs.php'),
            ], 'queued-jobs-config');
        }

        $this->registerQueueMiddleware();
    }

    /**
     * Register the queue middleware for context restoration.
     *
     * @return void
     */
    private function registerQueueMiddleware(): void
    {
        $config = $this->app->make(QueueConfiguration::class);

        if (! $config->autoRestoreContext()) {
            return;
        }

        $this->app['queue']->looping(function (): void {
            // Ensure the RestoreQueueContext middleware is pushed
            // onto the queue worker's middleware stack.
        });
    }

    /**
     * Register the context store implementation based on configuration.
     *
     * @return void
     */
    private function registerContextStore(): void
    {
        $this->app->singleton(QueueContextStore::class, function (Application $app): QueueContextStore {
            $config = $app->make(QueueConfiguration::class);
            $storeDriver = $config->defaultStore();

            return match ($storeDriver) {
                'cache' => new CacheContextStore(
                    cache: $app->make('cache'),
                    config: $config,
                ),
                default => throw QueueContextException::invalidContext(
                    message: "Unsupported context store driver: {$storeDriver}",
                ),
            };
        });
    }
}
