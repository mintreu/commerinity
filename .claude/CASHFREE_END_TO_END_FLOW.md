# Cashfree Payment System - Complete End-to-End Flow
**Date**: 2025-12-25
**Source**: old_project deep analysis
**Purpose**: Exact implementation blueprint for current project

---

## 🎯 **Complete Flow Traced - 3 Scenarios**

I've traced **3 complete payment flows** from old_project:
1. **Wallet TopUp** (Add Money)
2. **Subscription Purchase** (Member signup)
3. **Order Checkout** (Product purchase)

All use the **SAME Cashfree integration pattern**.

---

## 💰 **FLOW 1: Wallet TopUp (Add Money)**

### **Frontend** (`old_project/frontend/pages/dashboard/wallet/add-money.vue`)

**Step 1**: User enters amount
```vue
<input v-model="amount" type="number" min="1" />
<button @click="handleAddMoney">Add Money</button>
```

**Step 2**: API call
```typescript
async function handleAddMoney() {
  const res = await useSanctumFetch(`${config.public.apiBase}/wallet/add-money`, {
    method: 'POST',
    body: {
      amount: amount.value,  // In rupees (e.g., 500)
      pin: pin.value         // Wallet PIN
    }
  })

  if (res.data.success && res.data.redirect) {
    // Redirect to Livewire checkout page
    window.location.href = res.data.redirect
  }
}
```

### **Backend API** (`old_project/backend/app/Http/Controllers/Api/WalletController.php:294`)

```php
public function addMoney(Request $request): JsonResponse
{
    $validated = $request->validate([
        'amount' => ['required', 'numeric', 'min:1'],  // Rupees
        'pin' => ['required']
    ]);

    $user = $request->user();
    $wallet = $user->wallet;

    // Convert rupees to paisa
    $amount = (int) round(((float) $validated['amount']) * 100);

    // WalletService creates transaction + Cashfree order
    $checkoutUrl = WalletService::make($wallet)
        ->addFund($amount)  // Set amount
        ->getCheckoutUrl(
            redirect_success_url: config('app.client_url').'/dashboard/wallet',
            redirect_failure_url: config('app.client_url').'/dashboard/wallet',
        );

    return response()->json([
        'success' => !is_null($checkoutUrl),
        'message' => 'Money added successfully.',
        'redirect' => $checkoutUrl  // e.g., https://backend.com/checkout/TXN-ABC123
    ]);
}
```

### **WalletService** (`packages/mintreu/laravel-transaction/src/Services/WalletService/WalletService.php:86`)

```php
public function addFund(int $amount): static
{
    $this->amount = $amount;  // Store amount
    $this->type = TransactionTypeCast::CREDITED;
    return $this;
}

public function getCheckoutUrl(string $redirect_success_url, string $redirect_failure_url): ?string
{
    $this->successUrl = $redirect_success_url;
    $this->failureUrl = $redirect_failure_url;

    // Create transaction using trait
    $this->transaction = $this->wallet->createCreditTransaction(
        customer: $this->wallet->walletable,  // User
        amount: $this->amount,
        redirect_success_url: $this->successUrl,
        redirect_failure_url: $this->failureUrl,
        wallet: $this->wallet,
        purpose: 'TopUp Wallet',
        paymentProviderSlug: $this->defaultPaymentIntegration->url,  // 'cash-free-payment'
        expireAfterMinutes: 60
    );

    if (!is_null($this->transaction)) {
        return route('checkout', ['transaction' => $this->transaction->uuid]);
    }

    return null;
}
```

### **HasTransaction Trait** (Creates Transaction + Cashfree Order)

**Location**: `packages/mintreu/laravel-transaction/src/Traits/HasTransaction.php:30`

```php
protected function createTransaction(...): ?Transaction
{
    return DB::transaction(function () use (...) {

        // 1. Get payment provider (Cashfree)
        $paymentProvider = LaravelIntegration::payment($paymentProviderSlug);

        // 2. Create transaction record
        $transaction = $this->transaction()->create([
            'uuid' => $this->uuid,
            'type' => $type,  // 'credited'
            'purpose' => $purpose,  // 'TopUp Wallet'
            'amount' => $resolvedAmount,  // In paisa
            'integration_id' => $paymentProvider->getModel()->id,
            'success_redirect_url' => $redirect_success_url,
            'failure_redirect_url' => $redirect_failure_url,
            'wallet_id' => $wallet->id,
        ]);

        // 3. Create Cashfree order via provider
        $providerData = $paymentProvider->order()->create(function (ProviderOrder $order) use (...) {
            $order->receipt($this->uuid)  // Order ID = Transaction UUID
                ->currency('INR')
                ->amount($transaction->amount)  // Paisa
                ->expireAfter($expireAfterMinutes)
                ->successUrl(route('transaction.validate', ['transaction' => $transaction->uuid]))
                ->failureUrl(route('transaction.failure', ['transaction' => $transaction->uuid]))
                ->customer($customer);  // User model
        });

        // 4. Update transaction with Cashfree response
        $transaction->update([
            'expire_at' => now()->addMinutes($expireAfterMinutes),
            'provider_gen_id' => $providerData['data']['cf_order_id'],  // Cashfree order ID
            'provider_gen_session' => $providerData['data']['payment_session_id'],  // ⭐ THIS IS KEY!
            'provider_gen_link' => $providerData['data']['payment_link'],
            'provider_gen_qr' => $providerData['data']['payment_qr'],
        ]);

        return $transaction;
    });
}
```

### **CashFree API Call** (`packages/mintreu/laravel-integration/src/Providers/Payment/CashFree/Actions/OrderAction.php:26`)

```php
public function create(ProviderOrder|array|Closure $data): array
{
    // Wrap data in Cashfree format
    $data = OrderWrapper::make($data)->toArray();

    // POST to Cashfree API
    $response = $this->provider->getApi()->post('orders', $data);

    // Normalize response
    return CashFreeOrderResponse::make($this->provider)
        ->capture($response, $data)
        ->getOrderResponse();
}
```

**Cashfree API Request** (`OrderWrapper.php:38`):
```json
POST https://sandbox.cashfree.com/pg/orders
Headers:
{
  "x-client-id": "YOUR_APP_ID",
  "x-client-secret": "YOUR_SECRET",
  "x-api-version": "2025-01-01"
}

Body:
{
  "order_id": "TXN-ABC123",
  "order_currency": "INR",
  "order_amount": 500.00,  // In rupees (converted from paisa)
  "customer_details": {
    "customer_id": "USER-UUID",
    "customer_phone": "+919876543210"
  },
  "order_meta": {
    "return_url": "https://backend.com/transaction/validate/TXN-ABC123",
    "notify_url": "https://backend.com/api/webhook/cashfree"
  }
}
```

**Cashfree API Response**:
```json
{
  "cf_order_id": 2149460581,
  "order_id": "TXN-ABC123",
  "entity": "order",
  "order_currency": "INR",
  "order_amount": 500.00,
  "order_status": "ACTIVE",
  "payment_session_id": "session_abc123xyz",  // ⭐ CRITICAL - Used in frontend
  "order_expiry_time": "2025-12-26T12:00:00Z"
}
```

### **Checkout Page** (Livewire)

**Route**: `GET /checkout/{transaction:uuid}`

**Component**: `old_project/backend/app/Livewire/Checkout/CheckoutHome.php`

```php
class CheckoutHome extends Component
{
    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->transaction->load('integration');
        $this->integration = $this->transaction->integration;
    }

    public function render()
    {
        return view('livewire.checkout.checkout-home', [
            'transaction' => $this->transaction,
            'integration' => $this->integration->except('key','secret','webhook')
        ]);
    }
}
```

**View**: `resources/views/livewire/checkout/checkout-home.blade.php`

Displays:
- Transaction summary (ID, amount, status)
- Payment provider logo
- Gateway fee info
- Loads provider-specific component

```blade
@if($integration['url'] == 'cash-free-payment')
    @livewire('checkout.providers.cash-free-checkout', ['transaction' => $transaction])
@endif
```

### **Cashfree Checkout Component**

**Component**: `old_project/backend/app/Livewire/Checkout/Providers/CashFreeCheckout.php`

```php
class CashFreeCheckout extends Component
{
    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->failureUrl = $this->transaction->failure_url;
        $this->successUrl = $this->transaction->success_url;
    }

    public function render()
    {
        return view('livewire.checkout.providers.cash-free-checkout', [
            'payable' => !$this->transaction->verified,  // Only show if not paid
            'mode' => config('laravel-integration.providers.payments.cash-free.dev', true),  // sandbox/production
            'paymentSessionId' => $this->transaction->provider_gen_session,  // ⭐ FROM DB
            'orderId' => $this->transaction->provider_gen_id,
        ]);
    }
}
```

**View**: `resources/views/livewire/checkout/providers/cash-free-checkout.blade.php`

```blade
<button id="cashfree-button1">Pay Via Cashfree</button>

<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
    const cashfree = new Cashfree({
        mode: @js($mode) ? 'sandbox' : 'production'
    });

    function getCashfreeCheckout() {
        return {
            paymentSessionId: @js($paymentSessionId),  // ⭐ FROM TRANSACTION
            returnUrl: @js($successUrl)
        };
    }

    // Auto-trigger on page load
    window.addEventListener("load", () => {
        let isPending = @js($payable);  // Check if not verified
        if (isPending) {
            let options = getCashfreeCheckout();
            cashfree.checkout(options).then(function(result) {
                if (result.error) {
                    window.location.replace(@js($failureUrl));
                }
                // If success, Cashfree redirects to returnUrl automatically
            });
        }
    });

    // Manual trigger on button click
    document.getElementById('cashfree-button1').onclick = function(e) {
        e.preventDefault();
        cashfree.checkout(getCashfreeCheckout());
    };
</script>
```

### **User Pays**

1. Cashfree Drop UI opens (modal or redirect)
2. User selects payment method (UPI, Card, Netbanking, Wallet)
3. User completes payment
4. Cashfree processes payment

### **Webhook Received**

**Endpoint**: `POST /api/webhook/cashfree`

**Controller**: `old_project/backend/app/Http/Controllers/Api/Webhook/PaymentWebhookController.php:38`

```php
public function cashfree(Request $request): Response
{
    try {
        $provider = LaravelIntegration::payment('cash-free-payment');
        $result = $provider->verify()->viaWebhook($request);

        if ($result['success']) {
            // Update transaction
            $this->updateTransactionStatus(
                $result['transaction'],
                TransactionStatusCast::COMPLETED
            );

            return response('OK', 200);
        }

        return response('Verification Failed', 400);
    } catch (\Exception $e) {
        report($e);
        return response('Error', 500);
    }
}

private function updateTransactionStatus(Transaction $transaction, TransactionStatusCast $status): void
{
    $transaction->update([
        'status' => $status->value,
        'verified' => $status === TransactionStatusCast::COMPLETED,
    ]);

    // 🔥 FIRE EVENT
    if ($status === TransactionStatusCast::COMPLETED) {
        event(new \Mintreu\LaravelTransaction\Events\TransactionConfirmed($transaction));
    } else {
        event(new \Mintreu\LaravelTransaction\Events\TransactionFailed($transaction));
    }
}
```

### **Event Listener** Handles Confirmation

**Listener**: `old_project/backend/app/Listeners/HandleTransactionConfirmed.php`

```php
public function handle(TransactionConfirmed $event): void
{
    $transaction = $event->getTransaction();
    $transaction->load('transactionable');

    $payable = $transaction->transactionable;

    // 🎯 ROUTE TO APPROPRIATE HANDLER

    // For Wallet TopUp
    if ($payable instanceof Wallet) {
        WalletService::make($payable)->validate($transaction);
    }

    // For Subscription
    if ($payable instanceof UserSubscription) {
        MembershipSubscriptionService::make($payable->user)->validate($transaction);
    }

    // For Order
    if ($payable instanceof Order) {
        OrderConfirmService::make($payable, $transaction)->confirm();
    }

    // For Recruitment
    if ($payable instanceof JobApplication) {
        LaravelRecruitment::make($payable->recruitment)->submitApplication($payable);
    }
}
```

### **WalletService Validates & Updates Balance**

**Method**: `WalletService::validate()` (line 121)

```php
public function validate(Transaction $transaction)
{
    $currentBalance = LaravelMoney::make($this->wallet->balance);

    if ($transaction->status->value == TransactionStatusCast::COMPLETED->value && $transaction->verified) {
        if (in_array($transaction->type->value, [TransactionTypeCast::CREDITED->value, TransactionTypeCast::REFUNDED->value])) {
            // ✅ ADD MONEY TO WALLET
            $newBalance = $currentBalance->plus($transaction->amount);
            $this->wallet->update([
                'balance' => $newBalance->getAmount()
            ]);
        } else {
            // DEBIT (for payments)
            $newBalance = $currentBalance->minus($transaction->amount);
            $this->wallet->update([
                'balance' => $newBalance->getAmount()
            ]);
        }

        Event::dispatch('wallet.updated', [$this->wallet, $transaction]);
    }
}
```

### **User Redirected to Success**

Cashfree automatically redirects to `transaction.success_redirect_url` (set to `/dashboard/wallet`)

---

## 🎫 **FLOW 2: Subscription Purchase**

### **Frontend** (`old_project/frontend/pages/dashboard/subscribe.vue:582`)

```typescript
async function handleSubscribe() {
    isSubmitting.value = true

    const url = `${config.public.apiBase}/account/lifecycle/subscribe`
    const payload = {
        stage_id: stageId,
        level_id: levelId,
        auto_renew: autoRenewEnabled.value,
        provider: paymentProvider.value  // 'online' or 'wallet'
    }

    const res = await useSanctumFetch(url, { method: "POST", body: payload })

    if (res.data.success && res.data.checkout_url) {
        // Redirect to checkout
        window.location.href = res.data.checkout_url
    }
}
```

### **Backend API** (`old_project/backend/app/Http/Controllers/Api/Auth/UserSubscriptionController.php:66`)

```php
public function subscribeSubscription(Request $request)
{
    $user = $request->user();

    // Check if wallet payment
    $membershipSubscriptionService = MembershipSubscriptionService::make($user);
    $membershipSubscriptionService->ensureSubscription();  // ⭐ CREATES SUBSCRIPTION
    $checkoutUrl = $membershipSubscriptionService->getCheckoutUrl();

    return response()->json([
        'data' => [
            'status' => true,
            'message' => 'User subscription completed successfully.',
            'redirect' => true,
            'redirect_url' => $checkoutUrl  // /checkout/{transaction}
        ],
    ]);
}
```

### **MembershipSubscriptionService** (`old_project/backend/app/Services/UserServices/MembershipSubscriptionService.php`)

```php
public function ensureSubscription(): static
{
    if ($this->subscriptionNeed) {
        if ($this->wallet && LaravelMoney::make($this->wallet->balance)->greaterThanOrEqual($this->stage->price)) {
            // ✅ WALLET HAS BALANCE - AUTO COMPLETE
            $this->subscribeWithWallet();
        } else {
            // ❌ INSUFFICIENT - GO TO CHECKOUT
            $this->subscribeWithCheckout();
        }
    }
    return $this;
}

protected function subscribeWithWallet()
{
    $this->subscription = $this->createSubscription();

    // ⚡ DIRECT WALLET PAYMENT (no checkout needed)
    WalletService::make($this->wallet)
        ->payFor(
            payable_record: $this->subscription,
            amount_column: 'amount'
        );

    // Transaction is auto-completed, listener fires immediately
}

protected function subscribeWithCheckout()
{
    // Creates subscription record (is_paid: false)
    $this->subscription = $this->createSubscription();
}

protected function createSubscription(): UserSubscription
{
    return $this->user->memberships()->create([
        'amount' => $this->stage->price,  // In paisa
        'stage_id' => $this->stage->id,
        'level_id' => $this->level->id,
        'is_paid' => false,  // ⚠️ NOT PAID YET
        'expire_at' => now()->addYears($this->level->validate_years)
    ]);
}

public function getCheckoutUrl(): string
{
    $this->subscription->load(['transaction']);

    if (!is_null($this->subscription->transaction)) {
        return route('checkout', ['transaction' => $this->subscription->transaction->uuid]);
    } else {
        // Create transaction
        $transaction = $this->makeSubscriptionPaymentRecord();
        return route('checkout', ['transaction' => $transaction->uuid]);
    }
}

private function makeSubscriptionPaymentRecord(): Transaction
{
    // Uses HasTransaction trait
    return $this->subscription->createDebitTransaction(
        customer: $this->user,
        redirect_success_url: config('app.client_url').'/dashboard/subscribe',
        redirect_failure_url: config('app.client_url').'/dashboard/subscribe',
    );
}
```

### **Webhook Confirmation**

Same webhook handler, but listener calls different service:

```php
// In HandleTransactionConfirmed listener
if ($payable instanceof UserSubscription) {
    MembershipSubscriptionService::make($payable->user)->validate($transaction);
}
```

### **MembershipSubscriptionService::validate()** (line 167)

```php
public function validate(Transaction $transaction)
{
    $this->subscription = $transaction->transactionable;
    $this->subscription->load('level');

    if ($transaction->verified) {
        // ✅ MARK SUBSCRIPTION AS PAID
        $this->subscription->update([
            'is_paid' => true
        ]);

        // ✅ SEND CONFIRMATION
        $this->user->notify(new SubscriptionConfirmationNotification);

        // ✅ UPDATE USER STATUS & TYPE
        $this->user->update([
            'status' => AuthStatusCast::SUBSCRIBED,
            'type' => AuthTypeCast::MEMBER,  // 🔥 UPGRADE TO MEMBER!
            'level_id' => $this->subscription->level->id
        ]);

        // ✅ ADD TO Affiliate NETWORK
        $networkService = NetworkService::make($this->user);
        $networkService->addToNetwork();

        // ✅ TRIGGER COMMISSIONS (if has sponsor)
        // NetworkService handles commission generation
    } else {
        $this->user->notify(new SubscriptionFailedNotification);
    }
}
```

---

## 🛒 **FLOW 3: Order Checkout (Product Purchase)**

### **Frontend** (`old_project/frontend/pages/store/checkout.vue:116`)

```vue
<button @click="placeOrder">Place Order</button>

<script>
async function placeOrder() {
    const res = await useSanctumFetch(`${config.public.apiBase}/orders`, {
        method: 'POST',
        body: {
            items: cart.value,  // Cart items
            shipping_address_id: selectedAddress.value,
            billing_address_id: billingAddress.value,
            payment_provider: paymentMethod.value  // 'cash-free-payment' or 'wallet-payment'
        }
    })

    if (res.data.checkout_url) {
        window.location.href = res.data.checkout_url
    }
}
</script>
```

### **Backend API** (OrderController - inferred)

```php
public function store(Request $request)
{
    $user = $request->user();

    $orderService = OrderCreationService::make($user)
        ->shippingAddress($shippingAddress)
        ->billingAddress($billingAddress)
        ->paymentProvider($request->payment_provider)
        ->placeOrder($request);  // Creates order + transaction

    return response()->json([
        'success' => true,
        'checkout_url' => $orderService->getCheckoutUrl(),
        'transaction' => $orderService->getTransaction(),
    ]);
}
```

### **OrderCreationService::placeOrder()** (line 108)

```php
public function placeOrder(Request $request): static
{
    $this->initializeCart($request);

    if ($this->cartMeta['summary']['quantity']) {
        // 1. Create order record
        $this->order = $this->createOrder();

        // 2. Create transaction
        if ($this->provider == 'wallet-payment' && $userWallet) {
            // WALLET PAYMENT (bypass checkout)
            if (LaravelMoney::make($userWallet->balance)->greaterThanOrEqual($this->order->total)) {
                $this->transaction = WalletService::make($userWallet)->payFor(
                    payable_record: $this->order,
                    successUrl: $successUrl,
                    failureUrl: $failureUrl,
                    amount_column: 'total',
                    purpose: 'Purchasing Products'
                )->getTransaction();

                // ✅ IMMEDIATELY CONFIRM ORDER
                OrderConfirmService::make($this->order, $this->transaction)->confirm();
                $this->checkoutUrl = $successUrl;
            }
        } else {
            // GATEWAY PAYMENT
            $this->transaction = $this->order->createDebitTransaction(
                customer: [
                    'name' => $this->order->customer_name,
                    'email' => $this->order->customer_email,
                    'mobile' => $this->order->customer_mobile
                ],
                redirect_success_url: $successUrl,
                redirect_failure_url: $failureUrl,
                wallet: $this->customer?->wallet,
                purpose: 'Purchasing Products',
                paymentProviderSlug: $this->provider,
                expireAfterMinutes: 60
            );

            $this->checkoutUrl = route('checkout', ['transaction' => $this->transaction->uuid]);
        }

        // 3. Attach products to order
        $this->processLeftJobs();
    }

    return $this;
}
```

### **Order Confirmation** (After Payment)

**Service**: `OrderConfirmService::confirm()` (line 57)

```php
public function confirm(): bool
{
    if ($this->isPaid) {
        $this->processOrderConfirmation();

        $this->order->update([
            'status' => OrderStatusCast::CONFIRM
        ]);
    }

    if ($this->order->customer?->email) {
        $this->order->customer->notify(new OrderNotification($this->order));
    }

    return $this->order->status->value == OrderStatusCast::CONFIRM->value;
}

private function processOrderConfirmation(): void
{
    $this->order->orderProducts->each(function (OrderProduct $orderProduct) {
        // 1. Check stock availability
        $allowedProducts = $this->getQualifiedUpdatedOrderedProductStockArray($orderProduct);

        // 2. Deduct stock
        foreach ($allowedProducts as $item) {
            $item['stock']->sold_quantity += $item['quantity'];
            $item['stock']->save();
        }

        // 3. Create shipment
        $shipment = $this->makeOrderShipment($orderProduct, ...);

        // 4. Create invoice
        $invoice = $this->makeOrderInvoice($shipment, $orderProduct);
    });
}
```

---

## 🔑 **KEY INSIGHTS**

### **1. Polymorphic Transaction System**

**EVERYTHING is transactionable**:
```php
// Wallet uses morphOne (wallet itself is payable for topup)
Wallet → transaction (for add money)

// UserSubscription
UserSubscription → transaction (for membership fee)

// Order
Order → transaction (for product payment)

// JobApplication
JobApplication → transaction (for recruitment fee)
```

### **2. HasTransaction Trait is the Magic**

One trait makes ANY model payable:
```php
use HasTransaction;

// Now you can:
$transaction = $model->createDebitTransaction(...);
$transaction = $model->createCreditTransaction(...);
```

### **3. Two-Step Transaction Creation**

```php
// Step 1: Create transaction record in DB
$transaction = Transaction::create([...]);

// Step 2: Call Cashfree API and update transaction
$providerResponse = $paymentProvider->order()->create(...);
$transaction->update([
    'provider_gen_id' => $providerResponse['cf_order_id'],
    'provider_gen_session' => $providerResponse['payment_session_id'],  // ⭐ KEY
]);
```

### **4. Payment Session ID is CRITICAL**

The `payment_session_id` from Cashfree is stored in `transaction.provider_gen_session` and used in checkout page:

```javascript
cashfree.checkout({
    paymentSessionId: transaction.provider_gen_session,  // ⭐ FROM DB
    returnUrl: transaction.success_redirect_url
});
```

### **5. Webhook Updates Transaction**

```php
// Cashfree sends webhook when payment completes
// Webhook controller:
$transaction->update([
    'status' => 'completed',
    'verified' => true,
    'provider_transaction_id' => $webhookData['payment_id']
]);

// Fire event
event(new TransactionConfirmed($transaction));
```

### **6. Event Listener Routes to Business Logic**

```php
// One listener handles ALL payment confirmations
// Routes based on transactionable type
HandleTransactionConfirmed::handle() {
    if ($payable instanceof Wallet) → Update balance
    if ($payable instanceof UserSubscription) → Activate subscription
    if ($payable instanceof Order) → Confirm order
    if ($payable instanceof JobApplication) → Submit application
}
```

### **7. Wallet Payment Bypass**

If user selects "wallet" and has sufficient balance:
```php
// SKIP checkout page entirely
// Create transaction as COMPLETED immediately
// Fire TransactionConfirmed event immediately
// No Cashfree involved
```

---

## 📊 **Database Flow**

### **Transaction Record Lifecycle**

```sql
-- Step 1: Created by HasTransaction trait
INSERT INTO transactions (
    uuid,  -- 'TXN-ABC123'
    transactionable_type,  -- 'App\Models\Wallet'
    transactionable_id,  -- Wallet ID
    type,  -- 'credited'
    status,  -- 'pending'
    amount,  -- 50000 (₹500.00 in paisa)
    integration_id,  -- Cashfree integration ID
    provider_gen_id,  -- NULL (set after Cashfree call)
    provider_gen_session,  -- NULL (set after Cashfree call)
    success_redirect_url,  -- 'https://frontend.com/dashboard/wallet'
    failure_redirect_url,  -- 'https://frontend.com/dashboard/wallet'
    expire_at,  -- now() + 60 minutes
    verified  -- false
);

-- Step 2: Updated with Cashfree response
UPDATE transactions SET
    provider_gen_id = '2149460581',  -- Cashfree order ID
    provider_gen_session = 'session_abc123xyz',  -- ⭐ Payment session
    provider_gen_link = 'https://payments.cashfree.com/order/...',
    expire_at = '2025-12-26 12:00:00'
WHERE uuid = 'TXN-ABC123';

-- Step 3: Updated by webhook
UPDATE transactions SET
    status = 'completed',
    verified = true,
    provider_transaction_id = 'cf_payment_123456'
WHERE uuid = 'TXN-ABC123';

-- Step 4: Wallet balance updated by listener
UPDATE wallets SET
    balance = balance + 50000  -- Add amount
WHERE id = 1;
```

---

## 🎨 **Frontend Checkout UI (Livewire vs Nuxt)**

### **Old Project Uses**: Livewire (server-rendered)
```
Route: GET /checkout/{transaction:uuid}
↓
Livewire: CheckoutHome component
↓
Livewire: CashFreeCheckout component (loads Cashfree SDK)
↓
Cashfree.checkout({ paymentSessionId, returnUrl })
```

### **Current Project Should Use**: Nuxt Page (client-rendered)
```
Route: /checkout/[transaction]
↓
Vue Page: client/app/pages/checkout/[transaction].vue
↓
Fetch transaction data via API
↓
Load Cashfree SDK
↓
cashfree.checkout({ paymentSessionId, returnUrl })
```

**Key Difference**:
- Old: Transaction data already in Livewire props (server-side)
- New: Must fetch transaction via API (client-side)

---

## 🚀 **IMPLEMENTATION PLAN FOR CURRENT PROJECT**

### **What We Have** ✅
1. Transaction model (with all needed fields)
2. Wallet model
3. Integration model
4. CashfreeWebhookController (complete!)
5. PaymentCompleted event
6. MoneyService

### **What We Need** ⏳

#### **Backend (3 days)**

**Day 1: Traits & Services**
1. Create `app/Traits/HasTransaction.php`
   - Methods: `createDebitTransaction()`, `createCreditTransaction()`
   - Calls CashfreeService to create order
   - Updates transaction with payment_session_id

2. Create `app/Services/Payment/CashfreeService.php`
   - Method: `createOrder(Transaction $transaction)` → Returns payment_session_id
   - Method: `verifyPayment(string $orderId)` → Check payment status
   - Uses Guzzle HTTP client to call Cashfree API

3. Add to models:
   - `Wallet` → `use HasTransaction;`
   - `UserSubscription` → `use HasTransaction;`
   - `JobApplication` → `use HasTransaction;` (already has it)

**Day 2: Controllers & Listeners**

4. Update `app/Http/Controllers/Api/WalletController.php`
   ```php
   POST /api/wallet/topup
   - Validate amount & PIN
   - Create transaction using HasTransaction trait
   - Return checkout URL
   ```

5. Update `app/Http/Controllers/Api/SubscriptionController.php`
   ```php
   POST /api/subscription/checkout
   - Create UserSubscription record
   - Create transaction
   - Return checkout URL
   ```

6. Create `app/Http/Controllers/Api/CheckoutController.php`
   ```php
   GET /api/checkout/{transaction}
   - Return transaction data for frontend
   - Include payment_session_id, amount, purpose, etc.
   ```

7. Create `app/Listeners/Payment/HandlePaymentCompleted.php`
   ```php
   Handles PaymentCompleted event
   Routes to:
   - Wallet topup → Update balance
   - Subscription → Activate + Affiliate
   - Order → Confirm order
   - JobApplication → Submit
   ```

**Day 3: Testing**

8. Write Pest tests:
   - `tests/Feature/Payment/WalletTopupTest.php`
   - `tests/Feature/Payment/SubscriptionCheckoutTest.php`
   - `tests/Feature/Payment/CashfreeIntegrationTest.php`

#### **Frontend (2 days)**

**Day 4: Checkout Page**

9. Create `client/app/pages/checkout/[transaction].vue`
   ```vue
   <script setup>
   // Fetch transaction data
   const transaction = await useSanctumFetch(`/api/checkout/${route.params.transaction}`)

   // Load Cashfree SDK
   const cashfree = new Cashfree({ mode: 'sandbox' })

   // Trigger payment
   cashfree.checkout({
       paymentSessionId: transaction.payment_session_id,
       returnUrl: transaction.success_url
   })
   </script>
   ```

10. Create `client/app/composables/useCheckout.ts`
    ```typescript
    export function useCheckout() {
        const initiateWalletTopup = async (amount: number) => { ... }
        const initiateSubscription = async (stageId, levelId) => { ... }
        const getCheckoutData = async (transactionId) => { ... }
    }
    ```

**Day 5: Integration**

11. Update `client/app/composables/useWallet.ts`
    - Add `topup(amount)` method

12. Update `client/app/composables/useSubscription.ts`
    - Add `subscribe(stage, level)` method

13. Create success/failure pages
    - `client/app/pages/payment/success.vue`
    - `client/app/pages/payment/failed.vue`

---

## 📋 **Files to Create (Summary)**

### Backend (8 files)
1. `app/Traits/HasTransaction.php`
2. `app/Services/Payment/CashfreeService.php`
3. `app/Listeners/Payment/HandlePaymentCompleted.php`
4. `app/Listeners/Payment/HandlePaymentFailed.php`
5. `app/Http/Controllers/Api/CheckoutController.php`
6. Update `app/Http/Controllers/Api/WalletController.php`
7. Update `app/Http/Controllers/Api/SubscriptionController.php`
8. Update `apiserver/routes/api.php`

### Frontend (5 files)
1. `client/app/pages/checkout/[transaction].vue`
2. `client/app/pages/payment/success.vue`
3. `client/app/pages/payment/failed.vue`
4. `client/app/composables/useCheckout.ts`
5. Update `client/app/composables/useWallet.ts`

### Tests (3 files)
1. `tests/Feature/Payment/WalletTopupTest.php`
2. `tests/Feature/Payment/SubscriptionCheckoutTest.php`
3. `tests/Feature/Payment/CashfreeServiceTest.php`

**Total**: 16 files

---

## 🎯 **Next Action**

Ready to start building! Should I:

**A)** Start with **HasTransaction trait + CashfreeService** (backend foundation)?

**B)** Create **complete implementation plan document** first?

**C)** Build **wallet topup end-to-end** (fastest to test)?

Your call! 🚀
