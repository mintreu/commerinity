# Laravel Dev Tools Analysis & Recommendations

**Created**: 2025-12-17
**Purpose**: Comprehensive dev tools setup for code quality, coverage, security, and optimization

---

## Current Tools Inventory

### What You Already Have

| Tool | Version | Category | Status |
|------|---------|----------|--------|
| **Laravel Pint** | 1.26.0 | Code Formatting | ✅ Installed |
| **Pest** | 4.1.6 | Testing Framework | ✅ Installed |
| **Pest Laravel Plugin** | 4.0.0 | Laravel Testing | ✅ Installed |
| **Mockery** | 1.6.12 | Mocking | ✅ Installed |
| **Faker** | 1.24.1 | Test Data | ✅ Installed |
| **Collision** | 8.8.3 | Error Handling | ✅ Installed |
| **Laravel Pail** | 1.2.4 | Log Tailing | ✅ Installed |
| **Laravel Boost** | 1.8.4 | AI Dev Assistant | ✅ Installed |
| **Laravel Tinker** | 2.10.2 | REPL | ✅ Installed |
| **Laravel Sail** | 1.50.0 | Docker | ✅ Installed |

### What's Configured

| Feature | Status | Notes |
|---------|--------|-------|
| Code Coverage Config | ✅ Ready | phpunit.xml configured for PCOV |
| Coverage HTML Report | ✅ Ready | Outputs to `coverage-html/` |
| Coverage XML | ✅ Ready | For CI integration |
| Test Suites | ✅ Ready | Unit & Feature suites |

### What's Missing

| Tool | Category | Priority |
|------|----------|----------|
| PCOV/Xdebug | Code Coverage | **HIGH** |
| PHPStan | Static Analysis | **HIGH** |
| Laravel IDE Helper | IDE Support | **HIGH** |
| Enlightn | Security Scanner | **HIGH** |
| Rector | Auto Refactoring | Medium |
| PHP Insights | Code Quality | Medium |
| Larastan | Laravel Static Analysis | Medium |
| Pest Arch Plugin | Architecture Testing | Medium |

---

## Recommended Tools Installation

### TIER 1: Essential (Install Immediately)

#### 1. PCOV - Code Coverage Driver
**Why**: Fast coverage reports, already configured in phpunit.xml

```bash
# Windows (Laragon)
# Download from: https://windows.php.net/downloads/pecl/releases/pcov/

# Add to php.ini
extension=pcov
pcov.enabled=1
```

**Alternative - Use Xdebug** (slower but multi-purpose):
```bash
# Already may be available, just enable coverage mode
# In php.ini:
xdebug.mode=coverage
```

**Usage**:
```bash
php artisan test --coverage
php artisan test --coverage --min=80
```

---

#### 2. PHPStan + Larastan - Static Analysis
**Why**: Catches bugs before runtime, type safety

```bash
composer require --dev phpstan/phpstan larastan/larastan
```

**Create `phpstan.neon`**:
```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
    level: 5
    ignoreErrors:
        - '#Unsafe usage of new static#'
    excludePaths:
        - app/Filament/
    checkMissingIterableValueType: false
```

**Usage**:
```bash
vendor/bin/phpstan analyse
vendor/bin/phpstan analyse --level=6  # Stricter
vendor/bin/phpstan analyse --generate-baseline  # Ignore existing errors
```

**PHPStan Levels**:
| Level | Description |
|-------|-------------|
| 0 | Basic checks |
| 5 | Recommended for Laravel (checks types, dead code) |
| 6 | Strict type checking |
| 8 | Maximum strictness |
| 9 | Bleeding edge (experimental) |

---

#### 3. Laravel IDE Helper - IDE Support
**Why**: Better autocomplete, jump to definition

```bash
composer require --dev barryvdh/laravel-ide-helper
```

**Generate helpers**:
```bash
php artisan ide-helper:generate      # Facades
php artisan ide-helper:models -M     # Models (write to _ide_helper_models.php)
php artisan ide-helper:meta          # PhpStorm .phpstorm.meta.php
```

**Add to `composer.json` scripts**:
```json
"scripts": {
    "post-update-cmd": [
        "@php artisan ide-helper:generate",
        "@php artisan ide-helper:models -M"
    ]
}
```

---

#### 4. Enlightn - Security & Performance Scanner
**Why**: Finds security vulnerabilities, performance issues, best practice violations

```bash
composer require --dev enlightn/enlightn
php artisan vendor:publish --tag=enlightn
```

**Usage**:
```bash
php artisan enlightn                 # Full scan
php artisan enlightn --security      # Security only
php artisan enlightn --performance   # Performance only
php artisan enlightn --analyzer=XSS  # Specific analyzer
```

**What it checks**:
- SQL Injection vulnerabilities
- XSS vulnerabilities
- Mass assignment vulnerabilities
- CSRF protection
- Rate limiting
- Session security
- File permissions
- Debug mode in production
- N+1 queries
- And 120+ more checks

---

### TIER 2: Recommended (Install Soon)

#### 5. Pest Architecture Plugin - Architecture Testing
**Why**: Enforce coding standards automatically

```bash
composer require --dev pestphp/pest-plugin-arch
```

**Example tests** (`tests/Arch/ArchTest.php`):
```php
<?php

arch('controllers have suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('models extend eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('no debugging code')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('strict types everywhere')
    ->expect('App')
    ->toUseStrictTypes();

arch('services are final')
    ->expect('App\Services')
    ->toBeFinal();

arch('DTOs are readonly')
    ->expect('App\Dto')
    ->toBeReadonly();

arch('no env() outside config')
    ->expect('env')
    ->not->toBeUsedIn('App');
```

---

#### 6. PHP Insights - Code Quality Dashboard
**Why**: All-in-one code quality metrics

```bash
composer require --dev nunomaduro/phpinsights
php artisan vendor:publish --provider="NunoMaduro\PhpInsights\Application\Adapters\Laravel\InsightsServiceProvider"
```

**Usage**:
```bash
php artisan insights              # Full analysis
php artisan insights --summary    # Quick summary
php artisan insights --fix        # Auto-fix issues
```

**Metrics provided**:
- Code complexity
- Architecture violations
- Style consistency
- Security issues
- Code duplication

---

#### 7. Rector - Automated Refactoring
**Why**: Auto-upgrade PHP syntax, enforce patterns

```bash
composer require --dev rector/rector
```

**Create `rector.php`**:
```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/app/Filament',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        LaravelSetList::LARAVEL_120,
    ]);
```

**Usage**:
```bash
vendor/bin/rector process --dry-run  # Preview changes
vendor/bin/rector process            # Apply changes
```

---

### TIER 3: Nice to Have

#### 8. Laravel Debugbar - Debug Panel
**Why**: See queries, views, routes, cache in browser

```bash
composer require --dev barryvdh/laravel-debugbar
```

**Automatic**: Shows debug bar in browser when `APP_DEBUG=true`

---

#### 9. Pest Type Coverage Plugin
**Why**: Ensure type coverage

```bash
composer require --dev pestphp/pest-plugin-type-coverage
```

**Usage**:
```bash
php artisan test --type-coverage
php artisan test --type-coverage --min=90
```

---

#### 10. Laravel Pint Extra Rules
**Why**: Stricter code style

Update `pint.json`:
```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "final_class": true,
        "ordered_class_elements": true,
        "single_line_empty_body": true,
        "trailing_comma_in_multiline": {
            "elements": ["arrays", "arguments", "parameters"]
        }
    }
}
```

---

## Installation Script

Create `scripts/setup-dev-tools.sh`:
```bash
#!/bin/bash
echo "Installing Laravel Dev Tools..."

# Static Analysis
composer require --dev phpstan/phpstan larastan/larastan

# IDE Helper
composer require --dev barryvdh/laravel-ide-helper

# Security Scanner
composer require --dev enlightn/enlightn

# Architecture Testing
composer require --dev pestphp/pest-plugin-arch

# Code Quality
composer require --dev nunomaduro/phpinsights

# Auto Refactoring
composer require --dev rector/rector

# Debug Bar
composer require --dev barryvdh/laravel-debugbar

# Type Coverage
composer require --dev pestphp/pest-plugin-type-coverage

echo "Publishing configs..."
php artisan vendor:publish --tag=enlightn
php artisan vendor:publish --provider="NunoMaduro\PhpInsights\Application\Adapters\Laravel\InsightsServiceProvider"

echo "Generating IDE helpers..."
php artisan ide-helper:generate
php artisan ide-helper:models -M
php artisan ide-helper:meta

echo "Done! Dev tools installed."
```

---

## Composer Scripts Update

Add to `composer.json`:
```json
{
    "scripts": {
        "analyse": "vendor/bin/phpstan analyse",
        "insights": "php artisan insights",
        "security": "php artisan enlightn --security",
        "coverage": "php artisan test --coverage",
        "coverage-html": "php artisan test --coverage --coverage-html=coverage-html",
        "quality": [
            "@analyse",
            "@insights",
            "@security"
        ],
        "ci": [
            "vendor/bin/pint --test",
            "@analyse",
            "@php artisan test --parallel"
        ]
    }
}
```

**Usage**:
```bash
composer analyse          # Run PHPStan
composer insights         # Run PHP Insights
composer security         # Run security scan
composer coverage         # Run tests with coverage
composer quality          # Run all quality checks
composer ci               # Full CI pipeline
```

---

## Recommended CI Pipeline

### GitHub Actions Example

`.github/workflows/ci.yml`:
```yaml
name: CI

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, ctype, json, curl, pcov
          coverage: pcov

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Code Style
        run: vendor/bin/pint --test

      - name: Static Analysis
        run: vendor/bin/phpstan analyse

      - name: Security Scan
        run: php artisan enlightn --security --ci

      - name: Tests
        run: php artisan test --parallel --coverage --min=80
```

---

## Quick Reference Commands

### Daily Development
```bash
# Format code
vendor/bin/pint --dirty

# Run tests
php artisan test --filter=MyTest

# Check types
vendor/bin/phpstan analyse app/Services/

# Quick insights
php artisan insights --summary
```

### Before Commit
```bash
vendor/bin/pint
vendor/bin/phpstan analyse
php artisan test
```

### Weekly/Monthly
```bash
# Full security audit
php artisan enlightn

# Full code quality
php artisan insights

# Update dependencies
composer update --dry-run
composer outdated

# Rector upgrades
vendor/bin/rector process --dry-run
```

---

## Tool Comparison Matrix

| Tool | Speed | Depth | Auto-Fix | CI Ready |
|------|-------|-------|----------|----------|
| Pint | Fast | Style | Yes | Yes |
| PHPStan | Medium | Deep | No | Yes |
| Enlightn | Medium | Security | No | Yes |
| PHP Insights | Slow | Comprehensive | Partial | Yes |
| Rector | Slow | Refactoring | Yes | Yes |
| Pest | Fast | Testing | N/A | Yes |

---

## Implementation Priority

### Week 1: Foundation
1. ✅ Install PCOV for coverage
2. ✅ Install PHPStan + Larastan
3. ✅ Install Laravel IDE Helper
4. ✅ Create architecture tests

### Week 2: Security & Quality
5. Install Enlightn
6. Install PHP Insights
7. Run initial security audit
8. Fix critical issues

### Week 3: Automation
9. Setup Rector
10. Add composer scripts
11. Configure CI pipeline
12. Establish baseline metrics

---

## Expected Benefits

| Metric | Before | After (Target) |
|--------|--------|----------------|
| Code Coverage | 0% | 80%+ |
| Type Coverage | Unknown | 90%+ |
| PHPStan Level | N/A | Level 5+ |
| Security Issues | Unknown | 0 Critical |
| Code Style | Manual | Automated |

---

## Notes for Claude (AI Assistant)

These tools help me analyze your codebase better:

1. **PHPStan output** - Shows type issues I should fix
2. **Enlightn report** - Shows security vulnerabilities
3. **Coverage report** - Shows untested code paths
4. **PHP Insights** - Shows code quality metrics
5. **Architecture tests** - Enforces coding standards

When you install these, I can run analyses and help fix issues more effectively.
