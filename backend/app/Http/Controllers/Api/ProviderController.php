<?php

namespace App\Http\Controllers\Api;

use App\Casts\ProviderTypeCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Provider\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{



    public function getPaymentProviders()
    {
        $paymentProviders = Provider::with('media')->where('type',ProviderTypeCast::PAYMENT)->get();
        return ProviderResource::collection($paymentProviders);
    }



}
