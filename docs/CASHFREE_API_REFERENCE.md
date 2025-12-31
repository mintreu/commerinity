# Cashfree Payments API Reference
**Source**: Official Cashfree Docs (2025-01-01 API Version)
**Date**: 2025-12-31

---

## Order Create

**Endpoint**: `POST https://api.cashfree.com/pg/orders` (Production)
**Endpoint**: `POST https://sandbox.cashfree.com/pg/orders` (Sandbox)

### Headers
```
x-client-id: YOUR_API_KEY
x-client-secret: YOUR_SECRET
x-api-version: 2025-01-01
```

### Required Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `order_amount` | number | Bill amount (min: 1, up to 2 decimals) |
| `order_currency` | string | Currency code (default: "INR") |
| `customer_details` | object | Customer identification |

### Customer Details (Required)
```json
{
  "customer_id": "unique_id",
  "customer_phone": "+919999999999"
}
```

### Optional Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `order_id` | string | Your reference (3-45 chars, alphanumeric, underscore, hyphen) |
| `order_note` | string | Reference note (3-200 chars) |
| `order_expiry_time` | string | ISO8601 timestamp |
| `order_meta` | object | Controls payment methods and return URL |

### Order Meta
```json
{
  "return_url": "https://yoursite.com/payment-success?order_id={order_id}",
  "notify_url": "https://yoursite.com/api/webhook/cashfree"
}
```

### Example Request
```json
POST https://sandbox.cashfree.com/pg/orders
{
  "order_id": "ORDER_123",
  "order_amount": 500.00,
  "order_currency": "INR",
  "customer_details": {
    "customer_id": "USER_456",
    "customer_phone": "+919999999999"
  },
  "order_meta": {
    "return_url": "https://yoursite.com/payment-success",
    "notify_url": "https://yoursite.com/api/webhook/cashfree"
  }
}
```

### Response (200)
```json
{
  "cf_order_id": 2149460581,
  "order_id": "ORDER_123",
  "order_amount": 500.00,
  "order_currency": "INR",
  "order_status": "ACTIVE",
  "payment_session_id": "session_abc123xyz",
  "order_expiry_time": "2025-12-26T12:00:00Z"
}
```

### HTTP Status Codes
| Code | Description |
|------|-------------|
| 200 | Order created |
| 400 | Invalid request |
| 401 | Auth failed |
| 409 | Duplicate order |
| 429 | Rate limit |

---

## Order Pay (Payment Session)

**Endpoint**: `POST https://api.cashfree.com/pg/orders/sessions`

### Request
```json
{
  "payment_session_id": "session_abc123xyz",
  "payment_method": {
    "upi": {
      "upi_id": "user@upi"
    }
  }
}
```

### Response
```json
{
  "payment_amount": 500.00,
  "cf_payment_id": 123456,
  "payment_method": "upi",
  "channel": "link",
  "action": "link",
  "data": {
    "url": "https://..."
  }
}
```

---

## Payment Status Check

**Endpoint**: `GET https://api.cashfree.com/pg/orders/{order_id}`

### Response
```json
{
  "cf_order_id": 2149460581,
  "order_id": "ORDER_123",
  "order_amount": 500.00,
  "order_status": "PAID",
  "payments": [
    {
      "cf_payment_id": 123456,
      "payment_status": "SUCCESS",
      "payment_amount": 500.00
    }
  ]
}
```

---

## Webhook Verification

### Webhook Headers
```
x-cf-signature: sha256=...
x-cf-hmac-timestamp: 1704034200
```

### Signature Verification (Node.js Example)
```javascript
const crypto = require('crypto');

function verifyWebhook(data, signature, timestamp, secret) {
  const payload = timestamp + JSON.stringify(data);
  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');

  return signature === expectedSignature;
}
```

### Webhook Events
- `PAYMENT_SUCCESS` - Payment completed
- `PAYMENT_FAILED` - Payment failed
- `ORDER_EXPIRED` - Order timed out
- `REFUND_PROCESSED` - Refund completed

### Webhook Response
Return **HTTP 200 OK** with plain text `OK` to acknowledge receipt.

---

## Refunds

**Endpoint**: `POST https://api.cashfree.com/pg/refunds`

### Request
```json
{
  "refund_id": "REFUND_123",
  "payment_id": "cf_payment_123",
  "refund_amount": 500.00,
  "refund_note": "Customer request"
}
```

### Response
```json
{
  "cf_refund_id": 789,
  "refund_status": "SUCCESS"
}
```

---

## Settlements

**Endpoint**: `GET https://api.cashfree.com/settlements`

### Response
```json
{
  "settlements": [
    {
      "settlement_id": "SET_123",
      "amount": 10000.00,
      "status": "COMPLETED",
      "created_at": "2025-12-30T00:00:00Z"
    }
  ]
}
```

---

## Cashfree.js SDK Integration

### Include SDK
```html
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
```

### Initialize
```javascript
const cashfree = new Cashfree({
  mode: 'sandbox' // or 'production'
});
```

### Checkout
```javascript
cashfree.checkout({
  paymentSessionId: 'session_abc123xyz',
  returnUrl: 'https://yoursite.com/payment-success'
}).then(result => {
  if (result.error) {
    console.error(result.error);
  }
  // Success handled via redirect to returnUrl
});
```

---

## Important Notes

1. **payment_session_id** is CRITICAL - store it in transaction record
2. **return_url** is where user lands after payment
3. **notify_url** is webhook endpoint for background notification
4. Always verify webhook signature before processing
5. Return 200 OK immediately after webhook receipt
6. Order expires after ~45 minutes (configurable)

---

## Common Issues

### Webhook Not Working
- Return **plain text "OK"** with **HTTP 200**
- Do NOT return JSON
- Do NOT return 201 or other codes
- Must respond within 5 seconds

### Payment Verification Without Webhook
```php
// Check order status via API
$response = $cashfreeService->getOrder($orderId);

if ($response['order_status'] === 'PAID') {
    // Process payment
}
```
