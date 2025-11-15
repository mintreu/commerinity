<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\BeneficiaryActionContract;
use App\Services\ProviderServices\PayoutService\PayoutService;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;

class BeneficiaryAction implements BeneficiaryActionContract
{


    protected LaravelEaseBuzzApi $api;


    public function __construct(LaravelEaseBuzzApi $api)
    {
        $this->api = $api;
    }

    public static function make(LaravelEaseBuzzApi $api):static
    {
        return new static($api);
    }


    /**
     * Docs: https://docs.easebuzz.in/docs/neobanking/tbp570t80o0ik-beneficiary-transfers
     * Making Beneficiary
     * @param string $contact_id
     * @param array $data
     * @param string|null $company_ledger_account_upi_handle
     * @return array
     */
    public function create(string $contact_id,array $data,?string $company_ledger_account_upi_handle = null):array
    {

        $payload = $data;
        $payload['key'] = $this->api->getKey();
        $payload['contact_id'] = $contact_id;

        $hashAbleValue = $contact_id . '|' . $data['beneficiary_name'] . '|' . $data['account_number']. '|' . $data['ifsc']. '|' . $company_ledger_account_upi_handle;

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($hashAbleValue)->getGeneratedAuthHash();

        // Add headers and send the request
        return $this->api
            ->setBaseUrl('https://stoplight.io/mocks/easebuzz/neobanking/90198045/api/',true)
            ->fetchPost('beneficiaries/', $payload, $authorization);

    }

    /**
     * @return array
     */
    public function find()
    {
        // TODO: Implement find() method.
    }



    // Protected Methods
    protected function createOrGetContact()
    {

    }

    protected function createOrGetBeneficiary($contact)
    {
    }


}
