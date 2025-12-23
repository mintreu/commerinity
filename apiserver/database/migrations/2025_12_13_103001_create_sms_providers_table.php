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
     * SMS Providers table stores third-party SMS service configurations.
     * Supports multiple providers with auto-failover based on priority.
     */
    public function up(): void
    {
        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Display name: Fast2SMS, MSG91, etc.');
            $table->string('slug', 50)->unique()->comment('Identifier: fast2sms, msg91, textlocal');
            $table->string('driver', 50)->comment('Driver class: fast2sms, msg91, log');

            // API Configuration (encrypted in model)
            $table->text('api_key')->nullable()->comment('Encrypted API key');
            $table->text('api_secret')->nullable()->comment('Encrypted API secret (if required)');
            $table->string('sender_id', 20)->nullable()->comment('DLT approved sender ID');
            $table->string('entity_id', 50)->nullable()->comment('DLT entity ID');
            $table->json('config')->nullable()->comment('Additional provider-specific config');

            // Balance & Rate Management
            $table->decimal('balance', 12, 2)->default(0)->comment('Current wallet balance');
            $table->decimal('per_sms_cost', 8, 4)->default(0.25)->comment('Cost per SMS');
            $table->decimal('min_balance_threshold', 10, 2)->default(10)->comment('Alert when below this');
            $table->timestamp('balance_checked_at')->nullable()->comment('Last balance check time');
            $table->timestamp('rate_valid_until')->nullable()->comment('Per SMS rate validity');

            // Status & Priority
            $table->boolean('is_active')->default(true)->comment('Provider enabled/disabled');
            $table->boolean('is_default')->default(false)->comment('Primary provider');
            $table->unsignedTinyInteger('priority')->default(1)->comment('Failover order: 1=highest');

            // Service Capabilities
            $table->boolean('supports_dlt')->default(true)->comment('Supports DLT SMS');
            $table->boolean('supports_otp')->default(true)->comment('Supports OTP/transactional');
            $table->boolean('supports_promotional')->default(false)->comment('Supports promotional SMS');
            $table->boolean('supports_whatsapp')->default(false)->comment('Supports WhatsApp messages');
            $table->boolean('supports_voice_otp')->default(false)->comment('Supports voice OTP');

            // Statistics
            $table->unsignedBigInteger('total_sent')->default(0)->comment('Total SMS sent');
            $table->unsignedBigInteger('total_delivered')->default(0)->comment('Total delivered');
            $table->unsignedBigInteger('total_failed')->default(0)->comment('Total failed');
            $table->decimal('success_rate', 5, 2)->default(0)->comment('Delivery success %');

            // Health Check
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->string('last_error', 500)->nullable();
            $table->unsignedSmallInteger('consecutive_failures')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'priority']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_providers');
    }
};
