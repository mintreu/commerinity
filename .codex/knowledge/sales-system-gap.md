# Sales System Knowledge (old_project vs current)

## What old_project had
- Sales targets configured in laravel-commerinity config: `sales.targets` included `Level::class` (user level-based targeting).
- Sale selection logic in product APIs:
  - Auth user: load sale_products targeted to user level + fallback to global (null target).
  - Guest: only global sale_products (null target).

## What is missing now
- No sales target config (Level/Stage/UserType/User).
- No user-context sale selection in product listing/detail APIs.

## Implication
Sales must be applied based on user context (level/type/etc.) with global fallback; currently not implemented.
