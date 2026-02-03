# Test Performance Bugs Report

## Overview

This report details the findings from an investigation into why your `apiserver`'s test suite (`apiserver/tests/`) is taking an excessive amount of time (1 hour timeout) and consuming significant memory (4GB RAM allocated), without completing. The primary goal is to identify root causes and suggest solutions to improve test execution speed and stability.

## Summary of Findings

1.  **Browser Test Overheads**: Browser tests are inherently slow due to launching a real browser instance and explicit delays (`pause()` calls) significantly add to execution time.
2.  **Potential Performance Bottlenecks**: Other common causes for slow Laravel/Pest tests (e.g., unmocked external calls, excessive data generation, intentional delays *beyond explicit sleeps*) should be investigated.
3.  **(Previous assumption corrected)**: The `phpunit.xml` database configuration is intentionally set up to use `.env.testing`, which is a valid strategy, though often slower than in-memory SQLite. The focus should be on optimizing tests rather than changing this configuration if it's desired.

## Detailed Issues

### 1. Browser Tests - Inherent Slowness & Explicit Delays

-   **Files**:
    -   `apiserver/tests/Browser/OrderViewTest.php`
    -   `apiserver/tests/Browser/WalletTopupCompleteFlowTest.php`
    -   *(Potentially all files in `apiserver/tests/Browser/`)*
-   **Lines**:
    -   `OrderViewTest.php`: ~L50, L55 (calls to `pause(3000)`)
    -   `WalletTopupCompleteFlowTest.php`: ~L40, L43, L48, L51, L54, L57, L60 (multiple calls to `pause()`)
-   **Description**: Browser tests, by their nature, are significantly slower than unit or feature tests as they involve launching a browser instance and simulating user interaction.
    -   Explicit `pause(X)` calls introduce *intentional delays*. For example, `OrderViewTest.php` adds 6 seconds of delay, and `WalletTopupCompleteFlowTest.php` adds over 7.5 seconds per test method. If there are many such browser tests, these delays accumulate drastically, contributing to the 1-hour timeout.
    -   Furthermore, the `WalletTopupCompleteFlowTest.php` notes a "manual" step for payment completion, suggesting the test cannot fully complete the intended flow automatically, which can lead to longer waiting times or incomplete assertions.
-   **Recommendation**:
    -   **Minimize Browser Tests**: Reserve browser tests for critical end-to-end user flows that cannot be effectively tested otherwise.
    -   **Reduce `pause()` calls**: Review and remove or drastically reduce all `pause()` calls. Replace them with explicit waits for elements to appear (`$page->waitFor('selector')`) or conditions to be met. Arbitrary `sleep()` or `pause()` calls are a common anti-pattern in automated tests.
    -   **Optimize Test Environment**: Ensure the browser test environment is as performant as possible (e.g., headless browser, dedicated test server if needed).

### 2. `apiserver/phpunit.xml` - Database Connection (Clarification)

-   **File**: `apiserver/phpunit.xml`
-   **Lines**: 31-32 (commented-out lines for SQLite in-memory DB)
-   **Description**: You've clarified that tests are intentionally run against a database defined in `.env.testing` rather than an in-memory SQLite. This is a valid setup, but it's inherently slower for tests that frequently interact with the database (especially those using `RefreshDatabase` repeatedly for setup/teardown). While not a bug, this choice contributes to overall test suite duration.
-   **Recommendation**: Given your preferred setup, ensure the database defined in `.env.testing` is optimized for speed (e.g., on a fast SSD, local connection) and that your test setup/teardown is as efficient as possible. The slowness from this choice needs to be considered when evaluating other performance bottlenecks.

### 3. General Test Suite Performance Bottlenecks (Further Investigation Needed)

Beyond browser tests and the database setup, if performance issues persist, the following areas should be investigated:

-   **Unmocked External HTTP Calls**:
    -   **File**: All Feature Tests (especially in `Api`, `Payment`, `Services` directories), *excluding those already verified to use `Http::fake()`*.
    -   **Description**: While many payment-related tests correctly use `Http::fake()`, other tests might be making real HTTP requests to external services (payment gateways, third-party APIs) without proper mocking. This can cause tests to hang indefinitely if the external service is slow or unavailable, or introduce unexpected delays.
    -   **Recommendation**: Systematically review all feature tests. Ensure that any interaction with external services is properly mocked using `Http::fake()` or a similar mocking framework. Look for direct calls to `Http::get()`, `Http::post()`, or client libraries without faking.
-   **Excessive Data Generation**:
    -   **File**: All Feature Tests, especially those involving loops or many related models.
    -   **Description**: Some tests or factories might inadvertently create an extremely large number of database records, leading to slow setup and teardown times, even with `RefreshDatabase`.
    -   **Recommendation**: Review factory definitions and test setup methods for loops creating hundreds or thousands of records unnecessarily. Optimize factories to create only essential data for each test.
-   **Resource Leaks**:
    -   **File**: (Potentially any test or application code triggered by tests)
    -   **Description**: If tests open file handles, database connections, or other resources and fail to close them, this can lead to memory exhaustion or other unexpected behavior over many test runs.
    -   **Recommendation**: Monitor resource usage during test runs if other fixes don't resolve the memory issues. This is harder to pinpoint without dedicated profiling tools.

## Files Reviewed (Issues Identified)

The following files were specifically reviewed:

-   `apiserver/tests/Feature/WalletTopupCheckoutFlowTest.php`: Found to use `RefreshDatabase` and `Http::fake()`. However, it's a browser test with `pause()` calls contributing significantly to slowness.
-   `apiserver/tests/Feature/KycApiTest.php`: Found to use `RefreshDatabase` and no external HTTP calls. No obvious performance issues.
-   `apiserver/tests/Browser/OrderViewTest.php`: Browser test with `DatabaseMigrations` and `pause()` calls contributing to slowness.
-   `apiserver/tests/Browser/WalletTopupCompleteFlowTest.php`: Browser test with multiple `pause()` calls and a noted manual payment completion step, making it less suitable for full automation and contributing to overall slowness.

---
This report identifies the most likely immediate fixes and outlines further investigation areas if needed. The optimization of browser tests and reduction of `pause()` calls are critical next steps.