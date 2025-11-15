<?php

namespace App\Services\ProviderServices\PayoutService\Contracts;

use App\Services\ProviderServices\Contract\PaymentProviderServiceContract;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\UtilityActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\BeneficiaryActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\ContactActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\LedgerActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\PayoutActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\UpiActionContract;
use Illuminate\Database\Eloquent\Model;

interface PayoutServiceContract extends PaymentProviderServiceContract
{


    public function payout():PayoutActionContract;
    public function contact():ContactActionContract;
    public function beneficiary():BeneficiaryActionContract;

    public function ledger():LedgerActionContract;

    public function upi(Model $user):UpiActionContract;

}
