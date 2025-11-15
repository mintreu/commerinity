<?php

namespace App\Services\ProviderServices\PayoutService\Contracts\ActionContract;

interface LedgerActionContract
{


    public function create(array $data):array;

    public function find(string $id):array;

    public function findAll():array;


    public function update(string $id,array $data):array;

    public function balance():array;

    public function status(string $id,bool $status = false);

    public function collect(string $id);

}
