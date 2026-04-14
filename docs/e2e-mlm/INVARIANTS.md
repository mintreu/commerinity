# E2E MLM Commission Validation Pipeline - Invariants

This document enumerates the critical invariants and hard safety rules that the End-to-End (E2E) validation pipeline for the E-commerce and MLM Commission System must strictly enforce. These are non-negotiable principles guiding the design and execution of all tests.

## Hard Safety Rules (Non-Negotiable)

1.  **Truthfulness of Test Results**: Tests must **NEVER** claim to have passed unless the underlying test runner (e.g., PHPUnit) returns an exit code of `0`, and exact evidence (e.g., parsed JUnit XML, console output summary) can be shown to substantiate the pass. Any deviation means `FAILED`.
2.  **No Production Data Modification**: The E2E pipeline must **NEVER** write to a production database or interact with real external services (e.g., live payment gateways, real payout providers). All operations must occur within isolated, ephemeral environments.
3.  **Isolated Ephemeral Environment**: All tests must run exclusively on:
    *   An in-memory SQLite database (preferred for speed and isolation).
    *   **OR** a dedicated test database schema (if in-memory SQLite is not feasible for specific tests), which is fully reset before each test suite or relevant test group.
    The test environment must be entirely isolated from any production or staging systems.
4.  **No Real Payment Gateways**: The pipeline must **ONLY** use deterministic mocks for all payment gateway interactions. These mocks must provide predictable responses (success, failure, various error codes) without initiating any real financial transactions.
5.  **Sandboxed Payout/Wallet System**: The payout and wallet mechanisms must be completely sandboxed. This means:
    *   Only internal ledger tables are used to simulate fund movements.
    *   **NO** real transfers of funds to external accounts, services, or APIs are permitted.
    *   The ledger should accurately reflect debits and credits from a system perspective, but these are purely representational within the test environment.
6.  **Fail-Loud Principle**: Any failure detected during the pipeline's execution must immediately halt the process and generate an issues file (`issues/e2e_mlm_issues_YYYYMMDD_HHMM.md`) detailing the failure. The pipeline should not attempt to continue with known failures.

## Core System Invariants (to be asserted by tests)

Beyond the hard safety rules for the pipeline itself, the tests will specifically assert the following invariants of the SUT:

### Data Consistency & Integrity

*   **Stock Levels**:
    *   Product stock must accurately reflect successful orders (decremented).
    *   Product stock must be correctly restored upon full or partial refunds.
    *   Stock levels must **NOT** go negative unless explicitly designed and allowed by specific business rules (and if so, this behavior must be predictable).
    *   No race conditions should lead to incorrect stock decrements (e.g., double-decrementing the last item).
*   **Order Status Transitions**: Order statuses must transition through defined, valid states (e.g., `pending` -> `processing` -> `completed` or `pending` -> `payment_failed`). Invalid state jumps are forbidden.
*   **Order Totals**: The sum of line items, taxes, shipping, and discounts must consistently equal the final order total.
*   **Ledger Balance**: For every financial transaction (order, commission, refund), the sum of all credits across affected accounts must always equal the sum of all debits. The system's "balance sheet" should remain consistent.
*   **Wallet Balances**:
    *   User wallet balances must accurately reflect all legitimate credits (commissions) and debits (payouts/refunds).
    *   Wallet balances must **NOT** become negative unless there is an explicit, designed business rule allowing overdraft (and this rule must be tested).
*   **Commission Idempotency**: Re-running any commission calculation or distribution process with the same inputs must yield the exact same outcome (e.g., no duplicate ledger entries, no double payouts). The state after one run must be identical to the state after multiple runs.

### Business Logic Invariants

*   **Commission Eligibility**: Commissions are only calculated and distributed for successfully completed and paid orders.
*   **BV/PV Distribution**: Business Volume (BV) and Personal Volume (PV) must be distributed according to the configured ruleset and upline structure.
*   **MLM Payout Caps**: MLM commission allocations must respect defined level caps, breakpoints, and other rules.
*   **Refund Impact**:
    *   Full refunds must result in a full, proportional rollback of all associated commissions and stock restoration.
    *   Partial refunds must result in a proportional rollback of commissions and stock restoration.
    *   Rollbacks must be traceable and reflected correctly in ledger entries.

### Determinism & Auditability

*   **Frozen Time**: All time-sensitive operations within tests must occur under a frozen, controlled timestamp.
*   **Frozen Randomness**: Any use of random number generation (e.g., for unique IDs, test data) must be seeded to ensure deterministic outcomes.
*   **Commission Hashing**: Every commission run must generate a stable and verifiable `commission_hash` (`sha256(order_id + rules_version + tree_snapshot + line_items)`) that uniquely identifies the commission event and can be used for audit.
*   **Commission Snapshots**: A snapshot of the commission breakdown for an order must be stored, allowing for future auditing against the ruleset and tree structure at the time of the order.
