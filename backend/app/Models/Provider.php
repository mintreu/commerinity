<?php

namespace App\Models;

use App\Casts\ProductTypeCast;
use App\Casts\ProviderTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Provider extends Model implements  HasMedia
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory,InteractsWithMedia;

    protected $fillable = [
        'name',
        'url',
        'desc',
        'charge',
        'link',
        'type',
        'key',
        'secret',
        'webhook',
        'status',
        'default',
    ];

    protected $casts = [
      'type' => ProviderTypeCast::class,
      'status' => 'boolean',
     // 'charge' => 'decimal'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('displayImage')
            ->useFallbackUrl('https://placehold.co/600x400?text='.$this->name);

    }

    public function hasCharge()
    {
        return $this->charge > 0.00;
    }

}
