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
        Schema::create('product_tiers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Auto-updating stock fields
            $table->unsignedInteger('init_quantity');
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->integer('stock')->storedAs('CAST(init_quantity AS SIGNED) - CAST(sold_quantity AS SIGNED)');
            $table->boolean('in_stock')->storedAs('IF(stock > 0, true, false)')->index();

            // Selling Strategy
            $table->unsignedInteger('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->unsignedInteger('wholesale_unit_quantity')->nullable();

            // Purchase Info
            $table->foreignId('product_supplier_id')->nullable()->constrained('product_suppliers')->cascadeOnUpdate()->nullOnDelete();
            $table->string('purchase_invoice_id')->nullable();
            $table->unsignedBigInteger('landing_cost')->default(0);



            $table->float('profit_margin')->default(0)->comment('Percentage of profit margin to calculate price from landing cost');


            $table->unsignedBigInteger('price')->default(0);



            // Future expansion
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            // Purchase date will be created_at  from timestamps auto creation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tiers');
    }
};
