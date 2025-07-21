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
            $table->nullableMorphs('tenant');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->integer('price')->nullable();
            $table->float('reward_point', 10, 2)->default(0.00);
            $table->boolean('is_returnable')->default(false);
            $table->string('status')->default('Draft');
            $table->text('status_feedback')->nullable();
            $table->integer('view_count')->default(0);
            $table->json('meta_data')->nullable();
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
