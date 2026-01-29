# Payment flows (current apiserver snapshot)

## Common payment architecture
- Models use HasTransaction trait to create Transaction and initiate gateway payments.
- PaymentService is provider-agnostic; Cashfree is default.
- Checkout UI uses CheckoutController with cashfree payment_session_id.
- Confirmation via Cashfree webhooks OR CheckoutController verify/status (Cashfree API).
- On success: PaymentCompleted event -> HandlePaymentCompleted listener routes to domain handler.

Key files:
- apiserver/app/Traits/HasTransaction.php
- apiserver/app/Services/IntegrationServices/Payment/PaymentService.php
- apiserver/app/Http/Controllers/Api/CheckoutController.php
- apiserver/app/Http/Controllers/Api/Webhooks/CashfreeWebhookController.php
- apiserver/app/Listeners/Payment/HandlePaymentCompleted.php

---

## Wallet TopUp
Initiate:
- API: POST /api/wallet/topup
- Controller: apiserver/app/Http/Controllers/Api/WalletController.php
- Flow: validate amount -> Wallet::createCreditTransaction(...) -> checkout_url

Complete:
- Webhook/verify -> PaymentCompleted -> HandlePaymentCompleted::handleWalletTopup
- Wallet balance increases by transaction amount

---

## Subscription payment
Initiate:
- API: POST /api/subscription/subscribe
- Controller: apiserver/app/Http/Controllers/Api/SubscriptionController.php
- Flow:
  - Wallet: direct wallet debit + activateSubscription
  - Online: UserSubscription->createDebitTransaction(...) -> checkout_url

Complete (online):
- Webhook/verify -> PaymentCompleted -> handleSubscriptionPayment
- SubscriptionService->activateSubscription (membership + affiliate)
- If parent_id exists -> affiliate placement

---

## Recruitment / Job application fee
Initiate:
- API: POST /api/my-applications/{uuid}/pay
- Controller: apiserver/app/Http/Controllers/Api/Career/JobApplicationPaymentController.php
- Flow:
  - Wallet: debit + mark application submitted
  - Online: JobApplication->createDebitTransaction(...) -> checkout_url

Complete (online):
- Webhook/verify -> PaymentCompleted -> handleRecruitmentPayment
- Application status -> Submitted + is_paid

---

## Order payment
Initiate:
- API: POST /api/cart/checkout
- Controller: apiserver/app/Http/Controllers/Api/Order/OrderActionController.php
- Flow:
  - Cart validate -> create Order + OrderItems
  - Wallet: wallet debit -> order CONFIRMED
  - Online: Order->createDebitTransaction(...) -> checkout_url

Complete (online):
- Webhook/verify -> PaymentCompleted -> handleOrderConfirmation
- OrderValidationService confirms order, creates shipments/invoices

Related:
- apiserver/app/Services/Ecommerce/OrderService.php (return window + commissions at COMPLETED)
- apiserver/app/Services/Ecommerce/OrderService/OrderValidationService.php
