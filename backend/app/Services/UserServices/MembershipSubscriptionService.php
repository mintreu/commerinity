<?php

namespace App\Services\UserServices;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Models\Lifecycle\Level;
use App\Models\Lifecycle\Stage;
use App\Models\Lifecycle\UserSubscription;
use App\Models\User;
use App\Notifications\Subscription\SubscriptionConfirmationNotificaion;
use App\Notifications\Subscription\SubscriptionFailedNotificaion;
use App\Services\UserServices\NetworkServices\NetworkService;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;
use Throwable;

class MembershipSubscriptionService
{

    protected User $user;
    protected Stage $stage;
    protected Level $level;
    protected ?Wallet $wallet = null;
    protected ?UserSubscription $subscription = null;
    protected bool $subscriptionNeed = false;
    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->user->load([
            'memberships' => fn($query) => $query->where('is_paid',false)->limit(1),
            'wallet'
        ]);
        $this->wallet = $this->user->wallet;
        $existingUnpaidSubscription = $this->user->memberships->first();
        if ($existingUnpaidSubscription)
        {
            $existingUnpaidSubscription->load('stage','level');
        }
        $this->stage = $existingUnpaidSubscription ? $existingUnpaidSubscription->stage : $user->getNextLifecycleStage();
        $this->level = $existingUnpaidSubscription ? $existingUnpaidSubscription->level :  $this->stage->levels->first();

        if (!$existingUnpaidSubscription)
        {
            $this->subscriptionNeed = empty($this->user->level_id) || $this->user->level_id !== $this->level->id;
        }else{
            $this->subscription = $existingUnpaidSubscription;
        }

    }


    public static function make(User $user)
    {
        return new static($user);
    }

    public function isSubscriptionRequired(): bool
    {
        return $this->subscriptionNeed;
    }

    public function getSubscription():?UserSubscription
    {
        return $this->subscription;
    }

    public function getApplicableStage():?Stage
    {
        return $this->stage;
    }


    public function ensureSubscription():static
    {
        if ($this->subscriptionNeed)
        {
            if ($this->wallet && LaravelMoney::make($this->wallet->balance)->greaterThanOrEqual($this->stage->price))
            {
                $this->subscribeWithWallet();
            }else{
                $this->subscribeWithCheckout();
            }
        }

        return $this;
    }




    protected function subscribeWithWallet()
    {
        if ($this->wallet && LaravelMoney::make($this->wallet->balance)->greaterThanOrEqual($this->stage->price))
        {
            $this->subscription = $this->createSubscription();
            WalletService::make($this->wallet)
                ->payFor(payable_record: $this->subscription,amount_column: 'amount');
        }


    }



    protected function subscribeWithCheckout()
    {
        $this->createSubscription();
    }





    protected function createSubscription():UserSubscription
    {
        return $this->user->memberships()->create([
            'amount' => $this->stage->price,
            'stage_id' => $this->stage->id,
            'level_id' => $this->level->id,
            'expire_at' => now()->addYears($this->level->validate_years)
        ]);

    }


    /**
     * @throws Throwable
     */
    public function getCheckoutUrl():string
    {

        $this->subscription->load(['transaction']);
        if(!is_null($this->subscription->transaction))
        {
            return route('checkout',['transaction' => $this->subscription->transaction->uuid]);
        }else{
            $transaction = $this->makeSubscriptionPaymentRecord();
            return route('checkout',['transaction' => $transaction->uuid]);
        }
    }





    /**
     * @throws Throwable
     */
    private function makeSubscriptionPaymentRecord():Transaction
    {
        return $this->subscription->createDebitTransaction(
            customer: $this->user,
            redirect_success_url: config('app.client_url').'/dashboard/subscribe',
            redirect_failure_url: config('app.client_url').'/dashboard/subscribe',
        );
    }

    // Confirm Membership Transaction

    public function validate(Transaction $transaction)
    {
        $this->subscription = $transaction->transactionable;
        $this->subscription->load('level');
        if ($transaction->verified)
        {
            $this->subscription->update([
                'is_paid' => true
            ]);
            $this->user->notify(new SubscriptionConfirmationNotificaion);
            $this->user->update([
                'status' => AuthStatusCast::SUBSCRIBED,
                'type'  => AuthTypeCast::MEMBER,
                'level_id' => $this->subscription->level->id
            ]);

            // Other Events
            // Call Network Service
            $networkService = NetworkService::make($this->user);
            $networkService->addToNetwork();


        }else{
            $this->user->notify(new SubscriptionFailedNotificaion);
        }

    }


}
