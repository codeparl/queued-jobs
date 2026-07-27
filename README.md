# SchoolPalm Queued Jobs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/schoolpalm/queued-jobs.svg?style=flat-square)](https://packagist.org/packages/schoolpalm/queued-jobs)
[![Total Downloads](https://img.shields.io/packagist/dt/schoolpalm/queued-jobs.svg?style=flat-square)](https://packagist.org/packages/schoolpalm/queued-jobs)

A Laravel queue infrastructure package that allows module authors to dispatch queued jobs while preserving application execution context.

## Why?

In multi-tenant or multi-school applications, queued jobs often need access to the same context (tenant, school, user, module) that was present when the job was originally dispatched. Without context propagation, each job would need to manually resolve this information, leading to duplicated code and potential inconsistencies.

This package provides a clean, extensible way to automatically capture and restore the application execution context when dispatching and processing queued jobs.

## Installation

```bash
composer require schoolpalm/queued-jobs
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=queued-jobs-config
```

## Usage

### 1. Implement the Context Resolver

The consuming application must implement the `QueueContextResolver` contract to tell the package how to resolve the current context:

```php
use SchoolPalm\QueuedJobs\Contracts\QueueContextResolver;
use SchoolPalm\QueuedJobs\Context\QueueContext;

class AppQueueContextResolver implements QueueContextResolver
{
    public function resolve(): ?QueueContext
    {
        return new QueueContext(
            tenantId: tenant()->id,
            schoolId: school()?->id,
            userId: auth()->id(),
            module: app('current-module'),
            metadata: ['ip' => request()->ip()],
        );
    }
}
```

Register the resolver in your service provider:

```php
$this->app->bind(
    \SchoolPalm\QueuedJobs\Contracts\QueueContextResolver::class,
    \App\Resolvers\AppQueueContextResolver::class,
);
```

### 2. Create Context-Aware Jobs

Extend the `ContextAwareJob` base class or use the `HasQueueContext` trait:

#### Option A: Extend ContextAwareJob

```php
use SchoolPalm\QueuedJobs\Jobs\ContextAwareJob;

class ProcessReport extends ContextAwareJob
{
    public function handle(): void
    {
        $context = $this->getQueueContext();

        // Access tenant, school, user, etc.
        $tenantId = $context->getTenantId();
    }
}
```

#### Option B: Use the Trait

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use SchoolPalm\QueuedJobs\Jobs\Concerns\HasQueueContext;

class SendNotification implements ShouldQueue
{
    use Dispatchable, Queueable, HasQueueContext;

    public function handle(): void
    {
        $context = $this->getQueueContext();
        // ...
    }
}
```

### 3. Dispatch Jobs as Usual

```php
ProcessReport::dispatch($reportData);
```

The context is automatically captured at dispatch time and restored when the worker processes the job.

## Architecture

```
src/
├── Contracts/
│   ├── QueueContextResolver.php    # Contract for resolving current context
│   └── QueueContextStore.php       # Contract for storing/retrieving context
├── Context/
│   ├── QueueContext.php            # Immutable value object
│   └── QueueContextManager.php     # Orchestrator for context operations
├── Jobs/
│   ├── ContextAwareJob.php         # Base job with context support
│   └── Concerns/
│       └── HasQueueContext.php     # Trait for adding context to any job
├── Middleware/
│   └── RestoreQueueContext.php     # Queue middleware for context restoration
├── Queue/
│   ├── JobDispatcher.php           # Extended dispatcher with context injection
│   └── PayloadContext.php          # Payload value object
├── Stores/
│   └── CacheContextStore.php       # Cache-backed context store
├── Facades/
│   └── QueuedJobs.php              # Facade for QueueContextManager
├── Providers/
│   └── QueuedJobsServiceProvider.php
├── Support/
│   └── QueueConfiguration.php      # Configuration helper
└── Exceptions/
    └── QueueContextException.php   # Domain exception
```

## Configuration

The package publishes a `config/queued-jobs.php` file with the following options:

| Option                 | Description                            | Default |
| ---------------------- | -------------------------------------- | ------- |
| `default_store`        | Context store driver (cache, array)    | `cache` |
| `context_resolver`     | Class for resolving current context    | `null`  |
| `auto_restore_context` | Auto-restore context on job processing | `true`  |
| `serialization`        | Serialization driver (json, igbinary)  | `json`  |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email dev@schoolpalm.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

