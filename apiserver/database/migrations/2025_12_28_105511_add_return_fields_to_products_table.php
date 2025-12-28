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
     * Adds return policy fields to products:
     * - is_returnable: Whether product can be returned (default: false)
     * - return_days: Number of days allowed for return after delivery (default: 7)
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_returnable')->default(false)->after('status');
            $table->unsignedSmallInteger('return_days')->default(7)->after('is_returnable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_returnable', 'return_days']);
        });
    }
};
