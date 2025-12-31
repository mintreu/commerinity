# Cashfree Complete Implementation Guide
**Date**: 2025-12-31
**Status**: Complete Payment & Payout Integration

---

## 🎯 Overview

This project implements complete Cashfree integration for:
1. **Payment Gateway** - Accept payments (UPI, Card, Netbanking, Wallet)
2. **Payouts** - Send money to bank accounts, UPI, cards
3. **Cashgram** - One-time payout links
4. **SecureID** - KYC verification

---

## 📦 MCP Server Configuration

### Remote MCP (Recommended)
Use Cashfree's hosted MCP server:

```json
{
  "mcpServers": {
    "cashfree-remote": {
      "url": "https://mcp.cashfree.com/v1/sse",
      "headers": {
        "x-cf-api-key": "YOUR_API_KEY",
        "x-cf-api-secret": "YOUR_API_SECRET"
      }
    }
  }
}
```

### Local MCP
```json
{
  "mcpServers": {
    "cashfree": {
      "command": "node",
      "args": ["C:/laragon/www/mintreu/server/commerinity/cashfree-mcp/src/index.js"],
      "env": {
        "PAYMENTS_APP_ID": "your_pg_client_id",
        "PAYMENTS_APP_SECRET": "your_pg_client_secret",
        "PAYOUTS_APP_ID": "your_payouts_client_id",
        "PAYOUTS_APP_SECRET": "your_payouts_client_secret",
        "ENV": "sandbox"
      }
    }
  }
}
```

---

## 💰 PAYMENT GATEWAY (Incoming Payments)

### Flow Diagram
```
User → Frontend Checkout → Cashfree Payment Page → Webhook/Polling → Backend Verification → Wallet Updated
```

### Step 1: Create Order
```php
// POST /api/payments/create-order
$response = CashfreePaymentProvider::initiate(
    new PaymentInitiateRequest(
        amountInPaisa: 50000, // ₹500
        transactionId: 'TXN-'.Str::random(12),
        customerPhone: '+919999999999',
        customerEmail: 'user@example.com',
        purpose: 'Wallet TopUp',
        callbackUrl: route('payment.callback'),
        expiresInMinutes: 30
    )
);
```

### Step 2: Frontend Payment
```javascript
// Load Cashfree SDK
const cashfree = new Cashfree({ mode: 'sandbox' });

// Show payment UI
cashfree.checkout({
    paymentSessionId: response.payment_session_id,
    returnUrl: 'https://yoursite.com/checkout/{transaction}?status=success'
});
```

### Step 3: Verify Payment (3 Ways)

**A. Webhook (Primary)**
- Endpoint: `POST /api/webhooks/cashfree`
- Returns: Plain text "OK" with HTTP 200
- **CRITICAL**: Cashfree requires plain text, NOT JSON

**B. Polling (Fallback)**
```javascript
// Frontend polls every 3 seconds
async function checkStatus() {
    const response = await $fetch(`/api/checkout/${transactionId}/status`);
    if (response.data.is_verified) {
        // Payment confirmed!
    }
}
```

**C. Manual Verification API**
```php
// POST /api/checkout/{transaction}/verify
$response = CheckoutController::verify($transaction);
// Calls Cashfree API to verify payment status
```

### Step 4: Handle Completion
```php
// Event: PaymentCompleted
// Listener: HandlePaymentCompleted
// - Wallet TopUp → Update balance
// - Subscription → Activate membership
// - JobApplication → Submit application
```

### Payment Methods Supported
- UPI (`upi`)
- Cards (`card`)
- Netbanking (`netbanking`)
- Wallet (`wallet`)

---

## 💸 PAYOUTS (Outgoing Payments)

### Flow Diagram
```
Admin → Select Beneficiary → Initiate Payout → Cashfree → Bank/UPI → Beneficiary
                                                    ↓
                                            Webhook Callback
                                                    ↓
                                            Update Transaction Status
```

### Step 1: Create Beneficiary
```php
// POST /api/beneficiaries
$beneficiary = BeneficiaryAccount::create([
    'accountable_type' => User::class,
    'accountable_id' => $user->id,
    'type' => BeneficiaryTypeCast::BANK,
    'holder_name' => 'John Doe',
    'account_number' => '1234567890',
    'ifsc_code' => 'HDFC0001234',
    'bank_name' => 'HDFC Bank',
    'status' => BeneficiaryStatusCast::PENDING,
]);

// Verify beneficiary (manual or via API)
$beneficiary->verify(); // Sets status to VERIFIED
```

### Step 2: Beneficiary Observer Rules
```php
// Once VERIFIED:
// - Cannot edit bank/UPI details (immutable)
// - Only is_default can be changed
// - Automatically synced to Cashfree Payouts
```

### Step 3: Initiate Payout
```php
// POST /api/payouts
$response = CashfreePayoutProvider::initiate(
    new PayoutRequest(
        beneficiaryId: $beneficiary->uuid,
        amountInPaisa: 50000, // ₹500
        transferMode: 'BANK_TRANSFER',
        purpose: 'Withdrawal',
        referenceId: 'WD-'.Str::random(8)
    )
);
```

### Step 4: Check Payout Status
```php
// GET /api/payouts/{transferId}/status
$status = CashfreePayoutProvider::checkStatus($transferId);
// Returns: PENDING, SUCCESS, FAILED, REVERSED
```

---

## 🎫 CASHGRAM (Payout Links)

### Use Case
Send payout links to users who can claim via their preferred method (bank/UPI/wallet).

### Create Cashgram
```php
// POST /api/cashgram
$cashgram = CashfreePayoutProvider::createCashgram([
    'cashgramId' => 'CASHGRAM-'.Str::random(8),
    'amount' => 50000,
    'phone' => '+919999999999',
    'email' => 'user@example.com',
    'name' => 'John Doe',
    'expireBy' => now()->addDays(7),
    'notifyCustomer' => true,
]);

// Returns link like: https://cashfree.com/link/abc123
```

### Check Cashgram Status
```php
// GET /api/cashgram/{cashgramId}/status
$status = CashfreePayoutProvider::getCashgramStatus($cashgramId);
// Returns: UNCLAIMED, CLAIMED, EXPIRED
```

---

## 🔄 REFUNDS

### Full Refund
```php
// POST /api/payments/{orderId}/refund
$refund = CashfreePaymentProvider::refund(
    transactionId: $orderId,
    amountInPaisa: $fullAmount,
    reason: 'Customer request'
);
```

### Partial Refund
```php
// Refund specific amount
$refund = CashfreePaymentProvider::refund(
    transactionId: $orderId,
    amountInPaisa: 25000, // ₹250 of ₹500
    reason: 'Partial refund'
);
```

---

## 📋 WEBHOOKS

### Payment Webhook
```
POST /api/webhooks/cashfree
Content-Type: application/json
x-webhook-signature: sha256=...
x-webhook-timestamp: 1704034200
```

### Payout Webhook
```
POST /api/webhooks/cashfree/payout
Events: TRANSFER_SUCCESS, TRANSFER_FAILED, TRANSFER_REVERSED
```

### Webhook Response (CRITICAL!)
```php
// MUST return plain text "OK" with HTTP 200
return response('OK', 200);
// NOT JSON, NOT 201, NOT other codes
```

---

## ⏱️ TIMING REFERENCE

| Action | Typical Time |
|--------|--------------|
| Payment confirmation (webhook) | 5-30 seconds |
| Payment confirmation (polling) | 10-60 seconds |
| Payout to bank | Instant to 2 hours |
| Payout to UPI | Instant |
| Cashgram claim | User-dependent |
| Refund processing | 5-7 business days |

---

## 🔐 SECURITY

### Webhook Verification
```php
$signature = $request->header('x-webhook-signature');
$timestamp = $request->header('x-webhook-timestamp');
$rawBody = $request->getContent();

$computed = base64_encode(
    hash_hmac('sha256', $timestamp.$rawBody, $webhookSecret, true)
);

return hash_equals($computed, $signature);
```

### Idempotency
Use `x-idempotency-key` header for all critical operations:
```php
'x-idempotency-key' => $request->header('x-idempotency-key', Str::uuid())
```

---

## 📁 Key Files

### Backend
```
apiserver/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── CheckoutController.php      # Payment verification
│   │   └── Webhooks/CashfreeWebhookController.php
│   ├── Services/Payment/
│   │   ├── PaymentService.php
│   │   ├── Providers/CashfreePaymentProvider.php
│   │   └── Providers/CashfreePayoutProvider.php
│   ├── Models/
│   │   ├── Transaction.php
│   │   └── BeneficiaryAccount.php
│   └── Observers/
│       └── BeneficiaryAccountObserver.php
└── routes/api.php
```

### Frontend
```
client/
└── app/
    └── pages/
        └── checkout/
            └── [transaction].vue  # Payment page with polling
```

---

## 🧪 Testing

### Test Card (Sandbox)
```
Card: 4111 1111 1111 1111
Expiry: Any future date
CVV: Any 3 digits
OTP: Any 6 digits
```

### Test UPI
```
UPI ID: success@upi
```

---

## 🚀 Production Checklist

- [ ] Webhook URL whitelisted in Cashfree dashboard
- [ ] Plain text "OK" response confirmed
- [ ] Webhook signature verification enabled
- [ ] Polling fallback implemented
- [ ] Beneficiary observer registered
- [ ] Payment status enum updated
- [ ] Webhook tests passing
- [ ] Error handling for all scenarios

---

## 📞 Useful MCP Tools

```bash
# Search docs
cashfree_search(query: "webhook verification")

# Create payment link
cashfree_create_payment_link(amount: 500, phone: "9999999999")

# Check transfer status
cashfree_get_transfer_status_v2(transferId: "TXN123")

# Create cashgram
cashfree_create_cashgram(amount: 1000, phone: "9999999999")
```
