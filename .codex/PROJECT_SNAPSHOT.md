# Project Snapshot

Last updated: 2026-01-29 00:02

## Repo layout
- Root: `C:\laragon\www\mintreu\server\commerinity`
- `apiserver/`: Laravel 12 API backend.
- `client/`: Nuxt 4 frontend.

## Backend stack (apiserver)
- Laravel 12, PHP 8.4 (per `apiserver/AGENTS.md`)
- Sanctum v4, Livewire v3, Filament v4
- Testing: Pest v4, PHPUnit v12; formatting via Pint

## Frontend stack (client)
- Nuxt 4 + Nuxt UI + Tailwind CSS v4 + TypeScript

## Core domain concepts
- User types: regular, member, promoter, advisor, mentor
- MLM hierarchy: `parent_id` (upline) + adjacency list for tree; originator tracking
- Auth: mobile‑first, email optional; OTP + password login; Sanctum tokens
- Wallet: balance/transactions, PIN + security questions, topups, withdrawals, payouts; amounts in paisa
- E‑commerce: catalog, cart, orders, wishlist, reviews; checkout via transaction UUID
- Other: onboarding + KYC, subscriptions, notifications + push, helpdesk tickets, recruitment/careers, trends/analytics, ads, FAQ

## Key model notes
- `App\Models\User`
  - UUID auto‑generated: `REG{year}{12-random}` if missing
  - Referral code auto‑generated: 8 uppercase chars, unique
  - Casts: gender, type, status; `dob` date; `password` hashed
  - Relationships: `parent()`, `children()`, `genealogy()`, originator morphs

## Key API routes (from `apiserver/routes/api.php`)
- Health: `GET /api/health`
- Auth: `/api/auth/send-otp`, `/api/auth/verify-otp`, `/api/auth/register`, `/api/auth/login`, password reset, logout
- User profile: `/api/user`, `/api/user/profile`, `/api/user/avatar`, `/api/user/password`
- Wallet: `/api/wallet/*` (balance, stats, transactions, PIN, topup, send, withdraw, pay); beneficiaries under `/api/wallet/beneficiaries/*`
- Payouts: `/api/payouts/*` (to‑wallet, cashgram, balance)
- Catalog: `/api/catalog/*` + product reviews
- Cart: `/api/cart/*` (auth required)
- Orders: `/api/orders/*` (auth required); `/api/order/checkout`
- Checkout (public): `/api/checkout/{transaction:uuid}` + status
- Notifications: `/api/notifications/*`; push: `/api/push/*`
- Helpdesk: `/api/helpdesk/*` (auth required)
- Careers: `/api/careers/*` (public) + apply/check (auth required)
- FAQ: `/api/faq/*` (public)
- Webhooks: `/api/webhooks/cashfree`, `/api/webhooks/razorpay`

## Money + payments
- Monetary values in paisa (1 INR = 100 paisa), format via MoneyService
- Cashfree used for wallet topup and payouts; webhooks update transaction status
