# Commerinity Platform

Digital commerce, membership, and growth infrastructure built as a product-grade full-stack system.

Commerinity combines e-commerce, subscription, recruitment, wallet, and affiliate/MLM operations in one platform, with a Laravel API backend, Filament control panels, and a Nuxt customer application.

---

## Why Commerinity

- Product-first architecture for digital commerce and network-led growth
- Unified user lifecycle: onboarding, verification, purchase, subscription, and retention
- Built-in operational back office through Filament admin resources
- Multi-channel communication support: in-app, email, SMS, and push (config-driven)
- Structured for extension: modules, resources, relationship pages, and analytics widgets

## Product Scope

### Customer Experience (`client`)
- Authentication (password + OTP flows)
- Onboarding with address hierarchy and location support
- Product browsing, cart, checkout, orders, and invoices
- Subscription purchase and account lifecycle flows
- Job application workflows with payment-linked steps
- PWA-ready frontend delivery

### Platform Core (`apiserver`)
- Laravel 12 API domain services
- Filament 4 admin/app panels for operations
- Wallet, beneficiary, KYC, and transaction modules
- Membership levels/stages and affiliate hierarchy support
- Notifications pipeline (database/email/SMS/push based on configuration)
- Queue-driven async processing

## Architecture

```text
commerinity/
|- apiserver/   Laravel 12 API + Filament panels + background jobs
|- client/      Nuxt 4 SPA/PWA customer app
|- docs/        Product and technical documentation
|- plans/       Execution and implementation planning notes
|- issues/      Internal tracking notes
```

## Technology Stack

### Backend
- PHP 8.2+
- Laravel 12
- Filament 4
- Laravel Sanctum
- MySQL
- Pest / PHPUnit

### Frontend
- Node.js 20+ (recommended)
- Nuxt 4
- Nuxt UI
- TypeScript
- Vitest / Playwright

## Local Development Setup

### 1) Backend

```bash
cd apiserver
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Backend URL: `http://localhost:8000`

### 2) Frontend

```bash
cd client
npm install
npm run dev
```

Frontend URL: `http://localhost:3000`

### 3) Queue Worker (required for async notifications/jobs)

```bash
cd apiserver
php artisan queue:listen --tries=1
```

## Filament Panels

- Admin panel: `http://localhost:8000/admin`
- App panel: `http://localhost:8000/`

Provider files:
- `apiserver/app/Providers/Filament/AdminPanelProvider.php`
- `apiserver/app/Providers/Filament/AppPanelProvider.php`

## Environment Configuration

### Backend (`apiserver/.env`)
Minimum local values:
- `APP_CLIENT_URL=http://localhost:3000`
- `SANCTUM_STATEFUL_DOMAINS=localhost:3000`
- `OTP_TTL_MINUTES=15` (runtime minimum enforced: 5)

Production-critical:
- Mail transport (`MAIL_MAILER`, SMTP/API credentials) for real OTP and transactional email
- SMS provider credentials before enabling production SMS
- Queue/storage/cache drivers aligned to deployment infrastructure

### Frontend (`client`)
Primary runtime variables:
- `NUXT_PUBLIC_API_BASE`
- `NUXT_PUBLIC_SITE_URL`
- `NUXT_PUBLIC_ENABLE_PWA`

## Demo Accounts

Seeder: `apiserver/database/seeders/DemoUserSeeder.php`

Default password: `password`

| Role | Mobile | Email |
|---|---|---|
| Regular | `9876543210` | `regular@demo.com` |
| Member | `9876543211` | `member@demo.com` |
| Promoter | `9876543212` | `promoter@demo.com` |
| Advisor | `9876543213` | `advisor@demo.com` |
| Mentor | `9876543214` | `mentor@demo.com` |

## Engineering Commands

### Backend

```bash
cd apiserver
php artisan test
vendor/bin/pint
php artisan optimize:clear
```

### Frontend

```bash
cd client
npm run lint
npm run typecheck
npm run test
npm run build
```

## Deployment Readiness Checklist

- Configure production domains and env values for both apps
- Use real mail provider for OTP and transactional delivery
- Run queue workers under process supervision
- Validate payment gateway keys and webhook endpoints
- Verify PWA assets (`site.webmanifest`, icons, service worker)
- Execute lint, typecheck, tests, and build before release

## Security and Operations Notes

- OTP expiry is environment-configurable and guarded by minimum TTL enforcement
- Auth token flows use Sanctum-based API authentication
- Notification delivery depends on channel-level service credentials and queue health
- Payment and transaction workflows should be monitored with logs and failed job handling

## License

Proprietary. All rights reserved.
