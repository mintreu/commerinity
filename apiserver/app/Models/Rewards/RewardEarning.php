<?php

declare(strict_types=1);

namespace App\Models\Rewards;

use App\Casts\RewardStatusCast;
use App\Casts\RewardTypeCast;
use App\Models\Ecommerce\VoucherCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class RewardEarning extends Model
{
    use HasFactory;

    protected $table = 'reward_earnings';

    protected $fillable = [
        'uuid',
        'user_id',
        'source_type',
        'source_id',
        'reward_type',
        'coins',
        'voucher_code_id',
        'status',
        'is_used',
        'claimed_at',
        'expires_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'coins' => 'integer',
            'reward_type' => RewardTypeCast::class,
            'status' => RewardStatusCast::class,
            'is_used' => 'boolean',
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RewardEarning $reward): void {
            if (empty($reward->uuid)) {
                $reward->uuid = Str::uuid()->toString();
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

    public function voucherCode(): BelongsTo
    {
        return $this->belongsTo(VoucherCode::class);
    }
}
