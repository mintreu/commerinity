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
        Schema::create('historical_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_transaction_id')->nullable()->unique();
            $table->uuid('uuid')->unique();

            // Wallet relationship
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();

            // Polymorphic relationship to source (Order, Subscription, etc.)
            $table->morphs('transactionable', 'hist_txn_trx');

            // Transaction details
            $table->string('type');
            $table->string('status')->default('pending');

            // All amounts in paisa
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('fee')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('net_amount')->default(0);

            // Currency
            $table->string('currency', 3)->default('INR');

            // Payment method and provider
            $table->string('payment_method')->nullable();
            $table->string('checkout_type')->nullable();
            $table->foreignId('integration_id')->nullable()->constrained()->nullOnDelete();

            // Provider response data
            $table->string('provider_order_id')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->string('provider_signature')->nullable();
            $table->string('provider_gen_id')->nullable()->unique();
            $table->string('provider_gen_session')->nullable();
            $table->string('provider_gen_link')->nullable();
            $table->string('provider_gen_qr')->nullable();
            $table->string('provider_generated_sign')->nullable();
            $table->string('qr_code_url')->nullable();

            $table->string('success_url')->nullable();
            $table->string('failure_url')->nullable();
            $table->string('success_redirect_url')->nullable();
            $table->string('failure_redirect_url')->nullable();

            // Verification
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            // Description and notes
            $table->string('description')->nullable();
            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();

            // Reference tracking
            $table->string('reference_number')->nullable()->index();
            $table->unsignedBigInteger('parent_transaction_id')->nullable();

            // Expiry for pending transactions
            $table->timestamp('expires_at')->nullable();

            // Balance snapshot after transaction
            $table->unsignedBigInteger('balance_after')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->json('provider_response')->nullable();

            // Archive tracking
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
            $table->index('payment_method');
            $table->index('purpose');
            $table->index(['wallet_id', 'type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_transactions');
    }
};
