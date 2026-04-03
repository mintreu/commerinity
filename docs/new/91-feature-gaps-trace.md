# 91 Feature Gaps / Absurd or Incomplete Logic (Traceable)

This list includes code locations that appear incomplete, risky, or inconsistent with production-grade behavior.

## Critical

1. Checkout endpoint publicly exposed without clear signed-access policy
- Trace: `apiserver/routes/api.php:394`, `:396-399`
- Why gap: code comment itself flags insecure posture.
- Risk: transaction UUID leakage or brute-force probing.
- Suggested fix: signed URLs + strict expiry + rate-limit + optional token binding.

2. Transaction action endpoint missing signature verification
- Trace: `apiserver/app/Http/Controllers/Api/Transaction/TransactionActionController.php:31`
- Why gap: explicit TODO for signature verification.
- Risk: spoofed validate/failure callbacks.
- Suggested fix: HMAC/signature middleware + replay protection.

3. Commission processing wallet credit not fully implemented
- Trace: `apiserver/app/Jobs/Affiliate/ProcessCommissionJob.php:191`
- Why gap: TODO indicates final credit integration pending.
- Risk: calculated commissions may not settle to wallet consistently.
- Suggested fix: atomic settlement service + idempotency key.

## High

4. Sale integration incomplete in product/sale pipeline
- Trace: `apiserver/app/Models/Ecommerce/Product.php:541`
- Trace: `apiserver/app/Http/Controllers/Api/CatalogController.php:153`
- Why gap: TODO indicates sale relation is partial/placeholder.
- Risk: wrong pricing/promo visibility.
- Suggested fix: finalize active sale relation + cache invalidation tests.

5. Beneficiary provider-side delete flow incomplete
- Trace: `apiserver/app/Http/Controllers/Api/BeneficiaryAccountController.php:242`
- Why gap: provider API delete not implemented.
- Risk: local delete succeeds but remote provider state remains stale.
- Suggested fix: sync deletion via provider adapter + failure compensation.

6. Password reset email production path incomplete
- Trace: `apiserver/app/Http/Controllers/Api/Auth/PasswordResetController.php:59`
- Why gap: TODO for sending actual email reset link.
- Risk: production reset journey can degrade if not enabled.
- Suggested fix: queue-based mail notification + telemetry.

## Medium

7. Profile contact change verification actions pending
- Trace: `apiserver/app/Http/Controllers/Api/ProfileController.php:80`, `:86`
- Why gap: TODO for email/mobile verification flows.
- Risk: account takeover vector if change takes effect without re-verification.
- Suggested fix: staged pending_contact table + verify-confirm flow.

8. Advertisement targeting incomplete for user type filtering
- Trace: `apiserver/app/Models/Advertisement.php:391`
- Why gap: TODO for `target_user_types` enforcement.
- Risk: ads shown to wrong cohort, lower conversion/compliance issues.
- Suggested fix: apply user-type predicate at query scope level.

9. Wallet transfer notification not implemented
- Trace: `apiserver/app/Services/UserServices/UserWalletService.php:576`
- Why gap: sender-side notification pending.
- Risk: weak user trust/audit trail UX.
- Suggested fix: domain event + notification listener.

10. Debug auth endpoint present in public API
- Trace: `apiserver/routes/api.php:59`
- Why gap: debug route in non-dev API surface.
- Risk: unintended behavior exposure.
- Suggested fix: guard by environment or remove from production routes.

## Low / Cleanup

11. OTP login endpoint expectation mismatch in tests
- Trace: `apiserver/tests/Feature/Auth/LoginTest.php:104`
- Why gap: test says OTP login endpoints TODO.
- Suggested fix: either implement OTP login endpoint or update test scope.

12. UserResource tests still asserting TODO placeholders
- Trace: `apiserver/tests/Unit/Resources/UserResourceTest.php:254`, `:262`
- Why gap: behavior likely placeholder (`hasLevel`, `level_id`).
- Suggested fix: replace with finalized role/lifecycle logic assertions.

13. Filament generated TODO stubs left in code
- Trace: `apiserver/app/Filament/Resources/Ecommerce/Products/Pages/ViewProduct.php:30`
- Trace: `apiserver/app/Filament/Resources/Ecommerce/Orders/Pages/CreateOrder.php:20`
- Trace: `apiserver/app/Filament/Resources/Ecommerce/Categories/Pages/ViewCategory.php:23`
- Why gap: low risk but indicates unfinished cleanup.

14. Backup copy page left in frontend pages
- Trace: `client/app/pages/shop/[slug].vue.bak`
- Why gap: stale duplicate file in source tree.
- Risk: confusion during maintenance/review.

