<?php

namespace App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\UtilityActionContract;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;
use Illuminate\Support\Str;

class UtilityAction implements UtilityActionContract
{


    protected LaravelEaseBuzzApi $api;

    public function __construct(LaravelEaseBuzzApi $api)
    {
        $this->api = $api;
    }

    public static function make(LaravelEaseBuzzApi $api):static
    {
        return new static($api);
    }



    // Docs: https://docs.easebuzz.in/docs/neobanking/tvavxln1nbhtk-verify-pan-and-retrieve-details
    public function verifyPan(string $pan): array
    {
        // Generate the payload
        $payload = [
            'key' => $this->api->getKey(),
            'pan_number' => $pan,
        ];

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($pan)->getGeneratedAuthHash();

        // Add headers and send request
        return $this->api->fetchPost('verifications/pan/', $payload, $authorization);
    }


    public function verifyGst(string $gst): array
    {
        // Generate the payload
        $payload = [
            'key' => $this->api->getKey(),
            'gstin' => $gst,
        ];

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($gst)->getGeneratedAuthHash();

        // Add headers and send request
        return $this->api->fetchPost('verifications/gst/', $payload, $authorization);
    }



    public function verifyIfsc(string $ifsc): array
    {
        // Generate the payload
        $payload = [
            'key' => $this->api->getKey(),
            'ifsc' => $ifsc,
        ];

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($ifsc)->getGeneratedAuthHash();

        // Add headers and send request
        return $this->api->fetchPost('beneficiaries/ifsc/verify/', $payload, $authorization);
    }

    public function verifyBankAccount(string $bank_account_no,string $bank_ifsc): array
    {
        // Generate the payload
        $payload = [
            'key' => $this->api->getKey(),
            'account_no' => $bank_account_no,
            'ifsc' => $bank_ifsc,
            'unique_request_number' => Str::random(8)
        ];

        $hashAbleValue = $bank_account_no.'|'.$bank_ifsc;

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($hashAbleValue)->getGeneratedAuthHash();

        // Add headers and send request
        return $this->api->fetchPost('beneficiaries/bank_account/verify/', $payload, $authorization);
    }



    public function verifyVPA(string $vpa): array
    {
        // Generate the payload
        $payload = [
            'key' => $this->api->getKey(),
            'vpa' => $vpa,
            'unique_request_number' => Str::random(8)
        ];

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($vpa)->getGeneratedAuthHash();

        // Add headers and send request
        return $this->api->fetchPost('beneficiaries/ifsc/verify/', $payload, $authorization);
    }


    public function verifyUPI(string $upi_id): array
    {
        return $this->verifyVPA($upi_id);
    }

}
