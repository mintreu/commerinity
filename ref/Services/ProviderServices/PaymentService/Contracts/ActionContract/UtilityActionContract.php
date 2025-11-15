<?php

namespace App\Services\ProviderServices\PaymentService\Contracts\ActionContract;

interface UtilityActionContract
{


    public function verifyPan(string $pan): array;

    public function verifyGst(string $gst): array;

    public function verifyIfsc(string $ifsc): array;


    public function verifyBankAccount(string $bank_account_no,string $bank_ifsc):array;

}
