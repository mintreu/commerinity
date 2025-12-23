# Commission & Reward System
## MLM Commission Calculation with Product Rewards

---

## 🎯 **System Overview**

**Purpose**: Calculate and distribute commissions when users:
1. Purchase products (product-based rewards)
2. Recruit new members (membership commissions)
3. Achieve volume targets (business incentives)

**Critical**: Commissions must be **reversible** on returns/refunds

---

## 💰 **Commission Types**

### 1. **Affiliate Commission** (Direct Referral)
- **Trigger**: Direct referree makes purchase
- **Rate**: Product's `affiliate_commission_rate` (e.g., 5%)
- **Recipient**: Direct parent (upline level 1)

### 2. **Team Commission** (Downline Performance)
- **Trigger**: Anyone in downline makes purchase
- **Rate**: Product's `team_commission_rates` (e.g., {1: 3%, 2: 2%, 3: 1%})
- **Recipients**: All ancestors up to configured depth

### 3. **Business Commission** (Volume-Based)
- **Trigger**: Monthly team volume reaches threshold
- **Calculation**: Based on total sales volume + business_volume_points
- **Recipients**: Qualified team leaders

---

## 📊 **Database Schema**

### Commissions Table (Base)
```php
Schema::create('commissions', function (Blueprint $table) {
    $table->id();
    $table->string('type'); // affiliate, team, business
    $table->uuid('uuid')->unique();

    // Recipient
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Source
    $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

    // Amount
    $table->unsignedBigInteger('amount'); // Paise
    $table->decimal('rate', 5, 2)->nullable(); // Commission %
    $table->unsignedInteger('level')->nullable(); // For team commissions

    // Status
    $table->string('status')->default('pending'); // pending, paid, reversed
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('reversed_at')->nullable();
    $table->foreignId('reversed_by_commission_id')->nullable(); // Link to reversal

    // Metadata
    $table->json('metadata')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index(['order_id', 'status']);
    $table->index(['type', 'status']);
});
```

### Affiliate Commissions (Extends Base)
```php
// Uses Single Table Inheritance
// type = 'affiliate'
// level is NULL
// Direct relationship to parent
```

### Team Commissions
```php
// type = 'team'
// level = 1, 2, 3, etc. (upline depth)
// Multiple records per order (one per ancestor)
```

### Business Commissions
```php
// type = 'business'
// Calculated monthly based on team volume
// No direct order relationship
```

---

## 🛠️ **Models**

### Commission Model
```php
// app/Models/Commission/Commission.php

namespace App\Models\Commission;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'uuid', 'user_id', 'order_id', 'order_item_id', 'product_id',
        'amount', 'rate', 'level', 'status', 'paid_at', 'reversed_at',
        'reversed_by_commission_id', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommissionType::class,
            'status' => CommissionStatus::class,
            'amount' => 'integer',
            'rate' => 'decimal:2',
            'level' => 'integer',
            'paid_at' => 'datetime',
            'reversed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(Commission::class, 'reversed_by_commission_id');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === CommissionStatus::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === CommissionStatus::PAID;
    }

    public function isReversed(): bool
    {
        return $this->status === CommissionStatus::REVERSED;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => CommissionStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function reverse(Commission $reversalCommission): void
    {
        $this->update([
            'status' => CommissionStatus::REVERSED,
            'reversed_at' => now(),
            'reversed_by_commission_id' => $reversalCommission->id,
        ]);
    }
}
```

---

## 🎯 **Commission Service**

```php
// app/Services/Commission/CommissionService.php

namespace App\Services\Commission;

use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function __construct(
        protected CommissionCalculator $calculator,
        protected WalletService $walletService,
    ) {}

    public function processOrderCommissions(Order $order): Collection
    {
        if ($order->status !== OrderStatus::CONFIRMED) {
            throw new \Exception('Order must be confirmed to process commissions');
        }

        return DB::transaction(function () use ($order) {
            $commissions = collect();

            // Calculate all commissions
            $calculated = $this->calculator->calculate($order);

            // Create commission records
            foreach ($calculated as $data) {
                $commission = Commission::create($data);
                $commissions->push($commission);

                // Credit to wallet immediately
                $this->walletService->credit(
                    $commission->user,
                    $commission->amount,
                    "Commission from Order #{$order->order_number}",
                    $commission
                );

                // Mark as paid
                $commission->markAsPaid();
            }

            return $commissions;
        });
    }

    public function reverseOrderCommissions(Order $order): Collection
    {
        return DB::transaction(function () use ($order) {
            $reversed = collect();

            // Find all commissions for this order
            $commissions = Commission::where('order_id', $order->id)
                ->where('status', CommissionStatus::PAID)
                ->get();

            foreach ($commissions as $commission) {
                // Create negative commission (reversal)
                $reversal = Commission::create([
                    'type' => $commission->type,
                    'uuid' => Str::uuid(),
                    'user_id' => $commission->user_id,
                    'order_id' => $order->id,
                    'order_item_id' => $commission->order_item_id,
                    'product_id' => $commission->product_id,
                    'amount' => -$commission->amount, // Negative!
                    'rate' => $commission->rate,
                    'level' => $commission->level,
                    'status' => CommissionStatus::PAID,
                    'paid_at' => now(),
                    'metadata' => [
                        'reversal_of' => $commission->id,
                        'reason' => 'Order refund/return',
                    ],
                ]);

                // Debit from wallet
                $this->walletService->debit(
                    $commission->user,
                    $commission->amount,
                    "Commission reversal for Order #{$order->order_number}",
                    $reversal
                );

                // Mark original as reversed
                $commission->reverse($reversal);

                $reversed->push($reversal);
            }

            return $reversed;
        });
    }
}
```

---

## 🧮 **Commission Calculator**

```php
// app/Services/Commission/CommissionCalculator.php

namespace App\Services\Commission;

class CommissionCalculator
{
    public function calculate(Order $order): array
    {
        $commissions = [];
        $customer = $order->customer;

        foreach ($order->items as $item) {
            $product = $item->product;

            // Skip if no commission
            if (!$product->hasCommission()) {
                continue;
            }

            // 1. Affiliate Commission
            if ($product->affiliate_commission_rate > 0 && $customer->parent) {
                $commissions[] = $this->calculateAffiliate($customer, $order, $item, $product);
            }

            // 2. Team Commissions
            if (!empty($product->team_commission_rates)) {
                $teamCommissions = $this->calculateTeam($customer, $order, $item, $product);
                $commissions = array_merge($commissions, $teamCommissions);
            }
        }

        return $commissions;
    }

    protected function calculateAffiliate(User $customer, Order $order, OrderItem $item, Product $product): array
    {
        $amount = (int) round(($item->total * $product->affiliate_commission_rate) / 100);

        return [
            'type' => CommissionType::AFFILIATE,
            'uuid' => Str::uuid(),
            'user_id' => $customer->parent_id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'product_id' => $product->id,
            'amount' => $amount,
            'rate' => $product->affiliate_commission_rate,
            'level' => null,
            'status' => CommissionStatus::PENDING,
        ];
    }

    protected function calculateTeam(User $customer, Order $order, OrderItem $item, Product $product): array
    {
        $commissions = [];

        // Get all ancestors (MLM tree)
        $ancestors = $customer->ancestors()->get(); // Adjacency list

        foreach ($ancestors as $index => $ancestor) {
            $level = $index + 1;
            $rate = $product->getTeamCommissionForLevel($level);

            if ($rate > 0) {
                $amount = (int) round(($item->total * $rate) / 100);

                $commissions[] = [
                    'type' => CommissionType::TEAM,
                    'uuid' => Str::uuid(),
                    'user_id' => $ancestor->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'product_id' => $product->id,
                    'amount' => $amount,
                    'rate' => $rate,
                    'level' => $level,
                    'status' => CommissionStatus::PENDING,
                ];
            }
        }

        return $commissions;
    }
}
```

---

## 🔄 **Integration with Order Flow**

```php
// When order is confirmed (payment successful)

OrderObserver->updated(Order $order)
{
    if ($order->wasChanged('status') && $order->status === OrderStatus::CONFIRMED) {
        // Process commissions
        app(CommissionService::class)->processOrderCommissions($order);

        // Send notifications
        $order->customer->notify(new OrderConfirmedNotification($order));
    }
}

// When order is refunded

RefundOrderAction->execute(Order $order)
{
    DB::transaction(function () use ($order) {
        // 1. Process refund
        $payment = $order->payments()->latest()->first();
        app(PaymentService::class)->refund($payment);

        // 2. Reverse commissions
        app(CommissionService::class)->reverseOrderCommissions($order);

        // 3. Update order status
        $order->update(['status' => OrderStatus::REFUNDED]);
    });
}
```

---

## ✅ **Perfect! Auth Package Confirmed**

**We'll use**: `@qirolab/nuxt-sanctum-authentication`

Now creating remaining detailed plans... 🚀