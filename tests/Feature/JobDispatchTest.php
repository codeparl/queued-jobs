<?php

declare(strict_types=1);

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Queue;
use SchoolPalm\QueuedJobs\Facades\QueuedJobs;
use Tests\Fixtures\Jobs\TestJob;

it('can dispatch a queued job', function () {

    Queue::fake();


    QueuedJobs::job(
        new TestJob()
    )
        ->dispatch();


    Queue::assertPushed(
        TestJob::class
    );
});

it('attaches queue context to jobs', function () {

    Queue::fake();


    QueuedJobs::resolveContextUsing(
        fn() => [
            'school_id' => 10,
            'user_id' => 5,
        ]
    );


    QueuedJobs::job(
        new TestJob()
    )
        ->dispatch();


    Queue::assertPushed(
        TestJob::class,
        function (TestJob $job) {

            return $job
                ->getQueueContext()['school_id'] === 10
                && $job
                    ->getQueueContext()['user_id'] === 5;
        }
    );
});

it('attaches and preserves queue context through dispatch', function () {
    Queue::fake();

    QueuedJobs::resolveContextUsing(
        fn() => [
            'school_id' => 10,
            'user_id' => 5,
        ]
    );

    $job = new TestJob();

    QueuedJobs::job($job)
        ->dispatch();

    Queue::assertPushed(
        TestJob::class,
        function (TestJob $pushedJob) {

            $context = $pushedJob->getQueueContext();

            // Context must be preserved after dispatch
            expect($context)
                ->toMatchArray([
                    'tenant_id' => null,
                    'school_id' => 10,
                    'user_id' => 5,
                    'module' => null,
                    'metadata' => [],
                ]);

            return true;
        }
    );
});

it('allows job context to override global context', function () {

    Queue::fake();


    QueuedJobs::resolveContextUsing(
        fn() => [
            'school_id' => 1,
        ]
    );


    QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(20)
        ->dispatch();


    Queue::assertPushed(
        TestJob::class,
        function (TestJob $job) {

            $context = $job->getQueueContext();

            expect($context['school_id'])
                ->toBe(20);

            expect($context['school_id'])
                ->not->toBe(1);

            return true;
        }
    );
});
it('restores context before job execution', function () {

    $restored = null;

    QueuedJobs::restoreContextUsing(
        function ($context) use (&$restored) {
            $restored = $context;
        }
    );


    $job = new TestJob();

    $job->setQueueContext([
        'school_id' => 10,
        'user_id' => 5,
    ]);


    app(\SchoolPalm\QueuedJobs\Middleware\RestoreJobContext::class)
        ->handle(
            $job,
            fn() => null
        );


    expect($restored)
        ->not->toBeNull();


    expect($restored)
        ->toBeInstanceOf(\SchoolPalm\QueuedJobs\Context\QueueContext::class);


    expect($restored->schoolId())
        ->toBe(10);
});
