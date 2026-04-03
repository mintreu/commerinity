# Cashfree Payouts & Cashgram API Reference
**Source**: Official Cashfree Docs
**Date**: 2025-12-31

---

## Cashgram (Payout Links)

### Create Cashgram

**Endpoint**: `POST https://payout-api.cashfree.com/payout/v1/createCashgram`

**Headers**:
```
Authorization: Bearer <TOKEN>
Content-Type: application/json
```

**Request**:
```json
{
  "cashgramId": "CASHGRAM_123",
  "amount": 5000,
  "phone": "+919999999999",
  "email": "user@example.com",
  "name": "John Doe",
  "remark": "Refund for order #123",
  "notifyCustomer": 1,
  "expireBy": "2026-01-31T23:59:59Z"
}
```

**Response**:
```json
{
  "status": "SUCCESS",
  "message": "Cashgram created successfully",
  "data": {
    "cashgramId": "CASHGRAM_123",
    "link": "https://cashfree.com/link/abc123",
    "referenceId": "REF_123"
  }
}
```

### Get Cashgram Status

**Endpoint**: `GET https://payout-api.cashfree.com/payout/v1/getCashgramStatus?cashgramId=CASHGRAM_123`

**Response**:
```json
{
  "status": "SUCCESS",
  "data": {
    "cashgramId": "CASHGRAM_123",
    "status": "CLAIMED", // UNCLAIMED, CLAIMED, EXPIRED
    "amount": 5000,
    "claimedAt": "2025-12-31T10:00:00Z"
  }
}
```

---

## Payouts - Beneficiaries

### Create Beneficiary

**Endpoint**: `POST https://payout-api.cashfree.com/payout/v1/addBeneficiary`

**Request**:
```json
{
  "beneficiaryId": "BEN_123",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+919999999999",
  "bankAccount": "1234567890",
  "ifsc": "HDFC0001234",
  "vpa": "john@upi"
}
```

### Get Beneficiary

**Endpoint**: `GET https://payout-api.cashfree.com/payout/v1/getBeneficiary?beneficiaryId=BEN_123`

---

## Payouts - Transfers

### Single Transfer

**Endpoint**: `POST https://payout-api.cashfree.com/payout/v1/requestTransfer`

**Request**:
```json
{
  "beneficiaryId": "BEN_123",
  "amount": 50000,
  "transferId": "TXN_123",
  "transferMode": "BANK_TRANSFER", // BANK_TRANSFER, UPI, CARD, CASHGRAM
  "remark": "Withdrawal request"
}
```

### Batch Transfer

**Endpoint**: `POST https://payout-api.cashfree.com/payout/v1/requestBatchTransfer`

**Request**:
```json
{
  "batchId": "BATCH_123",
  "batch": [
    {
      "beneficiaryId": "BEN_1",
      "amount": 1000,
      "transferId": "TXN_1"
    },
    {
      "beneficiaryId": "BEN_2",
      "amount": 2000,
      "transferId": "TXN_2"
    }
  ]
}
```

### Get Transfer Status

**Endpoint**: `GET https://payout-api.cashfree.com/payout/v1/getTransferStatus?transferId=TXN_123`

**Response**:
```json
{
  "status": "SUCCESS",
  "data": {
    "transferId": "TXN_123",
    "status": "SUCCESS", // PENDING, SUCCESS, FAILED, REVERSED
    "utr": "123456789012",
    "amount": 50000
  }
}
```

---

## Authentication (Payouts)

**Endpoint**: `POST https://payout-api.cashfree.com/payout/v1/authorize`

**Request**:
```json
{
  "clientId": "YOUR_CLIENT_ID",
  "clientSecret": "YOUR_CLIENT_SECRET"
}
```

**Response**:
```json
{
  "status": "SUCCESS",
  "data": {
    "token": "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiresIn": 3600
  }
}
```

---

## Environments

- **Production**: `payout-api.cashfree.com`
- **Test/Sandbox**: `payout-gamma.cashfree.com`

---

## Webhooks for Payouts

Webhook headers:
```
x-webhook-signature: sha256=...
x-webhook-timestamp: 1704034200
```

Events:
- `TRANSFER_SUCCESS`
- `TRANSFER_FAILED`
- `TRANSFER_REVERSED`
- `CASHGRAM_CLAIMED`
- `CASHGRAM_EXPIRED`

Response: Plain text "OK" with HTTP 200.
