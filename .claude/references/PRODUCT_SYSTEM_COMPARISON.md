# Product System - Best of Both Worlds

## 🎯 **Quick Answer**

**YES! You should absolutely use BOTH projects:**

| Feature | Use From | Why |
|---------|----------|-----|
| **Base Architecture** | Popkult | Clean, simple, modern |
| **Stock Management** | Popkult | Multi-warehouse, DB constraints |
| **Query Performance** | Popkult | Scopes, N+1 prevention |
| **Smart Variant Updates** | Old Commerinity | Brilliant signature-based algorithm |
| **Filament Stock UI** | Popkult | Stock adjustment, priority management |
| **Money Handling** | Popkult | MoneyPHP precision (integers) |

---

## 🏗️ **Recommended Architecture**

### Product Model Structure
```php
// App\Models\Catalogue\Product.php

namespace App\Models\Catalogue;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic (from Popkult - clean)
        'name', 'sku', 'url', 'status', 'type',
        'parent_id', 'category_id', 'filter_group_id',

        // Content
        'description', 'short_description',

        // Pricing (paise as integers)
        'price',
        'gst_tax_type',

        // Media
        'product_display_id',

        // Optional (add only if needed)
        'min_quantity', 'max_quantity',
        'is_returnable',
        'width', 'height', 'length', 'weight',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'type' => ProductType::class,
            'gst_tax_type' => GstTaxRate::class,
            'price' => 'integer',  // Paise
            'view_count' => 'integer',
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
            ->withPivot('filter_id');
    }

    // === STOCK MANAGEMENT (from Popkult) ===

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function availableStocks(): HasMany
    {
        return $this->stocks()
            ->where('in_stock', true)
            ->orderBy('priority');
    }

    // === MEDIA (Curator) ===

    public function productDisplay(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'product_display_id');
    }

    public function productGallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_gallery_media');
    }

    // === CART/ORDERS ===

    public function cartedBy(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'cart_customer')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // === QUERY SCOPES (from Popkult) ===

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

    // === HELPER METHODS (from Popkult) ===

    public function totalStock(): int
    {
        return $this->stocks_sum_in_stock_quantity
            ?? $this->stocks()->sum('in_stock_quantity');
    }

    public function minStock(int $count): int
    {
        $available = $this->totalStock();
        return min($available, $count);
    }

    public function preferredWarehouseAddress(): ?Address
    {
        return $this->availableStocks()
            ->with('address')
            ->first()
            ?->address;
    }
}
```

---

## 📦 **Stock Management Model**

```php
// App\Models\Catalogue\ProductStock.php

namespace App\Models\Catalogue;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'init_quantity',
        'sold_quantity',
        'address_id',
        'priority',
        'low_stock_threshold',
        'notify_on_low_stock',
    ];

    protected function casts(): array
    {
        return [
            'init_quantity' => 'integer',
            'sold_quantity' => 'integer',
            'in_stock_quantity' => 'integer',  // DB computed
            'in_stock' => 'boolean',           // DB computed
            'priority' => 'integer',
            'low_stock_threshold' => 'integer',
            'notify_on_low_stock' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function inStock(): bool
    {
        return $this->in_stock;
    }

    public function isLowStock(): bool
    {
        return $this->in_stock_quantity <= $this->low_stock_threshold;
    }

    public function shouldNotify(): bool
    {
        return $this->notify_on_low_stock && $this->isLowStock();
    }
}
```

---

## 🛠️ **Services Structure**

```php
app/Services/Catalogue/
├── ProductService.php          # Main orchestration
├── ProductCreationService.php  # From Popkult (simple)
├── ProductUpdateService.php    # From Commerinity (smart variant updates)
├── StockService.php           # From Popkult (multi-warehouse)
└── VariantGenerator.php       # Cartesian product logic
```

---

## ✅ **What to Copy from Each**

### From Popkult (Foundation):
✅ Simple Product model (13 fields vs 67)
✅ ProductStock table with computed columns
✅ Database CHECK constraints
✅ Query scopes (withStockInfo, purchasable, etc.)
✅ Filament ManageProductStocks page
✅ Stock adjustment action
✅ Sync with variants action
✅ MoneyService pattern

### From Old Commerinity (Smart Logic):
✅ Smart variant update algorithm (signature-based)
✅ Cartesian product generator (for variants)
✅ Filter option attachment logic
✅ Variant duplicate prevention
✅ Dynamic filter selection UI

---

## 🎯 **Final Recommendation**

**Use Popkult as BASE + Add Commerinity's Smart Algorithms**

### Your Product System Will Have:

1. ✅ **Clean model** (Popkult's simplicity)
2. ✅ **Multi-warehouse stock** (Popkult's innovation)
3. ✅ **Smart variant updates** (Commerinity's intelligence)
4. ✅ **Query performance** (Popkult's scopes)
5. ✅ **Modern admin UI** (Popkult's Filament patterns)
6. ✅ **Money precision** (Popkult's MoneyPHP)
7. ✅ **Flexible filtering** (Both use 3-tier, Popkult cleaner)

---

## 📊 **Structure in Your Project**

```
app/
├── Models/
│   └── Catalogue/
│       ├── Product.php              # Main model (Popkult base)
│       ├── ProductStock.php         # Multi-warehouse (Popkult)
│       ├── Category.php
│       ├── FilterGroup.php
│       ├── Filter.php
│       └── FilterOption.php
│
├── Services/
│   └── Catalogue/
│       ├── ProductService.php       # Orchestration
│       ├── ProductCreationService.php  # Popkult approach
│       ├── ProductUpdateService.php    # Commerinity smart logic
│       ├── StockService.php         # Popkult multi-warehouse
│       └── VariantGenerator.php     # Cartesian product
│
├── Filament/
│   └── Resources/
│       └── Catalogue/
│           ├── ProductResource.php
│           └── Pages/
│               ├── CreateProduct.php     # Popkult wizard
│               ├── EditProduct.php       # Popkult tabs + Commerinity filters
│               ├── ManageVariants.php    # Custom page
│               └── ManageProductStocks.php  # Popkult page
│
└── Enums/
    └── Catalogue/
        ├── ProductStatus.php
        ├── ProductType.php
        └── GstTaxRate.php
```

---

**This gives you the BEST product management system possible!** 🚀

Ready to document this in the plans folder?
