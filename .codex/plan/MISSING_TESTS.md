# Missing / Weak Test Coverage

Last updated: 2026-01-29 00:12

## Observed gaps (based on test inventory + coverage)
- No cart API tests found (`rg "Cart" tests` returned 0 matches).
- No explicit tests for `/api/cart` CRUD or cart guest credentials.
- No tests found for `/api/messages` endpoints, notification endpoints, or push subscription endpoints.
- Limited coverage for checkout and transaction action controllers beyond wallet topup flow.
- Low overall coverage (3.01% lines) indicates most code paths are untested.

## High-priority test additions
- Cart API (add/update/remove/clear), guest flow, and summary totals.
- Order checkout & payment flows (wallet, cashfree, razorpay) including failure paths.
- Transaction action routes (validate/failed) with signature verification.
- Profile updates with email/mobile verification flows.
- Notifications & messaging endpoints.
- Helpdesk API CRUD + attachments.
- Subscription lifecycle API + auto-renew.

