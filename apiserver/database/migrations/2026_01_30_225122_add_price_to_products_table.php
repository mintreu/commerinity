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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->after('view_count')->comment('Canonical price in paise');
            $table->unsignedInteger('bv')->default(0)->after('price')->comment('Business Volume default for the product');
            $table->unsignedInteger('pv')->default(0)->after('bv')->comment('Personal Volume default for the product');
            $table->unsignedInteger('reward_points')->default(0)->after('pv')->comment('Reward points earned per unit');
            $table->unsignedInteger('min_quantity')->default(1)->after('reward_points')->comment('Min order quantity for the product');
            $table->unsignedInteger('max_quantity')->nullable()->after('min_quantity')->comment('Max order quantity (null = unlimited)');
            $table->unsignedInteger('wholesale_unit_quantity')->nullable()->after('max_quantity')->comment('Quantity threshold for wholesale pricing');
            $table->boolean('is_commissionable')->default(true)->after('wholesale_unit_quantity')->comment('Whether product generates affiliate commissions');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('is_commissionable')->comment('Override commission rate for product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
