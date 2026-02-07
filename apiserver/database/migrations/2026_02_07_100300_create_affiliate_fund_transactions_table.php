<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_account_id')->constrained('affiliate_fund_accounts')->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('type');
            $table->integer('amount')->default(0);
            $table->integer('balance_after')->default(0);
            $table->string('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['fund_account_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_fund_transactions');
    }
};
