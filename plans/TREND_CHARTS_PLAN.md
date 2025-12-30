# Trend Charts & Analytics Plan

## Overview

Using `flowframe/laravel-trend` package to generate comprehensive charts for:
- **Nuxt Frontend**: User-specific and team insights
- **Filament Admin**: Platform-wide analytics + admin-specific insights

All trend data accessed via **TrendService classes** for:
- Reusability across Filament widgets and API endpoints
- Easy testing with Pest
- Consistent data formatting

---

## User Types & Access Levels

| User Type | Access Level |
|-----------|-------------|
| **Regular** | Own wallet, transactions |
| **Member** | + Team basic stats, referral counts |
| **Promoter** | + Affiliate commissions, downline stats, level progress |
| **Advisor** | + Originated users, salary insights, team performance |
| **Mentor** | + Training insights (future) |
| **Admin** | ALL data + platform-wide analytics |

---

## Service Architecture

```
app/Services/Trends/
├── BaseTrendService.php          # Base class with common methods
├── UserTrendService.php          # User registration, activity trends
├── WalletTrendService.php        # Wallet balance, transactions
├── TransactionTrendService.php   # Transaction volume, methods, status
├── CommissionTrendService.php    # Affiliate commission trends
├── TeamTrendService.php          # Downline, referral trends
└── AdminTrendService.php         # Platform-wide aggregates
```

---

## Chart Specifications

### 1. USER TRENDS (UserTrendService)

#### 1.1 User Registrations (Admin Only)
```php
// New user registrations over time
Trend::model(User::class)
    ->between($start, $end)
    ->perDay() // or perMonth, perYear
    ->count();

// By user type
Trend::query(User::where('type', UserTypeCast::MEMBER))
    ->between($start, $end)
    ->perMonth()
    ->count();
```

**Filters:**
- Period: today, week, month, quarter, year, custom
- User Type: all, regular, member, promoter, advisor
- Status: all, active, inactive, suspended

**Charts:**
- Line: Registration trend
- Bar: Registrations by type
- Pie: User type distribution

---

### 2. WALLET TRENDS (WalletTrendService)

#### 2.1 Balance History (User-specific)
```php
// Track balance over time via completed transactions
Trend::query(
    Transaction::where('wallet_id', $walletId)
        ->completed()
)
    ->between($start, $end)
    ->perDay()
    ->average('balance_after');
```

#### 2.2 Credit vs Debit (User & Admin)
```php
// Credits over time
Trend::query(
    Transaction::where('wallet_id', $walletId)
        ->credits()
        ->completed()
)
    ->between($start, $end)
    ->perMonth()
    ->sum('amount');

// Debits over time
Trend::query(
    Transaction::where('wallet_id', $walletId)
        ->debits()
        ->completed()
)
    ->between($start, $end)
    ->perMonth()
    ->sum('amount');
```

**Filters:**
- Period: week, month, quarter, year
- Transaction Type: all, credit, debit, refund

**Charts:**
- Line: Balance history
- Bar: Credit vs Debit comparison
- Area: Cumulative balance growth

---

### 3. TRANSACTION TRENDS (TransactionTrendService)

#### 3.1 Transaction Volume (User & Admin)
```php
// Transaction count over time
Trend::query(
    Transaction::where('wallet_id', $walletId)->completed()
)
    ->between($start, $end)
    ->perDay()
    ->count();

// Transaction value over time
Trend::query(
    Transaction::where('wallet_id', $walletId)->completed()
)
    ->between($start, $end)
    ->perMonth()
    ->sum('amount');
```

#### 3.2 By Payment Method (Admin)
```php
// Per payment method
foreach (PaymentMethodCast::cases() as $method) {
    Trend::query(
        Transaction::where('payment_method', $method)->completed()
    )
        ->between($start, $end)
        ->perMonth()
        ->sum('amount');
}
```

#### 3.3 By Status (Admin)
```php
// Success rate over time
Trend::query(Transaction::completed())
    ->between($start, $end)
    ->perDay()
    ->count();

Trend::query(Transaction::where('status', TransactionStatusCast::FAILED))
    ->between($start, $end)
    ->perDay()
    ->count();
```

**Filters:**
- Period: day, week, month, year
- Payment Method: all, wallet, cashfree, razorpay, bank_transfer
- Status: all, completed, pending, failed

**Charts:**
- Line: Transaction volume trend
- Bar: By payment method
- Doughnut: Status distribution
- Stacked Bar: Success vs Failed over time

---

### 4. COMMISSION TRENDS (CommissionTrendService)

#### 4.1 Earnings Over Time (Promoter/Advisor)
```php
// User's total earnings
Trend::query(
    MlmCommission::where('user_id', $userId)
        ->where('status', CommissionStatusCast::PAID)
)
    ->between($start, $end)
    ->perMonth()
    ->sum('net_amount');
```

#### 4.2 By Commission Type (Promoter/Advisor)
```php
// Sponsor bonus trend
Trend::query(
    MlmCommission::where('user_id', $userId)
        ->where('type', CommissionTypeCast::SPONSOR_BONUS)
        ->paid()
)
    ->between($start, $end)
    ->perMonth()
    ->sum('net_amount');

// Level commission trend
Trend::query(
    MlmCommission::where('user_id', $userId)
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION)
        ->paid()
)
    ->between($start, $end)
    ->perMonth()
    ->sum('net_amount');
```

#### 4.3 Pending vs Paid (User)
```php
// Pending commissions
Trend::query(
    MlmCommission::where('user_id', $userId)->pending()
)
    ->between($start, $end)
    ->perMonth()
    ->sum('net_amount');
```

#### 4.4 Platform Commission Stats (Admin)
```php
// Total commissions paid
Trend::query(MlmCommission::paid())
    ->between($start, $end)
    ->perMonth()
    ->sum('net_amount');

// TDS collected
Trend::query(MlmCommission::paid())
    ->between($start, $end)
    ->perMonth()
    ->sum('tds_amount');
```

**Filters:**
- Period: month, quarter, year
- Commission Type: all, sponsor_bonus, level_commission, originator, etc.
- Status: all, pending, approved, paid

**Charts:**
- Line: Earnings trend
- Stacked Bar: By commission type
- Pie: Commission type distribution
- Bar: Pending vs Paid comparison

---

### 5. TEAM TRENDS (TeamTrendService)

#### 5.1 Downline Growth (Promoter/Advisor)
```php
// Direct referrals over time
Trend::query(
    User::where('parent_id', $userId)
)
    ->between($start, $end)
    ->perMonth()
    ->count();

// Team size growth (all levels)
// Use MlmGenealogy for total_team_count tracking
```

#### 5.2 Active vs Inactive Team (Promoter/Advisor)
```php
// Active team members (with subscription)
Trend::query(
    User::where('parent_id', $userId)
        ->where('type', '!=', UserTypeCast::REGULAR)
)
    ->between($start, $end)
    ->perMonth()
    ->count();
```

#### 5.3 Originated Users (Advisor Only)
```php
// Users originated by advisor
Trend::query(
    User::where('originator_id', $advisorId)
        ->where('originator_type', User::class)
)
    ->between($start, $end)
    ->perMonth()
    ->count();
```

**Filters:**
- Period: month, quarter, year
- Level: direct, level 1-4, all
- Status: all, active, inactive

**Charts:**
- Line: Team growth trend
- Area: Cumulative team size
- Bar: Active vs Inactive
- Pie: Team by level distribution

---

### 6. ADMIN-ONLY TRENDS (AdminTrendService)

#### 6.1 Platform Revenue
```php
// Total transaction volume
Trend::query(Transaction::completed())
    ->between($start, $end)
    ->perMonth()
    ->sum('amount');

// Fee collection
Trend::query(Transaction::completed())
    ->between($start, $end)
    ->perMonth()
    ->sum('fee');
```

#### 6.2 User Growth Metrics
```php
// New vs returning users (using login timestamps)
// Churn rate calculation
// Conversion rate (regular -> member)
```

#### 6.3 Payment Gateway Performance
```php
// Success rate by gateway
// Average transaction value by gateway
// Processing time trends
```

#### 6.4 KYC Trends
```php
Trend::model(Kyc::class)
    ->between($start, $end)
    ->perMonth()
    ->count();

// By status
Trend::query(Kyc::where('status', KycStatusCast::APPROVED))
    ->between($start, $end)
    ->perMonth()
    ->count();
```

#### 6.5 Wallet Health
```php
// Total platform balance
// Average wallet balance
// Wallets with zero balance
// Suspended wallets trend
```

---

## API Endpoints (Nuxt Frontend)

### User Dashboard Charts
```
GET /api/v1/trends/wallet/balance-history
GET /api/v1/trends/wallet/credit-debit
GET /api/v1/trends/transactions/volume
GET /api/v1/trends/transactions/by-method
```

### Promoter/Advisor Charts
```
GET /api/v1/trends/commissions/earnings
GET /api/v1/trends/commissions/by-type
GET /api/v1/trends/team/growth
GET /api/v1/trends/team/active-members
```

### Admin Charts
```
GET /api/v1/admin/trends/users/registrations
GET /api/v1/admin/trends/transactions/platform-volume
GET /api/v1/admin/trends/commissions/platform-total
GET /api/v1/admin/trends/revenue/summary
GET /api/v1/admin/trends/kyc/status
```

### Common Query Parameters
```
?period=week|month|quarter|year|custom
&start=2024-01-01
&end=2024-12-31
&interval=day|week|month|year
&type=all|credit|debit
&method=all|wallet|cashfree|razorpay
```

---

## Filament Widgets

### Dashboard Widgets (Admin)
1. **UsersOverviewChart** - Registration trends
2. **TransactionVolumeChart** - Platform transaction volume
3. **RevenueChart** - Fee collection, commissions paid
4. **PaymentMethodsChart** - Breakdown by gateway
5. **KycStatusChart** - Approval trends
6. **WalletHealthChart** - Platform balance metrics

### Resource-Specific Widgets
1. **UserTransactionChart** - On UserResource view
2. **UserCommissionChart** - On UserResource view (promoter/advisor)
3. **WalletActivityChart** - On WalletResource view

---

## Response Format

```json
{
  "success": true,
  "data": {
    "labels": ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    "datasets": [
      {
        "label": "Credits",
        "data": [10000, 15000, 12000, 18000, 22000, 25000],
        "backgroundColor": "#10B981",
        "borderColor": "#059669"
      },
      {
        "label": "Debits",
        "data": [5000, 7000, 8000, 9000, 11000, 12000],
        "backgroundColor": "#EF4444",
        "borderColor": "#DC2626"
      }
    ],
    "summary": {
      "total_credits": 102000,
      "total_debits": 52000,
      "net_change": 50000,
      "period": "2024-01-01 to 2024-06-30"
    }
  },
  "meta": {
    "period": "month",
    "interval": "month",
    "generated_at": "2024-07-01T10:00:00Z"
  }
}
```

---

## Implementation Order

### Phase 1: Core Services
1. [x] Install flowframe/laravel-trend
2. [ ] Create BaseTrendService
3. [ ] Create WalletTrendService
4. [ ] Create TransactionTrendService
5. [ ] Write tests for services

### Phase 2: User-Facing (Nuxt)
6. [ ] Create TrendController for API
7. [ ] Implement wallet balance history endpoint
8. [ ] Implement credit/debit trends endpoint
9. [ ] Implement transaction volume endpoint

### Phase 3: Affiliate Trends
10. [ ] Create CommissionTrendService
11. [ ] Create TeamTrendService
12. [ ] Implement commission endpoints
13. [ ] Implement team growth endpoints

### Phase 4: Admin Panel
14. [ ] Create AdminTrendService
15. [ ] Create Filament chart widgets
16. [ ] Add filters to widgets

### Phase 5: Advanced
17. [ ] Add caching for trend data
18. [ ] Add export functionality
19. [ ] Add real-time updates (polling)

---

## Testing Strategy

Each TrendService will have corresponding tests:

```php
// tests/Feature/Services/Trends/WalletTrendServiceTest.php
describe('WalletTrendService', function () {
    it('returns balance history for user wallet', function () {
        // Create wallet with transactions
        // Call service method
        // Assert correct trend data
    });

    it('filters by date range', function () {
        // Test date filtering
    });

    it('aggregates by different intervals', function () {
        // Test perDay, perMonth, perYear
    });
});
```

---

## Notes

- All amounts in paisa (divide by 100 for display)
- Use `dateColumn()` when not using created_at
- Cache frequently accessed trends (1-5 min TTL)
- Rate limit API endpoints to prevent abuse
- Admin charts can use longer cache times
