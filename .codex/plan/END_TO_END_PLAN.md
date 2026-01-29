# End-to-End Completion Plan

Last updated: 2026-01-29 00:12

## Phase 0 — Stabilize & align contracts
1. Fix frontend/back‑end API mismatches (cart verbs + paths).
2. Remove/guard public debug and insecure routes (debug auth flow, checkout routes) or add auth/signature verification.
3. Implement missing critical TODOs in payment and commission flows.

## Phase 1 — Core commerce flows (backend + frontend)
1. Cart API + guest cart strategy (define contract, add tests).
2. Order checkout end‑to‑end (create order, payment initiation, webhook finalize).
3. Shipping and tax calculation integration.
4. Product sale integration.

## Phase 2 — Wallet & payouts hardening
1. Complete wallet credit logic in commissions.
2. Transfer notifications.
3. Payout lifecycle edge cases + reconciliation tasks.

## Phase 3 — Membership, subscriptions, messaging, helpdesk
1. Subscription lifecycle + auto‑renew tests.
2. Messaging endpoints + UI flows.
3. Helpdesk full lifecycle + attachments.

## Phase 4 — Content & career
1. Career listings + applications.
2. CMS/blog pages + admin management.

## Phase 5 — Testing, QA, and release readiness
1. Expand Pest coverage to core flows (cart, checkout, wallet, subscription, messaging, helpdesk).
2. Add frontend tests for auth + onboarding + purchase flow.
3. Restore/verify full test suite execution.

