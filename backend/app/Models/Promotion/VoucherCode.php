<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherCode extends Model
{
    /** @use HasFactory<\Database\Factories\Promotion\VoucherCodeFactory> */
    use HasFactory;


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

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    // Optional: if you treat usages as a pivot with extra data
    public function users()
    {
        return $this->belongsToMany(User::class, 'voucher_code_usages')
            ->withPivot('times_used')
            ->withTimestamps();
    }

    // Check how many times a specific user used this code
    public function usageByUser($userId): int
    {
        return $this->users()->where('user_id', $userId)->first()?->pivot->times_used ?? 0;
    }

    public function canBeUsedByUser($userId): bool
    {
        return $this->isActive()
            && $this->times_used < $this->coupon_usage_limit
            && $this->usageByUser($userId) < $this->usage_per_user;
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
