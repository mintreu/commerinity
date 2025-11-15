<?php

namespace App\Services\UserServices\AuthService;


use App\Services\ProviderServices\SmsService\SmsService;

class UserRegisterService
{

    protected bool $api_request = false;


    public static function make(bool $api_req = false):static
    {
        $instance = new static();
        $instance->api_request = $api_req;
        return $instance;
    }



    public function register(array $data):\Illuminate\Http\JsonResponse
    {
        if (is_null($data['otp']))
        {
            return $this->sendOtpTo($data['mobile']);
        }
    }

    private function sendOtpTo(int $mobile): \Illuminate\Http\JsonResponse
    {

        $smsService = SmsService::make()->sendOtp($mobile);

        return response()->json([
            'success' => true,
            'error' => null,
            'message' => 'OTP has been sent to your mobile number.',
        ]);
    }


}
