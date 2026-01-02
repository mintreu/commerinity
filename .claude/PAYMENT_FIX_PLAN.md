# Payment System Fix Plan
**Date:** 2026-01-02
**Status:** In Progress
**Priority:** CRITICAL - 4 days deadline missed

## What You Found Wrong (User Feedback)

1. ❌ **Tests passing with invalid credentials** - I built fake tests that passed without real API calls
2. ❌ **`checkout_url` in Transaction model** - Wrong architecture, shouldn't be in database
3. ❌ **Checkout on Nuxt side** - Should be Laravel/Livewire side (like old_project)
4. ❌ **`credentials` JSON column in Integration** - Should be plain `key`/`secret` columns
5. ❌ **Ignored old_project patterns** - Tried to be "smart" instead of following proven code
6. ❌ **Not using fingerprint trait** - Used `$user->id` instead of `$user->fingerprint`
7. ❌ **Missing success_url/failure_url** - Critical for redirect flow
8. ❌ **PayoutService likely broken** - Payment and Payout should share 70% similarity
9. ❌ **Never properly studied old_project** - Didn't understand the flow before refactoring

## Current State (What You Fixed)

### ✅ Files You Already Fixed:
- `config/services.php` - Payment/Payout config with key/secret
- `database/seeders/IntegrationSeeder.php` - Seeds integrations
- `app/Models/Transaction.php`:
  - Commented out `checkout_url`
  - Added `success_url`, `failure_url`
  - Fixed integration relationship
- `app/Models/Integration.php`:
  - Has `credentials` JSON cast (STILL WRONG - needs to be plain columns)
- `app/Services/Payment/Providers/CashfreePaymentProvider.php`:
  - Fixed return_url to use `route('transaction.validate')`
  - Fixed notify_url to use `route('transaction.failure')`
  - Fixed checkout_url to return `route('checkout', ['transaction' => $transactionId])`
  - Changed headers to use `key`/`secret` (line 329-330)
- API and web routes
- Livewire files
- Transaction controllers (app/Http/Controllers/Api/Transaction/)
- Wallet-related files

### ❌ Files I Made (Untested by You):
- Webhook controller (`app/Http/Controllers/Api/Webhooks/CashfreeWebhookController.php`)
- Other payment-related files

## Architecture Issues

### Issue 1: Integration Model Credentials

**Current (WRONG):**
```php
// Integration model has JSON column
'credentials' => 'array',  // Encrypted JSON

// Seeder stores as JSON
'credentials' => [
    'key' => '...',
    'secret' => '...',
],
```

**Expected (old_project pattern):**
```php
// Integration should have plain columns (NOT JSON)
protected $fillable = [
    'key',      // Plain column
    'secret',   // Plain column (hidden)
    'webhook',  // Plain column (hidden)
];

protected $hidden = ['key', 'secret', 'webhook'];
```

**Fix Required:**
1. Create migration to add `key`, `secret`, `webhook` columns to integrations table
2. Remove `credentials` column
3. Update Integration model to use plain columns
4. Update seeder to set plain columns
5. Update CashfreePaymentProvider to use `$integration->key` not `$integration->getCredential('key')`

### Issue 2: Checkout Flow

**Current (WRONG):**
- Checkout page on Nuxt side (`client/app/pages/checkout/[transaction].vue`)
- Frontend loads payment session

**Expected (old_project pattern):**
- Checkout is Livewire component on Laravel side
- Route: `/checkout/{transaction}` renders Livewire view
- Provider-specific components: `CashfreeCheckout.php`, `RazorpayCheckout.php`
- Frontend just embeds the Laravel page (server-side rendered)

**Fix Required:**
1. Create `app/Livewire/Checkout/CheckoutHome.php` (main component)
2. Create `app/Livewire/Checkout/Providers/CashfreeCheckout.php`
3. Create Blade views: `resources/views/livewire/checkout/checkout-home.blade.php`
4. Add web route: `Route::get('/checkout/{transaction:uuid}', CheckoutHome::class)`
5. Remove Nuxt checkout page (or make it redirect to Laravel)

### Issue 3: Transaction Model

**Current State:**
```php
'checkout_url',  // Commented out (CORRECT)
'success_url',   // Added (CORRECT)
'failure_url',   // Added (CORRECT)
```

**Still Missing:**
- `provider_gen_session` - Payment session ID (Cashfree's `payment_session_id`)
- `provider_gen_link` - Direct payment link
- Should match old_project structure

### Issue 4: Payment/Payout Service Similarity

**Old Project Pattern:**
- Both services share same structure
- Methods: `initiate()`, `verify()`, `fetch()`, `refund()`
- Same response format
- 70%+ code similarity

**Current:**
- Need to audit both services
- Ensure payout follows same patterns as payment
- Use same DTO classes (PaymentResponse, PaymentRequest)

### Issue 5: Fingerprint Trait Usage

**Current (WRONG):**
```php
$transaction->user_id = $user->id;  // Direct ID usage
```

**Expected:**
```php
$transaction->user_fingerprint = $user->fingerprint;  // Use trait
```

**Fix Required:**
- Find all uses of `$user->id` in transaction/wallet/payment context
- Replace with `$user->fingerprint`
- Ensure User model uses `HasFingerprint` trait

## Old Project Flow (Reference)

### 1. Payment Initiation
```php
// Controller
$provider = LaravelIntegration::payment('cashfree');
$orderResponse = $provider->order()->create(function($order) {
    $order->amount(100)
          ->customerEmail($user->email)
          ->successUrl(route('transaction.success', $txn->uuid))
          ->failureUrl(route('transaction.failure', $txn->uuid));
});

// Store in transaction
$transaction->update([
    'provider_gen_id' => $orderResponse['data']['provider_gen_id'],
    'provider_gen_session' => $orderResponse['data']['provider_gen_session'],
    'provider_gen_link' => $orderResponse['data']['provider_gen_link'],
]);

// Return checkout URL
return ['checkout_url' => route('checkout', $transaction->uuid)];
```

### 2. Checkout Page (Livewire)
```php
// CheckoutHome.php
public function mount(Transaction $transaction)
{
    $this->transaction = $transaction->load('integration');

    // Render provider-specific component
    $component = match($transaction->integration->slug) {
        'cashfree' => CashfreeCheckout::class,
        'razorpay' => RazorpayCheckout::class,
    };
}
```

### 3. Provider Checkout (Livewire)
```blade
{{-- cash-free-checkout.blade.php --}}
<div id="cashfree-checkout"></div>

<script>
const cashfree = Cashfree({
    mode: "{{ $mode }}"  // sandbox or production
});

cashfree.checkout({
    paymentSessionId: "{{ $paymentSessionId }}",
    returnUrl: "{{ $transaction->failure_url }}"
});
</script>
```

### 4. Webhook Handler
```php
// CashfreeWebhookController
public function handle(Request $request)
{
    // Verify signature
    $provider = new CashfreePaymentProvider();
    $result = $provider->verifyWebhook($request);

    if ($result['success']) {
        $transaction = Transaction::where('provider_gen_id', $result['order_id'])->first();
        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true,
            'provider_transaction_id' => $result['transaction_id'],
        ]);

        event(new TransactionConfirmed($transaction));
    }

    return response('OK', 200);
}
```

## Action Plan (Priority Order)

### Phase 1: Fix Integration Model (CRITICAL)
- [ ] Create migration: add `key`, `secret`, `webhook` columns, drop `credentials`
- [ ] Update Integration model: use plain columns, remove getCredential() methods
- [ ] Update IntegrationSeeder: set plain columns
- [ ] Update CashfreePaymentProvider: use `$integration->key` directly
- [ ] Update CashfreePayoutProvider: same fix
- [ ] Test: `php artisan migrate:fresh --seed` and verify integrations table

### Phase 2: Fix Transaction Model
- [ ] Create migration: add `provider_gen_session`, `provider_gen_link`, remove `checkout_url`
- [ ] Update Transaction fillable array
- [ ] Update factories to include new fields

### Phase 3: Fix Checkout Flow (Move to Laravel)
- [ ] Create Livewire CheckoutHome component
- [ ] Create CashfreeCheckout Livewire component
- [ ] Create Blade views
- [ ] Add web route: `/checkout/{transaction:uuid}`
- [ ] Test: Visit checkout URL, ensure Cashfree SDK loads

### Phase 4: Fix Payment/Payout Services
- [ ] Audit PaymentService vs PayoutService
- [ ] Ensure 70% similarity in structure
- [ ] Fix method signatures to match
- [ ] Use same DTO classes

### Phase 5: Apply Fingerprint Trait
- [ ] Search for `$user->id` in payment/wallet/transaction context
- [ ] Replace with `$user->fingerprint`
- [ ] Test: Ensure relationships work

### Phase 6: Fix All Tests
- [ ] Remove fake tests that pass without real API
- [ ] Create tests with real Cashfree sandbox credentials
- [ ] Test payment flow end-to-end
- [ ] Test payout flow end-to-end
- [ ] Test webhook handling
- [ ] Ensure 100% pass rate

### Phase 7: Manual Browser Testing
- [ ] Test wallet add money flow
- [ ] Test order checkout flow
- [ ] Test payout (cashgram, withdrawal)
- [ ] Test webhook callbacks
- [ ] Verify redirects work (success_url, failure_url)

## Files to Audit/Fix

### Models
- [x] Transaction.php (partially fixed by user)
- [ ] Integration.php (needs plain columns)
- [ ] Wallet.php (check fingerprint usage)

### Services
- [ ] app/Services/Payment/PaymentService.php
- [ ] app/Services/Payment/PayoutService.php
- [ ] app/Services/Payment/Providers/CashfreePaymentProvider.php
- [ ] app/Services/Payment/Providers/CashfreePayoutProvider.php

### Controllers
- [x] Transaction controllers (fixed by user)
- [ ] app/Http/Controllers/Api/Webhooks/CashfreeWebhookController.php (untested)
- [ ] WalletController.php (check fingerprint)

### Livewire (TO CREATE)
- [ ] app/Livewire/Checkout/CheckoutHome.php
- [ ] app/Livewire/Checkout/Providers/CashfreeCheckout.php
- [ ] resources/views/livewire/checkout/

### Migrations (TO CREATE)
- [ ] Fix integrations table: add key/secret/webhook, drop credentials
- [ ] Fix transactions table: add provider_gen_session/link, drop checkout_url

### Tests
- [ ] tests/Feature/Payment/ (all need real credentials)
- [ ] tests/Feature/Payout/ (all need real credentials)
- [ ] tests/Feature/Wallet/ (check if passing properly)

## Notes

- **DO NOT** commit until all tests pass
- **DO NOT** skip manual browser testing
- **DO NOT** make assumptions - ask user if unclear
- **DO** follow old_project patterns exactly
- **DO** use fingerprint trait everywhere
- **DO** maintain Payment/Payout service similarity
- **DO** document all changes in `.claude/ACTIVITY_LOG.md`

## User's Style Requirements

1. Use `$user->fingerprint` not `$user->id`
2. Keep `success_url` and `failure_url` in transactions
3. Follow old_project architecture (proven, battle-tested)
4. Integration has plain columns (key, secret, webhook)
5. Checkout is Laravel/Livewire side
6. Payment/Payout services share 70% code structure

---

**Last Updated:** 2026-01-02
**Next Action:** Start Phase 1 - Fix Integration Model
