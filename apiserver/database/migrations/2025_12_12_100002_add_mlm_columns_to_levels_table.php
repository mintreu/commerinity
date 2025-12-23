<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            // ========================================
            // UNIQUE IDENTIFICATION
            // ========================================

            // Full display name - unique across ALL stages
            // e.g., "Premium Gold", "Elite Diamond", "Starter Bronze"
            $table->string('full_name')->after('name');

            // Global rank number - unique across ALL stages (1-16 for 4 stages x 4 levels)
            // Used for easy comparison: Rank 8 > Rank 5 means higher achievement
            $table->unsignedInteger('global_rank')->after('full_name');

            // Level number within stage (1, 2, 3, 4)
            $table->unsignedInteger('level_number')->default(1)->after('global_rank');

            // ========================================
            // QUALIFICATION REQUIREMENTS
            // ========================================

            // Direct recruits needed to qualify for this level
            $table->unsignedInteger('min_direct_referrals')->default(0)->after('team_member_limit');

            // Must be active direct members
            $table->unsignedInteger('min_active_directs')->default(0)->after('min_direct_referrals');

            // Minimum personal purchase required (in paisa)
            $table->unsignedBigInteger('min_personal_purchase')->default(0)->after('min_active_directs');

            // Minimum team sales volume required (in paisa)
            $table->unsignedBigInteger('min_team_sales')->default(0)->after('min_personal_purchase');

            // ========================================
            // COMMISSION & BENEFITS
            // ========================================

            // Commission multiplier at this level (1.0 = 100%, 1.5 = 150%)
            $table->decimal('commission_multiplier', 5, 2)->default(1.00)->after('depth_commissions');

            // Level-specific benefits (JSON)
            // {"badge": "silver", "dashboard_theme": "premium", "support_priority": "high"}
            $table->json('level_benefits')->nullable()->after('commission_multiplier');

            // Rank badge visual
            $table->string('badge_icon')->nullable()->after('level_benefits');
            $table->string('badge_color')->nullable()->after('badge_icon');

            // ========================================
            // INDEXES
            // ========================================

            $table->unique('full_name');
            $table->unique('global_rank');
            $table->unique(['stage_id', 'level_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropUnique(['full_name']);
            $table->dropUnique(['global_rank']);
            $table->dropUnique(['stage_id', 'level_number']);

            $table->dropColumn([
                'full_name',
                'global_rank',
                'level_number',
                'min_direct_referrals',
                'min_active_directs',
                'min_personal_purchase',
                'min_team_sales',
                'commission_multiplier',
                'level_benefits',
                'badge_icon',
                'badge_color',
            ]);
        });
    }
};
