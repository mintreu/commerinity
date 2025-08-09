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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('provider_gen_id')->unique();    // in case of easebuzz : txnid
            $table->string('provider_transaction_id')->nullable(); // in case of easebuzz : easepayid
            $table->integer('amount')->default(0);
            $table->boolean('verified')->default(false);
            $table->morphs('payable'); // models like subscriptions, transactions, user_subscriptions, recruitment_fees

            $table->json('provider_data')->nullable();

            $table->string('success_url')->nullable();

            $table->foreignId('provider_id')->constrained('providers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
