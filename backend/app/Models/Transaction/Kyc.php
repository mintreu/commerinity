<?php

namespace App\Models\Transaction;

use App\Casts\KycTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\Toolkit\Traits\HasUnique;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Kyc extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia,HasUnique;



    protected $fillable = [
        'uuid',
        'user_type',
        'company_name',
        'company_type',
        'has_tax',
        'gst',
        'pan',
        'aadhaar',
        'utility_bills',
        'kyc',
        'kycable_type',
        'kycable_id',
    ];

    protected $casts = [
        'user_type' => KycTypeCast::class,
        'company_type' => 'string',
        'has_tax' => 'boolean',
        'utility_bills' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($user){
            $user->setUniqueCode('uuid');
        });
        parent::booted();
    }
    public function kycable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('aadhaarImage'); // from public dir
        $this->addMediaCollection('panImage');
        $this->addMediaCollection('gstImage');
    }



}
