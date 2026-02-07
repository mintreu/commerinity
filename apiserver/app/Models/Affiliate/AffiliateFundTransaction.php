<?php

declare(strict_types=1);

namespace App\Models\Affiliate;

use App\Casts\FundTransactionTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AffiliateFundTransaction extends Model
{
    use HasFactory;

    protected $table = 'affiliate_fund_transactions';

    protected $fillable = [
        'fund_account_id',
        'source_type',
        'source_id',
        'type',
        'amount',
        'balance_after',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'type' => FundTransactionTypeCast::class,
            'meta' => 'array',
        ];
    }

    public function fundAccount(): BelongsTo
    {
        return $this->belongsTo(AffiliateFundAccount::class, 'fund_account_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
