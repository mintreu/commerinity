# Activity Log

## 2026-01-29 00:02
- Added .codex documentation files (snapshot, last session, activity log, assumptions, questions, sources).
- Captured system overview based on local files.
## 2026-01-29 00:12
- Ran `php artisan test` with extended timeout; suite timed out after 20 minutes.
- Listed tests (1077) and reviewed coverage snapshot.
- Reviewed docs/plans and noted frontend checklist gaps.
- Captured risks, missing tests, architectural breaks, and end-to-end plan in `.codex/plan/*`.
## 2026-01-31 05:15
- Reworked ecommerce pricing to read from the canonical `products.price` column (plus sale overrides) everywhere, moved stock-heuristics to `.codex/deprecated`, and kept fulfillment context helpers in `PriceCalculationService`.
- Added BV/PV/reward/wholesale metadata and commissions fields to `products` (fillable/casts/migrations/seeders) so API resources, carts, and customers pull metadata from the product row, not the stock entries.
- Ran `php artisan migrate` (no pending migrations) and attempted the test suite (`php artisan test`, filtered suite, Pest file) but every run hit the 4‑minute timeout; need a longer execution window next time.
