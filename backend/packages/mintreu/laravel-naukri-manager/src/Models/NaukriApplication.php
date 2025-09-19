<?php

namespace Mintreu\LaravelNaukriManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelGeokit\Traits\HasAddress;
use Mintreu\LaravelNaukriManager\Casts\NaukriApplicationStatusCast;
use Mintreu\LaravelTransaction\Traits\HasTransaction;
use Mintreu\Toolkit\Traits\HasUnique;

class NaukriApplication extends Model
{
    /** @use HasFactory<\Database\Factories\NaukriApplicationFactory> */
    use HasFactory,HasUnique,HasTransaction,HasAddress;


    protected $fillable = [
        'uuid',
        'guardian_name',

        'educations',
        'skills',
        'experiences',
        'is_paid',

        'reference_name',
        'reference_contact',
        'submitted_at',

        'status',
        'status_feedback',
        'address_id',
        'naukri_id'
    ];

    protected $casts = [
        'status' => NaukriApplicationStatusCast::class,
        'submitted_at' => 'datetime',
        'is_paid'   => 'boolean',
        'educations' => 'array',
        'skills' => 'array',
        'experiences' => 'array',
    ];

    protected static function booted()
    {
        static::creating(fn ($form) =>
            $form->setUniqueCode('uuid', 16, 'APP-' . now()->format('ym') . '-')
        );
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    public function naukri(): BelongsTo
    {
        return $this->belongsTo(Naukri::class,'naukri_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->user();
    }


    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class,'address_id','id');
    }





}
