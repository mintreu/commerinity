<?php

declare(strict_types=1);

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
        Schema::table('users', function (Blueprint $table) {
            // Current membership level (updated when subscription is confirmed)
            $table->foreignId('level_id')
                ->nullable()
                ->after('parent_id')
                ->constrained()
                ->nullOnDelete();

            // Subscription status timestamp
            $table->timestamp('subscribed_at')->nullable()->after('onboarded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropColumn(['level_id', 'subscribed_at']);
        });
    }
};
