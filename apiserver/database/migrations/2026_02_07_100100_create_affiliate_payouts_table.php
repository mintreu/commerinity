<?php

declare(strict_types=1);

use App\Casts\AffiliatePayoutStatusCast;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('pv')->default(0);
            $table->integer('bv')->default(0);
            $table->integer('gross_amount')->default(0);
            $table->integer('platform_fee')->default(0);
            $table->integer('platform_fee_gst')->default(0);
            $table->integer('tds_amount')->default(0);
            $table->integer('tcs_amount')->default(0);
            $table->integer('net_amount')->default(0);
            $table->string('status')->default(AffiliatePayoutStatusCast::PENDING->value);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'period_start', 'period_end']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
