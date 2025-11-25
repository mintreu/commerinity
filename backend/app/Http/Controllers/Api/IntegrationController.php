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

    public function getMinimalPaymentIntegrations(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => [

                // Cash On Delivery
                [
                    'name' => 'Cash On Delivery',
                    'url' => 'cod',
                    'default' => false,
                    'type' => 'Payment',
                    'hasCharge' => false,
                    'charge' => '0.00',
                    'thumbnail' => asset('/media/cod-payment.png'),
                ],

                // Online Payment
                [
                    'name' => 'Online Payment',
                    'url' => 'online-payment',
                    'default' => true,
                    'type' => 'Payment',
                    'hasCharge' => false,
                    'charge' => '0.00',
                    'thumbnail' => asset('/media/online-payment.png'),
                ],

            ],
        ]);
    }





}
