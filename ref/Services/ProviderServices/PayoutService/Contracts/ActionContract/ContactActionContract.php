<?php

namespace App\Services\ProviderServices\PayoutService\Contracts\ActionContract;

interface ContactActionContract
{


    public function create(array $data);

    public function find(string $id);

    public function findAll();

    public function edit(string $id,array $data);

}
