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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Unnamed Product');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('url')->unique();
            $table->string('type');
            $table->foreignId('filter_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->unsignedBigInteger('product_display_id')->nullable();
            $table->unsignedBigInteger('price')->default(0)->comment('Canonical price in paise');
            $table->string('hsn')->nullable()->comment('HSN/SAC code for GST classification');
            $table->string('gst_tax_type')->nullable()->comment('GST slab/percentage (product override)');
            $table->unsignedInteger('bv')->default(0)->comment('Business Volume default for the product');
            $table->unsignedInteger('pv')->default(0)->comment('Personal Volume default for the product');
            $table->unsignedInteger('reward_points')->default(0)->comment('Reward points earned per unit');
            $table->unsignedInteger('min_quantity')->default(1)->comment('Min order quantity for the product');
            $table->unsignedInteger('max_quantity')->nullable()->comment('Max order quantity (null = unlimited)');
            $table->unsignedInteger('wholesale_unit_quantity')->nullable()->comment('Quantity threshold for wholesale pricing');
            $table->unsignedInteger('weight_grams')->default(0)->comment('Product weight in grams');
            $table->unsignedInteger('length_cm')->default(0)->comment('Product length in centimeters');
            $table->unsignedInteger('width_cm')->default(0)->comment('Product width in centimeters');
            $table->unsignedInteger('height_cm')->default(0)->comment('Product height in centimeters');
            $table->boolean('is_commissionable')->default(true)->comment('Whether product generates affiliate commissions');
            $table->decimal('commission_rate', 5, 2)->nullable()->comment('Override commission rate for product');
            $table->string('status')->default(\App\Casts\ProductStatusCast::DRAFT);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
