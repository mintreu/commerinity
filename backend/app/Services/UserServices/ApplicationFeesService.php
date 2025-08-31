<?php

namespace App\Services\UserServices;

use App\Models\Naukri;
use App\Models\NaukriApplication;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;

class ApplicationFeesService
{

    protected NaukriApplication $application;
    protected Naukri $naukri;

    /**
     * @param Naukri $naukri
     */
    public function __construct(Naukri $naukri)
    {
        $this->naukri = $naukri;
    }


    public static function make(Naukri $naukri): static
    {
        return new static($naukri);
    }

    public function application(NaukriApplication $application): static
    {
        $this->application = $application;
        return $this;
    }


//    public function ensureFeesCollection(): static
//    {
//        $this->makeApplicationPaymentRecordBasedOnNaukri();
//        return $this;
//    }
    public function getCheckoutUrl():string
    {
        $this->application->load(['transaction','transaction.provider']);
        if(!is_null($this->application->transaction))
        {
            return route('checkout',['transaction' => $this->application->transaction->uuid]);
        }else{
            $transaction = $this->makeApplicationPaymentRecordBasedOnNaukri();
            return route('checkout',['transaction' => $transaction->uuid]);
        }
    }

    protected function makeApplicationPaymentRecordBasedOnNaukri(): ?\Illuminate\Database\Eloquent\Model
    {
        $transaction = null;
        $providerData = LaravelIntegration::payment()->order()->create([
            'receipt' => $this->application->uuid,
            'amount' => $this->naukri->fees,
            'currency' => LaravelMoney::defaultCurrency(),
        ]);

        if ($providerData['success'])
        {
            $transaction = $this->application->transaction()->create(array_merge([
                'type' => TransactionTypeCast::DEBITED,
                'expire_at' => now()->addDays()
            ],$providerData['data']));
        }
        return $transaction;
    }




}
