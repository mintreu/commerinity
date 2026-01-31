# Sales Targeting (Old Project Parity)

## Target = NULL
- If `target_type` and `target_id` are NULL, sale applies to **guest/regular/common** (global sale).

## Old Project Behavior
- Sales could be targeted by **Level** (membership tier).
- Auth user:
  - Load SaleProduct targeted to user level
  - Fallback to global SaleProduct (null target)
- Guest:
  - Only global SaleProduct

## Current Project Gaps
- No sales target config (Level/Stage/UserType/User).
- Product APIs don't apply user-context SaleProduct selection.
- No SaleController endpoint to list sales for guest/auth.

## Target State
- Allow targets: Level, Stage, UserType, User, plus global (null target).
- Sale resolution should be per user context and applied consistently across APIs.

## Files to align
- `apiserver/app/Models/Ecommerce/Sale.php`
- `apiserver/app/Models/Ecommerce/SaleProduct.php`
- `apiserver/app/Services/Ecommerce/SaleManager.php`
- `apiserver/app/Http/Controllers/Api/CatalogController.php`
- Add SaleController (if needed)
