# Migration Cleanup Checklist

This checklist tracks each table whose migration history needs to be consolidated and models aligned. Mark the box **only after**:

1. The final schema from any add/remove migrations is merged into the original `create_*` migration.
2. Any redundant migration files that no longer alter the schema are deleted.
3. Models, factories, and seeders reflect the final columns.
4. `php artisan migrate:fresh --seed` (or `php artisan app:reset` + seed) runs cleanly.

- [x] **Products / ProductStocks** — Pricing/BV/PV/commission moved into `create_products_table`, product stock now holds only purchase/inventory metadata, redundant migrations removed, models/forms updated, fresh migrate+seed verified.
- [x] **Orders / OrderItems** — Affiliate totals (`total_bv`, `total_pv`, `total_reward_points`, `commission_processed`) plus completion timestamps merged into `create_orders_table`, order item UUID/stock/affiliate columns added directly to `create_order_items_table`, redundant migrations removed, models aligned, base migrations refreshed.
- [ ] **Next table** — replace this placeholder with the actual table name once you begin cleaning it up.
