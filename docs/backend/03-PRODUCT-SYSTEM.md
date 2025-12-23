# Product System with Rewards Integration
## Best of Popkult + Old Commerinity

---

## 🎯 **System Goals**

1. ✅ **Multi-warehouse inventory** (from Popkult)
2. ✅ **Smart variant management** (from Commerinity)
3. ✅ **Product-based commissions** (for MLM rewards)
4. ✅ **3-tier filtering** (flexible attributes)
5. ✅ **Money precision** (MoneyPHP, paise storage)
6. ✅ **Performance optimized** (query scopes, N+1 prevention)

---

## 📊 **Database Schema**

### Products Table
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();

    // Basic Info
    $table->string('name');
    $table->string('sku')->unique();
    $table->string('url')->unique();
    $table->string('type')->default('simple'); // simple, configurable
    $table->string('status')->default('draft'); // draft, review, published

    // Relationships
    $table->foreignId('parent_id')->nullable()->constrained('products')->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('filter_group_id')->nullable()->constrained()->nullOnDelete();

    // Content
    $table->text('description')->nullable();
    $table->text('short_description')->nullable();

    // Pricing (in paise - integers)
    $table->unsignedBigInteger('price')->nullable();

    // Tax
    $table->string('gst_tax_type')->nullable(); // NONE, GST_5, GST_18, GST_40

    // Quantity Limits
    $table->unsignedInteger('min_quantity')->default(1);
    $table->unsignedInteger('max_quantity')->nullable();

    // Product Attributes
    $table->boolean('is_returnable')->default(true);
    $table->decimal('weight', 8, 2)->nullable(); // For shipping

    // === MLM REWARD CONFIGURATION ⭐ ===
    $table->decimal('affiliate_commission_rate', 5, 2)->default(0); // e.g., 5.00 = 5%
    $table->json('team_commission_rates')->nullable(); // {1: 3, 2: 2, 3: 1}
    $table->unsignedInteger('business_volume_points')->default(0);

    // Media
    $table->foreignId('product_display_id')->nullable(); // Curator media

    // Analytics
    $table->unsignedBigInteger('view_count')->default(0);

    $table->timestamps();

    // Indexes
    $table->index(['status', 'created_at']);
    $table->index('category_id');
    $table->index('parent_id');
});
```

### Product Stocks Table (Multi-Warehouse)
```php
Schema::create('product_stocks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();

    // Stock Tracking
    $table->unsignedInteger('init_quantity')->default(0);
    $table->unsignedInteger('sold_quantity')->default(0);

    // === COMPUTED COLUMNS (Database-level) ===
    $table->unsignedInteger('in_stock_quantity')
          ->storedAs('CAST(init_quantity AS SIGNED) - CAST(sold_quantity AS SIGNED)');
    $table->boolean('in_stock')
          ->storedAs('IF(in_stock_quantity > 0, true, false)');

    // Warehouse
    $table->foreignId('address_id')->constrained();
    $table->unsignedInteger('priority')->default(1); // Lower = higher priority

    // Alerts
    $table->unsignedInteger('low_stock_threshold')->default(5);
    $table->boolean('notify_on_low_stock')->default(true);

    $table->timestamps();

    // === DATABASE CONSTRAINT (Prevent overselling) ===
    $table->check('sold_quantity <= init_quantity');

    // Indexes
    $table->index(['product_id', 'in_stock']);
    $table->index('priority');
});
```

### Filter System (3-Tier)
```php
// Filter Groups
Schema::create('filter_groups', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('url')->unique();
    $table->timestamps();
});

// Filters
Schema::create('filters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('filter_group_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->boolean('is_required')->default(false);
    $table->unsignedInteger('order')->default(0);
    $table->timestamps();
});

// Filter Options
Schema::create('filter_options', function (Blueprint $table) {
    $table->id();
    $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
    $table->string('value');
    $table->string('swatch_value')->nullable(); // For colors/patterns
    $table->unsignedInteger('order')->default(0);
    $table->timestamps();
});

// Product-Filter Pivot
Schema::create('product_filter_options', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('filter_option_id')->constrained()->cascadeOnDelete();
    $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['product_id', 'filter_option_id']);
});

// Product Gallery
Schema::create('product_gallery_media', function (Blueprint $table) {
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('media_id'); // Curator media
    $table->timestamps();

    $table->unique(['product_id', 'media_id']);
});
```

---

## 🏗️ **Models**

### Product Model
```php
// app/Models/Catalogue/Product.php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'url', 'status', 'type',
        'parent_id', 'category_id', 'filter_group_id',
        'description', 'short_description',
        'price', 'gst_tax_type',
        'min_quantity', 'max_quantity', 'is_returnable',
        'weight', 'product_display_id', 'view_count',
        // MLM Rewards
        'affiliate_commission_rate',
        'team_commission_rates',
        'business_volume_points',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'type' => ProductType::class,
            'gst_tax_type' => GstTaxRate::class,
            'price' => 'integer',
            'view_count' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'is_returnable' => 'boolean',
            'weight' => 'decimal:2',
            'affiliate_commission_rate' => 'decimal:2',
            'team_commission_rates' => 'array',
            'business_volume_points' => 'integer',
        ];
    }

    // === RELATIONSHIPS ===

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class);
    }

    public function filterOptions(): BelongsToMany
    {
        return $this->belongsToMany(FilterOption::class, 'product_filter_options')
            ->withPivot('filter_id')
            ->withTimestamps();
    }

    // === STOCK (Multi-warehouse from Popkult) ===

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function availableStocks(): HasMany
    {
        return $this->stocks()->where('in_stock', true)->orderBy('priority');
    }

    // === MEDIA ===

    public function productDisplay(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'product_display_id');
    }

    public function productGallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_gallery_media');
    }

    // === SCOPES (from Popkult - Performance) ===

    public function scopeWithStockInfo(Builder $query): Builder
    {
        return $query->withSum('stocks', 'in_stock_quantity')
            ->withCount(['stocks', 'availableStocks']);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::PUBLISHED);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereHas('availableStocks');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->whereDoesntHave('availableStocks');
    }

    // === HELPER METHODS ===

    public function totalStock(): int
    {
        return $this->stocks_sum_in_stock_quantity
            ?? $this->stocks()->sum('in_stock_quantity');
    }

    public function minStock(int $count): int
    {
        return min($this->totalStock(), $count);
    }

    public function preferredWarehouseAddress(): ?Address
    {
        return $this->availableStocks()->with('address')->first()?->address;
    }

    // === COMMISSION HELPERS ⭐ ===

    public function hasCommission(): bool
    {
        return $this->affiliate_commission_rate > 0
            || !empty($this->team_commission_rates);
    }

    public function getTeamCommissionForLevel(int $level): float
    {
        return (float) ($this->team_commission_rates[$level] ?? 0);
    }
}
```

---

## 🛠️ **Services**

### ProductCreationService (from Popkult)
```php
// app/Services/Catalogue/ProductCreationService.php

namespace App\Services\Catalogue;

class ProductCreationService
{
    public function __construct(
        protected VariantGenerator $variantGenerator
    ) {}

    public function create(array $data): Product
    {
        if ($data['type'] === 'configurable') {
            return $this->createConfigurable($data);
        }

        return $this->createSimple($data);
    }

    protected function createSimple(array $data): Product
    {
        $product = Product::create($data);

        // Attach filter options (if any)
        if (!empty($data['filter_options'])) {
            $this->attachFilterOptions($product, $data['filter_options']);
        }

        return $product;
    }

    protected function createConfigurable(array $data): Product
    {
        // 1. Create parent
        $parent = Product::create(array_merge($data, ['type' => 'configurable']));

        // 2. Attach parent filter options
        if (!empty($data['filter_options'])) {
            $this->attachFilterOptions($parent, $data['filter_options']);
        }

        // 3. Generate variants
        if (!empty($data['filter_options'])) {
            $this->variantGenerator->generate($parent, $data);
        }

        return $parent;
    }

    protected function attachFilterOptions(Product $product, array $filterOptions): void
    {
        $pivotData = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
            foreach ($optionIds as $optionId) {
                $pivotData[$optionId] = ['filter_id' => $filterId];
            }
        }

        $product->filterOptions()->sync($pivotData);
    }
}
```

### VariantGenerator (Cartesian Product)
```php
// app/Services/Catalogue/VariantGenerator.php

namespace App\Services\Catalogue;

class VariantGenerator
{
    public function generate(Product $parent, array $data): void
    {
        $variants = $this->generateVariantCombinations(
            $data['sku'],
            $data['filter_options']
        );

        foreach ($variants as $variant) {
            $this->createVariant($parent, $data, $variant);
        }
    }

    protected function generateVariantCombinations(string $baseSku, array $filterOptions): array
    {
        // Get filter option values
        $filterOptionsWithValues = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            $options = FilterOption::whereIn('id', $optionIds)
                ->pluck('value', 'id')
                ->toArray();
            $filterOptionsWithValues[$filterId] = $options;
        }

        // Cartesian product
        $combinations = $this->cartesianProduct($filterOptionsWithValues);

        // Build variant data
        return array_map(function ($combination) use ($baseSku) {
            $values = implode('-', array_values($combination));
            return [
                'sku' => strtoupper("{$baseSku}-{$values}"),
                'url' => strtolower(str_replace(' ', '-', "{$baseSku}-{$values}")),
                'filter_option_ids' => array_keys($combination),
            ];
        }, $combinations);
    }

    protected function cartesianProduct(array $arrays): array
    {
        if (empty($arrays)) {
            return [[]];
        }

        $result = [[]];
        foreach ($arrays as $options) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($options as $optionId => $optionValue) {
                    $newResult[] = $existing + [$optionId => $optionValue];
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    protected function createVariant(Product $parent, array $parentData, array $variantData): void
    {
        // Check if variant already exists
        if (Product::where('sku', $variantData['sku'])->exists()) {
            return;
        }

        $variant = Product::create([
            'parent_id' => $parent->id,
            'type' => 'simple',
            'name' => $parent->name,
            'sku' => $variantData['sku'],
            'url' => $variantData['url'],
            'status' => $parent->status,
            'category_id' => $parent->category_id,
            'filter_group_id' => $parent->filter_group_id,
            'description' => $parent->description,
            'short_description' => $parent->short_description,
            'price' => $parent->price,
            'gst_tax_type' => $parent->gst_tax_type,
            'min_quantity' => $parent->min_quantity,
            'max_quantity' => $parent->max_quantity,
            'is_returnable' => $parent->is_returnable,
            'weight' => $parent->weight,
            // === INHERIT COMMISSION RATES ===
            'affiliate_commission_rate' => $parent->affiliate_commission_rate,
            'team_commission_rates' => $parent->team_commission_rates,
            'business_volume_points' => $parent->business_volume_points,
        ]);

        // Attach filter options
        $variant->filterOptions()->attach($variantData['filter_option_ids'], [
            'filter_id' => $parent->filterOptions->pluck('pivot.filter_id', 'id')->toArray(),
        ]);
    }
}
```

### ProductUpdateService (Smart from Commerinity)
```php
// app/Services/Catalogue/ProductUpdateService.php

namespace App\Services\Catalogue;

class ProductUpdateService
{
    public function __construct(
        protected VariantGenerator $variantGenerator
    ) {}

    public function update(Product $product, array $data): Product
    {
        // Check if filter group changed
        $filterGroupChanged = isset($data['filter_group_id'])
            && $product->filter_group_id != $data['filter_group_id'];

        if ($product->type === 'configurable') {
            if ($filterGroupChanged) {
                // Recreate all variants
                $this->recreateVariants($product, $data);
            } else {
                // Smart update (from Commerinity)
                $this->smartUpdateVariants($product, $data);
            }
        }

        // Update product
        $product->update($data);

        // Update filter options
        if (isset($data['filter_options'])) {
            $this->updateFilterOptions($product, $data['filter_options']);
        }

        return $product->fresh();
    }

    protected function smartUpdateVariants(Product $product, array $data): void
    {
        if (!isset($data['filter_options'])) {
            return;
        }

        // 1. Get new option IDs (sorted)
        $newOptionIds = collect($data['filter_options'])
            ->flatten()
            ->map(fn($v) => (int) $v)
            ->sort()
            ->values()
            ->all();

        // 2. Get existing option IDs from variants (sorted)
        $existingOptionIds = $product->variants()
            ->with('filterOptions')
            ->get()
            ->flatMap(fn($v) => $v->filterOptions->pluck('id'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        // 3. No change? Skip!
        if ($newOptionIds === $existingOptionIds) {
            return;
        }

        // 4. Generate new variant combinations
        $newVariants = $this->variantGenerator->generateVariantCombinations(
            $data['sku'] ?? $product->sku,
            $data['filter_options']
        );

        $existingVariants = $product->variants()->with('filterOptions')->get();

        // 5. Create signatures for comparison
        $existingSignatures = $existingVariants->mapWithKeys(function ($variant) {
            $optionIds = $variant->filterOptions->pluck('id')->sort()->values()->all();
            $signature = implode('-', $optionIds);
            return [$signature => $variant];
        });

        $newSignatures = collect($newVariants)->mapWithKeys(function ($variant) {
            $signature = implode('-', collect($variant['filter_option_ids'])->sort()->values()->all());
            return [$signature => $variant];
        });

        // 6. Delete outdated variants
        $toDelete = $existingSignatures->keys()->diff($newSignatures->keys());
        foreach ($toDelete as $signature) {
            $existingSignatures[$signature]->delete();
        }

        // 7. Create new variants only
        $toCreate = $newSignatures->keys()->diff($existingSignatures->keys());
        foreach ($toCreate as $signature) {
            $this->variantGenerator->createVariant(
                $product,
                $data,
                $newSignatures[$signature]
            );
        }
    }

    protected function recreateVariants(Product $product, array $data): void
    {
        // Delete all variants
        $product->variants()->delete();

        // Regenerate
        if (!empty($data['filter_options'])) {
            $this->variantGenerator->generate($product, $data);
        }
    }

    protected function updateFilterOptions(Product $product, array $filterOptions): void
    {
        $pivotData = [];
        foreach ($filterOptions as $filterId => $optionIds) {
            $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
            foreach ($optionIds as $optionId) {
                $pivotData[$optionId] = ['filter_id' => $filterId];
            }
        }

        $product->filterOptions()->sync($pivotData);
    }
}
```

### StockService (Multi-Warehouse)
```php
// app/Services/Catalogue/StockService.php

namespace App\Services\Catalogue;

class StockService
{
    public function addStock(Product $product, int $warehouseId, int $quantity, int $priority = 1): ProductStock
    {
        return ProductStock::create([
            'product_id' => $product->id,
            'address_id' => $warehouseId,
            'init_quantity' => $quantity,
            'sold_quantity' => 0,
            'priority' => $priority,
        ]);
    }

    public function adjustStock(ProductStock $stock, int $adjustment, string $reason = null): bool
    {
        $newInitQuantity = $stock->init_quantity + $adjustment;

        // Validate: can't go below sold quantity
        if ($newInitQuantity < $stock->sold_quantity) {
            return false;
        }

        $stock->update(['init_quantity' => $newInitQuantity]);

        // Log adjustment (optional)
        // StockAdjustmentLog::create([...])

        return true;
    }

    public function consumeStock(Product $product, int $quantity): bool
    {
        return DB::transaction(function () use ($product, $quantity) {
            // Get available stocks ordered by priority
            $stocks = $product->availableStocks()
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;

            foreach ($stocks as $stock) {
                if ($remaining <= 0) {
                    break;
                }

                $toConsume = min($stock->in_stock_quantity, $remaining);
                $stock->increment('sold_quantity', $toConsume);
                $remaining -= $toConsume;
            }

            return $remaining === 0; // All consumed?
        });
    }

    public function getTotalStock(Product $product): int
    {
        return $product->stocks()->sum('in_stock_quantity');
    }

    public function checkAvailability(Product $product, int $quantity): bool
    {
        return $this->getTotalStock($product) >= $quantity;
    }
}
```

---

## 🎯 **Product Reward Integration**

### Commission Configuration
```php
// When creating/editing product in Filament:

Section::make('MLM Rewards Configuration')
    ->schema([
        TextInput::make('affiliate_commission_rate')
            ->label('Affiliate Commission (%)')
            ->numeric()
            ->suffix('%')
            ->minValue(0)
            ->maxValue(100)
            ->default(0)
            ->helperText('Direct referrer commission on purchase'),

        Repeater::make('team_commission_rates')
            ->label('Team Commission Rates')
            ->schema([
                TextInput::make('level')
                    ->label('Level')
                    ->numeric()
                    ->required(),
                TextInput::make('rate')
                    ->label('Commission (%)')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
            ])
            ->defaultItems(3)
            ->addActionLabel('Add Level')
            ->helperText('Upline commission rates by depth'),

        TextInput::make('business_volume_points')
            ->label('Business Volume Points')
            ->numeric()
            ->default(0)
            ->helperText('Points for volume-based bonuses'),
    ])
```

### Commission Calculation (When Order Completes)
```php
// app/Services/Commission/CommissionService.php

public function calculateProductCommissions(Order $order): Collection
{
    $commissions = collect();

    foreach ($order->items as $item) {
        $product = $item->product;
        $customer = $order->customer;

        // Skip if no commission configured
        if (!$product->hasCommission()) {
            continue;
        }

        // 1. Affiliate Commission (Direct referrer)
        if ($product->affiliate_commission_rate > 0 && $customer->parent) {
            $amount = ($item->total * $product->affiliate_commission_rate) / 100;

            $commissions->push(
                AffiliateCommission::create([
                    'user_id' => $customer->parent_id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'product_id' => $product->id,
                    'amount' => (int) round($amount), // Paise
                    'rate' => $product->affiliate_commission_rate,
                    'status' => 'pending',
                ])
            );
        }

        // 2. Team Commissions (Upline tree)
        $upline = $customer->ancestors()->get(); // Adjacency list
        foreach ($upline as $index => $ancestor) {
            $level = $index + 1;
            $rate = $product->getTeamCommissionForLevel($level);

            if ($rate > 0) {
                $amount = ($item->total * $rate) / 100;

                $commissions->push(
                    TeamCommission::create([
                        'user_id' => $ancestor->id,
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'product_id' => $product->id,
                        'amount' => (int) round($amount),
                        'rate' => $rate,
                        'level' => $level,
                        'status' => 'pending',
                    ])
                );
            }
        }
    }

    return $commissions;
}
```

---

## 📋 **Filament Resource**

### ManageProductStocks Page (from Popkult)
```php
// app/Filament/Resources/Catalogue/ProductResource/Pages/ManageProductStocks.php

class ManageProductStocks extends ManageRelatedRecords
{
    protected static string $resource = ProductResource::class;
    protected static string $relationship = 'stocks';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('address.name')->label('Warehouse'),
                TextColumn::make('init_quantity')->label('Initial'),
                TextColumn::make('sold_quantity')->label('Sold')->color('warning'),
                TextColumn::make('in_stock_quantity')->label('Available')
                    ->badge()
                    ->color(fn (int $state) => match(true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('priority')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // Stock Adjustment Action
                Tables\Actions\Action::make('adjust_stock')
                    ->label('Adjust')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        TextInput::make('adjustment')
                            ->numeric()
                            ->required()
                            ->helperText('Positive to add, negative to remove'),
                        Textarea::make('reason'),
                    ])
                    ->action(function (array $data, $record) {
                        app(StockService::class)->adjustStock(
                            $record,
                            (int) $data['adjustment'],
                            $data['reason'] ?? null
                        );

                        Notification::make()
                            ->success()
                            ->title('Stock Adjusted')
                            ->send();
                    }),
            ]);
    }
}
```

---

## 🔌 **API Endpoints**

```php
// routes/api.php

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product:sku}', [ProductController::class, 'show']);
    Route::post('/{product:sku}/views', [ProductController::class, 'incrementViews']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category:url}', [CategoryController::class, 'show']);
    Route::get('/{category:url}/products', [CategoryController::class, 'products']);
});
```

---

## ✅ **Summary**

### What We're Building:
```
Product System =
  Popkult's foundation (clean, multi-warehouse)
  + Commerinity's smart updates (signature-based)
  + MLM reward integration (commission rates per product)
  + Enterprise patterns (service layer, query scopes)
```

### Key Features:
1. ✅ Multi-warehouse inventory with priority
2. ✅ Smart variant updates (don't recreate unnecessarily)
3. ✅ Product-based commission configuration
4. ✅ Database constraints (prevent overselling)
5. ✅ Query scopes (performance)
6. ✅ Money precision (paise as integers)

---

**Status**: ✅ Product system designed
**Next**: Commission system detailed plan
