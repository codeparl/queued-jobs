<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Context;

use SchoolPalm\QueuedJobs\Exceptions\QueueContextException;

/**
 * Immutable value object representing the application execution context.
 *
 * This class encapsulates all contextual information that should be
 * preserved when dispatching a queued job. Instances are immutable
 * and should be created using named constructor or the constructor itself.
 *
 * Properties:
 * - tenantId:  The active tenant identifier (nullable).
 * - schoolId:  The active school identifier (nullable).
 * - userId:    The authenticated user identifier (nullable).
 * - module:    The module that is dispatching the job (nullable).
 * - metadata:  Arbitrary key-value pairs for extension (nullable).
 */
final class QueueContext
{
    /**
     * @param string|null  $tenantId  The active tenant identifier.
     * @param int|string|null $schoolId The active school identifier.
     * @param int|string|null $userId   The authenticated user identifier.
     * @param string|null  $module   The dispatching module name.
     * @param array<string, mixed> $metadata Additional custom metadata.
     */
    public function __construct(
        private readonly string|int|null $tenantId = null,
        private readonly string|int|null $schoolId = null,
        private readonly string|int|null $userId = null,
        private readonly ?string $module = null,
        private readonly array $metadata = [],
    ) {}

    /**
     * Create a new context with a different tenant identifier.
     *
     * @param string|int|null $tenantId
     *
     * @return self
     */
    public function withTenantId(string|int|null $tenantId): self
    {
        return new self(
            tenantId: $tenantId,
            schoolId: $this->schoolId,
            userId: $this->userId,
            module: $this->module,
            metadata: $this->metadata,
        );
    }

    /**
     * Create a new context with a different school identifier.
     *
     * @param string|int|null $schoolId
     *
     * @return self
     */
    public function withSchoolId(string|int|null $schoolId): self
    {
        return new self(
            tenantId: $this->tenantId,
            schoolId: $schoolId,
            userId: $this->userId,
            module: $this->module,
            metadata: $this->metadata,
        );
    }

    /**
     * Create a new context with a different user identifier.
     *
     * @param string|int|null $userId
     *
     * @return self
     */
    public function withUserId(string|int|null $userId): self
    {
        return new self(
            tenantId: $this->tenantId,
            schoolId: $this->schoolId,
            userId: $userId,
            module: $this->module,
            metadata: $this->metadata,
        );
    }

    /**
     * Create a new context with a different module name.
     *
     * @param string|null $module
     *
     * @return self
     */
    public function withModule(?string $module): self
    {
        return new self(
            tenantId: $this->tenantId,
            schoolId: $this->schoolId,
            userId: $this->userId,
            module: $module,
            metadata: $this->metadata,
        );
    }

    /**
     * Create a new context with merged metadata.
     *
     * @param array<string, mixed> $metadata
     *
     * @return self
     */
    public function withMetadata(array $metadata): self
    {
        return new self(
            tenantId: $this->tenantId,
            schoolId: $this->schoolId,
            userId: $this->userId,
            module: $this->module,
            metadata: array_merge($this->metadata, $metadata),
        );
    }

    /**
     * Create a new context from the given payload array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     *
     * @throws QueueContextException If the payload data is invalid.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'] ?? $data['tenantId'] ?? null,
            schoolId: $data['school_id'] ?? $data['schoolId'] ?? null,
            userId: $data['user_id'] ?? $data['userId'] ?? null,
            module: $data['module'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * Convert the context to an array for serialisation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'school_id' => $this->schoolId,
            'user_id' => $this->userId,
            'module' => $this->module,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get the tenant identifier.
     *
     * @return string|int|null
     */
    public function getTenantId(): string|int|null
    {
        return $this->tenantId;
    }

    /**
     * Get the school identifier.
     *
     * @return string|int|null
     */
    public function getSchoolId(): string|int|null
    {
        return $this->schoolId;
    }

    /**
     * Get the user identifier.
     *
     * @return string|int|null
     */
    public function getUserId(): string|int|null
    {
        return $this->userId;
    }

    /**
     * Get the module name.
     *
     * @return string|null
     */
    public function getModule(): ?string
    {
        return $this->module;
    }

    /**
     * Get all metadata.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get a single metadata value by key.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Determine if the context is empty (no values set).
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->tenantId === null
            && $this->schoolId === null
            && $this->userId === null
            && $this->module === null
            && $this->metadata === [];
    }

    /**
     * Serialize the context to a JSON string.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}

