<?php

namespace Mintreu\LaravelCommerinity\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VoucherCodeUsage extends Pivot
{
    protected $table = 'voucher_code_usages';

    protected $fillable = [
        'voucher_code_id',
        'userable_id',
        'userable_type',
        'times_used',
    ];

    public function voucherCode()
    {
        return $this->belongsTo(VoucherCode::class, 'voucher_code_id');
    }

    public function userable()
    {
        return $this->morphTo();
    }
}


