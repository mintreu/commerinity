<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->datetime('starts_from');
            $table->datetime('ends_till');
            $table->boolean('status')->default(false);

            // Condition matching (AND/OR logic)
            $table->string('condition_type', 20)->default('match_all');
            $table->json('conditions')->nullable();
            $table->boolean('end_other_rules')->default(false);

            // Discount action
            $table->string('action_type', 20); // by_percent, by_fixed, to_percent, to_fixed
            $table->unsignedInteger('discount_amount')->default(0);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'starts_from', 'ends_till'], 'sales_active_idx');
        });

        // Polymorphic pivot for sale targets (categories, products, etc.)
        Schema::create('sale_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->morphs('target');
            $table->timestamps();

            $table->unique(['sale_id', 'target_type', 'target_id'], 'sale_targets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_targets');
        Schema::dropIfExists('sales');
    }
};
