<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // MLM fields for commission calculation
            $table->unsignedInteger('bv')->default(0)->after('total_price')->comment('Business Volume');
            $table->unsignedInteger('pv')->default(0)->after('bv')->comment('Personal Volume');
            $table->unsignedInteger('reward_points')->default(0)->after('pv');

            // Stock tracking for FIFO
            $table->foreignId('stock_id')->nullable()->after('product_id')->constrained('product_stocks')->nullOnDelete();
        });

        // Add MLM totals to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('total_bv')->default(0)->after('total')->comment('Total Business Volume');
            $table->unsignedInteger('total_pv')->default(0)->after('total_bv')->comment('Total Personal Volume');
            $table->unsignedInteger('total_reward_points')->default(0)->after('total_pv');
            $table->boolean('commission_processed')->default(false)->after('total_reward_points');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_id');
            $table->dropColumn(['bv', 'pv', 'reward_points']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_bv', 'total_pv', 'total_reward_points', 'commission_processed']);
        });
    }
};
