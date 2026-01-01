App Analysis Report  
Date: 2026-01-01

## 1. Scope and Methodology
- Backend: Laravel apiserver (wallet, checkout, address/onboarding, beneficiaries).
- Frontend: Nuxt client (wallet pages, checkout page, basic API tests).
- Signals used:
  - Existing automated tests (Pest, Vitest).
  - Type/interface definitions and enums.
  - Traits/factories and how tests use them.
  - Frontend pages and composables for critical flows (wallet, checkout, addresses).

This report focuses on:
- Concrete bugs and failing/fragile tests.
- Mismatches between backend contracts and frontend usage.
- Clearly incomplete or stubbed areas.
- High‑level completion status and improvement recommendations.

---

## 2. Backend Issues and Failed / Fragile Tests

### 2.1 Wallet Checkout Data Structure
- **Type**: Potential test fragility / contract dependency  
- **File**: `tests/Feature/CompleteWalletFlowTest.php`  
- **Path**: `apiserver/tests/Feature/CompleteWalletFlowTest.php`  
- **Lines**: ~714–773  
- **Context**:  
  - The test `checkout data structure includes all payment session fields` asserts the JSON shape for `/api/checkout/{transaction}`.
  - The actual implementation is in `app/Http/Controllers/Api/CheckoutController.php`.

**Relevant test code**  
- File: `apiserver/tests/Feature/CompleteWalletFlowTest.php`  
- Lines: 714–773  
- Contract asserted:
  - `data.transaction` must include:
    - `uuid`, `amount`, `amount_formatted`, `purpose`, `status`, `type`, `expires_at`, `is_verified`.
  - `data.payment` must include:
    - `provider`, `provider_slug`, `payment_session_id`, `is_sandbox`.
  - `data.customer` present as an object.
  - `data.redirect` with `success_url`, `failure_url`.

**Relevant implementation**  
- File: `apiserver/app/Http/Controllers/Api/CheckoutController.php`  
- Lines: ~36–105  
- Implementation returns:
  - `transaction` with `uuid`, `amount`, `amount_formatted`, `amount_in_rupees`, `purpose`, `description`, `status`, `type`, `expires_at`, `is_verified`.
  - `payment` with `provider`, `provider_slug`, `payment_session_id`, `is_sandbox`.
  - `customer` from `$transaction->metadata['customer'] ?? null`.
  - `redirect.success_url` and `redirect.failure_url`.

**Status / Risk**
- Structure now looks aligned between test and controller.
- This area is **sensitive to future changes** in `CheckoutController::show()`, because the test asserts a fairly strict structure.

**How to fix / keep stable**
- When changing checkout responses, always update both:
  - `CheckoutController::show()`  
  - `CompleteWalletFlowTest::test('checkout data structure includes all payment session fields')`
- Prefer adding fields (non‑breaking) rather than removing or renaming existing ones used by:
  - Frontend checkout page: `client/app/pages/checkout/[transaction].vue`.

---

### 2.2 Address Type Enum vs Trait and Tests

#### 2.2.1 Missing AddressTypeCast values
- **Type**: Enum/trait inconsistency (runtime bug risk)  
- **Files**:
  - Enum: `apiserver/app/Casts/AddressTypeCast.php` (lines ~18–57)  
  - Trait: `apiserver/app/Models/Traits/HasAddress.php` (lines ~42–96)

**Current enum definition**  
- File: `apiserver/app/Casts/AddressTypeCast.php`  
- Lines: 18–22  
- Values:
  - `HOME = 'home'`
  - `WORK = 'work'`
  - `OTHER = 'other'`

**Current trait usage**  
- File: `apiserver/app/Models/Traits/HasAddress.php`  
- Lines: 42–95  
- Methods:
  - `homeAddress()` filters on `AddressTypeCast::HOME->value`.
  - `workAddress()` filters on `AddressTypeCast::WORK->value`.
  - `otherAddress()` filters on `AddressTypeCast::OTHER->value`.
  - `deliveryAddresses()` filters on `AddressTypeCast::DELIVERY->value`.
  - `pickupAddresses()` filters on `AddressTypeCast::PICKUP->value`.
  - `hubAddresses()` filters on `AddressTypeCast::HUB->value`.
  - `servicePointAddresses()` filters on `AddressTypeCast::SERVICE_POINT->value`.

**Problem**
- Enum defines **only** `HOME`, `WORK`, `OTHER`.  
- Trait and tests reference `DELIVERY`, `PICKUP`, `HUB`, `SERVICE_POINT`, which **do not exist** in the enum.
- This is a latent bug that will surface as:
  - PHP fatal error when accessing e.g. `AddressTypeCast::DELIVERY`.

**How to fix (recommended)**
- Extend `AddressTypeCast` enum to include all types used by `HasAddress`:
  - `DELIVERY`, `PICKUP`, `HUB`, `SERVICE_POINT`, and any other types used in address factories or API.
- Example shape (adapt to desired labels/colors):
  - Add cases: `case DELIVERY = 'delivery'; case PICKUP = 'pickup'; case HUB = 'hub'; case SERVICE_POINT = 'service_point';`
  - Extend `getColor()`, `getIcon()`, `getLabel()` match expressions accordingly.

**Alternative (not recommended)**
- Remove or change trait methods to only use existing enum values.
- This would reduce functionality (no separate delivery/pickup/hub/service point grouping).

---

#### 2.2.2 HasAddressTest uses undefined enum value
- **Type**: Failing or future‑failing feature test  
- **File**: `apiserver/tests/Feature/Models/Traits/HasAddressTest.php`  
- **Lines**: ~18–34

**Test code**
- Creates three addresses:
  - `type: AddressTypeCast::HOME`
  - `type: AddressTypeCast::WORK`
  - `type: AddressTypeCast::DELIVERY`
- Asserts:
  - `homeAddress()` returns non‑null and type `HOME`.
  - `workAddress()` returns non‑null.
  - `deliveryAddresses` collection has count 1.

**Problem**
- `AddressTypeCast::DELIVERY` currently **does not exist**.
- This will cause:
  - PHP error when test class is loaded.

**How to fix**
- After adding `DELIVERY` to `AddressTypeCast` as described in 2.2.1:
  - Ensure the default `AddressFactory` can accept enum instances for `type` (it currently does for `HOME` and `WORK`).
  - Rerun this feature test to validate trait queries.

---

#### 2.2.3 AddressFactory vs UserAddressTest (office vs work)
- **Type**: Test/factory mismatch (failing tests)  
- **Files**:
  - Factory: `apiserver/database/factories/AddressFactory.php`  
  - Test: `apiserver/tests/Feature/UserAddressTest.php`

**Factory states**
- File: `apiserver/database/factories/AddressFactory.php`  
- Lines: ~31–56 and 71–88  
- Default:
  - `type` default is `AddressTypeCast::HOME`.
- States:
  - `home()` sets `'type' => 'home', 'title' => 'Home'`.
  - `work()` sets `'type' => AddressTypeCast::WORK, 'title' => 'Work'`.
  - No `office()` state defined.

**Test usage**
- File: `apiserver/tests/Feature/UserAddressTest.php`  
- Lines: ~18–37  
- Test `user can set default address`:
  - Creates:
    - `$home = Address::factory()->forUser($user)->home()->default()->create();`
    - `$office = Address::factory()->forUser($user)->office()->create();`

**Problem**
- `AddressFactory` has no `office()` method; calling it will trigger:
  - `BadMethodCallException: Call to undefined method AddressFactory::office()`.

**How to fix (recommended)**
- Align naming with business language:
  - Either **rename** `work()` to `office()` or **add** an `office()` state that calls `work()` internally.
  - Example:
    - Add `public function office(): static { return $this->work(); }`
- Update tests if you decide to use `work` consistently instead of `office`.

---

### 2.3 Onboarding and Address‑Related Scopes
- **Type**: Coverage gaps / potential edge cases  
- **Files**:
  - `apiserver/tests/Unit/Models/AddressTest.php` (lines ~91–212)  
  - `apiserver/tests/Unit/Models/UserTest.php` (lines ~148–294)  
  - Docs: `docs/backend/address-system-implementation.md`, `docs/backend/onboarding-process.md`

**Observations**
- Tests cover:
  - Address creation, geo hierarchy, scopes (`standalone`, `warehouses`, `stores`, `userAddresses`, `default`).
  - User onboarding via `User::isOnboardingComplete()` with minimal conditions.
- Docs list additional desired tests such as:
  - `mixed ownership addresses maintain proper isolation`.
  - `warehouse addresses isolated from user addresses`.
  - `default address logic maintains isolation`.

**Status**
- Core logic seems implemented and partially tested.
- Some doc‑listed tests are **not yet implemented**, meaning those edge cases rely on convention and manual reasoning only.

**Improvement tips**
- Implement the missing tests described in:
  - `docs/backend/address-system-implementation.md` (section around `UserAddressTest.php`).
  - `docs/backend/onboarding-process.md` (address related section).
- Prioritise:
  - Isolation between warehouse/store (standalone) addresses and user addresses.
  - Address deletion / soft‑delete interaction with onboarding completeness.

---

## 3. Frontend Issues and Contract Mismatches

### 3.1 Wallet Reset PIN Flow (frontend)

#### 3.1.1 Parameter mismatch: resetPinWithToken
- **Type**: Type mismatch / incorrect payload fields  
- **Files**:
  - Page: `client/app/pages/wallet/reset-pin.vue`  
  - Composable: `client/app/composables/useWallet.ts`

**Composable contract**
- File: `client/app/composables/useWallet.ts`  
- Lines: ~253–269  
- Function:
  - `resetPinWithToken(data: { reset_token: string; new_pin: string; confirm_pin: string })`
  - Sends POST to `${apiBase}/api/wallet/reset-pin`.

**Page usage**
- File: `client/app/pages/wallet/reset-pin.vue`  
- Lines: 245–256  
- Current call:
  - `resetPinWithToken({ token: verificationToken.value, method: ..., otp: ..., new_pin, confirm_pin })`

**Problems**
- Field is named `token` instead of required `reset_token`.
- Extra fields `method` and `otp` are passed but **not** part of composable type or backend validator.
- Backend (`WalletController::resetPinWithToken`) expects exactly:
  - `reset_token`, `new_pin`, `confirm_pin`.

**How to fix**
- In `reset-pin.vue`, change payload to:
  - `resetPinWithToken({ reset_token: verificationToken.value, new_pin: formData.value.new_pin, confirm_pin: formData.value.confirm_pin })`.
- If you need method/otp for analytics, handle them in a different endpoint or include them in metadata, but **do not** send unvalidated fields to `/api/wallet/reset-pin`.

---

#### 3.1.2 Security question key type mismatch
- **Type**: Frontend type/contract mismatch  
- **Files**:
  - Page: `client/app/pages/wallet/reset-pin.vue` (security questions)  
  - Composable: `client/app/composables/useWallet.ts` (`verifySecurityQuestion`)

**Composable contract**
- File: `client/app/composables/useWallet.ts`  
- Lines: ~240–249  
- Function:
  - `verifySecurityQuestion(questionKey: string, answer: string)`  
  - Sends `{ question_key: questionKey, answer }` to `/api/wallet/verify-security-question`.

**Page state**
- File: `client/app/pages/wallet/reset-pin.vue`  
- Lines: 24–25, 80–87, 181–199  
- Current definitions:
  - `securityQuestions` declared as `Array<{ id: number; question: string }>` and populated from `getUserSecurityQuestions()`.
  - `selectedQuestion` is `ref<number | null>(null)`.
  - `handleVerifySecurity` calls `verifySecurityQuestion(selectedQuestion.value!, formData.value.answer)`.

**Problems**
- `verifySecurityQuestion` expects a **string key** (`question_key` on the backend), but the page treats questions as numeric IDs.
- This misalignment leads to:
  - Typecheck errors (TypeScript).
  - Incorrect payload if backend expects keys like `'pet_name'`, `'birth_city'`.

**How to fix**
- Align `securityQuestions` with backend format:
  - Use shape `{ key: string; question: string }` or `{ key: string; label: string }`.
  - Ensure `getUserSecurityQuestions()` returns keys matching `WalletController::SECURITY_QUESTIONS`.
- Update page:
  - `const securityQuestions = ref<Array<{ key: string; question: string }>>([])`
  - `const selectedQuestion = ref<string | null>(null)`
  - Set default: `selectedQuestion.value = securityQuestions.value[0].key`
  - Call `verifySecurityQuestion(selectedQuestion.value!, formData.value.answer)`

---

#### 3.1.3 OTP verification token is a stub
- **Type**: Incomplete implementation (security risk if shipped as‑is)  
- **File**: `client/app/pages/wallet/reset-pin.vue`  
- **Lines**: ~162–179

**Current logic**
- `handleVerifyOtp`:
  - Only validates that entered OTP length is 6.
  - Directly sets `verificationToken.value = formData.value.otp` with comment:
    - “In real implementation, this would be a server‑provided token”.
  - Moves to step 3 without actually validating OTP server‑side.

**Backend contract**
- Backend exposes:
  - `POST /api/wallet/request-pin-otp` (used by composable `requestPinChangeOtp`).
  - `POST /api/wallet/reset-pin` that expects a `reset_token` previously stored in cache (see `WalletController::resetPinWithToken`).
- There is **no** current frontend call to verify OTP and obtain a reset token.

**Status**
- Flow is **not fully implemented**:
  - OTP is not validated on the server.
  - Reset token used by `/api/wallet/reset-pin` is not obtained via API.

**How to fix**
- Add an endpoint like `POST /api/wallet/verify-pin-otp` that:
  - Validates OTP.
  - Generates and stores a `reset_token` in cache with key `wallet-pin-reset:{wallet_id}`.
  - Returns `{ token: '...' }` to the client.
- Update frontend:
  - `handleVerifyOtp` should call the OTP verification endpoint.
  - Use the returned `token` to set `verificationToken.value`.
  - Pass this token as `reset_token` to `resetPinWithToken`.

---

### 3.2 Wallet Add / Withdraw / Send Flows

#### 3.2.1 Add Money (topup)
- **Files**:
  - Page: `client/app/pages/wallet/add.vue`  
  - Composable: `client/app/composables/useWallet.ts` (`topup`)  
  - Checkout page: `client/app/pages/checkout/[transaction].vue`

**Observations**
- `add.vue`:
  - Validates amount between ₹10 and ₹1,00,000.
  - Calls `topup(finalAmount.value)`.
- `useWallet.topup(amount: number)`:
  - (From prior analysis) creates a topup transaction and redirects the user to the universal `/checkout/{transaction}` page.
- `checkout/[transaction].vue`:
  - Uses `/api/checkout/{transaction}` for data and `Cashfree` SDK to complete payment.
  - Polls `/api/checkout/{transaction}/status` for verification.

**Status**
- Flow is architecturally complete end‑to‑end:
  - Create + view checkout + poll for status without relying on webhooks.
- Main risk is **regression** if backend checkout structure changes (see section 2.1).

**Improvement tips**
- Add frontend tests for:
  - Successful happy path topup (mock backend with test API base).
  - Handling of expired and already‑completed transactions.

---

#### 3.2.2 Withdraw and Beneficiary management
- **Files**:
  - Withdraw page: `client/app/pages/wallet/withdraw.vue`  
  - Beneficiaries management: `client/app/pages/wallet/bank-accounts.vue`  
  - Composable: `client/app/composables/useWallet.ts` (`withdraw`)

**Observations**
- `withdraw.vue`:
  - Fetches wallet and beneficiaries.
  - Shows only beneficiaries where `can_receive_payout === true`.
  - Verifies PIN using composable `withdraw` call.
  - Enforces redirect to `/wallet/setup-pin` when `wallet.requires_pin_setup` is true.
- `bank-accounts.vue`:
  - Full CRUD for bank/UPI beneficiaries, with:
    - IFSC validation (`/api/wallet/beneficiaries/verify-ifsc`).
    - Bank name/branch auto‑fill.
    - UPI validation (`name@upi` style).
  - Uses server‑side validation, exposes `formErrors` on failure.

**Status**
- These flows are **implemented and quite complete** on the client side.
- Combined with backend tests in `CompleteWalletFlowTest.php` and `AddressApiTest.php`, the withdrawal stack seems mature.

**Improvement tips**
- Add frontend error handling for:
  - Rate limiting and locked PIN (backend returns `locked`, `retry_after`).
  - Minimum withdrawal amount (backend enforces ₹100 and returns message).

---

### 3.3 Frontend Wallet API Tests
- **File**: `client/tests/api/wallet.test.ts`  
- **Lines**: 1–170

**Observations**
- Tests aim to validate wallet endpoints but allow 500 as a “valid” status:
  - `VALID_STATUS = [200, 404, 500]`.
  - Comment: “500 status = endpoint not implemented yet (acceptable during development)”.

**Status**
- Indicates that some wallet endpoints may still be returning 500 for certain environments.
- From a mature system perspective, this is **not acceptable** long‑term.

**How to improve**
- Change the tests to:
  - Treat 500 as a failure once endpoints are expected to be stable.
  - Assert response shape (e.g., wallet summary fields) instead of only status.
- Use feature flags or environment variables during development, instead of hard‑coding 500 as acceptable.

---

## 4. Backend–Frontend Contract Summary

### 4.1 Well‑aligned areas
- Checkout:
  - Backend `CheckoutController::show/status/verify` matches the universal checkout page.
  - `payment_session_id`, `provider_slug`, `redirect` URLs are used correctly.
- Wallet basics:
  - `/api/wallet`, `/api/wallet/stats`, `/api/wallet/transactions` are consumed by `useWallet` and wallet pages.
- Beneficiaries:
  - `/api/wallet/beneficiaries`, `/types`, `/verify-ifsc` align with `bank-accounts.vue`.

### 4.2 Misaligned areas
- Reset PIN:
  - Payload `reset_token` vs `token` mismatch.
  - OTP verification token flow missing.
  - Security question keys treated as numeric IDs instead of string keys.
- Address types:
  - Enum vs trait/test mismatch for `DELIVERY`, `PICKUP`, `HUB`, `SERVICE_POINT`.

---

## 5. Application Completion Status

### 5.1 Wallet subsystem
- **Backend**:  
  - Coverage:
    - Comprehensive feature tests in `CompleteWalletFlowTest.php` for:
      - Initial wallet state.
      - PIN setup and verification (including rate‑limiting).
      - Beneficiary management.
      - Withdrawals (success and failure cases).
      - Checkout integration with Cashfree and polling.
  - Remaining risks:
    - Enum/address type mismatch (section 2.2).
    - Some OTP verification flows around PIN reset need solidification.
  - **Completion estimate**: ~90–95% (logic mostly done, a few correctness and polish items).

- **Frontend**:  
  - Implemented pages:
    - `/wallet` (dashboard).
    - `/wallet/add` (topup).
    - `/wallet/withdraw`.
    - `/wallet/send`.
    - `/wallet/bank-accounts`.
    - `/wallet/setup-pin`.
    - `/wallet/reset-pin` (partially implemented, see 3.1).
    - `/checkout/[transaction]` (universal payment).
  - Gaps:
    - Reset PIN flow: OTP verification and reset token handling incomplete.
    - Some error states (rate limits, lockouts) not emphasised in UI.
  - **Completion estimate**: ~85–90% for wallet UI; reset PIN needs finishing to reach production‑ready status.

---

### 5.2 Address and Onboarding
- **Backend**:
  - Models, factories, and scopes are fully implemented.
  - Unit and feature tests for addresses and users cover most behaviours.
  - Enum values are the main inconsistency.
  - Onboarding rules (`isOnboardingComplete`) exist and are tested.
  - **Completion estimate**: ~90%, pending enum alignment and additional isolation tests.

- **Frontend**:
  - User‑facing address management pages are not fully included in the scanned files (they may exist but were not the focus of this pass).
  - Given the strong backend support and tests, frontend work here is likely either:
    - Partially implemented, or
    - Planned in `plans/FRONTEND_DASHBOARD_PLAN.md` and related docs.
  - **Completion estimate**: ~70–80% (backend ready; frontend may lag depending on actual pages).

---

### 5.3 Global quality indicators
- Automated test coverage:
  - Backend: Many feature/unit tests exist and are in an advanced state.
  - Frontend: Wallet API tests exist but are lenient (accept 500), suggesting some endpoints are still stabilising.
- Documentation:
  - Detailed backend plans and address/onboarding design docs exist.
  - Some sections explicitly list tests that should be created, marking incomplete coverage rather than missing functionality.

---

## 6. Unimplemented or Partially Implemented Client‑Side Features

### 6.1 PIN reset with OTP (client)
- **Status**: Partially implemented.
- Issues:
  - No client call to a dedicated OTP‑verification endpoint to obtain `reset_token`.
  - `verificationToken` is set directly from OTP input.
  - Payload field name mismatch (`token` vs `reset_token`).
- Impact:
  - Insecure and non‑functional in a real environment.
- Recommendation:
  - Implement proper OTP verification API and integrate it, as described in 3.1.3.

### 6.2 Security question selection UI
- **Status**: Implemented but misaligned.
- Issues:
  - Uses numeric IDs whereas backend uses string keys (`pet_name`, `birth_city`, etc.).
- Impact:
  - Requests to `/api/wallet/verify-security-question` may not match stored configuration.
- Recommendation:
  - Refactor UI to use the `key` field from `WalletController::SECURITY_QUESTIONS`.

### 6.3 Wallet API negative paths (frontend tests)
- **Status**: Not covered.
- Issues:
  - `wallet.test.ts` only ensures endpoints respond with 200/404/500, and treats 500 as acceptable.
- Impact:
  - Client behaviour under various error conditions (rate limit, lockouts, insufficient balance) is not automatically verified.
- Recommendation:
  - Extend Vitest API tests to:
    - Treat 500 as failure.
    - Assert contract fields for successful responses.
    - Cover error cases ensuring UI reacts appropriately.

---

## 7. Priority Fix List (Concrete Tips)

1. **Align AddressTypeCast with Trait and Tests**
   - File: `apiserver/app/Casts/AddressTypeCast.php` (lines 18–57).  
   - Add cases for `DELIVERY`, `PICKUP`, `HUB`, `SERVICE_POINT`.  
   - Extend `getColor()`, `getIcon()`, `getLabel()` to handle them.  
   - This will fix trait usage and `HasAddressTest` errors.

2. **Fix AddressFactory / UserAddressTest naming mismatch**
   - Files:
     - `apiserver/database/factories/AddressFactory.php` (lines 71–88).  
     - `apiserver/tests/Feature/UserAddressTest.php` (lines 18–37).  
   - Add `office()` state delegating to `work()` **or** standardise on `work()` in tests.  
   - Re‑run feature tests to ensure default address switching works as expected.

3. **Complete Reset PIN Flow on Frontend**
   - File: `client/app/pages/wallet/reset-pin.vue` (lines 162–256).  
   - Fix payload:
     - Send `reset_token` instead of `token`.  
     - Remove unsupported fields from payload (`method`, `otp`) for `/api/wallet/reset-pin`.  
   - Implement server‑side OTP verification and integrate it:
     - Endpoint should return `reset_token`.  
     - Use `reset_token` in `resetPinWithToken` call.

4. **Fix Security Question Key Types**
   - Files:
     - `client/app/pages/wallet/reset-pin.vue` (lines 24–25, 80–87, 181–199).  
     - `client/app/composables/useWallet.ts` (lines 240–249).  
   - Refactor questions to use string keys (e.g., `pet_name`).  
   - Ensure `verifySecurityQuestion` receives a valid `question_key` string.

5. **Tighten Frontend Wallet API Tests**
   - File: `client/tests/api/wallet.test.ts` (lines 16–18, 44–169).  
   - Change `VALID_STATUS` to `[200, 404]` (or just `200` where endpoint is required).  
   - Remove acceptance of 500 once endpoints are stabilised.  
   - Add assertions on response bodies for key endpoints.

6. **Add Address Isolation and Advanced Tests**
   - Files:
     - `docs/backend/address-system-implementation.md` (tests list).  
     - `apiserver/tests/Unit/Models/AddressTest.php`.  
   - Implement missing tests for:
     - Warehouse/store vs user address isolation.  
     - Mixed ownership and default address behaviour.

---

## 8. Overall Readiness Summary

- **Wallet module**:
  - Strong backend with comprehensive tests and Cashfree integration.
  - Frontend is close to production‑ready, with the main gap in the reset PIN flow and some missing error‑handling UX.

- **Address and onboarding**:
  - Backend is structurally solid; enum inconsistencies should be fixed soon.  
  - Docs outline additional tests for isolation; implementing them would harden the system.

- **Client‑side coverage**:
  - Wallet UI is nearly complete.  
  - API tests exist but still treat 500 as acceptable, signalling “work‑in‑progress” status rather than fully hardened.

With the priority fixes in section 7 addressed, the application should be in a solid position for end‑to‑end wallet and address/onboarding flows, with clear next steps for tightening contracts and improving robustness.

