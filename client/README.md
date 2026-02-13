# Commerinity Client App

Nuxt 4 frontend for the Commerinity digital product.

This app delivers the customer-facing experience for onboarding, catalog, cart, orders, subscription, wallet, recruitment apply flow, and user dashboards.

## Stack

- Node.js 20+ (recommended)
- Nuxt 4
- Nuxt UI
- TypeScript
- Vitest
- Playwright

## Core Experience

- Authentication UI with password/OTP support
- Address onboarding with geo selection and validation
- Product listing/detail, filters, cart, and checkout flow
- Order status, invoice access, and purchase confirmation UX
- Subscription plans and activation journey
- Wallet views, transfer/transaction views, and earnings pages
- FAQ/help and global search experience
- PWA support (`site.webmanifest` + service worker registration)

## Local Setup

```bash
cd client
npm install
npm run dev
```

App URL: `http://localhost:3000`

## Runtime Configuration

Set environment values in `client/.env` as needed:

- `NUXT_PUBLIC_API_BASE` (e.g. `http://localhost:8000`)
- `NUXT_PUBLIC_SITE_URL` (e.g. `http://localhost:3000`)
- `NUXT_PUBLIC_ENABLE_PWA=true`
- Optional branding/contact/public UI variables from `nuxt.config.ts`

## Quality Commands

```bash
# lint
npm run lint

# type checking
npm run typecheck

# unit/integration tests
npm run test

# production build
npm run build

# local production preview
npm run preview
```

## API Integration Notes

- Sanctum token-mode auth is configured in `nuxt.config.ts`
- Backend API should be reachable from `NUXT_PUBLIC_API_BASE`
- Ensure backend `APP_CLIENT_URL` and `SANCTUM_STATEFUL_DOMAINS` match client host

## PWA Notes

PWA assets and registration are included:
- `public/site.webmanifest`
- `public/sw.js`
- `app/plugins/pwa.client.ts`

After deployment, verify manifest and service worker from browser DevTools.

## Release Checklist

- Run `npm run lint`, `npm run typecheck`, `npm run test`, `npm run build`
- Verify auth/onboarding/cart/subscription/wallet critical paths
- Verify PWA installability and manifest validity
- Verify production API base URL and sitemap routes

## License

Proprietary. All rights reserved.
