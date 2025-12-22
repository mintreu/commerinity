<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mintreu\LaravelTransaction\Casts\WalletStatusCast;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // explicit column name
            $table->string('pin', 60); // store hashed PIN instead of raw 6 digits
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->morphs('walletable');
            $table->unique(['walletable_id', 'walletable_type']);
            $table->string('currency', 3)->default('INR'); // fixed currency for India
            $table->string('status')->default(WalletStatusCast::ACTIVE);
            $table->timestamps();
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
