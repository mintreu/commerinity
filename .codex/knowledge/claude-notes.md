# Claude folder notes (imported to .codex)

Source files reviewed (dates from files):
- .claude/CURRENT_STATUS.md (2026-01-02)
- .claude/PAYMENT_FIX_PLAN.md (2026-01-02)
- .claude/FEATURE_COMPLETENESS_AUDIT.md (2025-12-22)
- .claude/LAUNCH_BLOCKERS.md (2025-12-22)
- .claude/ECOMMERCE_PROGRESS.md (2025-12-27)
- .claude/SUBSCRIPTION_COMPLETE_SUMMARY.md (2025-12-26)

## Payment architecture notes (historical reference)
- Checkout flow intended on Laravel/Livewire side (not Nuxt).
- Transaction has success_url/failure_url and redirects after verification.
- Payment confirmation expected via webhook or verify endpoint, then event dispatch.
- Integration seeder uses Cashfree config; Integration model stored credentials in JSON (encrypted) in CURRENT_STATUS.

Potential discrepancy vs current code:
- .claude references TransactionConfirmed/TransactionFailed events.
- Current apiserver uses PaymentCompleted/PaymentFailed events (verify with code). This needs alignment check.

## Payment fix plan (historical)
- Suggested switch Integration from encrypted JSON credentials -> plain columns (key/secret/webhook). NOTE: This conflicts with current user preference; require user confirmation before changes.
- Suggested ensure wallet balance updates ONLY after confirmation event.
- Suggested add provider_gen_session/link on Transaction.
- Suggested audit PaymentService vs PayoutService similarity.
- Suggested use fingerprint instead of user id in payment context.
- Suggested real sandbox credentials for tests; remove fake passing tests.

## Subscription summary (historical)
- Subscription flow: wallet or gateway, auto-placement, sponsor tracking, commission triggering.
- Uses HasTransaction trait on UserSubscription.
- PaymentCompleted event -> activateSubscription in listener.

## E-commerce / launch status (historical)
- Feature audit & launch blockers list ecommerce + affiliate frontend + checkout UI as critical gaps (dated 2025-12-22).
- Ecommerce progress notes show models/migrations copied and cart/order work in progress (2025-12-27).
- These may be outdated; verify against current refactor.

## Action for new sessions
- Treat these as reference only. Validate against current code and user decisions.
- Do NOT mark items fixed unless user confirms.
