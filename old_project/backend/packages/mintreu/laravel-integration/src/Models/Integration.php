<?php

namespace Mintreu\LaravelIntegration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;

class Integration extends Model
{
    /** @use HasFactory<\Database\Factories\IntegrationFactory> */
    use HasPackageModelFactory;

    /**
     * Attributes hidden from array/json serialization.
     */
    protected $hidden = ['key', 'secret', 'webhook'];

    /**
     * Mass assignable attributes.
     */
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
        'is_live',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'type' => IntegrationTypeCast::class,
        'status' => 'boolean',
        'is_live' => 'boolean',
        'default' => 'boolean',
        'logo_url' => 'string',
        // 'charge' => 'decimal', // Uncomment if needed
    ];

    /**
     * Booted model event: ensure only one default per type.
     */
    protected static function booted()
    {
        static::updated(function (Integration $integration) {
            if ($integration->wasChanged('default') && $integration->default) {
                static::where('type', $integration->type)
                    ->where('id', '!=', $integration->id)
                    ->update(['default' => false]);

                Artisan::call('laravel-integration');
            }
        });
    }

    /**
     * Generic scope for filtering by type and active status.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type)->where('status', true);
    }

    /**
     * Scope for payment providers.
     */
    public function scopePayment($query)
    {
        return $this->scopeOfType($query, IntegrationTypeCast::PAYMENT->value);
    }

    /**
     * Scope for payout providers.
     */
    public function scopePayout($query)
    {
        return $this->scopeOfType($query, IntegrationTypeCast::PAYOUT->value);
    }

    /**
     * Return logo or branding URL.
     */
    public function getBranding(): ?string
    {
        return $this->logo_url;
    }

    /**
     * Determine if integration has a charge value.
     */
    public function hasCharge(): bool
    {
        return (float) $this->charge > 0.00;
    }
}
