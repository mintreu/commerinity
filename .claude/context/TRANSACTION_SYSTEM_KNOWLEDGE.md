# Transaction System Knowledge Base

> **Purpose**: Consolidated knowledge from all reference projects (JetPax, Popkult, old Commerinity)
> to prevent repeated scanning. This is the SINGLE SOURCE OF TRUTH for transaction implementation.

---

## 1. CURRENT IMPLEMENTATION STATUS

### Models Already Created (in apiserver/)
```
✅ Transaction.php       - Full transaction model with morph, provider fields
✅ Wallet.php            - Balance, hold_balance, PIN with hash
✅ BeneficiaryAccount.php - Bank/UPI, provider_beneficiary_id
✅ Integration.php       - Encrypted credentials storage
✅ PaymentService.php    - Unified gateway with provider registration
✅ NativePaymentProvider - Wallet/Cash/COD/Bank Transfer
✅ NativePayoutProvider  - Manual admin payout
```

### Providers NOT Yet Implemented
```
❌ CashfreePaymentProvider   - PRIORITY (default for India)
❌ CashfreePayoutProvider    - PRIORITY (default for India)
❌ RazorpayPaymentProvider   - Backup option
❌ RazorpayPayoutProvider    - Backup option
❌ StripePaymentProvider     - International (future)
```

---

## 2. COMMISSION STRUCTURE (CORRECTED)

```
LEVEL COMMISSIONS (on subscription amount):
├── Level 1: 5%  (Direct sponsor)
├── Level 2: 4%
├── Level 3: 3%
├── Level 4: 2%
├── Originator: 5% (if advisor recruited)
└── MAX TOTAL: 19% (14% Affiliate + 5% originator)

TYPICAL WEIGHTED PAYOUT: ~8.5% (based on network structure)
- Not all subscribers have 4 levels of uplines
- New users at bottom may only trigger 1-2 level payouts

STAGE PRICING (inclusive of 18% GST):
├── Stage 1 (Basic):   ₹250  (Base: ₹211.86, GST: ₹38.14)
├── Stage 2 (Premium): ₹500  (Base: ₹423.73, GST: ₹76.27)
└── Stage 3 (Elite):   ₹1,000 (Base: ₹847.46, GST: ₹152.54)
```

---

## 3. TRANSACTION TYPES (ALL CASES)

### 3.1 Subscription Transactions
```php
// Type: 'subscription_new', 'subscription_renewal', 'subscription_upgrade'
// Morph: transactionable_type = UserSubscription::class

$transaction = Transaction::create([
    'uuid' => 'TXN-' . Str::uuid(),
    'wallet_id' => $user->wallet->id,
    'transactionable_type' => UserSubscription::class,
    'transactionable_id' => $subscription->id,
    'amount' => 25000, // in paisa (₹250)
    'type' => 'subscription_new',
    'status' => 'pending',
    'payment_method' => 'cashfree',
    'metadata' => [
        'stage' => 'basic',
        'gst_amount' => 3814, // paisa
        'base_amount' => 21186, // paisa
    ],
]);

// ON CONFIRMATION:
// 1. Activate subscription
// 2. Process Affiliate commissions (levels 1-4)
// 3. Process originator commission (if applicable)
// 4. Update genealogy stats
```

### 3.2 Commission Transactions
```php
// Type: 'commission_level_1', 'commission_level_2', etc.
// These are CREDIT transactions to upline wallets

$commission = Transaction::create([
    'uuid' => 'TXN-' . Str::uuid(),
    'wallet_id' => $upline->wallet->id,
    'transactionable_type' => UserSubscription::class,
    'transactionable_id' => $subscription->id,
    'amount' => 1250, // 5% of ₹250 = ₹12.50 in paisa
    'type' => 'commission_level_1',
    'status' => 'completed', // Immediate credit
    'payment_method' => 'internal',
    'metadata' => [
        'from_user_id' => $subscriber->id,
        'level' => 1,
        'percentage' => 5,
        'source_amount' => 25000,
    ],
]);

// Credit wallet immediately
$upline->wallet->increment('balance', $commission->amount);
```

### 3.3 Wallet Transactions
```php
// Types: 'wallet_topup', 'wallet_transfer', 'wallet_debit'

// TOP-UP (External money → Wallet)
$topup = Transaction::create([
    'type' => 'wallet_topup',
    'amount' => 100000, // ₹1000
    'status' => 'pending', // Until payment confirmed
]);

// TRANSFER (Wallet → Wallet)
$transfer = Transaction::create([
    'type' => 'wallet_transfer',
    'wallet_id' => $sender->wallet->id,
    'metadata' => [
        'recipient_wallet_id' => $recipient->wallet->id,
    ],
]);

// DEBIT (Wallet → Payment)
$debit = Transaction::create([
    'type' => 'wallet_debit',
    'payment_method' => 'wallet', // Used wallet balance
]);
```

### 3.4 Withdrawal Transactions
```php
// Type: 'withdrawal_request', 'withdrawal_processed', 'withdrawal_failed'

$withdrawal = Transaction::create([
    'uuid' => 'TXN-' . Str::uuid(),
    'wallet_id' => $user->wallet->id,
    'transactionable_type' => BeneficiaryAccount::class,
    'transactionable_id' => $beneficiary->id,
    'amount' => 1500000, // ₹15,000
    'type' => 'withdrawal_request',
    'status' => 'pending',
    'metadata' => [
        'gross_amount' => 1500000,
        'tds_amount' => 150000, // 10% TDS if annual > ₹10,000
        'net_amount' => 1350000,
        'beneficiary_type' => 'bank', // or 'upi'
    ],
]);

// HOLD the balance immediately
$user->wallet->decrement('balance', $withdrawal->amount);
$user->wallet->increment('hold_balance', $withdrawal->amount);

// ON ADMIN APPROVAL → Process via Cashfree/Razorpay Payout
// ON REJECTION → Release hold back to balance
```

### 3.5 E-Commerce Transactions (Future)
```php
// Types: 'product_order', 'order_refund', 'vendor_settlement'

$order = Transaction::create([
    'type' => 'product_order',
    'transactionable_type' => Order::class,
    'transactionable_id' => $order->id,
    'metadata' => [
        'vendor_id' => $vendor->id,
        'platform_fee' => 15000, // 15% of order
        'affiliate_commission' => 5000, // 5% to uplines
        'gst_details' => [...],
    ],
]);
```

### 3.6 Task Reward Transactions
```php
// Type: 'task_reward'

$reward = Transaction::create([
    'type' => 'task_reward',
    'wallet_id' => $user->wallet->id,
    'amount' => 2500, // ₹25 for completing profile
    'status' => 'completed',
    'metadata' => [
        'task_id' => $task->id,
        'task_type' => 'onboarding',
        'task_name' => 'complete_profile',
    ],
]);

$user->wallet->increment('balance', $reward->amount);
```

---

## 4. PAYMENT PROVIDER IMPLEMENTATIONS

### 4.1 Cashfree (DEFAULT - PRIORITY)

**API Credentials Required:**
```php
// Store in Integration model (encrypted)
[
    'app_id' => 'CF_APP_ID',
    'secret_key' => 'CF_SECRET_KEY',
    'environment' => 'sandbox', // or 'production'
    'webhook_secret' => 'CF_WEBHOOK_SECRET',
]
```

**Payment Flow:**
```php
// 1. CREATE ORDER
POST https://sandbox.cashfree.com/pg/orders
Headers:
  x-client-id: {app_id}
  x-client-secret: {secret_key}
  x-api-version: '2023-08-01'
Body:
{
    "order_id": "TXN-uuid-here",
    "order_amount": 250.00,
    "order_currency": "INR",
    "customer_details": {
        "customer_id": "user-123",
        "customer_phone": "9876543210",
        "customer_email": "user@example.com"
    },
    "order_meta": {
        "return_url": "https://app.com/payment/callback?order_id={order_id}"
    }
}

Response:
{
    "cf_order_id": "123456789",
    "order_id": "TXN-uuid-here",
    "payment_session_id": "session_xxx",
    "order_status": "ACTIVE"
}

// 2. REDIRECT TO PAYMENT PAGE
// Use payment_session_id with Cashfree JS SDK

// 3. WEBHOOK CALLBACK
POST /api/webhooks/cashfree
{
    "type": "PAYMENT_SUCCESS_WEBHOOK",
    "data": {
        "order": {
            "order_id": "TXN-uuid-here",
            "order_amount": 250.00,
            "order_status": "PAID"
        },
        "payment": {
            "cf_payment_id": "123456",
            "payment_status": "SUCCESS",
            "payment_method": "upi"
        }
    }
}

// VERIFY SIGNATURE:
$signature = hash_hmac('sha256', $rawBody, $webhookSecret);
if ($signature !== $request->header('x-webhook-signature')) {
    throw new InvalidSignatureException();
}
```

**Payout Flow:**
```php
// 1. ADD BENEFICIARY
POST https://payout-gamma.cashfree.com/payout/v1/addBeneficiary
{
    "beneId": "BENE-user-123",
    "name": "User Name",
    "email": "user@example.com",
    "phone": "9876543210",
    "bankAccount": "1234567890",
    "ifsc": "HDFC0001234",
    "address1": "Address"
}

// Store beneId as provider_beneficiary_id in BeneficiaryAccount

// 2. REQUEST TRANSFER
POST https://payout-gamma.cashfree.com/payout/v1/requestTransfer
{
    "beneId": "BENE-user-123",
    "amount": 13500.00, // Net after TDS
    "transferId": "TXN-uuid-here",
    "transferMode": "banktransfer" // or "upi"
}

// 3. CHECK STATUS
GET https://payout-gamma.cashfree.com/payout/v1/getTransferStatus?transferId=TXN-xxx
```

### 4.2 Razorpay (BACKUP)

**From JetPax Reference - COMPLETE IMPLEMENTATION:**

```php
// Config Structure
[
    'key_id' => env('RAZORPAY_KEY'),
    'key_secret' => env('RAZORPAY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
]

// CREATE ORDER
$api = new Api($keyId, $keySecret);
$order = $api->order->create([
    'amount' => 25000, // in paisa
    'currency' => 'INR',
    'receipt' => 'TXN-xxx',
    'notes' => ['transaction_id' => $txn->id],
]);

// VERIFY PAYMENT (after callback)
$api->utility->verifyPaymentSignature([
    'razorpay_order_id' => $orderId,
    'razorpay_payment_id' => $paymentId,
    'razorpay_signature' => $signature,
]);

// WEBHOOK VERIFICATION
$webhookBody = file_get_contents('php://input');
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
$api->utility->verifyWebhookSignature($webhookBody, $webhookSignature, $webhookSecret);
```

**Razorpay Payout (RazorpayX) - From JetPax:**
```php
// 1. Create Contact
$contact = $api->contact->create([
    'name' => $user->name,
    'email' => $user->email,
    'contact' => $user->phone,
    'type' => 'customer',
]);
// Store contact->id

// 2. Create Fund Account
$fundAccount = $api->fund_account->create([
    'contact_id' => $contactId,
    'account_type' => 'bank_account',
    'bank_account' => [
        'name' => $beneficiary->account_holder_name,
        'ifsc' => $beneficiary->ifsc_code,
        'account_number' => $beneficiary->account_number,
    ],
]);
// Store fund_account->id as provider_beneficiary_id

// 3. Create Payout
$payout = $api->payout->create([
    'account_number' => $sourceBankAccount, // Your Razorpay account
    'fund_account_id' => $fundAccountId,
    'amount' => $netAmount, // in paisa
    'currency' => 'INR',
    'mode' => 'IMPS', // or 'NEFT', 'RTGS'
    'purpose' => 'payout',
    'queue_if_low_balance' => true,
]);
```

### 4.3 Stripe (INTERNATIONAL - Future)

```php
// For USD/EUR payments from international users
$paymentIntent = \Stripe\PaymentIntent::create([
    'amount' => 500, // cents
    'currency' => 'usd',
    'metadata' => ['transaction_id' => $txn->uuid],
]);

// Webhook verification
$event = \Stripe\Webhook::constructEvent(
    $payload,
    $sigHeader,
    $webhookSecret
);
```

---

## 5. EVENT-DRIVEN ARCHITECTURE

### TransactionConfirmed Event
```php
// app/Events/TransactionConfirmed.php
class TransactionConfirmed
{
    public function __construct(
        public readonly Transaction $transaction,
    ) {}
}

// Dispatch after payment verified
event(new TransactionConfirmed($transaction));
```

### Listeners
```php
// app/Listeners/HandleSubscriptionPayment.php
class HandleSubscriptionPayment
{
    public function handle(TransactionConfirmed $event): void
    {
        $txn = $event->transaction;

        if ($txn->transactionable_type !== UserSubscription::class) {
            return;
        }

        $subscription = $txn->transactionable;

        // 1. Activate subscription
        $subscription->update(['status' => 'active', 'activated_at' => now()]);

        // 2. Process Affiliate commissions
        $this->processAffiliateCommissions($subscription);

        // 3. Update genealogy
        $this->updateGenealogyStats($subscription->user);
    }

    private function processAffiliateCommissions(UserSubscription $sub): void
    {
        $user = $sub->user;
        $amount = $sub->amount; // in paisa

        $levels = [
            1 => 5,  // 5%
            2 => 4,  // 4%
            3 => 3,  // 3%
            4 => 2,  // 2%
        ];

        $currentUser = $user;
        foreach ($levels as $level => $percentage) {
            $upline = $currentUser->parent;
            if (!$upline || !$upline->hasActiveSubscription()) {
                break;
            }

            $commission = (int) ($amount * $percentage / 100);

            Transaction::create([
                'uuid' => 'TXN-' . Str::uuid(),
                'wallet_id' => $upline->wallet->id,
                'transactionable_type' => UserSubscription::class,
                'transactionable_id' => $sub->id,
                'amount' => $commission,
                'type' => "commission_level_{$level}",
                'status' => 'completed',
                'payment_method' => 'internal',
            ]);

            $upline->wallet->increment('balance', $commission);

            $currentUser = $upline;
        }
    }
}
```

---

## 6. WEBHOOK HANDLING PATTERN

```php
// routes/api.php
Route::post('/webhooks/cashfree', [CashfreeWebhookController::class, 'handle']);
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);

// Controller Pattern
class CashfreeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // 1. Verify signature
        $this->verifySignature($request);

        // 2. Parse event type
        $eventType = $request->input('type');

        // 3. Route to handler
        match ($eventType) {
            'PAYMENT_SUCCESS_WEBHOOK' => $this->handlePaymentSuccess($request),
            'PAYMENT_FAILED_WEBHOOK' => $this->handlePaymentFailed($request),
            'REFUND_STATUS_WEBHOOK' => $this->handleRefund($request),
            default => Log::info('Unhandled webhook', $request->all()),
        };

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentSuccess(Request $request): void
    {
        $orderId = $request->input('data.order.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (!$transaction) {
            Log::error('Transaction not found', ['order_id' => $orderId]);
            return;
        }

        $transaction->update([
            'status' => 'completed',
            'provider_reference' => $request->input('data.payment.cf_payment_id'),
            'provider_response' => $request->all(),
        ]);

        event(new TransactionConfirmed($transaction));
    }
}
```

---

## 7. IMPLEMENTATION PLAN

### Phase 1: Core Infrastructure (Week 1)
```
1. Events & Listeners
   - TransactionConfirmed event
   - HandleSubscriptionPayment listener
   - HandleWalletTopup listener
   - HandleWithdrawalApproved listener

2. Cashfree Provider
   - CashfreePaymentProvider (initiate, verify, refund)
   - CashfreePayoutProvider (addBeneficiary, transfer, checkStatus)
   - Webhook controller with signature verification

3. Testing
   - Unit tests for commission calculations
   - Feature tests for payment flow
   - Mock webhook tests
```

### Phase 2: Razorpay Backup (Week 2)
```
1. RazorpayPaymentProvider
2. RazorpayPayoutProvider (RazorpayX)
3. Provider switching logic in PaymentService
```

### Phase 3: E-Commerce Transactions (Week 3)
```
1. Order model with transaction morph
2. HandleOrderPayment listener
3. Vendor settlement logic
4. Refund processing
```

### Phase 4: Task Rewards (Week 4)
```
1. Task model
2. TaskRewardService
3. HandleTaskCompletion listener
```

---

## 8. CONSTANTS & CONFIGURATION

```php
// app/Enums/TransactionType.php
enum TransactionType: string
{
    case SubscriptionNew = 'subscription_new';
    case SubscriptionRenewal = 'subscription_renewal';
    case SubscriptionUpgrade = 'subscription_upgrade';
    case CommissionLevel1 = 'commission_level_1';
    case CommissionLevel2 = 'commission_level_2';
    case CommissionLevel3 = 'commission_level_3';
    case CommissionLevel4 = 'commission_level_4';
    case CommissionOriginator = 'commission_originator';
    case WalletTopup = 'wallet_topup';
    case WalletTransfer = 'wallet_transfer';
    case WalletDebit = 'wallet_debit';
    case WithdrawalRequest = 'withdrawal_request';
    case WithdrawalProcessed = 'withdrawal_processed';
    case WithdrawalFailed = 'withdrawal_failed';
    case ProductOrder = 'product_order';
    case OrderRefund = 'order_refund';
    case VendorSettlement = 'vendor_settlement';
    case TaskReward = 'task_reward';
    case ManualAdjustment = 'manual_adjustment';
}

// app/Enums/TransactionStatus.php
enum TransactionStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case OnHold = 'on_hold';
}

// config/affiliate.php
return [
    'commissions' => [
        'level_1' => 5,
        'level_2' => 4,
        'level_3' => 3,
        'level_4' => 2,
        'originator' => 5,
    ],
    'stages' => [
        'basic' => ['price' => 250, 'pv' => 25],
        'premium' => ['price' => 500, 'pv' => 50],
        'elite' => ['price' => 1000, 'pv' => 100],
    ],
    'gst_rate' => 18, // percentage
    'tds' => [
        'threshold' => 10000, // Annual threshold
        'rate' => 10, // percentage (changed from 5% to 10%)
    ],
    'withdrawal' => [
        'minimum' => 500,
        'processing_days' => 3,
    ],
];
```

---

## 9. REFERENCE PROJECT LOCATIONS (DO NOT SCAN AGAIN)

All necessary code patterns have been extracted above. These are for emergency reference only:

```
JetPax Razorpay: C:\laragon\www\iotron\JetPax-Production\apiserver\app\Services\Iotron
├── LaravelRazorpay/ (Payment)
└── LaravelRazorpayX/ (Payout)

Popkult Providers: C:\laragon\www\iotron\popkult\apiserver\app\Services\PaymentProviders

Old Commerinity: C:\laragon\www\mintreu\server\commerinity\backend
├── app/Listeners/HandleTransactionConfirmed.php
├── app/Services/MembershipSubscriptionService.php
└── packages/mintreu/laravel-transaction/
```

---

**Last Updated**: 2024-12-14
**Author**: Claude Code
**Status**: Ready for implementation
