<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Http\Resources\IntegrationResource;
use Mintreu\LaravelIntegration\Models\Integration;

class IntegrationController extends Controller
{



    public function getPaymentIntegrations()
    {
        $integrations = Integration::where('type',IntegrationTypeCast::PAYMENT)->get();
        return IntegrationResource::collection($integrations);

    }



}
