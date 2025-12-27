<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('init_quantity');
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->integer('in_stock_quantity')->storedAs('CAST(init_quantity AS SIGNED) - CAST(sold_quantity AS SIGNED)')->comment('Available quantity');
            $table->boolean('in_stock')->storedAs('IF(in_stock_quantity > 0, true, false)')->comment('Stock availability status')->index();
            $table->integer('priority')->default(0);
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->integer('low_stock_threshold')->default(5)->comment('Alert when stock drops to this level');
            $table->boolean('notify_on_low_stock')->default(true)->comment('Enable low stock notifications for this warehouse');
            $table->timestamps();
        });

        // Add constraint to prevent sold_quantity from exceeding init_quantity
        // This ensures in_stock_quantity never goes negative
        DB::statement('ALTER TABLE product_stocks ADD CONSTRAINT check_sold_not_exceed_init CHECK (sold_quantity <= init_quantity)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE product_stocks DROP CONSTRAINT IF EXISTS check_sold_not_exceed_init');
        Schema::dropIfExists('product_stocks');
    }
};
