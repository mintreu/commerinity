<?php

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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Product snapshot at order time
            $table->string('product_name');
            $table->string('product_sku')->nullable();

            // Pricing (snapshot at order time, in paise)
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->comment('Unit price in paise');
            $table->unsignedBigInteger('total_price')->comment('Total price in paise');

            $table->timestamps();

            // Performance indexes
            $table->index('order_id', 'idx_order_items_order');
            $table->index('product_id', 'idx_order_items_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
