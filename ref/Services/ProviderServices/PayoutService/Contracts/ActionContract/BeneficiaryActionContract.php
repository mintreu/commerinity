<?php

namespace App\Services\ProviderServices\PayoutService\Contracts\ActionContract;

interface BeneficiaryActionContract
{


    public function create(string $contact_id,array $data,?string $company_ledger_account_upi_handle = null):array;

    public function find();

}
