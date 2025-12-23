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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Service identification
            $table->string('name'); // Display name: "Cashfree Production"
            $table->string('slug')->unique(); // cashfree, razorpay, stripe, etc.
            $table->string('type'); // payment, payout, sms, shipping

            // Credentials (encrypted JSON)
            $table->text('credentials')->nullable(); // Encrypted: {key, secret, webhook_secret}

            // Settings
            $table->json('settings')->nullable(); // Additional config

            // Environment
            $table->boolean('is_sandbox')->default(false);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            // Testing
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_result')->nullable(); // success, failed
            $table->text('last_test_message')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('type');
            $table->index(['type', 'is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
