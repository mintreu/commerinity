<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('starts_from');
            $table->date('ends_till');
            $table->boolean('status')->default(false);

            // Usage limits
            $table->unsignedInteger('usage_per_customer')->default(1);
            $table->unsignedInteger('coupon_usage_limit')->default(0); // 0 = unlimited
            $table->unsignedInteger('times_used')->default(0);

            // Condition matching
            $table->string('condition_type', 20)->default('match_all');
            $table->json('conditions')->nullable();
            $table->boolean('end_other_rules')->default(false);

            // Discount action
            $table->string('action_type', 20); // by_percent, by_fixed, cart_fixed, cart_percent, buy_x_get_y
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('discount_quantity')->default(1);
            $table->string('discount_step', 50)->nullable();

            // Shipping options
            $table->boolean('apply_to_shipping')->default(false);
            $table->boolean('free_shipping')->default(false);

            // Minimum requirements
            $table->unsignedInteger('min_cart_value')->default(0);     // In paise
            $table->unsignedSmallInteger('min_quantity')->default(0);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'starts_from', 'ends_till'], 'vouchers_active_idx');
        });

        // Polymorphic pivot for voucher targets
        Schema::create('voucher_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->morphs('target');
            $table->timestamps();

            $table->unique(['voucher_id', 'target_type', 'target_id'], 'voucher_targets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_targets');
        Schema::dropIfExists('vouchers');
    }
};
