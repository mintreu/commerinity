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
        Schema::create('beneficiary_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic owner (User, Merchant, etc.)
            $table->morphs('accountable');

            // Optional wallet link
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();

            // Account type
            $table->string('type'); // savings, current, upi

            // Bank account details
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();

            // UPI details
            $table->string('upi_id')->nullable();

            // Account holder
            $table->string('holder_name');

            // Provider verification
            $table->foreignId('integration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_beneficiary_id')->nullable(); // ID from payment provider

            // Status
            $table->string('status')->default('pending'); // pending, verified, rejected, suspended
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();

            // Default flag
            $table->boolean('is_default')->default(false);

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes (morphs already creates accountable index)
            $table->index('type');
            $table->index('status');
            // Composite index for finding default account
            $table->index(['accountable_type', 'accountable_id', 'is_default'], 'beneficiary_accounts_default_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_accounts');
    }
};
