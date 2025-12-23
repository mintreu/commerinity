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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_code_2', 2)->unique()->index();
            $table->string('iso_code_3', 3)->unique();
            $table->integer('isd_code')->comment('International dialing code');
            $table->string('address_format')->nullable();
            $table->boolean('postcode_required')->default(true);
            $table->string('locale', 5)->default('en');
            $table->string('region', 50)->comment('Asia, Europe, etc.');
            $table->string('timezone', 50);
            $table->string('timezone_diff', 10)->comment('e.g., +05:30');
            $table->string('currency', 3)->default('USD');
            $table->string('flag')->nullable()->comment('Flag emoji or URL');
            $table->json('exchange_rate')->nullable();
            $table->float('multiplier')->default(1);
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
