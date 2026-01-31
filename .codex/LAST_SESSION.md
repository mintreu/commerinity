# Last Session

Date: 2026-01-31 05:30

## What was done
- Rebased ecommerce pricing so the storefront strictly reads from `products.price` plus sale adjustments while fulfillment retains warehouse/context helpers from `PriceCalculationService`; legacy stock pricing logic now lives under `.codex/deprecated/ecommerce/StockPricing`.
- Added BV/PV/reward/wholesale/commission metadata to `products` (fillable, casts, migrations, seeders) and updated CartService plus API/resources to aggregate those numbers from the product row instead of per-stock entries.
- Migration check showed “Nothing to migrate”; attempted test runs (`php artisan test`, filtered suite, Pest command) but each hit the 4-minute timeout, so they need rerunning with more time.
