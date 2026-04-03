# Current Issues

## 1. Tests hit real Mailtrap quota (SMTP 550/354 failure)
- **What happened:** `php artisan test --filter=AffiliateEndToEndTest` (and any mail-dependent scenario) failed with `Expected response code "354" but got code "550" ... Too many emails per second.` The failure was thrown deep inside `vendor/symfony/mailer/Transport/Smtp/SmtpTransport.php`.
- **Why it's an issue:** The test suite should never depend on an external SMTP server. Using the real Mailtrap SMTP credential also consumes the provider's rate limit, making the test suite flaky and blocked.
- **How we fixed it:** 
  1. `Tests\TestCase::setUp()` already fakes `Mail`, but `SmsService::sendOtp()` used the `'smtp'` mailer when a `$userId` was supplied. We now always call `SmsRequest::otp()` so every OTP path inherits the fake and the new `validity` variable.
  2. `config/mail.php` now computes the default mailer once, exposes it as `$defaultMailer`, and routes the `'smtp'` entry through `'array'` transport whenever `MAIL_MAILER=array`. This ensures any explicit `Mail::mailer('smtp')` call resolves to the safe transport while the environment is set to testing.
  3. Added a new OTP template (`otp-verification`) whose body only consumes the variables that the code actually passes (`otp`, `validity`, `app_name`).
- **Why it counts as an error:** Failing tests prevented us from validating any other code; fixing the mail transport restores confidence for the entire suite.
