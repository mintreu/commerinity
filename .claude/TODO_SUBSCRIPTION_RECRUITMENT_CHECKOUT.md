# TODO: Subscription & Recruitment Checkout Implementation
**Date**: 2025-12-25
**Status**: Ready to implement
**Based on**: Wallet topup implementation (completed ✅)

---

## 🎯 **Implementation Roadmap**

Now that wallet topup is working, we can easily extend to subscription and recruitment using the **same pattern**.

---

## 📋 **PHASE 1: Subscription Checkout** (2-3 hours)

### **Backend Tasks**

#### 1. Add HasTransaction to UserSubscription Model
**File**: `apiserver/app/Models/Membership/UserSubscription.php`

```php
// Add trait
use HasTransaction;

// Add constant
const TRANSACTION_AMOUNT_COLUMN = 'amount';
```

**Time**: 5 minutes

---

#### 2. Create Subscription Checkout Endpoint
**File**: `apiserver/app/Http/Controllers/Api/SubscriptionController.php`

**Method**: `checkout(Request $request)`

```php
public function checkout(Request $request): JsonResponse
{
    $validated = $request->validate([
        'stage_id' => ['required', 'exists:stages,id'],
        'level_id' => ['required', 'exists:levels,id'],
        'payment_method' => ['nullable', 'string', 'in:wallet,cashfree,razorpay'],
    ]);

    $user = $request->user();

    // Check if already has active subscription
    // Create UserSubscription record (is_paid: false)
    // Create transaction using HasTransaction
    // Return checkout URL
}
```

**Time**: 30 minutes

---

#### 3. Update HandlePaymentCompleted Listener
**File**: `apiserver/app/Listeners/Payment/HandlePaymentCompleted.php`

**Already done** ✅ - Subscription handler already exists!

Just verify it works:
```php
private function handleSubscriptionPayment(mixed $transaction, UserSubscription $subscription): void
{
    // Mark as paid
    // Upgrade user to MEMBER
    // Assign level/stage
    // Trigger MLM commissions
}
```

**Time**: 10 minutes (verification only)

---

#### 4. Add API Route
**File**: `apiserver/routes/api.php`

```php
Route::prefix('subscription')->group(function () {
    Route::post('/checkout', [SubscriptionController::class, 'checkout']);
});
```

**Time**: 2 minutes

---

#### 5. Write Tests
**File**: `apiserver/tests/Feature/Payment/SubscriptionCheckoutTest.php`

**Tests**:
- ✅ Can initiate subscription checkout
- ✅ Validates stage_id required
- ✅ Validates level_id required
- ✅ Prevents duplicate active subscription
- ✅ Requires authentication
- ✅ Accepts different payment methods
- ✅ Creates transaction correctly

**Time**: 45 minutes

---

### **Frontend Tasks**

#### 6. Update Subscription Page
**File**: `client/app/pages/subscription/index.vue` (or wherever subscription UI is)

**Add**:
```typescript
async function handleSubscribe(stageId: number, levelId: number, paymentMethod: string = 'cashfree') {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/subscription/checkout`, {
        method: 'POST',
        body: {
            stage_id: stageId,
            level_id: levelId,
            payment_method: paymentMethod
        }
    })

    if (response.success) {
        // Redirect to checkout
        window.location.href = response.data.checkout_url
    }
}
```

**Time**: 20 minutes

---

#### 7. Add useSubscription Composable Method
**File**: `client/app/composables/useSubscription.ts`

**Add**:
```typescript
const checkout = async (stageId: number, levelId: number, paymentMethod: string = 'cashfree') => {
    // Call API
    // Return checkout URL
}
```

**Time**: 15 minutes

---

### **Testing**

#### 8. End-to-End Test
- User clicks "Subscribe to Member"
- Selects stage/level
- Clicks "Pay Now"
- Redirects to `/checkout/{transaction}`
- Completes payment via Cashfree
- Webhook confirms payment
- User upgraded to MEMBER ✅
- MLM commissions triggered ✅
- Redirects to dashboard

**Time**: 30 minutes

---

**PHASE 1 Total Time**: ~3 hours

---

## 📋 **PHASE 2: Recruitment Payment** (1-2 hours)

### **Backend Tasks**

#### 1. Add HasTransaction to JobApplication Model
**File**: `apiserver/app/Models/Recruitment/JobApplication.php`

```php
// Add trait
use HasTransaction;

// Add constant
const TRANSACTION_AMOUNT_COLUMN = 'amount'; // Or 'application_fee'
```

**Time**: 5 minutes

---

#### 2. Update RecruitmentController
**File**: `apiserver/app/Http/Controllers/Api/RecruitmentController.php`

**Update `apply()` method**:
```php
public function apply(Request $request, Recruitment $recruitment): JsonResponse
{
    // Existing validation...

    // Create JobApplication
    $application = JobApplication::create([...]);

    // If recruitment has fee
    if ($recruitment->application_fee > 0) {
        $transaction = $application->createDebitTransaction(
            customer: $user,
            paymentMethod: PaymentMethodCast::CASHFREE,
            redirectSuccessUrl: config('app.client_url').'/career/applications/'.$application->uuid,
            redirectFailureUrl: config('app.client_url').'/career/'.$recruitment->slug.'/apply',
            wallet: $user->wallet,
            purpose: 'Recruitment Application Fee',
        );

        return response()->json([
            'success' => true,
            'checkout_url' => config('app.client_url').'/checkout/'.$transaction->uuid,
        ]);
    }

    // Free recruitment - submit immediately
    return response()->json([...]);
}
```

**Time**: 30 minutes

---

#### 3. HandlePaymentCompleted Listener
**Already done** ✅ - Recruitment handler exists!

```php
private function handleRecruitmentPayment(mixed $transaction, JobApplication $application): void
{
    $application->update([
        'status' => JobApplicationStatusCast::SUBMITTED,
        'paid_at' => now(),
    ]);
}
```

**Time**: 5 minutes (verification only)

---

#### 4. Write Tests
**File**: `apiserver/tests/Feature/Payment/RecruitmentPaymentTest.php`

**Tests**:
- ✅ Paid recruitment requires payment
- ✅ Free recruitment submits immediately
- ✅ Creates transaction for paid recruitment
- ✅ Payment confirmation submits application
- ✅ Different payment methods supported

**Time**: 30 minutes

---

### **Frontend Tasks**

#### 5. Update Recruitment Apply Page
**File**: `client/app/pages/career/[slug]/apply.vue`

**Update submit handler**:
```typescript
async function submitApplication() {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/careers/${slug}/apply`, {
        method: 'POST',
        body: {
            ...formData,
            payment_method: selectedPaymentMethod.value
        }
    })

    if (response.checkout_url) {
        // Paid recruitment - redirect to checkout
        window.location.href = response.checkout_url
    } else {
        // Free recruitment - show success
        navigateTo('/career/applications')
    }
}
```

**Time**: 15 minutes

---

#### 6. Test End-to-End
- User applies for paid job (fee: ₹500)
- Fills application form
- Clicks "Submit & Pay"
- Redirects to `/checkout/{transaction}`
- Pays via Cashfree
- Webhook confirms
- Application status → SUBMITTED ✅
- Redirects to applications list

**Time**: 20 minutes

---

**PHASE 2 Total Time**: ~2 hours

---

## 📊 **Implementation Summary**

### **What We're Reusing** (No Changes Needed):
✅ HasTransaction trait (already supports all models)
✅ PaymentService (already routes all providers)
✅ CashfreePaymentProvider (already configured)
✅ HandlePaymentCompleted listener (already has handlers)
✅ CheckoutController (works for all transaction types)
✅ Frontend checkout page (universal)
✅ Webhook handlers (already complete)

### **What We Need to Add**:

**Subscription** (3 hours):
1. Add trait to UserSubscription model (5 min)
2. Create checkout endpoint (30 min)
3. Write backend tests (45 min)
4. Update frontend subscription page (20 min)
5. Add composable method (15 min)
6. Test end-to-end (30 min)

**Recruitment** (2 hours):
1. Add trait to JobApplication model (5 min)
2. Update apply endpoint (30 min)
3. Write backend tests (30 min)
4. Update frontend apply page (15 min)
5. Test end-to-end (20 min)

**Total Estimate**: 5 hours

---

## 🎯 **Next Session Plan**

### **Order of Implementation**:
1. **Subscription First** (more complex, has MLM integration)
2. **Recruitment Second** (simpler, just status update)

### **Testing Strategy**:
- Write tests BEFORE implementation
- Test each payment method (wallet, cashfree, razorpay)
- Verify webhook handling
- Verify business logic (subscription activation, MLM commissions)

---

**Ready to start when you are!** 🚀
