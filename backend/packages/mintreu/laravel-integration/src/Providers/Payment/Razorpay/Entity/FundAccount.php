<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Entity;

use Razorpay\Api\Entity;

class FundAccount extends Entity
{

    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

}
