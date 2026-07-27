<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Stores;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use SchoolPalm\QueuedJobs\Contracts\QueueContextStore;
use SchoolPalm\QueuedJobs\Context\QueueContext;
use SchoolPalm\QueuedJobs\Support\QueueConfiguration;

/**
 * Cache-backed implementation of the QueueContextStore contract.
 *
 * Stores queue context data in Laravel's cache system so it can
 * be retrieved when the queued job is processed by a worker.
 */
final class CacheContextStore implements QueueContextStore
{
    /**
     * The cache repository instance.
     *
     * @var CacheRepository
     */
    private readonly CacheRepository $cache;

    /**
     * The prefix for cache keys.
     *
     * @var string
     */
    private readonly string $prefix;

    /**
     * The TTL for cached contexts in seconds.
     *
     * @var int
     */
    private readonly int $ttl;

    /**
     * @param CacheFactory      $cache  Laravel's cache factory.
     * @param QueueConfiguration $config The package configuration.
     */
    public function __construct(
        CacheFactory $cache,
        QueueConfiguration $config,
    ) {
        $storeConfig = $config->storeConfig('cache');
        $storeName = $storeConfig['store'] ?? 'default';

        $this->cache = $cache->store($storeName);
        $this->prefix = $storeConfig['prefix'] ?? 'queued_jobs_context_';
        $this->ttl = (int) ($storeConfig['ttl'] ?? 3600);
    }

    /**
     * {@inheritdoc}
     */
    public function store(QueueContext $context): string
    {
        $id = $this->generateId();

        $this->cache->put(
            key: $this->prefix . $id,
            value: $context->toArray(),
            ttl: $this->ttl,
        );

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve(string $id): ?QueueContext
    {
        $data = $this->cache->get($this->prefix . $id);

        if ($data === null || ! is_array($data)) {
            return null;
        }

        return QueueContext::fromArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $id): void
    {
        $this->cache->forget($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $id): bool
    {
        return $this->cache->has($this->prefix . $id);
    }

    /**
     * Generate a unique identifier for a context entry.
     *
     * @return string
     */
    private function generateId(): string
    {
        return bin2hex(random_bytes(16));
    }
}

