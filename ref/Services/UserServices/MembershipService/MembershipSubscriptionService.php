<?php

namespace App\Services\UserServices\MembershipService;

use App\Events\User\UserSubscribed;
use App\Filament\Common\Pages\Dashboard;
use App\Models\Enums\AuthStatusCast;
use App\Models\Lifecycle\Level;
use App\Models\Lifecycle\Stage;
use App\Models\Lifecycle\UserSubscription;
use App\Models\User;
use App\Models\Wallet\Payment;
use App\Services\CheckoutService\CheckoutService;
use App\Services\PrivilegeService\PrivilegeService;
use App\Services\ProviderServices\PaymentService\PaymentService;
use Illuminate\Database\Eloquent\Model;

class MembershipSubscriptionService extends CheckoutService
{

    protected Stage $stage;
    protected Level $level;
    protected User $user;
    protected ?UserSubscription $membership = null;




    public static function make(): static
    {
      return new self();
    }




    public function stage(\Illuminate\Database\Eloquent\Model|Stage $record):static
    {
        $this->stage = $record;
        return $this;
    }

    public function level(Level $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function subscriber(User|\Illuminate\Contracts\Auth\Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }


    public function membership(UserSubscription $userSubscription)
    {
        $this->membership = $userSubscription;
        return $this;
    }


    /**
     * Place Subscription
     * For User
     * @return mixed
     */
    public function subscribe():mixed
    {
        $this->membership = $this->findOrCreateMembershipSubscription();
        $paymentRecord = $this->getInitPayment($this->membership,Dashboard::getUrl());
        return $this->getInitProviderOrder(
            payment: $paymentRecord,
            customer: $this->user,
            address: $this->user->addresses()->first(),
            checkoutInfo: $this->stage->name,
            successUrl: route('membership.confirm',['payment' => $paymentRecord->uuid]),
            failureUrl: route('membership.cancel_by_user',['payment' => $paymentRecord->uuid]),
            hostedCheckout: true
        );
    }


    protected function findOrCreateMembershipSubscription()
    {
        // Retrieve the existing unpaid membership for the user, if it exists
        $membershipRecord = $this->user->memberships()->where('is_paid', false)->first();

        if ($membershipRecord) {
            // Refresh the unique code if an unpaid membership exists
            $membershipRecord->refreshUniqueCode('uuid');
            $membershipRecord->save();

            return $membershipRecord;
        }

        // If no unpaid membership exists, create a new one
        return $this->user->memberships()->create([
            'amount' => $this->stage->price,
            'stage_id' => $this->stage->id,
            'level_id' => $this->level->id,
        ]);
    }




    // ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


    /**
     * Confirm Subscription (Order)
     * @return bool
     */
    public function confirm():bool
    {


        // Update Membership
        $this->membership->update(['is_paid' => true]);
//
//        // Update Payment
//        $membership->payment->update([
//            'provider_transaction_id' => $array['easepayid'],
//            'verified' => true,
//            'provider_data' => $array
//        ]);

        // Update Member Status
        $this->user = $this->membership->user;

        // Update Status
        $this->membership->user->fill([
            'status' => AuthStatusCast::SUBSCRIBED,
            'level_id' => $this->membership->level_id,
        ])->save();

        // Update Privileges
        PrivilegeService::make($this->membership->user)->afterSubscribe();

        // Dispatch the event
        event(new UserSubscribed($this->membership->user));

        return true;

    }

    public function getSubscriber(): User
    {
        return $this->user;
    }


}
