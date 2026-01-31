<!--
ISSUE: PRODUCT PRICING REFRESH
Created: 2026-01-30
Author: codex
-->
# Product Pricing Refactor

## Goal
Move the current stock-based `getPrice()` logic into a `deprecated/` module and revert the live ecommerce flow to always rely on `products.price` (with sale adjustments) so pricing matches industry standard storefront implementations and removes the tight coupling between stock entries and price calculation.

## Why
1. **Confusion**: Every API, cart, catalog, and sale path currently inspects `ProductStock` entries to figure out price (batch expiry, warehouse, FEFO, pincode). That means the price the customer sees can differ from the persisted `products.price`, making third-party integrations (sale indexing, SEO meta, shareable links) inconsistent.
2. **Maintenance overhead**: The stock entry price calculation propagates into `PriceCalculationService`, `CartService`, `OrderValidationService`, Filament grids, and multiple resources. This makes it difficult to reason about pricing for future features and replicates stale logic across the codebase.
3. **New direction**: The business now wants `products.price` to be the canonical value and stock entries to serve purely inventory/purchasing/fulfilment needs. The current logic must be preserved (for reference or future inventory service) so we cannot delete it; instead, move it to `deprecated/` with clear documentation.

## Desired state
1. **`Product::getPrice()`** becomes a wrapper over `price` + sale adjustments (sale logic already exists). It should no longer instantiate `PriceCalculationService` or load stocks. Instead, price resolution becomes: `return $this->salePrice ?? $this->price;` (with safe defaults).
2. **`ProductStock` entries** keep their purchase/landing cost/batch data to support FIFO allocations, but their pricing helpers (like `getEffectivePrice()` and `PriceCalculationService::getBestStockForContext`) are deprecated/moved under `deprecated/` (e.g., `deprecated/PriceCalculationService.php`). Nothing should re-introduce those helpers into the main namespace (PSR-4 autoload can still point there via explicit include if needed for background tasks such as the yet-to-be-built distributor microservice).
3. **Cart / Order / Catalog / Sale resources** should always rely on `$product->price` (plus sale adjustments). Allocation between product stocks will remain FIFO-driven but now decoupled from price display; `CartService` and `OrderValidationService` continue using stock allocation logic exactly as before for inventory, but price used to calculate totals should be `product->price` (not `stock->getEffectivePrice()`). Keep the existing stock allocation return data but run totals using product price to ensure the subtotal matches everywhere.
4. **SEO / share / sale indexing** also see `products.price` (with any sale adjustments) to prevent inconsistent meta tags.
5. **`deprecated/` directory**: create `deprecated/ecommerce/StockPricing/PriceCalculationService.php`, `deprecated/ecommerce/StockPricing/ProductStockPriceResolver.php`, etc., to house previous logic. Update `composer.json` or an autoloader to map the namespace (if required) but keep these classes out of the normal `App` namespace. Document that these files are only referenced for reference or for a future Wholesale/Distributor project and should not be called from the main code until the new inventory service is ready. Ensure all references in the current codebase are rewritten to either use `Product::price` or import these deprecated helpers only if absolutely necessary (with `@deprecated` docblocks pointing to the new module).

## Risk & Mitigation
| Risk | Description | Mitigation |
| --- | --- | --- |
| Pricing stale | If we drop stock-based pricing without coordinating migrations, some products may still rely on stock price overrides. | During rollout, seed `products.price` from whichever stock entry previously served as the default and run scripts to populate missing values before the refactor. Document this data migration in the issue. |
| Sales/SEO mismatch | Sale and meta data currently call `getPrice()` and may expect location-specific values. | Update `SaleManager`, `SeoMetaGenerator`, and all resources to rely on `product.price` (with sale adjustments). Add regression tests ensuring `ProductResource` and `CartService` use consistent numbers. |
| Deprecation confusion | Other developers might still import the old service from `App`. | Keep the namespace in `deprecated/` (e.g., `Deprecated\fulfillment`). Export a `deprecated` alias in, say, `DeprecatedStockPricingService` that clearly states it’s only for reference and not part of the customer-facing system. Mark the folder with README. |

## Next tasks for another agent
1. Create the `issues/` doc (this file already). 2. Create `deprecated/ecommerce/StockPricing` with classes that encapsulate the existing `PriceCalculationService` functionality (mirroring the current file). 3. Refactor `Product`, `CartService`, `OrderValidationService`, all API resources, and `SaleManager` to rely on `product->price` + sale adjustments only while the deprecated folder is referenced only by background syncing or legacy admin workflows. 4. Add regression tests verifying the price returned by APIs exactly matches `products.price` (with optional sale overrides) and the cart/order totals use the product price despite FIFO allocation. 5. Once tests pass, remove direct dependencies on the old service from the `App` namespace and keep it isolated under `deprecated/` with documentation pointing to this issue briefing.

## References
- Current `App\\Services\\Ecommerce\\PriceCalculationService` (see `apiserver/app/Services/Ecommerce/PriceCalculationService.php`).  
- `Product::getPrice()` (apiserver/app/Models/Ecommerce/Product.php).  Should be truncated to just `products.price` (plus sale adjustments).  
- Cart/Order flows using `$stock->getEffectivePrice()` (apiserver/app/Services/Ecommerce/CartService/CartService.php, OrderValidationService). Need to ensure they use product price for totals but continue to use stock allocations for inventory deduction.  
- `SaleManager` and API resources referencing `PriceCalculationService`: they should now call `$product->price` (value from DB) and rely on `SaleProduct` to store computed sale prices if needed.
