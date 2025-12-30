# Business Analytics System - Minimal Approach

## Philosophy

**"Everything is already tracked - we just need to aggregate smartly"**

Your existing models already capture ALL business activity:
- `transactions` → All money movement (revenue, expenses, transfers)
- `affiliate_commissions` → All commission types (with `commissionable` morph)
- `user_subscriptions` → Membership revenue
- `wallets` → All balances (user, admin - via morph)
- `users` → Customer data

**We DON'T need** separate ledger/accounting tables. We need:
1. **Smart Services** that aggregate existing data
2. **Cached Snapshots** for performance (1 small table)
3. **Admin Model** with profit share config

---

## What We Actually Need (MINIMAL)

### New Tables: ONLY 3

| Table | Purpose | Why Needed |
|-------|---------|------------|
| `admins` | Admin users with hierarchy | Separate auth guard, profit share % |
| `admin_profit_distributions` | Monthly profit share records | Audit trail for admin payments |
| `business_snapshots` | Cached daily/monthly aggregates | Performance (avoid recalculating) |

### NO New Tables For:
- ❌ Ledger entries (use `transactions` + `affiliate_commissions`)
- ❌ Revenue tracking (calculate from existing data)
- ❌ Expense tracking (calculate from existing data)
- ❌ Balance sheet (calculate from wallets + transactions)

---

## 1. Revenue Sources (From Existing Data)

All revenue is already in `transactions` table via `purpose` field:

```php
// Calculate revenue from transactions
Transaction::completed()
    ->whereIn('purpose', [
        'subscription',      // Membership fees
        'subscription_renewal',
        'job_application',   // Job application fees (future)
        'service_fee',       // Platform service fees
        'withdrawal_fee',    // Withdrawal charges
        'transaction_fee',   // Transfer fees
        'product_purchase',  // E-commerce margin (future)
        'course_purchase',   // LMS fees (future)
        'advertising',       // Ad revenue (future)
        // Add more as business grows
    ])
    ->where('type', TransactionTypeCast::CREDIT)
    ->whereHas('wallet', fn($q) => $q->where('walletable_type', Admin::class))
    ->sum('amount');
```

### Revenue Categories (Extend `purpose` field)

Current purposes + Future:
```php
// Current
'subscription'           // Affiliate membership
'wallet_topup'          // User adds money
'commission_credit'     // Affiliate commission to user

// Future - Just add as you launch services
'job_application_fee'   // Job portal
'course_fee'            // LMS
'ad_placement'          // Advertising
'marketing_partnership' // Marketing agency
'service_subscription'  // SaaS services
```

---

## 2. Expense Sources (From Existing Data)

All expenses are already tracked:

```php
// Affiliate Commissions paid (expense)
MlmCommission::paid()
    ->whereNotIn('type', [CommissionTypeCast::REVERSAL, CommissionTypeCast::INCOME_DEDUCTION])
    ->sum('net_amount');

// Admin profit shares (expense)
AdminProfitDistribution::processed()
    ->wherePeriod($period)
    ->sum('amount');

// Refunds (expense)
Transaction::completed()
    ->where('type', TransactionTypeCast::REFUND)
    ->sum('amount');

// Gateway fees (expense - from transaction metadata or fee field)
Transaction::completed()
    ->where('purpose', 'gateway_fee')
    ->sum('amount');
```

---

## 3. Balance Sheet (From Existing Data)

### Assets (What Company Owns)

```php
class CompanyBalanceService
{
    public function getAssets(): array
    {
        // Company wallet (SuperAdmin's wallet)
        $companyWallet = Wallet::where('walletable_type', Admin::class)
            ->whereHas('walletable', fn($q) => $q->where('type', AdminTypeCast::SUPERADMIN))
            ->first();
        
        return [
            'company_cash' => $companyWallet?->balance ?? 0,
            'pending_receivables' => Transaction::pending()
                ->credits()
                ->whereHas('wallet.walletable', fn($q) => 
                    $q->where('walletable_type', Admin::class))
                ->sum('amount'),
            'hold_balances' => Wallet::sum('hold_balance'),
        ];
    }
    
    public function getLiabilities(): array
    {
        return [
            // Money we owe to users (their wallet balances)
            'user_wallet_balances' => Wallet::where('walletable_type', User::class)
                ->sum('balance'),
            
            // Pending withdrawals
            'pending_withdrawals' => Transaction::pending()
                ->where('purpose', 'withdrawal')
                ->sum('amount'),
            
            // Pending commissions
            'pending_commissions' => MlmCommission::payable()->sum('net_amount'),
            
            // Pending admin profit shares
            'pending_admin_shares' => AdminProfitDistribution::pending()->sum('amount'),
        ];
    }
    
    public function getEquity(): int
    {
        $assets = collect($this->getAssets())->sum();
        $liabilities = collect($this->getLiabilities())->sum();
        return $assets - $liabilities;
    }
}
```

---

## 4. Profit Calculation

```php
class ProfitCalculationService
{
    public function calculateProfit(string $period = null): array
    {
        $period = $period ?? now()->format('Y-m');
        
        // REVENUE: Credits to company wallet
        $revenue = Transaction::completed()
            ->when($period, fn($q) => $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$period]))
            ->whereHas('wallet', fn($q) => 
                $q->where('walletable_type', Admin::class)
                  ->whereHas('walletable', fn($q2) => 
                      $q2->where('type', AdminTypeCast::SUPERADMIN)))
            ->where('type', TransactionTypeCast::CREDIT)
            ->sum('amount');
        
        // EXPENSES: All outflows
        $commissionsPaid = MlmCommission::paid()
            ->when($period, fn($q) => $q->where('period_key', $period))
            ->sum('net_amount');
        
        $adminSharesPaid = AdminProfitDistribution::processed()
            ->when($period, fn($q) => $q->where('period', $period))
            ->sum('amount');
        
        $refunds = Transaction::completed()
            ->when($period, fn($q) => $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$period]))
            ->where('type', TransactionTypeCast::REFUND)
            ->sum('amount');
        
        $gatewayFees = Transaction::completed()
            ->when($period, fn($q) => $q->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$period]))
            ->sum('fee'); // Gateway fees stored in fee column
        
        $totalExpenses = $commissionsPaid + $adminSharesPaid + $refunds + $gatewayFees;
        
        return [
            'period' => $period,
            'revenue' => $revenue,
            'expenses' => [
                'commissions' => $commissionsPaid,
                'admin_shares' => $adminSharesPaid,
                'refunds' => $refunds,
                'gateway_fees' => $gatewayFees,
                'total' => $totalExpenses,
            ],
            'gross_profit' => $revenue - $totalExpenses,
            'net_profit' => $revenue - $totalExpenses, // Same for now, add operational costs later
        ];
    }
}
```

---

## 5. Business Snapshots Table (Cache Only)

Single table to cache calculated values for dashboard performance:

```php
Schema::create('business_snapshots', function (Blueprint $table) {
    $table->id();
    $table->string('period_type', 10); // daily, monthly
    $table->date('period_date');       // 2025-12-15 or 2025-12-01 (for monthly)
    
    // Cached aggregates (all in paisa)
    $table->unsignedBigInteger('total_revenue')->default(0);
    $table->unsignedBigInteger('total_expenses')->default(0);
    $table->bigInteger('net_profit')->default(0);
    $table->unsignedBigInteger('company_balance')->default(0);
    $table->unsignedBigInteger('user_wallet_total')->default(0);
    
    // Breakdown (JSON for flexibility)
    $table->json('revenue_breakdown')->nullable();  // by purpose
    $table->json('expense_breakdown')->nullable();  // by type
    $table->json('user_metrics')->nullable();       // counts, growth
    $table->json('affiliate_metrics')->nullable();        // commission stats
    
    $table->timestamps();
    
    $table->unique(['period_type', 'period_date']);
});
```

**Job to generate daily:**
```php
// Runs daily at 00:05
Schedule::job(new GenerateBusinessSnapshot('daily', now()->subDay()))->dailyAt('00:05');

// Runs monthly on 1st
Schedule::job(new GenerateBusinessSnapshot('monthly', now()->subMonth()))->monthlyOn(1, '01:00');
```

---

## 6. Revenue Source Extension (Future-Proof)

When you add new services, just add new `purpose` values:

```php
// config/business.php
return [
    'revenue_purposes' => [
        // Current
        'subscription' => ['label' => 'Membership', 'category' => 'affiliate'],
        'subscription_renewal' => ['label' => 'Renewal', 'category' => 'affiliate'],
        
        // Future - Just add here when you launch
        'job_application_fee' => ['label' => 'Job Application', 'category' => 'jobs'],
        'course_fee' => ['label' => 'Course Purchase', 'category' => 'lms'],
        'ad_placement' => ['label' => 'Advertisement', 'category' => 'advertising'],
        'marketing_fee' => ['label' => 'Marketing Service', 'category' => 'marketing'],
    ],
    
    'expense_types' => [
        'affiliate_commission' => ['label' => 'Affiliate Commissions', 'source' => 'affiliate_commissions'],
        'admin_salary' => ['label' => 'Admin Profit Share', 'source' => 'admin_profit_distributions'],
        'refund' => ['label' => 'Refunds', 'source' => 'transactions'],
        'gateway_fee' => ['label' => 'Payment Gateway', 'source' => 'transactions.fee'],
    ],
];
```

---

## 7. Admin Profit Distribution Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    MONTHLY PROFIT FLOW                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Calculate Period Profit                                     │
│     ┌──────────────────┐                                       │
│     │ Revenue (from    │                                       │
│     │ transactions)    │──┐                                    │
│     └──────────────────┘  │                                    │
│                           ▼                                    │
│     ┌──────────────────┐  ┌──────────────┐                    │
│     │ Expenses (from   │──│ NET PROFIT   │                    │
│     │ commissions +    │  │ = Revenue    │                    │
│     │ refunds + fees)  │  │ - Expenses   │                    │
│     └──────────────────┘  └──────┬───────┘                    │
│                                  │                             │
│  2. Distribute to Admins         ▼                             │
│     ┌────────────────────────────────────────┐                │
│     │ CEO (15%)      ──► ₹X to CEO wallet    │                │
│     │ Director (10%) ──► ₹Y to Dir wallet   │                │
│     │ Manager (5%)   ──► ₹Z to Mgr wallet   │                │
│     │ ...                                    │                │
│     └────────────────────────────────────────┘                │
│                                  │                             │
│  3. Record in admin_profit_distributions                       │
│     (audit trail with transaction_id)                          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 8. Filament Widgets (Use Existing TrendServices)

Extend your existing TrendServices:

```php
// Extend BaseTrendService
class CompanyTrendService extends BaseTrendService
{
    public function getRevenueByPurpose(string $period = 'month'): array
    {
        $dates = $this->parsePeriod($period);
        
        $data = Transaction::query()
            ->completed()
            ->credits()
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereHas('wallet', fn($q) => 
                $q->where('walletable_type', Admin::class))
            ->selectRaw('purpose, SUM(amount) as total')
            ->groupBy('purpose')
            ->pluck('total', 'purpose');
        
        return [
            'labels' => $data->keys()->map(fn($k) => config("business.revenue_purposes.{$k}.label", $k))->toArray(),
            'data' => $data->values()->map(fn($v) => MoneyService::toRupees($v))->toArray(),
        ];
    }
    
    public function getProfitTrend(string $period = 'year'): array
    {
        // Use cached snapshots for performance
        $snapshots = BusinessSnapshot::where('period_type', 'monthly')
            ->orderBy('period_date')
            ->limit(12)
            ->get();
        
        return $this->formatForChart(
            $snapshots->map(fn($s) => new TrendValue($s->period_date, $s->net_profit)),
            __('analytics.net_profit'),
            'rgba(34, 197, 94, 0.5)',
            'rgb(34, 197, 94)'
        );
    }
}
```

---

## 9. Admin Visibility Rules (From Cast)

```php
// AdminTypeCast methods
public function canViewFinancials(): bool
{
    return $this->getLevel() <= 1; // SuperAdmin, CEO only
}

public function canViewProfit(): bool
{
    return $this->getLevel() <= 2; // Up to Director
}

public function canViewRevenue(): bool
{
    return $this->getLevel() <= 3; // Up to Manager
}

public function canViewAllStats(): bool
{
    return $this->getLevel() <= 4; // Up to Lead
}

// In widgets
public static function canView(): bool
{
    return auth('admin')->user()?->type->canViewFinancials();
}
```

---

## 10. Final Implementation Summary

### What We Build:

| Component | Type | Purpose |
|-----------|------|---------|
| `AdminTypeCast` | Enum | Hierarchy + profit % + permissions |
| `AdminStatusCast` | Enum | Active/suspended/etc |
| `Admin` model | Model | Admin users |
| `admins` migration | Migration | Admin table |
| `admin_profit_distributions` migration | Migration | Payment audit trail |
| `business_snapshots` migration | Migration | Cached aggregates |
| `CompanyFinanceService` | Service | Balance sheet, P&L calculations |
| `ProfitDistributionService` | Service | Admin profit sharing logic |
| `CompanyTrendService` | Service | Extends BaseTrendService for analytics |
| `GenerateBusinessSnapshot` | Job | Daily/monthly snapshot generation |
| `ProcessAdminProfitSharing` | Job | Monthly profit distribution |
| 6-8 Filament Widgets | Widgets | Dashboard analytics |
| `AdminSeeder` | Seeder | SuperAdmin + CEO accounts |

### What We DON'T Build:
- ❌ Ledger tables
- ❌ Account chart tables  
- ❌ Revenue tracking tables
- ❌ Expense tracking tables
- ❌ Duplicate transaction tracking

### Table Count:
- **New tables: 3** (admins, admin_profit_distributions, business_snapshots)
- **Modified tables: 0** (we use existing data as-is)

---

## 11. Questions Answered

1. **Revenue Sources**: From `transactions.purpose` field - extensible
2. **Expense Categories**: From `affiliate_commissions` + `transactions` (fees, refunds)
3. **Historical Data**: Calculate from existing transactions, cache in snapshots
4. **Future Services**: Just add new `purpose` values to config

---

## Ready to Implement?

This approach:
- ✅ Uses 100% existing data
- ✅ Only 3 new tables (admin, distributions, cache)
- ✅ Extensible for future services (LMS, Jobs, Ads, etc.)
- ✅ Leverages existing TrendServices
- ✅ No data duplication
- ✅ Clear audit trail via transactions

**Shall I start implementation?**
