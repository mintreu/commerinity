<?php

namespace App\Http\Controllers;

use App\Models\User;
use Cashfree\Cashfree;
use Illuminate\Contracts\Auth\MustVerifyEmail;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\Fetcher\Fetch;
use Mintreu\LaravelIntegration\Support\OrderBuilder\ProviderOrder;
use Mintreu\LaravelIntegration\Support\ServiceConstants;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Support\Validator\ClassContractValidator;

class TestController extends Controller
{
    //


    public function index(Request $request)
    {
        dd( config('laravel-store.sales'));

        $user = User::firstWhere('email','applicant@example.com');

        dd(LaravelIntegration::payment('cash-free-payment')->order()->create(function (\Mintreu\LaravelIntegration\Support\ProviderOrder $order) use($user){
            $order
                ->currency('INR')
                ->amount(10.34)
                ->customer($user)
                ->receipt('asfd'.Str::random(3))
                ->successUrl(url('/payment/confirm'))
                ->failureUrl(url('/payment/failure'))
                ->expireAfter(20)
                ->notes([]);
        }));












       // dd(LaravelIntegration::payment()->getModel());

        // Note: LaravelIntegration  er vitar payment , payout, sms, shipping, booking sob alada alada provider ache
        $orderData = LaravelIntegration::payment()->order()->create([
            'receipt' => '123', 'amount' => 100, 'currency' => 'INR',
            'notes'=> array('key1'=> 'value3','key2'=> 'value2')
        ]);
//        dd($orderData);




        Fetch::make()->get('',function (){

        });

        Fetch::make()->get('',[]);









    }







}
