# Sanctum /user Endpoint Override Strategy
## Efficient Data Loading (From Old Commerinity)

---

## 🎯 **The Problem**

**Default Sanctum** `/api/user` endpoint:
```php
Route::get('/user', fn(Request $request) => $request->user());
```

Returns ONLY basic user data. Frontend needs MORE:
- Profile info (avatar, referral code, level, etc.)
- Verification status (email, mobile)
- Related data (addresses, wallet, subscriptions)

**Result**: Multiple API calls on every page load! ❌

---

## ✅ **Old Commerinity Solution - SMART!**

### **Override the /user Endpoint**

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Override default /user endpoint
    Route::get('/user', [SanctumUserController::class, 'getUser']);

    // Additional user endpoint for full profile
    Route::get('/user/profile', [SanctumUserController::class, 'getProfile']);
});
```

### **SanctumUserController.php**

```php
namespace App\Http\Controllers\Api\Auth;

class SanctumUserController extends Controller
{
    /**
     * Get authenticated user with essential data
     * Called by @qirolab/nuxt-sanctum-authentication automatically
     */
    public function getUser(Request $request): UserIndexResource
    {
        // Return via Resource for consistent formatting
        return UserIndexResource::make($request->user());
    }

    /**
     * Get full user profile with relationships
     * Called manually when needed
     */
    public function getProfile(Request $request): UserResource
    {
        $user = $request->user();

        // Eager load related data
        $user->load([
            'addresses' => fn($q) => $q->where('type', 'home'),
            'kyc',
            'kyc.media',
            'wallet',
            'currentLevel',
            'currentSubscription',
        ]);

        return UserResource::make($user);
    }
}
```

### **UserIndexResource.php** (Lean, for every page)

```php
class UserIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Identity
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,

            // Verification status
            'email_verified' => !is_null($this->email_verified_at),
            'mobile_verified' => !is_null($this->mobile_verified_at),

            // Affiliate
            'referral_code' => $this->referral_code,
            'parent_id' => $this->parent_id,
            'hasParent' => !is_null($this->parent_id),

            // Profile
            'gender' => $this->gender,
            'dob' => $this->dob,
            'avatar' => $this->getFirstMediaUrl('avatarImage'),

            // Status
            'type' => $this->type?->getLabel(), // regular, premium
            'status' => $this->status?->getLabel(), // draft, active, suspended
            'status_feedback' => $this->status_feedback,

            // Membership
            'hasLevel' => !is_null($this->level_id),
            'level_id' => $this->level_id,
            'onboarded' => $this->onboarded,
        ];
    }
}
```

---

## 💡 **Benefits**

### **1. Single API Call on Login**
```typescript
// Frontend: After login, @qirolab/nuxt-sanctum-authentication calls:
// GET /api/user

// Returns complete user object with:
const { user } = useSanctum()

// Available immediately:
user.value.referral_code  // For sharing
user.value.avatar         // For navbar
user.value.level_id       // For membership check
user.value.hasParent      // For Affiliate logic
user.value.email_verified // For verification prompt
```

### **2. Avoid Duplicate Calls**
```typescript
// ❌ Without override (multiple calls):
const user = await $fetch('/api/user')              // Basic
const profile = await $fetch('/api/user/profile')   // Full
const wallet = await $fetch('/api/wallet')          // Wallet
const level = await $fetch('/api/user/level')       // Level

// ✅ With override (one call):
const { user } = useSanctum()
// Everything essential is already loaded!
```

### **3. Conditional Full Load**
```typescript
// For dashboard page (needs more data):
const fullProfile = await useSanctumFetch('/api/user/profile')

// Returns:
// - All from UserIndexResource
// - + addresses
// - + kyc documents
// - + wallet balance
// - + current subscription
```

---

## 🏗️ **Implementation Plan**

### **For Our New Project**:

```php
// app/Http/Controllers/Api/Auth/UserController.php

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Load frequently needed relationships
        $user->loadMissing([
            'currentLevel',
            'currentSubscription' => fn($q) => $q->with('stage', 'level'),
        ]);

        return new UserIndexResource($user);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        // Load everything for profile page
        $user->load([
            'addresses',
            'kyc',
            'wallet',
            'currentLevel',
            'currentSubscription.stage',
            'currentSubscription.level',
            'parent:id,name,referral_code',
        ]);

        return new UserResource($user);
    }
}
```

### **Routes**:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/user/profile', [UserController::class, 'profile']);
});
```

### **Frontend Usage**:
```typescript
// nuxt.config.ts
laravelSanctum: {
  sanctumEndpoints: {
    user: '/api/user', // Override default /api/user
  }
}

// After login, automatically called:
const { user } = useSanctum()

// User object has everything from UserIndexResource
console.log(user.value.referral_code)
console.log(user.value.avatar)
console.log(user.value.level_id)
```

---

## ✅ **Summary**

**Old Commerinity Strategy** ⭐⭐⭐:
1. Override `/api/user` with custom controller
2. Return via Resource (UserIndexResource)
3. Include essential data (verification, Affiliate, membership)
4. Separate `/api/user/profile` for full data
5. Frontend gets everything in ONE call

**Benefits**:
- ✅ Reduces API calls (1 instead of 5+)
- ✅ Faster page loads
- ✅ Consistent data structure
- ✅ Easy to extend (add more fields to Resource)

**This is the way!** 🚀

---

**Implementation**: Copy this pattern to our new project
