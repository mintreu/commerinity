<?php

namespace Mintreu\LaravelIntegration\Providers\Payout\Razorpay\Actions;


use Mintreu\LaravelIntegration\Providers\Payout\Razorpay\RazorpayPayoutProvider;

class ContactAction
{


    protected RazorpayPayoutProvider $provider;

    /**
     * @param RazorpayPayoutProvider $razorpay
     */
    public function __construct(RazorpayPayoutProvider $razorpay)
    {
        $this->provider = $razorpay;
    }

    public function fetch($id)
    {
        return $this->provider->getApi()->contact->fetch($id)->toArray();
    }

    public function create($data)
    {
        return $this->provider->getApi()->contact->create($data)->toArray();
    }




}
