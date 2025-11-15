<?php

namespace App\Services\ProviderServices\PayoutService\Contracts\ActionContract;

interface PayoutActionContract
{


    public function create(array $data);

    public function find();

    public function all();

}
