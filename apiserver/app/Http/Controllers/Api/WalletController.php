<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Services\MoneyService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

final class WalletController extends Controller
{
    private const PIN_CHANGE_RATE_LIMIT = 3; // Max 3 PIN changes per hour

    private const PIN_VERIFY_RATE_LIMIT = 5; // Max 5 PIN attempts per 15 minutes

    private const SECURITY_QUESTIONS = [
        'pet_name' => 'What is the name of your first pet?',
        'birth_city' => 'In which city were you born?',
        'favorite_book' => 'What is your favorite book or novel?',
        'mother_maiden' => "What is your mother's maiden name?",
        'first_school' => 'What was the name of your first school?',
        'favorite_movie' => 'What is your favorite movie?',
        'childhood_friend' => 'What is the name of your childhood best friend?',
        'favorite_teacher' => 'Who was your favorite teacher?',
    ];

    public function __construct(
        private readonly UserWalletService $walletService,
        private readonly OtpManager $otpManager,
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
                'has_security_questions' => $this->hasSecurityQuestions($wallet),
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

        $transactions = $wallet->transactions()
            ->latest()
            ->paginate($request->input('per_page', 20));

        return TransactionResource::collection($transactions);
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
     * Get available security questions.
     */
    public function getSecurityQuestions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'questions' => collect(self::SECURITY_QUESTIONS)->map(fn ($label, $key) => [
                    'key' => $key,
                    'label' => $label,
                ])->values(),
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
            'security_question_1' => ['required', 'string', 'in:'.implode(',', array_keys(self::SECURITY_QUESTIONS))],
            'security_answer_1' => ['required', 'string', 'min:2', 'max:100'],
            'security_question_2' => ['required', 'string', 'in:'.implode(',', array_keys(self::SECURITY_QUESTIONS)), 'different:security_question_1'],
            'security_answer_2' => ['required', 'string', 'min:2', 'max:100'],
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

        // Store security questions (hashed answers)
        $this->setSecurityQuestions($wallet, [
            $request->input('security_question_1') => $request->input('security_answer_1'),
            $request->input('security_question_2') => $request->input('security_answer_2'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet PIN and security questions set successfully',
        ]);
    }

    /**
     * Request OTP for PIN change.
     */
    public function requestPinChangeOtp(Request $request): JsonResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:mobile,email'],
        ]);

        $user = $request->user();

        // Rate limit OTP requests
        $rateLimitKey = "wallet-pin-otp:{$user->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::PIN_CHANGE_RATE_LIMIT)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => "Too many OTP requests. Try again in {$seconds} seconds.",
            ], 429);
        }

        $method = $request->input('method');
        $credential = $method === 'mobile' ? $user->mobile : $user->email;

        if (! $credential) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($method).' not available for this account',
            ], 400);
        }

        try {
            $this->otpManager->generate($credential);
            RateLimiter::hit($rateLimitKey, 3600); // 1 hour window

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your '.$method,
                'data' => [
                    'credential_masked' => $this->maskCredential($credential, $method),
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
     * Change PIN with OTP verification.
     */
    public function changePin(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'method' => ['required', 'string', 'in:mobile,email'],
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'confirm_pin' => ['required', 'string', 'same:new_pin'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Get credential based on method
        $method = $request->input('method');
        $credential = $method === 'mobile' ? $user->mobile : $user->email;

        if (! $credential) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($method).' not available for this account',
            ], 400);
        }

        // Verify OTP
        try {
            $verified = $this->otpManager->verify($credential, $request->input('otp'));
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
        $this->otpManager->clear($credential);

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
     * Verify security question answer (for account recovery).
     */
    public function verifySecurityQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'question_key' => ['required', 'string', 'in:'.implode(',', array_keys(self::SECURITY_QUESTIONS))],
            'answer' => ['required', 'string'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Rate limit security question attempts
        $rateLimitKey = "wallet-security:{$user->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        $verified = $this->verifySecurityAnswer(
            $wallet,
            $request->input('question_key'),
            $request->input('answer')
        );

        if (! $verified) {
            RateLimiter::hit($rateLimitKey, 1800); // 30 minute window

            return response()->json([
                'success' => false,
                'message' => 'Security answer incorrect',
            ], 401);
        }

        RateLimiter::clear($rateLimitKey);

        // Generate temporary token for PIN reset
        $resetToken = $this->generatePinResetToken($wallet);

        return response()->json([
            'success' => true,
            'message' => 'Security question verified',
            'data' => [
                'reset_token' => $resetToken,
                'expires_in' => 300, // 5 minutes
            ],
        ]);
    }

    /**
     * Reset PIN using security question token.
     */
    public function resetPinWithToken(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'confirm_pin' => ['required', 'string', 'same:new_pin'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Verify reset token
        $tokenKey = "wallet-pin-reset:{$wallet->id}";
        $storedToken = Cache::get($tokenKey);

        if (! $storedToken || ! hash_equals($storedToken, $request->input('reset_token'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token',
            ], 401);
        }

        // Set new PIN
        $wallet->setPin($request->input('new_pin'));

        // Clear reset token
        Cache::forget($tokenKey);

        return response()->json([
            'success' => true,
            'message' => 'PIN reset successfully',
        ]);
    }

    /**
     * Send money to another user (requires PIN verification).
     */
    public function sendMoney(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'recipient_mobile' => ['required', 'string', 'size:10'],
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

        // Find recipient by mobile
        $recipient = \App\Models\User::where('mobile', $request->input('recipient_mobile'))->first();

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
                "Withdrawal to bank account ending {$beneficiary->account_number_masked}"
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
                'order' => \App\Models\Order::class,
                'subscription' => \App\Models\Subscription::class,
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
    public function getUserSecurityQuestions(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $metadata = $wallet->metadata ?? [];
        $userQuestions = $metadata['security_questions'] ?? [];

        $questions = [];
        foreach (array_keys($userQuestions) as $key) {
            if (isset(self::SECURITY_QUESTIONS[$key])) {
                $questions[] = [
                    'key' => $key,
                    'label' => self::SECURITY_QUESTIONS[$key],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions,
                'has_questions' => count($questions) >= 2,
            ],
        ]);
    }

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
     * Check if wallet has security questions set.
     */
    private function hasSecurityQuestions(\App\Models\Wallet $wallet): bool
    {
        $metadata = $wallet->metadata ?? [];

        return isset($metadata['security_questions']) && count($metadata['security_questions']) >= 2;
    }

    /**
     * Set security questions for wallet.
     */
    private function setSecurityQuestions(\App\Models\Wallet $wallet, array $questionsAnswers): void
    {
        $metadata = $wallet->metadata ?? [];

        $hashedQuestions = [];
        foreach ($questionsAnswers as $questionKey => $answer) {
            $hashedQuestions[$questionKey] = Hash::make(strtolower(trim($answer)));
        }

        $metadata['security_questions'] = $hashedQuestions;
        $wallet->metadata = $metadata;
        $wallet->save();
    }

    /**
     * Verify security question answer.
     */
    private function verifySecurityAnswer(\App\Models\Wallet $wallet, string $questionKey, string $answer): bool
    {
        $metadata = $wallet->metadata ?? [];

        if (! isset($metadata['security_questions'][$questionKey])) {
            return false;
        }

        return Hash::check(strtolower(trim($answer)), $metadata['security_questions'][$questionKey]);
    }

    /**
     * Generate temporary PIN reset token.
     */
    private function generatePinResetToken(\App\Models\Wallet $wallet): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenKey = "wallet-pin-reset:{$wallet->id}";

        Cache::put($tokenKey, $token, now()->addMinutes(5));

        return $token;
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
}
