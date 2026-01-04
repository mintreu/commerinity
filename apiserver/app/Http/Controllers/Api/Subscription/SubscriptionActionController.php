<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Services\UserServices\UserWalletService;
use App\Casts\PaymentMethodCast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SubscriptionActionController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
    ) {}

    /**
     * Subscribe to a plan
     *
     * POST /api/subscription/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'plan_uuid' => ['required', 'string', 'exists:stages,uuid'],
            'payment_method' => ['required', 'string', 'in:wallet,online'],
            'pin' => ['required_if:payment_method,wallet', 'nullable', 'string', 'size:6'],
        ]);

        $stage = Stage::where('uuid', $request->plan_uuid)->firstOrFail();
        $paymentMethod = $request->payment_method === 'wallet' ? PaymentMethodCast::WALLET : PaymentMethodCast::CASHFREE;

        try {
            return DB::transaction(function () use ($user, $stage, $paymentMethod, $request) {
                // Check if user already has an active subscription
                if (UserSubscription::where('user_id', $user->id)->where('status', UserSubscription::STATUS_ACTIVE)->exists()) {
                     throw new \Exception('You already have an active subscription');
                }

                // Check for existing pending transaction
                // We need to fetch the pending subscription if it exists
                $subscription = UserSubscription::where('user_id', $user->id)
                    ->where('stage_id', $stage->id)
                    ->where('status', UserSubscription::STATUS_PENDING)
                    ->first();

                if ($subscription && $subscription->hasPaymentTransaction()) {
                    $transaction = $subscription->getActivePaymentTransaction();
                    if ($transaction->status === TransactionStatusCast::PENDING && $transaction->payment_method !== PaymentMethodCast::WALLET) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Subscription payment already initiated. Redirecting...',
                            'data' => [
                                'subscription_uuid' => $subscription->uuid,
                                'checkout_url' => route('checkout.show', ['transaction' => $transaction->uuid]),
                            ]
                        ], 201);
                    }
                }

                if (! $subscription) {
                    // Create the Subscription record (Pending status)
                    $subscription = UserSubscription::create([
                        'user_id' => $user->id,
                        'stage_id' => $stage->id,
                        'amount' => $stage->price,
                        'status' => UserSubscription::STATUS_PENDING,
                    ]);
                }

                // Handle Wallet Payment
                if ($paymentMethod === PaymentMethodCast::WALLET) {
                    $wallet = $this->walletService->getOrCreateWallet($user);

                    if (! $wallet->hasSufficientBalance($stage->price)) {
                         throw new \Exception('Insufficient wallet balance');
                    }

                    // PIN Verification
                    if (! $this->walletService->verifyPin($wallet, $request->pin)) {
                         throw new \Exception('Invalid wallet PIN');
                    }

                    // Debit wallet
                    $transaction = $this->walletService->debit(
                        $wallet,
                        $stage->price,
                        'subscription_payment',
                        "Subscription to {$stage->name}"
                    );

                    // Activate subscription
                    $subscription->update([
                        'status' => UserSubscription::STATUS_ACTIVE,
                        'starts_at' => now(),
                        'expires_at' => now()->addYear(),
                        'paid_at' => now(),
                    ]);

                    // Link transaction
                    $transaction->update([
                        'transactionable_type' => get_class($subscription),
                        'transactionable_id' => $subscription->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Subscription activated successfully via wallet',
                        'data' => [
                            'subscription_uuid' => $subscription->uuid,
                            'status' => $subscription->status,
                        ]
                    ], 201);
                }

                // External Payment (Online)
                // Use HasTransaction trait on UserSubscription model?
                // I need to check if UserSubscription model has the trait.

                // I'll create the transaction for the subscription
                 $transaction = $subscription->createDebitTransaction(
                    customer: $user,
                    paymentMethod: PaymentMethodCast::CASHFREE,
                    redirectSuccessUrl: config('app.client_url')."/subscription?payment=success",
                    redirectFailureUrl: config('app.client_url')."/subscription?payment=failed",
                    wallet: $this->walletService->getOrCreateWallet($user),
                    purpose: "Subscription Payment for {$stage->name}"
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription created. Redirecting to payment...',
                    'data' => [
                        'subscription_uuid' => $subscription->uuid,
                        'checkout_url' => route('checkout.show', ['transaction' => $transaction->uuid]),
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
