<?php

namespace App\Models;

use App\Casts\TaxTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxCode extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'tax_codes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code', // hsn code
        'type',
        'description',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'cess_rate',
        'is_active',
    ];

    protected $casts = [
        'type' => TaxTypeCast::class,
    ];

    /**
     * Relationships
     * A tax code can be linked to multiple products.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Accessor: Total GST percentage for display or calculation.
     */
    public function getTotalGstRateAttribute(): float
    {
        return round($this->cgst_rate + $this->sgst_rate + $this->igst_rate + $this->cess_rate, 2);
    }

    /**
     * Scope: Only active tax codes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper: Determine if tax code applies to goods or services.
     */
    public function isGoods(): bool
    {
        return $this->type === 'goods';
    }

    public function isService(): bool
    {
        return $this->type === 'services';
    }
}
