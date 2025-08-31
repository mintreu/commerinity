<?php

namespace App\Models;

use App\Casts\NaukriApplicationStatusCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelTransaction\Traits\HasTransaction;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Mintreu\Toolkit\Traits\HasUnique;

class NaukriApplication extends Model
{
    /** @use HasFactory<\Database\Factories\NaukriApplicationFactory> */
    use HasFactory,HasUnique,HasTransaction;


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
        static::creating(function ($user){
            $user->setUniqueCode('uuid',12,now()->year);
        });
        parent::booted();
    }

    public function naukri(): BelongsTo
    {
        return $this->belongsTo(Naukri::class,'naukri_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class,'address_id','id');
    }





}
