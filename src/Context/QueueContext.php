<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Context;

/**
 * Class QueueContext
 *
 * Immutable value object representing context attached
 * to a queued job.
 *
 * The context is serialized with the job and restored
 * before execution.
 *
 * Supported default context:
 *
 * - tenant
 * - school
 * - user
 * - module
 * - metadata
 */
final class QueueContext
{
    public function __construct(
        private readonly string|int|null $tenantId = null,

        private readonly string|int|null $schoolId = null,

        private readonly string|int|null $userId = null,

        private readonly ?string $module = null,

        private readonly array $metadata = [],
    ) {}



    /**
     * Create context from array payload.
     */
    public static function fromArray(
        array $context
    ): self {

        return new self(

            tenantId: $context['tenant_id']
                ?? $context['tenantId']
                ?? null,

            schoolId: $context['school_id']
                ?? $context['schoolId']
                ?? null,

            userId: $context['user_id']
                ?? $context['userId']
                ?? null,

            module: $context['module']
                ?? null,

            metadata: $context['metadata']
                ?? [],
        );
    }



    /**
     * Convert context into serializable array.
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
     * Merge another context.
     *
     * Explicit job context overrides global context.
     */
    public function merge(
        QueueContext $context
    ): self {

        return new self(

            tenantId: $context->tenantId ?? $this->tenantId,

            schoolId: $context->schoolId ?? $this->schoolId,

            userId: $context->userId ?? $this->userId,

            module: $context->module ?? $this->module,

            metadata: array_merge(
                $this->metadata,
                $context->metadata
            ),
        );
    }



    public function tenantId(): string|int|null
    {
        return $this->tenantId;
    }


    public function schoolId(): string|int|null
    {
        return $this->schoolId;
    }


    public function userId(): string|int|null
    {
        return $this->userId;
    }


    public function module(): ?string
    {
        return $this->module;
    }


    public function metadata(): array
    {
        return $this->metadata;
    }



    public function isEmpty(): bool
    {
        return $this->tenantId === null
            && $this->schoolId === null
            && $this->userId === null
            && $this->module === null
            && empty($this->metadata);
    }
}
