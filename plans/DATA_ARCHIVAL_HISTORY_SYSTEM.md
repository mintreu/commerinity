# Data Archival & History System for Scale

## The Problem

At 10 crore (100 million) users with 5+ years of data:
- `transactions` table: 500M+ records
- `affiliate_commissions` table: 200M+ records  
- `user_subscriptions`: 150M+ records
- Query performance degrades exponentially
- Backup/restore becomes nightmare
- Storage costs explode

## The Solution: 3-Tier Data Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    3-TIER DATA ARCHITECTURE                             │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  TIER 1: HOT DATA (Main Tables)                                        │
│  ────────────────────────────────                                      │
│  • Last 1 year of records                                              │
│  • Full CRUD operations                                                │
│  • Real-time queries                                                   │
│  • User-facing APIs                                                    │
│  • Tables: transactions, affiliate_commissions, etc.                         │
│                                                                         │
│  ↓ Monthly archival job moves data older than 1 year ↓                 │
│                                                                         │
│  TIER 2: WARM DATA (Archive Tables)                                    │
│  ────────────────────────────────────                                  │
│  • 1-10 years old records                                              │
│  • Read-only (no updates)                                              │
│  • Admin queries with filters                                          │
│  • Partitioned by year/month                                           │
│  • Tables: transactions_archive, commissions_archive, etc.             │
│                                                                         │
│  ↓ Yearly aggregation job creates summaries ↓                          │
│                                                                         │
│  TIER 3: COLD DATA (Aggregated Summaries)                              │
│  ─────────────────────────────────────────                             │
│  • 10+ years old                                                       │
│  • Pre-aggregated summaries only                                       │
│  • AI/ML ready data structure                                          │
│  • Tables: yearly_summaries, user_lifetime_stats                       │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Database Design

### Tier 1: Hot Data (Existing Tables - No Change)

Keep existing tables as-is. They hold **last 1 year** of data:
- `transactions`
- `affiliate_commissions`
- `user_subscriptions`
- `wallets` (always hot - current balances)

### Tier 2: Archive Tables (New)

#### transactions_archive

```php
Schema::create('transactions_archive', function (Blueprint $table) {
    // Same structure as transactions
    $table->id();
    $table->string('uuid', 24)->index();
    $table->foreignId('wallet_id');
    $table->nullableMorphs('transactionable');
    $table->string('type', 20);
    $table->string('status', 20);
    $table->unsignedBigInteger('amount');
    $table->unsignedBigInteger('fee')->default(0);
    $table->unsignedBigInteger('tax')->default(0);
    $table->unsignedBigInteger('net_amount');
    $table->string('currency', 3)->default('INR');
    $table->string('payment_method', 30)->nullable();
    $table->string('purpose', 50)->nullable();
    $table->string('description')->nullable();
    $table->string('reference_number', 50)->nullable();
    $table->unsignedBigInteger('balance_after')->default(0);
    $table->json('metadata')->nullable();
    $table->timestamp('original_created_at'); // When originally created
    $table->timestamp('archived_at');         // When moved to archive
    
    // Partitioning key
    $table->unsignedSmallInteger('archive_year');
    $table->unsignedTinyInteger('archive_month');
    
    $table->timestamps();
    
    // Indexes for admin queries
    $table->index(['wallet_id', 'archive_year']);
    $table->index(['purpose', 'archive_year', 'archive_month']);
    $table->index(['type', 'status', 'archive_year']);
    $table->index('archive_year');
});
```

#### commissions_archive

```php
Schema::create('commissions_archive', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 24)->index();
    $table->foreignId('user_id');
    $table->foreignId('from_user_id')->nullable();
    $table->nullableMorphs('commissionable');
    $table->string('type', 30);
    $table->unsignedTinyInteger('level')->nullable();
    $table->decimal('rate_percent', 5, 2)->nullable();
    $table->unsignedBigInteger('base_amount')->default(0);
    $table->unsignedBigInteger('gross_amount');
    $table->unsignedBigInteger('tds_amount')->default(0);
    $table->unsignedBigInteger('admin_fee')->default(0);
    $table->unsignedBigInteger('net_amount');
    $table->string('status', 20);
    $table->string('period_key', 7);
    $table->json('metadata')->nullable();
    $table->timestamp('original_created_at');
    $table->timestamp('archived_at');
    
    $table->unsignedSmallInteger('archive_year');
    $table->unsignedTinyInteger('archive_month');
    
    $table->timestamps();
    
    $table->index(['user_id', 'archive_year']);
    $table->index(['type', 'archive_year']);
    $table->index(['period_key']);
});
```

#### subscriptions_archive

```php
Schema::create('subscriptions_archive', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 36)->index();
    $table->foreignId('user_id');
    $table->foreignId('stage_id');
    $table->unsignedBigInteger('amount');
    $table->string('status', 20);
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->unsignedBigInteger('total_commission_earned')->default(0);
    $table->json('metadata')->nullable();
    $table->timestamp('original_created_at');
    $table->timestamp('archived_at');
    
    $table->unsignedSmallInteger('archive_year');
    
    $table->timestamps();
    
    $table->index(['user_id', 'archive_year']);
});
```

### Tier 3: Aggregated Summaries (New)

#### monthly_business_summaries

```php
Schema::create('monthly_business_summaries', function (Blueprint $table) {
    $table->id();
    $table->string('period', 7)->unique(); // YYYY-MM
    $table->unsignedSmallInteger('year');
    $table->unsignedTinyInteger('month');
    
    // Revenue breakdown
    $table->unsignedBigInteger('total_revenue')->default(0);
    $table->json('revenue_by_purpose'); // {subscription: X, job_fee: Y, ...}
    
    // Expense breakdown
    $table->unsignedBigInteger('total_expenses')->default(0);
    $table->json('expense_breakdown'); // {commissions: X, refunds: Y, ...}
    
    // Profit
    $table->bigInteger('gross_profit')->default(0);
    $table->bigInteger('net_profit')->default(0);
    
    // User metrics
    $table->unsignedInteger('total_users')->default(0);
    $table->unsignedInteger('new_users')->default(0);
    $table->unsignedInteger('active_users')->default(0);
    $table->unsignedInteger('churned_users')->default(0);
    
    // Subscription metrics
    $table->unsignedInteger('new_subscriptions')->default(0);
    $table->unsignedInteger('renewals')->default(0);
    $table->unsignedInteger('cancellations')->default(0);
    $table->unsignedBigInteger('subscription_revenue')->default(0);
    
    // Affiliate metrics
    $table->unsignedBigInteger('total_commissions_paid')->default(0);
    $table->unsignedInteger('commission_recipients')->default(0);
    $table->json('commissions_by_type'); // {sponsor: X, level: Y, ...}
    
    // Transaction metrics
    $table->unsignedInteger('total_transactions')->default(0);
    $table->unsignedBigInteger('transaction_volume')->default(0);
    $table->json('transactions_by_type'); // {credit: X, debit: Y, ...}
    $table->json('transactions_by_purpose');
    
    // Wallet metrics
    $table->unsignedBigInteger('total_wallet_balance')->default(0);
    $table->unsignedBigInteger('company_wallet_balance')->default(0);
    $table->unsignedBigInteger('user_wallet_balance')->default(0);
    
    // Growth metrics (for AI)
    $table->decimal('revenue_growth_percent', 8, 2)->nullable();
    $table->decimal('user_growth_percent', 8, 2)->nullable();
    $table->decimal('profit_margin_percent', 8, 2)->nullable();
    
    // AI-ready aggregates
    $table->json('trends_data')->nullable(); // Pre-calculated for ML
    $table->json('anomalies')->nullable();   // Detected anomalies
    
    $table->timestamps();
    
    $table->index(['year', 'month']);
});
```

#### yearly_business_summaries

```php
Schema::create('yearly_business_summaries', function (Blueprint $table) {
    $table->id();
    $table->unsignedSmallInteger('year')->unique();
    
    // Annual totals
    $table->unsignedBigInteger('total_revenue')->default(0);
    $table->unsignedBigInteger('total_expenses')->default(0);
    $table->bigInteger('net_profit')->default(0);
    
    // Breakdowns
    $table->json('revenue_by_purpose');
    $table->json('revenue_by_quarter');
    $table->json('expense_breakdown');
    $table->json('expense_by_quarter');
    
    // User stats
    $table->unsignedInteger('users_at_year_start')->default(0);
    $table->unsignedInteger('users_at_year_end')->default(0);
    $table->unsignedInteger('new_users')->default(0);
    $table->unsignedInteger('churned_users')->default(0);
    $table->decimal('retention_rate', 5, 2)->nullable();
    
    // Best/worst months
    $table->unsignedTinyInteger('best_revenue_month')->nullable();
    $table->unsignedTinyInteger('worst_revenue_month')->nullable();
    $table->unsignedBigInteger('best_revenue_amount')->default(0);
    
    // AI-ready data
    $table->json('monthly_breakdown'); // All 12 months summary
    $table->json('growth_metrics');
    $table->json('predictions')->nullable(); // AI-generated predictions
    
    $table->timestamps();
});
```

#### user_lifetime_stats (Per-User Summary)

```php
Schema::create('user_lifetime_stats', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    
    // Lifetime totals
    $table->unsignedBigInteger('lifetime_spent')->default(0);
    $table->unsignedBigInteger('lifetime_earned')->default(0);
    $table->unsignedBigInteger('lifetime_commissions')->default(0);
    $table->unsignedBigInteger('lifetime_withdrawals')->default(0);
    
    // Counts
    $table->unsignedInteger('total_transactions')->default(0);
    $table->unsignedInteger('total_orders')->default(0);
    $table->unsignedInteger('total_subscriptions')->default(0);
    $table->unsignedInteger('total_referrals')->default(0);
    $table->unsignedInteger('active_referrals')->default(0);
    
    // Affiliate stats
    $table->unsignedTinyInteger('highest_level_achieved')->default(1);
    $table->unsignedBigInteger('team_total_volume')->default(0);
    
    // Dates
    $table->date('first_transaction_date')->nullable();
    $table->date('last_transaction_date')->nullable();
    $table->date('first_subscription_date')->nullable();
    
    // Yearly breakdown (rolling 10 years)
    $table->json('yearly_summary'); // [{year: 2025, spent: X, earned: Y}, ...]
    
    // AI features
    $table->decimal('predicted_ltv', 12, 2)->nullable(); // Predicted lifetime value
    $table->string('segment', 20)->nullable(); // high_value, at_risk, dormant, etc.
    $table->json('behavior_flags')->nullable(); // AI-detected patterns
    
    $table->timestamps();
    
    $table->index('segment');
    $table->index('predicted_ltv');
});
```

---

## Archival Jobs

### 1. Daily Transaction Archival

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Archive;

use App\Models\Transaction;
use App\Models\Archive\TransactionArchive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class ArchiveOldTransactions implements ShouldQueue
{
    use Queueable;
    
    private const BATCH_SIZE = 10000;
    private const RETENTION_DAYS = 365; // 1 year in hot storage
    
    public function handle(): void
    {
        $cutoffDate = now()->subDays(self::RETENTION_DAYS);
        
        DB::transaction(function () use ($cutoffDate) {
            Transaction::where('created_at', '<', $cutoffDate)
                ->where('status', '!=', 'pending') // Don't archive pending
                ->chunkById(self::BATCH_SIZE, function ($transactions) {
                    $archiveData = $transactions->map(fn($t) => [
                        'uuid' => $t->uuid,
                        'wallet_id' => $t->wallet_id,
                        'transactionable_type' => $t->transactionable_type,
                        'transactionable_id' => $t->transactionable_id,
                        'type' => $t->getRawOriginal('type'),
                        'status' => $t->getRawOriginal('status'),
                        'amount' => $t->amount,
                        'fee' => $t->fee,
                        'tax' => $t->tax,
                        'net_amount' => $t->net_amount,
                        'currency' => $t->currency,
                        'payment_method' => $t->getRawOriginal('payment_method'),
                        'purpose' => $t->purpose,
                        'description' => $t->description,
                        'reference_number' => $t->reference_number,
                        'balance_after' => $t->balance_after,
                        'metadata' => json_encode($t->metadata),
                        'original_created_at' => $t->created_at,
                        'archived_at' => now(),
                        'archive_year' => $t->created_at->year,
                        'archive_month' => $t->created_at->month,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->toArray();
                    
                    // Insert to archive
                    TransactionArchive::insert($archiveData);
                    
                    // Delete from main table
                    Transaction::whereIn('id', $transactions->pluck('id'))->delete();
                });
        });
        
        // Log archival stats
        logger()->info('Transaction archival completed', [
            'cutoff_date' => $cutoffDate,
            'archived_count' => TransactionArchive::where('archived_at', '>=', now()->subHour())->count(),
        ]);
    }
}
```

### 2. Monthly Summary Generation

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Archive;

use App\Models\Archive\MonthlyBusinessSummary;
use App\Services\Analytics\SummaryCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateMonthlyBusinessSummary implements ShouldQueue
{
    use Queueable;
    
    public function __construct(
        private readonly string $period // YYYY-MM
    ) {}
    
    public function handle(SummaryCalculationService $calculator): void
    {
        [$year, $month] = explode('-', $this->period);
        
        // Calculate from both hot + archive data
        $summary = $calculator->calculateMonthlySummary((int) $year, (int) $month);
        
        MonthlyBusinessSummary::updateOrCreate(
            ['period' => $this->period],
            [
                'year' => $year,
                'month' => $month,
                ...$summary,
            ]
        );
    }
}
```

### 3. User Lifetime Stats Update

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Archive;

use App\Models\User;
use App\Models\Archive\UserLifetimeStat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateUserLifetimeStats implements ShouldQueue
{
    use Queueable;
    
    public function __construct(
        private readonly int $userId
    ) {}
    
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user) return;
        
        // Aggregate from hot + archive tables
        $stats = $this->calculateLifetimeStats($user);
        
        UserLifetimeStat::updateOrCreate(
            ['user_id' => $this->userId],
            $stats
        );
    }
    
    private function calculateLifetimeStats(User $user): array
    {
        // Query both hot and archive tables
        // ... aggregation logic
    }
}
```

---

## Query Service (Unified Access)

```php
<?php

declare(strict_types=1);

namespace App\Services\History;

use App\Models\Transaction;
use App\Models\Archive\TransactionArchive;
use App\Models\Archive\MonthlyBusinessSummary;
use Illuminate\Support\Collection;

class HistoryQueryService
{
    /**
     * Get transactions with automatic tier selection
     */
    public function getTransactions(
        int $walletId,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null,
        bool $isAdmin = false
    ): Collection {
        $fromDate = $fromDate ?? now()->subYear();
        $toDate = $toDate ?? now();
        
        // User access: max 1 year (or 3 years for premium)
        if (!$isAdmin) {
            $maxHistory = config('history.user_max_years', 1);
            $fromDate = max($fromDate, now()->subYears($maxHistory));
        }
        
        $results = collect();
        
        // Check if we need hot data (last 1 year)
        if ($toDate->isAfter(now()->subYear())) {
            $hotData = Transaction::where('wallet_id', $walletId)
                ->whereBetween('created_at', [
                    max($fromDate, now()->subYear()),
                    $toDate
                ])
                ->get();
            $results = $results->merge($hotData);
        }
        
        // Check if we need archive data (admin only for > 1 year)
        if ($isAdmin && $fromDate->isBefore(now()->subYear())) {
            $archiveData = TransactionArchive::where('wallet_id', $walletId)
                ->whereBetween('original_created_at', [
                    $fromDate,
                    min($toDate, now()->subYear())
                ])
                ->get();
            $results = $results->merge($archiveData);
        }
        
        return $results->sortByDesc('created_at');
    }
    
    /**
     * Get business summary (always from aggregates for old data)
     */
    public function getBusinessSummary(
        int $year,
        ?int $month = null,
        bool $detailed = false
    ): array {
        // For current month: calculate real-time
        if ($year === now()->year && ($month === null || $month === now()->month)) {
            return $this->calculateRealTimeSummary($year, $month);
        }
        
        // For past periods: use pre-aggregated summaries
        if ($month) {
            $summary = MonthlyBusinessSummary::where('year', $year)
                ->where('month', $month)
                ->first();
        } else {
            $summary = YearlyBusinessSummary::where('year', $year)->first();
        }
        
        if (!$summary) {
            // Generate on-demand if missing
            return $this->generateMissingSummary($year, $month);
        }
        
        return $detailed ? $summary->toArray() : $summary->only([
            'total_revenue', 'total_expenses', 'net_profit',
            'total_users', 'new_users', 'total_transactions'
        ]);
    }
    
    /**
     * Get user history overview (for regular users)
     */
    public function getUserHistoryOverview(int $userId): array
    {
        $lifetimeStats = UserLifetimeStat::where('user_id', $userId)->first();
        
        if (!$lifetimeStats) {
            // Calculate on-demand
            dispatch(new UpdateUserLifetimeStats($userId));
            return $this->calculateBasicUserStats($userId);
        }
        
        return [
            'lifetime_spent' => $lifetimeStats->lifetime_spent,
            'lifetime_earned' => $lifetimeStats->lifetime_earned,
            'total_transactions' => $lifetimeStats->total_transactions,
            'member_since' => $lifetimeStats->first_transaction_date,
            'yearly_summary' => $lifetimeStats->yearly_summary,
        ];
    }
}
```

---

## AI-Ready Data Structure

### For Machine Learning Models

```php
// monthly_business_summaries.trends_data structure
{
    "revenue_trend": [/* 12 months rolling */],
    "user_growth_trend": [/* 12 months rolling */],
    "churn_trend": [/* 12 months rolling */],
    "seasonality_index": {
        "jan": 0.85, "feb": 0.90, /* ... */
    },
    "features": {
        "avg_transaction_value": 2500,
        "transactions_per_user": 3.5,
        "commission_ratio": 0.15,
        "retention_rate": 0.78
    }
}

// user_lifetime_stats.behavior_flags structure
{
    "is_high_value": true,
    "churn_risk": "low",
    "engagement_score": 85,
    "referral_potential": "high",
    "purchase_frequency": "monthly",
    "preferred_categories": ["subscription", "products"],
    "last_activity_days": 5
}
```

### AI Integration Points

```php
<?php

namespace App\Services\AI;

interface BusinessIntelligenceInterface
{
    // Revenue forecasting
    public function forecastRevenue(int $monthsAhead = 3): array;
    
    // Churn prediction
    public function predictChurn(int $userId): float;
    
    // User segmentation
    public function segmentUsers(): Collection;
    
    // Anomaly detection
    public function detectAnomalies(string $period): array;
    
    // Recommendations
    public function getBusinessRecommendations(): array;
    
    // Report generation
    public function generateReport(string $type, array $params): string;
}
```

---

## Scheduler Configuration

```php
// routes/console.php or app/Console/Kernel.php

// Daily: Archive old transactions (runs at 2 AM)
Schedule::job(new ArchiveOldTransactions())->dailyAt('02:00');
Schedule::job(new ArchiveOldCommissions())->dailyAt('02:30');

// Daily: Generate business snapshot for yesterday
Schedule::job(new GenerateDailySnapshot(now()->subDay()))->dailyAt('00:30');

// Monthly: Generate monthly summary (1st of month)
Schedule::job(new GenerateMonthlyBusinessSummary(now()->subMonth()->format('Y-m')))
    ->monthlyOn(1, '03:00');

// Monthly: Update all user lifetime stats (batch job)
Schedule::job(new BatchUpdateUserLifetimeStats())->monthlyOn(2, '04:00');

// Yearly: Generate yearly summary (Jan 2nd)
Schedule::job(new GenerateYearlyBusinessSummary(now()->subYear()->year))
    ->yearlyOn(1, 2, '05:00');

// Weekly: AI anomaly detection
Schedule::job(new DetectBusinessAnomalies())->weeklyOn(1, '06:00');
```

---

## Access Control Matrix

| Data Type | User | Admin (Exec) | Admin (Lead+) | Admin (Dir+) | SuperAdmin |
|-----------|------|--------------|---------------|--------------|------------|
| Own transactions (1yr) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Own transactions (3yr) | Premium | ✅ | ✅ | ✅ | ✅ |
| Own lifetime summary | ✅ | ✅ | ✅ | ✅ | ✅ |
| Other user's data | ❌ | ❌ | ✅ | ✅ | ✅ |
| Archive queries | ❌ | ❌ | ✅ | ✅ | ✅ |
| Business summaries | ❌ | ❌ | ❌ | ✅ | ✅ |
| Raw archive access | ❌ | ❌ | ❌ | ❌ | ✅ |
| AI reports | ❌ | ❌ | ❌ | ✅ | ✅ |
| Full data export | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## Storage Estimation

### At 10 Crore (100M) Users

| Table | Records/Year | Size/Year | 10 Years |
|-------|--------------|-----------|----------|
| transactions (hot) | 50M | ~15 GB | 15 GB (1yr only) |
| transactions_archive | 50M/yr | ~12 GB/yr | 120 GB |
| commissions (hot) | 20M | ~8 GB | 8 GB (1yr only) |
| commissions_archive | 20M/yr | ~6 GB/yr | 60 GB |
| monthly_summaries | 120 | ~50 MB | 50 MB |
| yearly_summaries | 10 | ~5 MB | 5 MB |
| user_lifetime_stats | 100M | ~20 GB | 20 GB |

**Total Hot Data**: ~25 GB (fast queries)
**Total Archive**: ~200 GB (10 years, indexed)
**Total Summaries**: ~20 GB (instant AI queries)

---

## Implementation Phases

### Phase 1: Foundation (Week 1)
1. Create archive table migrations
2. Create summary table migrations
3. Create Archive models
4. Create HistoryQueryService

### Phase 2: Archival Jobs (Week 2)
1. ArchiveOldTransactions job
2. ArchiveOldCommissions job
3. GenerateMonthlyBusinessSummary job
4. GenerateYearlyBusinessSummary job
5. UpdateUserLifetimeStats job
6. Configure scheduler

### Phase 3: Query Layer (Week 3)
1. Unified query service (hot + archive)
2. Admin Filament pages for archive access
3. User history API endpoints
4. Export functionality

### Phase 4: AI Preparation (Week 4)
1. Trends data structure population
2. Anomaly detection job
3. AI interface stubs
4. Report generation templates

---

## Final Summary

### New Tables: 7

| Table | Purpose | Size |
|-------|---------|------|
| `admins` | Admin hierarchy | Small |
| `admin_profit_distributions` | Profit share audit | Medium |
| `transactions_archive` | Archived transactions | Large |
| `commissions_archive` | Archived commissions | Large |
| `monthly_business_summaries` | Monthly aggregates | Small |
| `yearly_business_summaries` | Yearly aggregates | Tiny |
| `user_lifetime_stats` | Per-user summaries | Medium |

### Benefits

1. **Performance**: Hot tables stay small (1 year)
2. **Scalability**: Archive tables partitioned by year
3. **Cost**: Cold data can move to cheaper storage
4. **AI-Ready**: Pre-aggregated data for ML
5. **Compliance**: Full audit trail maintained
6. **User Experience**: Fast queries for recent data
7. **Admin Power**: Access to 10+ years of history

---

Ready to implement when you approve!
