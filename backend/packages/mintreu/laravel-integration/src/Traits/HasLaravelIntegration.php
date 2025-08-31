<?php

namespace Mintreu\LaravelIntegration\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;

trait HasLaravelIntegration
{


    protected function integrationOfType(IntegrationTypeCast $type): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'id')
            ->where('type', $type);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'id');
    }

    public function paymentIntegration(): BelongsTo
    {
        return $this->integrationOfType(IntegrationTypeCast::PAYMENT);
    }

    public function payoutIntegration(): BelongsTo
    {
        return $this->integrationOfType(IntegrationTypeCast::PAYOUT);
    }

    public function shippingIntegration(): BelongsTo
    {
        return $this->integrationOfType(IntegrationTypeCast::SHIPPING);
    }

    public function smsIntegration(): BelongsTo
    {
        return $this->integrationOfType(IntegrationTypeCast::SMS);
    }

    public function bookingIntegration(): BelongsTo
    {
        return $this->integrationOfType(IntegrationTypeCast::BOOKING);
    }



}
