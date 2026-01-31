# Sales + Voucher Validation: Old vs Current (Findings)

## Sales
**Old Project**
- Sale targeting via `sale_targets` with Level (user level-based targeting).
- Auth user: targeted sale + global fallback; Guest: global only.
- Cart applied sale per line with `CartSaleValidator`:
  - Evaluates sale conditions (AND/OR)
  - Honors `end_other_rules`
  - Uses resolved price (tier/stock) for comparisons
- Product APIs load sales for display (includes list of available sales).

**Current Project**
- SaleManager creates `sale_products`, but:
  - Product APIs do **not** select sale products by user context.
  - No cart sale validator; sale not applied in cart/order calculations.
  - Product model sale info is placeholder (`getActiveSaleInfo()` TODO).
  - SaleManager uses product price (not stock-resolved price).

**Missing**
- User-context sale selection (level/user-type/user) + global fallback.
- Cart/order-level sale application with `end_other_rules` + conditions.
- Stock-resolved price inside sale calculations.
- Consistent sale display (list of applicable sales for UI).

## Voucher/Coupon
**Old Project**
- CartVoucherValidator validates:
  - code validity, per-user usage
  - **voucher conditions** (cart/cart_item/product attributes)
  - match_all / match_any logic
- CartLineService applies voucher per line with full meta.
- Voucher targets configurable in config (e.g., Level).

**Current Project**
- Voucher model has conditions/targets but CartService only checks:
  - code validity + usage limits
  - **no condition logic** applied in cart
  - discount = `voucher->calculateDiscount(subtotal)` only
- No item-level voucher validation logic.
- Voucher targets limited to Category/Product in model.
- Redemption/usage increments not wired into order flow (not found).
- `apply_to_shipping` / `free_shipping` not enforced in cart totals.

**Missing**
- Condition-based voucher validation (cart/cart_item/product).
- Item-level discount logic (discount_quantity/step).
- Targeting by Level/UserType/User (config-based as old_project).
- Redemption + usage increment during order placement.
- Apply-to-shipping logic in cart totals.

