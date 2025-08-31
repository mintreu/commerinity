<?php

namespace App\Models\Lifecycle;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\LaravelTransaction\Traits\HasTransaction;
use Mintreu\Toolkit\Traits\HasUnique;

class UserSubscription extends Model
{
    use HasUnique,HasTransaction;

    protected $fillable = [
        'uuid',
        'amount',
        'is_paid',
        'expire_at',
        'checkout_expires_at',
        'user_id',
        'level_id',
        'stage_id',
        'wallet_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => LaravelMoneyCast::class,
        'is_paid' => 'boolean',
        'expires_at' => 'datetime',
        'checkout_expires_at' => 'datetime',
    ];


    protected static function booted()
    {
        static::creating(function ($record){
            if (is_null($record->uuid))
            {
                $record->setUniqueCode('uuid');
            }
        });
        parent::booted();
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->user();
    }


    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class,'stage_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class,'level_id');
    }


//    public function payment(): MorphOne
//    {
//        return $this->morphOne(Payment::class, 'payable');
//    }




}
