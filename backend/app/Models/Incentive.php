<?php

namespace App\Models;

use App\Casts\IncentiveTypeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelTransaction\Models\Transaction;

class Incentive extends Model
{
    protected $fillable = [
        'transaction_id',
        'incentivable_id',
        'incentivable_type',
        'sourceable_id',
        'sourceable_type',
        'type',
        'depth',
        'metadata',
    ];

    protected $casts = [
        'depth'    => 'integer',
        'metadata' => 'array',
        'type'     => IncentiveTypeCast::class,
    ];

    /**
     * The user/staff/etc. who received the incentive.
     */
    public function incentivable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user/order/etc. that generated the incentive.
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The transaction this incentive belongs to.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
