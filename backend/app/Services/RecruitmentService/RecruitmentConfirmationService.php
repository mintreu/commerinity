<?php

namespace App\Services\RecruitmentService;

use App\Casts\NaukriApplicationStatusCast;
use App\Models\NaukriApplication;
use Mintreu\LaravelTransaction\Models\Transaction;

class RecruitmentConfirmationService
{

    protected NaukriApplication $application;
    protected Transaction $transaction;

    /**
     * @param NaukriApplication $application
     */
    public function __construct(NaukriApplication $application)
    {
        $this->application = $application;
    }


    public static function make(NaukriApplication $application)
    {
        return new static($application);
    }

    public function validate(?Transaction $transaction)
    {
        $this->transaction = $transaction;
        if ($this->transaction->verified)
        {
            $this->submitApplication();
        }
    }



    public function submitApplication(): void
    {
        $this->application->update([
            'is_paid' => true,
            'status' => NaukriApplicationStatusCast::SUBMITTED
        ]);


        // Send Notification


    }


}
