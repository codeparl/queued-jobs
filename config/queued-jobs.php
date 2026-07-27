<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Queued Jobs Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file allows you to customize the behaviour of the
    | queue context propagation package. You can define which context
    | resolvers are active, how context is stored, and other options.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Context Store
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default context store driver that should be
    | used by the package. The "cache" driver uses Laravel's cache system.
    |
    | Supported: "cache", "array"
    |
    */
    'default_store' => env('QUEUED_JOBS_STORE', 'cache'),

    /*
    |--------------------------------------------------------------------------
    | Context Store Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the available context store drivers. Each
    | driver has its own configuration options.
    |
    */
    'stores' => [
        'cache' => [
            'driver' => 'cache',
            'store' => env('QUEUED_JOBS_CACHE_STORE', 'default'),
            'prefix' => 'queued_jobs_context_',
            'ttl' => 3600,
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Resolver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the class that should be used to resolve the
    | current application context. This should implement the
    | QueueContextResolver contract.
    |
    */
    'context_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Queue
    |--------------------------------------------------------------------------
    |
    | The default queue connection and name that jobs should be dispatched to
    | when no specific queue is provided.
    |
    */
    'default_queue' => env('QUEUED_JOBS_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Job Middleware
    |--------------------------------------------------------------------------
    |
    | If set to true, the RestoreQueueContext middleware will automatically
    | be applied to all queued jobs dispatched through the package.
    |
    */
    'auto_restore_context' => true,

    /*
    |--------------------------------------------------------------------------
    | Serialization
    |--------------------------------------------------------------------------
    |
    | The serialization driver to use when storing context data in the queue
    | payload. Supported: "json", "igbinary"
    |
    */
    'serialization' => env('QUEUED_JOBS_SERIALIZATION', 'json'),
];
