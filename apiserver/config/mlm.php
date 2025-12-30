<?php

declare(strict_types=1);

/**
 * Affiliate Commission System Configuration
 *
 * This config controls ALL Affiliate commission features.
 * Each feature can be enabled/disabled independently.
 * Commission visibility on invoices/dashboards follows these settings.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Matrix Configuration
    |--------------------------------------------------------------------------
    */
    'matrix' => [
        'max_direct_children' => (int) env('Affiliate_MAX_DIRECT_CHILDREN', 5),
        'max_depth' => (int) env('Affiliate_MAX_DEPTH', 4),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Deletion Behavior
    |--------------------------------------------------------------------------
    */
    'deletion' => [
        'reassignment_strategy' => env('Affiliate_REASSIGNMENT_STRATEGY', 'ancestor_slots'),
        'soft_delete_users' => (bool) env('Affiliate_SOFT_DELETE_USERS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Member Commission Features (Affiliate Tree - parent_id based)
    |--------------------------------------------------------------------------
    | These commissions flow through the Affiliate tree (parent → ancestors)
    | Triggered when a member subscribes/purchases
    */
    'member_commissions' => [
        // Master switch for all member commissions
        'enabled' => (bool) env('Affiliate_MEMBER_COMMISSIONS_ENABLED', true),

        // Direct Sponsor Bonus - one-time on direct referral
        'sponsor_bonus' => [
            'enabled' => (bool) env('Affiliate_SPONSOR_BONUS_ENABLED', true),
            // Rate configured per Stage model (sponsor_bonus JSON)
        ],

        // Level Commission - % based on depth (1-4 levels up)
        'level_commission' => [
            'enabled' => (bool) env('Affiliate_LEVEL_COMMISSION_ENABLED', true),
            // Rates configured per Stage model (commission_rates JSON)
        ],

        // Matching Bonus - % of direct downline's earnings
        'matching_bonus' => [
            'enabled' => (bool) env('Affiliate_MATCHING_BONUS_ENABLED', true),
            // Rate configured per Stage model (matching_bonus_percent)
        ],

        // Level Achievement Bonus - one-time on reaching new level
        'level_achievement' => [
            'enabled' => (bool) env('Affiliate_LEVEL_ACHIEVEMENT_ENABLED', true),
            // Amounts configured per Stage model (level_achievement_bonus JSON)
        ],

        // Pool Bonus - share from global pool distribution
        'pool_bonus' => [
            'enabled' => (bool) env('Affiliate_POOL_BONUS_ENABLED', false),
            // Contribution % per Stage model (pool_contribution_percent)
        ],

        // Purchase Commission - commission on product purchases
        'purchase_commission' => [
            'enabled' => (bool) env('Affiliate_PURCHASE_COMMISSION_ENABLED', true),
        ],

        // Renewal Bonus - bonus when member renews subscription
        'renewal_bonus' => [
            'enabled' => (bool) env('Affiliate_RENEWAL_BONUS_ENABLED', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Originator Commission Features (Agent/Advisor who recruited member)
    |--------------------------------------------------------------------------
    | These commissions go to the originator (agent/advisor) who brought
    | the member into the system (separate from Affiliate tree)
    | Only applicable when user has originator_type/originator_id set
    */
    'originator_commissions' => [
        // Master switch for all originator features
        'enabled' => (bool) env('Affiliate_ORIGINATOR_COMMISSIONS_ENABLED', true),

        // Joining Commission - originator gets % when originated user subscribes
        'joining_commission' => [
            'enabled' => (bool) env('Affiliate_ORIGINATOR_JOINING_ENABLED', true),
            'type' => env('Affiliate_ORIGINATOR_JOINING_TYPE', 'percent'), // 'percent' or 'fixed'
            'value' => (float) env('Affiliate_ORIGINATOR_JOINING_VALUE', 5), // 5% of subscription
            // When 'fixed': value in paisa
        ],

        // Recurring Commission - originator gets % on originated user's withdrawals/earnings
        'recurring_commission' => [
            'enabled' => (bool) env('Affiliate_ORIGINATOR_RECURRING_ENABLED', false),
            'type' => env('Affiliate_ORIGINATOR_RECURRING_TYPE', 'percent'),
            'value' => (float) env('Affiliate_ORIGINATOR_RECURRING_VALUE', 2), // 2% of withdrawal
            'frequency' => env('Affiliate_ORIGINATOR_RECURRING_FREQ', 'on_withdrawal'), // 'on_withdrawal' or 'monthly'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Income Deduction Feature (Agent/Advisor Salary Fund)
    |--------------------------------------------------------------------------
    | When enabled, a % is cut from member earnings to fund agent/advisor salaries
    | This is SEPARATE from originator commission (that's a direct payment)
    | This creates a pool for target-based salary payouts
    */
    'income_deduction' => [
        // Master switch - when true, deduction is shown on invoices
        'enabled' => (bool) env('Affiliate_INCOME_DEDUCTION_ENABLED', false),

        // Deduction percentage from member commission earnings
        'percent' => (float) env('Affiliate_INCOME_DEDUCTION_PERCENT', 3), // 3% cut

        // Who pays this deduction
        'deduct_from' => 'member_earnings', // Only from member commission payouts

        // Purpose description (shown on invoice when enabled)
        'description' => env('Affiliate_INCOME_DEDUCTION_DESC', 'Platform Service Fee'),

        // Show on member invoice/dashboard
        'show_on_invoice' => (bool) env('Affiliate_INCOME_DEDUCTION_SHOW_INVOICE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent/Advisor Salary System
    |--------------------------------------------------------------------------
    | Target-based salary for agents/advisors
    | Funded by company + income_deduction pool (when enabled)
    */
    'agent_salary' => [
        // Master switch for salary feature
        'enabled' => (bool) env('Affiliate_AGENT_SALARY_ENABLED', false),

        // Salary tiers based on targets (can be overridden in database)
        'tiers' => [
            [
                'name' => 'Bronze Agent',
                'min_originated_users' => 5,
                'min_team_sales' => 50000_00, // 50,000 INR in paisa
                'base_salary' => 5000_00, // 5,000 INR monthly
                'bonus_percent' => 1, // Extra 1% on originated user earnings
            ],
            [
                'name' => 'Silver Agent',
                'min_originated_users' => 15,
                'min_team_sales' => 150000_00,
                'base_salary' => 15000_00,
                'bonus_percent' => 2,
            ],
            [
                'name' => 'Gold Agent',
                'min_originated_users' => 30,
                'min_team_sales' => 500000_00,
                'base_salary' => 35000_00,
                'bonus_percent' => 3,
            ],
            [
                'name' => 'Diamond Agent',
                'min_originated_users' => 50,
                'min_team_sales' => 1000000_00,
                'base_salary' => 75000_00,
                'bonus_percent' => 5,
            ],
        ],

        // Salary payout frequency
        'payout_frequency' => env('Affiliate_SALARY_FREQUENCY', 'monthly'), // 'weekly', 'monthly'

        // Day of month for salary payout (1-28)
        'payout_day' => (int) env('Affiliate_SALARY_PAYOUT_DAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Task/Activity Based Commissions (Extensible)
    |--------------------------------------------------------------------------
    | Commission for completing tasks, milestones, referrals, performance KPIs
    | Highly extensible - tasks can be anything defined in database
    */
    'task_commissions' => [
        // Master switch for all task-based commissions
        'enabled' => (bool) env('Affiliate_TASK_COMMISSIONS_ENABLED', true),

        // Task completion commission
        'task_completion' => [
            'enabled' => (bool) env('Affiliate_TASK_COMPLETION_ENABLED', true),
            // Task definitions stored in database (affiliate_tasks table)
            // Each task has: name, description, reward_type, reward_value, criteria
        ],

        // Milestone/Goal achievement bonus
        'milestone_bonus' => [
            'enabled' => (bool) env('Affiliate_MILESTONE_BONUS_ENABLED', true),
            // Milestones defined in database (affiliate_milestones table)
            // Examples: First 10 referrals, ₹1 lakh team sales, etc.
        ],

        // Referral bonus (non-Affiliate, simple referral)
        'referral_bonus' => [
            'enabled' => (bool) env('Affiliate_REFERRAL_BONUS_ENABLED', true),
            'type' => env('Affiliate_REFERRAL_BONUS_TYPE', 'fixed'), // 'percent' or 'fixed'
            'value' => (int) env('Affiliate_REFERRAL_BONUS_VALUE', 100_00), // ₹100 in paisa
            // Different from sponsor_bonus - this is for non-member referrals
        ],

        // Performance/KPI bonus
        'performance_bonus' => [
            'enabled' => (bool) env('Affiliate_PERFORMANCE_BONUS_ENABLED', false),
            // KPIs defined in database (affiliate_kpis table)
            // Examples: Monthly sales target, customer retention rate
        ],

        // Custom commission type (fully database-driven)
        'custom' => [
            'enabled' => (bool) env('Affiliate_CUSTOM_COMMISSION_ENABLED', true),
            // All config comes from database (affiliate_commission_types table)
            // Allows admin to create new commission types via Filament
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Level-Based Feature Access
    |--------------------------------------------------------------------------
    | Control which features are available at each level
    | Makes app more professional and engaging
    */
    'level_features' => [
        'enabled' => (bool) env('Affiliate_LEVEL_FEATURES_ENABLED', true),

        // Features unlocked at each global_rank (1-16)
        'unlocks' => [
            1 => ['basic_dashboard', 'referral_link', 'basic_reports'],
            2 => ['team_tree_view', 'commission_history'],
            3 => ['advanced_reports', 'export_data'],
            4 => ['priority_support', 'exclusive_products'],
            5 => ['early_access', 'beta_features'],
            8 => ['mentor_tools', 'training_resources'],
            12 => ['leadership_dashboard', 'team_analytics'],
            16 => ['vip_support', 'custom_landing_page'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TDS (Tax Deducted at Source) Configuration
    |--------------------------------------------------------------------------
    */
    'tds' => [
        'enabled' => (bool) env('Affiliate_TDS_ENABLED', true),
        'threshold_monthly' => (int) env('Affiliate_TDS_THRESHOLD', 500000), // 5000 INR in paisa
        'rate_percent' => (float) env('Affiliate_TDS_RATE', 10), // 10%
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Fee Configuration (Comprehensive)
    |--------------------------------------------------------------------------
    | Fully configurable platform fee that can be applied to different user types
    | and triggered on different events. Replaces simple admin_fee.
    |
    | Applied on:
    | - withdrawal: When user withdraws funds
    | - monthly: Monthly deduction from earnings
    | - commission: Deducted from each commission payout
    |
    | User types that can be charged:
    | - member: Regular Affiliate members
    | - advisor/agent: Originators who recruit
    | - promoter: Hybrid users (both Affiliate + originator)
    | - staff: Future support
    |
    | Excluded: draft users, regular users (non-Affiliate)
    */
    'platform_fee' => [
        // Master switch
        'enabled' => (bool) env('Affiliate_PLATFORM_FEE_ENABLED', false),

        // Default fee for all applicable user types
        'default' => [
            'type' => env('Affiliate_PLATFORM_FEE_TYPE', 'percent'), // 'percent' or 'fixed'
            'value' => (float) env('Affiliate_PLATFORM_FEE_VALUE', 2), // 2% default
        ],

        // When to apply the fee
        'triggers' => [
            'on_withdrawal' => (bool) env('Affiliate_PLATFORM_FEE_ON_WITHDRAWAL', true),
            'on_commission' => (bool) env('Affiliate_PLATFORM_FEE_ON_COMMISSION', false),
            'monthly' => (bool) env('Affiliate_PLATFORM_FEE_MONTHLY', false),
        ],

        // Per user type configuration (overrides default when set)
        'user_types' => [
            'member' => [
                'enabled' => (bool) env('Affiliate_PLATFORM_FEE_MEMBER_ENABLED', true),
                'type' => env('Affiliate_PLATFORM_FEE_MEMBER_TYPE', null), // null = use default
                'value' => env('Affiliate_PLATFORM_FEE_MEMBER_VALUE', null),
            ],
            'advisor' => [
                'enabled' => (bool) env('Affiliate_PLATFORM_FEE_ADVISOR_ENABLED', true),
                'type' => env('Affiliate_PLATFORM_FEE_ADVISOR_TYPE', null),
                'value' => env('Affiliate_PLATFORM_FEE_ADVISOR_VALUE', null),
            ],
            'agent' => [
                'enabled' => (bool) env('Affiliate_PLATFORM_FEE_AGENT_ENABLED', true),
                'type' => env('Affiliate_PLATFORM_FEE_AGENT_TYPE', null),
                'value' => env('Affiliate_PLATFORM_FEE_AGENT_VALUE', null),
            ],
            'promoter' => [
                'enabled' => (bool) env('Affiliate_PLATFORM_FEE_PROMOTER_ENABLED', true),
                'type' => env('Affiliate_PLATFORM_FEE_PROMOTER_TYPE', null),
                'value' => env('Affiliate_PLATFORM_FEE_PROMOTER_VALUE', null),
            ],
            'staff' => [
                'enabled' => (bool) env('Affiliate_PLATFORM_FEE_STAFF_ENABLED', false),
                'type' => env('Affiliate_PLATFORM_FEE_STAFF_TYPE', null),
                'value' => env('Affiliate_PLATFORM_FEE_STAFF_VALUE', null),
            ],
        ],

        // Excluded user types (never charged)
        'excluded_types' => ['draft', 'regular', 'guest'],

        // Minimum amount to trigger fee (in paisa)
        'min_amount_threshold' => (int) env('Affiliate_PLATFORM_FEE_MIN_AMOUNT', 10000), // Rs 100

        // Show on user invoice/statement
        'show_on_invoice' => (bool) env('Affiliate_PLATFORM_FEE_SHOW_INVOICE', true),

        // Description shown to user
        'description' => env('Affiliate_PLATFORM_FEE_DESC', 'Platform Service Fee'),

        // Where the collected fees go
        'collection_wallet' => env('Affiliate_PLATFORM_FEE_WALLET', 'platform_fees'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Admin Fee (Deprecated - Use platform_fee instead)
    |--------------------------------------------------------------------------
    */
    'admin_fee' => [
        'enabled' => (bool) env('Affiliate_ADMIN_FEE_ENABLED', false),
        'type' => env('Affiliate_ADMIN_FEE_TYPE', 'percent'),
        'value' => (float) env('Affiliate_ADMIN_FEE_VALUE', 0),
        'description' => env('Affiliate_ADMIN_FEE_DESC', 'Admin Fee'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Commission Rates (Fallback when Stage doesn't define)
    |--------------------------------------------------------------------------
    */
    'default_sponsor_bonus_percent' => (float) env('Affiliate_DEFAULT_SPONSOR_BONUS', 10),
    'default_level_rates' => [
        1 => (float) env('Affiliate_DEFAULT_LEVEL_1_RATE', 5),
        2 => (float) env('Affiliate_DEFAULT_LEVEL_2_RATE', 3),
        3 => (float) env('Affiliate_DEFAULT_LEVEL_3_RATE', 2),
        4 => (float) env('Affiliate_DEFAULT_LEVEL_4_RATE', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Visibility Settings
    |--------------------------------------------------------------------------
    | Control what users see based on their type and active features
    | Values can be: true, false, or 'config:path.to.key' for dynamic lookup
    */
    'dashboard_visibility' => [
        // What member users see
        'member' => [
            'show_affiliate_earnings' => true,
            'show_team_tree' => true,
            'show_level_progress' => true,
            'show_deduction_info' => 'config:affiliate.income_deduction.enabled',
        ],

        // What advisor/agent users see
        'advisor' => [
            'show_originated_users' => true,
            'show_originator_earnings' => 'config:affiliate.originator_commissions.enabled',
            'show_salary_progress' => 'config:affiliate.agent_salary.enabled',
            'show_targets' => 'config:affiliate.agent_salary.enabled',
        ],

        // What promoter users see (hybrid - both Affiliate tree + can originate)
        'promoter' => [
            'show_affiliate_earnings' => true,
            'show_team_tree' => true,
            'show_originated_users' => true,
            'show_originator_earnings' => 'config:affiliate.originator_commissions.enabled',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Display Settings
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        // Show breakdown of commission calculations
        'show_commission_breakdown' => (bool) env('Affiliate_INVOICE_SHOW_BREAKDOWN', true),

        // Show deduction line item (dynamic based on income_deduction.enabled)
        'show_deductions' => 'config:affiliate.income_deduction.enabled',

        // Show originator cut (dynamic based on originator_commissions.enabled)
        'show_originator_cut' => 'config:affiliate.originator_commissions.enabled',
    ],
];
