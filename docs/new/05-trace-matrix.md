# 05 Trace Matrix (Module -> Files -> Lines)

## Auth
- Routes: `apiserver/routes/api.php:66-87`
- Controllers: `.../Auth/OtpController.php:30,57`; `RegisterController.php:34,109`; `LoginController.php:24,77`; `PasswordResetController.php:33,121`
- Frontend: `client/app/pages/auth/*`
- Tests: `apiserver/tests/Feature/Auth/*`, `client/tests/api/auth.test.ts`

## Onboarding/Profile/KYC/Address
- Routes: `apiserver/routes/api.php:100-122`
- Controllers: `OnboardingController.php:31,42,136`; `ProfileController.php`; `AddressController.php:20,35,71,93,112`; `KycController.php:20,38,60`
- Frontend: `client/app/pages/onboarding/index.vue`, `profile/*`, `addresses/index.vue`
- Tests: `OnboardingFlowTest`, `AddressApiTest`, `KycApiTest`

## Catalog/Cart/Order/Checkout
- Routes: `api.php:396-399`, `469-481`, `499-508`, `537-549`
- Controllers: `CatalogController.php:34,58,89,140,245`; `CartController.php:38,79,120,152,180,211`; `OrderActionController.php:32`; `OrderDisplayController.php:33,76,103,124`; `CheckoutController.php:36,113`
- Frontend: `shop/*`, `cart.vue`, `checkout/[transaction].vue`, `orders/index.vue`, `order/[uuid].vue`
- Tests: `apiserver/tests/Feature/Ecommerce/*`, `Payment/*`, `client/tests/e2e/*`

## Wallet/Payout
- Routes: `api.php:174-214`, `219-229`
- Controller: `WalletController.php:38,56,133,162,192,244,302,357,446,545,763`
- Frontend: `client/app/pages/wallet/*`, `useWallet.ts`
- Tests: `WalletTest.php`, `WalletTopupCheckoutFlowTest.php`, `client/tests/api/wallet.test.ts`

## Affiliate/Commission
- Routes: `api.php:146-157`, `163-169`
- Controllers: `AccountController.php:79,90,103,121`; `CommissionController.php:29,87,163,200`; `Api/Affiliate/*`
- Frontend: `client/app/pages/affiliate/*`, `network/*`
- Tests: `apiserver/tests/Feature/Affiliate/*`, `client/tests/api/affiliate.test.ts`

## Recruitment/Career
- Routes: `api.php:234-242`, `378-382`
- Controller: `RecruitmentController.php:37,53,73,89,136,169,196`
- Frontend: `client/app/pages/career/*`
- Tests: `apiserver/tests/Feature/Api/RecruitmentTest.php`, `client/tests/career.api.test.ts`

## Notification/Push/SMS/Messages
- Routes: `api.php:126-138`, `300-310`, `343-348`
- Controllers: `NotificationController.php:18,118,133,158,171`; `PushSubscriptionController.php:16,42,62`; `MessageController.php`
- Services: `SmsService.php:30,42,205`; `Fast2SmsProvider.php:30,34`
- Frontend: `notifications.vue`, `messages/*`
- Tests: notifications + sms feature tests

## Content/Search/FAQ/Helpdesk
- Routes: `api.php:425-431`, `436`, `441-453`, `458-464`, `387-390`
- Frontend: `faq.vue`, `blogs/*`, `news/*`, `search.vue`, `helpdesk/*`, `contact.vue`
- Tests: global search/helpdesk/content e2e

## Advertisement
- Routes: `api.php:486-494`
- Controller: `AdvertisementController.php:34,79,123,204`
- Frontend: `useAdvertisements.ts`, `components/ads/*`

## Admin Filament
- Full trace: `docs/new/inventory/backend-filament.txt`

