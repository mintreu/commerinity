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
            $table->string('sku')->unique();
            $table->string('url')->unique();
            $table->string('type')->nullable();
            $table->foreignId('filter_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->nullableMorphs('tenant');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->unsignedBigInteger('price')->default(0);

            // default we show tax inclusive way

            $table->boolean('is_tax_inclusive')
                ->default(false)
                ->comment('Indicates if the product price includes tax');

            $table->boolean('is_exempted')
                ->default(false)
                ->comment('Indicates if this product is exempt from tax');

            $table->string('tax_slab')->nullable();

            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();

//            $table->float('reward_point', 10, 2)->default(0.00);
            $table->boolean('is_returnable')->default(false);
            // Configurable Product
            $table->foreignId('parent_id')->nullable()->constrained('products')->cascadeOnUpdate()->nullOnDelete();

            // Shipping
            $table->decimal('width', 12, 2)->nullable();
            $table->decimal('height', 12, 2)->nullable();
            $table->decimal('length', 12, 2)->nullable();
            $table->decimal('weight', 12, 2)->nullable();

            // Downloadable product attributes
            $table->boolean('is_downloadable')->default(false);
            $table->string('download_link')->nullable(); // Secure URL for downloadable file
            $table->json('demo_data')->nullable(); // Optional demo auth with preview URL
            $table->unsignedBigInteger('file_size')->nullable(); // File size in bytes (optional)

            // In products table
            $table->string('proxy_base_url')->nullable(); // e.g., 'https://webcrm.com/internal' – Internal URL for proxying to App B
            $table->string('internal_auth_key')->nullable();
            $table->string('internal_auth_secret')->nullable(); // Shared secret for App A to authenticate with App B (e.g., JWT key)


            // Subscription product attributes
//            $table->unsignedSmallInteger('subscription_interval')->nullable(); // e.g., 1 for monthly
//            $table->enum('subscription_period', ['day', 'week', 'month', 'year'])->nullable(); // Billing period


            $table->string('status')->default(\Mintreu\Toolkit\Casts\PublishableStatusCast::DRAFT->value);
            $table->text('status_feedback')->nullable();



            $table->integer('view_count')->default(0);
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });



        Schema::create('product_filter_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('filter_option_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('filter_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
//            $table->timestamps();

            // Add unique constraint to prevent duplicates
            $table->unique(['product_id', 'filter_option_id']);
        });



    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_filter_options');
    }
};
