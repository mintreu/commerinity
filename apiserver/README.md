# Commerinity API Server

Laravel 12 backend for the Commerinity platform.

This service powers authentication, onboarding, e-commerce, subscriptions, recruitment, wallet, notifications, and Filament operations panels.

## Stack

- PHP 8.2+
- Laravel 12
- Filament 4
- Laravel Sanctum
- MySQL
- Pest / PHPUnit

## Key Capabilities

- Password + OTP authentication flows
- User onboarding with address and geo hierarchy
- Product/cart/order/payment/invoice APIs
- Subscription activation and lifecycle handling
- Job application and payment-linked workflows
- Wallet, beneficiary, KYC, and transaction services
- Multi-channel notifications (database, email, SMS, push)
- Admin operations via Filament resources and widgets

## Local Setup

```bash
cd apiserver
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

API base URL: `http://localhost:8000`

## Environment Essentials

Set these first in `apiserver/.env`:

- `APP_URL=http://localhost:8000`
- `APP_CLIENT_URL=http://localhost:3000`
- `SANCTUM_STATEFUL_DOMAINS=localhost:3000`
- `DB_*` credentials
- `QUEUE_CONNECTION=database` (or your preferred driver)
- `OTP_TTL_MINUTES=15` (minimum enforced at runtime: 5)

For real delivery in non-local environments:
- Configure `MAIL_*` for email OTP and transactional emails
- Configure SMS provider credentials
- Configure push/webpush credentials if used

## Queue Worker

Required for queued jobs and notifications:

```bash
cd apiserver
php artisan queue:listen --tries=1
```

## Filament Panels

- Admin panel: `http://localhost:8000/admin`
- App panel: `http://localhost:8000/`

Provider files:
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Providers/Filament/AppPanelProvider.php`

## Demo Users

Seeded via `database/seeders/DemoUserSeeder.php`.

Password for seeded accounts: `password`

- `regular@demo.com` / `9876543210`
- `member@demo.com` / `9876543211`
- `promoter@demo.com` / `9876543212`
- `advisor@demo.com` / `9876543213`
- `mentor@demo.com` / `9876543214`

## Development Commands

```bash
# tests
php artisan test

# formatter
vendor/bin/pint

# clear caches
php artisan optimize:clear
```

## Release Checklist

- Run tests and Pint
- Verify queue worker is running
- Validate payment webhook config and secrets
- Validate email/SMS delivery channels
- Check failed jobs, logs, and notification delivery paths

## License

Proprietary. All rights reserved.
