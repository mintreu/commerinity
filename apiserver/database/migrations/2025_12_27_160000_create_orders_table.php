<?php

use App\Casts\OrderStatusCast;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('order_number')->unique();
            $table->nullableMorphs('customerable'); // Polymorphic: User/Admin/etc.

            // Order status: pending, confirmed, processing, shipped, delivered, cancelled, refunded
            $table->string('status')->default(OrderStatusCast::PENDING->value)->index();

            // Pricing (all in paise for precision)
            $table->unsignedBigInteger('subtotal')->default(0)->comment('Subtotal in paise');
            $table->unsignedBigInteger('shipping_cost')->default(0)->comment('Shipping cost in paise');
            $table->unsignedBigInteger('tax')->default(0)->comment('Tax in paise');
            $table->unsignedBigInteger('discount')->default(0)->comment('Discount in paise');
            $table->unsignedBigInteger('total')->default(0)->comment('Total in paise');

            // Affiliate totals
            $table->unsignedInteger('total_bv')->default(0)->comment('Total Business Volume');
            $table->unsignedInteger('total_pv')->default(0)->comment('Total Personal Volume');
            $table->unsignedInteger('total_reward_points')->default(0)->comment('Total reward points earned');
            $table->unsignedInteger('total_coins')->default(0)->comment('Total coins earned');
            $table->boolean('commission_processed')->default(false)->comment('Commission payout processed flag');

            // Addresses (snapshot at order time)
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->nullOnDelete();

            // Checkout Expire At
            $table->dateTime('expire_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('return_period_ends_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Additional info
            $table->string('voucher')->nullable()->comment('Applied coupon code');
            $table->string('tracking_id')->nullable()->comment('Shipment tracking');
            $table->boolean('payment_success')->default(false);
            $table->integer('quantity')->default(0)->comment('Total items');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // Performance indexes
            $table->index('customerable_id', 'idx_orders_customer');
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
