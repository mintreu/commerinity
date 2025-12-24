# Checkout & Payment Architecture Analysis (Old Project)
**Date**: 2025-12-25
**Source**: old_project analysis
**Purpose**: Extract patterns for building unified checkout in current project

---

## 🎯 **Overview**

The old_project uses a **solid, unified architecture** for all payment scenarios:
1. **Wallet Add Money** (TopUp)
2. **Subscription Purchase** (Membership fees)
3. **Order Placement** (E-commerce products)
4. **Recruitment Fees** (Job application payments)

All flows use the **same Transaction → Webhook → Confirm pattern**.

---

## 📦 **Core Architecture Components**

### 1. **Integration System** (Payment Gateway Management)
**Location**: `packages/mintreu/laravel-integration`

**Key Files**:
- `Models/Integration.php` - Payment provider config (Cashfree, Razorpay, Paytm, Cash)
- `Contracts/PaymentIntegrationContract.php` - Provider interface
- `Providers/Payment/CashFree/CashFreePaymentProvider.php` - Cashfree implementation
- `Providers/Payment/CashFree/Support/CashFreeApi.php` - API wrapper

**Integration Model Structure**:
```php
integrations (table):
- name (e.g., "Cashfree")
- url (slug: "cash-free-payment")
- type (payment/payout/sms/shipping)
- key (API client ID)
- secret (API secret)
- webhook (webhook URL)
- status (active/inactive)
- default (is default provider?)
- is_live (sandbox/production)
- charge (provider fee %)
```

**Features**:
- ✅ Multiple providers support
- ✅ One default provider per type
- ✅ Sandbox/Production environment switching
- ✅ Dynamic provider selection

---

### 2. **Transaction System** (Unified Payment Tracking)
**Location**: `packages/mintreu/laravel-transaction`

**Key Files**:
- `Models/Transaction.php` - Universal transaction record
- `Models/Wallet.php` - User wallet
- `Services/WalletService/WalletService.php` - Wallet operations
- `Events/TransactionConfirmed.php` - Payment success event

**Transaction Model Structure**:
```php
transactions (table):
- uuid (unique ID)
- transactionable_type (Order, UserSubscription, JobApplication, Wallet)
- transactionable_id (polymorphic)
- walletable_type (User, Admin)
- walletable_id (wallet owner)
- wallet_id (nullable - for wallet payments)
- type (credited, debited, refunded, reversed)
- status (pending, processing, completed, failed, reversed)
- amount (in paisa)
- charge (gateway fee in paisa)
- purpose (description)
- payment_provider_slug (e.g., "cash-free-payment")
- provider_reference_id (gateway order ID)
- provider_payment_id (gateway payment ID)
- customer_name, customer_email, customer_mobile
- redirect_success_url
- redirect_failure_url
- expired_at (transaction expiry)
- verified (boolean - payment confirmed)
- meta (JSON - additional data)
```

**Key Features**:
- ✅ **Polymorphic** - Works with any payable model
- ✅ **Provider agnostic** - Works with any gateway
- ✅ **Status tracking** - Full lifecycle management
- ✅ **Automatic expiry** - Time-limited transactions
- ✅ **Charge tracking** - Gateway fees recorded

---

### 3. **Cashfree Integration** (Implementation Details)

#### **CashFreeApi.php** - HTTP Client
```php
class CashFreeApi
{
    protected string $key;          // x-client-id
    protected string $secret;       // x-client-secret
    protected string $apiVersion;   // x-api-version (2025-01-01)

    // Base URLs
    // Sandbox: https://sandbox.cashfree.com/pg/
    // Production: https://api.cashfree.com/pg/

    // Headers
    [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'x-api-version' => '2025-01-01',
        'x-client-id' => $key,
        'x-client-secret' => $secret,
    ]

    // Methods
    public function post(string $endpoint, array $payload): array
    public function get(string $endpoint, array $payload = []): array
}
```

#### **OrderAction.php** - Create Payment Order
```php
class OrderAction
{
    public function create(ProviderOrder|array|Closure $data): array
    {
        // POST /orders
        // Returns: payment_session_id, order_id, payment_url
    }

    public function fetch(string $id): array
    {
        // GET /orders/{order_id}
        // Returns: order status and details
    }
}
```

#### **VerifyAction.php** - Webhook Verification
```php
class VerifyAction
{
    public function viaWebhook(Request $request): array
    {
        // Verify webhook signature
        // Fetch order status from Cashfree
        // Return success/failure with transaction
    }
}
```

**Cashfree Order Payload**:
```json
{
  "order_id": "ORDER_2025_ABC123",
  "order_amount": 499.00,
  "order_currency": "INR",
  "customer_details": {
    "customer_id": "USER_123",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+919876543210"
  },
  "order_meta": {
    "return_url": "https://example.com/payment-success",
    "notify_url": "https://example.com/api/webhook/cashfree"
  }
}
```

**Cashfree Response**:
```json
{
  "cf_order_id": 2149460581,
  "order_id": "ORDER_2025_ABC123",
  "entity": "order",
  "order_currency": "INR",
  "order_amount": 499.00,
  "order_status": "ACTIVE",
  "payment_session_id": "session_abc123xyz",
  "order_expiry_time": "2025-12-26T12:00:00Z"
}
```

---

## 🔄 **Common Checkout Flow Pattern**

All payment scenarios follow this **unified 8-step flow**:

### **Step 1: Create Payable Record**
```php
// Example: Order
$order = Order::create([
    'uuid' => 'ORD2025' . Str::random(12),
    'customerable_type' => User::class,
    'customerable_id' => $user->id,
    'total' => 49900, // 499.00 in paisa
    'status' => OrderStatusCast::PENDING,
    'billing_address_id' => $address->id,
    'shipping_address_id' => $address->id,
]);

// Example: UserSubscription
$subscription = UserSubscription::create([
    'user_id' => $user->id,
    'amount' => 199900, // 1999.00 in paisa
    'stage_id' => $stage->id,
    'level_id' => $level->id,
    'is_paid' => false,
]);

// Example: JobApplication
$application = JobApplication::create([
    'user_id' => $user->id,
    'recruitment_id' => $recruitment->id,
    'amount' => $recruitment->application_fee,
    'status' => 'awaiting_payment',
]);
```

### **Step 2: Create Transaction Record**
```php
// Using Trait: HasTransaction
$transaction = $order->createDebitTransaction(
    customer: $user,
    redirect_success_url: config('app.client_url') . '/orders/' . $order->uuid,
    redirect_failure_url: config('app.client_url') . '/orders/' . $order->uuid,
    wallet: $user->wallet,
    purpose: 'Purchasing Products',
    paymentProviderSlug: 'cash-free-payment',
    expireAfterMinutes: 60
);

// Transaction created with:
// - transactionable: $order
// - type: 'debited'
// - status: 'pending'
// - amount: $order->total
// - verified: false
```

### **Step 3: Create Payment Provider Order**
```php
$provider = LaravelIntegration::payment('cash-free-payment');

$providerOrder = $provider->order()->create([
    'order_id' => $transaction->uuid,
    'order_amount' => MoneyService::toRupees($transaction->amount),
    'order_currency' => 'INR',
    'customer_details' => [
        'customer_id' => $user->uuid,
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'customer_phone' => $user->mobile,
    ],
    'order_meta' => [
        'return_url' => $transaction->redirect_success_url,
        'notify_url' => route('webhook.cashfree'),
    ],
]);

// Update transaction with provider details
$transaction->update([
    'provider_reference_id' => $providerOrder['cf_order_id'],
    'meta' => $providerOrder,
]);
```

### **Step 4: Redirect to Checkout**
```php
// Frontend redirects user to:
return response()->json([
    'success' => true,
    'checkout_url' => route('checkout', ['transaction' => $transaction->uuid]),
    'transaction' => $transaction->uuid,
]);

// Checkout page (/checkout/{transaction}) displays:
// - Order summary
// - Payment provider options
// - Cashfree payment session iframe
```

### **Step 5: User Completes Payment**
- User fills payment details (UPI, card, netbanking, wallet)
- Cashfree processes payment
- User redirected to success/failure URL

### **Step 6: Cashfree Webhook Received**
```php
// POST /api/webhook/cashfree
Route::post('/webhook/cashfree', [PaymentWebhookController::class, 'cashfree']);

public function cashfree(Request $request): Response
{
    $provider = LaravelIntegration::payment('cash-free-payment');
    $result = $provider->verify()->viaWebhook($request);

    if ($result['success']) {
        $transaction = $result['transaction'];

        // Update transaction
        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true,
            'provider_payment_id' => $result['payment_id'],
        ]);

        // Fire event
        event(new TransactionConfirmed($transaction));

        return response('OK', 200);
    }

    return response('Verification Failed', 400);
}
```

### **Step 7: Event Listener Confirms Action**
```php
// App\Listeners\HandleTransactionConfirmed

public function handle(TransactionConfirmed $event): void
{
    $transaction = $event->getTransaction();
    $transaction->load('transactionable');

    $payable = $transaction->transactionable;

    // Route to appropriate handler
    if ($payable instanceof Order) {
        OrderConfirmService::make($payable, $transaction)->confirm();
    }

    if ($payable instanceof UserSubscription) {
        MembershipSubscriptionService::make($payable->user)
            ->validate($transaction);
    }

    if ($payable instanceof JobApplication) {
        LaravelRecruitment::make($payable->recruitment)
            ->submitApplication($payable);
    }

    if ($payable instanceof Wallet) {
        WalletService::make($payable)->validate($transaction);
    }
}
```

### **Step 8: Execute Business Logic**

**For Order**:
```php
OrderConfirmService::make($order, $transaction)->confirm();
// - Update order status to CONFIRMED
// - Deduct product stock
// - Create shipments
// - Generate invoices
// - Send confirmation email
```

**For Subscription**:
```php
MembershipSubscriptionService::make($user)->validate($transaction);
// - Mark subscription as paid
// - Update user status to SUBSCRIBED
// - Update user type to MEMBER
// - Assign level_id
// - Add to MLM network
// - Trigger commission calculations
// - Send confirmation notification
```

**For JobApplication**:
```php
LaravelRecruitment::make($recruitment)->submitApplication($application);
// - Mark application as submitted
// - Change status from awaiting_payment to submitted
// - Notify HR team
// - Send confirmation to applicant
```

**For Wallet TopUp**:
```php
WalletService::make($wallet)->validate($transaction);
// - Add amount to wallet balance
// - Record transaction
// - Dispatch wallet.updated event
```

---

## 💰 **Wallet Payment Bypass Pattern**

For users with sufficient wallet balance, payment gateway is skipped:

```php
// Check wallet balance
if ($paymentProvider === 'wallet-payment' && $user->wallet) {
    $walletBalance = MoneyService::make($user->wallet->balance);
    $requiredAmount = MoneyService::make($order->total);

    if ($walletBalance->greaterThanOrEqual($requiredAmount)) {
        // Create transaction
        $transaction = WalletService::make($user->wallet)->payFor(
            payable_record: $order,
            successUrl: $successUrl,
            failureUrl: $failureUrl,
            amount_column: 'total',
            purpose: 'Purchasing Products'
        )->getTransaction();

        // Transaction is AUTOMATICALLY completed
        // Status: completed, verified: true

        // Immediately confirm order
        OrderConfirmService::make($order, $transaction)->confirm();

        // Redirect to success
        return redirect($successUrl);
    } else {
        // Insufficient balance
        return back()->withErrors(['wallet' => 'Insufficient balance']);
    }
}
```

---

## 🔄 **Trait: HasTransaction**

Makes any model payable:

```php
trait HasTransaction
{
    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function createDebitTransaction(
        array $customer,
        string $redirect_success_url,
        string $redirect_failure_url,
        ?Wallet $wallet = null,
        string $purpose = '',
        ?string $paymentProviderSlug = null,
        int $expireAfterMinutes = 60
    ): Transaction {
        $amount = $this->{$this::TRANSACTION_AMOUNT_VALUE} ?? $this->amount;

        return $this->transaction()->create([
            'uuid' => Str::uuid(),
            'walletable_type' => $wallet?->walletable_type,
            'walletable_id' => $wallet?->walletable_id,
            'wallet_id' => $wallet?->id,
            'type' => TransactionTypeCast::DEBITED,
            'status' => TransactionStatusCast::PENDING,
            'amount' => $amount,
            'purpose' => $purpose,
            'payment_provider_slug' => $paymentProviderSlug ?? Integration::payment()->first()->url,
            'customer_name' => $customer['name'],
            'customer_email' => $customer['email'],
            'customer_mobile' => $customer['mobile'],
            'redirect_success_url' => $redirect_success_url,
            'redirect_failure_url' => $redirect_failure_url,
            'expired_at' => now()->addMinutes($expireAfterMinutes),
            'verified' => false,
        ]);
    }

    public function createCreditTransaction(...) { /* Similar for wallet topup */ }
}
```

---

## 📊 **Database Schema Summary**

### **integrations**
```sql
CREATE TABLE integrations (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    url VARCHAR(255),      -- Slug
    type VARCHAR(50),      -- payment, payout, sms, shipping
    key TEXT,              -- API key
    secret TEXT,           -- API secret
    webhook TEXT,          -- Webhook URL
    status BOOLEAN,
    default BOOLEAN,
    is_live BOOLEAN,
    charge DECIMAL(8,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **transactions**
```sql
CREATE TABLE transactions (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    transactionable_type VARCHAR(255),
    transactionable_id BIGINT,
    walletable_type VARCHAR(255),
    walletable_id BIGINT,
    wallet_id BIGINT NULLABLE,
    type VARCHAR(50),              -- credited, debited, refunded, reversed
    status VARCHAR(50),            -- pending, processing, completed, failed
    amount BIGINT,                 -- In paisa
    charge BIGINT DEFAULT 0,
    purpose TEXT,
    payment_provider_slug VARCHAR(255),
    provider_reference_id VARCHAR(255),
    provider_payment_id VARCHAR(255),
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    customer_mobile VARCHAR(20),
    redirect_success_url TEXT,
    redirect_failure_url TEXT,
    expired_at TIMESTAMP,
    verified BOOLEAN DEFAULT FALSE,
    meta JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX(transactionable_type, transactionable_id),
    INDEX(walletable_type, walletable_id),
    INDEX(wallet_id),
    INDEX(status),
    INDEX(verified)
);
```

### **wallets**
```sql
CREATE TABLE wallets (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE,
    walletable_type VARCHAR(255),
    walletable_id BIGINT,
    balance BIGINT DEFAULT 0,  -- In paisa
    currency VARCHAR(3) DEFAULT 'INR',
    pin VARCHAR(255),          -- Hashed
    status VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    UNIQUE(walletable_type, walletable_id)
);
```

---

## 🎯 **Key Patterns to Extract for New Project**

### 1. **Polymorphic Transaction System**
✅ One `transactions` table handles all payment scenarios
✅ `transactionable` morphs to Order, Subscription, JobApplication, Wallet
✅ `HasTransaction` trait makes any model payable

### 2. **Event-Driven Confirmation**
✅ `TransactionConfirmed` event fired on webhook success
✅ `HandleTransactionConfirmed` listener routes to appropriate service
✅ Clean separation: payment processing vs business logic

### 3. **Provider Abstraction**
✅ `Integration` model stores all provider configs
✅ `IntegrationContract` allows easy provider switching
✅ `LaravelIntegration::payment($slug)` facade for access

### 4. **Unified Checkout Page**
✅ Single `/checkout/{transaction}` route for all scenarios
✅ Frontend displays payment options based on transaction
✅ Cashfree payment session embedded via iframe/SDK

### 5. **Wallet-First Payment**
✅ Always check wallet balance first
✅ Auto-complete if sufficient (no gateway fees)
✅ Fallback to payment gateway if insufficient

### 6. **Status Tracking**
✅ Transaction status: pending → processing → completed/failed
✅ Order status: pending → confirmed → processing → shipped
✅ Subscription: unpaid → paid (with level assignment)

### 7. **Security**
✅ Webhook signature verification
✅ Transaction expiry (60 minutes default)
✅ Provider reference ID validation
✅ Double-check payment status with provider API

---

## 🚀 **Implementation Plan for Current Project**

### Phase 1: Core Infrastructure (2 days)
1. ✅ **Already exists**: Transaction model, Wallet model
2. ✅ **Already exists**: Integration model with Cashfree/Razorpay webhooks
3. ⏳ **Missing**: HasTransaction trait
4. ⏳ **Missing**: TransactionConfirmed event + listener

### Phase 2: Cashfree Integration Enhancement (1 day)
1. ⏳ Create CashfreeService (simplified from package)
2. ⏳ Add order creation method
3. ⏳ Add webhook verification method
4. ⏳ Test with Cashfree sandbox

### Phase 3: Unified Checkout Page (Frontend) (1 day)
1. ⏳ Create `/checkout/[transaction].vue` page
2. ⏳ Display transaction summary
3. ⏳ Embed Cashfree payment SDK
4. ⏳ Handle success/failure redirects

### Phase 4: Payment Scenarios (2 days)
1. ⏳ Wallet add money flow
2. ⏳ Subscription purchase flow
3. ⏳ Order checkout flow (when e-commerce built)
4. ⏳ Recruitment payment flow (already exists, needs integration)

### Phase 5: Testing (1 day)
1. ⏳ Test each payment scenario end-to-end
2. ⏳ Test webhook handling
3. ⏳ Test wallet payment bypass
4. ⏳ Write Pest tests

**Total Estimate**: 5-7 days

---

## 📝 **Critical Files to Create**

### Backend
1. `app/Traits/HasTransaction.php`
2. `app/Events/TransactionConfirmed.php`
3. `app/Listeners/HandleTransactionConfirmed.php`
4. `app/Services/CashfreeService.php`
5. `app/Http/Controllers/Api/CheckoutController.php`
6. Update `apiserver/routes/api.php` (add checkout route)

### Frontend
1. `client/app/pages/checkout/[transaction].vue`
2. `client/app/composables/useCheckout.ts`
3. Update wallet composable for payment initiation

### Tests
1. `tests/Feature/Checkout/WalletTopupTest.php`
2. `tests/Feature/Checkout/SubscriptionCheckoutTest.php`
3. `tests/Feature/Checkout/WebhookHandlingTest.php`

---

**END OF ANALYSIS**
