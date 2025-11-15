<?php

namespace App\Services\CheckoutService;

use App\Models\Localization\Address;
use App\Models\Wallet\Payment;
use App\Services\ProviderServices\PaymentService\PaymentService;
use Illuminate\Database\Eloquent\Model;

class CheckoutService
{


    public static function make(): static
    {
        return new static();
    }


    public function getInitPayment(Model $record,string $redirectOnSuccessUrl,null|float|int $amount = null)
    {
        $existPaymentRecord = $record->payment()->first();
        if ($existPaymentRecord)
        {
            $existPaymentRecord->refreshUniqueCode();
            return $existPaymentRecord;
        }else{
            return $record->payment()->create([
                'provider_gen_id' => $record->uuid,
                'amount' => $amount ?? $record->amount,
                'provider_id' => PaymentService::make()->getModel()->id,
                'success_url' => $redirectOnSuccessUrl
            ]);
        }
    }


    public function getInitProviderOrder(Payment $payment,Model $customer,Address $address,string $checkoutInfo,string $successUrl,string $failureUrl,bool $hostedCheckout = true)
    {
        $data = $this->getBuildProviderData($payment,$customer,$address,$checkoutInfo,$successUrl,$failureUrl);
        return $hostedCheckout ? $this->initHostedCheckout($data) : $this->initLinkCheckout($data);
    }



    protected function initHostedCheckout(array $data)
    {
        return PaymentService::make()->order()->create($data);
    }

    protected function initLinkCheckout(array $data)
    {
        return PaymentService::make()->link()->create($data);
    }


    protected function getBuildProviderData(Payment $payment,Model $customer,Address $address,string $checkoutInfo,string $successUrl,string $failureUrl):array
    {
        $address->loadMissing(['state','country']);
        return [
            'txnid' => $payment->uuid,
            'amount' => $payment->amount,
            'firstname' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->mobile,
            'productinfo' => $checkoutInfo,
            'surl' => $successUrl,
            'furl' => $failureUrl,
            'address1' => $address->address_1,
            'city' => $address->city,
            'state' => $address->state->name,
            'country' => $address->country->name,
            'zipcode' => $address->postal_code,
        ];
    }






}
