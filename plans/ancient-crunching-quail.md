# Implementation Plan - Product Pricing Refactor

## Purpose
Decouple product pricing from stock entries. Move the current stock-based pricing calculation to a deprecated module and establish `Product::price` as the single source of truth for product pricing, while maintaining stock logic strictly for inventory allocation.

## Proposed Changes

### Phase 1: Migration & Deprecation
1. **Create Deprecated Module**
   - Create `apiserver/app/Services/Ecommerce/StockPricing/` directory.
   - Move `PriceCalculationService` logic to `apiserver/app/Services/Ecommerce/StockPricing/DeprecatedPriceService.php` (copy the class, rename it, and adjust namespace).
   - Document the deprecated service clearly.

### Phase 2: Core Model Refactor
2. **Refactor Product Model (`apiserver/app/Models/Ecommerce/Product.php`)**
   - Update `getPrice()` to strictly return `$this->price` (with sale adjustments).
   - Remove dependency on `PriceCalculationService` inside `getPrice()`.
   - Ensure sale price calculation (`calculateSalePrice`) uses `$this->price`.
   - Maintain `getFormattedPrice()`, `getSalePrice()`, etc., but pointed to the new logic.

### Phase 3: Service Updates
3. **Update CartService (`apiserver/app/Services/Ecommerce/CartService/CartService.php`)**
   - In `getCartTotal()`:
     - Continue using `allocateStockFifo()` to reserve stock items (batch numbers, locations).
     - **CRITICAL CHANGE**: The `unit_price` in the cart line items should come from `Product::price`, NOT `stock->getEffectivePrice()`.
     - Update calculation logic to use the canonical product price for subtotal.

4. **Update OrderValidationService (`apiserver/app/Services/Ecommerce/OrderService/OrderValidationService.php`)**
   - Ensure order totals and line item prices are validated against `Product::price`.
   - Ensure stock consumption logic (`processOrderItem`) remains unchanged (FIFO allocation is correct for inventory, just not for price).

5. **Update PriceCalculationService (Active)**
   - Clean up `App\Services\Ecommerce\PriceCalculationService`.
   - Remove methods that calculate price from landing cost (move to deprecated).
   - Keep methods relevant to sales or discounts if they are generic.
   - Redirect calls to `DeprecatedPriceService` if absolutely necessary for legacy admin views, but log warnings.

### Phase 4: Data & Resources
6. **Update API Resources**
   - `ProductResource`, `ProductDetailResource`, `CartItemResource`: Ensure they map `price` field directly from the Product model, not calculated from stock.

7. **Filament/Admin Updates**
   - Ensure admin forms (`ProductInfolist`, `ManageStock`) display the canonical `price` from the products table.
   - Allow admins to edit `products.price` directly.

### Phase 5: Verification
8. **Regression Testing**
   - Run the creation regression test (`tests/Feature/Ecommerce/PricingRegressionTest.php`).
   - **Expectation**: The test should initially FAIL (because it expects stock price).
   - **Update Test**: Update the test to expect `Product::price` (5000) instead of stock price (15000).
   - Verify that Cart totals match `Product::price * quantity`.

## Verification Steps
1. Run `php artisan test tests/Feature/Ecommerce/PricingRegressionTest.php` to confirm behavior change.
2. Verify `Product::price` is used in Cart/Order.
3. specific check: Ensure stock consumption (sold_quantity) still works correctly even though price is decoupled.
