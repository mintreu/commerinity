<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\BeneficiaryTypeCast;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeneficiaryAccountRequest;
use App\Http\Resources\BeneficiaryAccountResource;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BeneficiaryAccountController - Manage bank accounts for withdrawals
 *
 * Endpoints:
 * - GET /api/wallet/beneficiaries - List all beneficiaries
 * - POST /api/wallet/beneficiaries - Add new beneficiary
 * - GET /api/wallet/beneficiaries/{uuid} - Get single beneficiary
 * - DELETE /api/wallet/beneficiaries/{uuid} - Delete beneficiary
 * - POST /api/wallet/beneficiaries/{uuid}/default - Set as default
 * - POST /api/wallet/beneficiaries/verify-ifsc - Verify IFSC code
 */
final class BeneficiaryAccountController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
    ) {}

    /**
     * List all beneficiary accounts for the authenticated user's wallet.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $beneficiaries = $wallet->beneficiaryAccounts()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return BeneficiaryAccountResource::collection($beneficiaries);
    }

    /**
     * Add a new beneficiary account.
     */
    public function store(StoreBeneficiaryAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $type = BeneficiaryTypeCast::from($request->input('type'));
        $isBank = $type->isBank();

        // Create beneficiary with initial status
        $beneficiary = $wallet->beneficiaryAccounts()->create([
            'accountable_type' => $user->getMorphClass(),
            'accountable_id' => $user->id,
            'type' => $type,
            'account_number' => $isBank ? $request->input('account_number') : null,
            'ifsc_code' => $isBank ? strtoupper($request->input('ifsc_code')) : null,
            'bank_name' => $request->input('bank_name'),
            'branch_name' => $request->input('branch_name'),
            'upi_id' => ! $isBank ? strtolower($request->input('upi_id')) : null,
            'holder_name' => $request->input('holder_name'),
            'status' => BeneficiaryStatusCast::PENDING,
            'is_default' => false,
            'metadata' => [
                'added_via' => 'api',
                'ip_address' => $request->ip(),
            ],
        ]);

        // If this is the first beneficiary, make it default
        $existingCount = $wallet->beneficiaryAccounts()->count();
        if ($existingCount === 1) {
            $beneficiary->makeDefault();
        }

        Log::info('Beneficiary account added', [
            'user_id' => $user->id,
            'beneficiary_id' => $beneficiary->id,
            'type' => $type->value,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank account added successfully. Pending verification.',
            'data' => [
                'beneficiary' => new BeneficiaryAccountResource($beneficiary),
            ],
        ], 201);
    }

    /**
     * Get a single beneficiary account.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $beneficiary = $wallet->beneficiaryAccounts()
            ->where('uuid', $uuid)
            ->first();

        if (! $beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary account not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'beneficiary' => new BeneficiaryAccountResource($beneficiary),
            ],
        ]);
    }

    /**
     * Delete a beneficiary account.
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $beneficiary = $wallet->beneficiaryAccounts()
            ->where('uuid', $uuid)
            ->first();

        if (! $beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary account not found',
            ], 404);
        }

        // Check if there are pending withdrawals to this account
        $hasPendingWithdrawals = $wallet->transactions()
            ->whereIn('status', ['pending', 'processing'])
            ->where('purpose', 'withdrawal')
            ->whereJsonContains('metadata->beneficiary_id', $beneficiary->id)
            ->exists();

        if ($hasPendingWithdrawals) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with pending withdrawals',
            ], 400);
        }

        $wasDefault = $beneficiary->is_default;
        $beneficiary->delete();

        // If deleted account was default, make another one default
        if ($wasDefault) {
            $newDefault = $wallet->beneficiaryAccounts()->first();
            if ($newDefault) {
                $newDefault->makeDefault();
            }
        }

        Log::info('Beneficiary account deleted', [
            'user_id' => $user->id,
            'beneficiary_uuid' => $uuid,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank account removed successfully',
        ]);
    }

    /**
     * Set a beneficiary account as default.
     */
    public function setDefault(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $beneficiary = $wallet->beneficiaryAccounts()
            ->where('uuid', $uuid)
            ->first();

        if (! $beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary account not found',
            ], 404);
        }

        $beneficiary->makeDefault();

        return response()->json([
            'success' => true,
            'message' => 'Default account updated successfully',
            'data' => [
                'beneficiary' => new BeneficiaryAccountResource($beneficiary->fresh()),
            ],
        ]);
    }

    /**
     * Verify IFSC code and get bank details.
     *
     * Uses Razorpay's free IFSC API: https://ifsc.razorpay.com/{ifsc}
     */
    public function verifyIfsc(Request $request): JsonResponse
    {
        $request->validate([
            'ifsc_code' => ['required', 'string', 'size:11', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i'],
        ]);

        $ifscCode = strtoupper($request->input('ifsc_code'));

        try {
            $response = Http::timeout(10)
                ->get("https://ifsc.razorpay.com/{$ifscCode}");

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'ifsc' => $data['IFSC'] ?? $ifscCode,
                        'bank_name' => $data['BANK'] ?? null,
                        'branch_name' => $data['BRANCH'] ?? null,
                        'address' => $data['ADDRESS'] ?? null,
                        'city' => $data['CITY'] ?? null,
                        'state' => $data['STATE'] ?? null,
                        'micr' => $data['MICR'] ?? null,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid IFSC code',
            ], 400);
        } catch (\Exception $e) {
            Log::error('IFSC verification failed', [
                'ifsc' => $ifscCode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify IFSC code. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify a beneficiary account (Admin action, simulated here for testing).
     *
     * In production, this would be done by admin or automated verification.
     */
    public function verify(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $beneficiary = $wallet->beneficiaryAccounts()
            ->where('uuid', $uuid)
            ->first();

        if (! $beneficiary) {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary account not found',
            ], 404);
        }

        if ($beneficiary->status === BeneficiaryStatusCast::VERIFIED) {
            return response()->json([
                'success' => true,
                'message' => 'Account already verified',
                'data' => [
                    'beneficiary' => new BeneficiaryAccountResource($beneficiary),
                ],
            ]);
        }

        // In demo/development mode, auto-verify
        if (config('app.env') !== 'production') {
            $beneficiary->update([
                'status' => BeneficiaryStatusCast::VERIFIED,
                'verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account verified successfully (demo mode)',
                'data' => [
                    'beneficiary' => new BeneficiaryAccountResource($beneficiary->fresh()),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Verification pending. Please wait for admin approval.',
        ], 400);
    }

    /**
     * Get available account types.
     */
    public function getAccountTypes(): JsonResponse
    {
        $types = collect(BeneficiaryTypeCast::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->getLabel(),
            'is_bank' => $type->isBank(),
            'is_upi' => $type->isUpi(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'types' => $types,
            ],
        ]);
    }
}
