<?php

namespace Mintreu\LaravelIntegration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;
use function Livewire\of;

class Integration extends Model
{
    /** @use HasFactory<\Database\Factories\IntegrationFactory> */
    use HasPackageModelFactory;

    protected $hidden = ['key', 'secret', 'webhook'];

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
        'logo_url',
        'is_live'
    ];

    protected $casts = [
        'type' => IntegrationTypeCast::class,
        'status' => 'boolean',
        'is_live' => 'boolean',
        'default' => 'boolean',
        'logo_url' => 'string',
        // 'charge' => 'decimal'
    ];


    protected static function booted()
    {
        static::updated(function (Integration $integration) {
            if ($integration->wasChanged('default') && $integration->default) {
                Integration::where('type', $integration->type)
                    ->where('id', '!=', $integration->id)
                    ->update(['default' => false]);

                Artisan::call('laravel-integration');
            }
        });
    }




    public function getBranding()
    {
        return $this->logo_url;
    }

    public function hasCharge(): bool
    {
        return $this->charge > 0.00;
    }


}
