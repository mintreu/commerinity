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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic owner (User, Merchant, etc.)
            $table->morphs('walletable');

            // All amounts in paisa (smallest unit)
            // 1 Rupee = 100 Paisa, so ₹500.50 = 50050 paisa
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('hold_balance')->default(0);
            $table->unsignedBigInteger('total_credited')->default(0);
            $table->unsignedBigInteger('total_debited')->default(0);

            // Points system (loyalty, reward points)
            $table->unsignedBigInteger('points')->default(0);

            // Security
            $table->string('pin')->nullable(); // Hashed PIN
            $table->timestamp('pin_updated_at')->nullable();

            // Currency
            $table->string('currency', 3)->default('INR');

            // Status
            $table->string('status')->default('active');

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes (morphs already creates walletable index)
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
