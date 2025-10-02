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

            $table->unsignedInteger('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->unsignedInteger('wholesale_unit_quantity')->nullable();

            $table->unsignedBigInteger('price')->default(0);

            // Auto-updating stock fields
            $table->unsignedInteger('init_quantity');
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->integer('stock')->storedAs('CAST(init_quantity AS SIGNED) - CAST(sold_quantity AS SIGNED)');
            $table->boolean('in_stock')->storedAs('IF(stock > 0, true, false)')->index();

            // Future expansion
            //$table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();

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
