# Frontend Nuxt App

## Purpose
Single frontend for public commerce + authenticated member experiences.

## Structure
- Pages: `client/app/pages/*`
- Components: `client/app/components/*`
- Composables: `client/app/composables/*`

## Important Flows
- Auth pages: `pages/auth/*`
- Shop/cart/checkout: `pages/shop/*`, `cart.vue`, `checkout/[transaction].vue`
- Wallet: `pages/wallet/*`
- Affiliate/network: `pages/affiliate/*`, `pages/network/*`
- Career/helpdesk/messages/notifications: dedicated page folders

## Tests
- API tests: `client/tests/api/*`
- Flow tests: `client/tests/flows/*`
- Playwright UI tests: `client/tests/playwright/*`
- e2e content/catalog tests: `client/tests/e2e/*`

