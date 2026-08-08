# TODO: Add fluent dispatch-option APIs to JobBuilder

## Steps

- [x] 1. Add private property storage to `JobBuilder` for dispatch options
       (connection, queue, delay, tries, timeout, backoff, afterCommit, middleware).
- [x] 2. Add fluent methods to `JobBuilder`:
       `onConnection`, `onQueue`, `delay`, `tries`, `timeout`, `backoff`,
       `afterCommit`, `middleware`.
- [x] 3. Add `sync()` method to `JobBuilder` for immediate (synchronous) execution.
- [x] 4. Update `prepare()` to apply stored dispatch options to the `PendingJob`.
- [x] 5. Add tests verifying fluent chaining and `sync()`.
- [x] 6. Run `composer test` to confirm all tests pass.

## Notes

- `PendingJob` gained a `sync()` mode executing via the Bus `dispatchSync`
  (routes to Laravel's "sync" queue connection, running middleware).
- `JobBuilder::dispatchSync()` added as a convenience for synchronous dispatch.
- README and CHANGELOG updated to document the new APIs.
- All 15 tests pass (33 assertions).
