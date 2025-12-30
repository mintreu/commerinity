# Company Balance Sheet & Business Analytics System

## Overview

A comprehensive business intelligence system for admins to track company finances, analyze growth, and make data-driven decisions. Built on existing TrendService infrastructure.

---

## 1. Business Perspectives (Key Views)

### A. Financial Perspective
- Revenue & Expenses
- Profit & Loss
- Cash Flow
- Balance Sheet

### B. Customer Perspective
- User Acquisition & Retention
- User Lifetime Value (LTV)
- Churn Rate
- User Type Distribution

### C. Affiliate/Network Perspective
- Network Growth
- Commission Payouts
- Level-wise Performance
- Top Performers

### D. Operations Perspective
- Transaction Volume
- Wallet Activity
- KYC Pipeline
- Support Metrics

### E. Growth Perspective
- Month-over-Month Growth
- Year-over-Year Comparison
- Forecasting
- Goal Tracking

---

## 2. Balance Sheet Structure

### Assets (What Company Owns)

```
ASSETS
├── Current Assets
│   ├── Company Wallet Balance (SuperAdmin wallet)
│   ├── Pending Receivables (pending transactions)
│   ├── Hold Balances (user wallets hold)
│   └── Prepaid Expenses
│
└── Non-Current Assets
    ├── Platform Value (calculated)
    └── User Base Value (LTV × Users)
```

### Liabilities (What Company Owes)

```
LIABILITIES
├── Current Liabilities
│   ├── User Wallet Balances (total all user wallets)
│   ├── Pending Withdrawals
│   ├── Admin Profit Shares (unpaid)
│   ├── Commission Payouts (pending Affiliate)
│   └── Refunds Pending
│
└── Long-Term Liabilities
    ├── Future Commission Obligations
    └── Subscription Credits (unused)
```

### Equity

```
EQUITY
├── Retained Earnings (cumulative profit)
├── Current Period Profit/Loss
└── Owner's Capital (initial investment)
```

---

## 3. Database Design

### company_ledger_entries Table

```php
Schema::create('company_ledger_entries', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 24)->unique();
    
    // Classification
    $table->string('account_type'); // asset, liability, equity, revenue, expense
    $table->string('account_code', 20); // e.g., AST-001, LIB-001, REV-001
    $table->string('account_name');
    $table->string('category'); // current_asset, non_current_asset, etc.
    
    // Entry details
    $table->string('entry_type'); // debit, credit
    $table->unsignedBigInteger('amount'); // in paisa
    $table->unsignedBigInteger('balance_after'); // running balance
    
    // Reference
    $table->nullableMorphs('referenceable'); // Transaction, Order, Commission, etc.
    $table->string('description');
    $table->string('period', 7); // YYYY-MM
    
    // Audit
    $table->foreignId('created_by_admin_id')->nullable()->constrained('admins');
    $table->boolean('is_adjustment')->default(false);
    $table->text('notes')->nullable();
    
    $table->timestamps();
    
    $table->index(['account_type', 'period']);
    $table->index(['account_code', 'created_at']);
});
```

### company_financial_snapshots Table (Daily/Monthly Snapshots)

```php
Schema::create('company_financial_snapshots', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 24)->unique();
    
    // Period
    $table->string('period_type'); // daily, monthly, quarterly, yearly
    $table->date('period_date');
    $table->string('period_label', 10); // 2025-12-15, 2025-12, Q4-2025, 2025
    
    // Assets
    $table->unsignedBigInteger('total_assets')->default(0);
    $table->unsignedBigInteger('company_wallet_balance')->default(0);
    $table->unsignedBigInteger('pending_receivables')->default(0);
    $table->unsignedBigInteger('hold_balances')->default(0);
    
    // Liabilities
    $table->unsignedBigInteger('total_liabilities')->default(0);
    $table->unsignedBigInteger('user_wallet_balances')->default(0);
    $table->unsignedBigInteger('pending_withdrawals')->default(0);
    $table->unsignedBigInteger('pending_commissions')->default(0);
    $table->unsignedBigInteger('pending_admin_shares')->default(0);
    
    // Equity
    $table->bigInteger('total_equity')->default(0); // Can be negative
    $table->bigInteger('retained_earnings')->default(0);
    $table->bigInteger('period_profit_loss')->default(0);
    
    // Revenue (for P&L)
    $table->unsignedBigInteger('total_revenue')->default(0);
    $table->unsignedBigInteger('subscription_revenue')->default(0);
    $table->unsignedBigInteger('transaction_fees')->default(0);
    $table->unsignedBigInteger('other_revenue')->default(0);
    
    // Expenses (for P&L)
    $table->unsignedBigInteger('total_expenses')->default(0);
    $table->unsignedBigInteger('commission_expense')->default(0);
    $table->unsignedBigInteger('admin_salary_expense')->default(0);
    $table->unsignedBigInteger('refund_expense')->default(0);
    $table->unsignedBigInteger('operational_expense')->default(0);
    
    // Metrics
    $table->unsignedInteger('total_users')->default(0);
    $table->unsignedInteger('active_users')->default(0);
    $table->unsignedInteger('new_users')->default(0);
    $table->unsignedInteger('total_transactions')->default(0);
    $table->unsignedBigInteger('transaction_volume')->default(0);
    
    // JSON for detailed breakdown
    $table->json('revenue_breakdown')->nullable();
    $table->json('expense_breakdown')->nullable();
    $table->json('user_metrics')->nullable();
    $table->json('affiliate_metrics')->nullable();
    
    $table->timestamps();
    
    $table->unique(['period_type', 'period_date']);
    $table->index('period_date');
});
```

### business_goals Table

```php
Schema::create('business_goals', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 24)->unique();
    
    $table->string('name');
    $table->string('metric'); // revenue, users, transactions, etc.
    $table->string('period_type'); // monthly, quarterly, yearly
    $table->string('period', 10); // 2025-12, Q4-2025, 2025
    
    $table->unsignedBigInteger('target_value');
    $table->unsignedBigInteger('current_value')->default(0);
    $table->decimal('progress_percent', 5, 2)->default(0);
    
    $table->string('status')->default('active'); // active, achieved, missed, cancelled
    $table->foreignId('created_by_admin_id')->constrained('admins');
    
    $table->text('description')->nullable();
    $table->json('milestones')->nullable();
    
    $table->timestamps();
});
```

---

## 4. Account Chart (Chart of Accounts)

### Account Codes Structure

```php
// config/accounting.php

return [
    'accounts' => [
        // ASSETS (1xx)
        'AST-100' => ['name' => 'Company Main Wallet', 'type' => 'asset', 'category' => 'current'],
        'AST-101' => ['name' => 'Pending Receivables', 'type' => 'asset', 'category' => 'current'],
        'AST-102' => ['name' => 'Hold Balances', 'type' => 'asset', 'category' => 'current'],
        'AST-103' => ['name' => 'Gateway Pending', 'type' => 'asset', 'category' => 'current'],
        
        // LIABILITIES (2xx)
        'LIB-200' => ['name' => 'User Wallet Balances', 'type' => 'liability', 'category' => 'current'],
        'LIB-201' => ['name' => 'Pending Withdrawals', 'type' => 'liability', 'category' => 'current'],
        'LIB-202' => ['name' => 'Pending Commissions', 'type' => 'liability', 'category' => 'current'],
        'LIB-203' => ['name' => 'Admin Profit Shares', 'type' => 'liability', 'category' => 'current'],
        'LIB-204' => ['name' => 'Refunds Pending', 'type' => 'liability', 'category' => 'current'],
        
        // EQUITY (3xx)
        'EQT-300' => ['name' => 'Owner Capital', 'type' => 'equity', 'category' => 'equity'],
        'EQT-301' => ['name' => 'Retained Earnings', 'type' => 'equity', 'category' => 'equity'],
        
        // REVENUE (4xx)
        'REV-400' => ['name' => 'Subscription Revenue', 'type' => 'revenue', 'category' => 'operating'],
        'REV-401' => ['name' => 'Transaction Fees', 'type' => 'revenue', 'category' => 'operating'],
        'REV-402' => ['name' => 'Service Charges', 'type' => 'revenue', 'category' => 'operating'],
        'REV-403' => ['name' => 'Other Income', 'type' => 'revenue', 'category' => 'non_operating'],
        
        // EXPENSES (5xx)
        'EXP-500' => ['name' => 'Affiliate Commissions', 'type' => 'expense', 'category' => 'operating'],
        'EXP-501' => ['name' => 'Admin Salaries', 'type' => 'expense', 'category' => 'operating'],
        'EXP-502' => ['name' => 'Gateway Fees', 'type' => 'expense', 'category' => 'operating'],
        'EXP-503' => ['name' => 'Refunds', 'type' => 'expense', 'category' => 'operating'],
        'EXP-504' => ['name' => 'Operational Costs', 'type' => 'expense', 'category' => 'operating'],
    ],
];
```

---

## 5. Services Architecture

### CompanyLedgerService

```php
<?php

declare(strict_types=1);

namespace App\Services\Accounting;

class CompanyLedgerService
{
    // Record a ledger entry
    public function record(
        string $accountCode,
        string $entryType, // debit or credit
        int $amountPaisa,
        ?Model $reference = null,
        ?string $description = null,
        ?int $adminId = null
    ): CompanyLedgerEntry;
    
    // Get account balance
    public function getAccountBalance(string $accountCode, ?string $period = null): int;
    
    // Get all balances for balance sheet
    public function getBalanceSheet(?string $asOfDate = null): array;
    
    // Record double-entry (debit one, credit another)
    public function recordDoubleEntry(
        string $debitAccount,
        string $creditAccount,
        int $amount,
        ?Model $reference = null,
        string $description = ''
    ): void;
}
```

### FinancialSnapshotService

```php
<?php

declare(strict_types=1);

namespace App\Services\Accounting;

class FinancialSnapshotService
{
    // Generate daily snapshot (scheduled job)
    public function generateDailySnapshot(Carbon $date): CompanyFinancialSnapshot;
    
    // Generate monthly snapshot (aggregates daily)
    public function generateMonthlySnapshot(string $yearMonth): CompanyFinancialSnapshot;
    
    // Get balance sheet data
    public function getBalanceSheet(?string $asOfDate = null): array;
    
    // Get P&L statement
    public function getProfitLossStatement(string $fromDate, string $toDate): array;
    
    // Get cash flow statement
    public function getCashFlowStatement(string $fromDate, string $toDate): array;
    
    // Compare periods
    public function comparePeriods(string $period1, string $period2): array;
}
```

### BusinessAnalyticsService

```php
<?php

declare(strict_types=1);

namespace App\Services\Analytics;

class BusinessAnalyticsService
{
    // Dashboard KPIs
    public function getDashboardKPIs(?string $period = 'month'): array;
    
    // User analytics
    public function getUserAnalytics(string $period = 'month'): array;
    
    // Affiliate analytics
    public function getMlmAnalytics(string $period = 'month'): array;
    
    // Revenue analytics
    public function getRevenueAnalytics(string $period = 'month'): array;
    
    // Growth metrics
    public function getGrowthMetrics(): array;
    
    // Forecasting (simple linear regression)
    public function getForecast(string $metric, int $monthsAhead = 3): array;
}
```

---

## 6. Filament Dashboard Widgets

### Widget Hierarchy (Admin Type Based)

| Widget | SuperAdmin | CEO | Director | Manager | Lead | Executive |
|--------|------------|-----|----------|---------|------|-----------|
| BalanceSheetWidget | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| ProfitLossWidget | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| RevenueChartWidget | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| UserGrowthWidget | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| TransactionVolumeWidget | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| AffiliatePerformanceWidget | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| GoalProgressWidget | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| AdminTeamWidget | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| PersonalEarningsWidget | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |

### Widget Examples

#### BalanceSheetWidget (SuperAdmin & CEO Only)

```php
<?php

namespace App\Filament\Widgets;

use App\Services\Accounting\FinancialSnapshotService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BalanceSheetWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    public static function canView(): bool
    {
        $admin = auth('admin')->user();
        return $admin?->type->getLevel() <= 1; // SuperAdmin & CEO only
    }
    
    protected function getStats(): array
    {
        $service = app(FinancialSnapshotService::class);
        $data = $service->getBalanceSheet();
        
        return [
            Stat::make(__('accounting.total_assets'), MoneyService::format($data['total_assets']))
                ->description(__('accounting.assets_description'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),
                
            Stat::make(__('accounting.total_liabilities'), MoneyService::format($data['total_liabilities']))
                ->description(__('accounting.liabilities_description'))
                ->color('warning'),
                
            Stat::make(__('accounting.net_equity'), MoneyService::format($data['total_equity']))
                ->description($data['equity_change_percent'] . '% ' . __('accounting.from_last_month'))
                ->descriptionIcon($data['equity_change_percent'] >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($data['equity_change_percent'] >= 0 ? 'success' : 'danger'),
        ];
    }
}
```

#### RevenueExpenseChartWidget

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueExpenseChartWidget extends ChartWidget
{
    protected static ?string $heading = null;
    protected static ?int $sort = 3;
    
    public ?string $filter = 'month';
    
    protected function getHeading(): string
    {
        return __('accounting.revenue_vs_expense');
    }
    
    protected function getFilters(): ?array
    {
        return [
            'week' => __('periods.this_week'),
            'month' => __('periods.this_month'),
            'quarter' => __('periods.this_quarter'),
            'year' => __('periods.this_year'),
        ];
    }
    
    protected function getData(): array
    {
        $service = app(BusinessAnalyticsService::class);
        return $service->getRevenueExpenseChart($this->filter);
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}
```

#### GoalProgressWidget

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class GoalProgressWidget extends Widget
{
    protected static string $view = 'filament.widgets.goal-progress';
    protected static ?int $sort = 5;
    
    public function getGoals(): Collection
    {
        return BusinessGoal::where('status', 'active')
            ->orderBy('period')
            ->get();
    }
}
```

---

## 7. Filament Pages

### BalanceSheetPage

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BalanceSheet extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.pages.balance-sheet';
    
    public static function canAccess(): bool
    {
        return auth('admin')->user()?->type->getLevel() <= 1;
    }
    
    // Properties for date filtering, export, etc.
}
```

### ProfitLossPage

```php
<?php

namespace App\Filament\Pages;

class ProfitLoss extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.profit-loss';
    
    // P&L statement with period selection
    // Revenue breakdown
    // Expense breakdown
    // Net profit calculation
}
```

### AnalyticsDashboardPage

```php
<?php

namespace App\Filament\Pages;

class AnalyticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Analytics';
    
    // All-in-one analytics view
    // Filterable by period
    // Multiple chart types
    // Export functionality
}
```

### BusinessGoalsPage (CRUD + Progress Tracking)

```php
<?php

namespace App\Filament\Resources;

class BusinessGoalResource extends Resource
{
    // Create/Edit goals
    // Track progress
    // Milestones management
    // Notifications on achievement
}
```

---

## 8. Scheduled Jobs

### GenerateDailyFinancialSnapshot

```php
// Runs daily at midnight
Schedule::job(new GenerateDailyFinancialSnapshot(now()->subDay()))
    ->dailyAt('00:05');
```

### GenerateMonthlyFinancialSnapshot

```php
// Runs on 1st of every month
Schedule::job(new GenerateMonthlyFinancialSnapshot(now()->subMonth()->format('Y-m')))
    ->monthlyOn(1, '01:00');
```

### UpdateBusinessGoalProgress

```php
// Runs every 6 hours
Schedule::job(new UpdateBusinessGoalProgress())
    ->everySixHours();
```

---

## 9. Key Metrics & KPIs

### Financial KPIs
- **Gross Revenue**: Total income before expenses
- **Net Profit**: Revenue - All Expenses
- **Profit Margin**: (Net Profit / Revenue) × 100
- **Operating Cash Flow**: Cash from operations
- **Burn Rate**: Monthly expense rate

### Customer KPIs
- **CAC** (Customer Acquisition Cost): Marketing spend / New users
- **LTV** (Lifetime Value): Average revenue per user × Average lifespan
- **LTV:CAC Ratio**: Target > 3:1
- **Churn Rate**: Lost users / Total users
- **MRR** (Monthly Recurring Revenue): From subscriptions

### Affiliate KPIs
- **Network Depth**: Average levels in tree
- **Commission Payout Ratio**: Commissions / Revenue
- **Active Distributor Rate**: Active / Total distributors
- **Average Team Size**: Users per promoter

### Operational KPIs
- **Transaction Success Rate**: Successful / Total transactions
- **Average Transaction Value**: Total volume / Transaction count
- **KYC Approval Rate**: Approved / Submitted
- **Support Resolution Time**: Average ticket close time

---

## 10. Export & Reporting

### Export Formats
- PDF (Balance Sheet, P&L, Reports)
- Excel (Detailed data with charts)
- CSV (Raw data export)

### Scheduled Reports (Email to Admins)
- Daily Summary (SuperAdmin, CEO)
- Weekly Performance Report (All admins based on level)
- Monthly Financial Report (SuperAdmin, CEO, Director)
- Quarterly Business Review (SuperAdmin, CEO)

---

## 11. Implementation Phases

### Phase 1: Database & Models (Day 1-2)
1. Create migrations (ledger, snapshots, goals)
2. Create models with relationships
3. Create config/accounting.php chart of accounts
4. Write factories & seeders

### Phase 2: Services (Day 3-4)
1. CompanyLedgerService
2. FinancialSnapshotService
3. BusinessAnalyticsService
4. Extend existing TrendServices

### Phase 3: Scheduled Jobs (Day 5)
1. GenerateDailyFinancialSnapshot
2. GenerateMonthlyFinancialSnapshot
3. UpdateBusinessGoalProgress
4. Configure scheduler

### Phase 4: Filament Widgets (Day 6-7)
1. BalanceSheetWidget
2. ProfitLossWidget
3. RevenueChartWidget
4. UserGrowthWidget
5. GoalProgressWidget
6. AffiliatePerformanceWidget

### Phase 5: Filament Pages (Day 8-9)
1. BalanceSheet page
2. ProfitLoss page
3. AnalyticsDashboard page
4. BusinessGoalResource

### Phase 6: Exports & Reports (Day 10)
1. PDF export for financial statements
2. Excel export for detailed data
3. Scheduled email reports

---

## 12. Visibility Rules Expanded

### Financial Data Visibility

| Data | SuperAdmin | CEO | Director | Manager | Lead | Executive |
|------|------------|-----|----------|---------|------|-----------|
| Full Balance Sheet | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Company Profit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Revenue Details | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Expense Details | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| User Metrics | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Transaction Volume | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Affiliate Tree Stats | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Goal Progress | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Admin Payouts | ✅ | Own | Own | Own | Own | Own |

---

## 13. Sample Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│ COMPANY DASHBOARD                           [Period: This Month ▼] │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
│ │ Total Assets│ │ Liabilities │ │ Net Equity  │ │ Net Profit  │   │
│ │  ₹45.2L    │ │   ₹32.1L   │ │   ₹13.1L   │ │   ₹2.8L    │   │
│ │   +12% ↑   │ │    +8% ↑   │ │   +18% ↑   │ │   +25% ↑   │   │
│ └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│ ┌───────────────────────────────┐ ┌───────────────────────────────┐│
│ │    REVENUE VS EXPENSE         │ │      USER GROWTH              ││
│ │    (Bar Chart)                │ │      (Line Chart)             ││
│ │                               │ │                               ││
│ │  ▓▓▓▓▓▓▓▓  Revenue           │ │  ────── Total Users          ││
│ │  ░░░░░    Expense            │ │  ------ Active Users          ││
│ │                               │ │                               ││
│ └───────────────────────────────┘ └───────────────────────────────┘│
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│ ┌───────────────────────────────┐ ┌───────────────────────────────┐│
│ │    REVENUE BREAKDOWN          │ │      BUSINESS GOALS           ││
│ │    (Pie Chart)                │ │                               ││
│ │                               │ │  ▓▓▓▓▓▓▓░░░ Revenue: 78%    ││
│ │  🟢 Subscriptions: 45%       │ │  ▓▓▓▓▓░░░░░ Users: 52%       ││
│ │  🔵 Transaction Fees: 35%    │ │  ▓▓▓▓▓▓▓▓░░ Affiliate: 85%         ││
│ │  🟡 Other: 20%               │ │                               ││
│ └───────────────────────────────┘ └───────────────────────────────┘│
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│ RECENT TRANSACTIONS                         [View All →]           │
│ ┌───────────────────────────────────────────────────────────────┐ │
│ │ TXN-ABC123 │ Subscription │ +₹999   │ 2 mins ago │ Completed │ │
│ │ TXN-DEF456 │ Commission   │ -₹150   │ 5 mins ago │ Completed │ │
│ │ TXN-GHI789 │ Withdrawal   │ -₹5,000 │ 10 mins ago│ Pending   │ │
│ └───────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 14. Questions for Clarification

1. **Revenue Sources**: What generates revenue?
   - Subscriptions (membership fees)?
   - Transaction fees (% of transactions)?
   - Product sales margin?
   - Service charges?

2. **Expense Categories**: What are the main expenses?
   - Affiliate commissions?
   - Payment gateway fees?
   - Admin salaries/profit shares?
   - Operational costs?

3. **Snapshot Frequency**: 
   - Daily snapshots (recommended)?
   - Hourly for high-volume periods?

4. **Historical Data**: 
   - How far back to generate snapshots?
   - Seed with historical data or start fresh?

5. **Export Recipients**:
   - Which admins receive which reports?
   - Email frequency preferences?

---

## Ready for Implementation

This system integrates with:
- ✅ Existing TrendServices (BaseTrendService, WalletTrendService, etc.)
- ✅ Existing Transaction model (all transaction types tracked)
- ✅ Existing Wallet model (polymorphic for admin wallets)
- ✅ New Admin hierarchy system

Combined with the Admin Hierarchy plan, this gives you a complete business management system.

**Shall I proceed with implementation?**
