<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\Welcome\WelcomeDatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\OrderBuilder\ProviderOrder;
use Mintreu\LaravelMoney\Money;
use Mintreu\Toolkit\Support\Pdf\PdfMaker;


class TestController extends Controller
{
    //


    public function index(Request $request)
    {

        $user = User::with('children')->firstWhere('email','test@example.com');
        dd($user);






        $user = User::firstWhere('email','applicant@example.com');

        dd(LaravelIntegration::payment('cash-payment')->order()->create(function (\Mintreu\LaravelIntegration\Support\ProviderOrder $order) use($user){
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









        dd('stop!');



//        dd( config('laravel-store.sales'));
//
        $user = User::firstWhere('email','applicant@example.com');

        dd(LaravelIntegration::payment('razorpay-payment')->order()->create(function (\Mintreu\LaravelIntegration\Support\ProviderOrder $order) use($user){
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
        $orderData = LaravelIntegration::payment()->order()->create([
            'receipt' => '123', 'amount' => 100, 'currency' => 'INR',
            'notes'=> array('key1'=> 'value3','key2'=> 'value2')
        ]);
        dd($orderData);

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
