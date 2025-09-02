<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\SaleResource;
use App\Models\Lifecycle\Level;
use App\Models\Promotion\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{


    public function index(Request $request)
    {
        $user = $request->user();
        $sales = null;
        if (!$user)
        {
            // Fetch all non-target based sales
            $sales = Sale::withoutTargets()->get();

        }else{
            $sales = Sale::ForTarget(Level::class)->get();
        }




        return SaleResource::collection($sales);
    }



}
