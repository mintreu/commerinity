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
     * SMS Logs table tracks all sent messages for analytics and debugging.
     * Stores delivery status, provider response, and cost tracking.
     */
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Unique identifier for API');

            // Provider Reference
            $table->foreignId('sms_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_slug', 50)->comment('Provider used: fast2sms, msg91');

            // Recipient & Content
            $table->string('recipient', 20)->comment('Phone number (E.164 format)');
            $table->text('message')->comment('SMS content');
            $table->string('message_type', 30)->default('transactional')
                ->comment('otp, transactional, promotional, alert');

            // Template Reference (for DLT)
            $table->foreignId('sms_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('template_code', 50)->nullable()->comment('DLT message ID');
            $table->json('variables')->nullable()->comment('Template variable values');

            // User Reference (optional)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('loggable'); // Polymorphic: Order, Transaction, etc.

            // Provider Response
            $table->string('request_id', 100)->nullable()->comment('Provider request ID');
            $table->string('message_id', 100)->nullable()->comment('Provider message ID');

            // Status Tracking
            $table->string('status', 30)->default('pending')
                ->comment('pending, queued, sent, delivered, failed, rejected');
            $table->string('delivery_status', 50)->nullable()->comment('Provider delivery status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Cost Tracking
            $table->decimal('cost', 8, 4)->default(0)->comment('Cost of this SMS');
            $table->unsignedTinyInteger('segments')->default(1)->comment('Number of SMS segments');

            // Error Handling
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->unsignedTinyInteger('max_retries')->default(3);

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('source', 50)->nullable()->comment('web, api, job, console');
            $table->json('metadata')->nullable()->comment('Additional context data');

            $table->timestamps();

            // Indexes for common queries
            $table->index('recipient');
            $table->index('status');
            $table->index('message_type');
            $table->index(['sms_provider_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
