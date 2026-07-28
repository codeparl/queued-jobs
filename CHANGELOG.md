# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-07-30

### Added

- Initial release of `schoolpalm/queued-jobs`
- `ContextAwareJob` base class — extend to create context-aware queued jobs
- `QueueContext` immutable value object with tenant, school, user, module, and metadata fields
- `QueuedJobsManager` — capture and restore application context
- `JobBuilder` — fluent API for dispatching jobs with context overrides:
  - `withTenant()`, `withSchool()`, `withUser()`, `withModule()`, `withMetadata()`
  - `onConnection()`, `onQueue()`, `delay()`
- `PendingJob` — safe dispatch wrapper that preserves job object identity
- `RestoreJobContext` middleware — restores context before job execution
- `JobResultManager` — CRUD for tracking job execution results
- `JobResultBuilder` — fluent query builder for filtering job results
- `JobResultResource` — API resource transformer
- `QueueJobResult` Eloquent model with UUID primary keys
- `QueueConfiguration` — configuration accessor helper
- Facade (`QueuedJobs`) with IDE autocompletion support
- Service provider with auto-discovery support
- Full test suite with Pest PHP (11 tests, 29 assertions)

### Fixed

- **Critical**: Queue context was lost between `JobBuilder::prepare()` and `Queue::push()`. Root cause was `PendingDispatch`'s `__call` magic method invoking `Dispatchable::dispatch()` statically, creating a new job instance without context. Fix dispatches directly through `Bus\Dispatcher`.
- `RestoreJobContext` middleware now correctly handles array-based context from serialized job payloads
- `JobResultManager::create()` accepts both `QueueContext` objects and plain arrays

