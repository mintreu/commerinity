<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'in_stock_quantity' => 'integer',
            'in_stock' => 'boolean',
            'low_stock_threshold' => 'integer',
            'notify_on_low_stock' => 'boolean',
            'priority' => 'integer'
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

    public function minStock(int $count): int
    {
        return min($this->in_stock_quantity, $count);
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
