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
        Schema::table('stages', function (Blueprint $table) {
            // Matrix Configuration
            $table->unsignedInteger('matrix_width')->default(5)->after('max_team_members');
            $table->unsignedInteger('matrix_depth')->default(4)->after('matrix_width');

            // Sponsor Bonus (one-time on direct recruitment)
            // {"type": "percent", "value": 20} or {"type": "fixed", "value": 50000}
            $table->json('sponsor_bonus')->nullable()->after('commission_rates');

            // Matching Bonus (% of direct downline's earnings)
            $table->decimal('matching_bonus_percent', 5, 2)->default(0)->after('sponsor_bonus');
            $table->unsignedInteger('matching_bonus_levels')->default(1)->after('matching_bonus_percent');

            // Pool Contribution (% of each subscription goes to pool)
            $table->decimal('pool_contribution_percent', 5, 2)->default(0)->after('matching_bonus_levels');

            // Level Achievement Bonus (one-time bonus when user reaches each level)
            // {"1": 0, "2": 10000, "3": 50000, "4": 100000} = paisa bonus per level
            $table->json('level_achievement_bonus')->nullable()->after('pool_contribution_percent');

            // Upgrade Path
            $table->foreignId('upgrade_to_stage_id')->nullable()
                ->after('level_achievement_bonus')
                ->constrained('stages')
                ->nullOnDelete();
            $table->unsignedBigInteger('upgrade_price_difference')->default(0)->after('upgrade_to_stage_id');

            // Point Values (for qualification tracking)
            $table->unsignedInteger('pv')->default(0)->after('upgrade_price_difference');
            $table->unsignedInteger('bv')->default(0)->after('pv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropForeign(['upgrade_to_stage_id']);

            $table->dropColumn([
                'matrix_width',
                'matrix_depth',
                'sponsor_bonus',
                'matching_bonus_percent',
                'matching_bonus_levels',
                'pool_contribution_percent',
                'level_achievement_bonus',
                'upgrade_to_stage_id',
                'upgrade_price_difference',
                'pv',
                'bv',
            ]);
        });
    }
};
