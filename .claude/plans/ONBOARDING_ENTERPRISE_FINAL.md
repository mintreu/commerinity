# Enterprise Onboarding System - Industry Standard Plan
**Date**: 2025-12-09
**Version**: 2.0 (Final)
**Based On**: Old Commerinity + Popkult + Industry Best Practices

---

## 🎯 **Strategic Overview**

### **What is Onboarding?**
Post-registration progressive profile completion that unlocks features contextually.

### **Core Principles**
1. **Progressive Disclosure**: One step at a time, don't overwhelm
2. **Value-First**: Show benefits before asking for data
3. **Contextual**: Require data only when needed (JIT - Just In Time)
4. **Resumable**: Save progress, allow exit and return
5. **Type-Aware**: Different flows for different user types
6. **Gamified**: Progress tracking, celebration, rewards

---

## 📊 **Best Practices from Both Systems**

### **From Old Commerinity** ✅
- ✅ `onboarded` boolean flag (simple tracking)
- ✅ Single-page form (all sections visible)
- ✅ Postal code auto-fill (India Post API)
- ✅ Cascading dropdowns (State → Block → District)
- ✅ KYC with file uploads + preview
- ✅ Subscription choice (subscribe vs skip)
- ✅ OnboardingBanner component (persistent, animated)
- ✅ Progress ring (circular 0-100%)

### **From Popkult** ✅
- ✅ **Polymorphic addresses** (can belong to User, Customer, Warehouse)
- ✅ **Auto-default handling** (AddressObserver pattern)
- ✅ **Multiple address support** (home, work, billing, shipping)
- ✅ **Address name & contact** (not just location)
- ✅ **Clean separation** (addresses table, not user columns)

### **Industry Standards** ✅
- ✅ **Wizard UI** (step-by-step, linear flow)
- ✅ **Progress indicators** (breadcrumbs, percentage, step counter)
- ✅ **Validation feedback** (real-time, clear errors)
- ✅ **Auto-save** (draft mode, resume anytime)
- ✅ **Skip options** (for optional steps)
- ✅ **Feature gates** (require data when needed, not upfront)
- ✅ **Accessibility** (keyboard navigation, screen readers)
- ✅ **Mobile-first** (responsive wizard design)

---

## 🏗️ **Database Schema (Enterprise)**

### **Migration 1: Update Users Table**
```php
// Already exists ✅
$table->boolean('onboarded')->default(false);
$table->string('status')->default('draft')->index();
$table->text('status_feedback')->nullable();

// Profile fields
$table->text('bio')->nullable();
$table->string('gender')->default('other');
$table->date('dob')->nullable();
```

---

### **Migration 2: Create Addresses Table** (Polymorphic - Popkult Pattern)
```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();

    // Who owns this address (User, Customer, Warehouse, etc.)
    $table->nullableMorphs('addressable');

    // Address metadata
    $table->string('name')->nullable(); // "Home", "Work", "Mom's House"
    $table->string('contact', 15)->nullable(); // Phone for delivery
    $table->string('alternate_contact', 15)->nullable();
    $table->string('type')->default('home'); // home, work, billing, shipping

    // Location (India-specific)
    $table->string('postal_code', 6);
    $table->text('address_1'); // Street/building
    $table->text('address_2')->nullable(); // Apartment/floor
    $table->string('landmark')->nullable();
    $table->string('village')->nullable(); // For rural areas
    $table->string('city');
    $table->string('district')->nullable(); // Auto-filled from postal
    $table->string('state_code', 10); // State ISO code
    $table->string('block_id')->nullable(); // Municipality/block
    $table->string('country_code', 2)->default('IN');

    // Default address (auto-managed by observer)
    $table->boolean('is_default')->default(false);

    $table->timestamps();

    // Indexes
    $table->index(['addressable_type', 'addressable_id']);
    $table->index(['addressable_type', 'addressable_id', 'is_default']);
    $table->index('postal_code');
    $table->index('state_code');
});
```

**Why Polymorphic?**
- ✅ Users can have multiple addresses
- ✅ Same table for warehouses (future)
- ✅ Clean separation of concerns
- ✅ Reusable across entities

---

### **Migration 3: Create KYCs Table**
```php
Schema::create('kycs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

    // Identity documents
    $table->string('aadhaar', 12)->unique();
    $table->string('pan', 10)->unique();

    // Verification workflow
    $table->string('status')->default('pending'); // pending, verified, rejected
    $table->text('rejection_reason')->nullable();
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

    // Document metadata (for Spatie Media Library)
    // aadhaarImage collection
    // panImage collection

    $table->timestamps();

    $table->index(['user_id', 'status']);
    $table->index('status');
});
```

---

### **Migration 4: Create Geo Data Tables** (India-specific)
```php
// States table (Indian states + UTs)
Schema::create('states', function (Blueprint $table) {
    $table->id();
    $table->string('code', 10)->unique(); // ISO code (e.g., "DL", "MH")
    $table->string('name'); // Delhi, Maharashtra
    $table->string('country_code', 2)->default('IN');
    $table->boolean('is_union_territory')->default(false);
    $table->timestamps();
});

// Blocks table (municipalities)
Schema::create('blocks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('state_code', 10);
    $table->string('district')->nullable();
    $table->timestamps();

    $table->index('state_code');
    $table->index(['state_code', 'district']);
});
```

---

## 🎨 **Onboarding Flow (Final Design)**

### **Registration → Onboarding Trigger**
```
User registers (mobile + OTP)
  ↓
User created:
  - onboarded = false
  - status = draft
  - mobile_verified_at = now()
  ↓
Auto-login + Sanctum token
  ↓
Redirect to /onboarding?welcome=true
```

---

### **4-Step Wizard Flow**

```
┌────────────────────────────────────────────────────┐
│  🎯 Complete Your Profile                          │
│  ━━━━●━━━━○━━━━○━━━━○   Step 1 of 4 (25%)        │
├────────────────────────────────────────────────────┤
│                                                     │
│  📋 Step 1: Basic Profile                          │
│                                                     │
│  We already have your mobile number. Let's         │
│  complete the rest of your profile.                │
│                                                     │
│  ┌──────────────────────────────────────┐          │
│  │ Full Name *    [John Doe........]   │          │
│  │ Email          [john@mail.com....]  │          │
│  │ Gender         [Select   ▼]         │          │
│  │ Date of Birth  [DD/MM/YYYY]         │          │
│  │ Bio            [..................] │          │
│  │                [..................]  │          │
│  └──────────────────────────────────────┘          │
│                                                     │
│  💡 Why we ask: Personalize your experience        │
│  and unlock community features                     │
│                                                     │
│  [Skip for now]              [Next: Address →]     │
└────────────────────────────────────────────────────┘
```

---

### **Step 1: Profile Information**
**Fields**:
- ✅ Name (pre-filled, editable) - **Required**
- ✅ Email (optional, editable) - **Optional**
- ✅ Mobile (pre-filled, disabled) - **Verified**
- ✅ Gender (dropdown) - **Optional**
- ✅ DOB (date picker) - **Optional**
- ✅ Bio (textarea, 500 chars) - **Optional**

**API Call**:
```
POST /api/onboarding/profile
Body: { name, email, gender, dob, bio }
Response: { step_completed: true, next_step: 'address' }
```

**Can Skip**: ❌ No (name is required)

---

### **Step 2: Delivery Address**
**Fields** (Popkult-inspired structure):
- ✅ Address Name (e.g., "Home", "Work") - **Required**
- ✅ Contact Number (for delivery calls) - **Required**
- ✅ Postal Code (6 digits, auto-fill button) - **Required**
- ✅ Street Address (line 1) - **Required**
- ✅ Apartment/Floor (line 2) - **Optional**
- ✅ Landmark - **Optional**
- ✅ Village/Town - **Optional** (for rural)
- ✅ City/District (auto-filled or dropdown) - **Required**
- ✅ State (dropdown) - **Required**
- ✅ Block/Municipality (dropdown) - **Optional**
- ✅ Set as default address (checkbox) - **Auto-checked first address**

**Smart Features**:
```typescript
// Postal code auto-fill
onPostalCodeEnter() {
  fetch(`https://api.postalpincode.in/pincode/${postalCode}`)
  // Auto-fills: city, district, state, blocks
}

// Cascading dropdowns
onStateChange() {
  fetchBlocks(stateCode) // Load blocks for selected state
  fetchDistricts(stateCode) // Load districts
}
```

**API Call**:
```
POST /api/onboarding/address
Body: {
  name, contact, alternate_contact, type,
  postal_code, address_1, address_2, landmark,
  village, city, district, state_code, block_id,
  is_default
}
Response: { step_completed: true, next_step: 'kyc' }
```

**Can Skip**: ✅ Yes (required only at checkout)

**Skip Logic**:
```php
// In checkout flow
if (!$user->hasAddress()) {
    return redirect('/onboarding/address?return=/checkout');
}
```

---

### **Step 3: KYC Verification**
**Purpose**: Required for wallet, commissions, payouts (Indian compliance)

**Fields**:
- ✅ Aadhaar Number (12 digits) - **Required**
- ✅ Aadhaar Document Upload (image/PDF) - **Required**
- ✅ PAN Number (ABCDE1234F format) - **Required**
- ✅ PAN Card Upload (image/PDF) - **Required**

**Document Upload**:
```typescript
<FileUpload
  accept="image/*,application/pdf"
  maxSize={2048} // 2MB
  onUpload={handleAadhaarUpload}
  preview={true} // Show image preview
  encryption={true} // Encrypt on upload
/>
```

**Validation**:
```php
'aadhaar' => 'required|regex:/^\d{12}$/|unique:kycs,aadhaar'
'pan' => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/|unique:kycs,pan'
'aadhaar_file' => 'required|file|mimes:jpeg,png,pdf|max:2048'
'pan_file' => 'required|file|mimes:jpeg,png,pdf|max:2048'
```

**API Call**:
```
POST /api/onboarding/kyc (multipart/form-data)
Body: FormData {
  aadhaar, pan, aadhaar_file, pan_file
}
Response: {
  step_completed: true,
  kyc_status: 'pending', // Will be verified by admin
  next_step: 'subscription'
}
```

**Can Skip**: ✅ Yes (required only for wallet access)

**Skip Logic**:
```php
// In wallet/withdrawal flow
if (!$user->hasVerifiedKyc()) {
    return redirect('/onboarding/kyc?return=/wallet');
}
```

---

### **Step 4: Membership Subscription**
**Purpose**: Offer paid membership with exclusive benefits

**UI**:
```
┌─────────────────────────────────────────┐
│  Choose Your Membership                 │
│                                          │
│  ┌────────────────────────────────┐     │
│  │ 👑 Premium Membership          │     │
│  │ ₹999/month                     │     │
│  │                                 │     │
│  │ ✓ Exclusive discounts          │     │
│  │ ✓ Early access to sales        │     │
│  │ ✓ Priority support             │     │
│  │ ✓ Affiliate commission boost         │     │
│  │ ✓ Member-only products         │     │
│  └────────────────────────────────┘     │
│                                          │
│  ○ Subscribe Now (₹999/month)           │
│  ○ Start Free (Regular Customer)        │
│                                          │
│  ☑ I agree to Terms & Conditions        │
│                                          │
│  [Complete Onboarding]                  │
└─────────────────────────────────────────┘
```

**API Call**:
```
POST /api/onboarding/subscription
Body: { subscription_type: 'subscribe' | 'skip', tnc: true }
Response: {
  step_completed: true,
  onboarded: true,
  redirect_url: '/checkout/{uuid}' or '/dashboard'
}
```

**Can Skip**: ✅ Yes (subscription is always optional)

**If Subscribe**: Redirect to `/checkout/{subscription_uuid}`
**If Skip**: Mark onboarded, redirect to `/dashboard`

---

## 🗄️ **Database Models (Enterprise)**

### **Address Model** (Polymorphic - Best Practice)
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'alternate_contact',
        'type',
        'postal_code',
        'address_1',
        'address_2',
        'landmark',
        'village',
        'city',
        'district',
        'state_code',
        'block_id',
        'country_code',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Auto-handle default address (only one can be default)
    protected static function booted(): void
    {
        static::saving(function (Address $address): void {
            if ($address->is_default && $address->addressable) {
                // Unset default for all other addresses of this owner
                $address->addressable->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_1,
            $this->address_2,
            $this->landmark,
            $this->village,
            $this->city,
            $this->state_code,
            $this->postal_code,
        ]));
    }
}
```

---

### **Kyc Model**
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Kyc extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'aadhaar',
        'pan',
        'status',
        'rejection_reason',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('aadhaarImage')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);

        $this->addMediaCollection('panImage')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    // Methods
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function verify(int $verifiedById): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedById,
        ]);
    }

    public function reject(string $reason, int $rejectedById): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_by' => $rejectedById,
        ]);
    }
}
```

---

### **User Model Relationships**
```php
// In User model
public function addresses(): MorphMany
{
    return $this->morphMany(Address::class, 'addressable');
}

public function defaultAddress(): MorphOne
{
    return $this->morphOne(Address::class, 'addressable')
        ->where('is_default', true);
}

public function kyc(): HasOne
{
    return $this->hasOne(Kyc::class);
}

// Helper methods
public function hasAddress(): bool
{
    return $this->addresses()->exists();
}

public function hasVerifiedKyc(): bool
{
    return $this->kyc()->where('status', 'verified')->exists();
}

public function getOnboardingProgressAttribute(): array
{
    return [
        'profile_complete' => $this->gender && $this->dob,
        'address_added' => $this->hasAddress(),
        'kyc_submitted' => $this->kyc()->exists(),
        'kyc_verified' => $this->hasVerifiedKyc(),
        'onboarded' => $this->onboarded,
        'percentage' => $this->calculateOnboardingPercentage(),
    ];
}

private function calculateOnboardingPercentage(): int
{
    $steps = 0;
    $completed = 0;

    // Profile
    $steps++;
    if ($this->gender && $this->dob) $completed++;

    // Address
    $steps++;
    if ($this->hasAddress()) $completed++;

    // KYC
    $steps++;
    if ($this->kyc()->exists()) $completed++;

    // Subscription (implicit - always "complete" if onboarded)
    $steps++;
    if ($this->onboarded) $completed++;

    return (int) (($completed / $steps) * 100);
}
```

---

## 🚀 **Backend Implementation**

### **Controller**: `OnboardingController`
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\CompleteProfileRequest;
use App\Http\Requests\Onboarding\AddAddressRequest;
use App\Http\Requests\Onboarding\SubmitKycRequest;
use App\Http\Requests\Onboarding\ChooseSubscriptionRequest;
use App\Models\Address;
use App\Models\Kyc;
use Illuminate\Http\JsonResponse;

final class OnboardingController extends Controller
{
    /**
     * Get onboarding status and progress
     */
    public function status(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'data' => [
                'onboarded' => $user->onboarded,
                'progress' => $user->onboarding_progress,
                'current_step' => $this->getCurrentStep($user),
                'steps' => [
                    [
                        'key' => 'profile',
                        'label' => 'Basic Profile',
                        'completed' => (bool) ($user->gender && $user->dob),
                        'required' => true,
                    ],
                    [
                        'key' => 'address',
                        'label' => 'Delivery Address',
                        'completed' => $user->hasAddress(),
                        'required' => false,
                        'required_for' => ['checkout', 'orders'],
                    ],
                    [
                        'key' => 'kyc',
                        'label' => 'KYC Verification',
                        'completed' => $user->kyc()->exists(),
                        'required' => false,
                        'required_for' => ['wallet', 'withdrawals', 'commissions'],
                    ],
                    [
                        'key' => 'subscription',
                        'label' => 'Membership',
                        'completed' => $user->onboarded,
                        'required' => false,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Complete profile step
     */
    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'bio' => $request->input('bio'),
            'gender' => $request->input('gender'),
            'dob' => $request->input('dob'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'next_step' => 'address',
            ],
        ]);
    }

    /**
     * Add address step
     */
    public function addAddress(AddAddressRequest $request): JsonResponse
    {
        $user = $request->user();

        // Create address (polymorphic)
        $address = $user->addresses()->create([
            'name' => $request->input('name'),
            'contact' => $request->input('contact'),
            'alternate_contact' => $request->input('alternate_contact'),
            'type' => $request->input('type', 'home'),
            'postal_code' => $request->input('postal_code'),
            'address_1' => $request->input('address_1'),
            'address_2' => $request->input('address_2'),
            'landmark' => $request->input('landmark'),
            'village' => $request->input('village'),
            'city' => $request->input('city'),
            'district' => $request->input('district'),
            'state_code' => $request->input('state_code'),
            'block_id' => $request->input('block_id'),
            'country_code' => 'IN',
            'is_default' => $request->input('is_default', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'data' => [
                'address' => $address,
                'next_step' => 'kyc',
            ],
        ]);
    }

    /**
     * Submit KYC documents
     */
    public function submitKyc(SubmitKycRequest $request): JsonResponse
    {
        $user = $request->user();

        // Create or update KYC
        $kyc = $user->kyc()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'aadhaar' => $request->input('aadhaar'),
                'pan' => strtoupper($request->input('pan')),
                'status' => 'pending',
                'submitted_at' => now(),
            ]
        );

        // Handle file uploads
        if ($request->hasFile('aadhaar_file')) {
            $kyc->clearMediaCollection('aadhaarImage');
            $kyc->addMediaFromRequest('aadhaar_file')
                ->toMediaCollection('aadhaarImage');
        }

        if ($request->hasFile('pan_file')) {
            $kyc->clearMediaCollection('panImage');
            $kyc->addMediaFromRequest('pan_file')
                ->toMediaCollection('panImage');
        }

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted successfully. Verification may take 24-48 hours.',
            'data' => [
                'kyc' => $kyc,
                'next_step' => 'subscription',
            ],
        ]);
    }

    /**
     * Choose subscription (final step)
     */
    public function chooseSubscription(ChooseSubscriptionRequest $request): JsonResponse
    {
        $user = $request->user();

        $subscriptionType = $request->input('subscription_type');
        $tnc = $request->boolean('tnc');

        if (!$tnc) {
            return response()->json([
                'success' => false,
                'message' => 'You must accept Terms & Conditions',
            ], 422);
        }

        // Mark onboarded
        $user->update([
            'onboarded' => true,
            'status' => 'active',
        ]);

        // If subscribe, create subscription and redirect to checkout
        if ($subscriptionType === 'subscribe') {
            // Create subscription order (implementation depends on subscription system)
            $subscriptionUuid = $this->createSubscriptionOrder($user);

            return response()->json([
                'success' => true,
                'message' => 'Onboarding complete! Redirecting to checkout...',
                'data' => [
                    'onboarded' => true,
                    'redirect_url' => "/checkout/{$subscriptionUuid}",
                ],
            ]);
        }

        // Skip subscription - regular user
        return response()->json([
            'success' => true,
            'message' => 'Onboarding complete! Welcome to Commerinity Pro.',
            'data' => [
                'onboarded' => true,
                'redirect_url' => '/dashboard',
            ],
        ]);
    }

    /**
     * Skip a specific step
     */
    public function skipStep(string $step): JsonResponse
    {
        $allowedSkips = ['address', 'kyc', 'subscription'];

        if (!in_array($step, $allowedSkips)) {
            return response()->json([
                'success' => false,
                'message' => 'This step cannot be skipped',
            ], 422);
        }

        // Determine next step
        $nextStep = match ($step) {
            'address' => 'kyc',
            'kyc' => 'subscription',
            'subscription' => null, // Complete
            default => null,
        };

        return response()->json([
            'success' => true,
            'message' => 'Step skipped',
            'data' => [
                'skipped_step' => $step,
                'next_step' => $nextStep,
            ],
        ]);
    }

    private function getCurrentStep(User $user): string
    {
        if (!$user->gender || !$user->dob) return 'profile';
        if (!$user->hasAddress()) return 'address';
        if (!$user->kyc()->exists()) return 'kyc';
        if (!$user->onboarded) return 'subscription';
        return 'complete';
    }

    private function createSubscriptionOrder(User $user): string
    {
        // TODO: Implement subscription order creation
        // Return subscription order UUID for checkout
        return 'SUB'.uniqid();
    }
}
```

---

### **Form Requests**

#### **CompleteProfileRequest**
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'min:2', 'max:255'],
        'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$this->user()->id],
        'bio' => ['nullable', 'string', 'max:500'],
        'gender' => ['nullable', 'in:male,female,other'],
        'dob' => ['nullable', 'date', 'before:today'],
    ];
}
```

#### **AddAddressRequest**
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'], // "Home", "Work"
        'contact' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
        'alternate_contact' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
        'type' => ['required', 'in:home,work,billing,shipping'],
        'postal_code' => ['required', 'regex:/^\d{6}$/'],
        'address_1' => ['required', 'string', 'max:500'],
        'address_2' => ['nullable', 'string', 'max:500'],
        'landmark' => ['nullable', 'string', 'max:255'],
        'village' => ['nullable', 'string', 'max:255'],
        'city' => ['required', 'string', 'max:255'],
        'district' => ['nullable', 'string', 'max:255'],
        'state_code' => ['required', 'string', 'exists:states,code'],
        'block_id' => ['nullable', 'string', 'max:255'],
        'is_default' => ['boolean'],
    ];
}
```

#### **SubmitKycRequest**
```php
public function rules(): array
{
    $userId = $this->user()->id;

    return [
        'aadhaar' => ['required', 'regex:/^\d{12}$/', "unique:kycs,aadhaar,{$userId},user_id"],
        'pan' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/', "unique:kycs,pan,{$userId},user_id"],
        'aadhaar_file' => ['required', 'file', 'mimes:jpeg,png,pdf', 'max:2048'],
        'pan_file' => ['required', 'file', 'mimes:jpeg,png,pdf', 'max:2048'],
    ];
}

public function messages(): array
{
    return [
        'aadhaar.regex' => 'Aadhaar must be exactly 12 digits',
        'pan.regex' => 'PAN must be in format: ABCDE1234F',
    ];
}
```

---

## 🎨 **Frontend Implementation**

### **Onboarding Wizard** (`pages/onboarding/index.vue`)
```vue
<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-900 dark:to-slate-800 p-6">
    <div class="max-w-4xl mx-auto">

      <!-- Progress Header -->
      <div class="glass-card p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h1 class="text-2xl font-bold gradient-text-primary">Complete Your Profile</h1>
          <div class="text-sm text-slate-600 dark:text-slate-400">
            Step {{ currentStepIndex + 1 }} of {{ steps.length }}
          </div>
        </div>

        <!-- Progress Stepper -->
        <div class="flex items-center justify-between">
          <div
            v-for="(step, index) in steps"
            :key="step.key"
            class="flex items-center"
            :class="{ 'flex-1': index < steps.length - 1 }"
          >
            <!-- Step Circle -->
            <div class="relative">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all"
                :class="getStepClasses(step, index)"
              >
                <UIcon
                  v-if="step.completed"
                  name="i-lucide-check"
                  class="w-5 h-5 text-white"
                />
                <span v-else class="text-sm font-bold">{{ index + 1 }}</span>
              </div>
              <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-xs font-medium text-slate-600 dark:text-slate-400">
                {{ step.label }}
              </div>
            </div>

            <!-- Connector Line -->
            <div
              v-if="index < steps.length - 1"
              class="flex-1 h-0.5 mx-2 transition-colors"
              :class="step.completed ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-600'"
            ></div>
          </div>
        </div>
      </div>

      <!-- Current Step Content -->
      <div class="glass-card p-8 mb-6">
        <component
          :is="currentStepComponent"
          @next="handleNext"
          @skip="handleSkip"
          @back="handleBack"
        />
      </div>

      <!-- Help Text -->
      <div class="text-center text-sm text-slate-600 dark:text-slate-400">
        Need help? <a href="/support" class="text-blue-600 hover:underline">Contact Support</a>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
  layout: 'minimal' // Clean layout, no nav
})

const router = useRouter()

const steps = ref([
  { key: 'profile', label: 'Profile', component: 'ProfileStep', completed: false, required: true },
  { key: 'address', label: 'Address', component: 'AddressStep', completed: false, required: false },
  { key: 'kyc', label: 'KYC', component: 'KycStep', completed: false, required: false },
  { key: 'subscription', label: 'Plan', component: 'SubscriptionStep', completed: false, required: false }
])

const currentStepIndex = ref(0)

const currentStep = computed(() => steps.value[currentStepIndex.value])
const currentStepComponent = computed(() => resolveComponent(`Onboarding${currentStep.value.component}`))

onMounted(async () => {
  await loadOnboardingStatus()
})

async function loadOnboardingStatus() {
  const { data } = await useFetch('/api/onboarding/status')
  if (data.value?.data) {
    // Update step completion status
    data.value.data.steps.forEach((apiStep: any) => {
      const step = steps.value.find(s => s.key === apiStep.key)
      if (step) step.completed = apiStep.completed
    })

    // Navigate to first incomplete step
    const firstIncomplete = steps.value.findIndex(s => !s.completed)
    if (firstIncomplete !== -1) {
      currentStepIndex.value = firstIncomplete
    }

    // If all complete, redirect to dashboard
    if (data.value.data.onboarded) {
      router.push('/dashboard')
    }
  }
}

function handleNext() {
  // Mark current as complete
  steps.value[currentStepIndex.value].completed = true

  // Move to next
  if (currentStepIndex.value < steps.value.length - 1) {
    currentStepIndex.value++
  } else {
    // All steps complete
    router.push('/dashboard')
  }
}

function handleSkip() {
  // Skip allowed only for optional steps
  if (!currentStep.value.required) {
    handleNext()
  }
}

function handleBack() {
  if (currentStepIndex.value > 0) {
    currentStepIndex.value--
  }
}

function getStepClasses(step: any, index: number) {
  if (step.completed) {
    return 'bg-blue-600 border-blue-600'
  }
  if (index === currentStepIndex.value) {
    return 'bg-white dark:bg-slate-800 border-blue-600 text-blue-600'
  }
  return 'bg-slate-100 dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-400'
}
</script>
```

---

### **Step Components**

#### **ProfileStep.vue**
```vue
<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
        Tell us about yourself
      </h2>
      <p class="text-slate-600 dark:text-slate-400">
        Help us personalize your experience
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Name -->
      <FormField
        v-model="form.name"
        label="Full Name"
        icon="i-lucide-user"
        required
        placeholder="John Doe"
      />

      <!-- Email -->
      <FormField
        v-model="form.email"
        label="Email Address"
        icon="i-lucide-mail"
        type="email"
        placeholder="you@example.com"
        hint="Optional, but recommended for password resets"
      />

      <!-- Gender -->
      <div class="space-y-2">
        <label class="text-sm font-semibold">Gender</label>
        <select v-model="form.gender" class="form-select">
          <option value="">Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>

      <!-- DOB -->
      <FormField
        v-model="form.dob"
        label="Date of Birth"
        icon="i-lucide-calendar"
        type="date"
        :max="maxDate"
      />

      <!-- Bio -->
      <div class="space-y-2">
        <label class="text-sm font-semibold">Bio (Optional)</label>
        <textarea
          v-model="form.bio"
          class="form-textarea"
          rows="3"
          maxlength="500"
          placeholder="Tell us about yourself..."
        ></textarea>
        <div class="text-xs text-right text-slate-500">
          {{ form.bio?.length || 0 }} / 500
        </div>
      </div>

      <!-- Error -->
      <div v-if="error" class="alert-error">{{ error }}</div>

      <!-- Actions -->
      <div class="flex gap-4 pt-4">
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary flex-1"
        >
          Continue to Address →
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
const emit = defineEmits(['next'])

const { user } = useUserType()
const config = useRuntimeConfig()

const loading = ref(false)
const error = ref<string | null>(null)

const maxDate = computed(() => new Date().toISOString().split('T')[0])

const form = reactive({
  name: user.value?.name || '',
  email: user.value?.email || '',
  bio: user.value?.bio || '',
  gender: user.value?.gender || '',
  dob: user.value?.dob || ''
})

async function submit() {
  loading.value = true
  error.value = null

  try {
    const token = useCookie('commerinity_auth_token').value

    await $fetch(`${config.public.apiBase}/api/onboarding/profile`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: form
    })

    emit('next')
  } catch (err: any) {
    error.value = err.data?.message || 'Failed to save profile'
  } finally {
    loading.value = false
  }
}
</script>
```

---

## 🚦 **Feature Gates (Contextual Requirements)**

### **Checkout Requires Address**
```php
// In CheckoutController or middleware
if (!auth()->user()->hasAddress()) {
    return response()->json([
        'error' => 'Address required to proceed with checkout',
        'redirect' => '/onboarding/address?return=/checkout',
        'feature' => 'checkout',
    ], 403);
}
```

### **Wallet Requires KYC**
```php
// In WalletController or middleware
if (!auth()->user()->hasVerifiedKyc()) {
    return response()->json([
        'error' => 'KYC verification required to access wallet',
        'redirect' => '/onboarding/kyc?return=/wallet',
        'feature' => 'wallet',
        'kyc_status' => auth()->user()->kyc?->status ?? 'not_submitted',
    ], 403);
}
```

### **Commission Withdrawal Requires Both**
```php
// In WithdrawalController
$user = auth()->user();

if (!$user->hasAddress()) {
    return response()->json(['error' => 'Address required', 'redirect' => '/onboarding/address'], 403);
}

if (!$user->hasVerifiedKyc()) {
    return response()->json(['error' => 'Verified KYC required', 'redirect' => '/onboarding/kyc'], 403);
}
```

---

## 📱 **Onboarding Banner** (Dashboard)

### **Component**: `OnboardingBanner.vue`
```vue
<template>
  <div v-if="showBanner" class="banner-gradient mb-6 rounded-2xl p-6 relative overflow-hidden">
    <!-- Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-90"></div>

    <div class="relative z-10 flex items-center gap-6">
      <!-- Progress Ring -->
      <div class="relative w-20 h-20 flex-shrink-0">
        <svg class="w-full h-full -rotate-90">
          <circle
            cx="40" cy="40" r="36"
            stroke="rgba(255,255,255,0.2)"
            stroke-width="4"
            fill="none"
          />
          <circle
            cx="40" cy="40" r="36"
            stroke="white"
            stroke-width="4"
            fill="none"
            stroke-linecap="round"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="dashoffset"
            class="transition-all duration-1000"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-2xl font-bold text-white">{{ progress }}%</span>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1">
        <h3 class="text-xl font-bold text-white mb-1">
          {{ nextStep?.label || 'Profile Complete!' }}
        </h3>
        <p class="text-white/80 text-sm">
          {{ nextStep?.description || 'You're all set!' }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex gap-2 flex-shrink-0">
        <NuxtLink v-if="nextStep" :to="'/onboarding'" class="btn-white">
          Continue Setup
        </NuxtLink>
        <button @click="minimize" class="btn-icon-white">
          <UIcon name="i-lucide-minus" />
        </button>
      </div>
    </div>

    <!-- Minimized Preview -->
    <div v-if="isMinimized" class="mt-4 flex gap-2">
      <button
        v-for="step in steps"
        :key="step.key"
        @click="goToStep(step)"
        class="step-mini"
        :class="step.completed ? 'completed' : 'pending'"
      >
        <UIcon :name="step.icon" class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const { user } = useUserType()

const isMinimized = ref(false)
const progress = ref(0)
const steps = ref([])
const nextStep = ref(null)

const circumference = 2 * Math.PI * 36
const dashoffset = computed(() => circumference - (progress.value / 100) * circumference)
const showBanner = computed(() => !user.value?.onboarded && progress.value < 100)

onMounted(async () => {
  await loadProgress()
})

async function loadProgress() {
  const { data } = await useFetch('/api/onboarding/status')
  if (data.value?.data) {
    steps.value = data.value.data.steps
    progress.value = data.value.data.progress.percentage
    nextStep.value = steps.value.find(s => !s.completed)
  }
}

function minimize() {
  isMinimized.value = !isMinimized.value
}

function goToStep(step: any) {
  navigateTo('/onboarding')
}
</script>
```

---

## 🎮 **Gamification & UX Enhancements**

### **1. Progress Celebration**
```typescript
// When user completes a step
if (step.completed) {
  showConfetti() // Confetti animation
  playSound('success') // Optional success sound
  showToast({
    title: 'Step Complete! 🎉',
    message: `${step.label} completed. ${remainingSteps} steps remaining.`
  })
}

// When all steps complete
if (allStepsComplete) {
  showModal({
    title: 'Welcome to Commerinity Pro! 🎊',
    message: 'Your profile is complete. Start exploring now!',
    action: 'Go to Dashboard'
  })
}
```

### **2. Smart Hints**
```vue
<!-- Show benefits for each step -->
<div class="info-box">
  <UIcon name="i-lucide-lightbulb" />
  <p>Adding your address now saves time during checkout!</p>
</div>

<div class="info-box">
  <UIcon name="i-lucide-shield-check" />
  <p>Verified KYC unlocks wallet and commission features.</p>
</div>
```

### **3. Estimated Time**
```vue
<div class="text-sm text-slate-600">
  ⏱ Estimated time: 2 minutes
</div>
```

### **4. Exit Confirmation**
```typescript
// If user tries to close onboarding
onBeforeLeave((to, from) => {
  if (!user.value?.onboarded && hasUnsavedChanges) {
    const confirmed = confirm('Your progress will be saved. Continue later?')
    if (!confirmed) return false
  }
})
```

---

## 🧪 **Testing Strategy**

### **Backend Tests**
```php
// tests/Feature/OnboardingTest.php

it('returns onboarding status for new user', function () {
    $user = User::factory()->create(['onboarded' => false]);

    $this->actingAs($user)
        ->getJson('/api/onboarding/status')
        ->assertOk()
        ->assertJson([
            'data' => [
                'onboarded' => false,
                'current_step' => 'profile',
            ],
        ]);
});

it('completes profile step', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/onboarding/profile', [
            'name' => 'Updated Name',
            'gender' => 'male',
            'dob' => '1990-01-01',
        ])
        ->assertOk()
        ->assertJson(['data' => ['next_step' => 'address']]);

    expect($user->fresh())
        ->gender->toBe('male')
        ->dob->toEqual('1990-01-01');
});

it('adds address with polymorphic relationship', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/onboarding/address', [
            'name' => 'Home',
            'contact' => '+919876543210',
            'postal_code' => '110001',
            'address_1' => '123 Main St',
            'city' => 'New Delhi',
            'state_code' => 'DL',
            'is_default' => true,
        ])
        ->assertCreated();

    expect($user->addresses()->count())->toBe(1)
        ->and($user->defaultAddress)->not->toBeNull();
});

it('submits KYC with file uploads', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/api/onboarding/kyc', [
            'aadhaar' => '123456789012',
            'pan' => 'ABCDE1234F',
            'aadhaar_file' => UploadedFile::fake()->image('aadhaar.jpg'),
            'pan_file' => UploadedFile::fake()->image('pan.jpg'),
        ])
        ->assertCreated();

    expect($user->kyc)->not->toBeNull()
        ->and($user->kyc->getMedia('aadhaarImage'))->toHaveCount(1);
});

it('marks user as onboarded after subscription choice', function () {
    $user = User::factory()->create(['onboarded' => false]);

    $this->actingAs($user)
        ->postJson('/api/onboarding/subscription', [
            'subscription_type' => 'skip',
            'tnc' => true,
        ])
        ->assertOk();

    expect($user->fresh()->onboarded)->toBeTrue();
});

it('requires address before checkout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/checkout')
        ->assertForbidden()
        ->assertJson([
            'error' => 'Address required to proceed with checkout',
        ]);
});

it('requires verified KYC before wallet access', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/wallet')
        ->assertForbidden()
        ->assertJson([
            'error' => 'KYC verification required to access wallet',
        ]);
});
```

---

## 📋 **Implementation Checklist**

### **Day 1: Database & Models**
- [ ] Create `addresses` migration (polymorphic)
- [ ] Create `kycs` migration
- [ ] Create `states` migration (seed with Indian states)
- [ ] Create `blocks` migration
- [ ] Create Address model with HasFactory
- [ ] Create Kyc model with HasMedia
- [ ] Create AddressObserver (auto-default handling)
- [ ] Create Address factory
- [ ] Create Kyc factory
- [ ] Run migrations
- [ ] Seed states table (36 states + UTs)

### **Day 2: Backend API**
- [ ] Create OnboardingController
- [ ] Create CompleteProfileRequest
- [ ] Create AddAddressRequest
- [ ] Create SubmitKycRequest
- [ ] Create ChooseSubscriptionRequest
- [ ] Add 6 routes to `routes/api.php`:
  - GET `/api/onboarding/status`
  - POST `/api/onboarding/profile`
  - POST `/api/onboarding/address`
  - POST `/api/onboarding/kyc`
  - POST `/api/onboarding/subscription`
  - POST `/api/onboarding/skip/{step}`
- [ ] Create GeoController (states, blocks)
- [ ] Add 2 geo routes:
  - GET `/api/geo/states/{country}`
  - GET `/api/geo/blocks/{state}`

### **Day 3: Backend Tests**
- [ ] Create `tests/Feature/OnboardingTest.php` (10+ tests)
- [ ] Create `tests/Feature/Models/AddressTest.php`
- [ ] Create `tests/Feature/Models/KycTest.php`
- [ ] Test onboarding status
- [ ] Test each step completion
- [ ] Test skip functionality
- [ ] Test feature gates (checkout, wallet)
- [ ] Run all tests: `php artisan test`
- [ ] Ensure 100% pass rate

### **Day 4: Frontend Wizard**
- [ ] Create `pages/onboarding/index.vue` (wizard container)
- [ ] Create `components/onboarding/ProfileStep.vue`
- [ ] Create `components/onboarding/AddressStep.vue`
- [ ] Create `components/onboarding/KycStep.vue`
- [ ] Create `components/onboarding/SubscriptionStep.vue`
- [ ] Create `components/onboarding/ProgressStepper.vue`
- [ ] Create `composables/useOnboarding.ts`
- [ ] Add TypeScript types in `types/onboarding.ts`

### **Day 5: Frontend Banner & Integration**
- [ ] Create `components/OnboardingBanner.vue`
- [ ] Add banner to `layouts/dashboard.vue`
- [ ] Create onboarding middleware
- [ ] Add middleware to dashboard routes
- [ ] Test wizard navigation (next, back, skip)
- [ ] Test auto-save functionality
- [ ] Test postal code auto-fill
- [ ] Test file uploads

### **Day 6: Polish & Testing**
- [ ] E2E test: Complete onboarding flow
- [ ] Test feature gates (checkout blocked, wallet blocked)
- [ ] Test resume functionality
- [ ] Verify auto-default address logic
- [ ] Test KYC file previews
- [ ] Accessibility audit
- [ ] Mobile responsiveness check
- [ ] Update documentation
- [ ] Update ACTIVITY_LOG

---

## 🎯 **API Endpoints (Complete List)**

```
# Onboarding
GET    /api/onboarding/status           Get progress and current step
POST   /api/onboarding/profile          Complete profile step
POST   /api/onboarding/address          Add address step
POST   /api/onboarding/kyc              Submit KYC step
POST   /api/onboarding/subscription     Choose subscription (final step)
POST   /api/onboarding/skip/{step}      Skip optional step

# Geo Data
GET    /api/geo/states/{country}        List states (e.g., /api/geo/states/IN)
GET    /api/geo/blocks/{state}          List blocks for state (e.g., /api/geo/blocks/DL)
POST   /api/geo/postal-lookup           Postal code → address (proxy to India Post API)

# Address Management (Post-onboarding)
GET    /api/addresses                   List user's addresses
POST   /api/addresses                   Add new address
PUT    /api/addresses/{id}              Update address
DELETE /api/addresses/{id}              Delete address
POST   /api/addresses/{id}/default      Set as default

# KYC Management (Post-onboarding)
GET    /api/kyc                         Get KYC status
PUT    /api/kyc                         Update KYC (if rejected)
GET    /api/kyc/status                  Check verification status
```

---

## 💡 **Best Practices Applied**

### **1. Polymorphic Addresses** (From Popkult)
```php
// User can have multiple addresses
$user->addresses()->create([...]);

// Warehouse can have address
$warehouse->addresses()->create([...]);

// Auto-handle default
// Only one address can be default per entity (via Observer)
```

### **2. Auto-Save Progress**
```typescript
// Save on each field blur
onBlur(field) {
  debounce(() => {
    saveProgress({ [field]: value })
  }, 500)
}
```

### **3. Postal Code Smart Fill** (From Old Commerinity)
```typescript
async function fetchAddressByPostal(postalCode: string) {
  const response = await fetch(`https://api.postalpincode.in/pincode/${postalCode}`)
  const data = await response.json()

  if (data[0].Status === 'Success') {
    const po = data[0].PostOffice[0]
    form.city = po.District
    form.state_code = getStateCode(po.State)
    form.district = po.District
    // Auto-load blocks after state selected
    await fetchBlocks(form.state_code)
  }
}
```

### **4. Type-Aware Requirements**
```typescript
const requiredSteps = computed(() => {
  const type = user.value?.type

  return {
    profile: true, // Always required
    address: type !== 'regular', // Required for Affiliate users
    kyc: ['promoter', 'advisor', 'mentor'].includes(type), // Required for payouts
    subscription: false // Always optional
  }
})
```

### **5. Feature-Gated Access**
```typescript
// Frontend composable: useFeatureGate
export function useFeatureGate() {
  const { user } = useSanctum()

  async function requireAddress(returnUrl?: string) {
    if (!user.value?.has_address) {
      const url = '/onboarding/address'
      if (returnUrl) url += `?return=${returnUrl}`
      await navigateTo(url)
      return false
    }
    return true
  }

  async function requireKyc(returnUrl?: string) {
    if (!user.value?.kyc_verified) {
      const url = '/onboarding/kyc'
      if (returnUrl) url += `?return=${returnUrl}`
      await navigateTo(url)
      return false
    }
    return true
  }

  return { requireAddress, requireKyc }
}

// Usage in checkout page
const { requireAddress } = useFeatureGate()

async function proceedToCheckout() {
  if (await requireAddress('/checkout')) {
    // Continue with checkout
  }
}
```

---

## 🎨 **UI Component Library**

### **Reusable Components**

#### **FormField.vue** (Consistent field styling)
```vue
<template>
  <div class="space-y-2">
    <label class="flex items-center gap-2 text-sm font-semibold">
      <UIcon v-if="icon" :name="icon" class="w-4 h-4" />
      <span>{{ label }}</span>
      <span v-if="required" class="text-red-500">*</span>
      <span v-if="optional" class="text-slate-500 text-xs">(Optional)</span>
    </label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :type="type"
      :placeholder="placeholder"
      :required="required"
      :maxlength="maxlength"
      class="form-input"
    />
    <p v-if="hint" class="text-xs text-slate-500">{{ hint }}</p>
    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
  </div>
</template>
```

#### **FileUpload.vue** (KYC documents)
```vue
<template>
  <div class="space-y-3">
    <label class="block text-sm font-semibold">{{ label }}</label>

    <div class="flex gap-4">
      <!-- Upload Area -->
      <div
        @click="triggerFileInput"
        @drop.prevent="handleDrop"
        @dragover.prevent
        class="flex-1 border-2 border-dashed rounded-xl p-6 cursor-pointer hover:border-blue-500 transition-colors"
      >
        <div class="text-center">
          <UIcon name="i-lucide-upload-cloud" class="w-12 h-12 mx-auto mb-2 text-slate-400" />
          <p class="text-sm text-slate-600">Click to upload or drag & drop</p>
          <p class="text-xs text-slate-500 mt-1">{{ accept }} • Max {{ maxSizeMB }}MB</p>
        </div>
        <input
          ref="fileInput"
          type="file"
          :accept="accept"
          @change="handleFileChange"
          class="hidden"
        />
      </div>

      <!-- Preview -->
      <div v-if="previewUrl" class="w-40 h-40 border rounded-xl overflow-hidden">
        <img :src="previewUrl" class="w-full h-full object-cover" />
      </div>
    </div>
  </div>
</template>
```

---

## 📊 **Analytics & Tracking**

### **Track Onboarding Funnel**
```sql
-- Onboarding completion funnel
SELECT
    'Registered' as stage,
    COUNT(*) as users
FROM users
UNION ALL
SELECT
    'Profile Complete',
    COUNT(*)
FROM users
WHERE gender IS NOT NULL AND dob IS NOT NULL
UNION ALL
SELECT
    'Address Added',
    COUNT(*)
FROM users
WHERE EXISTS(SELECT 1 FROM addresses WHERE addressable_id = users.id AND addressable_type = 'App\\Models\\User')
UNION ALL
SELECT
    'KYC Submitted',
    COUNT(*)
FROM users
WHERE EXISTS(SELECT 1 FROM kycs WHERE user_id = users.id)
UNION ALL
SELECT
    'Fully Onboarded',
    COUNT(*)
FROM users
WHERE onboarded = true;
```

### **Track Drop-off Points**
```php
// In analytics dashboard
$metrics = [
    'total_registered' => User::count(),
    'profile_complete' => User::whereNotNull('gender')->whereNotNull('dob')->count(),
    'address_added' => User::has('addresses')->count(),
    'kyc_submitted' => User::has('kyc')->count(),
    'fully_onboarded' => User::where('onboarded', true)->count(),
];

$dropOffRates = [
    'profile_to_address' => (1 - $metrics['address_added'] / $metrics['profile_complete']) * 100,
    'address_to_kyc' => (1 - $metrics['kyc_submitted'] / $metrics['address_added']) * 100,
    'kyc_to_complete' => (1 - $metrics['fully_onboarded'] / $metrics['kyc_submitted']) * 100,
];
```

---

## 🎁 **User Benefits Messaging**

### **Step 1: Profile**
> **Why complete your profile?**
> - Personalize your shopping experience
> - Join the Commerinity community
> - Unlock referral features
> - Get birthday rewards

### **Step 2: Address**
> **Why add your address now?**
> - Skip this step at checkout (faster ordering)
> - Get accurate delivery estimates
> - Unlock location-based offers
> - Save 2 minutes during your first purchase

### **Step 3: KYC**
> **Why verify your identity?**
> - Unlock digital wallet
> - Earn and withdraw commissions
> - Participate in Affiliate rewards
> - Secure your account (fraud prevention)

### **Step 4: Subscription**
> **Why become a member?**
> - Save 10-30% on every order
> - Get early access to sales
> - Earn higher commission rates
> - Priority customer support
> - Member-only exclusive products

---

## 🔐 **Security Considerations**

### **KYC Document Security**
```php
// Encrypt documents at rest
$kyc->addMediaFromRequest('aadhaar_file')
    ->withCustomProperties(['encrypted' => true])
    ->toMediaCollection('aadhaarImage', 'secure'); // Use secure disk

// Restrict access
public function downloadAadhaar(Kyc $kyc)
{
    $this->authorize('view', $kyc); // Only owner or admin

    return $kyc->getFirstMedia('aadhaarImage')
        ->stream(); // Stream, don't expose URL
}
```

### **Address Validation**
```php
// Verify postal code format (India)
'postal_code' => 'required|regex:/^[1-9][0-9]{5}$/'

// Verify state exists
'state_code' => 'required|exists:states,code'

// Sanitize contact numbers
'contact' => 'required|regex:/^\+?[1-9]\d{1,14}$/' // E.164 format
```

---

## 🚀 **Final Recommendations**

### **Approach: Hybrid System**
✅ **Wizard** (focused, step-by-step) when user clicks "Continue Setup"
✅ **Banner** (persistent reminder) shown in dashboard
✅ **Feature Gates** (JIT requirements) when accessing locked features

### **Workflow**
```
Register → Auto-login → Show Welcome Modal
  ↓
"Complete your profile now" or "I'll do it later"
  ↓
If Now: → /onboarding (wizard)
If Later: → /dashboard (banner shown)
  ↓
User shops, tries to checkout
  ↓
If no address: → /onboarding/address (wizard starts at step 2)
  ↓
User completes address → Back to checkout
```

### **Key Principles**
1. ✅ **Non-Blocking**: Never force onboarding, allow skip and resume
2. ✅ **Contextual**: Require data when needed (JIT)
3. ✅ **Progressive**: One step at a time
4. ✅ **Rewarding**: Celebrate progress
5. ✅ **Flexible**: Allow editing after completion

---

## 📦 **Deliverables Summary**

### **Backend** (2-3 days)
- 4 database migrations
- 2 models (Address, Kyc)
- 1 observer (AddressObserver)
- 1 controller (OnboardingController, 6 methods)
- 1 geo controller (GeoController, 2 methods)
- 4 form requests
- 15+ Pest tests
- 8 new API routes

### **Frontend** (2-3 days)
- 1 wizard page
- 4 step components
- 1 banner component
- 1 composable
- 1 middleware
- TypeScript types

### **Documentation**
- API documentation
- User guide
- Admin guide (KYC verification)

---

## ⏱️ **Estimated Timeline: 6 Days**

**Day 1**: Migrations + Models + Factories
**Day 2**: Backend Controllers + Requests + Routes
**Day 3**: Backend Tests (ensure 100% pass)
**Day 4**: Frontend Wizard + Step Components
**Day 5**: Frontend Banner + Integration
**Day 6**: E2E Testing + Polish + Documentation

---

## ✅ **Success Criteria**

- [ ] User can complete onboarding in under 5 minutes
- [ ] All optional steps can be skipped
- [ ] Progress is saved automatically
- [ ] Feature gates work correctly
- [ ] Address auto-fill works for Indian postal codes
- [ ] KYC files upload and preview correctly
- [ ] Default address logic works (only one default)
- [ ] Banner shows correct progress percentage
- [ ] Wizard is mobile-responsive
- [ ] All backend tests pass (20+ tests)
- [ ] E2E test covers full flow

---

**Status**: ✅ **INDUSTRY-STANDARD PLAN COMPLETE**
**Ready for**: Implementation approval and execution
**Confidence**: 100% (combines best of both systems + industry standards)
