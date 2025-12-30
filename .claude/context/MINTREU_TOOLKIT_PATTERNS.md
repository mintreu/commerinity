# Mintreu Toolkit - Reusable Patterns & Best Practices

**Date**: 2025-12-08
**Purpose**: Document enterprise-grade patterns from old commerinity for reuse in Commerinity Pro

---

## 📦 Package Overview

`mintreu/toolkit` is a foundational Laravel package providing battle-tested traits, helpers, and utilities for the Mintreu ecosystem.

### Key Components

1. **HasUnique** - Unique code generation (UUID, ULID, referral codes)
2. **HasFingerprint** - Secure fingerprinting for records
3. **UniqueCodeHelper** - Static helper for unique code generation
4. **PublishableStatusCast** - Enum for content publishing statuses
5. **HasPackageModelFactory** - Dynamic factory resolution for packages

---

## 🎯 Pattern 1: Unique Code Generation

### User Model Implementation (Old Commerinity)

```php
protected static function booted()
{
    static::creating(function ($user){
        $user->setUniqueCodeUpper('referral_code', 8);
        $user->setUniqueCode('uuid', 16, 'REG'.now()->year);
    });
    parent::booted();
}
```

**Pattern Analysis**:
- ✅ Referral code: 8 chars uppercase (e.g., `ABCD1234`)
- ✅ UUID: 16 chars with year prefix (e.g., `REG2025XYZABC1234`)
- ✅ Uniqueness guaranteed via database check loop
- ✅ Only generates if column is empty

### Applying to Commerinity Pro

Our current implementation in `User::booted()`:
```php
static::creating(function (User $user) {
    // Auto-generate UUID (16 chars with REG prefix + year)
    if (! $user->uuid) {
        $user->uuid = 'REG'.now()->year.Str::upper(Str::random(12));
    }

    // Auto-generate unique referral code (8 chars uppercase)
    if (! $user->referral_code) {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        $user->referral_code = $code;
    }
});
```

**✅ Improvement Needed**: Consider using `HasUnique` trait for consistency once we package toolkit.

---

## 🔐 Pattern 2: Fingerprinting

### Purpose
Secure, deterministic fingerprints for record identification without exposing raw IDs.

### Implementation
```php
use Mintreu\Toolkit\Traits\HasFingerprint;
use Mintreu\Toolkit\Contracts\Fingerprintable;

class User extends Authenticatable implements Fingerprintable
{
    use HasFingerprint;
}
```

### Usage
```php
$user->fingerprint();        // Short (16 chars): 'aB3dE5fG6hI7jK8'
$user->fingerprintFull();    // Full (64 chars): long secure hash
$user->signature();          // Alias for fingerprintFull()
$user->matchesFingerprint($fingerprint); // Verify fingerprint
```

### Algorithm
1. Raw identity: `uuid` → `ulid` → `slug` → `email` → `id_timestamp_appkey`
2. HMAC-SHA256 with app key
3. Base64-url encoded
4. Trimmed to desired length

**Use Cases**:
- Public-facing record links without exposing IDs
- Secure API tokens/signatures
- Record verification in distributed systems

---

## 🧪 Pattern 3: Test-First Development

### Toolkit README Recommendations

From toolkit README section 6:

> **Implement a Robust Test Suite:**
> - Unit Tests for all unique code generation methods
> - Test uniqueness guarantees, correct length, prefix/suffix
> - Integration tests with dummy models
> - Database operations to verify uniqueness constraints

### Our Implementation Plan

```php
// tests/Feature/Models/UserTest.php
test('generates unique UUID on creation with REG prefix and year', function () {
    $user = User::factory()->create();

    expect($user->uuid)
        ->toStartWith('REG'.now()->year)
        ->toHaveLength(16)
        ->toBeUpperCase();
});

test('generates unique referral code on creation', function () {
    $user = User::factory()->create();

    expect($user->referral_code)
        ->toHaveLength(8)
        ->toBeUpperCase()
        ->toMatch('/^[A-Z0-9]{8}$/');
});

test('does not regenerate UUID if already set', function () {
    $uuid = 'REG2025CUSTOM01';
    $user = User::factory()->create(['uuid' => $uuid]);

    expect($user->uuid)->toBe($uuid);
});
```

---

## 🔄 Pattern 4: Affiliate & Originator System

### Two Separate Systems

#### System 1: Affiliate Tree (`parent_id` + `referral_code`)
```php
// Joining via referral code
$parentUser = User::where('referral_code', $referralCode)->first();
$newUser = User::create([
    'parent_id' => $parentUser->id,
    // ... other fields
]);

// Query upline/downline
$upline = $user->ancestors;           // All ancestors
$downline = $user->descendants;       // All descendants
$directChildren = $user->children();  // Direct referrals
```

#### System 2: Agent Recruitment (`originator`)
```php
// Agent recruits a new member
$agent = Agent::find(1);
$newUser = User::create([
    'originator_type' => Agent::class,
    'originator_id' => $agent->id,
    'parent_id' => null, // Team head
    // OR
    'parent_id' => $existingUser->id, // Under existing user
]);

// Query agent's recruits
$agent->originatedUsers; // All users recruited by this agent
```

### Test Scenarios

```php
test('agent can recruit member as new team head', function () {
    $agent = Agent::factory()->create();

    $user = User::factory()->create([
        'originator_type' => Agent::class,
        'originator_id' => $agent->id,
        'parent_id' => null,
    ]);

    expect($user->originator)->toBeInstanceOf(Agent::class);
    expect($user->parent_id)->toBeNull();
    expect($agent->originatedUsers)->toContain($user);
});

test('agent can recruit member under existing user', function () {
    $agent = Agent::factory()->create();
    $teamHead = User::factory()->create();

    $user = User::factory()->create([
        'originator_type' => Agent::class,
        'originator_id' => $agent->id,
        'parent_id' => $teamHead->id,
    ]);

    expect($user->originator)->toBeInstanceOf(Agent::class);
    expect($user->parent->id)->toBe($teamHead->id);
});
```

---

## 📢 Pattern 5: Notifications on Status Change

### Old Commerinity Pattern (Inferred)

User model had:
- `type` (AuthTypeCast): regular → member → promoter → advisor → mentor
- `status` (AuthStatusCast): draft → pending → approved → rejected → banned

### Notification Triggers

```php
// In User model observers or events
protected static function booted()
{
    static::updated(function (User $user) {
        if ($user->isDirty('status')) {
            $user->notifyStatusChange($user->getOriginal('status'), $user->status);
        }

        if ($user->isDirty('type')) {
            $user->notifyTypeChange($user->getOriginal('type'), $user->type);
        }
    });
}
```

### Test Pattern

```php
use Illuminate\Support\Facades\Notification;

test('sends notification when user type changes', function () {
    Notification::fake();

    $user = User::factory()->create(['type' => UserTypeCast::REGULAR]);

    $user->update(['type' => UserTypeCast::MEMBER]);

    Notification::assertSentTo($user, UserTypeChangedNotification::class);
});

test('sends notification when user status changes', function () {
    Notification::fake();

    $user = User::factory()->create(['status' => UserStatusCast::DRAFT]);

    $user->update(['status' => UserStatusCast::APPROVED]);

    Notification::assertSentTo($user, UserStatusChangedNotification::class);
});
```

---

## 🎨 Pattern 6: Enum Best Practices

### From Toolkit README

```php
enum PublishableStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';

    public function getLabel(): string {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Review',
            self::PUBLISHED => 'Published',
            self::REJECTED => 'Rejected',
            self::ARCHIVED => 'Archived',
        };
    }

    public function getColor(): string {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'warning',
            self::PUBLISHED => 'success',
            self::REJECTED => 'danger',
            self::ARCHIVED => 'secondary',
        };
    }

    public function getIcon(): ?string {
        return match($this) {
            self::DRAFT => 'heroicon-o-pencil',
            self::PENDING => 'heroicon-o-clock',
            self::PUBLISHED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::ARCHIVED => 'heroicon-o-archive-box',
        };
    }
}
```

**Our Implementation**: Already applied in `app/Casts/UserTypeCast.php`, `UserStatusCast.php`, `GenderCast.php`.

---

## 🏗️ Architecture Principles

### 1. Package-Ready Structure

From toolkit pattern:
- Group related functionality into traits
- Use contracts/interfaces for type safety
- Make everything testable and reusable
- Namespace properly for package extraction

### 2. Dependency Injection Over Facades

OtpManager pattern (already applied):
```php
public function __construct(
    private readonly CacheContract $cache,
    private readonly Hasher $hasher,
    private readonly bool $isDemoMode = false
) {}
```

**Not**:
```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
```

### 3. Test Coverage Requirements

From toolkit README:
> "For a toolkit designed for reusability and data integrity, comprehensive tests are absolutely critical"

**Standards**:
- ✅ Unit tests for all public methods
- ✅ Integration tests with database
- ✅ Edge case coverage
- ✅ Uniqueness constraint verification
- ✅ Notification testing with fakes

---

## 📝 Action Items for Commerinity Pro

### Immediate (Current Sprint)

1. ✅ Document toolkit patterns (this file)
2. ⏳ Write comprehensive User model tests
   - UUID/referral code generation
   - Parent-child Affiliate relationships
   - Originator scenarios (Agent recruitment)
   - Type/status change notifications
3. ⏳ Test recursive relationship queries

### Future (Next Sprint)

1. Extract toolkit patterns into `commerinity-pro/toolkit` package
2. Implement `HasFingerprint` trait
3. Create notification system for status/type changes
4. Build Observer pattern for User model events

---

**Last Updated**: 2025-12-08 15:45 PM
**Status**: Active - Used as reference for test development
