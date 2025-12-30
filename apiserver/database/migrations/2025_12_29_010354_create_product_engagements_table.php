<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->morphs('authorable'); // authorable_id, authorable_type
            $table->foreignId('parent_id')->nullable()->constrained('product_engagements')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5 stars
            $table->text('review')->nullable();
            $table->unsignedInteger('helpful_votes')->default(0);
            $table->timestamps();

            // Only one top-level review per user per product
            $table->unique(
                ['product_id', 'authorable_id', 'authorable_type'],
                'unique_user_product_review'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_engagements');
    }
};
