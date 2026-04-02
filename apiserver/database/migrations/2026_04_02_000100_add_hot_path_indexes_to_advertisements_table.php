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
     * Adds read-path indexes for large advertisement tables without touching data.
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->index(
                ['placement', 'is_active', 'show_to_members', 'show_to_guests', 'position', 'id'],
                'ads_place_aud_pos_idx'
            );

            $table->index(
                ['placement', 'block', 'is_active', 'show_to_members', 'show_to_guests', 'position', 'id'],
                'ads_place_block_aud_pos_idx'
            );

            $table->index(
                ['placement', 'page_target', 'is_active', 'position', 'id'],
                'ads_place_page_active_pos_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->dropIndex('ads_place_aud_pos_idx');
            $table->dropIndex('ads_place_block_aud_pos_idx');
            $table->dropIndex('ads_place_page_active_pos_idx');
        });
    }
};

