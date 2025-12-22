<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Actions;

use Mintreu\LaravelIntegration\Providers\Payout\Razorpay\RazorpayPayoutProvider;

class FundAccountAction
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
        return $this->provider->getApi()->fund_account->fetch($id)->toArray();
    }


    public function create($data)
    {
        return $this->provider->getApi()->fund_account->create($data)->toArray();
    }


}
