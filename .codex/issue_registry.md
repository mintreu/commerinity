# Issue Registry (2026-02-06)

## 1. CommissionTrendService fatal (undefined constant)
- **Symptom:** `GET /api/trends/dashboard` (TrendController::dashboardSummary) crashes with `Undefined constant App\\Casts\\CommissionStatusCast::REVERSAL` as soon as the dashboard tries to fetch comparison stats.
- **Evidence:** laravel log entries at `storage/logs/laravel.log` (e.g. `2026-02-05 18:16:30`, entry IDs 3301596, 3302032, 3302645) point to `app/Services/Trends/CommissionTrendService.php:449-465`.
- **Location:** `app/Services/Trends/CommissionTrendService.php` lines 449-470 (method `getComparisonStats`).
- **Fix guidance:** Replace the non-existent `CommissionStatusCast::REVERSAL` reference with an actual enum case (`CommissionStatusCast::REVERSED` or `CommissionStatusCast::CANCELLED`). Alternatively, add a `REVERSAL` case to `CommissionStatusCast` so that the comparison query resolves.

## 2. Team trend endpoints call non-existent methods
- **Symptom:** `/api/trends/team/growth`, `/levels`, `/activity` all return 500 because TrendController attempts to call missing methods (`TeamTrendService::getTeamGrowth`, `::getLevelDistribution`, `::getTeamActivity`).
- **Evidence:** log entries around `2026-02-05 18:17` (IDs 3301792, 3301840, 3301888) show stack traces rooted in `TrendController.php:231-294`.
- **Location:** `app/Http/Controllers/Api/TrendController.php` lines 231-294 vs. `app/Services/Trends/TeamTrendService.php` (the class only exposes `getDirectReferralTrend`, `getActiveInactiveTrend`, `getTeamByType`, etc.).
- **Fix guidance:** Either add concrete implementations for the controller’s called methods or update the controller to call the existing helpers with matching semantics; at present every team data request is a guaranteed 500.

## 3. WalletController route lacks an implementation
- **Symptom:** Calling `/api/wallet/security-questions` raises `Undefined method getSecurityQuestions` and returns 500, so the wallet setup workflow cannot render the question list.
- **Evidence:** `2026-02-05 18:17:31` log entry (ID 3301936) captures the missing method when `routes/api.php:167` dispatches the request.
- **Location:** `routes/api.php` line 167 registers `WalletController::getSecurityQuestions`, but `WalletController.php` only contains a comment placeholder around line 615 and no action.
- **Fix guidance:** Implement the missing endpoint to return the configured security questions (no answers) or drop the route if it is unused; otherwise every UI call will keep failing.

## 4. Filament still autoloads a deleted SmsProviderResource
- **Symptom:** Admin/Filament routes throw `ErrorException: include(.../app/Filament/Resources/SmsProviders/SmsProviderResource.php): Failed to open stream` and `Class "App\\Filament\\Resources\\SmsProviders\\SmsProviderResource" not found`, preventing the Filament UI from booting.
- **Evidence:** Repeated log entries (`storage/logs/laravel.log` IDs 3301311, 3301356, 3301401, 3301446, 3301507) show the missing file referenced from `vendor/composer/ClassLoader.php:576` and `vendor/filament/filament/routes/web.php:161`.
- **Location:** Composer’s autoload map or Filament resource registry still references `app/Filament/Resources/SmsProviders/SmsProviderResource` even though the namespace no longer exists.
- **Fix guidance:** Remove the deleted resource from Filament’s list (`App\\Providers\\FilamentServiceProvider::getResources()` or `config/filament.php`) and rerun `composer dump-autoload`, or recreate a minimal SmsProviderResource so the class is available again.

## 5. Order/cart totals omit tax, shipping, and commission metadata
- **Symptom:** Orders such as `ORD-PACOEYOFRV` show subtotal ₹200 but zero tax, shipping, discount, BV/PV, etc., even though the product and user qualify for GST/shipping rules and affiliate earnings should log. This suggests cart/order computations bypass the configured tax/shipping calculators and affiliate hooks.
- **Evidence:** Manual checkout flow on `http://localhost:3000/order/c64bc43f-9e3e-4cb6-97e4-a4d988718d65` shows empty tax/shipping lines. Cart view and logs indicate the backend added only base price. There are no `taxable_amount`, `shipping_amount`, or `commission_amount` on the resulting order record.
- **Location candidates:** `apiserver/app/Services/Ecommerce/CartService.php`, `OrderService`, or `OrderTotalCalculator` (if present) – these classes should aggregate product price, sale/voucher discounts, GST slabs, shipping charges, and BV/PV credits before persisting the order. Also check `app/Services/Affiliate/CommissionProcessor` to see if commissions trigger on COMPLETED orders. `app/Http/Controllers/Api/CheckoutController` (if exists) and `app/Models/Ecommerce/Order` relations/attributes may need verification.
- **Fix guidance:** Trace `CartService::calculateTotals` → `OrderService::createFromCart` to ensure tax/shipping calculators run and their results persist in both the `order_items` and `orders` records. Verify GST slabs or `TaxRule` usage around lines where totals are accumulated. Confirm commission factories hook during the same transaction, adding BV & PV fields to the order summary and wallet transactions.
