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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('name');
//            $table->string('slug')->unique();
            $table->string('url')->unique();
            $table->boolean('status')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('order')->default(0);
            $table->text('desc')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('banners')->nullable();
            $table->foreignId('category_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
