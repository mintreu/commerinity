<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Services\NotificationSmsSenderInterface;
use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Casts\TransactionTypeCast;
use App\Http\Resources\WalletResource;
use App\Models\Ecommerce\Order;
use App\Models\Geo\Country;
use App\Services\MoneyService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Minishlink\WebPush\Subscription;

final class WalletController extends Controller
{
    private const PIN_CHANGE_RATE_LIMIT = 3; // Max 3 PIN changes per hour

    private const PIN_VERIFY_RATE_LIMIT = 5; // Max 5 PIN attempts per 15 minutes

    public function __construct(
        private readonly UserWalletService $walletService,
        private readonly OtpManager $otpManager,
        private readonly NotificationSmsSenderInterface $smsService,
    ) {}

    /**
     * Get the authenticated user's wallet.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json([
            'success' => true,
            'data' => [
                'wallet' => new WalletResource($wallet),
                'summary' => $this->walletService->getWalletSummary($wallet),
                'requires_pin_setup' => ! $wallet->hasPin() || $this->isDefaultPin($wallet),
            ],
        ]);
    }

    /**
     * Get wallet transactions with rate limiting.
     */
    public function transactions(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);
        $page = max((int) $request->input('page', 1), 1);
        $includeHistory = filter_var($request->input('include_history', false), FILTER_VALIDATE_BOOL);
        $cutoff = now()->subYear();

        $recentQuery = $wallet->transactions()
            ->where('created_at', '>=', $cutoff);

        if (! $includeHistory) {
            $transactions = $recentQuery->latest()->paginate($perPage);
            $hasHistory = $wallet->historicalTransactions()->exists();

            return TransactionResource::collection($transactions)
                ->additional(['history_available' => $hasHistory]);
        }

        $recentCount = (clone $recentQuery)->count();

        $historicalQuery = $wallet->historicalTransactions()
            ->where('created_at', '<', $cutoff);

        $historicalCount = (clone $historicalQuery)->count();
        $total = $recentCount + $historicalCount;

        $offset = ($page - 1) * $perPage;
        $items = collect();

        if ($offset < $recentCount) {
            $recent = (clone $recentQuery)
                ->latest()
                ->skip($offset)
                ->take($perPage)
                ->get();

            $items = $items->concat($recent);
            $remaining = $perPage - $recent->count();

            if ($remaining > 0) {
                $historical = (clone $historicalQuery)
                    ->latest()
                    ->take($remaining)
                    ->get();

                $items = $items->concat($historical);
            }
        } else {
            $historicalOffset = $offset - $recentCount;
            $items = (clone $historicalQuery)
                ->latest()
                ->skip($historicalOffset)
                ->take($perPage)
                ->get();
        }

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return TransactionResource::collection($paginator)
            ->additional(['history_available' => $historicalCount > 0]);
    }

    /**
     * Get wallet balance summary.
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'balance_formatted' => MoneyService::format($wallet->balance),
                'hold_balance' => $wallet->hold_balance,
                'hold_balance_formatted' => MoneyService::format($wallet->hold_balance),
                'available_balance' => $wallet->available_balance,
                'available_balance_formatted' => MoneyService::format($wallet->available_balance),
                'points' => $wallet->points,
                'currency' => $wallet->currency,
                'status' => $wallet->status->value,
                'can_transact' => $wallet->canTransact(),
                'can_receive' => $wallet->canReceive(),
                'has_pin' => $wallet->hasPin() && ! $this->isDefaultPin($wallet),
                'requires_pin_setup' => ! $wallet->hasPin() || $this->isDefaultPin($wallet),
            ],
        ]);
    }

    /**
     * Set up wallet PIN for first time (no verification required).
     * Only works if PIN is not set or is default PIN.
     */
    public function setupPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'confirm_pin' => ['required', 'string', 'same:pin'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Only allow setup if PIN is not set or is default
        if ($wallet->hasPin() && ! $this->isDefaultPin($wallet)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN already set. Use change-pin endpoint to update.',
            ], 400);
        }

        // Set PIN
        $wallet->setPin($request->input('pin'));

        return response()->json([
            'success' => true,
            'message' => 'Wallet PIN set successfully',
        ]);
    }

    /**
     * Request OTP for PIN change (mobile only).
     */
    public function requestPinChangeOtp(Request $request): JsonResponse
    {
        $user = $request->user();

        // Mobile is mandatory
        if (! $user->mobile) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number is required for OTP verification',
            ], 400);
        }

        // Rate limit OTP requests
        $rateLimitKey = "wallet-pin-otp:{$user->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::PIN_CHANGE_RATE_LIMIT)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => "Too many OTP requests. Try again in {$seconds} seconds.",
            ], 429);
        }

        try {
            $result = $this->otpManager->sendOtp($user->mobile, OtpManager::CREDENTIAL_MOBILE, 'wallet_pin_change');
            RateLimiter::hit($rateLimitKey, 3600); // 1 hour window

            if (! ($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'OTP send failed',
                ], (int) ($result['code'] ?? 422));
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your mobile',
                'data' => [
                    'credential_masked' => $this->maskCredential($user->mobile, 'mobile'),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }
    }

    /**
     * Change PIN with OTP verification (mobile only).
     */
    public function changePin(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'confirm_pin' => ['required', 'string', 'same:new_pin'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Mobile is mandatory
        if (! $user->mobile) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number is required for OTP verification',
            ], 400);
        }

        // Verify OTP
        try {
            $verified = $this->otpManager->verify($user->mobile, $request->input('otp'));
            if (! $verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                ], 401);
            }
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }

        // Check if new PIN is same as old
        if ($wallet->verifyPin($request->input('new_pin'))) {
            return response()->json([
                'success' => false,
                'message' => 'New PIN cannot be the same as current PIN',
            ], 400);
        }

        // Set new PIN
        $wallet->setPin($request->input('new_pin'));

        // Clear OTP after successful PIN change
        $this->otpManager->clear($user->mobile);

        return response()->json([
            'success' => true,
            'message' => 'PIN changed successfully',
        ]);
    }

    /**
     * Verify PIN with security question fallback.
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Rate limit PIN verification attempts
        $rateLimitKey = "wallet-pin-verify:{$user->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::PIN_VERIFY_RATE_LIMIT)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Account locked for {$seconds} seconds.",
                'locked' => true,
                'retry_after' => $seconds,
            ], 429);
        }

        if (! $wallet->hasPin()) {
            return response()->json([
                'success' => false,
                'message' => 'No PIN set for this wallet',
                'requires_setup' => true,
            ], 400);
        }

        $verified = $wallet->verifyPin($request->input('pin'));

        if (! $verified) {
            RateLimiter::hit($rateLimitKey, 900); // 15 minute window
            $remaining = self::PIN_VERIFY_RATE_LIMIT - RateLimiter::attempts($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN',
                'attempts_remaining' => max(0, $remaining),
            ], 401);
        }

        // Clear rate limit on success
        RateLimiter::clear($rateLimitKey);

        return response()->json([
            'success' => true,
            'message' => 'PIN verified',
        ]);
    }

    /**
     * Send money to another user (requires PIN verification).
     */
    public function sendMoney(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'recipient_mobile' => ['required', 'string', 'regex:/^\\+?[0-9\\s-]{10,15}$/'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'], // Amount in rupees
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Verify PIN first
        $pinResult = $this->verifyPinForTransaction($wallet, $request->input('pin'), $user->id);
        if ($pinResult !== true) {
            return $pinResult;
        }

        $rawMobile = (string) $request->input('recipient_mobile');
        $digits = preg_replace('/\\D/', '', $rawMobile) ?? '';

        $recipient = null;
        if (strlen($digits) === 10) {
            $recipient = \App\Models\User::where('mobile', $digits)->first();
        }

        if (! $recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found',
            ], 404);
        }

        if ($recipient->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send money to yourself',
            ], 400);
        }

        $recipientWallet = $this->walletService->getOrCreateWallet($recipient);

        if (! $recipientWallet->canReceive()) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient cannot receive funds at this time',
            ], 400);
        }

        // Convert rupees to paisa
        $amountInPaisa = (int) ($request->input('amount') * 100);

        if (! $wallet->hasSufficientBalance($amountInPaisa)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
            ], 400);
        }

        try {
            $result = $this->walletService->transfer(
                $wallet,
                $recipientWallet,
                $amountInPaisa,
                'p2p_transfer',
                $request->input('note') ?? "Transfer to {$recipient->name}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Money sent successfully',
                'data' => [
                    'transaction' => new TransactionResource($result['debit']),
                    'recipient_name' => $recipient->name,
                    'amount_formatted' => MoneyService::format($amountInPaisa),
                    'new_balance_formatted' => MoneyService::format($wallet->fresh()->balance),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Withdraw money to bank account (requires PIN verification).
     */
    public function withdraw(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'amount' => ['required', 'numeric', 'min:100', 'max:200000'], // Min ₹100, Max ₹2L
            'beneficiary_uuid' => ['required', 'string'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Verify PIN first
        $pinResult = $this->verifyPinForTransaction($wallet, $request->input('pin'), $user->id);
        if ($pinResult !== true) {
            return $pinResult;
        }

        // Validate beneficiary belongs to user's wallet and is verified
        $beneficiary = $wallet->beneficiaryAccounts()
            ->where('uuid', $request->input('beneficiary_uuid'))
            ->where('status', \App\Casts\BeneficiaryStatusCast::VERIFIED)
            ->first();

        if (! $beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or unverified beneficiary account',
            ], 400);
        }

        // Convert rupees to paisa
        $amountInPaisa = (int) ($request->input('amount') * 100);

        // Check minimum withdrawal threshold from wallet config
        if (! $wallet->meetsWithdrawalThreshold($amountInPaisa)) {
            $minimumAmount = MoneyService::format($wallet->getMinimumWithdrawalAmount());

            return response()->json([
                'success' => false,
                'message' => "Minimum withdrawal amount is {$minimumAmount}",
                'minimum_amount' => $wallet->getMinimumWithdrawalAmount(),
            ], 400);
        }

        if (! $wallet->hasSufficientBalance($amountInPaisa)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
            ], 400);
        }

        try {
            // Hold the funds first (will be released after payout)
            $transaction = $this->walletService->hold(
                $wallet,
                $amountInPaisa,
                'withdrawal',
                "Withdrawal to bank account ending {$beneficiary->account_number_masked}",
                TransactionTypeCast::WITHDRAWAL
            );

            // Update transaction with beneficiary info
            $transaction->update([
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'beneficiary_id' => $beneficiary->id,
                    'beneficiary_uuid' => $beneficiary->uuid,
                    'account_number_masked' => $beneficiary->account_number_masked,
                    'bank_name' => $beneficiary->bank_name,
                    'upi_id' => $beneficiary->upi_id,
                    'holder_name' => $beneficiary->holder_name,
                ]),
            ]);

            // Queue payout job to payment provider (Cashfree/Razorpay)
            \App\Jobs\Wallet\ProcessPayoutJob::dispatch(
                $transaction->id,
                $beneficiary->id
            )->onQueue('payouts');

            if ($user->mobile && $this->smsService->canSend(1)) {
                $this->smsService->sendTemplate(
                    phone: $user->mobile,
                    templateSlug: 'withdrawal-request',
                    variables: [
                        'number' => MoneyService::toRupeesString($transaction->amount),
                    ],
                    type: 'transactional',
                    userId: $user->id,
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted. Funds will be transferred within 24-48 hours.',
                'data' => [
                    'transaction' => new TransactionResource($transaction),
                    'amount_formatted' => MoneyService::format($amountInPaisa),
                    'new_available_balance_formatted' => MoneyService::format($wallet->fresh()->available_balance),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Pay via wallet for orders/subscriptions (requires PIN verification).
     */
    public function payViaWallet(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'amount' => ['required', 'numeric', 'min:1'],
            'purpose' => ['required', 'string', 'in:order,subscription,service'],
            'reference_type' => ['nullable', 'string'],
            'reference_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Verify PIN first
        $pinResult = $this->verifyPinForTransaction($wallet, $request->input('pin'), $user->id);
        if ($pinResult !== true) {
            return $pinResult;
        }

        // Convert rupees to paisa
        $amountInPaisa = (int) ($request->input('amount') * 100);

        if (! $wallet->hasSufficientBalance($amountInPaisa)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance',
            ], 400);
        }

        // Get related model if reference provided
        $relatedModel = null;
        if ($request->filled('reference_type') && $request->filled('reference_id')) {
            // Map reference types to models
            $modelMap = [
                'order' => Order::class,
                'subscription' => Subscription::class,
            ];

            $modelClass = $modelMap[$request->input('reference_type')] ?? null;
            if ($modelClass && class_exists($modelClass)) {
                $relatedModel = $modelClass::find($request->input('reference_id'));
            }
        }

        try {
            $transaction = $this->walletService->debit(
                $wallet,
                $amountInPaisa,
                $request->input('purpose'),
                $request->input('description') ?? 'Wallet payment',
                $relatedModel
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'data' => [
                    'transaction' => new TransactionResource($transaction),
                    'amount_formatted' => MoneyService::format($amountInPaisa),
                    'new_balance_formatted' => MoneyService::format($wallet->fresh()->balance),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's security questions (keys only, not answers).
     */

    /**
     * Get wallet statistics (for dashboard cards).
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        $monthlyCredits = $wallet->transactions()
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('net_amount');

        $monthlyDebits = $wallet->transactions()
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('net_amount');

        $pendingAmount = $wallet->transactions()
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $recentTransactionCount = $wallet->transactions()
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'balance_formatted' => MoneyService::format($wallet->balance),
                'available_balance' => $wallet->available_balance,
                'available_balance_formatted' => MoneyService::format($wallet->available_balance),
                'hold_balance' => $wallet->hold_balance,
                'hold_balance_formatted' => MoneyService::format($wallet->hold_balance),
                'total_credited' => $wallet->total_credited,
                'total_credited_formatted' => MoneyService::format($wallet->total_credited),
                'total_debited' => $wallet->total_debited,
                'total_debited_formatted' => MoneyService::format($wallet->total_debited),
                'points' => $wallet->points,
                'monthly_credits' => $monthlyCredits,
                'monthly_credits_formatted' => MoneyService::format((int) $monthlyCredits),
                'monthly_debits' => $monthlyDebits,
                'monthly_debits_formatted' => MoneyService::format((int) $monthlyDebits),
                'pending_amount' => $pendingAmount,
                'pending_amount_formatted' => MoneyService::format((int) $pendingAmount),
                'recent_transaction_count' => $recentTransactionCount,
                'requires_pin_setup' => ! $wallet->hasPin() || $this->isDefaultPin($wallet),
            ],
        ]);
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    /**
     * Verify PIN for financial transactions with rate limiting.
     *
     * @return bool|JsonResponse Returns true if verified, JsonResponse if failed
     */
    private function verifyPinForTransaction(\App\Models\Wallet $wallet, string $pin, int $userId): bool|JsonResponse
    {
        // Check if PIN is set up
        if (! $wallet->hasPin() || $this->isDefaultPin($wallet)) {
            return response()->json([
                'success' => false,
                'message' => 'Please set up your wallet PIN before making transactions',
                'requires_pin_setup' => true,
            ], 400);
        }

        // Rate limit PIN verification
        $rateLimitKey = "wallet-pin-verify:{$userId}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::PIN_VERIFY_RATE_LIMIT)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => "Too many incorrect PIN attempts. Try again in {$seconds} seconds.",
                'locked' => true,
                'retry_after' => $seconds,
            ], 429);
        }

        // Verify PIN
        if (! $wallet->verifyPin($pin)) {
            RateLimiter::hit($rateLimitKey, 900); // 15 minute window
            $remaining = self::PIN_VERIFY_RATE_LIMIT - RateLimiter::attempts($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN',
                'attempts_remaining' => max(0, $remaining),
            ], 401);
        }

        // Clear rate limit on successful verification
        RateLimiter::clear($rateLimitKey);

        return true;
    }

    /**
     * Check if wallet has default PIN (123456).
     */
    private function isDefaultPin(\App\Models\Wallet $wallet): bool
    {
        if (! $wallet->pin) {
            return false;
        }

        return Hash::check('123456', $wallet->pin);
    }

    /**
     * Mask credential for display.
     */
    private function maskCredential(string $credential, string $type): string
    {
        if ($type === 'mobile') {
            return substr($credential, 0, 3).'****'.substr($credential, -3);
        }

        // Email
        $parts = explode('@', $credential);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $maskedName = substr($name, 0, 2).str_repeat('*', max(0, strlen($name) - 2));

        return $maskedName.'@'.$domain;
    }

    /**
     * Initiate wallet topup (add money)
     *
     * Creates transaction and returns checkout URL
     */
    public function topup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'], // ₹1 to ₹1,00,000
            // 'payment_method' => ['nullable', 'string', 'in:wallet,cashfree,razorpay,upi,card'], // Optional payment method
        ]);

        $user = $request->user();

        $wallet = $this->walletService->getOrCreateWallet($user);

        // Convert rupees to paisa
        $amountInPaisa = (int) round($validated['amount'] * 100);

        // Determine payment method (default: cashfree)
        $paymentMethod = \App\Casts\PaymentMethodCast::tryFrom($validated['payment_method'] ?? 'cashfree')
            ?? \App\Casts\PaymentMethodCast::CASHFREE;

        try {
            // Create transaction using HasTransaction trait
            $transaction = $wallet->createCreditTransaction(
                customer: $user,
                amount: $amountInPaisa,
                paymentMethod: $paymentMethod, // ⭐ NOW SWITCHABLE!
                redirectSuccessUrl: config('app.client_url').'/wallet?status=success',
                redirectFailureUrl: config('app.client_url').'/wallet?status=failed',
                wallet: $wallet,
                purpose: 'Wallet TopUp',
                expireAfterMinutes: 60
            );

            return response()->json([
                'success' => true,
                'message' => 'Checkout initiated successfully',
                'data' => [
                    'transaction_id' => $transaction->uuid,
                    'checkout_url' => route('checkout', ['transaction' => $transaction->uuid]),  // always checkout with apiserver side
                    'amount' => $transaction->amount,
                    'amount_formatted' => MoneyService::format($transaction->amount),
                    'payment_method' => $transaction->payment_method->value,
                    'expires_at' => $transaction->expires_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate checkout: '.$e->getMessage(),
            ], 500);
        }
    }
}
