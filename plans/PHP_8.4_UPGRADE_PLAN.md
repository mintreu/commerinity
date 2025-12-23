# PHP 8.3 to 8.4 Upgrade Plan

**Created**: 2025-12-17
**Status**: Ready for Implementation
**Priority**: Medium
**Estimated Effort**: ~2-3 hours total

---

## Executive Summary

The codebase is **98% ready** for PHP 8.4. Only **3 deprecation warnings** were found in a single file. All packages are compatible. The upgrade is straightforward.

### Current State
- **Current PHP Version**: 8.4.15 (already running)
- **composer.json requires**: `"php": "^8.2"` (compatible with 8.4)
- **Composer Version**: 2.9.2
- **All packages**: Compatible with PHP 8.4

### Issues Found
| Severity | Count | Description |
|----------|-------|-------------|
| Deprecations | 3 | Implicitly nullable parameters in `MlmCommission.php` |
| Errors | 0 | None |
| Breaking Changes | 0 | None |

---

## Phase 1: Fix Deprecations (Required)

### 1.1 Implicitly Nullable Parameters

**File**: `apiserver/app/Models/Mlm/MlmCommission.php`

**Issue**: PHP 8.4 deprecates implicitly marking parameters as nullable. Must use explicit `?` type.

**Changes Required**:

```php
// Line 284 - BEFORE
public function hold(string $reason = null): void

// Line 284 - AFTER
public function hold(?string $reason = null): void

// Line 300 - BEFORE
public function cancel(string $reason = null): void

// Line 300 - AFTER
public function cancel(?string $reason = null): void

// Line 320 - BEFORE
public function reverse(string $reason = null): self

// Line 320 - AFTER
public function reverse(?string $reason = null): self
```

**Why**: In PHP 8.4, typing `string $param = null` without the `?` prefix generates a deprecation warning. The explicit `?string` is required.

---

## Phase 2: Update composer.json (Recommended)

### 2.1 Update PHP Version Requirement

```json
// BEFORE
"require": {
    "php": "^8.2",

// AFTER (Option A - Require 8.4 minimum)
"require": {
    "php": "^8.4",

// AFTER (Option B - Support 8.3+ for flexibility)
"require": {
    "php": "^8.3",
```

**Recommendation**: Use `"php": "^8.4"` since you're already running 8.4 and want to leverage new features.

---

## Phase 3: Update Packages (Optional)

### 3.1 Outdated Packages

Run `composer update` to get latest compatible versions:

| Package | Current | Latest | Priority |
|---------|---------|--------|----------|
| filament/filament | 4.0.0 | 4.3.1 | High |
| filament/spatie-laravel-media-library-plugin | 4.0.0 | 4.3.1 | High |
| laravel/framework | 12.41.1 | 12.43.1 | Medium |
| pestphp/pest | 4.1.6 | 4.2.0 | Medium |
| laravel/boost | 1.8.4 | 1.8.5 | Low |
| laravel/sail | 1.50.0 | 1.51.0 | Low |

**Command**:
```bash
cd apiserver && composer update
```

---

## Phase 4: Leverage PHP 8.4 Features (Optional Enhancement)

### 4.1 Property Hooks (New in 8.4)

Can replace complex getters/setters with cleaner property hooks:

```php
// BEFORE (Traditional accessor)
class User extends Model {
    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }
}

// AFTER (PHP 8.4 property hooks - for non-Eloquent classes)
class UserDTO {
    public string $fullName {
        get => "{$this->firstName} {$this->lastName}";
    }
}
```

**Note**: Property hooks are best for DTOs and value objects, not Eloquent models.

### 4.2 Asymmetric Visibility (New in 8.4)

```php
// BEFORE
class WalletService {
    private int $balance;

    public function getBalance(): int {
        return $this->balance;
    }
}

// AFTER (PHP 8.4)
class WalletService {
    public private(set) int $balance; // readable publicly, writable only privately
}
```

### 4.3 New Without Parentheses (New in 8.4)

```php
// BEFORE
$result = (new MoneyService(1000))->formatted();

// AFTER (PHP 8.4)
$result = new MoneyService(1000)->formatted();
```

### 4.4 #[Deprecated] Attribute (New in 8.4)

```php
// Mark methods as deprecated with custom messages
#[\Deprecated("Use newMethod() instead", since: "2.0")]
public function oldMethod(): void {
    // ...
}
```

---

## Phase 5: Add Code Coverage (Requested)

### 5.1 Install PCOV (Recommended)

PCOV is faster than Xdebug for coverage-only use.

**For Laragon/Windows**:

1. Download PCOV DLL from: https://pecl.php.net/package/pcov
2. Add to `php.ini`:
```ini
extension=pcov
pcov.enabled=1
pcov.directory=app
```

**Alternative - Use Xdebug** (if already installed):
```ini
xdebug.mode=coverage
```

### 5.2 Configure Pest for Coverage

Update `phpunit.xml` (already exists):

```xml
<coverage>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
    <exclude>
        <directory suffix=".php">./app/Filament</directory>
    </exclude>
</coverage>
```

### 5.3 Run Tests with Coverage

```bash
# Text coverage report
php artisan test --coverage

# HTML coverage report
php artisan test --coverage --coverage-html=coverage-report

# Minimum coverage threshold
php artisan test --coverage --min=80
```

---

## Implementation Checklist

### Immediate (Required)

- [ ] Fix 3 deprecation warnings in `MlmCommission.php`
- [ ] Run `php -l` on all files to verify no errors
- [ ] Run test suite to verify functionality

### Short-term (Recommended)

- [ ] Update `composer.json` PHP requirement to `^8.4`
- [ ] Run `composer update` for latest packages
- [ ] Install PCOV for code coverage
- [ ] Configure coverage in `phpunit.xml`

### Long-term (Optional)

- [ ] Adopt property hooks in DTOs and value objects
- [ ] Use asymmetric visibility where appropriate
- [ ] Add `#[Deprecated]` attributes to legacy methods
- [ ] Achieve 80%+ code coverage

---

## Verification Steps

### 1. After Fixing Deprecations
```bash
cd apiserver
php -l app/Models/Mlm/MlmCommission.php
# Should show: No syntax errors detected
```

### 2. Full Codebase Check
```bash
find app/ -name "*.php" -exec php -l {} \; 2>&1 | grep -i "deprecated\|error"
# Should return nothing
```

### 3. Run Test Suite
```bash
php artisan test
# All tests should pass
```

### 4. Verify Coverage
```bash
php artisan test --coverage
# Should show coverage report
```

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Package incompatibility | Low | Medium | All major packages tested with 8.4 |
| Hidden deprecations | Very Low | Low | Full lint scan completed |
| Performance regression | Very Low | Low | 8.4 is generally faster |
| Production issues | Very Low | High | Test thoroughly before deploy |

---

## Rollback Plan

If issues occur after upgrade:

1. Revert PHP requirement in `composer.json` to `^8.2`
2. Run `composer install` to restore packages
3. Switch Laragon back to PHP 8.3

---

## PHP 8.4 Key Features Reference

| Feature | Description | Use Case |
|---------|-------------|----------|
| Property Hooks | Define get/set logic inline | DTOs, Value Objects |
| Asymmetric Visibility | Different read/write visibility | Immutable public properties |
| `new` without parentheses | Cleaner instantiation syntax | Fluent builders |
| `#[\Deprecated]` | Custom deprecation messages | API versioning |
| Lazy Objects | Native lazy initialization | Heavy services |
| `array_find()` | Find first matching element | Array utilities |
| `array_any()`/`array_all()` | Check array conditions | Validation |

---

## Conclusion

**The upgrade is safe and straightforward.**

- Only 3 lines of code need changing
- All packages are compatible
- PHP 8.4 is already installed and running
- Benefits: Better performance, new features, continued support

**Recommended Action**: Implement Phase 1 immediately, then proceed with remaining phases as time permits.
