# User Onboarding Process - Complete Plan

**Date**: 2025-12-09
**Status**: 🔵 PLANNING
**Priority**: HIGH

## Executive Summary

This document outlines the complete user onboarding process for the refactored Commerinity Pro platform, combining best practices from **old commerinity** (geo-hierarchical address system) and **popkult** (polymorphic address pattern with comprehensive tests).

---

## Table of Contents

1. [Address System Architecture](#address-system-architecture)
2. [Onboarding Flow Design](#onboarding-flow-design)
3. [Database Schema](#database-schema)
4. [Models & Relationships](#models--relationships)
5. [API Endpoints](#api-endpoints)
6. [Validation Rules](#validation-rules)
7. [Testing Strategy](#testing-strategy)
8. [Frontend Implementation](#frontend-implementation)
9. [Implementation Phases](#implementation-phases)

---

## Address System Architecture

### Overview

Our address system combines:
- **Old Commerinity**: Geo-hierarchical breakdown (Country → State → Block) with JSON seed data
- **Popkult**: Polymorphic `addressable` pattern with type-based categorization

### Address Types

```
User Addresses (Polymorphic):
├── home       # Primary residence
├── office     # Work location
├── billing    # Billing address
└── shipping   # Delivery address

System Addresses (Standalone):
├── warehouse  # Inventory storage locations
└── store      # Pickup locations
```

### Key Features

1. **Polymorphic Ownership**
   - Users have multiple addresses via `morphMany`
   - Warehouses/stores are standalone (null `addressable`)

2. **Geo-Hierarchical Breakdown**
   - Country (ISO codes, currency, timezone, locale)
   - State (code, country_id)
   - Block/District (name, state_code, lat/long)
   - City (string field in address)

3. **Default Address Logic**
   - One default per user (auto-updates siblings)
   - Standalone addresses isolated from user addresses

4. **Location Services**
   - Latitude/longitude support (future Google Maps integration)
   - Block-level precision for Indian addresses

---

## Onboarding Flow Design

### Multi-Step Wizard

```
Step 1: Registration (Already Exists)
├── Email/Mobile (at least one required)
├── Password
├── Name
└── Referral code (optional)

Step 2: Basic Profile ⭐ NEW
├── Date of Birth
├── Gender
├── Bio (optional)
└── Avatar upload (optional)

Step 3: Primary Address ⭐ NEW
├── Address Type (home/office)
├── Full Name (for address)
├── Contact Number
├── Address Line 1
├── Address Line 2 (optional)
├── Landmark (optional)
├── Country (dropdown)
├── State (dropdown, filtered by country)
├── Block/District (dropdown, filtered by state)
├── City (text input)
├── Postal Code
└── Set as default (checkbox)

Step 4: KYC Documents (Optional)
├── ID Proof (Aadhaar/PAN/Passport)
├── Address Proof
└── Photo

Step 5: Preferences
├── Communication preferences
├── Notification settings
└── Privacy settings

Step 6: Complete
├── Email verification sent
├── Mobile OTP sent
└── Dashboard redirect
```

### User Status Flow

```
DRAFT (registration)
  ↓
PENDING (onboarding incomplete)
  ↓
ACTIVE (onboarding complete, verified)
  ↓
SUSPENDED (admin action)
  ↓
BANNED (violation)
```

### Onboarding Completion Criteria

User is considered "onboarded" when:
- ✅ Basic profile filled (name, dob, gender)
- ✅ At least one address added
- ✅ Email OR mobile verified
- ❌ KYC optional (required for specific features)

---

## Database Schema

### 1. Countries Table (Existing - Old Commerinity)

```php
Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('iso_code_2', 2)->unique()->index();
    $table->string('iso_code_3', 3)->unique();
    $table->integer('isd_code'); // International dialing code
    $table->string('address_format')->nullable();
    $table->boolean('postcode_required')->default(true);
    $table->string('locale', 5)->default('en');
    $table->string('region', 50); // Asia, Europe, etc.
    $table->string('timezone', 50);
    $table->string('timezone_diff', 10); // +05:30
    $table->string('currency', 3)->default('USD');
    $table->string('flag')->nullable(); // Flag emoji or URL
    $table->json('exchange_rate')->nullable();
    $table->float('multiplier')->default(1);
    $table->boolean('is_active')->default(false);
    $table->timestamps();
});
```

### 2. States Table (Existing - Old Commerinity)

```php
Schema::create('states', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code', 10)->index();
    $table->foreignId('country_id')
        ->constrained('countries')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();

    $table->unique(['code', 'country_id']);
    $table->timestamps();
});
```

### 3. Blocks Table (Existing - Old Commerinity)

```php
Schema::create('blocks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('url')->unique();
    $table->string('district_name')->nullable();

    $table->string('state_code', 10)->nullable();
    $table->foreign('state_code')
        ->references('code')
        ->on('states')
        ->cascadeOnUpdate()
        ->nullOnDelete();

    // Geo-coordinates for future Maps integration
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();

    $table->timestamps();
});
```

### 4. Addresses Table (NEW - Combined Approach)

```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 36)->unique();

    // Contact Information
    $table->string('title')->nullable(); // "Home", "Office", "Mom's House"
    $table->string('person_name');
    $table->string('person_email')->nullable()->index();
    $table->string('person_mobile', 15);
    $table->string('alternate_contact', 15)->nullable();

    // Address Type
    $table->enum('type', ['home', 'office', 'billing', 'shipping', 'warehouse', 'store'])
        ->default('home')
        ->index();

    // Address Details
    $table->text('address_1');
    $table->text('address_2')->nullable();
    $table->string('landmark')->nullable();
    $table->string('city');
    $table->string('postal_code', 10)->index();

    // Geo-Hierarchical References
    $table->foreignId('block_id')
        ->nullable()
        ->constrained('blocks')
        ->cascadeOnUpdate()
        ->nullOnDelete();

    $table->string('state_code', 10)->nullable();
    $table->foreign('state_code')
        ->references('code')
        ->on('states')
        ->cascadeOnUpdate()
        ->nullOnDelete();

    $table->string('country_code', 2)->default('IN');
    $table->foreign('country_code')
        ->references('iso_code_2')
        ->on('countries')
        ->cascadeOnUpdate()
        ->restrictOnDelete();

    // Optional: Direct lat/long (overrides block location)
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();

    // Metadata
    $table->boolean('default')->default(false);
    $table->unsignedInteger('priority')->default(1);
    $table->string('pickup_location')->nullable(); // For warehouse/store

    // Polymorphic Ownership (null = standalone address)
    $table->nullableMorphs('addressable');

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index(['addressable_type', 'addressable_id']);
    $table->index(['type', 'default']);
});
```

### 5. Users Table Updates (Minimal Changes)

```php
// Already has:
$table->boolean('onboarded')->default(false);
$table->string('status')->default(UserStatusCast::DRAFT->value);

// Additional indexes (optional optimization)
$table->index('onboarded');
```

---

## Models & Relationships

### User Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRecursiveRelationships, Notifiable;

    protected $fillable = [
        'uuid', 'name', 'email', 'mobile', 'password',
        'referral_code', 'parent_id', 'bio', 'gender', 'dob',
        'type', 'status', 'status_feedback', 'onboarded',
        'email_verified_at', 'mobile_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'gender' => GenderCast::class,
            'type' => UserTypeCast::class,
            'status' => UserStatusCast::class,
            'onboarded' => 'boolean',
        ];
    }

    // Addresses Relationship
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    // Default Address Helper
    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('default', true)->first();
    }

    // Check Onboarding Completion
    public function isOnboardingComplete(): bool
    {
        return $this->onboarded
            && $this->addresses()->exists()
            && ($this->hasVerifiedEmail() || $this->hasVerifiedMobile());
    }

    // Mobile Verification Check
    public function hasVerifiedMobile(): bool
    {
        return !is_null($this->mobile_verified_at);
    }
}
```

### Address Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'title', 'person_name', 'person_email', 'person_mobile',
        'alternate_contact', 'type', 'address_1', 'address_2', 'landmark',
        'city', 'postal_code', 'block_id', 'state_code', 'country_code',
        'latitude', 'longitude', 'default', 'priority', 'pickup_location',
    ];

    protected function casts(): array
    {
        return [
            'default' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'priority' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Address $address) {
            if (!$address->uuid) {
                $address->uuid = (string) Str::uuid();
            }
        });

        static::saving(function (Address $address) {
            if ($address->default) {
                if ($address->addressable_id && $address->addressable_type) {
                    // User-owned: update only user's addresses
                    $address->addressable->addresses()
                        ->where('id', '!=', $address->id)
                        ->update(['default' => false]);
                } else {
                    // Standalone: update only standalone addresses of same type
                    Address::query()
                        ->where('id', '!=', $address->id)
                        ->where('type', $address->type)
                        ->whereNull('addressable_id')
                        ->whereNull('addressable_type')
                        ->update(['default' => false]);
                }
            }
        });
    }

    // Relationships
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'iso_code_2');
    }

    // Scopes
    public function scopeStandalone($query)
    {
        return $query->whereNull('addressable_id')
            ->whereNull('addressable_type');
    }

    public function scopeWarehouses($query)
    {
        return $query->standalone()->where('type', 'warehouse');
    }

    public function scopeStores($query)
    {
        return $query->standalone()->where('type', 'store');
    }

    public function scopeUserAddresses($query)
    {
        return $query->whereMorphedTo('addressable', User::class);
    }

    // Helpers
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_1,
            $this->address_2,
            $this->landmark,
            $this->city,
            $this->state?->name,
            $this->postal_code,
            $this->country?->name,
        ]);

        return implode(', ', $parts);
    }
}
```

### Country Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name', 'iso_code_2', 'iso_code_3', 'isd_code',
        'address_format', 'postcode_required', 'locale', 'region',
        'timezone', 'timezone_diff', 'currency', 'flag',
        'exchange_rate', 'multiplier', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'array',
            'postcode_required' => 'boolean',
            'is_active' => 'boolean',
            'multiplier' => 'float',
        ];
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'country_code', 'iso_code_2');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### State Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $fillable = ['name', 'code', 'country_id'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'state_code', 'code');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'state_code', 'code');
    }
}
```

### Block Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = [
        'name', 'url', 'district_name', 'state_code',
        'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
```

---

## API Endpoints

### Onboarding Endpoints

```php
// routes/api.php

Route::middleware(['auth:sanctum'])->group(function () {

    // Onboarding Status
    Route::get('/onboarding/status', [OnboardingController::class, 'status']);

    // Step 2: Profile
    Route::post('/onboarding/profile', [OnboardingController::class, 'updateProfile']);

    // Step 3: Address
    Route::post('/onboarding/address', [OnboardingController::class, 'addAddress']);

    // Complete Onboarding
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete']);

    // User Addresses CRUD
    Route::apiResource('addresses', AddressController::class);
    Route::patch('/addresses/{address}/set-default', [AddressController::class, 'setDefault']);
});

// Geo Data (Public)
Route::prefix('geo')->group(function () {
    Route::get('/countries', [GeoController::class, 'countries']);
    Route::get('/states/{country_code}', [GeoController::class, 'states']);
    Route::get('/blocks/{state_code}', [GeoController::class, 'blocks']);
});
```

### Example Response Formats

**GET /api/onboarding/status**
```json
{
  "success": true,
  "data": {
    "onboarded": false,
    "steps_completed": {
      "registration": true,
      "profile": false,
      "address": false,
      "kyc": false
    },
    "required_steps": ["profile", "address"],
    "next_step": "profile"
  }
}
```

**GET /api/geo/states/IN**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Maharashtra",
      "code": "MH",
      "country_id": 1
    },
    {
      "id": 2,
      "name": "Karnataka",
      "code": "KA",
      "country_id": 1
    }
  ]
}
```

---

## Validation Rules

### ProfileRequest (Step 2)

```php
namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', 'in:male,female,other'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'],
        ];
    }
}
```

### AddressRequest (Step 3)

```php
namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:home,office,billing,shipping'],
            'person_name' => ['required', 'string', 'max:255'],
            'person_email' => ['nullable', 'email', 'max:255'],
            'person_mobile' => ['required', 'regex:/^\+?[1-9]\d{1,14}$/', 'max:15'],
            'alternate_contact' => ['nullable', 'regex:/^\+?[1-9]\d{1,14}$/', 'max:15'],
            'address_1' => ['required', 'string', 'max:500'],
            'address_2' => ['nullable', 'string', 'max:500'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'regex:/^[0-9]{6}$/', 'max:10'],
            'country_code' => ['required', 'exists:countries,iso_code_2'],
            'state_code' => ['required', 'exists:states,code'],
            'block_id' => ['nullable', 'exists:blocks,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'default' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.regex' => 'Please enter a valid 6-digit PIN code.',
            'person_mobile.regex' => 'Please enter a valid mobile number with country code.',
        ];
    }
}
```

---

## Testing Strategy

### Test Categories

1. **Unit Tests** (`tests/Unit/Models/`)
   - Address model boot logic
   - Default address toggling
   - Geo-hierarchy relationships
   - User onboarding status

2. **Feature Tests** (`tests/Feature/`)
   - User address CRUD
   - Polymorphic relationships
   - Default address isolation
   - Onboarding flow
   - API endpoints

3. **Integration Tests**
   - Complete onboarding flow
   - Address with geo-hierarchy
   - Multi-user isolation

### Key Test Cases (Based on Popkult)

```php
// tests/Feature/UserAddressTest.php

test('user can have multiple addresses')
test('user addresses are properly polymorphic')
test('setting default user address updates other user addresses only')
test('setting default user address does not affect standalone addresses')
test('setting default user address does not affect other users addresses')
test('user can get default address')
test('user default address returns null when no default set')
test('user can have different address types')
test('user addresses can be deleted')
test('user addresses persist when user deleted') // Or cascade?
test('address scopes work correctly')
test('mixed ownership addresses maintain proper isolation')
test('user can filter addresses by type')
test('user addresses maintain data integrity')

// Additional Tests for Geo-Hierarchy

test('address belongs to correct country state block')
test('block provides latitude longitude to address')
test('state filtering by country works')
test('block filtering by state works')
test('address full_address attribute formats correctly')
test('india geo data seeds correctly')
```

### Test Data

Use **factories** for all models:

```php
// database/factories/AddressFactory.php

public function definition(): array
{
    return [
        'uuid' => (string) Str::uuid(),
        'title' => fake()->randomElement(['Home', 'Office', 'Parents House']),
        'person_name' => fake()->name(),
        'person_email' => fake()->safeEmail(),
        'person_mobile' => '+91' . fake()->numerify('##########'),
        'type' => 'home',
        'address_1' => fake()->streetAddress(),
        'city' => fake()->city(),
        'postal_code' => fake()->numerify('######'),
        'country_code' => 'IN',
        'state_code' => 'MH',
        'default' => false,
    ];
}

public function home(): static
{
    return $this->state(['type' => 'home']);
}

public function office(): static
{
    return $this->state(['type' => 'office']);
}

public function warehouse(): static
{
    return $this->state([
        'type' => 'warehouse',
        'addressable_id' => null,
        'addressable_type' => null,
    ]);
}

public function store(): static
{
    return $this->state([
        'type' => 'store',
        'addressable_id' => null,
        'addressable_type' => null,
    ]);
}
```

---

## Frontend Implementation

### Nuxt 4 Pages

```
client/pages/
├── auth/
│   ├── register.vue          # Step 1
│   └── verify-email.vue
├── onboarding/
│   ├── profile.vue           # Step 2
│   ├── address.vue           # Step 3
│   ├── kyc.vue               # Step 4 (optional)
│   ├── preferences.vue       # Step 5
│   └── complete.vue          # Step 6
└── dashboard/
    └── addresses/
        ├── index.vue         # List addresses
        ├── create.vue        # Add new
        └── [id]/edit.vue     # Edit existing
```

### Key Components

```vue
<!-- components/onboarding/AddressForm.vue -->
<template>
  <UForm :state="form" @submit="handleSubmit">
    <!-- Contact Info -->
    <UFormGroup label="Full Name" name="person_name">
      <UInput v-model="form.person_name" />
    </UFormGroup>

    <UFormGroup label="Mobile" name="person_mobile">
      <UInput v-model="form.person_mobile" type="tel" />
    </UFormGroup>

    <!-- Address Details -->
    <UFormGroup label="Address Line 1" name="address_1">
      <UTextarea v-model="form.address_1" />
    </UFormGroup>

    <!-- Geo Hierarchy -->
    <UFormGroup label="Country" name="country_code">
      <USelect
        v-model="form.country_code"
        :options="countries"
        @change="loadStates"
      />
    </UFormGroup>

    <UFormGroup label="State" name="state_code">
      <USelect
        v-model="form.state_code"
        :options="states"
        :disabled="!form.country_code"
        @change="loadBlocks"
      />
    </UFormGroup>

    <UFormGroup label="Block/District" name="block_id">
      <USelect
        v-model="form.block_id"
        :options="blocks"
        :disabled="!form.state_code"
      />
    </UFormGroup>

    <UFormGroup label="City" name="city">
      <UInput v-model="form.city" />
    </UFormGroup>

    <UFormGroup label="PIN Code" name="postal_code">
      <UInput v-model="form.postal_code" maxlength="6" />
    </UFormGroup>

    <!-- Default Checkbox -->
    <UCheckbox v-model="form.default" label="Set as default address" />

    <UButton type="submit" :loading="loading">
      Save Address
    </UButton>
  </UForm>
</template>

<script setup lang="ts">
const form = reactive({
  title: '',
  type: 'home',
  person_name: '',
  person_mobile: '',
  address_1: '',
  city: '',
  postal_code: '',
  country_code: 'IN',
  state_code: '',
  block_id: null,
  default: true,
})

const countries = ref([])
const states = ref([])
const blocks = ref([])

const loadStates = async () => {
  const { data } = await $fetch(`/api/geo/states/${form.country_code}`)
  states.value = data
}

const loadBlocks = async () => {
  const { data } = await $fetch(`/api/geo/blocks/${form.state_code}`)
  blocks.value = data
}
</script>
```

---

## Implementation Phases

### Phase 1: Database & Models (Week 1)

**Tasks:**
1. ✅ Review existing migrations
2. ⬜ Create `addresses` migration (combined approach)
3. ⬜ Create Country, State, Block models
4. ⬜ Create Address model with polymorphic relationships
5. ⬜ Add `addresses()` relationship to User model
6. ⬜ Create factories for all geo models
7. ⬜ Write unit tests for models

**Deliverables:**
- Migrations ready
- Models with relationships
- Factories for testing

---

### Phase 2: Seeders & Geo Data (Week 1-2)

**Tasks:**
1. ⬜ Prepare India geo JSON (countries, states, blocks)
2. ⬜ Create CountrySeeder
3. ⬜ Create StateSeeder
4. ⬜ Create BlockSeeder
5. ⬜ Run seeders and verify data
6. ⬜ Test geo-hierarchy queries

**Deliverables:**
- Complete India geo database
- Verified relationships

---

### Phase 3: API & Controllers (Week 2)

**Tasks:**
1. ⬜ Create GeoController (countries, states, blocks)
2. ⬜ Create AddressController (CRUD)
3. ⬜ Create OnboardingController (multi-step)
4. ⬜ Create Form Requests (validation)
5. ⬜ Create API Resources (transformers)
6. ⬜ Write feature tests for all endpoints

**Deliverables:**
- Working API endpoints
- Comprehensive validation
- API tests passing

---

### Phase 4: Frontend Implementation (Week 3)

**Tasks:**
1. ⬜ Create Nuxt pages (profile, address, complete)
2. ⬜ Build AddressForm component
3. ⬜ Implement cascading dropdowns (country → state → block)
4. ⬜ Add form validation
5. ⬜ Create onboarding wizard navigation
6. ⬜ Implement progress indicator
7. ⬜ Add success/error notifications

**Deliverables:**
- Complete onboarding UI
- Responsive design
- User-friendly flow

---

### Phase 5: Testing & Polish (Week 4)

**Tasks:**
1. ⬜ Run full test suite (backend)
2. ⬜ Add Pest browser tests (frontend flow)
3. ⬜ Test multi-user isolation
4. ⬜ Test edge cases (no default, multiple addresses)
5. ⬜ Performance optimization (eager loading)
6. ⬜ Add indexes for queries
7. ⬜ Documentation (API docs, README)

**Deliverables:**
- All tests passing
- Optimized queries
- Complete documentation

---

## Success Metrics

### Technical
- ✅ All Pest tests passing (unit + feature + browser)
- ✅ API response time < 200ms
- ✅ Database queries optimized (N+1 prevented)
- ✅ 100% test coverage for critical flows

### User Experience
- ✅ Onboarding completion in < 3 minutes
- ✅ Mobile-responsive design
- ✅ Clear error messages
- ✅ Intuitive cascading dropdowns

### Business
- ✅ 80%+ onboarding completion rate
- ✅ Accurate address data for shipping
- ✅ Support for multi-warehouse fulfillment

---

## Security Considerations

1. **Address Privacy**
   - Users can only view/edit their own addresses
   - Implement Policy for authorization
   - Soft delete for audit trail

2. **Geo Data Integrity**
   - Foreign key constraints
   - Cascade updates properly
   - Prevent deletion of active countries/states

3. **Input Validation**
   - Sanitize all inputs
   - Validate lat/long ranges
   - Check postal code format

4. **API Security**
   - Rate limiting on geo endpoints
   - Sanctum authentication required
   - CORS configured properly

---

## Future Enhancements

1. **Google Maps Integration**
   - Autocomplete address input
   - Pin location on map
   - Validate lat/long

2. **Address Verification**
   - India Post PIN code API
   - Google Maps Geocoding API
   - Suggest corrections

3. **Smart Defaults**
   - Pre-fill state/block from PIN code
   - Detect location from IP
   - Save preferences

4. **Analytics**
   - Track onboarding drop-off points
   - A/B test flow variations
   - Optimize conversion rate

---

## Conclusion

This comprehensive onboarding plan combines:
- **Old Commerinity's** geo-hierarchical address system
- **Popkult's** polymorphic pattern and test coverage
- **Enterprise best practices** for data integrity

The result is a robust, scalable, and user-friendly onboarding experience that supports multiple address types, accurate geo-location, and multi-warehouse fulfillment.

---

**Plan Created**: 2025-12-09
**Ready for**: Implementation
**Next Step**: Begin Phase 1 (Database & Models)
