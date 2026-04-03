# 04 Stakeholder Views

## For Developers
- Start with `03-full-system-flow.md`.
- Then use `inventory/*` to navigate exact files.
- Primary backend extension points:
  - Controllers: `apiserver/app/Http/Controllers/Api/*`
  - Services: `apiserver/app/Services/*`
  - Domain models: `apiserver/app/Models/*`

## For QA / Testers
- Backend regression suites: `apiserver/tests/Feature/*`, `Unit/*`.
- Frontend/API/playwright: `client/tests/*`.
- Recommended smoke paths:
  - auth login + user
  - catalog + cart + checkout
  - wallet balance + transactions
  - affiliate stats + commissions
  - career list + application

## For Product / Managers
- Core business engines:
  - Conversion: auth/onboarding
  - Revenue: ecommerce checkout
  - Retention: wallet + rewards + notifications
  - Growth: affiliate + commissions
  - Hiring: recruitment module

## For Designers
- UI entry points are page-first in `client/app/pages/*`.
- Reusable blocks in `client/app/components/*`.
- Cross-cutting UX state in `client/app/composables/*`.

## For Client/Owners
- Public-facing modules: catalog, content, careers, search, ads.
- Logged-in user modules: profile, onboarding, wallet, orders, network.
- Admin control center: Filament resources (moderation, catalog, finance, sms, analytics).

