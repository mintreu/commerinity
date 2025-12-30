<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Affiliate Genealogy table tracks the network tree structure for the 5x4 matrix system.
     * Each user has one genealogy record that tracks their position in the Affiliate tree,
     * their sponsor chain, team counts per level, and sales volumes.
     */
    public function up(): void
    {
        Schema::create('affiliate_genealogy', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // ========================================
            // USER REFERENCE
            // ========================================

            // One genealogy record per user
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ========================================
            // PLACEMENT (for matrix spillover - can differ from parent_id)
            // ========================================
            // NOTE: Sponsor/referral tree uses users.parent_id (no duplication)
            // This is only for matrix placement when slots are full

            // Where placed in matrix (usually same as user.parent_id, differs on spillover)
            $table->foreignId('placement_parent_id')->nullable()->constrained('users')->nullOnDelete();

            // Position under placement parent (1-5 for 5-width matrix)
            $table->unsignedTinyInteger('placement_position')->default(1);

            // Depth from root (0 = root, 1 = level 1, etc.)
            $table->unsignedInteger('depth')->default(0);

            // ========================================
            // TEAM COUNTS (updated by triggers/events)
            // ========================================

            // Direct children (immediate referrals) - max 5 in our system
            $table->unsignedInteger('direct_count')->default(0);
            $table->unsignedInteger('active_direct_count')->default(0);

            // Team counts by level depth
            $table->unsignedInteger('level_1_count')->default(0);  // Direct: max 5
            $table->unsignedInteger('level_2_count')->default(0);  // Depth 2: max 25
            $table->unsignedInteger('level_3_count')->default(0);  // Depth 3: max 125
            $table->unsignedInteger('level_4_count')->default(0);  // Depth 4: max 625

            // Totals
            $table->unsignedInteger('total_team_count')->default(0);   // Sum of all levels (max 780)
            $table->unsignedInteger('active_team_count')->default(0);  // Active members only

            // ========================================
            // SALES VOLUME TRACKING (in paisa)
            // ========================================

            // Personal sales
            $table->unsignedBigInteger('personal_sales')->default(0);

            // Team sales by level
            $table->unsignedBigInteger('level_1_sales')->default(0);
            $table->unsignedBigInteger('level_2_sales')->default(0);
            $table->unsignedBigInteger('level_3_sales')->default(0);
            $table->unsignedBigInteger('level_4_sales')->default(0);

            // Total team sales
            $table->unsignedBigInteger('total_team_sales')->default(0);

            // ========================================
            // POINTS TRACKING
            // ========================================

            $table->unsignedInteger('personal_pv')->default(0);  // Personal Point Value
            $table->unsignedInteger('team_pv')->default(0);      // Team Point Value

            // ========================================
            // CURRENT STATUS & QUALIFICATION
            // ========================================

            // Current stage and level (quick access, also in user_subscriptions)
            $table->foreignId('current_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();

            // Highest level ever achieved
            $table->foreignId('highest_level_id')->nullable()->constrained('levels')->nullOnDelete();

            // Activity status
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            // ========================================
            // TIMESTAMPS
            // ========================================

            $table->timestamps();
            $table->softDeletes();

            // ========================================
            // INDEXES
            // ========================================

            // Each user has exactly one genealogy record
            $table->unique('user_id');

            // Find all members placed under a user (for matrix queries)
            $table->index('placement_parent_id');

            // Paths are used with LIKE queries, no index needed
            // (e.g., WHERE sponsor_path LIKE '/1/5/23/%' for downline queries)

            // Filter by depth and activity
            $table->index(['depth', 'is_active']);

            // Filter by stage/level
            $table->index('current_stage_id');
            $table->index('current_level_id');

            // Combined queries
            $table->index(['is_active', 'current_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_genealogy');
    }
};
