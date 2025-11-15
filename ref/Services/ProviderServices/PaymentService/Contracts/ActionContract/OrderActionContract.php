<?php

namespace App\Services\ProviderServices\PaymentService\Contracts\ActionContract;

interface OrderActionContract
{


    public function create(array $data);

    public function find();

    public function all();

}
