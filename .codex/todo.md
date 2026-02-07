# TODO (Commerinity)

1. Add sales targeting support (old_project parity) ✅
   - Add sales target config (Level/Stage/UserType/User as needed).
   - Implement user-context sale selection in product list/detail APIs:
     - Auth user: targeted sale_products + global fallback
     - Guest: global sale_products only
   - Ensure SaleProduct relation/resolver exists to fetch active sales per user context.
   - Update tests: product list/detail pricing with user vs guest sale targets.

2. Complete pricing system (single source of truth) *(details and refactor plan captured in `issues/product-pricing-refactor.md`, keep this as a trigger for the next session)* ✅
   - Resolve stock by location (nearest warehouse first; pincode proximity; FIFO for guest).
   - Ensure Product/Cart/Order use identical price resolution + sale application.
   - Display MRP/sale/current price + discount consistently in Nuxt.
   - Make pricing global-ready (India-first, adaptable).

3. Restore full sales + voucher/cart validation logic (old_project parity)
   - Port CartSaleValidator + CartVoucherValidator logic (conditions, match_all/any).
   - Apply `end_other_rules`, item-level discount, discount_quantity/step.
   - Enforce voucher `apply_to_shipping` / `free_shipping` in cart totals.
   - Implement voucher targets (Level/UserType/User) like old_project config.
   - Wire voucher redemption + usage increments on order placement.
   - Add sale/voucher meta for UI display (available + applied).

4. Nuxt public site completion (live priority)
   - Guest/Regular/Member/Promoter/Advisor/Mentor flows.
   - Order view page completion.
   - Wallet flows (withdraw to bank) end-to-end.

5. FAQs
   - Admin FAQ management (backend + admin UI).
   - Public FAQ (Nuxt footer CTA).
   - Personalized FAQ per user (client-side).

6. SMS provider architecture decision
   - Evaluate removing sms_providers table in favor of Integration model (sms type constant + cast).
   - Confirm how sms logs map to provider/integration.

7. E2E order flow tests
   - Product -> order confirm -> return -> refund (multi-warehouse).
   - Make tests stable (Pest + browser). Target: solid coverage.

8. Payout flow completion
   - Review existing payout implementation + Cashfree MCP usage.
   - Finish wallet payout flow + tests.

9. MLM/Advisor rules (live)
   - Ensure MLM commissions apply only to Member + Promoter.
   - Active subscription required for BV/PV/Rewards; otherwise value goes to company fund.
   - Advisor/Mentor are company staff; advisor income uses originator tracking.
   - Prepare for future Distributor type (design placeholder).

10. Commission system fixes (urgent)
   - Order purchase commissions: persist results + align timing with return window (COMPLETED only).
   - Add stage/level context for purchase commissions.
   - Enforce Member/Promoter eligibility for MLM commissions.
   - Handle company fund/unclaimed for inactive subscriptions.
   - Implement advisor monthly team-sales commission logic.

11. Joining commissions
   - Verify subscription confirmation triggers joining commissions correctly (after payment confirm).
   - Ensure sponsor bonus / level commission / originator joining flow persists records.

12. Audit & strengthen tests (all)
   - Review all existing tests (feature/unit) for gaps and flaky assumptions.
   - Add missing E2E coverage for commissions, sales/vouchers, pricing, wallet.
   - Ensure tests cover guest vs auth, location-based stock, and target-based sales.

13. Review seeders
   - Inspect all seeders for consistency with current models (users, products, stocks, sales).
   - Update seed data to match new pricing + commission rules.

14. Client-side missing features (Nuxt)
   - Advisor/mentor flows: onboarding team head, sponsor subscription payment link, KYC/forms.
   - Member -> promoter upgrade flow via own team subscription payment.
   - Wallet withdrawal UI + beneficiary verification flow.
   - Order view page completion + payment status + invoice links.
   - Blog pages + remaining public pages.

15. Notifications (admin + user)
   - Audit all actions that need notifications (order, payment, subscription, recruitment, wallet, commissions).
   - Implement missing DB/email/SMS/push/Toast notifications consistently.
   - Improve templates + delivery status logging.

16. Nuxt product description (Filament rich editor CSS)
    - Identify Filament native rich editor output classes.
    - Add matching CSS in Nuxt (global CSS or Description component).
    - Ensure product description renders with same styling as admin editor output.

17. Dashboard Part 2 (Frontend Detailed Audit + Completion TODO) — IN PROGRESS
    - Define DB schema for appointments, challenges, programs, mentees (fields + relations).
    - Implement migrations + models with proper fillables and relations.
    - Add API controllers + routes:
      - GET/POST /api/appointments
      - GET /api/challenges, /api/challenges/active, /api/challenges/{id}
      - GET/POST /api/programs, GET /api/programs/{id}
      - GET /api/mentees
      - Optional: GET /api/dashboard/{advisor|promoter|mentor} aggregations
    - Wire dashboards to live APIs (advisor/promoter/mentor) with loading + error states.
    - Create missing pages:
      - /appointments, /appointments/new
      - /challenges, /challenges/[id]
      - /programs, /programs/new, /programs/[id]
      - /mentees

18. Account deletion lifecycle (last job)
    - Design safe deletion plan (soft delete + restore + audit retention).
    - Map all user relations (morphs + non-null FKs) and decide cleanup/anonymization.
    - Implement deletion flow + restore OTP + 90-day purge + wallet transfer to company.
    - Preserve orders/transactions/commissions for audit; validate no FK breaks.
    - Add scheduler + tests + admin notifications.

19. Product stock/pricing regression & affiliate calculations (critical)
    - Investigate Pest failures (`PricingRegressionTest`, `ProductBasicTest`, `ProductStockTest`, `ProductStockSelectionTest`, `SalesTargetingTest`) to determine which model fields/relations are miscomputed (enums, generated columns, BV/PV totals).
    - Fix `ProductStock` computations for profit, effective price, billing value, commissionability, and location-based ordering so BV/PV and stock selection behave as expected.
    - Ensure sale targeting logic properly applies user-type and location-based pricing before order creation, then re-run tests until they pass.

20. Order/cart totals & affiliate metadata (urgent follow-up)
    - Trace the order/cart pipeline for tax/shipping/discount/affiliate metadata and log each stage to understand why your manual order shows zero tax/shipping and no BV/PV credits.
    - Add Pest coverage that asserts the cart/order APIs surface the calculated totals (tax, shipping, commission, BV, PV) for both guest and member purchases.
    - Make the MLM commission generator and wallet accumulator depend on the `COMPLETED` order status so wallet/top-up tests reflect real payouts.

21. Fix failing tests (regression)
    - `Tests\Unit\Models\KycTest`: personal/business scopes + field assertions failing.
    - `Tests\Feature\AddressApiTest`: default address, create/update, cross-user update, and mobile validation failures.

