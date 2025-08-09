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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('provider_gen_id')->unique();
            $table->string('provider_transaction_id')->nullable();
            $table->integer('amount')->default(0);
            $table->boolean('verified')->default(false);

            $table->morphs('payoutable'); // models like users, office, ops, admin, all auth models

            $table->text('notes')->nullable();
            $table->json('provider_data')->nullable();

            $table->foreignId('provider_id')->constrained('providers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
