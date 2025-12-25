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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // User relationship
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Stage and Level
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();

            // Pricing (all in paisa) - snapshot at time of subscription
            $table->unsignedBigInteger('base_price')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('amount')->default(0); // Final amount paid

            // Payment tracking
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();

            // Wallet used for payment (if any)
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();

            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Status
            $table->string('status')->default('pending'); // pending, active, expired, cancelled, upgraded

            // Previous subscription (for tracking upgrade path)
            $table->foreignId('previous_subscription_id')
                ->nullable()
                ->constrained('user_subscriptions')
                ->nullOnDelete();

            // Sponsor (who paid for this subscription - nullable for self-paid)
            $table->nullableMorphs('sponsor');

            // Metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'expires_at']);
            $table->index(['stage_id', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
