<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Resources;

use SchoolPalm\QueuedJobs\Models\QueueJobResult;

final class JobResultResource
{
    public function __construct(
        private readonly QueueJobResult $job
    ) {}



    /**
     * Convert job result to API-friendly array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [

            'id' => $this->job->id,

            'job_class' => $this->job->job_class,

            'status' => $this->job->status,

            'school_id' => $this->job->school_id,

            'user_id' => $this->job->user_id,
            'title' => $this->job->title,
            'description' => $this->job->description,
            'module' => $this->job->module,


            'result' => $this->job->result,

            'error' => $this->job->error,


            'started_at' => optional(
                $this->job->started_at
            )->toISOString(),


            'completed_at' => optional(
                $this->job->completed_at
            )->toISOString(),


            'created_at' => optional(
                $this->job->created_at
            )->toISOString(),


            'updated_at' => optional(
                $this->job->updated_at
            )->toISOString(),

        ];
    }



    /**
     * Create resource from model.
     */
    public static function make(
        QueueJobResult $job
    ): self {

        return new self($job);
    }



    /**
     * Create collection response.
     *
     * @param iterable<QueueJobResult> $jobs
     *
     * @return array<int,array<string,mixed>>
     */
    public static function collection(
        iterable $jobs
    ): array {

        $results = [];

        foreach ($jobs as $job) {

            $results[] = self::make($job)
                ->toArray();
        }

        return $results;
    }
}
