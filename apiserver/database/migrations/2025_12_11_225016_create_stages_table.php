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
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Basic info
            $table->string('name'); // Fresher, Moderator, Expert
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Pricing (all in paisa)
            $table->unsignedBigInteger('base_price')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedInteger('tax_percentage')->default(0); // 18 = 18%
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('price')->default(0); // Final price

            // Affiliate Configuration
            $table->unsignedInteger('max_team_members')->default(780); // 5^1 + 5^2 + 5^3 + 5^4

            // Commission configuration (JSON for flexibility)
            $table->json('commission_rates')->nullable(); // {"level_1": 10, "level_2": 5, ...}

            // Benefits and Accessibility (JSON arrays)
            $table->json('benefits')->nullable();
            $table->json('accessibility')->nullable();

            // Ordering and display
            $table->unsignedInteger('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
