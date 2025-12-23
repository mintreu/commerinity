# Enterprise MLM & Membership System - Complete Architecture Plan

## Executive Summary

This plan outlines an **industry-standard, enterprise-grade MLM system** designed to compete with top MLM software providers like Infinite MLM, HybridMLM, and Prime MLM Software. The system supports multiple compensation plan types with a flexible, configurable architecture.

**Sources Referenced:**
- [HybridMLM - MLM Compensation Plans Compared](https://www.hybridmlm.io/blogs/mlm-compensation-plans-compared-binary-matrix-and-unilevel-explained/)
- [Infinite MLM Software](https://infinitemlmsoftware.com/)
- [Prime MLM Software](https://primemlmsoftware.com/board-mlm-plan/)

---

## Part 1: Industry Analysis & Plan Types

### 1.1 MLM Compensation Plan Types (Industry Standard)

| Plan Type | Structure | Width | Depth | Best For |
|-----------|-----------|-------|-------|----------|
| **Unilevel** | Unlimited frontline, fixed depth levels | Unlimited | 5-10 levels | Startups, simplicity |
| **Binary** | Two legs (left/right), power leg concept | 2 | Unlimited | Fast scaling |
| **Matrix** | Fixed width x depth (3x3, 5x5, 5x7) | Fixed | Fixed | Predictability |
| **Board/Cycle** | Members cycle through boards | Board-based | Board-based | Gamification |
| **Hybrid** | Combines multiple plans | Configurable | Configurable | Complex needs |

### 1.2 Industry-Standard Bonus Types

1. **Direct/Sponsor Bonus** - One-time bonus for direct recruitment
2. **Level/Generation Bonus** - Commission based on levels deep
3. **Matching Bonus** - % of downline's earnings
4. **Pool/Global Bonus** - Share from company's global pool
5. **Rank Achievement Bonus** - One-time bonus on rank up
6. **Leadership Bonus** - Extra % for leaders
7. **Fast Start Bonus** - Time-limited recruitment bonus
8. **Retail Profit** - Markup on product sales
9. **Binary Pairing Bonus** - For binary leg balancing
10. **Cycle/Board Bonus** - For matrix cycling

### 1.3 Our Implementation: **Configurable Hybrid System**

We'll build a **plan-agnostic system** where:
- Plan type is configurable per stage
- Bonus types are modular and can be enabled/disabled
- Commission rates are stored in JSON for flexibility
- Ranks/Levels have clear qualification criteria

---

## Part 2: Database Schema Refactoring

### 2.1 Core Tables Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        MLM SYSTEM ARCHITECTURE                          │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────────┐        │
│  │    PLANS     │────▶│    RANKS     │────▶│  RANK_BONUSES    │        │
│  │ (mlm_plans)  │     │ (mlm_ranks)  │     │                  │        │
│  └──────────────┘     └──────────────┘     └──────────────────┘        │
│         │                    │                                          │
│         ▼                    ▼                                          │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────────┐        │
│  │  PACKAGES    │────▶│    USERS     │────▶│  USER_PACKAGES   │        │
│  │(mlm_packages)│     │  (members)   │     │                  │        │
│  └──────────────┘     └──────────────┘     └──────────────────┘        │
│                              │                                          │
│                              ▼                                          │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────────┐        │
│  │   NETWORK    │◀────│  GENEALOGY   │────▶│  COMMISSIONS     │        │
│  │ (mlm_network)│     │(mlm_genealogy│     │(mlm_commissions) │        │
│  └──────────────┘     └──────────────┘     └──────────────────┘        │
│                                                                         │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────────┐        │
│  │ BONUS_TYPES  │────▶│ BONUS_CONFIG │────▶│  BONUS_PAYOUTS   │        │
│  │              │     │              │     │                  │        │
│  └──────────────┘     └──────────────┘     └──────────────────┘        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Detailed Table Schemas

#### 2.2.1 `mlm_plans` - Compensation Plan Configuration

```php
Schema::create('mlm_plans', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    // Basic Info
    $table->string('name');                           // "Matrix 5x5", "Binary Pro"
    $table->string('slug')->unique();
    $table->text('description')->nullable();

    // Plan Type (determines calculation logic)
    $table->enum('type', [
        'unilevel',     // Unlimited width, fixed depth
        'binary',       // 2 legs, spillover
        'matrix',       // Fixed width x depth
        'board',        // Cycling boards
        'hybrid'        // Combination
    ])->default('matrix');

    // Structure Configuration
    $table->unsignedInteger('max_width')->default(5);         // Max direct referrals (0 = unlimited)
    $table->unsignedInteger('max_depth')->default(5);         // Max commission levels
    $table->boolean('has_spillover')->default(false);         // Auto-place overflow
    $table->enum('spillover_strategy', [
        'left_first',       // Fill left leg first
        'right_first',      // Fill right leg first
        'weak_leg',         // Fill weaker leg
        'alternate',        // Alternate legs
        'fifo'              // First available position
    ])->nullable();

    // For Binary Plans
    $table->enum('binary_pairing_type', [
        'weak_leg',         // Pay on weak leg volume
        'strong_leg',       // Pay on strong leg volume
        'lesser_leg',       // Pay on lesser of two
        'both_legs'         // Pay on both
    ])->nullable();
    $table->unsignedBigInteger('binary_cap_daily')->default(0);   // Daily cap in paisa
    $table->unsignedBigInteger('binary_cap_weekly')->default(0);

    // Enabled Bonus Types (JSON array of bonus_type slugs)
    $table->json('enabled_bonuses')->nullable();

    // Commission Structure (JSON)
    // {"level_1": 10, "level_2": 5, "level_3": 3, "level_4": 2, "level_5": 1}
    $table->json('level_commissions')->nullable();

    // Global Pool Settings
    $table->decimal('pool_contribution_percent', 5, 2)->default(0);  // % to global pool

    // Status
    $table->boolean('is_active')->default(true);
    $table->boolean('is_default')->default(false);

    $table->timestamps();
    $table->softDeletes();
});
```

#### 2.2.2 `mlm_ranks` - Rank/Level Definitions

```php
Schema::create('mlm_ranks', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('plan_id')->constrained('mlm_plans')->cascadeOnDelete();

    // Basic Info
    $table->string('name');                           // "Bronze", "Silver", "Gold", "Diamond"
    $table->string('slug');
    $table->string('badge_icon')->nullable();         // Icon path/class
    $table->string('badge_color')->nullable();        // Hex color
    $table->text('description')->nullable();

    // Rank Order (higher = better)
    $table->unsignedInteger('level')->default(1);     // 1, 2, 3, 4, 5...
    $table->unsignedInteger('sort_order')->default(0);

    // ============================================
    // QUALIFICATION CRITERIA (must meet ALL)
    // ============================================

    // Personal Requirements
    $table->unsignedBigInteger('min_personal_sales')->default(0);      // Min personal purchase (paisa)
    $table->unsignedBigInteger('min_personal_pv')->default(0);         // Min personal PV (points)

    // Team Requirements
    $table->unsignedInteger('min_direct_referrals')->default(0);       // Direct recruits needed
    $table->unsignedInteger('min_active_directs')->default(0);         // Active direct members
    $table->unsignedInteger('min_team_size')->default(0);              // Total team size
    $table->unsignedBigInteger('min_team_sales')->default(0);          // Team sales volume (paisa)
    $table->unsignedBigInteger('min_team_pv')->default(0);             // Team PV

    // Leg Requirements (for binary/matrix)
    $table->json('leg_requirements')->nullable();
    // {"left_leg_volume": 50000, "right_leg_volume": 50000, "min_per_leg": 2}

    // Rank Requirements (must have X members at Y rank)
    $table->json('rank_requirements')->nullable();
    // [{"rank_id": 2, "count": 3}] = Need 3 members at rank 2

    // Time-based Requirements
    $table->unsignedInteger('qualification_period_days')->default(30);  // Days to qualify
    $table->boolean('must_maintain_monthly')->default(false);           // Must re-qualify

    // ============================================
    // RANK BENEFITS
    // ============================================

    // Commission Multipliers
    $table->decimal('commission_multiplier', 5, 2)->default(1.00);     // 1.0 = 100%, 1.5 = 150%
    $table->decimal('matching_bonus_percent', 5, 2)->default(0);       // % of downline earnings
    $table->unsignedInteger('matching_bonus_levels')->default(0);      // How deep matching applies

    // Pool Share
    $table->decimal('pool_share_percent', 5, 2)->default(0);           // Share of global pool

    // Additional Benefits (JSON)
    $table->json('benefits')->nullable();
    // {"free_products": true, "event_access": "vip", "car_bonus": false}

    // Validity
    $table->unsignedInteger('validity_days')->default(365);

    $table->boolean('is_active')->default(true);

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['plan_id', 'slug']);
    $table->unique(['plan_id', 'level']);
});
```

#### 2.2.3 `mlm_packages` - Subscription/Joining Packages

```php
Schema::create('mlm_packages', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('plan_id')->constrained('mlm_plans')->cascadeOnDelete();
    $table->foreignId('entry_rank_id')->nullable()->constrained('mlm_ranks')->nullOnDelete();

    // Basic Info
    $table->string('name');                           // "Starter Pack", "Premium Pack"
    $table->string('slug');
    $table->text('description')->nullable();

    // Pricing (all in paisa)
    $table->unsignedBigInteger('base_price')->default(0);
    $table->unsignedBigInteger('discount')->default(0);
    $table->decimal('tax_percent', 5, 2)->default(18);
    $table->unsignedBigInteger('tax_amount')->default(0);        // Calculated
    $table->unsignedBigInteger('price')->default(0);             // Final price

    // Points Value
    $table->unsignedInteger('pv')->default(0);                   // Point Value
    $table->unsignedInteger('bv')->default(0);                   // Business Value
    $table->unsignedInteger('cv')->default(0);                   // Commission Value

    // Package Type
    $table->enum('type', [
        'joining',          // Initial joining package
        'upgrade',          // Upgrade existing membership
        'renewal',          // Renewal package
        'topup',            // Additional purchase
        'product_pack'      // Product-based package
    ])->default('joining');

    // Validity
    $table->unsignedInteger('validity_days')->default(365);

    // Included Products (JSON array of product_ids with quantities)
    $table->json('included_products')->nullable();
    // [{"product_id": 1, "quantity": 2}, {"product_id": 5, "quantity": 1}]

    // Commission Configuration for this package
    $table->json('sponsor_commission')->nullable();
    // {"type": "percent", "value": 20} or {"type": "fixed", "value": 10000}

    $table->json('level_commissions')->nullable();
    // {"1": 10, "2": 5, "3": 3} - Override plan-level commissions

    // Upgrade Path
    $table->foreignId('upgrade_from_package_id')->nullable()->constrained('mlm_packages')->nullOnDelete();
    $table->unsignedBigInteger('upgrade_price_difference')->default(0);

    // Limits
    $table->unsignedInteger('max_purchases_per_user')->default(1);  // 0 = unlimited
    $table->unsignedInteger('total_quantity_limit')->default(0);    // 0 = unlimited
    $table->unsignedInteger('quantity_sold')->default(0);

    // Status
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['plan_id', 'slug']);
});
```

#### 2.2.4 `mlm_bonus_types` - Configurable Bonus Types

```php
Schema::create('mlm_bonus_types', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    $table->string('name');                           // "Direct Sponsor Bonus"
    $table->string('slug')->unique();                 // "direct_sponsor_bonus"
    $table->text('description')->nullable();

    // Calculation Type
    $table->enum('calculation_type', [
        'percent_of_purchase',      // % of package price
        'percent_of_pv',            // % of PV
        'percent_of_bv',            // % of BV
        'percent_of_downline',      // % of downline earnings
        'fixed_amount',             // Fixed paisa amount
        'pool_share',               // Share of pool
        'tiered',                   // Based on tiers/levels
        'custom'                    // Custom calculation
    ])->default('percent_of_purchase');

    // Trigger Event
    $table->enum('trigger_event', [
        'on_join',                  // When member joins
        'on_purchase',              // On any purchase
        'on_rank_achieve',          // On rank achievement
        'on_renewal',               // On membership renewal
        'daily',                    // Daily calculation
        'weekly',                   // Weekly calculation
        'monthly',                  // Monthly calculation
        'on_team_purchase',         // When team member purchases
        'on_binary_pair',           // On binary leg pairing
        'on_matrix_cycle'           // On matrix board cycle
    ])->default('on_join');

    // Who Receives
    $table->enum('recipient', [
        'sponsor',                  // Direct sponsor
        'upline',                   // All uplines (level-based)
        'self',                     // The member themselves
        'qualified_ranks',          // Members with specific ranks
        'pool_participants'         // Pool share participants
    ])->default('sponsor');

    // Default Configuration (can be overridden per plan)
    $table->json('default_config')->nullable();
    // {"percent": 10, "max_levels": 5, "min_rank": 2}

    // Caps
    $table->unsignedBigInteger('daily_cap')->default(0);          // 0 = no cap
    $table->unsignedBigInteger('weekly_cap')->default(0);
    $table->unsignedBigInteger('monthly_cap')->default(0);
    $table->unsignedBigInteger('lifetime_cap')->default(0);

    // Requirements
    $table->json('requirements')->nullable();
    // {"min_rank": 1, "min_active_days": 30, "min_personal_pv": 100}

    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('sort_order')->default(0);

    $table->timestamps();
});
```

#### 2.2.5 `mlm_plan_bonus_configs` - Plan-specific Bonus Configuration

```php
Schema::create('mlm_plan_bonus_configs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plan_id')->constrained('mlm_plans')->cascadeOnDelete();
    $table->foreignId('bonus_type_id')->constrained('mlm_bonus_types')->cascadeOnDelete();

    // Override default config
    $table->json('config')->nullable();
    // {"percent": 15, "max_levels": 7, "level_rates": {"1": 10, "2": 8}}

    // Caps specific to this plan
    $table->unsignedBigInteger('daily_cap')->default(0);
    $table->unsignedBigInteger('weekly_cap')->default(0);
    $table->unsignedBigInteger('monthly_cap')->default(0);

    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('sort_order')->default(0);

    $table->timestamps();

    $table->unique(['plan_id', 'bonus_type_id']);
});
```

#### 2.2.6 `mlm_genealogy` - Network Tree Structure

```php
Schema::create('mlm_genealogy', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('plan_id')->constrained('mlm_plans')->cascadeOnDelete();

    // Sponsor Tree (who recruited whom)
    $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();

    // Placement Tree (where placed in structure - for binary/matrix)
    $table->foreignId('placement_id')->nullable()->constrained('users')->nullOnDelete();
    $table->enum('placement_position', ['left', 'right', 'center'])->nullable();

    // Hierarchical Path (for fast queries)
    // Materialized path: "/1/5/23/current_id/"
    $table->string('sponsor_path', 1000)->nullable();
    $table->string('placement_path', 1000)->nullable();

    // Depth in tree
    $table->unsignedInteger('sponsor_depth')->default(0);       // Level from root
    $table->unsignedInteger('placement_depth')->default(0);

    // Current Rank
    $table->foreignId('current_rank_id')->nullable()->constrained('mlm_ranks')->nullOnDelete();
    $table->timestamp('rank_achieved_at')->nullable();
    $table->foreignId('highest_rank_id')->nullable()->constrained('mlm_ranks')->nullOnDelete();

    // Volume Tracking (updated periodically)
    $table->unsignedBigInteger('personal_pv')->default(0);      // Personal PV
    $table->unsignedBigInteger('personal_sales')->default(0);   // Personal sales (paisa)
    $table->unsignedBigInteger('team_pv')->default(0);          // Total team PV
    $table->unsignedBigInteger('team_sales')->default(0);       // Team sales (paisa)
    $table->unsignedBigInteger('left_leg_pv')->default(0);      // For binary
    $table->unsignedBigInteger('right_leg_pv')->default(0);
    $table->unsignedBigInteger('carry_forward_pv')->default(0); // Unpaired PV

    // Team Counts
    $table->unsignedInteger('direct_count')->default(0);        // Direct referrals
    $table->unsignedInteger('team_count')->default(0);          // Total team
    $table->unsignedInteger('active_directs')->default(0);      // Active direct members
    $table->unsignedInteger('active_team')->default(0);         // Active team members

    // Status
    $table->boolean('is_active')->default(true);
    $table->timestamp('activated_at')->nullable();
    $table->timestamp('last_activity_at')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['user_id', 'plan_id']);
    $table->index(['sponsor_id', 'plan_id']);
    $table->index(['placement_id', 'plan_id']);
    $table->index('sponsor_path');
    $table->index('placement_path');
});
```

#### 2.2.7 `mlm_user_packages` - User Package Subscriptions

```php
Schema::create('mlm_user_packages', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('package_id')->constrained('mlm_packages')->cascadeOnDelete();
    $table->foreignId('genealogy_id')->constrained('mlm_genealogy')->cascadeOnDelete();

    // Transaction Reference
    $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();

    // Pricing at time of purchase (snapshot)
    $table->unsignedBigInteger('base_price')->default(0);
    $table->unsignedBigInteger('discount')->default(0);
    $table->unsignedBigInteger('tax_amount')->default(0);
    $table->unsignedBigInteger('price_paid')->default(0);

    // Points awarded
    $table->unsignedInteger('pv_awarded')->default(0);
    $table->unsignedInteger('bv_awarded')->default(0);

    // Status
    $table->enum('status', [
        'pending',          // Awaiting payment
        'active',           // Currently active
        'expired',          // Validity ended
        'cancelled',        // Cancelled by user/admin
        'upgraded',         // Upgraded to higher package
        'renewed'           // Renewed
    ])->default('pending');

    // Validity
    $table->timestamp('activated_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('renewed_at')->nullable();

    // Payment
    $table->boolean('is_paid')->default(false);
    $table->timestamp('paid_at')->nullable();
    $table->string('payment_method')->nullable();

    // Upgrade tracking
    $table->foreignId('upgraded_from_id')->nullable()->constrained('mlm_user_packages')->nullOnDelete();
    $table->foreignId('upgraded_to_id')->nullable()->constrained('mlm_user_packages')->nullOnDelete();

    $table->json('metadata')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['user_id', 'status']);
    $table->index(['expires_at', 'status']);
});
```

#### 2.2.8 `mlm_commissions` - Commission Ledger

```php
Schema::create('mlm_commissions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    // Who receives
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('genealogy_id')->constrained('mlm_genealogy')->cascadeOnDelete();

    // Source of commission
    $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('from_package_id')->nullable()->constrained('mlm_user_packages')->nullOnDelete();
    $table->foreignId('from_transaction_id')->nullable()->constrained()->nullOnDelete();

    // Bonus Type
    $table->foreignId('bonus_type_id')->constrained('mlm_bonus_types')->cascadeOnDelete();

    // Amount
    $table->unsignedBigInteger('gross_amount')->default(0);      // Before deductions (paisa)
    $table->unsignedBigInteger('tds_amount')->default(0);        // TDS deducted
    $table->unsignedBigInteger('admin_fee')->default(0);         // Admin fee
    $table->unsignedBigInteger('net_amount')->default(0);        // Final amount

    // Commission Details
    $table->unsignedInteger('level')->default(0);                // At which level (1-5)
    $table->decimal('rate_applied', 5, 2)->default(0);           // % or multiplier used
    $table->unsignedBigInteger('base_value')->default(0);        // Value commission based on

    // Status
    $table->enum('status', [
        'pending',          // Calculated, not yet approved
        'approved',         // Approved, ready for payout
        'processing',       // Payout in progress
        'paid',             // Paid to wallet
        'held',             // On hold
        'cancelled',        // Cancelled
        'reversed'          // Reversed/clawed back
    ])->default('pending');

    // Payout Reference
    $table->foreignId('payout_batch_id')->nullable();
    $table->foreignId('wallet_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

    // Timestamps
    $table->timestamp('calculated_at')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('paid_at')->nullable();

    // Period tracking
    $table->date('commission_date')->nullable();                 // Date commission is for
    $table->string('period_type')->nullable();                   // daily, weekly, monthly
    $table->string('period_key')->nullable();                    // "2025-01", "2025-W01"

    $table->text('description')->nullable();
    $table->json('metadata')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['user_id', 'status']);
    $table->index(['user_id', 'bonus_type_id']);
    $table->index(['from_user_id', 'bonus_type_id']);
    $table->index(['commission_date', 'status']);
    $table->index('period_key');
});
```

#### 2.2.9 `mlm_rank_history` - Rank Achievement History

```php
Schema::create('mlm_rank_history', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('genealogy_id')->constrained('mlm_genealogy')->cascadeOnDelete();
    $table->foreignId('rank_id')->constrained('mlm_ranks')->cascadeOnDelete();
    $table->foreignId('previous_rank_id')->nullable()->constrained('mlm_ranks')->nullOnDelete();

    $table->enum('type', [
        'achieved',         // Rank achieved
        'maintained',       // Re-qualified for same rank
        'demoted',          // Failed to maintain
        'manually_set'      // Admin override
    ])->default('achieved');

    // Qualification snapshot
    $table->json('qualification_snapshot')->nullable();
    // {"personal_pv": 500, "team_pv": 5000, "direct_count": 5}

    // Bonus awarded for this achievement
    $table->unsignedBigInteger('bonus_awarded')->default(0);

    $table->timestamp('achieved_at');
    $table->timestamp('valid_until')->nullable();

    $table->text('notes')->nullable();
    $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();

    $table->index(['user_id', 'achieved_at']);
    $table->index(['rank_id', 'type']);
});
```

#### 2.2.10 `mlm_payout_batches` - Batch Payout Processing

```php
Schema::create('mlm_payout_batches', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    $table->string('batch_number')->unique();
    $table->string('period_type');                    // daily, weekly, monthly
    $table->string('period_key');                     // "2025-01-15", "2025-W02"
    $table->date('period_start');
    $table->date('period_end');

    // Totals
    $table->unsignedBigInteger('total_gross')->default(0);
    $table->unsignedBigInteger('total_tds')->default(0);
    $table->unsignedBigInteger('total_admin_fee')->default(0);
    $table->unsignedBigInteger('total_net')->default(0);
    $table->unsignedInteger('total_recipients')->default(0);
    $table->unsignedInteger('total_commissions')->default(0);

    // Status
    $table->enum('status', [
        'draft',            // Being prepared
        'pending_approval', // Awaiting approval
        'approved',         // Approved, ready to process
        'processing',       // Payout in progress
        'completed',        // All paid
        'partial',          // Some failed
        'failed',           // All failed
        'cancelled'         // Cancelled
    ])->default('draft');

    // Processing
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->timestamp('completed_at')->nullable();

    $table->text('notes')->nullable();
    $table->json('metadata')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['period_type', 'period_key']);
    $table->index('status');
});
```

#### 2.2.11 `mlm_pool_funds` - Global Bonus Pool

```php
Schema::create('mlm_pool_funds', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();

    $table->foreignId('plan_id')->constrained('mlm_plans')->cascadeOnDelete();

    $table->string('name');                           // "Leadership Pool", "Global Pool"
    $table->string('slug');
    $table->text('description')->nullable();

    // Fund Balance (paisa)
    $table->unsignedBigInteger('balance')->default(0);
    $table->unsignedBigInteger('total_contributed')->default(0);
    $table->unsignedBigInteger('total_distributed')->default(0);

    // Distribution Rules
    $table->enum('distribution_frequency', [
        'daily', 'weekly', 'monthly', 'quarterly', 'annually', 'manual'
    ])->default('monthly');

    $table->json('distribution_rules')->nullable();
    // {"min_rank": 3, "share_type": "equal" | "rank_weighted"}

    $table->boolean('is_active')->default(true);

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['plan_id', 'slug']);
});
```

---

## Part 3: Enums and Casts

### 3.1 New Enums Required

```php
// app/Enums/Mlm/PlanType.php
enum PlanType: string {
    case UNILEVEL = 'unilevel';
    case BINARY = 'binary';
    case MATRIX = 'matrix';
    case BOARD = 'board';
    case HYBRID = 'hybrid';
}

// app/Enums/Mlm/PackageType.php
enum PackageType: string {
    case JOINING = 'joining';
    case UPGRADE = 'upgrade';
    case RENEWAL = 'renewal';
    case TOPUP = 'topup';
    case PRODUCT_PACK = 'product_pack';
}

// app/Enums/Mlm/BonusCalculationType.php
enum BonusCalculationType: string {
    case PERCENT_OF_PURCHASE = 'percent_of_purchase';
    case PERCENT_OF_PV = 'percent_of_pv';
    case PERCENT_OF_BV = 'percent_of_bv';
    case PERCENT_OF_DOWNLINE = 'percent_of_downline';
    case FIXED_AMOUNT = 'fixed_amount';
    case POOL_SHARE = 'pool_share';
    case TIERED = 'tiered';
    case CUSTOM = 'custom';
}

// app/Enums/Mlm/BonusTrigger.php
enum BonusTrigger: string {
    case ON_JOIN = 'on_join';
    case ON_PURCHASE = 'on_purchase';
    case ON_RANK_ACHIEVE = 'on_rank_achieve';
    case ON_RENEWAL = 'on_renewal';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case ON_TEAM_PURCHASE = 'on_team_purchase';
    case ON_BINARY_PAIR = 'on_binary_pair';
    case ON_MATRIX_CYCLE = 'on_matrix_cycle';
}

// app/Enums/Mlm/CommissionStatus.php
enum CommissionStatus: string {
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case HELD = 'held';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';
}

// app/Enums/Mlm/UserPackageStatus.php
enum UserPackageStatus: string {
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case UPGRADED = 'upgraded';
    case RENEWED = 'renewed';
}
```

---

## Part 4: Service Architecture

### 4.1 Service Classes

```
app/Services/Mlm/
├── MlmService.php                    # Main facade/coordinator
├── GenealogyService.php              # Tree management
├── CommissionService.php             # Commission calculation
├── RankService.php                   # Rank qualification & advancement
├── PayoutService.php                 # Payout processing
├── PoolService.php                   # Global pool management
│
├── Calculators/
│   ├── CommissionCalculatorInterface.php
│   ├── UnilevelCalculator.php
│   ├── BinaryCalculator.php
│   ├── MatrixCalculator.php
│   └── PoolCalculator.php
│
├── Qualifiers/
│   ├── RankQualifierInterface.php
│   └── RankQualifier.php
│
└── Events/
    ├── MemberJoined.php
    ├── PackagePurchased.php
    ├── RankAchieved.php
    └── CommissionPaid.php
```

### 4.2 Key Service Methods

```php
// GenealogyService
- placeMember(User $user, User $sponsor, ?User $placement, ?string $position)
- getUpline(User $user, int $levels = 5): Collection
- getDownline(User $user, int $levels = 5): Collection
- getDirectReferrals(User $user): Collection
- getTeamSize(User $user): int
- updateVolumes(User $user): void
- findPlacementPosition(User $sponsor): array // For auto-placement

// CommissionService
- calculateCommissions(UserPackage $package): Collection
- processLevelCommissions(UserPackage $package): void
- processSponsorBonus(UserPackage $package): void
- processMatchingBonus(User $user, Carbon $period): void
- getCalculator(Plan $plan): CommissionCalculatorInterface

// RankService
- checkQualification(User $user, Rank $rank): bool
- processRankAdvancement(User $user): ?Rank
- getQualificationProgress(User $user, Rank $rank): array
- demoteIfUnqualified(User $user): void

// PayoutService
- createBatch(string $periodType, Carbon $start, Carbon $end): PayoutBatch
- approveBatch(PayoutBatch $batch, User $approver): void
- processBatch(PayoutBatch $batch): void
- processCommissionToPayout(Commission $commission): void
```

---

## Part 5: Migration Plan

### 5.1 What to Keep (Modify)

| Existing Table | Action | Notes |
|----------------|--------|-------|
| `wallets` | Keep | Already good |
| `transactions` | Keep | Already good |
| `beneficiary_accounts` | Keep | Already good |
| `integrations` | Keep | Already good |

### 5.2 What to Replace

| Old Table | New Table | Reason |
|-----------|-----------|--------|
| `stages` | `mlm_plans` | More flexible, supports multiple plan types |
| `levels` | `mlm_ranks` | Proper rank system with qualification criteria |
| `user_subscriptions` | `mlm_user_packages` | Better package tracking |

### 5.3 New Tables to Create

1. `mlm_plans` - Plan configuration
2. `mlm_ranks` - Rank definitions
3. `mlm_packages` - Package definitions
4. `mlm_bonus_types` - Bonus type registry
5. `mlm_plan_bonus_configs` - Plan-bonus mapping
6. `mlm_genealogy` - Network tree
7. `mlm_user_packages` - User subscriptions
8. `mlm_commissions` - Commission ledger
9. `mlm_rank_history` - Rank changes
10. `mlm_payout_batches` - Batch payouts
11. `mlm_pool_funds` - Global pools

### 5.4 Migration Order

```
1. mlm_plans (no dependencies)
2. mlm_ranks (depends on mlm_plans)
3. mlm_packages (depends on mlm_plans, mlm_ranks)
4. mlm_bonus_types (no dependencies)
5. mlm_plan_bonus_configs (depends on mlm_plans, mlm_bonus_types)
6. mlm_genealogy (depends on users, mlm_plans, mlm_ranks)
7. mlm_user_packages (depends on users, mlm_packages, mlm_genealogy, transactions)
8. mlm_commissions (depends on users, mlm_genealogy, mlm_user_packages, mlm_bonus_types)
9. mlm_rank_history (depends on users, mlm_genealogy, mlm_ranks)
10. mlm_payout_batches (depends on users)
11. mlm_pool_funds (depends on mlm_plans)
12. Update mlm_commissions to add payout_batch_id FK
```

---

## Part 6: Implementation Phases

### Phase 1: Foundation (Week 1)
- [ ] Create all migrations
- [ ] Create all enums/casts
- [ ] Create base models with relationships
- [ ] Create seeders for default plan, ranks, packages, bonus types

### Phase 2: Core Services (Week 2)
- [ ] GenealogyService - Tree management
- [ ] CommissionService - Basic calculations
- [ ] RankService - Qualification checks

### Phase 3: Commission Calculators (Week 3)
- [ ] UnilevelCalculator
- [ ] MatrixCalculator
- [ ] BinaryCalculator (if needed)
- [ ] Integration tests

### Phase 4: Payout System (Week 4)
- [ ] PayoutService
- [ ] Batch processing
- [ ] Wallet integration
- [ ] TDS handling

### Phase 5: Admin Panel (Week 5)
- [ ] Plan management (Filament)
- [ ] Rank management
- [ ] Package management
- [ ] Commission reports
- [ ] Payout management

### Phase 6: API & Frontend (Week 6)
- [ ] Member dashboard API
- [ ] Genealogy tree API
- [ ] Commission history API
- [ ] Frontend components

---

## Part 7: Key Differentiators vs Competitors

| Feature | Our System | Competitors |
|---------|------------|-------------|
| **Plan Flexibility** | Multiple plan types, configurable per stage | Usually single plan type |
| **Bonus Modularity** | Plug-and-play bonus types | Hardcoded bonuses |
| **Real-time Calculation** | Event-driven, instant | Often batch-only |
| **Genealogy Performance** | Materialized paths for fast queries | Recursive queries |
| **Multi-currency** | Support multiple currencies | Usually single currency |
| **TDS Compliance** | Built-in Indian TDS handling | Often missing |
| **Audit Trail** | Complete commission history | Limited tracking |
| **API-First** | Full REST API | Admin-only often |

---

## Part 8: Sample Data Structure

### Default Plan (Matrix 5x5)

```json
{
  "name": "Matrix 5x5 Plan",
  "type": "matrix",
  "max_width": 5,
  "max_depth": 5,
  "has_spillover": true,
  "spillover_strategy": "fifo",
  "level_commissions": {
    "1": 10,
    "2": 5,
    "3": 3,
    "4": 2,
    "5": 1
  },
  "enabled_bonuses": [
    "direct_sponsor_bonus",
    "level_commission",
    "rank_achievement_bonus",
    "matching_bonus"
  ]
}
```

### Default Ranks

```json
[
  {
    "name": "Bronze",
    "level": 1,
    "min_direct_referrals": 0,
    "min_team_size": 0,
    "commission_multiplier": 1.0,
    "matching_bonus_percent": 0
  },
  {
    "name": "Silver",
    "level": 2,
    "min_direct_referrals": 5,
    "min_team_size": 25,
    "min_personal_pv": 100,
    "commission_multiplier": 1.1,
    "matching_bonus_percent": 5
  },
  {
    "name": "Gold",
    "level": 3,
    "min_direct_referrals": 10,
    "min_team_size": 125,
    "min_personal_pv": 200,
    "rank_requirements": [{"rank_id": 2, "count": 3}],
    "commission_multiplier": 1.25,
    "matching_bonus_percent": 10
  },
  {
    "name": "Platinum",
    "level": 4,
    "min_direct_referrals": 15,
    "min_team_size": 500,
    "rank_requirements": [{"rank_id": 3, "count": 3}],
    "commission_multiplier": 1.5,
    "matching_bonus_percent": 15
  },
  {
    "name": "Diamond",
    "level": 5,
    "min_direct_referrals": 20,
    "min_team_size": 1000,
    "rank_requirements": [{"rank_id": 4, "count": 5}],
    "commission_multiplier": 2.0,
    "matching_bonus_percent": 20,
    "pool_share_percent": 5
  }
]
```

---

## Approval Checklist

Before proceeding with implementation:

- [ ] Approve table schemas
- [ ] Approve enum definitions
- [ ] Approve service architecture
- [ ] Confirm plan type priorities (Matrix vs Binary vs Unilevel)
- [ ] Confirm bonus types needed
- [ ] Confirm Indian TDS requirements (10% on >5000/month?)
- [ ] Confirm whether to keep or drop old stages/levels tables

---

**Ready to proceed with implementation upon approval.**
