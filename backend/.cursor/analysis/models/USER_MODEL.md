# User Model - End-to-End Analysis

## Model Overview
**File**: `backend/app/Models/User.php`
**Purpose**: Core user authentication and profile management model

## Key Attributes

### Fillable Fields
- `uuid` - Unique identifier (auto-generated)
- `name` - User's full name
- `email` - Email address (nullable, unique)
- `mobile` - Mobile number (nullable, unique)
- `password` - Hashed password
- `referral_code` - Auto-generated 8-character uppercase code
- `parent_id` - MLM parent user reference
- `type` - Authentication type (email/mobile) - Cast: `AuthTypeCast`
- `status` - User status - Cast: `AuthStatusCast`
- `status_feedback` - Status change reason
- `bio` - User biography
- `gender` - Gender - Cast: `GenderCast`
- `dob` - Date of birth
- `email_verified_at` - Email verification timestamp
- `mobile_verified_at` - Mobile verification timestamp
- `onboarded` - Boolean flag for onboarding completion
- `level_id` - Current lifecycle level reference

### Hidden Fields
- `password`
- `remember_token`

### Appended Attributes
- `avatar` - Computed from media library

## Relationships

### Direct Relationships
1. **`level()`** - `BelongsTo` - Current lifecycle level
2. **`memberships()`** - `HasMany` - All user subscriptions
3. **`membership()`** - `HasOne` - Active subscription (where expire_at >= now)
4. **`originator()`** - `MorphTo` - Who originated this user
5. **`originatedUsers()`** - `MorphMany` - Users originated by this user

### Trait-Based Relationships (from packages)
- **Addresses** - `HasAddress` trait (from `laravel-geokit`)
- **Cart Items** - `HasCartOwner` trait (from `laravel-commerinity`)
- **KYC** - `HasKyc` trait
- **Orders** - `HasOrder` trait
- **Product Engagements** - `HasProductEngagement` trait
- **Product Wishlists** - `HasProductWishlist` trait
- **Support Tickets** - `HasSupportTicket` trait (from `laravel-helpdesk`)
- **Wallet** - `HasWallet` trait (from `laravel-transaction`)
- **Beneficiaries** - `HasBeneficiary` trait (from `laravel-transaction`)
- **Job Applications** - `HasJobApplications` trait (from `laravel-recruitment`)
- **Push Subscriptions** - `HasPushSubscriptions` (from `notification-channels/webpush`)
- **Media** - `InteractsWithMedia` (from `spatie/laravel-medialibrary`)
- **Recursive Relationships** - `HasRecursiveRelationships` (for MLM tree)

## Business Logic

### Auto-Generated Fields
```php
protected static function booted()
{
    static::creating(function ($user){
        $user->setUniqueCodeUpper('referral_code',8);
        $user->setUniqueCode('uuid',16,'REG'.now()->year);
    });
}
```

### Avatar Handling
- Uses Spatie Media Library
- Collection: `avatarImage`
- Fallback: Random avatar from `pravatar.cc`
- Access via `getAvatarAttribute()` accessor

### Panel Access
- Implements `FilamentUser` interface
- `canAccessPanel()` always returns `true` - **SECURITY ISSUE**

## Issues Found

### 🔴 Critical Issues

1. **Security: Panel Access Always Allowed**
   ```php
   public function canAccessPanel(Panel $panel): bool
   {
       return true; // ⚠️ SECURITY RISK
   }
   ```
   **Fix**: Should check user role/permissions

2. **Missing Validation**
   - No validation for `parent_id` (should verify parent exists)
   - No validation for `referral_code` uniqueness in boot method
   - No validation for `level_id` existence

### 🟡 High Priority Issues

3. **Inconsistent UUID Generation**
   - Uses year prefix: `REG2025...`
   - May cause issues if year changes
   - Should use consistent format

4. **Missing Relationships**
   - No direct relationship to `children` (downline)
   - No relationship to `transactions` (through wallet)
   - No relationship to `notifications`

5. **Avatar Fallback**
   - Uses random avatar URL which may break
   - Should use local placeholder

### 🔵 Medium Priority Issues

6. **Missing Scopes**
   - No scope for active users
   - No scope for verified users
   - No scope for onboarded users

7. **Missing Methods**
   - No method to check if user has active subscription
   - No method to get full name
   - No method to check referral eligibility

## Frontend Integration

### API Endpoints Used
- `GET /user` - Get authenticated user
- `PUT /account/profile` - Update profile
- `POST /user/avatar` - Upload avatar
- `GET /account/stats` - User statistics
- `GET /account/tree` - MLM tree structure

### Frontend Components
- `frontend/pages/dashboard/auth/profile.vue` - Profile management
- `frontend/pages/dashboard/account/index.vue` - Account settings
- `frontend/components/account/AvatarUploader.vue` - Avatar upload

## Data Flow

### Registration Flow
1. User submits registration form
2. `AuthController::register()` validates and creates user
3. `booted()` method generates `uuid` and `referral_code`
4. OTP verification (if required)
5. User created with `status` = pending/active

### Profile Update Flow
1. Frontend calls `PUT /account/profile`
2. `UserAccountController::updateProfile()` validates
3. User model updated
4. Response includes updated user data

### Avatar Upload Flow
1. Frontend uploads image via `POST /user/avatar`
2. `UserAccountController::updateAvatar()` handles upload
3. Spatie Media Library stores file
4. `avatar` accessor returns URL

## Recommendations

1. **Security**
   - Implement proper `canAccessPanel()` logic
   - Add role-based access control
   - Validate parent_id on creation

2. **Data Integrity**
   - Add database constraints for foreign keys
   - Add unique constraint on referral_code
   - Add index on parent_id for MLM queries

3. **Performance**
   - Add eager loading for common relationships
   - Cache avatar URLs
   - Optimize MLM tree queries

4. **Functionality**
   - Add scopes for common queries
   - Add helper methods for business logic
   - Implement referral validation

