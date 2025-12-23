<?php

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
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Device Identification
            $table->string('device_fingerprint', 64); // Unique browser fingerprint
            $table->string('device_name')->nullable(); // iPhone 14, Chrome on Windows
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Trust Status
            $table->timestamp('trusted_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at'); // Default: 30 days

            // Location (Optional)
            $table->string('country_code', 2)->nullable();
            $table->string('city')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'device_fingerprint']);
            $table->index('expires_at');
            $table->unique(['user_id', 'device_fingerprint']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
