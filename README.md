# SchoolPalm Queued Jobs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/schoolpalm/queued-jobs.svg?style=flat-square)](https://packagist.org/packages/schoolpalm/queued-jobs)
[![Total Downloads](https://img.shields.io/packagist/dt/schoolpalm/queued-jobs.svg?style=flat-square)](https://packagist.org/packages/schoolpalm/queued-jobs)
[![Tests](https://github.com/schoolpalm/queued-jobs/actions/workflows/tests.yml/badge.svg)](https://github.com/schoolpalm/queued-jobs/actions/workflows/tests.yml)

A Laravel queue infrastructure package that preserves application execution context (tenant, school, user, module) when dispatching and processing queued jobs. Designed for multi-tenant and multi-school Laravel applications.

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

### Run Migrations

```bash
php artisan migrate
```

## Quick Start

### 1. Create a Context-Aware Job

Extend `ContextAwareJob`:

```php
<?php

namespace App\Jobs;

use SchoolPalm\QueuedJobs\Jobs\ContextAwareJob;

class GenerateReport extends ContextAwareJob
{
    public function __construct(
        private readonly array $reportData
    ) {}

    public function handle(): void
    {
        // Context is automatically available
        $context = $this->getQueueContext();

        $schoolId = $context['school_id'];
        $userId   = $context['user_id'];
        $module   = $context['module'];

        // ... your job logic
    }
}
```

### 2. Register a Context Resolver

In your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SchoolPalm\QueuedJobs\Facades\QueuedJobs;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        QueuedJobs::resolveContextUsing(function () {
            return [
                'tenant_id' => tenancy()->tenant?->id,
                'school_id' => session('school_id'),
                'user_id'   => auth()->id(),
                'module'    => request()->route('module'),
                'metadata'  => [
                    'ip' => request()->ip(),
                ],
            ];
        });
    }
}
```

### 3. Register a Context Restorer

The restorer runs before each queued job executes, restoring the application state:

```php
QueuedJobs::restoreContextUsing(function (QueueContext $context) {
    // Switch tenant database
    if ($context->tenantId()) {
        tenancy()->initialize($context->tenantId());
    }

    // Set current school
    if ($context->schoolId()) {
        session(['school_id' => $context->schoolId()]);
    }

    // Authenticate user
    if ($context->userId()) {
        auth()->loginUsingId($context->userId());
    }
});
```

### 4. Dispatch Jobs

#### Using the Facade (with fluent context overrides)

```php
use SchoolPalm\QueuedJobs\Facades\QueuedJobs;

// Basic dispatch — context is captured automatically
QueuedJobs::job(new GenerateReport($data))->dispatch();

// With fluent context overrides
QueuedJobs::job(new GenerateReport($data))
    ->withTenant(1)
    ->withSchool(20)
    ->withUser(50)
    ->withModule('reports')
    ->withMetadata(['source' => 'api'])
    ->onQueue('high')
    ->onConnection('redis')
    ->delay(now()->addMinutes(5))
    ->dispatch();
```

#### Standard Laravel Dispatch

If the job is dispatched via `GenerateReport::dispatch()`, the context will NOT be attached automatically. Always use the `QueuedJobs::job()` facade to ensure context propagation.

## Fluent API Reference

### `QueuedJobs::job(object $job): JobBuilder`

Start building a job dispatch.

| Method                                    | Description                         |
| ----------------------------------------- | ----------------------------------- |
| `->withTenant(string\|int $id)`           | Attach tenant context               |
| `->withSchool(string\|int $id)`           | Attach school context               |
| `->withUser(string\|int $id)`             | Attach user context                 |
| `->withModule(string $module)`            | Attach module context               |
| `->withMetadata(array $data)`             | Attach arbitrary metadata           |
| `->withContext(array\|QueueContext $ctx)` | Attach full context object or array |
| `->onConnection(?string $conn)`           | Set queue connection                |
| `->onQueue(?string $queue)`               | Set queue name                      |
| `->delay($delay)`                         | Set job delay                       |
| `->tries(int $tries)`                     | Override max retry attempts         |
| `->timeout(int $seconds)`                 | Override job timeout                |
| `->backoff(int\|array $backoff)`          | Override retry backoff              |
| `->afterCommit(bool $value = true)`       | Dispatch after DB commit            |
| `->middleware(array $middleware)`         | Add job-specific middleware         |
| `->sync()`                                | Dispatch synchronously (immediate)  |
| `->dispatch()`                            | Execute the dispatch                |
| `->dispatchSync()`                        | Execute synchronously (immediate)   |

### `QueuedJobs::resolveContextUsing(Closure $callback)`

Register a callback that captures the current application context.

### `QueuedJobs::restoreContextUsing(Closure $callback)`

Register a callback that restores application context before job execution.

### `QueuedJobs::context(array|QueueContext $context)`

Set global or default context.

## Job Result Tracking

The package includes optional job result tracking with a database-backed results table.

### Creating a Result

```php
use SchoolPalm\QueuedJobs\Managers\JobResultManager;

$manager = app(JobResultManager::class);

$result = $manager->create($job, [
    'school_id' => 10,
    'user_id' => 5,
]);
```

### Marking Completion / Failure

```php
// Inside your job's handle() method
$this->completeResult(['file' => 'report.pdf', 'pages' => 10]);
$this->failResult('Processing failed: memory limit exceeded');
```

### Querying Results

```php
use SchoolPalm\QueuedJobs\Facades\QueuedJobs;

// Get results builder
$jobs = QueuedJobs::jobs();

// Filtering
$jobs->forSchool(10)
     ->forUser(5)
     ->forModule('reports')
     ->completed()
     ->latest();

// Execute
$results = $jobs->get();
$result  = $jobs->first();
$paginated = $jobs->paginate(20);
$count   = $jobs->count();
$exists  = $jobs->exists();
```

## Context Serialization

Context is serialized with the job into the Laravel queue payload. The `QueueContext` value object is immutable and safely serializes/unserializes via PHP's native serialization. When the queue worker pops the job:

1. Laravel unserializes the job (including the `queueContext` array)
2. The `RestoreJobContext` middleware fires
3. The array context is converted back to a `QueueContext` value object
4. Your registered `restoreContextUsing` callback is invoked
5. Your job's `handle()` method runs with full context restored

## Architecture

```
src/
├── Builders/
│   ├── JobBuilder.php           # Fluent builder for job dispatch
│   └── JobResultBuilder.php     # Query builder for job results
├── Context/
│   └── QueueContext.php         # Immutable context value object
├── Enums/
│   └── JobResultStatus.php      # Status enum (pending/processing/completed/failed)
├── Facades/
│   └── QueuedJobs.php           # Facade for QueuedJobsManager
├── Jobs/
│   └── ContextAwareJob.php      # Base job class with context support
├── Managers/
│   ├── QueuedJobsManager.php    # Core manager (context capture/restore)
│   └── JobResultManager.php     # CRUD for job results
├── Middleware/
│   └── RestoreJobContext.php    # Queue middleware for context restoration
├── Models/
│   └── QueueJobResult.php       # Eloquent model for job results
├── Providers/
│   └── QueuedJobsServiceProvider.php
├── Resources/
│   └── JobResultResource.php    # API resource transformer
└── Support/
    ├── PendingJob.php           # Dispatch wrapper (avoids PendingDispatch)
    └── QueueConfiguration.php   # Config accessor
```

## Configuration

| Option                 | Default                      | Description                           |
| ---------------------- | ---------------------------- | ------------------------------------- |
| `connection`           | `QUEUE_CONNECTION` env       | Default queue connection              |
| `queue`                | `QUEUE_NAME` env             | Default queue name                    |
| `capture_context`      | `true`                       | Auto-capture context on dispatch      |
| `auto_restore_context` | `true`                       | Auto-restore context on job execution |
| `middleware`           | `[RestoreJobContext::class]` | Middleware applied to jobs            |
| `tries`                | `3`                          | Default job retry count               |
| `timeout`              | `120`                        | Default job timeout (seconds)         |

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

