# Ecommerce, Catalog, Cart, Order

## Purpose
Public discovery + authenticated checkout/order lifecycle.

## Primary Flow
- Catalog routes: `apiserver/routes/api.php:469-481`
- Cart routes: `:499-508`
- Order read routes: `:537-543`
- Order action routes: `:545-549`
- Checkout page routes: `:396-399`

## Core Controllers
- `CatalogController.php:34,58,89,140,245`
- `CartController.php:38,79,120,152,180,211,239`
- `OrderActionController.php:32,184,210`
- `OrderDisplayController.php:33,76,103,124`
- `CheckoutController.php:36,113`

## Frontend
- `client/app/pages/shop/*`
- `client/app/pages/cart.vue`
- `client/app/pages/checkout/[transaction].vue`
- `client/app/pages/orders/index.vue`, `order/[uuid].vue`

## Tests
- `apiserver/tests/Feature/Ecommerce/*`
- `apiserver/tests/Feature/Payment/*`
- `client/tests/e2e/*`

## ? Notes
- Public checkout access requires strict signed-link and expiry controls.

