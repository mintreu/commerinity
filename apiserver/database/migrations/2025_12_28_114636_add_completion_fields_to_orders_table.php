<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds order completion tracking fields:
     * - delivered_at: When order was marked as delivered
     * - return_period_ends_at: Calculated end of return period (based on max return_days from order items)
     * - completed_at: When order was finalized (after return period)
     *
     * Order Flow:
     * PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED → COMPLETED
     *                                                ↓             ↓
     *                                        (return period)   (commission trigger)
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('expire_at');
            $table->timestamp('return_period_ends_at')->nullable()->after('delivered_at');
            $table->timestamp('completed_at')->nullable()->after('return_period_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'return_period_ends_at', 'completed_at']);
        });
    }
};
