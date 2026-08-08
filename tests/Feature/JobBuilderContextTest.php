<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Tests\Fixtures\Jobs\TestJob;
use SchoolPalm\QueuedJobs\Facades\QueuedJobs;


it('allows explicit job context assignment', function () {

    Queue::fake();


    QueuedJobs::job(
        new TestJob()
    )
        ->withTenant(1)
        ->withSchool(20)
        ->withUser(50)
        ->dispatch();


    Queue::assertPushed(
        TestJob::class,
        function (TestJob $job) {

            $context = $job->getQueueContext();


            return $context['tenant_id'] === 1
                && $context['school_id'] === 20
                && $context['user_id'] === 50;
        }
    );
});

it('attaches metadata to jobs', function () {

    Queue::fake();


    QueuedJobs::job(
        new TestJob()
    )
        ->withMetadata([
            'report_id' => 100,
            'format' => 'pdf',
        ])
        ->dispatch();


    Queue::assertPushed(
        TestJob::class,
        function (TestJob $job) {

            return $job
                ->getQueueContext()['metadata']['report_id'] === 100;
        }
    );
});

it('restores job context through middleware', function () {

    $restored = null;


    QueuedJobs::restoreContextUsing(
        function ($context) use (&$restored) {
            $restored = $context;
        }
    );


    $job = new TestJob();


    $job->setQueueContext([
        'tenant_id' => 5,
        'school_id' => 10,
        'user_id' => 20,
    ]);


    $middleware = app(
        \SchoolPalm\QueuedJobs\Middleware\RestoreJobContext::class
    );


    $middleware->handle(
        $job,
        fn() => null
    );


    expect($restored)
        ->not()
        ->toBeNull();


    expect($restored)
        ->toBeInstanceOf(\SchoolPalm\QueuedJobs\Context\QueueContext::class);


    expect($restored->schoolId())
        ->toBe(10);
});

it('creates a job result record', function () {

    $job = new TestJob();


    $result = app(
        \SchoolPalm\QueuedJobs\Managers\JobResultManager::class
    )
        ->create(
            $job,
            [
                'school_id' => 10,
                'user_id' => 5,
            ]
        );


    expect($result->school_id)
        ->toBe(10);
});

it('stores successful job output', function () {

    $manager = app(
        \SchoolPalm\QueuedJobs\Managers\JobResultManager::class
    );


    $result = $manager->create(
        new TestJob(),
        [
            'school_id' => 10,
        ]
    );


    $manager->complete(
        $result->id,
        [
            'file' => 'timetable.pdf',
        ]
    );


    $result->refresh();


    expect($result->status)
        ->toBe('completed');


    expect($result->result['file'])
        ->toBe('timetable.pdf');
});

it('stores failed job errors', function () {

    $manager = app(
        \SchoolPalm\QueuedJobs\Managers\JobResultManager::class
    );


    $result = $manager->create(
        new TestJob(),
        []
    );


    $manager->fail(
        $result->id,
        'Something failed'
    );


    $result->refresh();


    expect($result->status)
        ->toBe('failed');


    expect($result->error['message'])
        ->toBe('Something failed');
});

it('allows fluent queue dispatch options', function () {

    Queue::fake();


    QueuedJobs::job(
        new TestJob()
    )
        ->onQueue('high')
        ->onConnection('redis')
        ->delay(now()->addMinutes(5))
        ->dispatch();


    Queue::assertPushed(
        TestJob::class,
        function (TestJob $job) {

            return $job->queue === 'high'
                && $job->connection === 'redis';
        }
    );
});

it('allows retry and timeout options', function () {

    Queue::fake();


    QueuedJobs::job(
        new TestJob()
    )
        ->tries(5)
        ->timeout(60)
        ->backoff([10, 20, 30])
        ->dispatch();


    Queue::assertPushed(
        TestJob::class,
        function (TestJob $job) {

            return $job->tries === 5
                && $job->timeout === 60
                && $job->backoff === [10, 20, 30];
        }
    );
});

it('dispatches synchronously when sync is used', function () {

    // Queue::fake() is intentionally NOT used here, because the
    // "sync" connection executes the job immediately on the current
    // process. Execution is observed via the static flag since the
    // sync queue runs a serialized copy of the job instance.


    TestJob::$wasExecuted = false;

    $job = new TestJob();

    QueuedJobs::job($job)
        ->sync()
        ->withMetadata(['request_id' => 123])
        ->dispatch();


    // The job must have been executed synchronously.
    expect(TestJob::$wasExecuted)
        ->toBeTrue();
});

it('supports dispatchSync for immediate execution', function () {

    // Queue::fake() is intentionally NOT used here, because the
    // "sync" connection executes the job immediately on the current
    // process. Execution is observed via the static flag since the
    // sync queue runs a serialized copy of the job instance.


    TestJob::$wasExecuted = false;

    $job = new TestJob();

    QueuedJobs::job($job)
        ->dispatchSync();


    // The job must have been executed synchronously.
    expect(TestJob::$wasExecuted)
        ->toBeTrue();
});
