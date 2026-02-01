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
            $table->uuid('uuid')->nullable()->comment('Public UUID');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained('product_stocks')->nullOnDelete();

            // Product snapshot at order time   .. not required we have product relation for this
//            $table->string('product_name');
//            $table->string('product_sku')->nullable();

            // Pricing (snapshot at order time, in paise)
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price')->comment('Unit price in paise');
            $table->unsignedBigInteger('total_price')->comment('Total price in paise');
            $table->unsignedInteger('bv')->default(0)->comment('Business Volume for this item');
            $table->unsignedInteger('pv')->default(0)->comment('Personal Volume earned');
            $table->unsignedInteger('reward_points')->default(0)->comment('Reward points for this item');

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
