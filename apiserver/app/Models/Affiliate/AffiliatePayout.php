<?php

declare(strict_types=1);

namespace App\Models\Affiliate;

use App\Casts\AffiliatePayoutStatusCast;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AffiliatePayout extends Model
{
    use HasFactory;

    protected $table = 'affiliate_payouts';

    protected $fillable = [
        'uuid',
        'user_id',
        'period_start',
        'period_end',
        'pv',
        'bv',
        'gross_amount',
        'platform_fee',
        'platform_fee_gst',
        'tds_amount',
        'tcs_amount',
        'net_amount',
        'status',
        'paid_at',
        'transaction_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'pv' => 'integer',
            'bv' => 'integer',
        'gross_amount' => 'integer',
        'platform_fee' => 'integer',
        'platform_fee_gst' => 'integer',
        'tds_amount' => 'integer',
        'tcs_amount' => 'integer',
        'net_amount' => 'integer',
            'status' => AffiliatePayoutStatusCast::class,
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AffiliatePayout $payout): void {
            if (empty($payout->uuid)) {
                $payout->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
