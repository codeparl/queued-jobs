# TODO: Auto-create job results on dispatch & expose result APIs

## Steps

- [x] 1. Add `JobResultManager::attachJobId()` helper to persist the Laravel queue job id.
- [x] 2. Update `JobBuilder` to inject `JobResultManager` and auto-create a `QueueJobResult`
       record during `prepare()` for result-aware jobs (attaching the result id to the job).
- [x] 3. Add `JobBuilder::result()`, `JobBuilder::resultResource()`, `JobBuilder::resultArray()`
       accessors and capture the queue `job_id` after `dispatch()`.
- [x] 4. Add resource-transforming methods to `JobResultBuilder`
       (`resources()`, `firstResource()`).
- [x] 5. Add feature tests verifying result creation on dispatch and the `JobResultResource` transformer.
- [x] 6. Update README documenting the new result-access APIs.
- [x] 7. Run `composer test` to confirm all tests pass.
