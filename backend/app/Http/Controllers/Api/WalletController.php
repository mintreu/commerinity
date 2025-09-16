<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\Transaction\WalletResource;
use App\Http\Resources\Transaction\BeneficiaryAccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\WalletStatusCast;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Models\BeneficiaryAccount;

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
                ],
            ]);
        }

        return WalletResource::make($user->wallet);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $user->load('wallet');

        if ($user->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet already exists.',
            ], 200);
        }

        $userDob = $user->dob instanceof Carbon ? $user->dob : Carbon::parse($user->dob);
        $birthYear = $userDob?->format('Y') ?? '0000';

        $validated = $request->validate([
            'pin' => ['nullable', 'string', 'min:4', 'max:6'],
        ]);

        $wallet = $user->wallet()->create([
            'uuid'     => (string) Str::uuid(),
            'pin'      => $validated['pin'] ?? $birthYear, // hashed by mutator
            'balance'  => 0.00,
            'currency' => LaravelMoney::defaultCurrency(),
            'status'   => WalletStatusCast::ACTIVE,
        ]);

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

    public function addMoney(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
        }

        DB::transaction(function () use ($wallet, $validated) {
            // Lock for update to avoid race conditions
            $locked = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            $amount = (float) $validated['amount'];
            $locked->balance = (float) $locked->balance + $amount;
            $locked->save();

            $locked->transactions()->create([
                'type'   => 'credit',
                'purpose'=> 'wallet_topup',
                'amount' => $amount,
                'status' => 'success',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Money added successfully.',
            'redirect' => 'redirect_url'
        ]);
    }

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
                    'type'   => 'debit',
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

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1'],
            'recipient_uuid' => ['required', 'uuid'],
            'pin'            => ['nullable', 'string', 'min:4', 'max:6'],
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
                    'type'    => 'debit',
                    'purpose' => 'p2p_transfer_out',
                    'amount'  => $amount,
                    'status'  => 'success',
                    'metadata'=> ['to' => $recipientLocked->uuid],
                ]);

                // Credit recipient
                $recipientLocked->balance = (float) $recipientLocked->balance + $amount;
                $recipientLocked->save();
                $recipientLocked->transactions()->create([
                    'type'    => 'credit',
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

    public function transactions(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;
        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
        }

        $query = $wallet->transactions();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $perPage = (int) $request->get('per_page', 10);
        $transactions = $query->paginate($perPage);

        return TransactionResource::collection($transactions);
    }

    // Stub for provider callback; customize for gateway
    public function verify(Request $request): JsonResponse
    {
        // Implement provider-specific verification and update transaction status.
        return response()->json(['success' => true]);
    }
}
