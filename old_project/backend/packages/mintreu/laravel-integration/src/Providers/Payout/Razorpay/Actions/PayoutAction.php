<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Actions;



use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelIntegration\Providers\Payout\Razorpay\RazorpayPayoutProvider;
use Mintreu\LaravelMoney\LaravelMoney;
use Throwable;

class PayoutAction
{

    protected RazorpayPayoutProvider $provider;
    protected Model|Authenticatable $payee;
    protected ?Model $bankModel = null;

    protected ?string $fundAccountId = null;
    protected ?string $fundContactId = null;
    protected string $payeeType;

    /**
     * @param RazorpayPayoutProvider $razorpay
     */
    public function __construct(RazorpayPayoutProvider $razorpay)
    {
        $this->provider = $razorpay;
    }


    public function fetch($id)
    {
        return $this->provider->getApi()->payout->fetch($id)->toArray();
    }


    /**
     * @throws Throwable
     */
    public function payee(Authenticatable|Model $payee, string $bank_relationship = 'bankAccount',string $payee_type = 'vendor'): static
    {
        $this->payee = $payee;
        $this->payeeType = $payee_type;
        $this->bankModel = $payee->{$bank_relationship};

        // Ensure the bank model exists
        throw_if(is_null($this->bankModel), new \Exception("The bank model relationship '{$bank_relationship}' does not exist for the payee."));


        // Check if 'fund_contact' column exists in the model attributes
        if (!array_key_exists('fund_contact', $this->bankModel->getAttributes())) {
            throw new \Exception("'fund_contact' column is missing in the payee's bank model.");
        }
        $this->fundContactId = $this->bankModel->fund_contact;


        // Check if 'fund_account' column exists in the model attributes
        if (!array_key_exists('fund_account', $this->bankModel->getAttributes())) {
            throw new \Exception("'fund_account' column is missing in the payee's bank model.");
        }
        $this->fundAccountId = $this->bankModel->fund_account;


        return $this;
    }

    /**
     * @throws Throwable
     */
    public function sendToBank(Model $payoutModel)
    {
        $this->fundContactId = is_null($this->fundContactId) ? $this->getFundContactId() : $this->fundContactId;
        $this->fundAccountId = is_null($this->fundAccountId) ? $this->getFundAccountId() : $this->fundAccountId;
        $this->bankModel->update(['fund_contact' => $this->fundContactId, 'fund_account' => $this->fundAccountId]);

        throw_unless($this->provider->getSourceBankAccount(),'RAZORPAY_X_SOURCE_ACCOUNT not set');


        $data = [
            "account_number" => $this->provider->getSourceBankAccount(),
            "fund_account_id" => $this->fundAccountId,
            "amount" => $payoutModel->net_payable_amount,
            "currency" => (new LaravelMoney($payoutModel->net_payable_amount))->getCurrency()->getCurrency(),
            "mode" => $this->provider->getMode(),
            "purpose" => "payout",
            "queue_if_low_balance" => true,
            "reference_id" => $this->fundContactId,
            "narration" => config('app.name')." Fund Transfer",
        ];

        $data['amount'] = is_int($data['amount']) ? $data['amount'] : $data['amount'] * 100;

        // Finally Call
        return $this->provider->getApi()->payout->create($data)->toArray();

    }






    protected function getFundContactId():string
    {



        $contactData = $this->provider->contact()->create([
            'name' => $this->payee->name,
            'email' => $this->payee->email,
            'contact' => $this->payee->contact,
            'type' => $this->payeeType,
            'reference_id' => md5($this->payee->email),
            'notes' => [],
        ]);
        return $contactData['id'];
    }

    protected function getFundAccountId():string
    {
        $fundAccountData =  $this->provider->fundAccount()->create([
            "contact_id" => $this->fundContactId,
            "account_type" => "bank_account",
            "bank_account" => [
                "name" => $this->bankModel->account_name,
                "ifsc" => $this->bankModel->ifsc,
                "account_number" => $this->bankModel->account_no
            ],
        ]);
        return $fundAccountData['id'];
    }


}
