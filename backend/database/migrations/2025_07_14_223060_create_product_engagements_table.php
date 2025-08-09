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
        Schema::create('product_engagements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->cascadeOnDelete();

            // Polymorphic user (customer, admin, vendor, etc.)
            $table->morphs('authorable');

            // Self-referencing parent for comment replies
            $table->foreignId('parent_id')->nullable()->constrained('product_engagements')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('review')->nullable();
            $table->boolean('wishlisted')->default(false);

            $table->timestamps();

            // Optional: prevent duplicate reviews per product-user (excluding replies)
            $table->unique([
                'product_id',
                'authorable_id',
                'authorable_type'
            ], 'unique_product_review')
                ->whereNull('parent_id'); // only enforce uniqueness on top-level reviews
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_engagements');
    }
};
