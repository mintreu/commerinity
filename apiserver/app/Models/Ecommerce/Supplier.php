<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Models\Traits\HasAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\Ecommerce\SupplierFactory> */
    use HasFactory;
    use HasAddress;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'contact_person',
        'gst_number',
        'tax_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get product stocks provided by this supplier
     */
    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }
}
