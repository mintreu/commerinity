# Test Status

Last updated: 2026-01-29 00:12

## Test inventory
- Listed tests: 1077 (via `php artisan test --list-tests`).
- Test files in `apiserver/tests`: 100+ items across Feature/Unit.

## Full test run attempts
- `php artisan test` (timeout 20 minutes) -> timed out.
- Earlier attempts at 2 and 5 minutes timed out.
- `php artisan test --stop-on-failure` was aborted by user.

## Coverage snapshot (from `apiserver/coverage.txt`)
- Classes: 0.39% (2/515)
- Methods: 4.46% (133/2981)
- Lines: 3.01% (837/27836)
- Coverage report timestamp: 2026-01-28 19:29:46

## Notes
- Full suite appears slow or blocked; isolate by running test groups or specific directories.
- Next run suggestion: run by directory (e.g., `tests/Feature/Auth`) to establish passing/failing areas.
