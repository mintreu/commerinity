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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // ========================================
            // LEVEL PROGRESSION TRACKING
            // ========================================

            // Current level within the stage (tracks progression)
            $table->foreignId('current_level_id')->nullable()
                ->after('level_id')
                ->constrained('levels')
                ->nullOnDelete();

            // When the current level was achieved
            $table->timestamp('level_achieved_at')->nullable()->after('current_level_id');

            // Highest level ever achieved (for history/display)
            $table->foreignId('highest_level_id')->nullable()
                ->after('level_achieved_at')
                ->constrained('levels')
                ->nullOnDelete();

            // Qualification snapshot when level was achieved (JSON)
            // {"direct_count": 5, "team_count": 30, "team_sales": 500000}
            $table->json('qualification_snapshot')->nullable()->after('highest_level_id');

            // ========================================
            // POINTS TRACKING
            // ========================================

            // Personal points accumulated
            $table->unsignedInteger('personal_pv')->default(0)->after('qualification_snapshot');

            // Team points accumulated
            $table->unsignedInteger('team_pv')->default(0)->after('personal_pv');

            // ========================================
            // COMMISSION TRACKING
            // ========================================

            // Total commission earned from this subscription (lifetime, in paisa)
            $table->unsignedBigInteger('total_commission_earned')->default(0)->after('team_pv');

            // Commission earned in current month (in paisa)
            $table->unsignedBigInteger('current_month_commission')->default(0)->after('total_commission_earned');

            // ========================================
            // RENEWAL TRACKING
            // ========================================

            // Last renewal date
            $table->timestamp('last_renewed_at')->nullable()->after('current_month_commission');

            // Number of times renewed
            $table->unsignedInteger('renewal_count')->default(0)->after('last_renewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['current_level_id']);
            $table->dropForeign(['highest_level_id']);

            $table->dropColumn([
                'current_level_id',
                'level_achieved_at',
                'highest_level_id',
                'qualification_snapshot',
                'personal_pv',
                'team_pv',
                'total_commission_earned',
                'current_month_commission',
                'last_renewed_at',
                'renewal_count',
            ]);
        });
    }
};
