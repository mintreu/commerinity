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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('uuid');
            $table->integer('amount');
            $table->integer('subtotal'); // subtotal  base_price
            $table->integer('discount'); // subtotal discount
            $table->integer('tax'); // subtotal tax
            $table->integer('total'); // subtotal final price
            $table->integer('quantity'); // total quantity
            $table->string('voucher')->nullable();
            $table->boolean('is_cod')->default(false);
            $table->string('tracking_id')->nullable(); // can be deleted

            $table->string('status')->default('pending');
            $table->boolean('payment_success')->default(false);
            $table->dateTime('expire_at')->nullable();


            $table->nullableMorphs('customerable');

            // Guest Case
            $table->boolean('has_guest')->default(false);
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_mobile')->nullable();

            // billing address
            $table->boolean('shipping_is_billing')->default(true);
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->onUpdate('cascade')->onDelete('cascade');

            // delivery/shipping address
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->cascadeOnUpdate()->cascadeOnDelete();


            $table->timestamps();
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
