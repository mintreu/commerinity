<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->morphs('authorable'); // authorable_id, authorable_type
            $table->timestamps();

            // One wishlist entry per user per product
            $table->unique(
                ['product_id', 'authorable_id', 'authorable_type'],
                'unique_user_product_wishlist'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_wishlists');
    }
};
