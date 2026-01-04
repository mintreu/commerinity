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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Wallet relationship
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();

            // Polymorphic relationship to source (Order, Subscription, etc.)
//            $table->nullableMorphs('transactionable'); // this is totally worng without transactionable no meanig of transaction
            $table->morphs('transactionable');

            // Transaction details
            $table->string('type'); // credit, debit, refund, chargeback, adjustment, hold, release
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled

            // All amounts in paisa
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('fee')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('net_amount')->default(0); // amount - fee - tax

            // Currency
            $table->string('currency', 3)->default('INR');

            // Payment method and provider
            $table->string('payment_method')->nullable(); // cash, cod, wallet, cashfree, razorpay, etc.
            $table->foreignId('integration_id')->nullable()->constrained()->nullOnDelete();

            // Provider response data
            $table->string('provider_order_id')->nullable(); // Provider's order/payment ID
            $table->string('provider_transaction_id')->nullable(); // Provider's transaction reference
            $table->string('provider_signature')->nullable(); // Signature for verification
            //$table->string('checkout_url')->nullable(); // Payment link  not required
            $table->string('qr_code_url')->nullable(); // QR code for payment

            $table->string('success_url')->nullable(); // success redirect after checkout
            $table->string('failure_url')->nullable();  // failure redirect after checkout

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            // Description and notes
            $table->string('description')->nullable();
            $table->string('purpose')->nullable(); // subscription, order, topup, payout, commission, etc.
            $table->text('notes')->nullable();

            // Reference tracking
            $table->string('reference_number')->nullable()->index();
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            // Expiry for pending transactions
            $table->timestamp('expires_at')->nullable();

            // Balance snapshot after transaction
            $table->unsignedBigInteger('balance_after')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->json('provider_response')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes (nullableMorphs already creates transactionable index)
            $table->index('type');
            $table->index('status');
            $table->index('payment_method');
            $table->index('purpose');
            $table->index(['wallet_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
