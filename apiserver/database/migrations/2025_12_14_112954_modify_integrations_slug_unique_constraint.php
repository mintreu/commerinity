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
     * Allow same slug for different types (e.g., razorpay payment and razorpay payout)
     */
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            // Drop the global unique constraint on slug
            $table->dropUnique(['slug']);

            // Add composite unique constraint on slug + type
            $table->unique(['slug', 'type'], 'integrations_slug_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('integrations_slug_type_unique');

            // Restore the global unique constraint on slug
            $table->unique('slug');
        });
    }
};
