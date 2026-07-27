<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Support;

use Illuminate\Contracts\Config\Repository;

/**
 * Configuration helper for the QueuedJobs package.
 *
 * Provides a clean, typed interface for reading package configuration
 * values from Laravel's config repository.
 */
final class QueueConfiguration
{
    /**
     * @param Repository $config Laravel's configuration repository.
     */
    public function __construct(
        private readonly Repository $config,
    ) {}

    /**
     * Get the default context store driver.
     *
     * @return string
     */
    public function defaultStore(): string
    {
        return $this->config->get('queued-jobs.default_store', 'cache');
    }

    /**
     * Get the configuration for a specific store driver.
     *
     * @param string|null $driver The driver name, or null for the default.
     *
     * @return array<string, mixed>
     */
    public function storeConfig(?string $driver = null): array
    {
        $driver = $driver ?? $this->defaultStore();

        return $this->config->get("queued-jobs.stores.{$driver}", []);
    }

    /**
     * Get the registered context resolver class.
     *
     * @return string|null
     */
    public function contextResolver(): ?string
    {
        return $this->config->get('queued-jobs.context_resolver');
    }

    /**
     * Determine if automatic context restoration is enabled.
     *
     * @return bool
     */
    public function autoRestoreContext(): bool
    {
        return (bool) $this->config->get('queued-jobs.auto_restore_context', true);
    }

    /**
     * Get the serialization driver.
     *
     * @return string
     */
    public function serialization(): string
    {
        return $this->config->get('queued-jobs.serialization', 'json');
    }

    /**
     * Get the default queue name.
     *
     * @return string
     */
    public function defaultQueue(): string
    {
        return $this->config->get('queued-jobs.default_queue', 'default');
    }
}
