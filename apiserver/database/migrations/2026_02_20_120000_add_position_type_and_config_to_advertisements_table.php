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
        Schema::table('advertisements', function (Blueprint $table): void {
            // Render position category inside placement (e.g. sidebar, grid_slot, popup)
            $table->string('position_type')->nullable()->after('placement');

            // Position-aware UI config (grid interval, max frequency, device targeting, etc.)
            $table->json('position_config')->nullable()->after('position');

            // Generic third-party setup payload (provider keys, slots, params)
            $table->json('third_party_config')->nullable()->after('ad_unit_id');
            $table->string('third_party_script_url')->nullable()->after('third_party_config');

            $table->index(['placement', 'position_type', 'is_active'], 'ads_placement_position_type_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->dropIndex('ads_placement_position_type_active_idx');
            $table->dropColumn([
                'position_type',
                'position_config',
                'third_party_config',
                'third_party_script_url',
            ]);
        });
    }
};

