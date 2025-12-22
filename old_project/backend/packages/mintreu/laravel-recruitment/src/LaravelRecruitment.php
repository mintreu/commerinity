<?php

namespace Mintreu\LaravelRecruitment;

use App\Services\OrderService\OrderConfirmService;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelRecruitment\Casts\JobApplicationStatusCast;
use Mintreu\LaravelRecruitment\Models\JobApplication;
use Mintreu\LaravelRecruitment\Models\Recruitment;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;
use Mintreu\Toolkit\Support\HasErrors;

class LaravelRecruitment
{
    use HasErrors;
    protected Model $applicant;
    protected Recruitment $recruitment;
    protected array $formData = [];
    protected ?Transaction $transaction = null;
    protected ?JobApplication $application = null;
    protected ?string $returnUrl = null;
    protected ?Address $address = null;


    /**
     * @param Recruitment $recruitment
     */
    public function __construct(Models\Recruitment $recruitment)
    {
        $this->recruitment = $recruitment;
    }


    public static function make(Models\Recruitment $recruitment): static
    {
        return new static($recruitment);
    }

    public function user(Model $applicant)
    {
        $this->applicant = $applicant;
        $this->applicant->load([
            'wallet',
            'addresses'
        ]);
        return $this;
    }

    public function apply(array $data = []):static
    {
        $this->formData = $data;
        $this->checkAndSubmitJobApplication();
        return $this;
    }


    public function toArray(): array
    {
        return [
            'recruitment' => $this->recruitment,
            'applicant' => $this->applicant,
            'data' => $this->formData,
            'application' => $this->application,
            'return_url' => $this->returnUrl,
            'error' => $this->getError(),
            'errors' => $this->getErrors()
        ];
    }

    private function checkAndSubmitJobApplication(): void
    {
        if (is_null($this->applicant))
        {
            $this->setError('Applicant not found!');
        }
        if (is_null($this->getError()))
        {
            $this->ensureApplicantProfile();
            $this->ensureApplicantAddress();

            $this->application =  $this->applicant->jobApplications()->firstWhere('recruitment_id',$this->recruitment?->id);

            if ($this->application)
            {
                $this->setError('You already applied for this job. Your Application Id: '.$this->application?->uuid);
            }

            if (is_null($this->getError()))
            {
                $this->processApplication();
            }
        }

    }

    private function ensureApplicantProfile()
    {
    }

    private function ensureApplicantAddress(): void
    {
        $this->address = isset($this->formData['address_uuid'])
            ? $this->applicant->addresses->where('uuid',$this->formData['address_uuid'])->first()
            : $this->applicant->addresses->first();

        if (is_null($this->address))
        {
            $this->setError('Applicant has no address!');
        }
    }

    private function processApplication()
    {

        if ($this->recruitment->isPayable())
        {
            $this->application = $this->createApplication();
            $this->transaction = $this->ensureTransaction();
        }else{
            $this->application = $this->createApplication();
            $this->application = $this->submitApplication($this->application);
        }



    }

    private function createApplication()
    {
        $this->application = $this->applicant->jobApplications()->create(array_merge($this->formData,[
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::DRAFT,
            'amount' => $this->recruitment->getFees()
        ]));
        return $this->application;
    }

    public function submitApplication(?JobApplication $application = null,?Transaction $transaction = null):JobApplication
    {
        $this->application = $application;
        $this->application->loadMissing([
            'recruitment',
            'transaction'
        ]);
        $this->recruitment = $this->recruitment ?? $this->application->recuritment;
        $this->transaction = $transaction ?? $this->application->transaction;
        $isPaid = $this->recruitment->isPayable();
        if ($transaction = $this->application->transaction ?? $this->transaction)
        {
            $isPaid =  $transaction->verified;
        }


        $application->update([
           'is_paid' =>  $isPaid,
           'status' => JobApplicationStatusCast::SUBMITTED,
           'submitted_at' => now()
        ]);
        return $application;
    }


    private function ensureTransaction():?Transaction
    {


        $userWallet = $this->applicant?->wallet;
        $clientViewUrl = config('app.client_url').'/dashboard/career/applications/'.$this->application?->uuid;

        if ($userWallet && LaravelMoney::make($userWallet?->balance)->greaterThanOrEqual($this->recruitment->getFees())) {
            $this->transaction = WalletService::make($userWallet)->payFor(
                payable_record: $this->application,
                successUrl: $clientViewUrl,
                failureUrl: $clientViewUrl,
                amount_column: 'amount',
                purpose: 'Application Processing Fees',
            )->getTransaction();

            $this->returnUrl = $clientViewUrl;
        }

        if (is_null($this->transaction))
        {
            // Other Providers
            $this->transaction = $this->application->createDebitTransaction(
                customer: $this->applicant,
                redirect_success_url: $clientViewUrl,
                redirect_failure_url: $clientViewUrl,
                purpose: 'Application Processing Fees',
                paymentProviderSlug: Integration::where([
                    ['default',true],
                    ['status',true],
                    ['type',IntegrationTypeCast::PAYMENT]
                ])->first()?->url,
                expireAfterMinutes: 60
            );

            $this->returnUrl = route('checkout',['transaction' => $this->transaction->uuid]);
        }

        if ($this->transaction)
        {
            $this->application->update([
                'status' => JobApplicationStatusCast::AWAITING_PAYMENT
            ]);
        }


        return $this->transaction;

    }




}
