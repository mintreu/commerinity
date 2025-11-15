<?php

namespace App\Services\ApplicantService;

use App\Filament\Common\Resources\Recruitment\JobApplicationResource;
use App\Models\Enums\Recruitment\JobApplicationStatusCast;
use App\Models\Recruitment\Applicant;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\Wallet\Payment;
use App\Services\CheckoutService\CheckoutService;
use App\Services\ProviderServices\PaymentService\PaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobApplicationPaymentService extends CheckoutService
{

    protected ?JobApplication $application;
    protected Applicant|Model $applicant;
    protected Recruitment $recruitment;
    protected Payment $payment;



    public function jobApplication(JobApplication $jobApplication): static
    {
        $this->application = $jobApplication;
        $this->application->load('applicant','recruitment');
        $this->applicant = $this->application->applicant;
        $this->recruitment = $this->application->recruitment;
        return $this;
    }



    public function getJobApplication(): JobApplication
    {
        return $this->application;
    }

    public function checkout()
    {
      $this->payment =  $this->getInitPayment(
          record: $this->application,
          redirectOnSuccessUrl: JobApplicationResource::getUrl('view',['record' => $this->application->uuid]),
          amount: $this->recruitment->fee_amount
      );

      $this->application->update(['amount' => $this->payment->amount]);


        return $this->getInitProviderOrder(
            payment: $this->payment,
            customer: $this->applicant,
            address: $this->applicant->addresses()->first(),
            checkoutInfo: 'application-'.$this->application->uuid,
            successUrl: route('job.application.fees.confirm', ['payment' => $this->payment->uuid]),
            failureUrl: route('job.application.fees.cancel_by_user', ['payment' => $this->payment->uuid]),
            hostedCheckout: true
        );

    }




    // Validate Application Fees


    public function confirm():bool
    {
        // Update Job Application Status
        $this->application->update([
            'status' => JobApplicationStatusCast::SUBMITTED,
            'is_paid' => true,
            'submitted_at' => now(),
        ]);
        return $this->application->is_paid;
    }

    public function retryCheckout(Payment $payment, \Illuminate\Http\Request $request)
    {
        $this->payment = $payment;
        $this->payment->load('payable.applicant');
        $this->application = $this->payment->payable;
        $this->applicant = $this->application->applicant;

        if (in_array($this->application->status->value,[JobApplicationStatusCast::PENDING->value,JobApplicationStatusCast::DRAFT->value]))
        {
            $this->application->update([
                'status' => JobApplicationStatusCast::PENDING
            ]);

            $this->payment->refreshUniqueCode();

            return $this->providerOrderInit();
        }else{
            return redirect()->route('job.application.cancel', ['payment' => $payment->uuid])
                ->with('message', __('Payment has been canceled after maximum retries.'));
        }

    }




    protected function providerOrderInit()
    {
        $customer = $this->applicant;
        $address = $customer->addresses()->first();
        $address->load(['state','country']);
        return PaymentService::make()->order()->create([
            'txnid' => $this->payment->uuid,
            'amount' => $this->payment->amount,
            'firstname' => $this->applicant->name,
            'email' => $this->applicant->email,
            'phone' => $this->applicant->mobile,
            'productinfo' => 'application-'.$this->application->uuid,
            'surl' => route('job.application.confirm', ['payment' => $this->payment->uuid]),
            'furl' => route('job.application.cancel_by_user',['payment' => $this->payment->uuid]),
            'address1' => $address->address_1,
            'city' => $address->city,
            'state' => $address->state->name,
            'country' => $address->country->name,
            'zipcode' => $address->postal_code,
        ]);
    }

    public function cancelCheckout(Payment $payment)
    {
        $this->payment = $payment;
        $this->payment->load('payable');

        // Cancel The Payment
        $this->payment->update([
           'verified' => false,
//           'status' =>
        ]);


        // Cancel The Application
        $this->application = $this->payment->payable;

        $this->application->update([
            'status' => JobApplicationStatusCast::CANCELLED
        ]);
    }


}
