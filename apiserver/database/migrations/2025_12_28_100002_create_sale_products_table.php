<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Override sale timing for specific product
            $table->datetime('starts_from')->nullable();
            $table->datetime('ends_till')->nullable();

            // Override sale action for specific product
            $table->string('action_type', 20)->nullable();
            $table->unsignedInteger('sale_price')->default(0);      // Final sale price (in paise)
            $table->unsignedInteger('discount_amount')->default(0); // Discount amount (in paise or %)

            $table->boolean('end_other_rules')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Polymorphic target (for user-specific or group-specific sales)
            $table->nullableMorphs('target');

            $table->timestamps();

            $table->index(['product_id', 'starts_from', 'ends_till'], 'sale_products_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_products');
    }
};
