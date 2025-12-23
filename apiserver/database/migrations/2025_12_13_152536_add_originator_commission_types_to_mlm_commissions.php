<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add originator commission types to the enum.
     */
    public function up(): void
    {
        // Modify enum to include originator commission types
        DB::statement("ALTER TABLE mlm_commissions MODIFY COLUMN type ENUM(
            'sponsor_bonus',
            'level_commission',
            'matching_bonus',
            'level_achievement',
            'pool_bonus',
            'purchase_commission',
            'renewal_bonus',
            'adjustment',
            'reversal',
            'originator_joining',
            'originator_recurring',
            'task_completion'
        ) DEFAULT 'level_commission'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE mlm_commissions MODIFY COLUMN type ENUM(
            'sponsor_bonus',
            'level_commission',
            'matching_bonus',
            'level_achievement',
            'pool_bonus',
            'purchase_commission',
            'renewal_bonus',
            'adjustment',
            'reversal'
        ) DEFAULT 'level_commission'");
    }
};
