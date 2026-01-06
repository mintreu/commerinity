# Next Session Instructions

- Do **not** modify PaymentService, PayoutService, or any integration service classes unless the user explicitly authorizes it.
- Once the user confirms their fixes are complete, run the full transaction system test plan:
  1. Execute all automated tests covering transactions, payments, payouts, wallet top-ups, and SMS notifications.
  2. If any test fails, **only adjust the tests** (e.g., mocks, expectations) to match the corrected behavior. Do not change the underlying services.
  3. After automated tests pass, perform an end-to-end manual verification of the transaction lifecycle (initiate payment → verify → payout → wallet ledger) using existing tooling.
- Record test outcomes and any required follow-up in `.claude/ACTIVITY_LOG.md` before ending the session.
