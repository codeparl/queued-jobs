<?php

declare(strict_types=1);

namespace Tests\Fixtures\Jobs;

use SchoolPalm\QueuedJobs\Jobs\ContextAwareJob;

final class TestJob extends ContextAwareJob
{
    public bool $executed = false;

    /**
     * Static execution flag used to observe execution
     * when the job runs on a serialized (unserialized)
     * instance via the sync queue connection.
     */
    public static bool $wasExecuted = false;

    public function handle(): void
    {
        $this->executed = true;

        static::$wasExecuted = true;

        $this->completeResult([
            'message' => 'Job completed',
            'value' => 100,
        ]);
    }
}
