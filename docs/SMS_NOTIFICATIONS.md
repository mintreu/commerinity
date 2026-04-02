# SMS Template Register

## Status at a glance
- `sms_templates` currently holds 13 seeded entries plus the OTP template that powers the runtime `OtpManager`. All rows are created/updated by `Database\Seeders\SmsTemplateSeeder` and the seeded `template_id` values are placeholders that must be replaced once DLT sends the official template IDs.
- Every seeded template uses the `{#app_name#}` placeholder, which resolves to `config('app.name')` at runtime so there is no hardcoded brand name inside the SMS body.
- The database records store the DLT-friendly `message_id`, and Fast2SMS expects `template_code` to be the slug (e.g., `otp-verification`).

## Template registry
First-time DLT approval buyers should export the rows below under their categories. `Variables` list the pipe order that the Fast2SMS dashboard must expect.

### OTP templates
| Slug | Message ID | Body (DLT) | Variables | Trigger (current) | Recipient | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| `otp-verification` | `OTP_VER_001` | `{#otp#} is your OTP for {#app_name#}. Valid for {#validity#} minutes. Keep it private. - {#app_name#}` | `otp`, `validity`, `app_name` | `OtpManager::sendOtp()` → `SmsService::sendOtp()` → `SmsRequest::otp()` (mobile OTP path) | Mobile user requesting OTP (login/registration/reset) | TTL sourced from `config('auth.otp_ttl_minutes', 15)` and passed as `{#validity#}`. This is the only OTP slug currently executed in code. |
| `login-otp` | `OTP_LOGIN_001` | `{#otp#} is your OTP to login to {#app_name#}. Valid for {#validity#} minutes. Do not share with anyone. - {#app_name#}` | `otp`, `validity`, `app_name` | Reserved for login-only OTP flows once we introduce slug-based selectors | High-priority user | Seed ready even though we currently route every OTP through `otp-verification`. |
| `registration-otp` | `OTP_REG_001` | `{#otp#} is your OTP to verify your mobile number on {#app_name#}. Valid for {#validity#} minutes. - {#app_name#}` | `otp`, `validity`, `app_name` | Reserved for dedicated mobile verification during onboarding | New registrant | Reserved for a separate `purpose` tag when marketing wants two OTP variants. |
| `password-reset-otp` | `OTP_PWD_001` | `{#otp#} is your OTP to reset your password on {#app_name#}. Valid for {#validity#} minutes. Do not share. - {#app_name#}` | `otp`, `validity`, `app_name` | Reserved for password reset when we map reset flows to slug | Account owner resetting credentials | Security copy emphasises confidentiality. |
| `transaction-otp` | `OTP_TXN_001` | `{#otp#} is your OTP for transaction of Rs.{#amount#} on {#app_name#}. Valid for {#validity#} mins. Do not share. - {#app_name#}` | `otp`, `amount`, `validity`, `app_name` | Planned for high-value transactions once we implement transaction-specific OTP fanning | Wallet or order owner | Includes `{#amount#}` for auditing big transfers. |

### Transactional templates
| Slug | Message ID | Body (DLT) | Variables | Where it should fire | Recipient | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| `welcome` | `TXN_WELCOME_001` | `Welcome to {#app_name#}, {#name#}! Your account has been created. Start your journey with {#app_name#}. - {#app_name#}` | `app_name`, `name` | `RegisterController::sendWelcomeNotifications()` → `SmsService::sendWelcome()` (queued) | Newly registered user | Currently sent via ad-hoc string but the copy matches this template. For DLT, switch this notification to `SmsRequest` with slug `welcome` and `{#name#}` variables. |
| `wallet-credit` | `TXN_WCREDIT_001` | `Rs.{#amount#} credited to your {#app_name#} wallet. Txn ID: {#txn_id#}. New balance: Rs.{#balance#}. - {#app_name#}` | `amount`, `txn_id`, `balance`, `app_name` | Wallet top-up success (future adaptation of `WalletTransactionNotificationService::notifyTopupCompleted`) | Wallet owner | Message mirrors the current SMS body of the same service; once we route through templates, drop the inline string. |
| `wallet-debit` | `TXN_WDEBIT_001` | `Rs.{#amount#} debited from your {#app_name#} wallet. Txn ID: {#txn_id#}. Available balance: Rs.{#balance#}. - {#app_name#}` | `amount`, `txn_id`, `balance`, `app_name` | Wallet deduction/payout flows | Wallet owner | Placeholder for refunds, withdrawals or admin adjustments. |
| `commission-earned` | `TXN_COMM_001` | `Congratulations! You earned Rs.{#amount#} commission on {#type#}. Credited to your wallet. Total earnings: Rs.{#total#}. - {#app_name#}` | `amount`, `type`, `total`, `app_name` | Commission processor events | Sponsor/upline that earned commission | Match this template to the commission calculator once multi-level payouts are streamed. |
| `withdrawal-processed` | `TXN_WDRAW_001` | `Withdrawal of Rs.{#amount#} processed. Ref: {#ref_id#}. Amount will be credited to your bank in 24-48 hrs. - {#app_name#}` | `amount`, `ref_id`, `app_name` | Withdrawal payout pipeline | User who withdrew funds | Works with withdrawal cron; no live sender yet. |
| `kyc-approved` | `TXN_KYC_001` | `Dear {#name#}, your KYC verification is complete. You now have full access to all features on {#app_name#}. - {#app_name#}` | `name`, `app_name` | KYC approval flow | Verified member | Template keeps the tone consistent across compliance notifications. |
| `subscription-activated` | `TXN_SUB_001` | `Your {#plan#} subscription is now active on {#app_name#}. Valid till {#expiry#}. Thank you for upgrading! - {#app_name#}` | `plan`, `expiry`, `app_name` | `SendSubscriptionActivatedNotifications` currently sends a similar ad-hoc SMS | Subscription owner | Swap the listener to use this template when DLT copy is live. |
| `job-application-received` | `TXN_JOB_001` | `Dear {#name#}, your application for {#position#} has been received. Application ID: {#app_id#}. We will contact you soon. - {#app_name#}` | `name`, `position`, `app_id`, `app_name` | `JobApplicationNotificationService::notifyApplied()` once payment clears | Candidate | Template-ready alternative to the current manual message when the job module wants DLT compliance. |

### Promotional templates
| Slug | Message ID | Body (DLT) | Variables | Trigger | Recipient | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| `referral-bonus` | `PROMO_REF_001` | `Great news! Your referral {#referred_name#} joined {#app_name#}. Rs.{#bonus#} bonus added to your wallet. Keep sharing! - {#app_name#}` | `referred_name`, `bonus`, `app_name` | Referral reward flow (CRM/marketing automation) | Referrer | Use for automated referral celebration SMS when the bonus posts. |

## Runtime SMS flows (non-template bodies)
These are currently sent as inline strings via `NotificationSmsSenderInterface` or direct `SmsService` calls and are not yet recorded in `sms_templates`. Switch to the DLT templates above once each copy is approved.

| Flow | Trigger | Recipient | Body | Notes |
| --- | --- | --- | --- | --- |
| Welcome general SMS | `RegisterController::sendWelcomeNotifications()` (queued) | Newly registered user | `Welcome to {config('app.name')}, {name}! Your account has been created successfully. Start exploring and enjoy exclusive benefits!` | Should become the `welcome` template with `{#name#}` when DLT approval is required. |
| Order confirmation | `OrderValidationService::sendOrderSmsNotification()` after invoice generation | Order customer | `Your order {order_number} has been confirmed. Your invoice is now available. View details: {client_url}/notifications` | Replace with the `transactional` template (e.g., a trimmed `order-confirmation`) once content is signed-off. |
| Subscription activation | `SendSubscriptionActivatedNotifications::handle()` (queued listener) | Subscription owner | `Your {stage} ({level}) subscription is active. Visit {config('app.client_url', config('app.url'))} to unlock deals.` | Template `subscription-activated` is the DLT-ready sibling. |
| Job application (payment required) | `JobApplicationNotificationService::notifyApplied()` (requires payment) | Applicant | `Application No {applicationNumber} created for {jobTitle}. Please complete payment to submit.` | Swap to `job-application-received` template when payment notification must match DLT copy. |
| Job application (payment not required) | Same method (no payment) | Applicant | `Application No {applicationNumber} submitted successfully for {jobTitle}.` | Same as above but acknowledges auto submission. |
| Job application payment confirmed | `JobApplicationNotificationService::notifyPaymentConfirmed()` | Applicant | `Payment confirmed for {jobTitle}. Application No {applicationNumber} is submitted.` | Could be its own template if marketing wants a separate approval. |
| Wallet top-up success | `WalletTransactionNotificationService::notifyTopupCompleted()` | Wallet owner | `Wallet top-up successful. Amount {amount}. Ref {reference}. Balance {balance}.` | Template `wallet-credit` is the DLT copy that mirrors this string. |

## DLT readiness checklist
1. Update `template_id` with the actual Fast2SMS (or provider) template ID and flip `is_dlt_approved` to `true` once approval completes.
2. Use the slug (e.g., `otp-verification`, `welcome`) as `template_code` when building `SmsRequest` so Fast2SMS knows the variable pipe order listed above.
3. Keep `{#app_name#}` in every body; the code injects `config('app.name')` so the message stays current even if the brand name changes.
4. For the runtime flows above, replace the inline bodies with `SmsTemplate` lookups and pipe the `variables` listed in the tables.
