# Wallet, Payout, Transaction

## Business Purpose
- Core financial layer for user balance, transfers, top-up, withdrawal.
- Ensures traceable ledger entries and beneficiary-based payout flows.

## Backend Logic
- Wallet endpoints: balance, stats, history, send/withdraw/pay.
- PIN + security question + reset token sequence for high-risk actions.
- Beneficiary account CRUD + validation (IFSC etc.) before withdrawals.
- Payout abstractions include provider integrations and fallback strategies.

## Key Backend Files
- Controllers: `apiserver/app/Http/Controllers/Api/WalletController.php`
- Controllers: `apiserver/app/Http/Controllers/Api/PayoutController.php`
- Controllers: `apiserver/app/Http/Controllers/Api/BeneficiaryAccountController.php`
- Services: `apiserver/app/Services/Wallet/*`
- Services: `apiserver/app/Services/IntegrationServices/Payout/*`
- Models: `apiserver/app/Models/*Wallet*`, `*Transaction*`, `*Beneficiary*`
- Filament: Wallet/Transaction/Beneficiary resources

## Frontend
- `client/app/pages/wallet/*`
- `client/app/composables/useWallet.ts`
- `client/app/pages/dashboard/earnings.vue`

## Tests
- `apiserver/tests/Feature/WalletTest.php`
- `apiserver/tests/Feature/WalletTopupCheckoutFlowTest.php`
- `apiserver/tests/Feature/CompleteWalletFlowTest.php`
- `apiserver/tests/Feature/Payment/*` (gateway + payout provider tests)
- `client/tests/api/wallet.test.ts`

## ? Potential Issues / Confusion
- <span style="color:red;font-size:1.1em;"><strong>Provider mode drift risk: if integration config flips between quick-mode and DLT/provider-specific mode, transactions can pass while notifications fail silently.</strong></span>
- <span style="color:red;font-size:1.1em;"><strong>PIN reset path must be monitored for brute-force/rate-limit completeness across all endpoints.</strong></span>

