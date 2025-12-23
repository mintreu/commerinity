# Critical Issues & Technical Debt - Old Commerinity

## CRITICAL (Fix Immediately)

### 1. Money Precision Bug
**Location**: `mintreu/laravel-money` package
**File**: `LaravelMoneyCast::set()`

**Issue**:
```php
// Current code (BROKEN)
public function set($model, string $key, $value, array $attributes)
{
    // Conversion commented out!
    // return $value * 100; // Convert to cents
    return $value; // Stores as float
}
```

**Impact**:
- Monetary values stored as floats
- Rounding errors in calculations
- Financial discrepancies in:
  - Order totals
  - Commission calculations
  - Wallet balances
  - Refunds

**Example of Problem**:
```php
// Order total: $10.99
// Stored as: 10.99 (float)
// After calculations: 10.989999999 (float precision error)
// Displayed as: $10.98 or $11.00 (depending on rounding)

// Correct approach:
// Stored as: 1099 (integer, cents)
// Calculations: Always integers, no precision loss
// Display: 1099 / 100 = $10.99
```

**Solution**:
1. Uncomment conversion in `LaravelMoneyCast::set()`
2. Create migration to convert existing float values to integers
3. Test all financial calculations
4. Add validation to ensure money fields always use cast

**Priority**: CRITICAL - Fix before any financial operations

---

### 2. Lack of Automated Tests
**Location**: All packages and main application

**Issue**:
- Most packages have zero tests
- Main application has minimal test coverage
- No integration tests for critical flows
- No API tests

**Impact**:
- High risk of regressions
- Difficult to refactor safely
- No confidence in code changes
- Cannot safely upgrade dependencies

**Affected Areas**:
- All 11 custom packages
- Payment gateway integrations
- Commission calculation logic
- Cart operations
- Order processing
- Wallet operations

**Solution**:
1. **Immediate**:
   - Add tests for critical financial operations
   - Test money handling
   - Test commission calculations
   - Test payment gateway integrations

2. **Short-term**:
   - Minimum 80% code coverage for packages
   - Feature tests for all API endpoints
   - Browser tests for critical user flows (Pest v4)

3. **Long-term**:
   - CI/CD pipeline with automated testing
   - Test coverage reports
   - Mandatory tests for new features

**Priority**: CRITICAL - Required for safe refactoring

---

### 3. Commission Recalculation on Returns
**Location**: Order processing, Incentive calculation

**Issue**:
- No mechanism to reverse commissions on order returns/refunds
- Commissions paid immediately on order completion
- No tracking of commission source orders

**Impact**:
- Overpaid commissions on returned orders
- Financial loss
- Complex manual reconciliation required

**Example**:
```
1. User B buys product for $100
2. User A (referrer) gets $10 commission
3. Commission paid to wallet
4. User B returns product
5. User B refunded $100
6. User A still has $10 commission (WRONG!)
```

**Solution**:
1. Add `order_id` reference to incentive records (already exists)
2. Add `reversed_at` timestamp to incentives
3. On order return/refund:
   - Find related incentives
   - Create negative incentive records
   - Deduct from wallet balances
   - Mark original incentives as reversed
4. Prevent withdrawal if balance would go negative

**Priority**: CRITICAL - Financial integrity issue

---

## HIGH Priority

### 4. Missing API Documentation
**Location**: Entire API

**Issue**:
- No OpenAPI/Swagger documentation
- API endpoints undocumented
- Request/response formats unclear
- No versioning strategy

**Impact**:
- Difficult for frontend developers
- Hard to integrate third-party services
- No clear API contract
- Breaking changes unnoticed

**Solution**:
1. Generate OpenAPI specification (Swagger/Scramble)
2. Document all endpoints with:
   - Request parameters
   - Request body schema
   - Response schema
   - Authentication requirements
   - Error responses
3. Implement API versioning (`/api/v1/`)
4. Add changelog for API changes

**Priority**: HIGH - Required for maintainability

---

### 5. Package Documentation Gaps
**Location**: All custom packages

**Issue**:
- READMEs are templates with no content
- No usage examples
- No API documentation
- Installation instructions missing

**Impact**:
- Difficult for new developers
- Unclear package dependencies
- Time wasted figuring out usage
- Knowledge loss when developers leave

**Solution**:
1. Complete README for each package with:
   - Purpose and features
   - Installation instructions
   - Usage examples
   - Configuration options
   - Trait documentation
   - API reference
2. Inter-package dependency diagram
3. Package versioning strategy

**Priority**: HIGH - Required for maintainability

---

### 6. No Stock Management
**Location**: Product catalog

**Issue**:
- No inventory tracking
- No stock quantity field
- No reservation on cart add
- No stock validation on checkout

**Impact**:
- Overselling products
- Order fulfillment issues
- Customer dissatisfaction
- Manual inventory reconciliation

**Solution**:
1. Add `stock_quantity` field to products table
2. Add `stock_status` enum (in_stock, out_of_stock, backorder)
3. Implement stock operations:
   - Reserve on cart add (with timeout)
   - Release on cart remove/timeout
   - Deduct on order placement
   - Restore on order cancellation
4. Add low stock alerts
5. Admin inventory management

**Priority**: HIGH - Required for production e-commerce

---

## MEDIUM Priority

### 7. User Model Complexity
**Location**: `app/Models/User.php`

**Issue**:
- User model has 15+ traits
- Violates Single Responsibility Principle
- Difficult to test
- Hard to maintain

**Traits on User Model**:
```php
use HasApiTokens, HasPushSubscriptions, Notifiable;
use InteractsWithMedia;
use HasRecursiveRelationships;
use HasAddress, HasCartOwner, HasKyc, HasOrder;
use HasLifecycle, HasFingerprint;
use HasJobApplications, HasSupportTicket;
use HasWallet, HasBeneficiary;
use HasVoucherAccess, HasProductEngagement, HasProductWishlist;
```

**Impact**:
- Model is a "god object"
- Difficult to understand what User "is" vs "has"
- Testing requires many factories/mocks
- Changes risky due to many dependencies

**Solution**:
1. Keep core traits (authentication, authorization)
2. Extract heavy logic to service classes
3. Use separate models for aggregates (UserProfile, UserWallet, etc.)
4. Consider event sourcing for lifecycle
5. Document remaining traits with clear purposes

**Priority**: MEDIUM - Technical debt, affects maintainability

---

### 8. Missing Rate Limiting
**Location**: All API routes

**Issue**:
- No throttling on API endpoints
- No protection against brute force
- No DDoS protection

**Impact**:
- Vulnerable to abuse
- Credential stuffing attacks
- API resource exhaustion
- High server costs

**Solution**:
1. Add Laravel rate limiting middleware
2. Different limits for:
   - Authenticated users (higher)
   - Guest users (lower)
   - Sensitive endpoints (login, register, OTP)
3. Implement exponential backoff
4. Add CAPTCHA for repeated failures

**Routes Needing Limits**:
- `/api/login` - 5 attempts per minute
- `/api/register` - 3 attempts per minute
- `/api/forgot-password` - 3 attempts per hour
- `/api/verify-mobile` - 5 attempts per hour
- All other API routes - 60 requests per minute

**Priority**: MEDIUM - Security concern

---

### 9. Concurrent Operation Issues
**Location**: Wallet operations, Cart updates

**Issue**:
- No database locking for wallet operations
- Race conditions possible in:
  - P2P transfers
  - Concurrent cart updates
  - Stock deduction
  - Commission payouts

**Example Race Condition**:
```
User A wallet: $100

Request 1 (P2P send $60):
1. Read balance: $100
2. Check sufficient funds: YES
3. Deduct $60
4. Update balance: $40

Request 2 (Withdrawal $60) [concurrent]:
1. Read balance: $100 (stale read)
2. Check sufficient funds: YES
3. Deduct $60
4. Update balance: $40

Result: Both succeed, balance $40, but should be negative
```

**Impact**:
- Balance inconsistencies
- Double-spending
- Financial loss
- Data integrity issues

**Solution**:
1. Use database transactions with row locking
2. Implement pessimistic locking for wallet operations:
   ```php
   DB::transaction(function() {
       $wallet = Wallet::where('id', $id)->lockForUpdate()->first();
       // Perform operations
   });
   ```
3. Add version columns for optimistic locking
4. Implement idempotency keys for critical operations

**Priority**: MEDIUM - Financial integrity

---

### 10. Missing Product Engagement parent_id
**Location**: `app/Models/ProductEngagement.php`

**Issue**:
- Model has nested reply functionality
- Database migration has `parent_id` column
- Model doesn't define relationship

**Impact**:
- Nested reviews/replies don't work
- Cannot display threaded comments
- Poor user experience

**Solution**:
1. Add relationship to model:
   ```php
   public function parent() {
       return $this->belongsTo(ProductEngagement::class, 'parent_id');
   }

   public function replies() {
       return $this->hasMany(ProductEngagement::class, 'parent_id');
   }
   ```
2. Update API to support nested replies
3. Update frontend to display threads

**Priority**: MEDIUM - Feature incomplete

---

## LOW Priority (Technical Debt)

### 11. No Soft Deletes
**Issue**: Soft deletes not consistently used
**Impact**: Data loss on deletion, difficult auditing
**Solution**: Implement soft deletes on key models

### 12. Inconsistent Error Handling
**Issue**: Generic try-catch blocks, missing error logging
**Impact**: Difficult debugging, poor error messages
**Solution**: Standardize exception handling, add detailed logging

### 13. No Caching Strategy
**Issue**: No Redis, database cache only
**Impact**: Slow performance, high database load
**Solution**: Implement Redis caching for categories, products, config

### 14. Missing Indexes
**Issue**: No database indexes on foreign keys and query columns
**Impact**: Slow queries as data grows
**Solution**: Add indexes on frequently queried columns

### 15. No API Pagination Standardization
**Issue**: Inconsistent pagination across endpoints
**Impact**: Confusing API responses
**Solution**: Standardize pagination format

### 16. Hardcoded Configuration
**Issue**: Some configs hardcoded in code instead of .env
**Impact**: Difficult to change per environment
**Solution**: Move to config files

### 17. No Logging Strategy
**Issue**: Minimal application logging
**Impact**: Difficult debugging production issues
**Solution**: Implement structured logging (daily, error channels)

### 18. Missing Queue Configuration
**Issue**: Sync queue driver (no actual queuing)
**Impact**: Slow API responses for heavy operations
**Solution**: Configure database/Redis queue, add queue workers

### 19. No Event Sourcing
**Issue**: No audit trail for critical operations
**Impact**: Cannot trace financial changes
**Solution**: Implement event sourcing for orders, transactions, incentives

### 20. Frontend Build Optimization
**Issue**: Large bundle sizes, no code splitting
**Impact**: Slow page loads
**Solution**: Implement lazy loading, code splitting, tree shaking

---

## Refactoring Priority Matrix

### CRITICAL (Week 1)
1. Fix money precision bug ✓ MUST FIX FIRST
2. Add commission reversal logic
3. Add basic test coverage (financial operations)

### HIGH (Week 2-3)
4. Implement stock management
5. Add API documentation
6. Complete package documentation
7. Add rate limiting

### MEDIUM (Week 4-6)
8. Refactor User model
9. Add database locking
10. Fix product engagement
11. Implement soft deletes
12. Add caching layer

### LOW (Ongoing)
13. Improve error handling
14. Add database indexes
15. Implement event sourcing
16. Optimize frontend
17. Add comprehensive logging
18. Configure queue workers

---

## Testing Strategy for Refactoring

1. **Before ANY Changes**:
   - Write tests for existing behavior
   - Document current bugs
   - Create baseline metrics

2. **During Refactoring**:
   - Test-driven development
   - One feature at a time
   - Deploy behind feature flags

3. **After Changes**:
   - Run full test suite
   - Performance testing
   - Security audit

4. **Continuous**:
   - Code reviews
   - Static analysis
   - Dependency updates
