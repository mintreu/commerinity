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
        Schema::create('two_factor_auths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 2FA Settings
            $table->boolean('enabled')->default(false);
            $table->string('method')->nullable(); // sms, email, totp, biometric
            $table->timestamp('enabled_at')->nullable();

            // Biometric Authentication (Mobile Apps)
            $table->boolean('biometric_enabled')->default(false);
            $table->string('biometric_type')->nullable(); // fingerprint, face_id, iris
            $table->text('biometric_public_key')->nullable(); // Device public key for verification
            $table->timestamp('biometric_registered_at')->nullable();

            // TOTP (Authenticator App) - Google Authenticator, Microsoft Authenticator
            $table->text('totp_secret')->nullable(); // Encrypted secret key
            $table->string('totp_algorithm')->default('sha1'); // sha1, sha256, sha512
            $table->integer('totp_digits')->default(6); // 6 or 8 digits
            $table->integer('totp_period')->default(30); // seconds

            // Backup Codes (Recovery)
            $table->text('backup_codes')->nullable(); // JSON array, encrypted
            $table->integer('backup_codes_used')->default(0);
            $table->integer('backup_codes_total')->default(10);
            $table->timestamp('backup_codes_generated_at')->nullable();

            // Security
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique('user_id');
            $table->index(['user_id', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_auths');
    }
};
