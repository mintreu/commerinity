<?php

namespace Mintreu\LaravelTransaction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Mintreu\Toolkit\Traits\HasUnique;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Wallet extends Model
{
    use HasFactory,HasUnique;

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


    protected static function booted()
    {
        static::creating(function ($record){
            $record->setUniqueCode('uuid',16);
        });
        parent::booted();
    }


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


    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class,'wallet_id');
    }


    public function beneficiaries(): HasMany
    {
        return $this->hasMany(BeneficiaryAccount::class,'wallet_id');
    }

    public function beneficiary(): HasOne
    {
        return $this->hasOne(BeneficiaryAccount::class, 'wallet_id')->where('default', true);
    }




    /**
     * Generate QR Code for this wallet UUID
     */
    public function getQRCode(): string
    {
        $code = QrCode::margin(2)->generate($this->uuid);

        return 'data:image/svg+xml;base64,' . base64_encode($code);
    }

    /**
     * Validate QR code string and return Wallet
     */
    public static function fromQRCode(string $qrPayload): ?self
    {
        // For now, QR payload = wallet UUID
        return static::where('uuid', $qrPayload)->first();
    }



}
