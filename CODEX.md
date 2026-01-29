# CODEX.md

Commerinity workspace bootstrap for new sessions.

## Goal
Build a production-grade ecommerce + affiliate/MLM system with best-in-class reliability and full end-to-end test coverage. Target: top-tier quality (performance, correctness, security, UX).

## Repo structure (key roots)
- apiserver/  -> Laravel backend (API, Filament admin, payment, MLM).
- client/     -> Nuxt frontend (public site + user dashboards).
- old_project/ -> legacy reference implementation (feature parity source).
- .codex/knowledge/ -> session memory and design notes.
- .codex/todo.md -> master task list (must complete fully).

## System rules (must follow)
- Pricing must be single-source-of-truth from ProductStock (not Product). Same pricing everywhere (API, cart, order).
- Guest vs auth pricing:
  - Guest: FIFO (no location).
  - Auth w/ address: nearest warehouse by pincode (lat/lng later).
- Sales/discounts must apply consistently (product list/detail/cart/order).
- Sales targeting:
  - NULL target = global/guest/common sale.
  - Auth user: targeted + global fallback.
- MLM commissions:
  - Only Member + Promoter eligible.
  - Active subscription required for BV/PV/Rewards; otherwise value goes to company fund/unclaimed.
  - Advisor/Mentor are staff; advisor income based on team heads they originate (not MLM tree commission).
- Commissions on orders only after COMPLETED (post return window).

## Payment flows (current)
See: .codex/knowledge/payment-flows.md

## Sales + voucher validation parity
Old reference logic: old_project/ (CartSaleValidator + CartVoucherValidator)
Current apiserver is missing parts. Must port fully.

## Tests
- Strengthen all tests; add E2E coverage for:
  - pricing/sale/voucher/cart/order
  - payment (wallet/subscription/order/recruitment)
  - commission processing timing
  - guest vs auth pricing
- Keep tests stable and deterministic.

## Client (Nuxt) gaps (must complete)
See .codex/todo.md

## Notifications
Admin + user notifications (DB/email/SMS/push/toast) must be complete for all key actions.

## References
- Project references: .codex/knowledge/project-references.md
- Affiliate/MLM notes: .codex/knowledge/affiliate-mlm.md
- Pricing/stock notes: .codex/knowledge/pricing-and-stock.md
- Sales targeting notes: .codex/knowledge/sales-targeting.md
- Sales/voucher validation notes: .codex/knowledge/sales-voucher-validation.md

## Current master checklist
Always start from: .codex/todo.md

## Session startup routine
- Read: CODEX.md, .codex/todo.md, and .codex/knowledge/*.md (especially claude-notes.md).
- Do not mark any issue “fixed” unless user confirms. Track confirmations in .codex/knowledge (details) and reflect completion in .codex/todo.md only after confirmation.
- Do not over-engineer. If anything is unclear or looks inconsistent, ask the user before changing behavior or refactoring. Do not guess.
