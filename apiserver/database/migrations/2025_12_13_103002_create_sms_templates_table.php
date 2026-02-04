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
     * SMS Templates table stores DLT-approved message templates.
     * Each template has a unique message_id from Fast2SMS/provider.
     */
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();

            // Provider Reference
            $table->foreignId('integration_id')->constrained('integrations')->cascadeOnDelete();

            // Template Identification
            $table->string('name', 100)->comment('Internal name: otp_login, welcome, etc.');
            $table->string('slug', 50)->comment('Unique identifier: otp-login');
            $table->string('message_id', 50)->comment('Provider message ID (6-digit for Fast2SMS)');

            // DLT Details
            $table->string('entity_id', 50)->nullable()->comment('DLT entity ID');
            $table->string('template_id', 50)->nullable()->comment('DLT template ID');
            $table->string('sender_id', 20)->comment('DLT approved sender ID');

            // Template Content
            $table->text('content')->comment('Template with {#var#} placeholders');
            $table->json('variables')->nullable()->comment('List of variable names');
            $table->unsignedTinyInteger('variable_count')->default(0);

            // Classification
            $table->string('category', 30)->default('transactional')
                ->comment('otp, transactional, promotional, service');
            $table->string('language', 10)->default('en')->comment('en, hi, unicode');

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_dlt_approved')->default(false);
            $table->timestamp('dlt_approved_at')->nullable();

            // Usage Statistics
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['integration_id', 'slug']);
            $table->index('message_id');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
