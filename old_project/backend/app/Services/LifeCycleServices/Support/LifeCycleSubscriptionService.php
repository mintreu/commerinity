<?php

namespace App\Services\LifeCycleServices\Support;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Filament\Resources\UserResource;
use App\Models\Lifecycle\Level;
use App\Models\Lifecycle\Stage;
use App\Models\Lifecycle\UserSubscription;
use App\Models\User;
use App\Notifications\Subscription\SubscriptionConfirmationNotificaion;
use App\Notifications\Subscription\SubscriptionFailedNotification;
use App\Services\UserServices\NetworkServices\NetworkService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

class LifeCycleSubscriptionService
{
    protected User|Model $user;
    protected ?Wallet $wallet = null;
    protected ?UserSubscription $existSubscription = null;
    protected ?UserSubscription $subscription = null;
    protected ?Model $originator = null;
    protected ?Stage $stage = null;
    protected ?Level $level = null;
    protected ?string $redirectUrl = null;
    protected ?Transaction $transaction = null;

    public function __construct(User|Model $user)
    {
        // Efficiently eager-load wallet and latest membership
        $this->user = $user->loadMissing([
            'wallet',
            'memberships' => fn($q) => $q->latest('id')->limit(1),
        ]);

        $this->wallet = $this->user->wallet;
        $this->existSubscription = $this->user->memberships->first();
    }

    public static function make(User|Model $user): static
    {
        return new static($user);
    }

    /**
     * Create or reuse a user subscription intelligently.
     */
    public function create(?Model $originator = null): ?UserSubscription
    {
        $this->originator = $this->user->originator ?? $originator;

        if ($this->existSubscription) {
            return $this->handleExistingSubscription();
        }

        // No existing subscription → create new one
        $this->subscription = $this->makeSubscription();

        // Update User For Originator if Avail
        if ($this->subscription && $this->originator)
        {
            $this->user->update([
                'originator_id' => $this->originator->id,
                'originator_type' => get_class($this->originator)
            ]);
            // Send Filament Notification
            Notification::make()
                ->title('You are now became originator of '.$this->user->name)
                ->success()
                ->sendToDatabase($this->originator);
        }
    }





    /**
     * Validate active subscription.
     */
    public function validate(Transaction $transaction): bool
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

            // Events For Originator
            if ($this->user->originator)
            {
                // send event for originator for became originator of this user. now originator can unlock some parks

            }


        }else{
            $this->user->notify(new SubscriptionFailedNotification);
        }
        return $this->subscription && !$this->subscription->isExpired();
    }

    /**
     * Get the active or newly created subscription.
     */
    public function getSubscription(): ?UserSubscription
    {
        return $this->subscription;
    }






    /**
     * Handle existing subscriptions (paid/unpaid/expired).
     */
    protected function handleExistingSubscription(): ?UserSubscription
    {
        if ($this->existSubscription->isPaid()) {
            if ($this->existSubscription->isExpired()) {
                // Existing subscription expired → create new one
                return $this->subscription = $this->makeSubscription();
            }

            // Still valid → reuse current subscription
            return $this->subscription = $this->existSubscription;
        }

        // Unpaid → refresh payment window
        return $this->subscription = $this->refreshTransactionForRetry();
    }

    /**
     * Create new subscription for the user (used for first-time or expired).
     */
    protected function makeSubscription(): ?UserSubscription
    {
        $this->stage = $this->user->getNextLifecycleStage();
        $this->level = $this->stage->levels->first();

        $this->subscription = $this->user->memberships()->create([
            'amount'    => $this->stage->price,
            'stage_id'  => $this->stage->id,
            'level_id'  => $this->level->id,
            'is_paid'   => false,
            'expire_at' => now()->addYears($this->level->validate_years)
        ]);

        $this->processTransaction();

    }

    /**
     * Allow user to retry payment on an existing unpaid subscription.
     */
    protected function refreshTransactionForRetry(): ?UserSubscription
    {
        $this->transaction = $this->subscription->refreshTransaction();

        return $this->existSubscription;
    }


    protected function processTransaction()
    {
        if (is_null($this->originator))
        {
            $successUrl =  config('app.client_url').'/dashboard/subscribe';
        }else{
            filament()->setCurrentPanel(filament()->getPanel('admin'));
            $successUrl = UserResource::getUrl('view',['record' => $this->user->getRouteKey()]);
        }


        if ($this->wallet && LaravelMoney::make($this->wallet->balance)->greaterThanOrEqual($this->stage->price))
        {
            $this->transaction = WalletService::make($this->wallet)
                ->payFor(
                    payable_record: $this->subscription,
                    successUrl: $successUrl,
                    failureUrl: $successUrl,
                    purpose: 'Subscription for '.$this->subscription->name,
                )->getTransaction();
            $this->redirectUrl = $successUrl;
        }else{
            // Checkout Process

            $this->transaction = $this->subscription->createDebitTransaction(
                customer: $this->user,
                redirect_success_url: $successUrl,
                redirect_failure_url: $successUrl,
            );
            $this->redirectUrl = route('checkout',['transaction' => $this->subscription->transaction->uuid]);
        }
    }












}
