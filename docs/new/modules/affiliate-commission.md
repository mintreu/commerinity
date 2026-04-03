# Affiliate & Commission

## Purpose
Growth engine via referral tree, commission, disbursement, and fund views.

## Primary Flow
- Route entry: `apiserver/routes/api.php:146-157`, `163-169`
- Tree/stats/upline: `apiserver/app/Http/Controllers/Api/AccountController.php:79,90,103,121`
- Commission read APIs: `apiserver/app/Http/Controllers/Api/CommissionController.php:29,87,163,200`
- Funds/ledger/disbursements: `apiserver/app/Http/Controllers/Api/Affiliate/*`

## Frontend
- `client/app/pages/affiliate/*`
- `client/app/pages/network/*`
- `client/app/composables/useAffiliateFunds.ts`, `useAffiliateLedger.ts`, `useNetwork.ts`

## Tests
- `apiserver/tests/Feature/Affiliate/*`
- `client/tests/api/affiliate.test.ts`

## ? Notes
- Commission settlement and wallet-credit chain should stay idempotent and auditable.

