<?php

declare(strict_types=1);

use App\Casts\JobApplicationStatusCast;
use App\Casts\OrderStatusCast;
use App\Casts\ShipmentStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Casts\UserTypeCast;
use App\Models\Address;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\Shipment;
use App\Models\Ecommerce\ShipmentItem;
use App\Models\Membership\Stage;
use App\Models\Membership\Level;
use App\Models\Membership\UserSubscription;
use App\Models\Recruitment\JobApplication;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Events\PaymentCompleted;
use App\Jobs\Affiliate\CalculateCommissionsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Database\Seeders\StageSeeder;
use Database\Seeders\LevelSeeder;

uses(RefreshDatabase::class);

/**
 * Complete Order Flow Integration Test
 *
 * Tests the complete end-to-end flow for e-commerce orders:
 * 1. User has active subscription (required for affiliate commissions)
 * 2. Order is created with items
 * 3. Payment transaction is created
 * 4. Payment is completed (simulated)
 * 5. Order is confirmed
 * 6. Stock is consumed
 * 7. Shipments are created
 * 8. Invoices are generated
 * 9. Affiliate commissions are queued (for members)
 */

beforeEach(function () {
    // Create payment integration
    $this->integration = \App\Models\Integration::factory()->cashfree()->create();
    app(\App\Services\IntegrationServices\Payment\PaymentService::class)->refreshProviders();

    // Create user with wallet
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->create([
        'walletable_type' => User::class,
        'walletable_id' => $this->user->id,
    ]);

    // Create addresses using for() factory method with relationship name
    $this->shippingAddress = Address::factory()->for($this->user, 'addressable')->create();
    $this->billingAddress = Address::factory()->for($this->user, 'addressable')->create();

    $this->seed([StageSeeder::class, LevelSeeder::class]);

    // Create product with stock
    $this->product = Product::factory()->create();
    $this->stock = ProductStock::factory()->create([
        'product_id' => $this->product->id,
        'init_quantity' => 100,
        'sold_quantity' => 0,
    ]);

    // Create pickup address for stock (standalone warehouse address)
    $this->pickupAddress = Address::factory()->warehouse()->create();
    $this->stock->update(['address_id' => $this->pickupAddress->id]);
    $this->stock->update(['address_id' => $this->pickupAddress->id]);

    // Mock Cashfree API for order payment
    Http::fake(['sandbox.cashfree.com/pg/orders' => Http::response([
        'cf_order_id' => 'cf_order_'.fake()->uuid(),
        'order_id' => '*',
        'payment_session_id' => 'session_'.fake()->uuid(),
        'order_status' => 'ACTIVE',
    ], 200)]);
});

describe('Order Flow - Regular User (No Subscription)', function () {
    it('creates order without affiliate commissions for regular user', function () {
        // Create pending order
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'subtotal' => 10000,
            'shipping_cost' => 5000,
            'tax' => 1800,
            'discount' => 0,
            'total' => 16800,
            'total_bv' => 5000, // Business Volume for potential commissions
            'total_pv' => 10000,
            'shipping_address_id' => $this->shippingAddress->id,
            'quantity' => 1,
        ]);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'total_price' => 10000,
            'bv' => 5000,
            'pv' => 10000,
        ]);

        // Create pending transaction for order
        $transaction = Transaction::create([
            'uuid' => 'TXN-ORDER-REG-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 16800,
            'purpose' => 'Order Payment',
            'payment_method' => 'cashfree',
        ]);

        // Verify order is pending
        expect($order->fresh()->status->value)->toBe('pending');
        expect($this->stock->fresh()->sold_quantity)->toBe(0);

        // Simulate payment completion (fire event directly)
        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true,
            'verified_at' => now(),
        ]);

        event(new PaymentCompleted($transaction));

        // Verify order is confirmed
        $order->refresh();
        expect($order->status->value)->toBe('confirmed');
        expect($order->payment_success)->toBeTrue();

        // Verify stock was consumed
        $stock = $this->stock->fresh();
        expect($stock->sold_quantity)->toBe(1);

        // Verify shipment was created
        expect($order->shipments)->toHaveCount(1);
        $shipment = $order->shipments->first();
        expect($shipment->status->value)->toBe('processing');
        expect($shipment->total_quantity)->toBe(1);
        expect($shipment->pickup_address_id)->toBe($this->pickupAddress->id);

        // Verify shipment item was created
        expect(ShipmentItem::where('shipment_id', $shipment->id)->count())->toBe(1);

        // Verify invoice was created
        expect($shipment->invoice)->not->toBeNull();
        expect($shipment->invoice->uuid)->not->toBeEmpty();

        // Verify NO commissions were created (user has no subscription)
        expect(AffiliateCommission::count())->toBe(0);
        expect($order->fresh()->commission_processed)->toBeFalse();
    });
});

describe('Order Flow - Member User (With Subscription)', function () {
    beforeEach(function () {
        // Use seeded membership stage and level
        $this->stage = Stage::query()->where('slug', 'pro')->first()
            ?? Stage::query()->orderBy('sort_order')->first();
        $this->level = $this->stage?->getFirstLevel();

        // Upgrade user to member
        $this->user->update(['type' => UserTypeCast::MEMBER->value]);

        // Create active subscription
        $this->subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->atLevel($this->level)
            ->active()
            ->create();
    });

    it('creates order with queued affiliate commissions for member user', function () {
        Bus::fake();

        // Create affiliate structure (sponsor upline)
        $sponsor = User::factory()->create(['type' => UserTypeCast::MEMBER->value]);
        $this->user->update(['parent_id' => $sponsor->id]);
        AffiliateGenealogy::createForUser($sponsor->id);
        AffiliateGenealogy::createForUser($this->user->id);

        // Create pending order
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'subtotal' => 20000,
            'shipping_cost' => 5000,
            'tax' => 3600,
            'discount' => 1000,
            'total' => 27600,
            'total_bv' => 10000, // Business Volume for affiliate commissions
            'total_pv' => 20000,
            'shipping_address_id' => $this->shippingAddress->id,
            'quantity' => 2,
        ]);

        // Create order items
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 10000,
            'total_price' => 20000,
            'bv' => 5000,
            'pv' => 10000,
        ]);

        // Create pending transaction
        $transaction = Transaction::create([
            'uuid' => 'TXN-ORDER-MEM-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 27600,
            'purpose' => 'Order Payment',
            'payment_method' => 'cashfree',
        ]);

        // Verify order can generate commissions
        expect($order->canGenerateCommission())->toBeTrue();

        // Simulate payment completion
        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true,
            'verified_at' => now(),
        ]);

        event(new PaymentCompleted($transaction));

        // Verify order is confirmed
        $order->refresh();
        expect($order->status->value)->toBe('confirmed');

        // Verify stock was consumed
        expect($this->stock->fresh()->sold_quantity)->toBe(2);

        // Verify shipments were created
        expect($order->shipments)->toHaveCount(1);

        Bus::assertDispatched(CalculateCommissionsJob::class, function ($job) use ($order) {
            return $job->trigger->getId() === $order->id;
        });
        expect(AffiliateCommission::count())->toBe(0);
    });

    it('order without BV does not generate commissions', function () {
        // Create order with zero BV
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'subtotal' => 10000,
            'total' => 11800,
            'total_bv' => 0, // No BV = no commissions
            'total_pv' => 0,
            'shipping_address_id' => $this->shippingAddress->id,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 10000,
            'total_price' => 10000,
            'bv' => 0,
            'pv' => 0,
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-NO-BV-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 11800,
            'purpose' => 'Order Payment',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify no commissions were created
        expect(AffiliateCommission::count())->toBe(0);
    });
});

describe('Order Flow - Multiple Shipments', function () {
    it('creates separate shipments for different pickup addresses', function () {
        // Create multiple stock entries at different locations
        $pickupAddress2 = Address::factory()->warehouse()->create();
        $stock2 = ProductStock::factory()->create([
            'product_id' => $this->product->id,
            'init_quantity' => 50,
            'sold_quantity' => 0,
            'address_id' => $pickupAddress2->id,
        ]);
        $this->stock->update([
            'init_quantity' => 30,
            'sold_quantity' => 0,
        ]);

        // Create order requiring 75 units (will split across both stocks)
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'subtotal' => 75000,
            'total' => 88500,
            'total_bv' => 37500,
            'shipping_address_id' => $this->shippingAddress->id,
            'quantity' => 75,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 75,
            'unit_price' => 1000,
            'total_price' => 75000,
            'bv' => 500,
            'pv' => 1000,
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-MULTI-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 88500,
            'purpose' => 'Order Payment',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify 2 shipments were created (one per pickup address)
        expect($order->fresh()->shipments)->toHaveCount(2);

        // Verify stock allocation
        $pickupIds = $order->shipments->pluck('pickup_address_id')->sort()->values();
        expect($pickupIds[0])->toBe($this->pickupAddress->id);
        expect($pickupIds[1])->toBe($pickupAddress2->id);
    });
});

describe('Order Flow - Error Cases', function () {
    it('throws error when stock is insufficient', function () {
        // Create order requiring more stock than available
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'subtotal' => 200000,
            'total' => 236000,
            'shipping_address_id' => $this->shippingAddress->id,
            'quantity' => 200, // More than 100 available
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 200,
            'unit_price' => 1000,
            'total_price' => 200000,
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-OVER-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 236000,
            'purpose' => 'Order Payment',
        ]);

        // Expect exception during payment completion
        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);

        expect(fn () => event(new PaymentCompleted($transaction)))
            ->toThrow(\Exception::class, 'Insufficient stock');
    });

    it('handles order without transaction gracefully', function () {
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'total' => 16800,
            'shipping_address_id' => $this->shippingAddress->id,
        ]);

        // Create transaction without order relationship
        $transaction = Transaction::create([
            'uuid' => 'TXN-NO-ORDER-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => 999999,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 16800,
            'purpose' => 'Unknown',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Order should remain pending
        expect($order->fresh()->status->value)->toBe('pending');
    });
});

describe('Order Flow - Commission Context', function () {
    beforeEach(function () {
        // Use seeded membership stage and level
        $this->stage = Stage::query()->where('slug', 'pro')->first()
            ?? Stage::query()->orderBy('sort_order')->first();
        $this->level = $this->stage?->getFirstLevel();
        $this->user->update(['type' => UserTypeCast::MEMBER->value]);
        $this->subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->atLevel($this->level)
            ->active()
            ->create();
    });

    it('correctly provides commission context', function () {
        $order = Order::create([
            'customerable_type' => User::class,
            'customerable_id' => $this->user->id,
            'status' => OrderStatusCast::PENDING->value,
            'subtotal' => 15000,
            'total' => 17700,
            'total_bv' => 7500,
            'total_pv' => 15000,
            'total_reward_points' => 150,
            'shipping_address_id' => $this->shippingAddress->id,
            'quantity' => 3,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 5000,
            'total_price' => 15000,
            'bv' => 2500,
            'pv' => 5000,
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-CTX-' . fake()->randomNumber(6),
            'transactionable_type' => Order::class,
            'transactionable_id' => $order->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 17700,
            'purpose' => 'Order Payment',
            'payment_method' => 'cashfree',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify commission context is correct
        $context = $order->refresh()->getCommissionContext();
        expect($context)->toMatchArray([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total' => 17700,
            'total_bv' => 7500,
            'total_pv' => 15000,
            'total_reward_points' => 150,
            'item_count' => 3,
            'status' => 'confirmed',
        ]);
    });
});
