<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\Transaction\WalletResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\LaravelTransaction\Casts\WalletStatusCast;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

class WalletController extends Controller
{


    public function index(Request $request): WalletResource|JsonResponse
    {
        $user = $request->user();

        $user->load([
            'wallet',
            'wallet.transactions' => fn($q) => $q->latest()->limit(5),
            'wallet.beneficiary',
        ]);

        if (is_null($user->wallet)) {
            return response()->json([
                'data' => [
                    'wallet' => null,
                    'transactions' => null,
                    'beneficiary' => null,
                    'stats' => null,
                ],
            ]);
        }

        $wallet = $user->wallet;

        // Calculate comprehensive statistics
        $stats = $this->calculateWalletStats($wallet);

        // Attach stats to wallet for resource access
        $wallet->stats = $stats;

        return WalletResource::make($wallet);
    }

    /**
     * Calculate comprehensive wallet statistics
     */
    protected function calculateWalletStats($wallet): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // Base query for today's completed transactions only
        $todayQuery = $wallet->transactions()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->where('status', TransactionStatusCast::COMPLETED->value);

        // Today's activity by type (completed only)
        $todayCredits = (clone $todayQuery)
            ->where('type', TransactionTypeCast::CREDITED->value)
            ->sum('amount');

        $todayDebits = (clone $todayQuery)
            ->where('type', TransactionTypeCast::DEBITED->value)
            ->sum('amount');

        // Net today activity (credits - debits)
        $todayNetActivity = $todayCredits - $todayDebits;

        // Total transaction counts by type (all time, completed only)
        $totalCreditsCount = $wallet->transactions()
            ->where('type', TransactionTypeCast::CREDITED->value)
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->count();

        $totalDebitsCount = $wallet->transactions()
            ->where('type', TransactionTypeCast::DEBITED->value)
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->count();

        $totalTransactions = $totalCreditsCount + $totalDebitsCount;

        // Pending transactions count
        $pendingCount = $wallet->transactions()
            ->whereIn('status', [
                TransactionStatusCast::PENDING->value,
                TransactionStatusCast::PROCESSING->value,
            ])
            ->count();

        // Failed transactions count (for reference)
        $failedCount = $wallet->transactions()
            ->whereIn('status', [
                TransactionStatusCast::FAILED->value,
                TransactionStatusCast::CANCELLED->value,
            ])
            ->count();

        // Last transaction (any status)
        $lastTransaction = $wallet->transactions()
            ->latest()
            ->first();

        // Last completed transaction
        $lastCompletedTransaction = $wallet->transactions()
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->latest()
            ->first();

        // Beneficiary count
        $beneficiaryCount = $wallet->beneficiaries()
            ->where('beneficiary_active', true)
            ->count();

        $hasDefaultBeneficiary = $wallet->beneficiaries()
            ->where('default', true)
            ->where('beneficiary_active', true)
            ->exists();

        // This week's activity (completed only)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weekCredits = $wallet->transactions()
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('type', TransactionTypeCast::CREDITED->value)
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->sum('amount');

        $weekDebits = $wallet->transactions()
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('type', TransactionTypeCast::DEBITED->value)
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->sum('amount');

        // This month's activity (completed only)
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthCredits = $wallet->transactions()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('type', TransactionTypeCast::CREDITED->value)
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->sum('amount');

        $monthDebits = $wallet->transactions()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('type', TransactionTypeCast::DEBITED->value)
            ->where('status', TransactionStatusCast::COMPLETED->value)
            ->sum('amount');

        return [
            // Today's stats
            'today' => [
                'credits' => $todayCredits,
                'debits' => $todayDebits,
                'net_activity' => $todayNetActivity,
            ],

            // This week's stats
            'week' => [
                'credits' => $weekCredits,
                'debits' => $weekDebits,
                'net_activity' => $weekCredits - $weekDebits,
            ],

            // This month's stats
            'month' => [
                'credits' => $monthCredits,
                'debits' => $monthDebits,
                'net_activity' => $monthCredits - $monthDebits,
            ],

            // Transaction counts
            'counts' => [
                'total' => $totalTransactions,
                'credits' => $totalCreditsCount,
                'debits' => $totalDebitsCount,
                'pending' => $pendingCount,
                'failed' => $failedCount,
            ],

            // Beneficiary info
            'beneficiary' => [
                'count' => $beneficiaryCount,
                'has_default' => $hasDefaultBeneficiary,
            ],

            // Last transactions
            'last_transaction' => $lastTransaction ? [
                'id' => $lastTransaction->id,
                'uuid' => $lastTransaction->uuid,
                'purpose' => $lastTransaction->purpose,
                'amount' => $lastTransaction->amount,
                'type' => $lastTransaction->type->value,
                'status' => $lastTransaction->status->value,
                'created_at' => $lastTransaction->created_at,
            ] : null,

            'last_completed_transaction' => $lastCompletedTransaction ? [
                'id' => $lastCompletedTransaction->id,
                'uuid' => $lastCompletedTransaction->uuid,
                'purpose' => $lastCompletedTransaction->purpose,
                'amount' => $lastCompletedTransaction->amount,
                'type' => $lastCompletedTransaction->type->value,
                'created_at' => $lastCompletedTransaction->created_at,
            ] : null,
        ];
    }


    // Wallet CURD METHODS

    /**
     * Create User Wallet When None Have
     * Unlock Wallet for User
     * @param Request $request
     * @return WalletResource|JsonResponse
     */
    public function create(Request $request): WalletResource|JsonResponse
    {
        $user = Auth::user();
        $user->load('wallet');

        if ($user->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet already exists.',
            ], 200);
        }

        $validated = $request->validate([
            'pin' => ['nullable', 'digits:6'],
        ]);

        $wallet = WalletService::create(owner: $user, pin: $validated['pin'] ?? null);

        return WalletResource::make($wallet);
    }

    public function qr(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet;
        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
        }
        return response()->json([
            'data' => [
                'uuid' => $wallet->uuid,
                'qr'   => $wallet->getQRCode(),
            ],
        ]);
    }

    public function changePin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin'         => ['required', 'string', 'min:4', 'max:6'],
            'confirm_pin' => ['required', 'same:pin'],
            'old_pin'     => ['nullable', 'string', 'min:4', 'max:6'],
        ]);

        $wallet = $request->user()->wallet;
        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
        }

        // Optional old pin verification (enforce if desired)
        if (!empty($validated['old_pin']) && !$wallet->verifyPin($validated['old_pin'])) {
            return response()->json(['success' => false, 'message' => 'Old PIN is incorrect.'], 422);
        }

        $wallet->pin = $validated['pin']; // hashed by mutator
        $wallet->save();

        return response()->json(['success' => true, 'message' => 'PIN changed successfully.']);
    }


    /**
     * Return Checkout Url
     * @param Request $request
     * @return JsonResponse
     */
    public function addMoney(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'reference' => ['nullable', 'string', 'max:255'],
            'pin' => ['required']
        ]);

        $user = $request->user();
        $wallet = $user->wallet;



        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
        }

        $amount = (int) round(((float) $validated['amount']) * 100);
        $checkoutUrl = WalletService::make($wallet)
            ->addFund($amount)
            ->getCheckoutUrl(
                redirect_success_url: config('app.client_url').'/dashboard/wallet',
                redirect_failure_url: config('app.client_url').'/dashboard/wallet',
            );


        // On Successfully Return Checkout Url
        return response()->json([
            'success' => !is_null($checkoutUrl),
            'message' => 'Money added successfully.',
            'redirect' => $checkoutUrl
        ]);
    }


    /**
     * Return true or false
     * Generate a Pending Or Holding Status Transaction For it
     * @param Request $request
     * @return JsonResponse
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'], // threshold: ₹100
            'pin'    => ['nullable', 'string', 'min:4', 'max:6'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
        }

        // Require default beneficiary (bank)
        $wallet->load('beneficiary');
        if (!$wallet->beneficiary || empty($wallet->beneficiary->account_number)) {
            return response()->json(['success' => false, 'message' => 'No default bank account set.'], 422);
        }

        // Optional: verify PIN for withdrawals
        if (!empty($validated['pin']) && !$wallet->verifyPin($validated['pin'])) {
            return response()->json(['success' => false, 'message' => 'Invalid PIN.'], 422);
        }

        try {
            DB::transaction(function () use ($wallet, $validated) {
                $locked = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

                $amount = (float) $validated['amount'];
                if ($amount > (float) $locked->balance) {
                    abort(422, 'Insufficient balance.');
                }

                $locked->balance = (float) $locked->balance - $amount;
                $locked->save();

                $locked->transactions()->create([
                    'type'   => TransactionTypeCast::DEBITED->value,
                    'purpose'=> 'wallet_withdrawal',
                    'amount' => $amount,
                    'status' => 'pending', // set success after payout provider confirms
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Withdrawal initiated.']);
    }


    /**
     * Send Money return true or false
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1'],
            'recipient_uuid' => ['required', 'uuid'],
            'pin'            => ['nullable', 'digit:6'],
        ]);

        $sender = $request->user()->wallet;
        if (!$sender) {
            return response()->json(['success' => false, 'message' => 'Sender wallet not found.'], 404);
        }

        // Optional PIN check for P2P
        if (!empty($validated['pin']) && !$sender->verifyPin($validated['pin'])) {
            return response()->json(['success' => false, 'message' => 'Invalid PIN.'], 422);
        }

        $recipient = Wallet::where('uuid', $validated['recipient_uuid'])->first();
        if (!$recipient) {
            return response()->json(['success' => false, 'message' => 'Recipient wallet not found.'], 404);
        }

        $amount = (float) $validated['amount'];

        try {
            DB::transaction(function () use ($sender, $recipient, $amount) {
                $senderLocked = Wallet::whereKey($sender->id)->lockForUpdate()->first();
                $recipientLocked = Wallet::whereKey($recipient->id)->lockForUpdate()->first();

                if ($amount > (float) $senderLocked->balance) {
                    abort(422, 'Insufficient balance.');
                }

                // Debit sender
                $senderLocked->balance = (float) $senderLocked->balance - $amount;
                $senderLocked->save();
                $senderLocked->transactions()->create([
                    'type'   => TransactionTypeCast::DEBITED->value,
                    'purpose' => 'p2p_transfer_out',
                    'amount'  => $amount,
                    'status'  => 'success',
                    'metadata'=> ['to' => $recipientLocked->uuid],
                ]);

                // Credit recipient
                $recipientLocked->balance = (float) $recipientLocked->balance + $amount;
                $recipientLocked->save();
                $recipientLocked->transactions()->create([
                    'type'   => TransactionTypeCast::CREDITED->value,
                    'purpose' => 'p2p_transfer_in',
                    'amount'  => $amount,
                    'status'  => 'success',
                    'metadata'=> ['from' => $senderLocked->uuid],
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Transfer successful.']);
    }





    /**
     * Return true or false
     * @param Request $request
     * @return JsonResponse
     */
    public function pointToBalanceConversion(Request $request):JsonResponse
    {
        $validated = $request->validate([
           'pin'    => ['required'],
           'points' => ['required', 'numeric', 'min:1'],
        ]);

        $user = $request->user();
        $user->load('wallet');
        $wallet = $user->wallet;
        $currentPoints = $wallet->points;


        if ($validated['points'] > $currentPoints)
        {
            return response()->json(['success' => false, 'message' => 'You only have '.$currentPoints.' points in total for conversion']);
        }else{

            $conversionRatio = 10;  // some ratio later use config value here..
            $pointAmount = $validated['points'] * $conversionRatio;
            $newBalance = LaravelMoney::make($pointAmount)->plus($wallet->balance)->getAmount();
            $newPoint = $wallet->points - $validated['points'];

            // Here we later ad a internal transaction for it..


            $wallet->update([
               'balance' => $newBalance,
               'points' => $newPoint,
            ]);
            return response()->json(['success' => true, 'message' => 'Transfer successful.']);
        }

    }












}
