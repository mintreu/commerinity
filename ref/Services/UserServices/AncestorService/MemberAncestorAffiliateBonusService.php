<?php

namespace App\Services\UserServices\AncestorService;

use App\Models\Commission\AffiliateCommission;
use App\Models\Enums\AuthStatusCast;
use App\Models\Enums\CommissionCast;
use App\Models\Enums\Wallet\TransactionStatusCast;
use App\Models\Enums\Wallet\TransactionTypeCast;
use App\Models\User;
use App\Services\MoneyService\Money;

class MemberAncestorAffiliateBonusService
{

    protected User $subscriber;


    public static function make()
    {
        return new static();
    }



    public function subscriber(User $user)
    {
        $this->subscriber = $user;


        // Reload the ancestors and their level to ensure it's up-to-date after upgrade

        $this->subscriber->load([
            'memberships',
            'ancestors' => function ($query) {
                $query->where('status', AuthStatusCast::SUBSCRIBED);
            },
            'ancestors.affiliateEarnings' => function ($query) {
                $query->where('user_id', $this->subscriber->id);
            },
            'ancestors.level'
        ]);

        return $this;
    }



    public function bonusToAncestors(): void
    {

        $ancestors = $this->subscriber->ancestors;
        if ($ancestors->count())
        {
            // Group ancestors by level_id
            $groupedAncestors = $ancestors->groupBy('level_id');

            // Debugging the grouped ancestors
            foreach ($groupedAncestors as $groupLevel)
            {
                $this->giveBonusToAncestorsAsPerTheirCurrentLevel($groupLevel);
            }
        }

    }



    protected function giveBonusToAncestorsAsPerTheirCurrentLevel($ancestors): void
    {
        $levelModel = $ancestors->first()->level;
        $latestMembership = $this->subscriber->memberships->sortByDesc('created_at')->first();
        $subscriptionAmount = new Money($latestMembership->amount);
        $levelWiseCommissionPercent = $levelModel->joining_bonus;

        $commission =  $subscriptionAmount->multiplyOnce($levelWiseCommissionPercent)->divideOnce(100);

        foreach ($ancestors as $member)
        {
            if ($member->affiliateEarnings->count())
            {
                continue;
            }else{
                // Create Commission For Member
                $affiliateCommissionRecord = $member->affiliateEarnings()->create([
                    'user_id' => $this->subscriber->id,
                    'ancestor_id' => $member->id,
                    'commission_amount' => $commission->getValue(),
                    'commission_type' => $this->subscriber->parent_id != $member->id ? CommissionCast::TEAM_JOINING : CommissionCast::SELF_JOINING,
                ]);



                if (is_null($member->wallet))
                {
                    // Create a wallet
                    $newWallet = $member->wallet()->create();
                    $member->load('wallet');

//                    $submittedActiveBank = $member->active_bank()->first();
//                    if ($submittedActiveBank)
//                    {
//                        $submittedActiveBank->fill(['wallet_id' => $newWallet->id])->save();
//                    }

                }


                if ($member->wallet)
                {
                    // Pass to wallet as Transaction
                    $newTransaction = $member->wallet->transactions()->create([
                        'amount' => $affiliateCommissionRecord->commission_amount,
                        'type' => TransactionTypeCast::CREDITED,
                        'description' => 'Commission from subscriber referral credited as '.$affiliateCommissionRecord->commission_type->getLabel().' to your wallet.',
                        'status' => TransactionStatusCast::COMPLETED,
                        'verified' => true,
                        'transactionable_id' => $affiliateCommissionRecord->id,
                        'transactionable_type' => AffiliateCommission::class
                    ]);



                    if ($newTransaction)
                    {
                        // Update Wallet Balance
                        $member->wallet->update([
                            'balance' => $member->wallet->balance + $newTransaction->amount
                        ]);

                        // Update Commission Record
                        $affiliateCommissionRecord->update([
                            'is_paid' => true,
                        ]);



                    }
                }


            }
        }
    }







}
