# Active Issues & Bugs

This file tracks active issues, bugs, and technical debt in the project. Always check this file before starting work.

## 🚨 Critical - Blocking Development

### 1. Database Schema Conflict: Filters
- **Issue**: `filters` table has `filter_group_id` (1-to-M), but Models (`Filter`, `FilterGroup`) and Pivot Table (`filter_group_mappings`) implement M-to-M.
- **Impact**: `FilterSeeder` fails because `filter_group_id` is required but Models try to use Pivot.
- **Fix**:
  - Remove `filter_group_id` column from `filters` table (or make nullable if transition needed).
  - Ensure `filter_group_mappings` table exists.
  - Ensure Models align with M-to-M (`BelongsToMany`).
  - **Status**: Codebase uses M-to-M, Migration `2025_12_27_120002` inconsistent with actual DB state in some environments.

### 2. Product Price Visibility (0.00)
- **Issue**: Product price shows as `0.00` on frontend.
- **Cause**: `ProductSeeder` does not set `price` on `Product` model. `ProductStock` seeded with `price: null` inherits `Product` price (which is 0).
- **Fix**: Update `ProductSeeder` to set `price` on `Product` model during creation.
- **Status**: Detected, fix required in `ProductSeeder.php`.

### 3. Redundant SMS Provider Model
- **Issue**: `SHSProvider` model/table is redundant.
- **Solution**: Use `Integration` model with `type: 'sms'`.
- **Action**: Migrate data if any, then remove `SHSProvider`.

## ⚠️ High Priority - Logic & Testing

### 1. Beneficiary Account Verification Loop
- **Issue**: `BeneficiaryAccountObserver` can cause infinite loop during `syncToCashfree` if logic isn't guarded.
- **Fix**: Ensure `updated` observer only triggers sync if relevant fields change.
- **Status**: Partially fixed, requires thorough testing.

### 2. Missing End-to-End Tests
- **Issue**: Critical flows (Order -> Shipping, Payouts) lack E2E tests.
- **Action**: Implement Puppeteer tests for full checkout flow.

## ℹ️ Missing Features / Improvements

### 1. Data Seeding
- **Missing**: Sales, Discounts, Vouchers, tiered sales for Member levels.
- **Action**: Create comprehensive seeders for these entities.

### 2. Coupon/Voucher Testing
- **Issue**: Cart voucher application not fully tested.

## 🧪 Broken Tests (Tracked)
- `BeneficiaryAccountTest.php` - Recent crashes (Stack Overflow). Fixed but verified? needed.
