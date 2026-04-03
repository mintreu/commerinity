# 01 Architecture

## High-Level
- Backend: Laravel API + Filament admin (`apiserver`)
- Frontend: Nuxt/Vue shop + member portal (`client`)
- Auth: Sanctum token auth
- Data: relational DB + media files (Spatie media-style usage)
- Payments: Cashfree + Razorpay + native fallback abstractions
- Messaging/Comms: notifications + SMS + web push

## Runtime Layers
- Controllers: `app/Http/Controllers/Api/*`
- Business services: `app/Services/*`
- Domain models: `app/Models/*`
- Admin operations: `app/Filament/*`
- Async/Jobs/Listeners: `app/Jobs`, `app/Listeners`, `app/Events`

## Route Surface
- Main API: `apiserver/routes/api.php`
- Console/scheduler: `apiserver/routes/console.php`
- Auth, wallet, affiliate, catalog, cart, orders, career, notifications, webhooks are all routed from `api.php`

## Frontend Structure
- Pages: `client/app/pages/*`
- Shared state/composable logic: `client/app/composables/*`
- Domain components: `client/app/components/*`
- API and flow tests: `client/tests/*`

## Business Intent (System Level)
- Public traffic: content + catalog + search + ads
- Registered users: onboarding, KYC, wallet, orders, commissions, team/network
- Subscribers/members: additional messaging, affiliate/commission features, lifecycle modules
- Admin: manages catalog, users, KYC, transactions, SMS templates/logs, advertisements, recruitment, backup/restore

