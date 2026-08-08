<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use SchoolPalm\QueuedJobs\Facades\QueuedJobs;
use SchoolPalm\QueuedJobs\Models\QueueJobResult;
use SchoolPalm\QueuedJobs\Resources\JobResultResource;
use Tests\Fixtures\Jobs\TestJob;

it('creates a job result record when dispatching a context-aware job', function () {

    Queue::fake();

    QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(10)
        ->withUser(5)
        ->withModule('reports')
        ->dispatch();

    $result = QueueJobResult::query()->first();

    expect($result)->not->toBeNull();

    expect($result->job_class)
        ->toBe(TestJob::class);

    expect($result->school_id)
        ->toBe(10);

    expect($result->user_id)
        ->toBe(5);

    expect($result->module)
        ->toBe('reports');

    expect($result->status)
        ->toBe('pending');
});

it('exposes the created result through the builder', function () {

    Queue::fake();

    $builder = QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(10);

    $builder->dispatch();

    $result = $builder->result();

    expect($result)
        ->toBeInstanceOf(QueueJobResult::class);

    expect($result->school_id)
        ->toBe(10);
});

it('transforms the created result via the resource API', function () {

    Queue::fake();

    $builder = QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(10)
        ->withUser(5);

    $builder->dispatch();

    $resource = $builder->resultResource();

    expect($resource)
        ->toBeInstanceOf(JobResultResource::class);

    $array = $resource->toArray();

    expect($array['school_id'])
        ->toBe(10);

    expect($array['user_id'])
        ->toBe(5);

    expect($array['job_class'])
        ->toBe(TestJob::class);

    expect($array['status'])
        ->toBe('pending');
});

it('exposes the created result as an array through the builder', function () {

    Queue::fake();

    $builder = QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(10);

    $builder->dispatch();

    expect($builder->resultArray())
        ->toMatchArray([
            'school_id' => 10,
            'job_class' => TestJob::class,
        ]);
});

it('does not create a result for non-context-aware jobs', function () {

    Queue::fake();

    $job = new class {
        public function handle(): void
        {
            // no-op
        }
    };

    QueuedJobs::job($job)
        ->dispatch();

    expect(QueueJobResult::query()->count())
        ->toBe(0);
});

it('returns null result APIs for non-context-aware jobs', function () {

    Queue::fake();

    $builder = QueuedJobs::job(
        new class {
            public function handle(): void
            {
                // no-op
            }
        }
    );

    $builder->dispatch();

    expect($builder->result())
        ->toBeNull();

    expect($builder->resultResource())
        ->toBeNull();

    expect($builder->resultArray())
        ->toBeNull();
});

it('attaches the queue job id to the result record', function () {

    Queue::fake();

    $builder = QueuedJobs::job(
        new TestJob()
    );

    $builder->dispatch();

    $result = $builder->result();

    // With a fake queue the dispatch returns a PendingDispatch without a job id.
    // The job_id column should remain null in that scenario.
    expect($result->job_id)
        ->toBeNull();
});

it('queries results through the result builder with resources', function () {

    Queue::fake();

    QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(10)
        ->dispatch();

    QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(20)
        ->dispatch();

    $resources = QueuedJobs::jobs()
        ->forSchool(10)
        ->resources();

    expect($resources)
        ->toHaveCount(1);

    expect($resources[0]['school_id'])
        ->toBe(10);

    expect($resources[0]['job_class'])
        ->toBe(TestJob::class);
});

it('returns the first matching result resource', function () {

    Queue::fake();

    QueuedJobs::job(
        new TestJob()
    )
        ->withSchool(10)
        ->dispatch();

    $resource = QueuedJobs::jobs()
        ->forSchool(10)
        ->firstResource();

    expect($resource)
        ->toMatchArray([
            'school_id' => 10,
            'job_class' => TestJob::class,
        ]);
});
