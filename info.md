# SchoolPalm Queued Jobs

**schoolpalm/queued-jobs** is a Laravel package that provides context-aware queue processing for SchoolPalm applications.

It allows module developers to create background jobs that automatically preserve and restore SchoolPalm application context such as:

- Tenant database context
- School context
- User context
- Module context
- Custom metadata

## How It Works

```
Module Developer
      |
      v
QueuedJobs::job(new Job())->dispatch()
      |
      v
JobBuilder::prepare()
  ├── Captures global context (tenant, school, user)
  ├── Merges fluent overrides (->withSchool(), ->withUser())
  └── Calls $job->setQueueContext(...)
      |
      v
PendingJob::dispatch()
  └── Dispatches directly via Bus\Dispatcher
      (preserves exact job object with context)
      |
      v
Laravel Queue System
  └── Job is serialized with queueContext array
      |
      v
Queue Worker
  ├── Unserializes job
  ├── RestoreJobContext middleware fires
  │   ├── Converts array context → QueueContext
  │   └── Calls QueuedJobsManager::restoreContext()
  └── Job's handle() executes with full context restored
```

## Installation

```bash
composer require schoolpalm/queued-jobs
```

Publish configuration:

```bash
php artisan vendor:publish --tag=queued-jobs-config
```

Run migrations:

```bash
php artisan migrate
```

## Laravel Queue Setup

The package uses Laravel's native queue system.

### Create Queue Tables

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

### .env

```
QUEUE_CONNECTION=database
```

### Run Worker

```bash
php artisan queue:work
```

## Creating a Context-Aware Job

Extend `ContextAwareJob`:

```php
namespace Modules\Reports\Jobs;

use SchoolPalm\QueuedJobs\Jobs\ContextAwareJob;

class GenerateReport extends ContextAwareJob
{
    public function handle(): void
    {
        $context = $this->getQueueContext();
        // $context['tenant_id'], $context['school_id'], etc.
    }
}
```

## Dispatching Jobs

### With Automatic Context Capture

```php
QueuedJobs::job(new GenerateReport())->dispatch();
```

This captures the context resolved by `resolveContextUsing()`.

### With Explicit Context Overrides

```php
QueuedJobs::job(new GenerateReport())
    ->withTenant(1)
    ->withSchool(20)
    ->withUser(50)
    ->withModule('reports')
    ->dispatch();
```

### With Queue Configuration

```php
QueuedJobs::job(new GenerateReport())
    ->onConnection('redis')
    ->onQueue('high')
    ->delay(now()->addMinutes(10))
    ->dispatch();
```

## Context Restoration

When a queue worker processes a job, the package restores the original application context before executing the job.

### Registering a Restorer

```php
QueuedJobs::restoreContextUsing(function (QueueContext $context) {
    tenancy()->initialize($context->tenantId());
    session(['school_id' => $context->schoolId()]);
    auth()->loginUsingId($context->userId());
});
```

## Job Context Fields

| Field       | Type              | Description                               |
| ----------- | ----------------- | ----------------------------------------- |
| `tenant_id` | string\|int\|null | Identifies the tenant database            |
| `school_id` | string\|int\|null | Identifies the school inside a tenant     |
| `user_id`   | string\|int\|null | Identifies the user who triggered the job |
| `module`    | string\|null      | Module that owns the job                  |
| `metadata`  | array             | Custom arbitrary metadata                 |

## Package Architecture

```
src/
├── Builders/
│   ├── JobBuilder.php           # Fluent job dispatch builder
│   └── JobResultBuilder.php     # Job result query builder
├── Context/
│   └── QueueContext.php         # Immutable context value object
├── Enums/
│   └── JobResultStatus.php      # Pending / Processing / Completed / Failed
├── Facades/
│   └── QueuedJobs.php           # Facade
├── Jobs/
│   └── ContextAwareJob.php      # Base job class
├── Managers/
│   ├── QueuedJobsManager.php    # Context capture & restore
│   └── JobResultManager.php     # Job result CRUD
├── Middleware/
│   └── RestoreJobContext.php    # Queue middleware
├── Models/
│   └── QueueJobResult.php       # Eloquent model
├── Providers/
│   └── QueuedJobsServiceProvider.php
├── Resources/
│   └── JobResultResource.php    # API transformation
└── Support/
    ├── PendingJob.php           # Dispatch wrapper
    └── QueueConfiguration.php   # Config helper
```

## Design Principles

- Uses Laravel Queue instead of creating a custom queue system
- Does not manage queue storage
- Does not replace Laravel workers
- Provides context awareness for multi-tenant applications
- Context is serialized directly in the job payload (no external stores)
- Avoids `PendingDispatch` magic methods that can create new job instances

## Use Cases

- Generating student reports with correct school context
- Processing examination results per tenant
- Sending bulk SMS/email notifications as the correct user
- Generating PDF documents with school branding
- AI/ML processing tasks requiring tenant data isolation
- Large data imports/exports per school
- Payroll processing in multi-tenant environments
- Background synchronization across modules

## Key Bug Fix (v1.0.0)

The initial release fixed a critical issue where queue context was lost during dispatch. The root cause was `PendingDispatch`'s `__call` magic method invoking `Dispatchable::dispatch()` statically, which created a new job instance without context. The fix bypasses `PendingDispatch` entirely and dispatches directly through Laravel's `Bus\Dispatcher`.

