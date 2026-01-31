# Pricing and Stock (Single Source of Truth)

## Goals
- All prices shown anywhere (catalog, product detail, cart, checkout, order) must be consistent.
- Base price is resolved from **ProductStock** (multi-warehouse) using FIFO / location-aware selection.
- Sale/discount is applied **after** base stock price resolution.

## Stock Selection Rules
- **If address is NULL (guest):**
  - Use FIFO (priority/created_at) across available stock.
- **If address is present (auth user):**
  - Current approach: use **pincode proximity** (closest pincode) to pick warehouse.
  - If geo not available, fall back to same-state first, then FIFO.
  - Later improvement: lat/lng distance (more precise), especially when distributor stock is added.
  - Never show Delhi price for Mumbai order when Mumbai/Gujarat stock exists.

## Future Note: Distributor
- Distributor stock should be prioritized **before** company stock:
  1) Distributor stock nearest to user
  2) If distributor cannot fulfill, fallback to company warehouse

## Known Gaps
- Cart list/subtotal still uses Product.price (should use stock-resolved price).
- ProductStock::getEffectivePrice() falls back to Product.price instead of cost+margin.
- SaleManager still uses product price and not stock-resolved price.

## Target State
- Introduce/centralize a `PricingResolver` or service that accepts:
  - Product
  - User context (type/level/etc.)
  - Address/location
  - Quantity
- That service returns:
  - selected stock
  - base price
  - sale price (if applicable)
  - display price

## Files to align
- `apiserver/app/Services/Ecommerce/CartService/CartService.php`
- `apiserver/app/Http/Resources/CartItemResource.php`
- `apiserver/app/Http/Controllers/Api/CartController.php`
- `apiserver/app/Http/Resources/Ecommerce/ProductResource.php`
- `apiserver/app/Http/Resources/Ecommerce/ProductDetailResource.php`
- `apiserver/app/Http/Resources/Ecommerce/VariantResource.php`
- `apiserver/app/Services/Ecommerce/SaleManager.php`
