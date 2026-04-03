# 03 Full System Flow (Traceable)

This document explains the operational flow of the project with route/controller/frontend/test trace points.

---

## Flow A: Authentication + Session + User Identity

### A1. OTP and auth entry
1. OTP send endpoint: `apiserver/routes/api.php:66`
2. OTP verify endpoint: `apiserver/routes/api.php:67`
3. Register endpoint: `apiserver/routes/api.php:70`
4. Login endpoint: `apiserver/routes/api.php:74`
5. Forgot/reset password: `apiserver/routes/api.php:79`, `:82`

### A2. Backend methods
- OTP send/verify: `apiserver/app/Http/Controllers/Api/Auth/OtpController.php:30`, `:57`
- Register: `.../RegisterController.php:34`, email variant `:109`
- Login (web/mobile): `.../LoginController.php:24`, `:77`
- Password reset: `.../PasswordResetController.php:33`, `:121`

### A3. Frontend touchpoints
- Login page: `client/app/pages/auth/login.vue`
- Register page: `client/app/pages/auth/register.vue`
- Forgot/reset pages: `client/app/pages/auth/forgot-password.vue`, `reset-password.vue`

### A4. Tests
- Backend auth tests: `apiserver/tests/Feature/Auth/*`
- Frontend/API: `client/tests/api/auth.test.ts`, `client/tests/auth.api.test.ts`

---

## Flow B: Onboarding + Profile + Address + KYC

### B1. Route layer
- Onboarding status/update/complete: `apiserver/routes/api.php:105-108`
- Address CRUD + default: `apiserver/routes/api.php:111-117`
- KYC status/submit/resubmit: `apiserver/routes/api.php:119-121`

### B2. Controller logic
- Onboarding: `apiserver/app/Http/Controllers/Api/OnboardingController.php:31`, `:42`, `:136`
- Address: `.../AddressController.php:20`, `:35`, `:71`, `:93`, `:112`
- KYC: `.../KycController.php:20`, `:38`, `:60`

### B3. Frontend pages
- Onboarding: `client/app/pages/onboarding/index.vue`
- Profile: `client/app/pages/profile/*`
- Addresses: `client/app/pages/addresses/index.vue`
- KYC UI: `client/app/pages/profile/kyc.vue`
- Geo lookup composable: `client/app/composables/useGeoData.ts`

### B4. Tests
- `apiserver/tests/Feature/OnboardingFlowTest.php`
- `apiserver/tests/Feature/AddressApiTest.php`
- `apiserver/tests/Feature/KycApiTest.php`
- `client/tests/flows/regular-flow.test.ts`

---

## Flow C: Catalog -> Cart -> Checkout -> Order

### C1. Catalog browsing (public)
- Routes start: `apiserver/routes/api.php:469`
- Controller methods:
  - list: `apiserver/app/Http/Controllers/Api/CatalogController.php:34`
  - detail: `:58`
  - category: `:89`
  - search: `:140`
  - filters: `:245`

### C2. Cart operations (auth)
- Cart routes start: `apiserver/routes/api.php:499`
- Controller methods:
  - index: `apiserver/app/Http/Controllers/Api/CartController.php:38`
  - add: `:79`
  - update: `:120`
  - remove: `:152`
  - clear: `:180`
  - coupon apply/remove: `:211`, `:239`

### C3. Checkout and payment initiation
- Checkout display/status routes: `apiserver/routes/api.php:396-399`
- Checkout controller: `apiserver/app/Http/Controllers/Api/CheckoutController.php:36`, `:113`
- Order action routes: `apiserver/routes/api.php:545-549`
- Order checkout method: `apiserver/app/Http/Controllers/Api/Order/OrderActionController.php:32`

### C4. Order read paths
- Order routes start: `apiserver/routes/api.php:537`
- Order display: `apiserver/app/Http/Controllers/Api/Order/OrderDisplayController.php:33`, `:76`, `:103`, `:124`

### C5. Payment webhooks
- Route group: `apiserver/routes/api.php:413`
- Cashfree webhook endpoint route: `:415-416`
- Razorpay webhook route: `:419`

### C6. Frontend
- Catalog page: `client/app/pages/shop/products.vue`
- Product detail: `client/app/pages/shop/product/[slug].vue`
- Cart: `client/app/pages/cart.vue` (checkout payload + `/api/order/checkout` usage near line 833)
- Checkout page: `client/app/pages/checkout/[transaction].vue` (`/api/checkout/{id}` fetch around line 348)
- Orders list/detail: `client/app/pages/orders/index.vue`, `client/app/pages/order/[uuid].vue`

### C7. Tests
- Backend ecommerce: `apiserver/tests/Feature/Ecommerce/*`
- Payment/checkout/order: `apiserver/tests/Feature/Payment/*`
- Frontend e2e: `client/tests/e2e/shop-products.e2e.test.ts`, `product-detail.e2e.test.ts`

---

## Flow D: Wallet + Beneficiary + Payout

### D1. Route map
- Wallet route group start: `apiserver/routes/api.php:174`
- Payout route group start: `apiserver/routes/api.php:219`

### D2. WalletController methods
- wallet show: `apiserver/app/Http/Controllers/Api/WalletController.php:38`
- transactions: `:56`
- balance: `:133`
- setup pin: `:162`
- request pin otp: `:192`
- change pin: `:244`
- verify pin: `:302`
- send money: `:357`
- withdraw: `:446`
- wallet pay: `:545`
- topup: `:763`

### D3. Frontend
- Wallet dashboard: `client/app/pages/wallet/index.vue`
- Add/send/withdraw/pin/transactions pages: `client/app/pages/wallet/*`
- Composable: `client/app/composables/useWallet.ts`

### D4. Tests
- `apiserver/tests/Feature/WalletTest.php`
- `apiserver/tests/Feature/WalletTopupCheckoutFlowTest.php`
- `client/tests/api/wallet.test.ts`

---

## Flow E: Affiliate + Commission + Subscription Signals

### E1. Routes
- Affiliate group start: `apiserver/routes/api.php:146`
- Commissions group start: `:163`

### E2. Controllers
- Account affiliate tree/stats: `apiserver/app/Http/Controllers/Api/AccountController.php:79`, `:90`, `:103`, `:121`
- Commission summary/list/by-type/monthly: `apiserver/app/Http/Controllers/Api/CommissionController.php:29`, `:87`, `:163`, `:200`
- Affiliate ledger/fund/disbursement controllers under: `apiserver/app/Http/Controllers/Api/Affiliate/*`

### E3. Frontend
- Affiliate pages: `client/app/pages/affiliate/*`
- Network pages: `client/app/pages/network/*`
- Composables: `client/app/composables/useAffiliateFunds.ts`, `useAffiliateLedger.ts`, `useNetwork.ts`

### E4. Tests
- `apiserver/tests/Feature/Affiliate/*`
- `client/tests/api/affiliate.test.ts`
- `client/tests/flows/member-flow.test.ts`

---

## Flow F: Recruitment + Job Application

### F1. Routes
- Public careers list/filters/detail: `apiserver/routes/api.php:378-382`
- Apply route (auth): `apiserver/routes/api.php:234`
- My applications: `:237-242`

### F2. Controller methods
- List/detail/filters/apply/my applications/check/payment:
  `apiserver/app/Http/Controllers/Api/RecruitmentController.php:37`, `:53`, `:73`, `:89`, `:136`, `:169`, `:196`

### F3. Frontend
- Careers list/detail/apply: `client/app/pages/career/*`
- Applications pages: `client/app/pages/career/applications/*`

### F4. Tests
- Backend: `apiserver/tests/Feature/Api/RecruitmentTest.php`
- Imports: `apiserver/tests/Feature/Imports/*`
- Frontend: `client/tests/career.api.test.ts`

---

## Flow G: Notifications + Push + Messages + Activity

### G1. Routes
- Notifications group: `apiserver/routes/api.php:126`
- Push group: `:135`
- Messages group: `:300`
- Activity tracking group: `:343`

### G2. Controllers
- NotificationController: `apiserver/app/Http/Controllers/Api/Notification/NotificationController.php:18`, `:118`, `:133`, `:158`, `:171`
- PushSubscriptionController: `.../PushSubscriptionController.php:16`, `:42`, `:62`

### G3. SMS side
- SMS service core: `apiserver/app/Services/IntegrationServices/Sms/SmsService.php:30`, `:42`, `:205`
- Fast2SMS provider base URL: `apiserver/app/Services/IntegrationServices/Sms/Providers/Fast2SmsProvider.php:30`, `:34`
- Template seed baseline: `apiserver/database/seeders/SmsTemplateSeeder.php:18`

### G4. Frontend
- Notifications page: `client/app/pages/notifications.vue`
- Messages list/detail/compose: `client/app/pages/messages/*`
- Notice/push hooks via composables and app shell integrations.

### G5. Tests
- Backend notification tests under `apiserver/tests/Feature/Notifications/*`
- SMS tests: `apiserver/tests/Feature/Services/SmsServiceTest.php`

---

## Flow H: Content + Search + FAQ + Helpdesk

### H1. Routes
- FAQ group: `apiserver/routes/api.php:425`
- Global search route: `:436`
- Blogs/news groups: `:441`, `:448`
- Helpdesk routes (auth): `:458-464`
- Contact/inquiry routes: `:387-390`

### H2. Frontend
- Pages: `client/app/pages/faq.vue`, `blogs/*`, `news/*`, `search.vue`, `helpdesk/*`, `contact.vue`

### H3. Tests
- `apiserver/tests/Feature/Api/GlobalSearchControllerTest.php`
- `apiserver/tests/Feature/Api/HelpdeskModelTest.php`
- `client/tests/e2e/content-pages.e2e.test.ts`

---

## Flow I: Advertisement Delivery

### I1. API
- Ad route group: `apiserver/routes/api.php:486`
- forPage endpoint: `:490`
- forPlacement endpoint: `:491`
- forBlock endpoint: `:492`
- click record endpoint: `:493`

### I2. Controller behavior
- Placement query: `apiserver/app/Http/Controllers/Api/AdvertisementController.php:34`
- Block query: `:79`
- Page-level aggregation: `:123`
- click logging: `:204`

### I3. Frontend
- composable: `client/app/composables/useAdvertisements.ts`
- ad components: `client/app/components/ads/*`

---

## Flow J: Admin / Filament Operations

### J1. Filament inventory
- Full resource/page list: `docs/new/inventory/backend-filament.txt`

### J2. Business-admin coverage
- User/admin/KYC/address moderation
- Ecommerce operations (products/orders/shipments/sales/vouchers)
- Affiliate trees + commissions
- SMS templates + SMS logs
- Integration setup + activity logs

---

## Traceability Notes
- For complete file-by-file listing: use `docs/new/inventory/*.txt`
- For module narratives: use `docs/new/modules/*.md`

