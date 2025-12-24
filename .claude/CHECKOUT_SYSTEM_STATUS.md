# Checkout & Payment System Status - Current Project
**Date**: 2025-12-25
**Purpose**: Clear status of what EXISTS vs what needs BUILDING

---

## ✅ **What We ALREADY HAVE (Current Project)**

### **Backend Models & Database**
✅ `Transaction` model (apiserver/app/Models/Transaction.php)
   - Polymorphic `transactionable` (can link to any payable model)
   - Fields: uuid, amount, fee, tax, net_amount, status, type, payment_method
   - Provider tracking: integration_id, provider_order_id, provider_transaction_id
   - Redirect URLs, expiry, verification status
   - Full metadata & provider response storage

✅ `Wallet` model (apiserver/app/Models/Wallet.php)
   - Polymorphic `walletable` (User, Admin, etc.)
   - Balance tracking (in paisa)
   - PIN system
   - Status management

✅ `Integration` model (apiserver/app/Models/Integration.php)
   - Payment provider configuration
   - Types: payment, payout, sms, shipping
   - Encrypted credentials storage
   - Sandbox/production toggle
   - Default provider selection

✅ `UserSubscription` model
   - Already exists for membership management
   - Has amount, stage_id, level_id, is_paid fields

✅ `JobApplication` model
   - Recruitment payment tracking
   - Application fee field exists

### **Events Already Created**
✅ `App\Events\PaymentCompleted`
✅ `App\Events\PaymentFailed`
✅ `App\Events\PayoutCompleted`
✅ `App\Events\PayoutFailed`
✅ `App\Events\RefundProcessed`

### **Webhooks Complete**
✅ `CashfreeWebhookController` (447 lines, production-ready!)
   - Signature verification
   - Payment success/failed/dropped handling
   - Payout success/failed/reversed handling
   - Refund processing
   - Event dispatching

✅ `RazorpayWebhookController`
   - Similar implementation for Razorpay

✅ **Routes registered**:
   - POST `/api/webhooks/cashfree`
   - POST `/api/webhooks/cashfree/payout`
   - POST `/api/webhooks/razorpay`

### **Services Exist**
✅ `MoneyService` - Paisa/Rupee conversions (81 tests passing!)

### **Controllers Exist**
✅ `WalletController` - Basic wallet operations
✅ `SubscriptionController` - Subscription management (needs payment integration)

---

## ⏳ **What We NEED TO BUILD**

### **Phase 1: Core Payment Infrastructure** (2 days)

#### 1. Traits
❌ `app/Traits/HasTransaction.php`
```php
// Makes any model payable with one line
use HasTransaction;

// Methods:
- createDebitTransaction() - For payments
- createCreditTransaction() - For refunds/topups
```

#### 2. Services
❌ `app/Services/Payment/CashfreeService.php`
```php
// Simplified Cashfree API wrapper
- createOrder() - Create payment order
- verifyPayment() - Verify payment status
- getOrderStatus() - Fetch order details
```

❌ `app/Services/Payment/CheckoutService.php`
```php
// Unified checkout logic
- initiatePayment() - Create transaction + provider order
- getCheckoutData() - Prepare checkout page data
- handleCallback() - Process success/failure redirects
```

#### 3. Event Listeners
❌ `app/Listeners/Payment/HandlePaymentCompleted.php`
```php
// Route confirmed payments to appropriate handlers
- Wallet TopUp → Update balance
- Subscription → Activate membership
- JobApplication → Submit application
- Order → Confirm order (when e-commerce built)
```

❌ `app/Listeners/Payment/HandlePaymentFailed.php`
```php
// Handle failed payments
- Notify user
- Restore inventory (for orders)
- Log failure reason
```

#### 4. Controllers
❌ `app/Http/Controllers/Api/CheckoutController.php`
```php
// API endpoints:
- GET /api/checkout/{transaction} - Get checkout data
- POST /api/checkout/initiate - Create payment
- GET /api/checkout/{transaction}/status - Check payment status
```

❌ Update `app/Http/Controllers/Api/WalletController.php`
```php
// Add methods:
- POST /api/wallet/topup - Initiate wallet add money
```

❌ Update `app/Http/Controllers/Api/SubscriptionController.php`
```php
// Add methods:
- POST /api/subscription/checkout - Initiate subscription payment
```

---

### **Phase 2: Frontend Checkout** (2 days)

#### 1. Checkout Page
❌ `client/app/pages/checkout/[transaction].vue`
```vue
// Universal checkout page for all payment types
- Display transaction summary
- Show payment amount
- Embed Cashfree Drop UI
- Handle success/failure redirects
- Loading states
```

#### 2. Success/Failure Pages
❌ `client/app/pages/payment/success.vue`
❌ `client/app/pages/payment/failed.vue`

#### 3. Composables
❌ `client/app/composables/useCheckout.ts`
```typescript
// Methods:
- initiateWalletTopup(amount) - Start wallet topup
- initiateSubscription(stageId) - Start subscription payment
- initiateRecruitment(applicationId) - Pay recruitment fee
- getCheckoutData(transactionId) - Fetch checkout info
- checkPaymentStatus(transactionId) - Poll payment status
```

#### 4. Update Existing Composables
❌ Update `client/app/composables/useWallet.ts`
   - Add topup() method to call checkout API

❌ Update `client/app/composables/useSubscription.ts`
   - Add subscribe() method with payment flow

---

### **Phase 3: Payment Flow Integration** (2 days)

#### 1. Wallet Topup Flow
```
User clicks "Add Money"
  → Enter amount
  → Click "Continue"
  → API: POST /api/wallet/topup {amount}
  → Backend creates Transaction
  → Backend creates Cashfree order
  → Returns checkout URL
  → Frontend redirects to /checkout/{transaction}
  → Cashfree Drop UI loaded
  → User pays
  → Webhook received → PaymentCompleted event
  → HandlePaymentCompleted listener updates wallet balance
  → User redirected to success page
```

#### 2. Subscription Flow
```
User clicks "Subscribe to Member"
  → Select stage/level
  → API: POST /api/subscription/checkout {stage_id, level_id}
  → Backend creates UserSubscription (is_paid: false)
  → Backend creates Transaction
  → Backend creates Cashfree order
  → Frontend redirects to /checkout/{transaction}
  → User pays
  → Webhook → PaymentCompleted
  → Listener activates subscription, assigns level, adds to MLM network
  → User redirected to dashboard as MEMBER
```

#### 3. Recruitment Payment Flow
```
User applies for job with fee
  → API: POST /api/careers/{slug}/apply
  → Backend creates JobApplication (status: awaiting_payment)
  → Backend creates Transaction
  → Returns checkout URL
  → Frontend redirects to /checkout/{transaction}
  → User pays
  → Webhook → PaymentCompleted
  → Listener changes status to 'submitted'
  → User redirected to applications page
```

---

### **Phase 4: Testing** (1 day)

❌ Write comprehensive Pest tests:
- `tests/Feature/Payment/WalletTopupTest.php`
- `tests/Feature/Payment/SubscriptionCheckoutTest.php`
- `tests/Feature/Payment/RecruitmentPaymentTest.php`
- `tests/Feature/Payment/WebhookHandlingTest.php`
- `tests/Feature/Payment/CheckoutFlowTest.php`

---

## 🎯 **Implementation Priority**

### **Easiest First** (Recommended)
1. **Wallet Topup** - Simplest, no complex business logic
2. **Subscription Checkout** - Medium complexity, triggers MLM
3. **Recruitment Payment** - Already mostly done

### **File Creation Order**
```
Day 1 - Backend Core:
1. app/Traits/HasTransaction.php
2. app/Services/Payment/CashfreeService.php
3. app/Services/Payment/CheckoutService.php
4. app/Listeners/Payment/HandlePaymentCompleted.php
5. app/Http/Controllers/Api/CheckoutController.php
6. Update WalletController (add topup)
7. Update api.php routes

Day 2 - Backend Testing:
1. Write Pest tests for all services
2. Test wallet topup end-to-end (without frontend)
3. Fix any issues

Day 3 - Frontend:
1. Create useCheckout.ts composable
2. Create /checkout/[transaction].vue page
3. Create /payment/success.vue
4. Create /payment/failed.vue
5. Update useWallet.ts (add topup method)

Day 4 - Integration:
1. Test wallet topup E2E with frontend
2. Implement subscription checkout
3. Test subscription E2E
4. Polish UX

Day 5 - Final Testing:
1. Test all payment scenarios
2. Test webhook handling
3. Test error cases
4. Write documentation
```

---

## 🔥 **Ready to Start?**

We have:
- ✅ All database tables
- ✅ All models
- ✅ Complete webhook handlers
- ✅ Events defined
- ✅ Routes ready

We need:
- ⏳ Services to CREATE payments
- ⏳ Listeners to HANDLE confirmed payments
- ⏳ Frontend checkout pages

**Total Estimate**: 4-5 days to production-ready checkout system

---

**Next Action**: Start building backend services (HasTransaction trait → CashfreeService → CheckoutService)
