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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Stage relationship
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();

            // Basic info
            $table->string('name'); // Bronze, Silver, Gold, Diamond
            $table->string('slug');
            $table->text('description')->nullable();

            // Level configuration
            $table->unsignedInteger('team_member_limit')->default(5); // 5, 25, 125, 625

            // Validity period
            $table->unsignedInteger('validity_days')->default(365); // 1 year default

            // Commission rates (percentage)
            $table->decimal('joining_bonus', 5, 2)->default(0); // % of subscription price given as bonus
            $table->decimal('purchase_commission', 5, 2)->default(0); // % on team purchases
            $table->decimal('recruitment_commission', 5, 2)->default(0); // % on new member recruitment

            // Level-specific commission rates per depth
            $table->json('depth_commissions')->nullable(); // {"1": 10, "2": 5, "3": 3, "4": 2, "5": 1}

            // Ordering within stage
            $table->unsignedInteger('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('stage_id');
            $table->index(['stage_id', 'sort_order']);
            $table->unique(['stage_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
