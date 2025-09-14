<?php

namespace Mintreu\LaravelTransaction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'pin',
        'balance',
        'currency',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Automatically hash the PIN before saving.
     */
    public function setPinAttribute($value): void
    {
        $this->attributes['pin'] = Hash::make($value);
    }

    /**
     * Verify the provided PIN against the stored hash.
     */
    public function verifyPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin);
    }

    /**
     * Get the owning model (User, Merchant, etc.).
     */
    public function walletable()
    {
        return $this->morphTo();
    }


    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class,'wallet_id');
    }



}
