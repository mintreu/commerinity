# E2E MLM Commission Validation Pipeline - Test Matrix

This document outlines the key test scenarios for the End-to-End (E2E) validation pipeline of the E-commerce and MLM Commission System. Each scenario details the input, expected outcomes, and specific assertions to be made.

## SUT Flow Stages

The tests cover the following stages:
1.  **Product Management**: Product Creation & Stock
2.  **Order Processing**: API interaction, Cart, Order Placement
3.  **Post-Order Processing**: Commission Calculation, BV/PV Distribution, MLM Payouts

## Test Categories

### I. Successful Order Flow (Happy Path)

**Description**: Verify the entire process from product creation to successful MLM commission distribution for a standard order.

**Scenarios**:

*   **Scenario 1.1: Single Product, Single Customer Order**
    *   **Input**: Customer user, 1 product with stock and BV/PV, default commission rules.
    *   **Expected Outcome**: Order placed successfully, stock decreased, customer wallet updated, distributor(s) in the upline receive commissions.
    *   **Assertions**:
        *   Product stock reduced correctly.
        *   Order status transitions valid (e.g., `pending` -> `processing` -> `completed`).
        *   Order totals (subtotal, tax, shipping, discounts) consistent.
        *   Wallet ledger entries balanced (sum credits == sum debits for all affected users).
        *   Correct BV/PV distributed to upline according to rules.
        *   MLM commissions allocated to upline per level caps.
        *   No negative wallet balances.
        *   Commission hash generated and stored.
        *   Commission breakdown snapshot stored.

*   **Scenario 1.2: Multiple Products, Multiple Customers Order**
    *   **Input**: Multiple customer users, multiple products, varying quantities, default commission rules.
    *   **Expected Outcome**: All orders processed, stocks decreased, customer wallets updated, multiple distributors receive commissions.
    *   **Assertions**: Same as 1.1, applied across all orders and involved parties.

*   **Scenario 1.3: Order by Distributor**
    *   **Input**: Distributor user places an order, 1 product.
    *   **Expected Outcome**: Distributor receives BV/PV on their own purchase, upline receives commissions.
    *   **Assertions**: Same as 1.1, with specific checks for self-commissioning rules (if applicable).

### II. Failure and Edge Cases

**Description**: Verify system behavior under various failure conditions and edge cases to ensure robustness and data integrity.

**Scenarios**:

*   **Scenario 2.1: Payment Failed**
    *   **Input**: Attempt to place an order with simulated payment failure.
    *   **Expected Outcome**: Order status reflects payment failure, no stock change, no commission generated.
    *   **Assertions**:
        *   Order status `payment_failed`.
        *   Product stock remains unchanged.
        *   No commission records created.
        *   No wallet ledger entries related to order/commission.

*   **Scenario 2.2: Product Out of Stock**
    *   **Input**: Attempt to place an order for a product that is out of stock.
    *   **Expected Outcome**: Order placement fails, appropriate error message.
    *   **Assertions**:
        *   Order not created.
        *   API returns a clear "out of stock" error.
        *   No stock decrement.
        *   No commission records.

*   **Scenario 2.3: Duplicate Order/Commission Webhook**
    *   **Input**: Simulate receiving the same successful order webhook twice.
    *   **Expected Outcome**: Only one order is processed, and commissions are generated only once.
    *   **Assertions**:
        *   Only one unique order record created.
        *   Only one set of commission records created.
        *   Wallet balances reflect single processing.
        *   Idempotency: Re-running commission job does not duplicate payouts.

*   **Scenario 2.4: Concurrent Orders (Race Condition)**
    *   **Input**: Simulate multiple users attempting to order the last item(s) concurrently.
    *   **Expected Outcome**: Only the correct number of orders succeed based on available stock; no double decrement.
    *   **Assertions**:
        *   Final stock count is correct (zero or negative if overselling is allowed, but ideally zero).
        *   Only successful orders decrement stock.
        *   No race condition leads to invalid stock counts (e.g., negative stock when not allowed).

### III. Refund and Rollback Scenarios

**Description**: Verify the correct handling of refunds and their impact on stock, orders, and commissions.

**Scenarios**:

*   **Scenario 3.1: Full Refund**
    *   **Input**: A successfully completed order is fully refunded.
    *   **Expected Outcome**: Order status `refunded`, commissions are fully rolled back, stock is restored.
    *   **Assertions**:
        *   Order status `refunded`.
        *   Product stock restored to pre-order level.
        *   All commission records for this order are voided/reversed.
        *   Wallet ledger entries reflect full commission rollback (balanced debits/credits).
        *   Upline distributor wallets correctly reflect the rollback.
        *   Commission hash invalidation or new hash for rollback recorded.

*   **Scenario 3.2: Partial Refund**
    *   **Input**: A successfully completed order is partially refunded (e.g., one of two items, or partial amount).
    *   **Expected Outcome**: Order status reflects partial refund, proportional commission rollback, proportional stock restoration.
    *   **Assertions**:
        *   Order status `partially_refunded`.
        *   Product stock proportionally restored.
        *   Commission records proportionally voided/reversed based on refund amount/items.
        *   Wallet ledger entries reflect proportional rollback.
        *   Upline distributor wallets correctly reflect the proportional rollback.

## Invariants to Assert Across All Relevant Tests:

*   **Stock**: Never goes negative unless explicitly allowed by system rules (and if so, behavior is well-defined).
*   **Order Totals**: Always sum up correctly (subtotal + tax + shipping - discounts).
*   **Ledger Balance**: For any transaction, sum of credits must equal sum of debits.
*   **Wallet Balances**: No user's wallet balance should become negative unexpectedly.
*   **Commission Idempotency**: Running commission job multiple times for the same valid input results in identical outcomes (no duplicates).
*   **Data Integrity**: All related records (order, order items, commissions, ledger entries) are consistent after any operation.
