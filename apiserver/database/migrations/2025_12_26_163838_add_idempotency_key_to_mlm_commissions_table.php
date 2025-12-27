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
     * Adds idempotency_key column for duplicate commission prevention.
     * Key format: {type}:{recipient_id}:{commissionable_type}:{commissionable_id}:{level}
     *
     * The unique index allows fast deduplication checks and prevents
     * the same commission from being created twice even with retries.
     */
    public function up(): void
    {
        Schema::table('mlm_commissions', function (Blueprint $table) {
            $table->string('idempotency_key', 255)->nullable()->after('metadata');
            $table->unique('idempotency_key', 'mlm_commissions_idempotency_key_unique');

            // Add index for faster monthly total queries
            $table->index(['user_id', 'created_at'], 'mlm_commissions_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mlm_commissions', function (Blueprint $table) {
            $table->dropUnique('mlm_commissions_idempotency_key_unique');
            $table->dropIndex('mlm_commissions_user_created_idx');
            $table->dropColumn('idempotency_key');
        });
    }
};
