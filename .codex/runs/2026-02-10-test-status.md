# Feature Test Status (2026-02-10)

## Completed
- `tests/Feature/AddressApiTest.php` — passed after migrating clean database
- `tests/Feature/AddressManagementTest.php` — passed (single example test)
- `tests/Feature/ExampleTest.php` — passed (default Laravel example)
- `tests/Feature/GeoDataTest.php` — passed (geo seed assertions)
- `tests/Feature/KycApiTest.php` — passed (validation, scopes, helpers)
- `tests/Feature/OnboardingFlowTest.php` — passed after onboarding service/validation fixes
- `tests/Feature/TokenDebugTest.php` — passed (token deletion lifecycle example)
- `tests/Feature/TransactionTest.php` — passed (transaction helper coverage)
- `tests/Feature/UserAddressTest.php` — passed (addresses, referrals, onboarding checks)
- `tests/Feature/WalletTest.php` — passed (wallet example)
- `tests/Feature/WalletTopupCheckoutFlowTest.php` — passed after aligning wallet ownership expectation and status payload (`verified` + `is_verified`)
- `tests/Feature/OrderApiTest.php` — passed (order endpoints + invoice stream)

## Heavy suites (to run last)
- `tests/Feature/CompleteWalletFlowTest.php` — attempted multiple times (MySQL + sqlite) but migrations never completed and the suite produced no output even after several minutes; the process had to be aborted twice, so keep this queued for a dedicated window
- `tests/Feature/CompleteWalletFlowTest.php` — individual tests ran cleanly via `php artisan test --filter "<test name>"` (wallet system state, PIN setup, balance retrieval, beneficiary creation, withdrawal flows, stats, checkout endpoints); once migrations finish, rerun the full suite to confirm the holistic flow
- `php artisan test tests/Feature` (full feature suite) — initiated but halted after many minutes of migration churn; will rerun once the heavy suites can be shepherded to completion
