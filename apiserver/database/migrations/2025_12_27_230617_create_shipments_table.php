<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pickup_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->unsignedInteger('total_quantity')->default(0);
            $table->string('status')->default('processing');
            $table->string('shipping_method')->nullable();
            $table->string('provider')->default('native');
            $table->unsignedBigInteger('shipping_provider_id')->nullable();
            $table->string('provider_channel_id')->nullable();
            $table->string('provider_order_id')->nullable();
            $table->string('shipment_id')->nullable();
            $table->string('tracking_id')->nullable();
            $table->json('tracking_data')->nullable();
            $table->json('shipment_track_activities')->nullable();
            $table->json('last_update')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('return_initiated_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('cod')->default(false);
            $table->unsignedBigInteger('cod_amount')->default(0);
            $table->string('cod_status')->default('pending');
            $table->timestamp('cod_collected_at')->nullable();
            $table->timestamp('cod_remitted_at')->nullable();
            $table->unsignedBigInteger('charge')->default(0);
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['tracking_id']);
            $table->index(['status']);
            $table->index(['provider']);
            $table->index(['cod', 'cod_status']);
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            $table->unique(['shipment_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
