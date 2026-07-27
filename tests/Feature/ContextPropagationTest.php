<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Tests\Feature;

use SchoolPalm\QueuedJobs\Tests\TestCase;

/**
 * Feature tests for context propagation through the queue system.
 *
 * @coversNothing Integration tests for the full context lifecycle.
 */
final class ContextPropagationTest extends TestCase
{
    /**
     * Test that the configuration is properly loaded.
     *
     * @return void
     */
    public function test_configuration_is_loaded(): void
    {
        $config = $this->app['config']->get('queued-jobs');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_store', $config);
        $this->assertArrayHasKey('stores', $config);
        $this->assertArrayHasKey('context_resolver', $config);
    }

    /**
     * Test that the QueueContextManager is resolvable from the container.
     *
     * @return void
     */
    public function test_context_manager_is_resolvable(): void
    {
        $manager = $this->app->make(\SchoolPalm\QueuedJobs\Context\QueueContextManager::class);

        $this->assertInstanceOf(
            \SchoolPalm\QueuedJobs\Context\QueueContextManager::class,
            $manager,
        );
    }

    /**
     * Test that the QueuedJobs facade is accessible.
     *
     * @return void
     */
    public function test_facade_is_accessible(): void
    {
        $this->assertTrue(
            $this->app->bound('queued-jobs.manager'),
        );
    }
}

