<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Career;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\JobApplication;
use App\Services\Recruitment\JobApplicationService;
use App\Services\UserServices\UserWalletService;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class JobApplicationPaymentController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
        private readonly JobApplicationService $applicationService,
    ) {}

    /**
     * Pay application fee
     *
     * POST /api/my-applications/{uuid}/pay
     */
    public function pay(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $application = JobApplication::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($application->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Application fee is already paid',
            ], 400);
        }

        if ($application->hasPaymentTransaction()) {
             $transaction = $application->getActivePaymentTransaction();
             if ($transaction->status === TransactionStatusCast::SUCCESS) {
                  return response()->json([
                      'success' => false,
                      'message' => 'Payment already completed for this application',
                  ], 400);
             }

             if ($transaction->status === TransactionStatusCast::PENDING && $transaction->payment_method !== PaymentMethodCast::WALLET) {
                  return response()->json([
                      'success' => true,
                      'message' => 'Payment already initiated. Redirecting...',
                      'data' => [
                          'application_uuid' => $application->uuid,
                          'checkout_url' => route('checkout.show', ['transaction' => $transaction->uuid]),
                      ]
                  ], 200);
             }
        }

        $request->validate([
            'payment_method' => ['required', 'string', 'in:wallet,online'],
            'pin' => ['required_if:payment_method,wallet', 'nullable', 'string', 'size:6'],
        ]);

        $paymentMethod = $request->payment_method === 'wallet' ? PaymentMethodCast::WALLET : PaymentMethodCast::CASHFREE;

        try {
            return DB::transaction(function () use ($user, $application, $paymentMethod, $request) {
                // Handle Wallet Payment
                if ($paymentMethod === PaymentMethodCast::WALLET) {
                    $wallet = $this->walletService->getOrCreateWallet($user);

                    if (! $wallet->hasSufficientBalance($application->amount)) {
                         throw new \Exception('Insufficient wallet balance');
                    }

                    // PIN Verification
                    if (! $this->walletService->verifyPin($wallet, $request->pin)) {
                         throw new \Exception('Invalid wallet PIN');
                    }

                    // Debit wallet
                    $transaction = $this->walletService->debit(
                        $wallet,
                        $application->amount,
                        'job_application_fee',
                        "Application fee for {$application->recruitment->title}"
                    );

                    // Mark as paid
                    $application->update([
                        'is_paid' => true,
                        'paid_at' => now(),
                        'status' => 'submitted', // Or next logical status
                    ]);

                    // Link transaction
                    $transaction->update([
                        'transactionable_type' => get_class($application),
                        'transactionable_id' => $application->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Application fee paid successfully via wallet',
                        'data' => [
                            'application_uuid' => $application->uuid,
                            'status' => $application->status,
                        ]
                    ], 200);
                }

                // External Payment (Online)
                // Use JobApplicationService to initiate payment
                $transaction = $application->createDebitTransaction(
                    customer: $user,
                    paymentMethod: PaymentMethodCast::CASHFREE,
                    redirectSuccessUrl: config('app.client_url')."/career/applications/{$application->uuid}?payment=success",
                    redirectFailureUrl: config('app.client_url')."/career/applications/{$application->uuid}?payment=failed",
                    wallet: $this->walletService->getOrCreateWallet($user),
                    purpose: "Job Application Fee for {$application->recruitment->title}"
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated. Redirecting...',
                    'data' => [
                        'application_uuid' => $application->uuid,
                        'checkout_url' => route('checkout.show', ['transaction' => $transaction->uuid]),
                    ]
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
