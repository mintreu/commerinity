# E2E MLM Commission Validation Pipeline Overview

## System Under Test (SUT) Flow:

1.  **Product Management**: Product Creation -> Product Stock Add
2.  **Order Processing**: API Endpoints (frontend interaction) -> Add to Cart -> Order Placement
3.  **Post-Order Processing**: On successful order -> Commission Calculation -> BV/PV rewards distribution -> MLM commission payouts

## High-Level Architecture (ASCII Diagram):

```
+---------------------+      +---------------------+      +---------------------+
|  Test Orchestrator  |      |   Laravel Artisan   |      |      PHPUnit        |
| (run_e2e_mlm.sh)    |----->| (seed_test_world.php)|----->| (MlmFlowE2ETest.php)|
|                     |      | (Seeder/Commands)   |<-----| (MlmRefundRollback) |
+---------------------+      +---------------------+      +---------------------+
       |                                   |                         |
       |                                   |                         |
       v                                   v                         v
+--------------------------------------------------------------------------+
|                        Isolated Test Environment                         |
|--------------------------------------------------------------------------|
|  In-Memory SQLite DB (for speed & isolation) / Dedicated Test DB Schema  |
|--------------------------------------------------------------------------|
|  Mocked Services: Payment Gateway, External Payout (Ledger only)         |
|--------------------------------------------------------------------------|
|  Frozen Time & RNG (Carbon::setTestNow, mt_srand)                        |
+--------------------------------------------------------------------------+
       |
       |  Captures output, exit codes, artifacts
       v
+---------------------+      +---------------------+
|    Report Generator |----->|  Factual Report     |
| (part of run_e2e.sh)|      | (report_YYYYMMDD.md)|
|                     |----->|  Issues Report      |
|                     |      | (issues_YYYYMMDD.md)|
+---------------------+      +---------------------+
```

## Assumptions and Design Choices:

1.  **Framework**: This pipeline is designed for a Laravel application, leveraging its Artisan commands, Eloquent ORM, and testing utilities (Pest, PHPUnit).
2.  **Environment Isolation**:
    *   Tests will run against an in-memory SQLite database (or a dedicated test schema if configured, but SQLite in-memory is preferred for speed and simplicity in E2E).
    *   Database transactions will be used per test, or `RefreshDatabase` trait for full isolation.
    *   The `.env.testing` file will configure the test environment.
3.  **Determinism**:
    *   Time will be frozen using `Carbon::setTestNow()` at the beginning of each test.
    *   The random number generator (RNG) will be seeded using `mt_srand()` or similar to ensure consistent data generation in factories/seeders.
    *   API calls from the "frontend" will be simulated directly within tests, bypassing actual HTTP requests to ensure full control and speed.
4.  **Mocking External Services**:
    *   **Payment Gateway**: Will be fully mocked. The mock will provide deterministic responses (success, failure) based on test requirements. No real payment transactions will occur.
    *   **External Payouts**: Real money transfers are strictly forbidden. The system will only interact with internal ledger tables, simulating the *effect* of a payout without actual external communication.
5.  **Seeding**:
    *   The `seed_test_world.php` script will create a predictable "test world," including:
        *   A multi-level distributor tree (at least 3 levels deep).
        *   Customer users.
        *   Products with defined stock, BV (Business Volume), and PV (Personal Volume) values.
        *   A specific versioned commission ruleset (e.g., `ruleset_v1`).
        *   Initial wallet ledger states for users.
6.  **Commission Hashing**: A stable SHA256 hash will be generated for each commission run, based on `order_id`, `rules_version`, `tree_snapshot` (relevant distributor hierarchy), and `line_items`. This hash will be stored and used for auditability and idempotency checks.
7.  **Commission Processing**: For E2E tests, commission calculation and distribution will be triggered and processed synchronously within the test context to ensure immediate validation of results. In a real system, these might be queued, but for E2E, synchronous processing simplifies immediate assertion.
8.  **Reporting**: The `run_e2e_mlm.sh` script will parse PHPUnit's output and generate two Markdown reports:
    *   A factual report summarizing execution, counts, and overall pass/fail status.
    *   An issues report detailing failures, including trimmed stack traces and suspected modules.
9.  **Idempotency**: Tests will verify that re-running the commission logic (e.g., via a simulated webhook or job) does not lead to duplicate payouts or incorrect state changes.
10. **Error Handling**: Tests will cover scenarios like out-of-stock, payment failures, concurrent orders, and duplicate webhooks to ensure graceful degradation and correct state management.
11. **Testing Framework**: Pest will be the primary testing framework, leveraging Laravel's testing capabilities.

This overview sets the stage for the subsequent deliverables.
