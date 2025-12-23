# User Onboarding System - Enterprise Plan
**Date**: 2025-12-09
**Based On**: Old Commerinity analysis + Enterprise improvements
**Status**: Planning Phase

---

## 📋 **Executive Summary**

**Goal**: Guide new users through completing their profile after registration, progressively collecting information to unlock features.

**Philosophy**:
- **Progressive Disclosure**: Don't overwhelm on Day 1
- **Value-First**: Show benefits before asking for data
- **Flexible**: Allow skipping non-essential steps
- **Smart**: Auto-fill where possible (postal code → address)
- **Contextual**: Different flows for different user types

---

## 🎯 **What is Onboarding?**

### **Definition**
Onboarding is the **post-registration** process where users complete their profile to unlock full platform features.

### **Current State (users table)**
```sql
onboarded TINYINT(1) DEFAULT 0
```

**Values**:
- `0` = Not onboarded (incomplete profile)
- `1` = Onboarded (profile complete)

### **User Journey**
```
Register (Mobile + OTP)
  ↓
User Created (onboarded = 0, status = draft)
  ↓
Auto-login + Redirect to onboarding
  ↓
Complete Profile Steps
  ↓
Mark onboarded = 1, status = active
  ↓
Full platform access
```

---

## 📊 **Old Commerinity Onboarding Analysis**

### **5-Step Process**

#### **Step 1: Profile Information** ✅ GOOD
**Fields**:
- Name (required, pre-filled from registration)
- Email (required, pre-filled)
- Mobile (required, disabled - cannot change)
- Gender (optional: male, female, other)
- Date of Birth (optional)

**UI**: 2-column grid, clean form

---

#### **Step 2: Address Information** ✅ EXCELLENT
**Fields**:
- Postal Code (6 digits, required)
- Auto-fill button (fetches from API: https://api.postalpincode.in/pincode/{code})
- Street Address (required)
- Landmark (optional)
- Village/Town (optional)
- District/City (dropdown, required)
- State (dropdown, required)
- Block/Municipality (dropdown, required)
- Country (read-only: India)

**Smart Features**:
- ✅ Postal code auto-fills city, state, block
- ✅ Cascading dropdowns (state → blocks → districts)
- ✅ Pre-populated if user already has address

---

#### **Step 3: KYC Documents** ⚠️ REQUIRED (Indian Compliance)
**Fields**:
- Aadhaar Number (12 digits, required)
- Aadhaar File Upload (image/PDF, optional but recommended)
- PAN Number (10 chars: ABCDE1234F, required)
- PAN File Upload (image/PDF, optional but recommended)

**UI**:
- Side-by-side layout
- Live image preview on upload
- File type validation (image/*, application/pdf, max 2MB)

**Purpose**:
- Required for wallet withdrawals
- Required for commission payouts
- Indian KYC compliance

---

#### **Step 4: Subscription Choice** ✅ SMART
**Options**:
1. **Subscribe Now**: Show plan details → redirect to payment
2. **Not Interested**: Skip → mark as regular user

**If Subscribe**:
- Shows subscription plan details (fetched from `/lifecycle/subscribable`)
- Price, benefits, levels, max team capacity
- Proceed to checkout after onboarding

**If Not Interested**:
- User becomes regular customer
- Can subscribe later from dashboard

---

#### **Step 5: Terms & Conditions** ✅ REQUIRED
**Fields**:
- Checkbox: "I agree to Terms & Conditions" (required)

**Validation**: Must be checked to proceed

---

### **Backend Endpoint**
```php
POST /user/onboarding

Controller: UserOnboardingController@processOnboarding
Request: UserOnboardingRequest

Payload (FormData):
- profile[name, email, mobile, gender, dob]
- address[postal_code, address_1, landmark, village, city, state_code, block_id]
- kyc[aadhaar, pan, aadhaar_file, pan_file]
- finish[subscription_type, tnc]

Response:
{
  data: {
    status: true,
    message: "Onboarding completed",
    redirect: true,
    redirect_url: "/dashboard" or "/checkout/{uuid}"
  }
}
```

---

### **Supporting Endpoints**
```
GET  /geo/countries              Get country list
GET  /geo/states/IN              Get Indian states
GET  /geo/state/{code}           Get blocks & districts for state
GET  /lifecycle/subscribable     Get subscription plan details
GET  /user/my-profile            Get user profile (for pre-filling)
```

---

### **Frontend Component**
**Banner**: `OnboardingBanner.vue`
- Progress ring (0-100%)
- Current step indicator
- "Continue Setup" button
- Skip, minimize, close actions
- Celebration animation on completion

**Page**: `/dashboard/account/onboarding.vue`
- Single-page form (not multi-step wizard)
- All 5 sections visible at once
- Fieldsets for each section
- Real-time validation
- Submit at bottom

---

## 🚀 **Enterprise Onboarding Plan (New)**

### **Improvements Over Old Commerinity**

#### **1. Progressive Onboarding** (Not All-at-Once)
**Old**: Single page with ALL fields at once (overwhelming)
**New**: Multi-step wizard with progress tracking

```
Step 1: Profile (30 seconds)
  ↓
Step 2: Address (1 minute) - OPTIONAL for regular users
  ↓
Step 3: KYC (2 minutes) - OPTIONAL initially, REQUIRED for wallet
  ↓
Step 4: Subscription (30 seconds)
  ↓
Complete!
```

---

#### **2. Type-Aware Onboarding**
**Different flows for different user types**:

**REGULAR User**:
```
✅ Profile (name, email, gender, DOB) - Required
⚠️ Address - Skip for now (add when first order)
⚠️ KYC - Skip for now (add when wallet needed)
✅ Subscription - Offer but allow skip
```

**MEMBER/PROMOTER User**:
```
✅ Profile - Required
✅ Address - Required (for product delivery)
⚠️ KYC - Recommended (for commissions)
✅ Subscription - Required for member
```

**ADVISOR/MENTOR User**:
```
✅ Profile - Required
✅ Address - Required
✅ KYC - Required (for salary/payments)
✅ Subscription - Not applicable
```

---

#### **3. Contextual Requirements**
**Smart field requirements based on user intent**:

```typescript
// Example logic
if (user.type === 'regular' && !has_orders) {
  address_required = false // Can add later at checkout
}

if (user.type === 'promoter' || wants_wallet_access) {
  kyc_required = true // Need for commissions
}

if (joining_via_referral) {
  show_mlm_intro = true // Explain MLM benefits
}
```

---

### **4. Modular Step System**

#### **Step Definition**
```typescript
interface OnboardingStep {
  key: string
  label: string
  description: string
  icon: string
  required: boolean  // NEW: Mark truly required vs optional
  completed: boolean
  path: string
  requiredFor: string[] // NEW: "wallet", "mlm", "orders", etc.
}
```

#### **Step Configuration**
```typescript
const onboardingSteps = [
  {
    key: 'profile',
    label: 'Complete Profile',
    description: 'Add your basic information',
    icon: 'i-lucide-user',
    required: true, // Always required
    requiredFor: ['*'], // Required for everything
    path: '/onboarding/profile'
  },
  {
    key: 'address',
    label: 'Add Address',
    description: 'For faster checkout and delivery',
    icon: 'i-lucide-map-pin',
    required: false, // Optional initially
    requiredFor: ['orders', 'mlm'], // Required when placing order
    path: '/onboarding/address'
  },
  {
    key: 'kyc',
    label: 'KYC Verification',
    description: 'Required for wallet and payments',
    icon: 'i-lucide-shield-check',
    required: false, // Optional initially
    requiredFor: ['wallet', 'withdrawals', 'commissions'],
    path: '/onboarding/kyc'
  },
  {
    key: 'subscription',
    label: 'Choose Plan',
    description: 'Unlock premium features',
    icon: 'i-lucide-crown',
    required: false, // Optional
    requiredFor: ['member_features'],
    path: '/onboarding/subscription'
  }
]
```

---

## 🎨 **UI/UX Design**

### **Option 1: Wizard (Recommended)**
**Multi-page flow with progress stepper**

```
┌─────────────────────────────────────────┐
│  Onboarding Progress                    │
│  ━━━━━●━━━━━━━━━━━━━━━  25% Complete   │
│  Step 1 of 4: Complete Profile          │
├─────────────────────────────────────────┤
│                                          │
│  [Form Fields for Current Step]         │
│                                          │
│  [Previous]  [Skip]  [Next: Address →]  │
└─────────────────────────────────────────┘
```

**Benefits**:
- Focus on one thing at a time
- Less overwhelming
- Clear progress indicator
- Easy navigation (back/next)

---

### **Option 2: Accordion (Alternative)**
**All steps visible, expand one at a time**

```
┌──────────────────────────────────┐
│ ✅ 1. Profile        [View]      │
│ → 2. Address         [Edit]      │
│ ⏸ 3. KYC             [Pending]   │
│ ⏸ 4. Subscription    [Pending]   │
└──────────────────────────────────┘

[Expanded Step 2]
┌──────────────────────────────────┐
│ Address Information              │
│ [Form fields...]                 │
│ [Save & Continue]                │
└──────────────────────────────────┘
```

**Benefits**:
- See all steps at once
- Jump to any step
- Track completion visually

---

### **Option 3: Banner + Lazy (From Old Commerinity)**
**Persistent banner in dashboard, click to complete**

```
┌────────────────────────────────────────────────┐
│ 🎯 Complete Your Profile  [60% Complete]       │
│ Next: Add Address → [Continue Setup] [Dismiss] │
└────────────────────────────────────────────────┘
```

**Benefits**:
- Non-intrusive
- User can complete at their pace
- Persistent reminder
- Easy to dismiss

---

## 🏗️ **Implementation Plan**

### **Phase 1: Database & Models** (1 day)

#### **Migration 1: Update Users Table**
```php
// Already exists in current migration ✅
$table->boolean('onboarded')->default(false);
$table->string('status')->default('draft')->index();
```

#### **Migration 2: Create Addresses Table**
```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Type: HOME, WORK, BILLING, SHIPPING
    $table->string('type')->default('HOME');
    $table->boolean('is_default')->default(false);

    // Address fields
    $table->string('postal_code', 6);
    $table->text('address_1'); // Street address
    $table->string('landmark')->nullable();
    $table->string('village')->nullable();
    $table->string('city');
    $table->string('state_code', 10);
    $table->string('block_id')->nullable();
    $table->string('country', 2)->default('IN');

    $table->timestamps();

    $table->index(['user_id', 'type']);
    $table->index(['user_id', 'is_default']);
});
```

#### **Migration 3: Create KYCs Table**
```php
Schema::create('kycs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Document numbers
    $table->string('aadhaar', 12)->unique();
    $table->string('pan', 10)->unique();

    // Verification status
    $table->string('status')->default('pending'); // pending, verified, rejected
    $table->text('rejection_reason')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->foreignId('verified_by')->nullable()->constrained('users');

    $table->timestamps();

    $table->index(['user_id', 'status']);
});
```

**Note**: Files (Aadhaar image, PAN image) stored via Spatie Media Library collections.

---

### **Phase 2: Backend API** (2 days)

#### **Controller**: `OnboardingController`
```php
namespace App\Http\Controllers\Api;

final class OnboardingController extends Controller
{
    /**
     * Get onboarding status
     * Returns: current step, completion %, steps list
     */
    public function status(): JsonResponse

    /**
     * Complete profile step
     * Updates: name, email, bio, gender, dob
     */
    public function completeProfile(CompleteProfileRequest $request): JsonResponse

    /**
     * Add address step
     * Creates/updates address
     */
    public function addAddress(AddAddressRequest $request): JsonResponse

    /**
     * Submit KYC documents
     * Creates KYC record + uploads files
     */
    public function submitKyc(SubmitKycRequest $request): JsonResponse

    /**
     * Choose subscription
     * Handles: subscribe → checkout, or skip → regular
     */
    public function chooseSubscription(ChooseSubscriptionRequest $request): JsonResponse

    /**
     * Mark onboarding complete
     * Sets: onboarded = 1, status = active
     */
    public function complete(): JsonResponse
}
```

#### **Endpoints**
```
GET    /api/onboarding/status           Get current progress
POST   /api/onboarding/profile          Complete profile step
POST   /api/onboarding/address          Add address step
POST   /api/onboarding/kyc              Submit KYC step
POST   /api/onboarding/subscription     Choose subscription
POST   /api/onboarding/complete         Mark as complete
POST   /api/onboarding/skip/{step}      Skip optional step
```

#### **Supporting Endpoints** (Geo Data)
```
GET    /api/geo/countries               List countries
GET    /api/geo/states/{country}        List states (e.g., /api/geo/states/IN)
GET    /api/geo/blocks/{state}          List blocks for state
POST   /api/geo/postal-lookup           Postal code → address data
```

---

### **Phase 3: Frontend Components** (3 days)

#### **Onboarding Wizard Component**
**Location**: `client/app/pages/onboarding/index.vue`

**Features**:
- Multi-step wizard UI
- Progress stepper (1 of 4, 2 of 4, etc.)
- Step validation before proceeding
- Back/Next navigation
- Skip optional steps
- Auto-save progress
- Exit and resume later

---

#### **Step Components**

**1. Profile Step** (`components/onboarding/ProfileStep.vue`)
```vue
<template>
  <div class="space-y-6">
    <h2>Tell us about yourself</h2>

    <!-- Name (pre-filled, editable) -->
    <!-- Email (pre-filled, editable) -->
    <!-- Mobile (pre-filled, disabled) -->
    <!-- Gender (optional dropdown) -->
    <!-- DOB (optional date picker) -->

    <button @click="saveProfile">Continue to Address →</button>
  </div>
</template>
```

---

**2. Address Step** (`components/onboarding/AddressStep.vue`)
```vue
<template>
  <div class="space-y-6">
    <h2>Where should we deliver?</h2>

    <!-- Postal Code with auto-fill button -->
    <!-- Street Address -->
    <!-- Landmark (optional) -->
    <!-- Village/Town (optional) -->
    <!-- City (dropdown, auto-filled) -->
    <!-- State (dropdown, auto-filled) -->
    <!-- Block (dropdown, cascading) -->
    <!-- Country (read-only: India) -->

    <div class="flex gap-4">
      <button @click="skip">Skip for now</button>
      <button @click="saveAddress">Continue to KYC →</button>
    </div>
  </div>
</template>
```

---

**3. KYC Step** (`components/onboarding/KycStep.vue`)
```vue
<template>
  <div class="space-y-6">
    <h2>Verify your identity</h2>

    <div class="alert-info">
      Required for wallet withdrawals and commission payouts
    </div>

    <!-- Aadhaar Number (12 digits) -->
    <!-- Aadhaar File Upload (with preview) -->
    <!-- PAN Number (ABCDE1234F format) -->
    <!-- PAN File Upload (with preview) -->

    <div class="flex gap-4">
      <button @click="skip">Submit Later</button>
      <button @click="submitKyc">Continue to Plan →</button>
    </div>
  </div>
</template>
```

---

**4. Subscription Step** (`components/onboarding/SubscriptionStep.vue`)
```vue
<template>
  <div class="space-y-6">
    <h2>Choose your membership</h2>

    <!-- Plan Card (fetched from API) -->
    <div class="plan-card">
      <h3>{{ plan.name }}</h3>
      <p>{{ plan.description }}</p>
      <div class="price">{{ plan.price }}</div>
      <ul class="benefits">
        <li v-for="benefit in plan.benefits">{{ benefit }}</li>
      </ul>
    </div>

    <!-- Radio buttons -->
    <div>
      <label>
        <input type="radio" v-model="choice" value="subscribe" />
        Subscribe Now
      </label>
      <label>
        <input type="radio" v-model="choice" value="skip" />
        Not Interested (Remain as Regular)
      </label>
    </div>

    <!-- Terms checkbox -->
    <label>
      <input type="checkbox" v-model="acceptTerms" required />
      I agree to Terms & Conditions
    </label>

    <button @click="complete" :disabled="!acceptTerms">
      {{ choice === 'subscribe' ? 'Proceed to Payment' : 'Complete Setup' }}
    </button>
  </div>
</template>
```

---

#### **Onboarding Banner Component**
**Location**: `components/OnboardingBanner.vue`

**Features** (from old commerinity, enhanced):
- ✅ Circular progress indicator (0-100%)
- ✅ Current step display with icon
- ✅ "Continue Setup" button
- ✅ Minimize/Close buttons
- ✅ Steps preview (when minimized)
- ✅ Celebration confetti on completion
- ✨ NEW: Estimated time to complete
- ✨ NEW: Benefits preview for next step
- ✨ NEW: Smart positioning (non-intrusive)

**Usage**:
```vue
<!-- In dashboard layout -->
<template>
  <div>
    <OnboardingBanner v-if="!user.onboarded" />
    <slot />
  </div>
</template>
```

---

### **Phase 4: Business Logic** (1 day)

#### **Onboarding Service**
```php
namespace App\Services;

final class OnboardingService
{
    public function __construct(
        private readonly User $user,
    ) {}

    /**
     * Get onboarding progress
     */
    public function getProgress(): array
    {
        return [
            'completed_steps' => $this->getCompletedSteps(),
            'total_steps' => $this->getTotalSteps(),
            'percentage' => $this->getPercentage(),
            'next_step' => $this->getNextStep(),
            'is_complete' => $this->user->onboarded,
        ];
    }

    private function getCompletedSteps(): array
    {
        $steps = [];

        // Profile complete if has name, gender, dob
        if ($this->user->name && $this->user->gender && $this->user->dob) {
            $steps[] = 'profile';
        }

        // Address complete if has at least one address
        if ($this->user->addresses()->exists()) {
            $steps[] = 'address';
        }

        // KYC complete if KYC record exists
        if ($this->user->kyc()->exists()) {
            $steps[] = 'kyc';
        }

        // Subscription complete if has level (member) or explicitly skipped
        if ($this->user->level_id || $this->user->onboarded) {
            $steps[] = 'subscription';
        }

        return $steps;
    }

    /**
     * Mark user as onboarded
     */
    public function markComplete(): void
    {
        $this->user->update([
            'onboarded' => true,
            'status' => UserStatusCast::ACTIVE->value,
        ]);
    }

    /**
     * Check if step is required for user type
     */
    public function isStepRequired(string $step): bool
    {
        return match ($step) {
            'profile' => true, // Always required
            'address' => $this->user->type !== 'regular',
            'kyc' => in_array($this->user->type, ['promoter', 'advisor', 'mentor']),
            'subscription' => false, // Always optional
            default => false,
        };
    }
}
```

---

### **Phase 5: Middleware & Guards** (1 day)

#### **Middleware**: `RequireOnboarding`
```php
final class RequireOnboarding
{
    public function handle(Request $request, Closure $next, ...$except): Response
    {
        $user = $request->user();

        if (!$user || $user->onboarded) {
            return $next($request);
        }

        // Redirect to onboarding if not complete
        return redirect()->route('onboarding.index');
    }
}
```

**Usage**:
```php
// routes/web.php
Route::middleware(['auth', 'require.onboarding'])->group(function () {
    Route::get('/dashboard', ...);
    Route::get('/shop', ...);
    // etc.
});

// Exclude onboarding routes themselves
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', ...); // No require.onboarding here
});
```

---

#### **Frontend Middleware**: `onboarding.ts`
```typescript
export default defineNuxtRouteMiddleware((to) => {
  const { user } = useSanctum()

  // Allow onboarding routes
  if (to.path.startsWith('/onboarding')) {
    return
  }

  // Redirect if not onboarded
  if (user.value && !user.value.onboarded) {
    return navigateTo('/onboarding')
  }
})
```

**Add to routes**:
```typescript
// pages/dashboard/index.vue
definePageMeta({
  middleware: ['auth', 'onboarding']
})
```

---

## 📋 **Step-by-Step Implementation**

### **Day 1: Database Setup**
```bash
# Create migrations
php artisan make:migration create_addresses_table --no-interaction
php artisan make:migration create_kycs_table --no-interaction

# Create models
php artisan make:model Address --factory --no-interaction
php artisan make:model Kyc --factory --no-interaction

# Run migrations
php artisan migrate
```

### **Day 2: Backend API**
```bash
# Create controller
php artisan make:controller Api/OnboardingController --no-interaction

# Create form requests
php artisan make:request Onboarding/CompleteProfileRequest --no-interaction
php artisan make:request Onboarding/AddAddressRequest --no-interaction
php artisan make:request Onboarding/SubmitKycRequest --no-interaction

# Create service
php artisan make:class Services/OnboardingService --no-interaction

# Create middleware
php artisan make:middleware RequireOnboarding --no-interaction

# Add routes to routes/api.php
```

### **Day 3: Backend Tests**
```bash
# Create tests
php artisan make:test Feature/OnboardingTest --pest --no-interaction
php artisan make:test Feature/Models/AddressTest --pest --no-interaction
php artisan make:test Feature/Models/KycTest --pest --no-interaction

# Run tests
php artisan test --filter=Onboarding
```

### **Day 4-5: Frontend Wizard**
```bash
cd client

# Create pages
- pages/onboarding/index.vue (wizard container)
- pages/onboarding/profile.vue
- pages/onboarding/address.vue
- pages/onboarding/kyc.vue
- pages/onboarding/subscription.vue

# Create components
- components/onboarding/OnboardingWizard.vue
- components/onboarding/ProgressStepper.vue
- components/onboarding/ProfileStep.vue
- components/onboarding/AddressStep.vue
- components/onboarding/KycStep.vue
- components/onboarding/SubscriptionStep.vue

# Create composable
- composables/useOnboarding.ts
```

### **Day 6: Integration & Testing**
- Wire frontend to backend APIs
- Test all flows
- Fix integration issues
- Polish UI/UX

---

## 🎯 **Onboarding Flow Diagram**

```
Registration Complete
  ↓
onboarded = false, status = draft
  ↓
Redirect to /onboarding
  ↓
┌─────────────────────────────┐
│ Step 1: Profile             │
│ ✅ Name, Email (pre-filled) │
│ ⚠️ Gender, DOB (add)        │
│ [Continue →]                │
└─────────────────────────────┘
  ↓
┌─────────────────────────────┐
│ Step 2: Address (Optional)  │
│ Postal code auto-fill       │
│ State → Block → City        │
│ [Skip] [Continue →]         │
└─────────────────────────────┘
  ↓
┌─────────────────────────────┐
│ Step 3: KYC (Optional)      │
│ Aadhaar + PAN               │
│ Upload documents            │
│ [Submit Later] [Continue →] │
└─────────────────────────────┘
  ↓
┌─────────────────────────────┐
│ Step 4: Subscription        │
│ ○ Subscribe Now             │
│ ○ Not Interested            │
│ ✅ Accept Terms (required)  │
│ [Complete Setup]            │
└─────────────────────────────┘
  ↓
onboarded = 1, status = active
  ↓
IF subscribe → Redirect to /checkout/{subscription_uuid}
IF skip → Redirect to /dashboard
```

---

## 🎨 **UI Design Specifications**

### **Wizard Layout**
```
┌────────────────────────────────────────────┐
│ [Logo]  Complete Your Profile   [X Close] │
├────────────────────────────────────────────┤
│                                             │
│  ● ━━━ ○ ━━━ ○ ━━━ ○    25% Complete     │
│  Profile  Address  KYC  Plan               │
│                                             │
├────────────────────────────────────────────┤
│                                             │
│  [Current Step Content]                    │
│                                             │
│  ┌──────────────────────────────────┐     │
│  │ Name:     [...................]  │     │
│  │ Email:    [...................]  │     │
│  │ Gender:   [Select      ▼]        │     │
│  │ DOB:      [DD/MM/YYYY]          │     │
│  └──────────────────────────────────┘     │
│                                             │
├────────────────────────────────────────────┤
│  [← Back]     [Skip]     [Next: Address →] │
└────────────────────────────────────────────┘
```

### **Banner Layout** (Persistent in Dashboard)
```
┌──────────────────────────────────────────────────────┐
│ ● 60%  Next: Add Address                             │
│ Complete your profile to unlock all features         │
│ [Continue Setup →]  [Minimize ━]  [Dismiss ✕]       │
└──────────────────────────────────────────────────────┘
```

---

## 🔐 **Security & Validation**

### **Backend Validation**

**Profile**:
- Name: required, min:2, max:255
- Email: nullable, email, unique:users
- Gender: nullable, in:male,female,other
- DOB: nullable, date, before:today

**Address**:
- Postal code: required, regex:/^\d{6}$/
- Address 1: required, max:500
- City: required, max:255
- State: required, exists:states,code
- Block: required, max:255

**KYC**:
- Aadhaar: required, regex:/^\d{12}$/, unique:kycs
- PAN: required, regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/, unique:kycs
- Aadhaar file: nullable, file, mimes:jpeg,png,pdf, max:2048
- PAN file: nullable, file, mimes:jpeg,png,pdf, max:2048

---

### **File Upload Security**
- ✅ Validate MIME types (not just extensions)
- ✅ Sanitize filenames
- ✅ Store in private storage (not public)
- ✅ Scan for malware (optional, via ClamAV)
- ✅ Encrypt sensitive documents (Aadhaar, PAN)

---

## 📊 **Database Relationships**

### **User Model**
```php
class User extends Authenticatable
{
    // Relationships
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)
            ->where('is_default', true);
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(Kyc::class);
    }

    // Scopes
    public function scopeOnboarded($query)
    {
        return $query->where('onboarded', true);
    }

    public function scopeNotOnboarded($query)
    {
        return $query->where('onboarded', false);
    }

    // Accessors
    public function getOnboardingProgressAttribute(): int
    {
        return app(OnboardingService::class, ['user' => $this])
            ->getPercentage();
    }
}
```

---

## 🎯 **Progressive Requirements**

### **Level 1: Registration Complete** ✅
**What's set**:
- ✅ Name, Mobile, Password
- ✅ Mobile verified (via OTP)
- ✅ Referral code generated
- ✅ UUID assigned
- ✅ Token created
- ✅ Auto-login

**What's missing**:
- ❌ Email (optional)
- ❌ Gender, DOB (optional)
- ❌ Address (not needed yet)
- ❌ KYC (not needed yet)

**Access Granted**:
- ✅ Dashboard (read-only)
- ✅ Shop (browse products)
- ⚠️ Cart (yes, but can't checkout without address)
- ❌ Wallet (no, needs KYC)
- ❌ MLM Features (no, needs full profile)

---

### **Level 2: Profile Complete**
**Required**:
- ✅ Name, Email, Mobile
- ✅ Gender, DOB

**Access Granted**:
- ✅ Full dashboard
- ✅ MLM features (referral sharing)
- ⚠️ Still can't checkout (needs address)

---

### **Level 3: Address Added**
**Required**:
- ✅ At least one address

**Access Granted**:
- ✅ Can place orders
- ✅ Checkout enabled
- ✅ Full e-commerce features

---

### **Level 4: KYC Verified**
**Required**:
- ✅ Aadhaar + PAN submitted
- ✅ Status = verified (admin approval)

**Access Granted**:
- ✅ Wallet access
- ✅ Commission withdrawals
- ✅ P2P transfers
- ✅ Payment gateway payouts

---

### **Level 5: Fully Onboarded** 🎉
**Status**:
- ✅ onboarded = true
- ✅ status = active

**Access**:
- ✅ Everything unlocked
- ✅ No restrictions
- ✅ Banner dismissed

---

## 🚦 **Access Control Rules**

### **Feature-Based Gating**
```php
// In controllers/middleware
if (!$user->hasAddress()) {
    return response()->json([
        'error' => 'Address required',
        'redirect' => '/onboarding/address',
    ], 403);
}

if (!$user->hasVerifiedKyc()) {
    return response()->json([
        'error' => 'KYC verification required',
        'redirect' => '/onboarding/kyc',
    ], 403);
}
```

### **Frontend Guards**
```typescript
// middleware/require-address.ts
export default defineNuxtRouteMiddleware(() => {
  const { user } = useSanctum()

  if (!user.value?.has_address) {
    return navigateTo('/onboarding/address')
  }
})

// Usage in checkout page
definePageMeta({
  middleware: ['auth', 'require-address']
})
```

---

## 📝 **Test Plan**

### **Backend Tests**
```php
// tests/Feature/OnboardingTest.php

it('returns onboarding status for new user')
it('completes profile step successfully')
it('validates profile data')
it('adds address with postal code auto-fill')
it('submits KYC documents')
it('uploads KYC files to media library')
it('chooses subscription and redirects to checkout')
it('skips subscription and marks onboarded')
it('marks user as onboarded after all steps')
it('prevents onboarded user from accessing onboarding')
it('requires address before checkout')
it('requires KYC before wallet access')
```

### **Frontend E2E Tests**
```typescript
// Pest Browser Test
it('completes full onboarding flow', function () {
    // Register new user
    // Should redirect to /onboarding
    // Complete profile step
    // Add address step
    // Submit KYC step
    // Choose subscription
    // Should redirect to checkout or dashboard
    // Verify onboarded = true
});
```

---

## 🎁 **User Benefits Communication**

### **Why Complete Onboarding?**

**Show value proposition at each step**:

**Profile Step**:
> "Complete your profile to personalize your experience and unlock community features"

**Address Step**:
> "Add your address for faster checkout and exclusive delivery offers"

**KYC Step**:
> "Verify your identity to unlock wallet, earn commissions, and withdraw earnings"

**Subscription Step**:
> "Join as a member for exclusive discounts, priority support, and MLM benefits"

---

## 🎯 **Success Metrics**

### **Track Completion Rates**
```sql
-- Onboarding funnel
SELECT
    COUNT(*) as total_users,
    SUM(CASE WHEN onboarded = 1 THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN gender IS NOT NULL THEN 1 ELSE 0 END) as profile_complete,
    SUM(CASE WHEN EXISTS(SELECT 1 FROM addresses WHERE user_id = users.id) THEN 1 ELSE 0 END) as has_address,
    SUM(CASE WHEN EXISTS(SELECT 1 FROM kycs WHERE user_id = users.id) THEN 1 ELSE 0 END) as kyc_submitted
FROM users
WHERE created_at >= '2025-01-01';
```

### **Drop-off Analysis**
```
Step 1 (Profile): 90% completion
Step 2 (Address): 70% completion
Step 3 (KYC): 40% completion
Step 4 (Subscription): 30% completion
```

**Optimize** steps with low completion rates.

---

## 🔄 **Post-Onboarding Updates**

### **Allow Updates After Onboarding**
Users can still update profile even after onboarding:

```
/profile/edit → Update profile
/addresses → Manage addresses (add, edit, delete, set default)
/kyc/update → Re-submit KYC if rejected
/subscription → Upgrade/downgrade membership
```

**onboarded flag = true** doesn't freeze the profile!

---

## 📦 **Deliverables**

### **Backend**
- [ ] Addresses table migration
- [ ] KYCs table migration
- [ ] Address model + factory
- [ ] Kyc model + factory
- [ ] OnboardingController (6 methods)
- [ ] 3 Form Request classes
- [ ] OnboardingService class
- [ ] RequireOnboarding middleware
- [ ] 10+ Pest tests
- [ ] API routes

### **Frontend**
- [ ] /onboarding/index.vue (wizard)
- [ ] 4 step components
- [ ] OnboardingBanner.vue (persistent)
- [ ] useOnboarding.ts composable
- [ ] Onboarding middleware
- [ ] TypeScript types

### **Documentation**
- [ ] API documentation
- [ ] User guide (what to expect)
- [ ] Admin guide (KYC verification process)

---

## 🚀 **Next Steps**

### **Immediate Actions**
1. Review this plan with user
2. Get approval on approach (Wizard vs Banner vs Hybrid)
3. Decide on required vs optional steps per user type
4. Start implementation (Day 1: Migrations)

### **Questions for User**
1. **KYC**: Required immediately or later (when needed for wallet)?
2. **Address**: Required for all users or only when placing order?
3. **UI Approach**: Wizard (focused) vs Banner (flexible)?
4. **Subscription**: Push aggressively or soft-sell?

---

**Recommended**: **Hybrid Approach**
- Banner in dashboard (non-intrusive)
- Click → Multi-step wizard (focused)
- Allow skip and resume later
- Require before feature access (checkout, wallet, etc.)

---

**Last Updated**: 2025-12-09
**Status**: ✅ Plan complete, awaiting user approval
