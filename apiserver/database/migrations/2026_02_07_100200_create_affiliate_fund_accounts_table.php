<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_fund_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('fund_type');
            $table->integer('balance')->default(0);
            $table->integer('total_credited')->default(0);
            $table->integer('total_debited')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'fund_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_fund_accounts');
    }
};
