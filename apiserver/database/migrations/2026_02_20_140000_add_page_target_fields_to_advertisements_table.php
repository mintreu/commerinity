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
            $table->string('page_target')->nullable()->after('placement');
            $table->string('page_pattern')->nullable()->after('page_target');

            $table->index(['page_target', 'is_active'], 'ads_page_target_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->dropIndex('ads_page_target_active_idx');
            $table->dropColumn([
                'page_target',
                'page_pattern',
            ]);
        });
    }
};

