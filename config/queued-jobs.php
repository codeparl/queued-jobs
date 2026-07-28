<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection
    |--------------------------------------------------------------------------
    |
    | The default Laravel queue connection used when a job does not
    | explicitly define a connection.
    |
    */

    'connection' => env(
        'QUEUE_CONNECTION',
        'database'
    ),


    /*
    |--------------------------------------------------------------------------
    | Default Queue Name
    |--------------------------------------------------------------------------
    |
    | The queue name used when a job does not explicitly define a queue.
    |
    */

    'queue' => env(
        'QUEUE_NAME',
        'default'
    ),


    /*
    |--------------------------------------------------------------------------
    | Context Capture
    |--------------------------------------------------------------------------
    |
    | Determines whether the current application context should be
    | automatically captured when dispatching context-aware jobs.
    |
    | Context is stored directly inside the serialized Laravel job payload.
    |
    */

    'capture_context' => true,


    /*
    |--------------------------------------------------------------------------
    | Context Restoration
    |--------------------------------------------------------------------------
    |
    | Determines whether queue workers should automatically restore
    | application context before executing a job.
    |
    */

    'auto_restore_context' => true,


    /*
    |--------------------------------------------------------------------------
    | Context Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware executed by ContextAwareJob.
    |
    */

    'middleware' => [

        \SchoolPalm\QueuedJobs\Middleware\RestoreQueueContext::class,

    ],


    /*
    |--------------------------------------------------------------------------
    | Default Job Options
    |--------------------------------------------------------------------------
    |
    | Default Laravel job execution options.
    |
    */

    'tries' => 3,

    'timeout' => 120,

];
