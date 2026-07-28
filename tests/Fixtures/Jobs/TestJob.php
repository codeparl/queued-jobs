<?php

declare(strict_types=1);

namespace Tests\Fixtures\Jobs;

use SchoolPalm\QueuedJobs\Jobs\ContextAwareJob;

final class TestJob extends ContextAwareJob
{
    public bool $executed = false;

    public function handle(): void
    {
        $this->executed = true;

        $this->completeResult([
            'message' => 'Job completed',
            'value' => 100,
        ]);
    }
}
