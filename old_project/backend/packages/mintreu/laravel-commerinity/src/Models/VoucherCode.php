<?php

namespace Mintreu\LaravelCommerinity\Models;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;

class VoucherCode extends Model
{
    use HasPackageModelFactory;

    protected $fillable = [
        'code',
        'coupon_usage_limit',
        'usage_per_user',
        'times_used',
        'type',
        'starts_from',
        'ends_till',
        'voucher_id',
    ];

    protected $casts = [
        'coupon_usage_limit' => 'integer',
        'usage_per_user' => 'integer',
        'times_used' => 'integer',
        'type' => 'integer',
        'starts_from' => 'date',
        'ends_till' => 'date',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function orders(): HasMany
    {
        // ধরে নিচ্ছি orders টেবিলে `voucher_code` কলাম আছে
        return $this->hasMany(Order::class, 'voucher_code', 'code');
    }


    public function usages()
    {
        return $this->morphedByMany(
            related: config('auth.providers.users.model'), // your User model
            name: 'userable',
            table: 'voucher_code_usages',
            foreignPivotKey: 'voucher_code_id',
            relatedPivotKey: 'userable_id'
        )
            ->withPivot('times_used')
            ->withTimestamps();
    }



//    public function usages()
//    {
//        return $this->morphToMany(
//            related: config('auth.providers.users.model'), // configurable
//            name: 'userable',
//            table: 'voucher_code_usages'
//        )
//            ->using(VoucherCodeUsage::class) // custom pivot
//            ->withPivot('times_used')
//            ->withTimestamps();
//    }

    public function usageByUser(Model $user): int
    {
        return $this->usages()
            ->wherePivot('userable_id', $user->getKey())
            ->wherePivot('userable_type', $user->getMorphClass())
            ->first()?->pivot->times_used ?? 0;
    }

    public function canBeUsedByUser(Model $user): bool
    {
        return $this->isActive()
            && $this->times_used < $this->coupon_usage_limit
            && $this->usageByUser($user) < $this->usage_per_user;
    }

    public function isActive(): bool
    {
        return now()->between($this->starts_from, $this->ends_till);
    }

    public function isExpired(): bool
    {
        return $this->ends_till && now()->gt($this->ends_till);
    }
}

