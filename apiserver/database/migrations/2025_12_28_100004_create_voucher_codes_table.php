<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->boolean('is_primary')->default(false);

            // Usage limits (can override voucher limits)
            $table->unsignedInteger('coupon_usage_limit')->default(0); // 0 = use voucher limit
            $table->unsignedInteger('usage_per_user')->default(0);     // 0 = use voucher limit
            $table->unsignedInteger('times_used')->default(0);

            // Code-specific validity period (optional override)
            $table->date('starts_from')->nullable();
            $table->date('ends_till')->nullable();

            $table->unsignedTinyInteger('type')->default(0); // 0=public, 1=private, 2=single-use

            $table->timestamps();

            $table->index(['voucher_id', 'is_primary']);
        });

        // Track usage per user per code
        Schema::create('voucher_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_code_id')->constrained('voucher_codes')->cascadeOnDelete();
            $table->morphs('userable');
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamps();

            $table->unique(['voucher_code_id', 'userable_type', 'userable_id'], 'voucher_usage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_code_usages');
        Schema::dropIfExists('voucher_codes');
    }
};
