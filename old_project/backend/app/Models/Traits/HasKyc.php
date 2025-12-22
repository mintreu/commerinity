<?php

namespace App\Models\Traits;

use App\Models\Transaction\Kyc;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasKyc
{

    public function kyc(): MorphOne
    {
        return $this->morphOne(KYC::class, 'kycable');
    }

}
