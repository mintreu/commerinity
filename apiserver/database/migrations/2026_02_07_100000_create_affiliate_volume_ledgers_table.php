<?php

declare(strict_types=1);

use App\Casts\AffiliateVolumeStatusCast;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_volume_ledgers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->unsignedTinyInteger('depth')->default(0); // 0 = self, 1..n = upline depth
            $table->integer('bv')->default(0);
            $table->integer('pv')->default(0);
            $table->string('status')->default(AffiliateVolumeStatusCast::PENDING->value);
            $table->timestamp('eligible_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['confirmed_at']);
            $table->index(['eligible_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_volume_ledgers');
    }
};
