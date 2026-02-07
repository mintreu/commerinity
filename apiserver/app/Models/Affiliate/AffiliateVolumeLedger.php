<?php

declare(strict_types=1);

namespace App\Models\Affiliate;

use App\Casts\AffiliateVolumeStatusCast;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AffiliateVolumeLedger extends Model
{
    use HasFactory;

    protected $table = 'affiliate_volume_ledgers';

    protected $fillable = [
        'uuid',
        'user_id',
        'source_type',
        'source_id',
        'order_id',
        'order_item_id',
        'depth',
        'bv',
        'pv',
        'status',
        'eligible_at',
        'confirmed_at',
        'reversed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'bv' => 'integer',
            'pv' => 'integer',
            'status' => AffiliateVolumeStatusCast::class,
            'eligible_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'reversed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AffiliateVolumeLedger $ledger): void {
            if (empty($ledger->uuid)) {
                $ledger->uuid = Str::uuid()->toString();
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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
