<?php

declare(strict_types=1);

namespace SchoolPalm\QueuedJobs\Builders;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use SchoolPalm\QueuedJobs\Enums\JobResultStatus;
use SchoolPalm\QueuedJobs\Models\QueueJobResult;

final class JobResultBuilder
{
    /**
     * Underlying query builder.
     */
    private Builder $query;

    public function __construct()
    {
        $this->query = QueueJobResult::query();
    }

    /**
     * Filter by result identifier.
     */
    public function id(string $id): self
    {
        $this->query->where('id', $id);

        return $this;
    }

    /**
     * Filter by Laravel queue job id.
     */
    public function jobId(string $jobId): self
    {
        $this->query->where('job_id', $jobId);

        return $this;
    }

    /**
     * Filter by job class.
     */
    public function jobClass(string $jobClass): self
    {
        $this->query->where('job_class', $jobClass);

        return $this;
    }

    /**
     * Filter by school.
     */
    public function forSchool(string|int|null $schoolId): self
    {
        if ($schoolId === null) {
            $this->query->whereNull('school_id');
        } else {
            $this->query->where('school_id', $schoolId);
        }

        return $this;
    }

    /**
     * Filter by user.
     */
    public function forUser(string|int|null $userId): self
    {
        if ($userId === null) {
            $this->query->whereNull('user_id');
        } else {
            $this->query->where('user_id', $userId);
        }

        return $this;
    }

    /**
     * Filter by module.
     */
    public function forModule(string $module): self
    {
        $this->query->where('module', $module);

        return $this;
    }

    /**
     * Filter by status.
     */
    public function status(JobResultStatus|string $status): self
    {
        $this->query->where(
            'status',
            $status instanceof JobResultStatus
                ? $status->value
                : $status
        );

        return $this;
    }

    /**
     * Pending jobs.
     */
    public function pending(): self
    {
        return $this->status(JobResultStatus::Pending);
    }

    /**
     * Processing jobs.
     */
    public function processing(): self
    {
        return $this->status(JobResultStatus::Processing);
    }

    /**
     * Completed jobs.
     */
    public function completed(): self
    {
        return $this->status(JobResultStatus::Completed);
    }

    /**
     * Failed jobs.
     */
    public function failed(): self
    {
        return $this->status(JobResultStatus::Failed);
    }

    /**
     * Order by newest first.
     */
    public function latest(): self
    {
        $this->query->latest();

        return $this;
    }

    /**
     * Order by oldest first.
     */
    public function oldest(): self
    {
        $this->query->oldest();

        return $this;
    }

    /**
     * Execute and return all results.
     */
    public function get(): Collection
    {
        return $this->query->get();
    }

    /**
     * Return the first matching result.
     */
    public function first(): ?QueueJobResult
    {
        return $this->query->first();
    }

    /**
     * Find by primary key.
     */
    public function find(string $id): ?QueueJobResult
    {
        return QueueJobResult::find($id);
    }

    /**
     * Determine whether any matching records exist.
     */
    public function exists(): bool
    {
        return $this->query->exists();
    }

    /**
     * Count matching records.
     */
    public function count(): int
    {
        return $this->query->count();
    }

    /**
     * Paginate results.
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->query->paginate($perPage);
    }

    /**
     * Delete matching results.
     */
    public function delete(): int
    {
        return $this->query->delete();
    }

    /**
     * Expose the underlying query when needed.
     */
    public function query(): Builder
    {
        return $this->query;
    }
}
