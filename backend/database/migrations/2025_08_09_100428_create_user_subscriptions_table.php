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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('amount')->default(0);

            $table->boolean('is_paid')->default(false);
            $table->timestamp('expire_at')->nullable();
            $table->timestamp('checkout_expires_at')->nullable();


            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnUpdate()->cascadeOnDelete();

            $table->timestamps();
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
