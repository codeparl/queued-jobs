<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Managers;

use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Models\QueueJobResult;
use SchoolPalm\QueuedJobs\Enums\JobResultStatus;

final class JobResultManager
{

    public function create(
        object $job,
        QueueContext|array $context
    ): QueueJobResult {

        if (is_array($context)) {
            $context = QueueContext::fromArray($context);
        }

        return QueueJobResult::create([

            'job_class' => $job::class,

            'status' => JobResultStatus::Pending->value,

            'school_id' => $context->schoolId(),

            'user_id' => $context->userId(),

            'module' => $context->module(),

        ]);
    }



    public function start(
        QueueJobResult $result
    ): void {

        $result->update([

            'status' => JobResultStatus::Processing->value,

            'started_at' => now(),

        ]);
    }



    public function complete(
        string $id,
        array $result
    ): QueueJobResult {

        $job = QueueJobResult::findOrFail($id);

        $job->update([
            'status' => JobResultStatus::Completed->value,
            'result' => $result,
            'completed_at' => now(),
        ]);

        return $job;
    }



    public function fail(
        string $id,
        string $message
    ): QueueJobResult {

        $result = QueueJobResult::findOrFail($id);

        $result->update([

            'status' => JobResultStatus::Failed->value,

            'error' => [
                'message' => $message,
            ],

            'completed_at' => now(),

        ]);

        return $result;
    }
}
