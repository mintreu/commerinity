<?php

namespace App\Services\RecruitmentService;

use App\Models\Naukri;
use App\Models\NaukriApplication;
use App\Models\User;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Models\Transaction;

class RecruitmentApplicationCreationService
{

    protected User $applicant;
    protected Naukri $naukri;
    protected NaukriApplication $application;
    protected Transaction $transaction;
    protected ?string $checkoutUrl = null;

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

    public function applicant(User $applicant): static
    {
        $this->applicant = $applicant;
        return $this;
    }

    public function createApplication(array $data): static
    {
        $this->application = $this->applicant->applications()->create($data);
        return $this;
    }

    public function toResponse(): array
    {
        if ($this->naukri->is_payable)
        {
            $this->prepareForTransaction();
            $redirectUrl =  route('checkout',['transaction' => $this->transaction->uuid]);
        }else{
            RecruitmentConfirmationService::make($this->application)->submitApplication();
            $redirectUrl =  config('app.client_url').'/dashboard/career/applications/'.$this->application->uuid;
        }



        return [
            'data' => [
                'status' => true,
                'message' => 'Application submitted successfully.',
                'redirect' => !is_null($redirectUrl),
                'redirect_url' => $redirectUrl,
                'application_id' => $this->application->uuid
            ],
        ];

    }

    // Transaction related

    private function prepareForTransaction()
    {
        $this->application->load(['transaction','transaction.provider','user']);
        $transaction = $this->application?->transaction;
        if ($transaction)
        {
            $this->transaction = $transaction;
        }else{
            $this->transaction = $this->application->createDebitTransaction(
                customer: $this->application->user,
                redirect_success_url: config('app.client_url').'/dashboard/career/applications/'.$this->application->uuid,
                redirect_failure_url: config('app.client_url').'/dashboard/career/applications/'.$this->application->uuid,
                purpose: 'Job Application Fees',
                expireAfterMinutes: 4320,
                amount: $this->naukri->fees,
            );
        }
    }



}
