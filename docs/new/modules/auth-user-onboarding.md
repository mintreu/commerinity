# Auth, User, Onboarding

## Business Purpose
- User acquisition + secure login + profile readiness before deeper modules.
- Onboarding ensures user profile + contact verification + baseline completeness.

## Backend Logic
- OTP send/verify + registration + login + reset password routes.
- Sanctum-protected `/user`, profile update, password change.
- Onboarding status and completion endpoints.
- Address + KYC flows are coupled to onboarding progression.

## Key Backend Files
- Controllers: `apiserver/app/Http/Controllers/Api/Auth/*`
- Controllers: `apiserver/app/Http/Controllers/Api/ProfileController.php`
- Controllers: `apiserver/app/Http/Controllers/Api/OnboardingController.php`
- Controllers: `apiserver/app/Http/Controllers/Api/AddressController.php`
- Controllers: `apiserver/app/Http/Controllers/Api/KycController.php`
- Services: `apiserver/app/Services/UserServices/*`
- Models: `apiserver/app/Models/*User*`, `*Kyc*`, `*Address*`

## Frontend
- `client/app/pages/auth/*`
- `client/app/pages/profile/*`
- `client/app/pages/onboarding/index.vue`
- `client/app/pages/addresses/index.vue`
- `client/app/composables/useOnboarding.ts`
- `client/app/composables/useGeoData.ts`

## Tests
- `apiserver/tests/Feature/Auth/*`
- `apiserver/tests/Feature/OnboardingFlowTest.php`
- `apiserver/tests/Feature/AddressApiTest.php`
- `apiserver/tests/Feature/KycApiTest.php`
- `client/tests/api/auth.test.ts`

## ? Potential Issues / Confusion
- <span style="color:red;font-size:1.1em;"><strong>OTP/login/registration UX overlaps with multiple OTP templates. Mapping must stay synced with DLT-approved final template keys.</strong></span>
- <span style="color:red;font-size:1.1em;"><strong>Profile completeness and KYC gating rules should be centrally documented in code (single policy source) to avoid frontend/backend mismatch.</strong></span>

