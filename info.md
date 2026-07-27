# SchoolPalm Queued Jobs

**schoolpalm/queued-jobs** is a Laravel package that provides context-aware queue processing for SchoolPalm applications.

It allows module developers to create background jobs that automatically preserve and restore SchoolPalm application context such as:

- Tenant database context
- School context
- User context
- Application execution context

## Overview

Laravel already provides a powerful queue system. This package does not replace Laravel queues. Instead, it extends Laravel jobs by adding SchoolPalm awareness.

Module Developer | | v SchoolPalm Queued Jobs | | Attach SchoolPalm Context | | v Laravel Queue System | | v Database Queue (jobs table) | | v Queue Worker | | Restore Context | | Execute Job

## Installation

```

composer require schoolpalm/queued-jobs
```

Publish configuration:

```

php artisan vendor:publish \
--tag=queued-jobs-config
```

## Laravel Queue Setup

The package uses Laravel's native queue system. The default recommended driver is the database queue.

### Create Queue Tables

```

php artisan queue:table

php artisan queue:failed-table

php artisan migrate
```

### .env

```

QUEUE_CONNECTION=database
```

### Run Worker

```

php artisan queue:work
```

## Creating a SchoolPalm Job

Module developers should extend **SchoolPalmJob** instead of Laravel's default Job class.

```

namespace Modules\Reports\Jobs;

use SchoolPalm\QueuedJobs\Jobs\SchoolPalmJob;

class GenerateReport extends SchoolPalmJob
{

    public function handle()
    {
        // Generate report

    }

}

```

## Dispatching Jobs

Jobs are dispatched normally.

```

GenerateReport::dispatch();
```

The package automatically captures the current SchoolPalm context.

Example context:

```

[
    "tenant_id" => "tenant_abc",
    "school_id" => 15,
    "user_id"   => 20
]
```

## Context Restoration

When a queue worker processes a job, the package restores the original application context before executing the job.

Example:

```


class RestoreContext
{

    public function handle($job, $next)
    {

        Tenant::initialize(
            $job->context->tenantId
        );


        SchoolContext::set(
            $job->context->schoolId
        );


        return $next($job);

    }

}

```

The job developer does not need to manually switch databases or schools.

## Job Context

A job context contains information required to execute a job in the same environment where it was created.

| Context | Description                               |
| ------- | ----------------------------------------- |
| Tenant  | Identifies the tenant database            |
| School  | Identifies the school inside a tenant     |
| User    | Identifies the user who triggered the job |

## Package Architecture

```


src/

├── Context/
│
│   ├── JobContext.php
│   └── ContextResolver.php
│
├── Jobs/
│
│   └── SchoolPalmJob.php
│
├── Middleware/
│
│   └── RestoreContext.php
│
├── Providers/
│
│   └── QueuedJobsServiceProvider.php
│
└── Facades/

    └── QueuedJobs.php


```

## Design Principles

- Uses Laravel Queue instead of creating a custom queue system.
- Does not manage queue storage.
- Does not replace Laravel workers.
- Provides SchoolPalm context awareness.
- Allows modules to create tenant-aware background processes.

## Use Cases

- Generating student reports
- Processing examination results
- Sending bulk SMS notifications
- Generating documents
- AI processing tasks
- Large data imports
- Payroll processing
- Background synchronization

## Future Extensions

- Job progress tracking using cache-store
- Queue dashboard integration
- Module job monitoring
- Retry policies
- Scheduled module jobs

## Summary

**schoolpalm/queued-jobs** provides a bridge between Laravel queues and SchoolPalm's multi-tenant architecture.

It enables module developers to write simple queue jobs while ensuring that tenant and school context is preserved automatically.