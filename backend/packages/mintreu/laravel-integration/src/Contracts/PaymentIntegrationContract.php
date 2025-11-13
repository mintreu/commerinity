<?php

namespace Mintreu\LaravelIntegration\Contracts;

use Illuminate\Http\Request;

interface PaymentIntegrationContract
{


    public function order();

    public function verify();

    public function getSlug():string;
}
