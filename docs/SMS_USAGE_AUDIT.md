# SMS Usage Audit & DLT Alignment Report

This document audits the SMS template usage across the application, identifying where templates are used, how they are triggered, and which ones are currently missing or broken.

---

## 1. Master Template Status (Seeder vs. App)

| Template Slug | Seeder Line | Status in App | Trigger File & Line | Other Bugs Found |
|---------------|-------------|---------------|----------------------|------------------|
| `welcome` | 48 | **BROKEN** | `SmsService.php:140` | Bypasses DLT (sends raw string via `sendSingle`). |
| `otp-general` | 57 | **ACTIVE** | `SmsRequest.php:62` | OK (except `app_name` variable count). |
| `otp-transaction` | 65 | **MISSING** | N/A | Never triggered in the codebase. |
| `wallet-update` | 73 | **ACTIVE** | `WalletTransactionNotificationService.php:41` | OK. |
| `job-application-received` | 81 | **ACTIVE** | `JobApplicationNotificationService.php:44` | OK. |
| `job-interview-scheduled` | 89 | **MISSING** | N/A | Never triggered in the codebase. |
| `withdrawal-request` | 97 | **MISSING** | N/A | Trigger missing in `WalletController`. Variable mismatch: content uses `{#number#}`, variables list uses `amount`. |
| `withdrawal-processing` | 106 | **MISSING** | N/A | Trigger missing in `ProcessPayoutJob`. |
| `withdrawal-settled` | 115 | **MISSING** | N/A | Trigger missing in `ProcessPayoutJob`. |
| `withdrawal-failed` | 124 | **MISSING** | N/A | Trigger missing in `ProcessPayoutJob`. |
| `subscription-activated` | 134 | **BROKEN** | `SendSubscriptionActivatedNotifications.php:52` | **Slug Mismatch:** Code uses `subscription-status`, Seeder uses `subscription-activated`. |
| `subscription-expired` | 143 | **MISSING** | N/A | No cron job or listener triggers this. |
| `order-shipment-status` | 153 | **ACTIVE** | `OrderValidationService.php:361` | OK. |

---

## 2. Detailed Flow Analysis

### A. Welcome SMS (The Registration Flow)
*   **Trigger:** `RegisterController.php:198` calls `SmsService::sendWelcome()`.
*   **Current Bug:** `SmsService::sendWelcome()` (at `apiserver/app/Services/IntegrationServices/Sms/SmsService.php:140`) hardcodes the message string and sends it via `sendSingle()`.
*   **The Problem:** `sendSingle()` uses a non-DLT route. In production, this will likely be blocked.
*   **Fix:** Refactor `sendWelcome` to use `sendTemplateSingle($phone, 'welcome', [...])`.

### B. Wallet & Job Flows (The Success Cases)
*   **Wallet:** `WalletTransactionNotificationService.php:41` triggers `wallet-update` whenever a top-up is completed. This is the cleanest implementation in the app.
*   **Recruitment:** `JobApplicationNotificationService.php` triggers `job-application-received` at Lines 44 and 69 (for both initial application and payment confirmation).

### C. Subscription Flow (The Mismatch)
*   **Trigger:** `SendSubscriptionActivatedNotifications.php:52` (Listener).
*   **The Bug:** The listener requests `templateSlug: 'subscription-status'`. However, the `SmsTemplateSeeder.php` defines the slug as `subscription-activated` (Line 134).
*   **Result:** The `Fast2SmsProvider` fails to find the template in the DB and returns a "Template not found" error.

### D. Withdrawal Flow (The Total Gap)
*   **Status:** The seeder contains 4 withdrawal templates (`request`, `processing`, `settled`, `failed`).
*   **The Bug:** None of these are called in `apiserver/app/Http/Controllers/Api/WalletController.php` or `apiserver/app/Jobs/Wallet/ProcessPayoutJob.php`.
*   **Variable Bug:** The withdrawal templates in the seeder (Lines 97-124) use `{#number#}` in the content string but define `['amount']` in the variables array. This would cause a crash or empty value in Fast2SMS.

---

## 3. Critical Bugs (Non-`app_name` related)

1.  **Welcome Route Bypass:** `SmsService::sendWelcome` avoids the DLT template system entirely, making it illegal/unreliable in production DLT environments.
2.  **Subscription Slug Mismatch:** `subscription-status` (Code) vs `subscription-activated` (Seeder).
3.  **Withdrawal Implementation Gap:** Full set of payout notifications is missing from the payout logic.
4.  **Withdrawal Placeholder Mismatch:** `{#number#}` in content vs `amount` in variables list in `SmsTemplateSeeder.php`.

---

## 4. Implementation Guide

### To Fix "Welcome"
Refactor `SmsService.php:140` to use `sendTemplateSingle`.

### To Fix "Subscription"
Rename the slug in `SmsTemplateSeeder.php:134` from `subscription-activated` to `subscription-status`.

### To Fix "Withdrawals"
1.  Add `$smsService->sendTemplate(...)` inside `WalletController::withdraw` for the initial request.
2.  Add `$smsService->sendTemplate(...)` inside `ProcessPayoutJob::processPayoutResponse` for processing/settled/failed states.
3.  In `SmsTemplateSeeder.php`, change `{#number#}` to `{#amount#}` in all withdrawal content strings.

### To Fix `app_name` (User Suggestion)
Instead of passing `app_name` as a variable from the code, update `SmsTemplateSeeder.php` content strings to:
`... Welcome to ' . config('app.name') . '. ...'`
This removes the need for `{#app_name#}` placeholders and reduces the variable count for DLT.

---
*Report updated on: 2026-04-14*
