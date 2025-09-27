<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelCommerinity\Models\Sale;
use Mintreu\LaravelCommerinity\Support\VoucherManager;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\Fetcher\Fetch;
use Mintreu\LaravelIntegration\Support\OrderBuilder\ProviderOrder;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;
use Mintreu\LaravelProductCatalogue\Services\ProductUpdateService;


class TestController extends Controller
{
    //


    public function index(Request $request)
    {


       $newProductData = Product::factory()->raw();

       $newProduct = ProductCreationService::make($newProductData)->create();

       // Update
        $updateService = ProductUpdateService::make($newProduct)->update([

        ]);














//        dd( config('laravel-store.sales'));
//
//        $user = User::firstWhere('email','applicant@example.com');
//
//        dd(LaravelIntegration::payment('cash-free-payment')->order()->create(function (\Mintreu\LaravelIntegration\Support\ProviderOrder $order) use($user){
//            $order
//                ->currency('INR')
//                ->amount(10.34)
//                ->customer($user)
//                ->receipt('asfd'.Str::random(3))
//                ->successUrl(url('/payment/confirm'))
//                ->failureUrl(url('/payment/failure'))
//                ->expireAfter(20)
//                ->notes([]);
//        }));











//
//       // dd(LaravelIntegration::payment()->getModel());
//
//        // Note: LaravelIntegration  er vitar payment , payout, sms, shipping, booking sob alada alada provider ache
//        $orderData = LaravelIntegration::payment()->order()->create([
//            'receipt' => '123', 'amount' => 100, 'currency' => 'INR',
//            'notes'=> array('key1'=> 'value3','key2'=> 'value2')
//        ]);
////        dd($orderData);
//
//
//
//
//        Fetch::make()->get('',function (){
//
//        });
//
//        Fetch::make()->get('',[]);
//
//







    }







}
