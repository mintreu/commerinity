# Affiliate Matrix 5×4 System - Focused Architecture Plan

## System Overview

**Matrix Type:** 5-width × 4-depth (5^n where n = level 1-4)

```
                         [USER]
                            │
         ┌──────┬──────┬────┴────┬──────┬──────┐
       [D1]   [D2]   [D3]      [D4]   [D5]        ← Level 1: 5 direct (5^1)
         │      │      │         │      │
       [×5]  [×5]   [×5]      [×5]   [×5]         ← Level 2: 25 total (5^2)
         │      │      │         │      │
       [×5]  [×5]   [×5]      [×5]   [×5]         ← Level 3: 125 total (5^3)
         │      │      │         │      │
       [×5]  [×5]   [×5]      [×5]   [×5]         ← Level 4: 625 total (5^4)

Total Team Capacity per Stage: 5 + 25 + 125 + 625 = 780 members
```

---

## Part 1: Core Tables (3 Enhanced + 2 New)

### 1.1 Enhanced Tables

| Table | Status | Changes Needed |
|-------|--------|----------------|
| `stages` | Enhance | Add bonus configs, pool settings |
| `levels` | Enhance | Add qualification criteria, level bonuses |
| `user_subscriptions` | Enhance | Add level tracking, qualification data |

### 1.2 New Tables

| Table | Purpose |
|-------|---------|
| `affiliate_genealogy` | Tree structure, sponsor paths, team counts |
| `affiliate_commissions` | Commission ledger with full audit trail |

---

## Part 2: Database Schema

### 2.1 `stages` - Enhanced Stage Table

```php
// Current columns to KEEP:
// id, uuid, name, slug, description, base_price, discount, tax_percentage,
// tax_amount, price, max_team_members, commission_rates, benefits,
// accessibility, sort_order, is_active, is_default, timestamps, soft_deletes

// NEW columns to ADD via migration:
Schema::table('stages', function (Blueprint $table) {
    // Matrix Configuration
    $table->unsignedInteger('matrix_width')->default(5);          // Children per user
    $table->unsignedInteger('matrix_depth')->default(4);          // Levels per stage

    // Sponsor Bonus (one-time on direct recruitment)
    $table->json('sponsor_bonus')->nullable();
    // {"type": "percent", "value": 20} or {"type": "fixed", "value": 50000}

    // Level-wise commission rates (override if different from commission_rates)
    // {"1": 10, "2": 5, "3": 3, "4": 2} = % at each depth level
    // Already have commission_rates JSON - will use that

    // Matching Bonus (% of direct downline's earnings)
    $table->decimal('matching_bonus_percent', 5, 2)->default(0);
    $table->unsignedInteger('matching_bonus_levels')->default(1); // How many gen deep

    // Pool Contribution (% of each subscription goes to pool)
    $table->decimal('pool_contribution_percent', 5, 2)->default(0);

    // Rank Bonus (one-time bonus when user reaches each level)
    $table->json('level_achievement_bonus')->nullable();
    // {"1": 0, "2": 10000, "3": 50000, "4": 100000} = paisa bonus per level

    // Upgrade Path
    $table->foreignId('upgrade_to_stage_id')->nullable()
        ->constrained('stages')->nullOnDelete();
    $table->unsignedBigInteger('upgrade_price_difference')->default(0);

    // Point Values (for qualification tracking)
    $table->unsignedInteger('pv')->default(0);  // Point Value
    $table->unsignedInteger('bv')->default(0);  // Business Value
});
```

**Calculated Fields:**
- `max_team_members` = 5^1 + 5^2 + 5^3 + 5^4 = 780 (auto-calculated from matrix_width × matrix_depth)

### 2.2 `levels` - Enhanced Level Table

```php
// Current columns to KEEP:
// id, uuid, stage_id, name, slug, description, team_member_limit,
// validity_days, joining_bonus, purchase_commission, recruitment_commission,
// depth_commissions, sort_order, is_active, timestamps, soft_deletes

// NEW columns to ADD via migration:
Schema::table('levels', function (Blueprint $table) {
    // ========================================
    // UNIQUE IDENTIFICATION (Option 2 + 3)
    // ========================================

    // Full display name - unique across ALL stages
    $table->string('full_name')->unique();  // "Premium Gold", "Elite Diamond"

    // Global rank number - unique across ALL stages (1-16 for 4 stages × 4 levels)
    $table->unsignedInteger('global_rank')->unique();  // 1, 2, 3... 16

    // Level number within stage (1, 2, 3, 4)
    $table->unsignedInteger('level_number')->default(1);

    // ========================================
    // QUALIFICATION REQUIREMENTS
    // ========================================

    // Team requirements for this level
    $table->unsignedInteger('min_direct_referrals')->default(0);   // Direct recruits needed
    $table->unsignedInteger('min_active_directs')->default(0);     // Must be active
    $table->unsignedBigInteger('min_personal_purchase')->default(0); // Personal buy (paisa)
    $table->unsignedBigInteger('min_team_sales')->default(0);      // Team volume (paisa)

    // Cumulative team limit at this level (5, 30, 155, 780)
    // team_member_limit already exists - will use for 5^level value

    // ========================================
    // COMMISSION & BENEFITS
    // ========================================

    // Commission multiplier at this level
    $table->decimal('commission_multiplier', 5, 2)->default(1.00); // 1.0 = 100%

    // Level-specific benefits
    $table->json('level_benefits')->nullable();
    // {"badge": "silver", "dashboard_theme": "premium", "support_priority": "high"}

    // Achievement bonus (one-time when reaching this level)
    // joining_bonus already exists - will use for level achievement bonus

    // Rank badge
    $table->string('badge_icon')->nullable();
    $table->string('badge_color')->nullable();
});

// Add unique constraint
$table->unique(['stage_id', 'level_number']);
```

**Level Data Example (All Stages with Global Ranks):**

| Stage | Level | full_name | global_rank | level_number | team_member_limit |
|-------|-------|-----------|-------------|--------------|-------------------|
| Starter | Bronze | Starter Bronze | 1 | 1 | 5 |
| Starter | Silver | Starter Silver | 2 | 2 | 25 |
| Starter | Gold | Starter Gold | 3 | 3 | 125 |
| Starter | Diamond | Starter Diamond | 4 | 4 | 625 |
| Premium | Bronze | Premium Bronze | 5 | 1 | 5 |
| Premium | Silver | Premium Silver | 6 | 2 | 25 |
| Premium | Gold | Premium Gold | 7 | 3 | 125 |
| Premium | Diamond | Premium Diamond | 8 | 4 | 625 |
| Elite | Bronze | Elite Bronze | 9 | 1 | 5 |
| Elite | Silver | Elite Silver | 10 | 2 | 25 |
| Elite | Gold | Elite Gold | 11 | 3 | 125 |
| Elite | Diamond | Elite Diamond | 12 | 4 | 625 |
| Royal | Bronze | Royal Bronze | 13 | 1 | 5 |
| Royal | Silver | Royal Silver | 14 | 2 | 25 |
| Royal | Gold | Royal Gold | 15 | 3 | 125 |
| Royal | Diamond | Royal Diamond | 16 | 4 | 625 |

**Per Stage Capacity:** 5 + 25 + 125 + 625 = 780 members

**Benefits of this structure:**
- `full_name`: Human-readable unique identifier ("Premium Gold")
- `global_rank`: Easy numeric comparison (Rank 8 > Rank 5 means higher achievement)
- `level_number`: Position within stage (always 1-4)
- Sorting by `global_rank` gives proper hierarchy across entire system

### 2.3 `user_subscriptions` - Enhanced User Subscription

```php
// Current columns - CHECK existing migration first
// If not exists or needs enhancement:

Schema::table('user_subscriptions', function (Blueprint $table) {
    // Current Level Progress
    $table->foreignId('current_level_id')->nullable()
        ->constrained('levels')->nullOnDelete();
    $table->timestamp('level_achieved_at')->nullable();

    // Highest level ever achieved (for history)
    $table->foreignId('highest_level_id')->nullable()
        ->constrained('levels')->nullOnDelete();

    // Qualification snapshot (when level was achieved)
    $table->json('qualification_snapshot')->nullable();
    // {"direct_count": 5, "team_count": 30, "team_sales": 500000}

    // Points accumulated
    $table->unsignedInteger('personal_pv')->default(0);
    $table->unsignedInteger('team_pv')->default(0);

    // Commission tracking
    $table->unsignedBigInteger('total_commission_earned')->default(0);  // Lifetime (paisa)
    $table->unsignedBigInteger('current_month_commission')->default(0); // This month
});
```

### 2.4 `affiliate_genealogy` - NEW: Network Tree Structure

```php
Schema::create('affiliate_genealogy', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    // User reference
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Sponsor (who referred this user)
    $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();

    // Placement (where placed in tree - can differ from sponsor for spillover)
    $table->foreignId('placement_parent_id')->nullable()->constrained('users')->nullOnDelete();
    $table->unsignedInteger('placement_position')->default(1); // 1-5 (which child slot)

    // Materialized Path for fast tree queries
    // Format: "/1/5/23/89/" where numbers are user_ids
    $table->string('sponsor_path', 2000)->nullable();
    $table->string('placement_path', 2000)->nullable();

    // Depth from root
    $table->unsignedInteger('depth')->default(0);  // 0 = root, 1 = level 1, etc.

    // ========================================
    // COUNTERS (Updated by triggers/events)
    // ========================================

    // Direct children (immediate referrals)
    $table->unsignedInteger('direct_count')->default(0);        // Max 5 in our system
    $table->unsignedInteger('active_direct_count')->default(0); // Active ones

    // Team counts by level
    $table->unsignedInteger('level_1_count')->default(0);  // Direct: max 5
    $table->unsignedInteger('level_2_count')->default(0);  // Depth 2: max 25
    $table->unsignedInteger('level_3_count')->default(0);  // Depth 3: max 125
    $table->unsignedInteger('level_4_count')->default(0);  // Depth 4: max 625

    // Total team
    $table->unsignedInteger('total_team_count')->default(0);    // Sum of all levels
    $table->unsignedInteger('active_team_count')->default(0);   // Active members only

    // Volume tracking (in paisa)
    $table->unsignedBigInteger('personal_sales')->default(0);   // Own purchases
    $table->unsignedBigInteger('level_1_sales')->default(0);    // Level 1 team sales
    $table->unsignedBigInteger('level_2_sales')->default(0);    // Level 2 team sales
    $table->unsignedBigInteger('level_3_sales')->default(0);    // Level 3 team sales
    $table->unsignedBigInteger('level_4_sales')->default(0);    // Level 4 team sales
    $table->unsignedBigInteger('total_team_sales')->default(0); // All team sales

    // Points
    $table->unsignedInteger('personal_pv')->default(0);
    $table->unsignedInteger('team_pv')->default(0);

    // Status
    $table->boolean('is_active')->default(true);
    $table->timestamp('activated_at')->nullable();
    $table->timestamp('last_activity_at')->nullable();

    // Current qualification
    $table->foreignId('current_stage_id')->nullable()->constrained('stages')->nullOnDelete();
    $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();

    $table->timestamps();
    $table->softDeletes();

    // Indexes for fast queries
    $table->unique('user_id');
    $table->index('sponsor_id');
    $table->index('placement_parent_id');
    $table->index('sponsor_path');
    $table->index('placement_path');
    $table->index(['depth', 'is_active']);
    $table->index('current_stage_id');
});
```

### 2.5 `affiliate_commissions` - NEW: Commission Ledger

```php
Schema::create('affiliate_commissions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    // Who receives the commission
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Source of commission (who triggered it)
    $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();

    // What triggered the commission
    $table->nullableMorphs('commissionable'); // UserSubscription, Order, Transaction, etc.

    // Commission Type
    $table->enum('type', [
        'sponsor_bonus',        // Direct referral bonus
        'level_commission',     // Level-based commission (depth 1-4)
        'matching_bonus',       // % of downline's earnings
        'level_achievement',    // Bonus for reaching a level
        'pool_bonus',           // Global pool distribution
        'purchase_commission',  // Commission on product purchase
        'other'                 // Manual/adjustment
    ])->default('level_commission');

    // Level info (for level_commission type)
    $table->unsignedInteger('level')->nullable();  // 1, 2, 3, or 4
    $table->decimal('rate_percent', 5, 2)->default(0);  // Rate applied

    // Amounts (all in paisa)
    $table->unsignedBigInteger('base_amount')->default(0);    // Amount commission calculated on
    $table->unsignedBigInteger('gross_amount')->default(0);   // Before deductions
    $table->unsignedBigInteger('tds_amount')->default(0);     // TDS deducted (if applicable)
    $table->unsignedBigInteger('admin_fee')->default(0);      // Platform fee
    $table->unsignedBigInteger('net_amount')->default(0);     // Final amount

    // Status
    $table->enum('status', [
        'pending',      // Calculated, awaiting approval
        'approved',     // Approved, ready for payout
        'processing',   // Payout in progress
        'paid',         // Credited to wallet
        'held',         // On hold (compliance, etc.)
        'cancelled',    // Cancelled
        'reversed'      // Clawed back
    ])->default('pending');

    // Payout tracking
    $table->foreignId('paid_via_transaction_id')->nullable()
        ->constrained('transactions')->nullOnDelete();
    $table->timestamp('paid_at')->nullable();

    // Period tracking
    $table->date('commission_date');  // Date commission is for
    $table->string('period_key')->nullable();  // "2025-01", "2025-W02" for grouping

    // Audit
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index(['user_id', 'type']);
    $table->index(['from_user_id']);
    $table->index(['commission_date', 'status']);
    $table->index('period_key');
    $table->index(['commissionable_type', 'commissionable_id']);
});
```

---

## Part 3: Commission Calculation Logic

### 3.1 Commission Flow

```
User B subscribes to Stage (pays ₹5,000)
           │
           ▼
┌──────────────────────────────────────────────────────┐
│  1. SPONSOR BONUS                                     │
│     User A (sponsor) gets 20% = ₹1,000               │
└──────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────────┐
│  2. LEVEL COMMISSIONS (upline chain)                  │
│     Level 1 (A): 10% = ₹500                          │
│     Level 2 (A's sponsor): 5% = ₹250                 │
│     Level 3: 3% = ₹150                               │
│     Level 4: 2% = ₹100                               │
└──────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────────┐
│  3. MATCHING BONUS (if enabled)                       │
│     A's sponsor gets 10% of A's commission           │
└──────────────────────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────────────────────┐
│  4. UPDATE COUNTERS                                   │
│     - Increment team counts for all uplines          │
│     - Update sales volumes                           │
│     - Check level qualifications                     │
└──────────────────────────────────────────────────────┘
```

### 3.2 Level Qualification Check

```php
// When team count changes, check if user qualifies for next level
function checkLevelQualification(User $user): ?Level
{
    $genealogy = $user->genealogy;
    $currentLevel = $genealogy->current_level_id;
    $stage = $genealogy->current_stage;

    $nextLevel = Level::where('stage_id', $stage->id)
        ->where('level_number', '>', $currentLevel?->level_number ?? 0)
        ->orderBy('level_number')
        ->first();

    if (!$nextLevel) return null; // Already at max level

    // Check qualification criteria
    $qualified = true;

    // Check direct referrals
    if ($genealogy->direct_count < $nextLevel->min_direct_referrals) {
        $qualified = false;
    }

    // Check team member limit (cumulative for this level)
    $teamAtLevel = match($nextLevel->level_number) {
        1 => $genealogy->level_1_count,
        2 => $genealogy->level_1_count + $genealogy->level_2_count,
        3 => $genealogy->level_1_count + $genealogy->level_2_count + $genealogy->level_3_count,
        4 => $genealogy->total_team_count,
    };

    // Team limit is the 5^n value, user needs to have filled previous level
    // Level 2 requires 5 directs (level 1 full)
    // Level 3 requires 25+ team (level 2 capacity)
    // Level 4 requires 125+ team (level 3 capacity)

    if ($qualified) {
        // Promote user
        $genealogy->current_level_id = $nextLevel->id;
        $genealogy->save();

        // Award level achievement bonus
        if ($nextLevel->joining_bonus > 0) {
            $this->awardLevelBonus($user, $nextLevel);
        }

        return $nextLevel;
    }

    return null;
}
```

---

## Part 4: Service Architecture

```
app/Services/Mlm/
├── MlmService.php              # Main coordinator
├── GenealogyService.php        # Tree management
│   ├── placeMember()
│   ├── getUpline()
│   ├── getDownline()
│   ├── updateCounters()
│   └── findAvailablePosition()
│
├── CommissionService.php       # Commission calculations
│   ├── processSubscriptionCommissions()
│   ├── calculateSponsorBonus()
│   ├── calculateLevelCommissions()
│   ├── calculateMatchingBonus()
│   └── creditToWallet()
│
├── LevelService.php            # Level progression
│   ├── checkQualification()
│   ├── promoteToLevel()
│   └── getQualificationProgress()
│
└── Events/
    ├── MemberJoined.php
    ├── SubscriptionPurchased.php
    ├── LevelAchieved.php
    └── CommissionEarned.php
```

---

## Part 5: Migration Strategy

### 5.1 Migrations to Create

```
1. YYYY_MM_DD_000001_add_affiliate_columns_to_stages_table.php
2. YYYY_MM_DD_000002_add_affiliate_columns_to_levels_table.php
3. YYYY_MM_DD_000003_add_affiliate_columns_to_user_subscriptions_table.php
4. YYYY_MM_DD_000004_create_affiliate_genealogy_table.php
5. YYYY_MM_DD_000005_create_affiliate_commissions_table.php
```

### 5.2 Seeders

```php
// Default Stage with 4 Levels
Stage::create([
    'name' => 'Premium Membership',
    'slug' => 'premium',
    'base_price' => 500000,  // ₹5,000 in paisa
    'matrix_width' => 5,
    'matrix_depth' => 4,
    'max_team_members' => 780,  // 5+25+125+625
    'commission_rates' => [
        '1' => 10,  // 10% at level 1
        '2' => 5,   // 5% at level 2
        '3' => 3,   // 3% at level 3
        '4' => 2,   // 2% at level 4
    ],
    'sponsor_bonus' => ['type' => 'percent', 'value' => 20],
    'level_achievement_bonus' => [
        '1' => 0,
        '2' => 10000,   // ₹100
        '3' => 50000,   // ₹500
        '4' => 100000,  // ₹1,000
    ],
]);

// 4 Levels for the Stage
$levels = [
    ['level_number' => 1, 'name' => 'Bronze', 'team_member_limit' => 5],
    ['level_number' => 2, 'name' => 'Silver', 'team_member_limit' => 25],
    ['level_number' => 3, 'name' => 'Gold', 'team_member_limit' => 125],
    ['level_number' => 4, 'name' => 'Diamond', 'team_member_limit' => 625],
];
```

---

## Part 6: Summary

### Tables Count: 5 (3 enhanced + 2 new)

| Table | Type | Purpose |
|-------|------|---------|
| `stages` | Enhanced | Stage = Plan + Package (pricing, commissions, matrix config) |
| `levels` | Enhanced | 4 levels per stage (5^n team limits, bonuses) |
| `user_subscriptions` | Enhanced | User's active stage + level progress |
| `affiliate_genealogy` | **NEW** | Tree structure, counters, paths |
| `affiliate_commissions` | **NEW** | Commission ledger with audit trail |

### Commission Types: 5

1. **Sponsor Bonus** - One-time on direct referral
2. **Level Commission** - % based on depth (1-4)
3. **Matching Bonus** - % of downline's earnings
4. **Level Achievement** - Bonus for reaching level 2/3/4
5. **Purchase Commission** - On product purchases (future)

### Key Formulas

```
Matrix Width = 5
Matrix Depth = 4
Level N capacity = 5^N
Total Stage capacity = 5^1 + 5^2 + 5^3 + 5^4 = 780

Level 1: 5 members (direct)
Level 2: 25 members (5×5)
Level 3: 125 members (5×5×5)
Level 4: 625 members (5×5×5×5)
```

---

## Approval Checklist

- [ ] Approve enhanced `stages` columns
- [ ] Approve enhanced `levels` columns
- [ ] Approve enhanced `user_subscriptions` columns
- [ ] Approve new `affiliate_genealogy` table
- [ ] Approve new `affiliate_commissions` table
- [ ] Confirm commission types needed
- [ ] Confirm TDS handling (10% on >₹5,000/month?)

**Ready to implement upon approval.**
