<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Managers\JobResultManager;
use SchoolPalm\QueuedJobs\Middleware\RestoreJobContext;

abstract class ContextAwareJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * Serialized queue context.
     *
     * @var array<string,mixed>
     */
    protected array $queueContext = [];


    /**
     * Job result identifier.
     */
    protected ?string $queueJobResultId = null;



    public function setQueueContext(
        array|QueueContext $context
    ): static {


        $this->queueContext =
            $context instanceof QueueContext
            ? $context->toArray()
            : $context;

        return $this;
    }



    public function getQueueContext(): array
    {
        return $this->queueContext;
    }



    public function middleware(): array
    {
        $middleware = config(
            'queued-jobs.middleware',
            [
                RestoreJobContext::class,
            ]
        );


        return array_map(
            fn(string $class) => app($class),
            $middleware
        );
    }



    public function setJobResultId(
        string $id
    ): static {

        $this->queueJobResultId = $id;

        return $this;
    }



    public function getJobResultId(): ?string
    {
        return $this->queueJobResultId;
    }



    public function completeResult(
        array $result
    ): void {

        if (!$this->queueJobResultId) {
            return;
        }


        app(JobResultManager::class)
            ->complete(
                $this->queueJobResultId,
                $result
            );
    }



    public function failResult(
        string $message
    ): void {

        if (!$this->queueJobResultId) {
            return;
        }


        app(JobResultManager::class)
            ->fail(
                $this->queueJobResultId,
                $message
            );
    }
}
