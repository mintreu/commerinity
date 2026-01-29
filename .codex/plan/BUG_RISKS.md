# Potential Bugs / Risks

Last updated: 2026-01-29 00:12

## API / Frontend mismatches
- Cart update uses `PATCH /api/cart/{slug}` in `client/app/composables/useCart.ts`, but backend route expects `PUT /api/cart/{productId}`.
- Cart clear uses `POST /api/cart/clear` in frontend; backend exposes `DELETE /api/cart` (no `/clear`).

## TODOs in critical flows (backend)
- Missing signature verification in `apiserver/app/Http/Controllers/Api/Transaction/TransactionActionController.php`.
- `ProcessCommissionJob` has TODO for wallet credit logic.
- `UserWalletService` TODO for transfer notification.
- CartService shipping calculation TODO.
- Product sale system integration TODO in `Product` + `CatalogController`.
- Profile change email/mobile verification TODOs.
- OTP/Password reset “send actual SMS/email” TODOs for production.

## Security / exposure
- Public checkout route is explicitly marked “bad thing, not secured” in `routes/api.php` comments.
- Public debug route `/debug-auth-flow` is enabled.

