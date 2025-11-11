<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use App\Models\User;
use App\Notifications\PushNotification;
use App\Notifications\Welcome\WelcomeDatabaseNotification;
use App\Services\LifeCycleService\LifeCycleService;
use App\Services\OrderService\OrderConfirmService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\OrderBuilder\ProviderOrder;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelMoney\Money;


class TestController extends Controller
{
    //


    public function index(Request $request)
    {


        // get actual user instance
        $user = User::where('email', 'test@example.com')->first();

        if (! $user) {
            info('User not found.');
            return;
        }

// check if user has push subscriptions
        if ($user->pushSubscriptions()->exists()) {

            $user->notify(new PushNotification(
                title: 'Hello from Laravel 👋',
                body: 'This is a test web push notification.',
                icon: asset('icon-192x192.png'),
                url: route('dev.test') // or any URL you want
            ));

        } else {
            info('No push subscriptions found for this user.');
        }


        //dd(LifeCycleService::make($user)->fetchAndResolve());

        return response()->json(['status' => 'ok']);





//        $order = Order::firstWhere('uuid','2025o2U8wC1z6z7q');
//
//
//        dd([
//           OrderConfirmService::make($order)->confirm()
//        ]);





        return response()->json([
            'money_150_50_amount' => LaravelMoney::make(150.50)->getAmount(),
            'money_150_50_formatted' => LaravelMoney::make(150.50)->formatted(),
            'money_15050_amount' => LaravelMoney::make(15050)->getAmount(),
            'money_15050_formatted' => LaravelMoney::make(15050)->formatted(),
        ]);



//        $user = User::with('children')->firstWhere('email','test@example.com');
//        dd($user);
//
//
//
//
//
//
        $user = User::firstWhere('email','applicant@example.com');

        $order = LaravelIntegration::payment('cash-free-payment')->order()->create(function (\Mintreu\LaravelIntegration\Support\ProviderOrder $order) use($user){
            $order
                ->currency('INR')
                ->amount(10.34)
                ->customer($user)
                ->receipt('asfd'.Str::random(3))
                ->successUrl(url('/payment/confirm'))
                ->failureUrl(url('/payment/failure'))
                ->expireAfter(20)
                ->notes([]);
        });

        return response()->json(['order' => $order]);




        return response()->json(['status' => 'stopped']);



//        dd( config('laravel-store.sales'));
//
        $user = User::firstWhere('email','applicant@example.com');

        $order = LaravelIntegration::payment('razorpay-payment')->order()->create(function (\Mintreu\LaravelIntegration\Support\ProviderOrder $order) use($user){
            $order
                ->currency('INR')
                ->amount(10.34)
                ->customer($user)
                ->receipt('asfd'.Str::random(3))
                ->successUrl(url('/payment/confirm'))
                ->failureUrl(url('/payment/failure'))
                ->expireAfter(20)
                ->notes([]);
        });

        return response()->json(['razorpay_order' => $order]);

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

        return response()->json(['order_data' => $orderData]);

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
