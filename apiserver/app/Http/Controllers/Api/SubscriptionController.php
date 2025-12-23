<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Services\MoneyService;
use App\Services\Wallet\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SubscriptionController - API for user subscription management
 *
 * Provides:
 * - View available membership plans (stages)
 * - View current subscription status
 * - Subscribe/upgrade to a plan
 * - View subscription history
 */
final class SubscriptionController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
    ) {}

    /**
     * Get available membership plans (stages).
     *
     * GET /api/subscription/plans
     */
    public function plans(): JsonResponse
    {
        $stages = Stage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Stage $stage) => [
                'uuid' => $stage->uuid,
                'name' => $stage->name,
                'slug' => $stage->slug,
                'description' => $stage->description,
                'price' => $stage->price,
                'price_formatted' => MoneyService::format($stage->price),
                'base_price' => $stage->base_price,
                'base_price_formatted' => MoneyService::format($stage->base_price),
                'discount' => $stage->discount,
                'discount_formatted' => MoneyService::format($stage->discount),
                'tax_amount' => $stage->tax_amount,
                'tax_amount_formatted' => MoneyService::format($stage->tax_amount),
                'pv' => $stage->pv,
                'benefits' => $stage->benefits ?? [],
                'max_team_members' => $stage->max_team_members,
                'is_default' => $stage->is_default,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => $stages,
            ],
        ]);
    }

    /**
     * Get current user subscription status.
     *
     * GET /api/subscription/status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscription = UserSubscription::getActiveForUser($user->id);

        if (! $subscription) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_subscription' => false,
                    'subscription' => null,
                    'can_subscribe' => true,
                ],
            ]);
        }

        $subscription->load(['stage', 'currentLevel', 'highestLevel']);

        return response()->json([
            'success' => true,
            'data' => [
                'has_subscription' => true,
                'subscription' => $this->formatSubscription($subscription),
                'can_subscribe' => false,
                'can_upgrade' => $this->canUpgrade($subscription),
            ],
        ]);
    }

    /**
     * Subscribe to a plan using wallet balance.
     *
     * POST /api/subscription/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'plan_uuid' => ['required', 'string', 'exists:stages,uuid'],
            'pin' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Check if user already has active subscription
        if (UserSubscription::hasActiveSubscription($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription. Please upgrade instead.',
            ], 400);
        }

        // Verify PIN
        if (! $wallet->hasPin()) {
            return response()->json([
                'success' => false,
                'message' => 'Please set up your wallet PIN first.',
                'requires_pin_setup' => true,
            ], 400);
        }

        if (! $wallet->verifyPin($request->input('pin'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid wallet PIN.',
            ], 400);
        }

        $stage = Stage::where('uuid', $request->input('plan_uuid'))
            ->where('is_active', true)
            ->first();

        if (! $stage) {
            return response()->json([
                'success' => false,
                'message' => 'Selected plan is not available.',
            ], 400);
        }

        // Check wallet balance
        if (! $wallet->hasSufficientBalance($stage->price)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
                'required' => MoneyService::format($stage->price),
                'available' => MoneyService::format($wallet->available_balance),
            ], 400);
        }

        try {
            // Debit wallet
            $transaction = $this->walletService->debit(
                $wallet,
                $stage->price,
                'subscription',
                "Subscription to {$stage->name} plan"
            );

            // Create subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'stage_id' => $stage->id,
                'base_price' => $stage->base_price,
                'discount' => $stage->discount,
                'tax_amount' => $stage->tax_amount,
                'amount' => $stage->price,
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'originator_type' => get_class($user),
                'originator_id' => $user->id,
            ]);

            // Activate subscription
            $subscription->activate($transaction->id);
            $subscription->load(['stage', 'currentLevel']);

            // Trigger commission calculation (queued)
            // CommissionCalculationJob::dispatch($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully!',
                'data' => [
                    'subscription' => $this->formatSubscription($subscription),
                    'transaction_reference' => $transaction->reference_number,
                    'new_balance_formatted' => MoneyService::format($wallet->fresh()->available_balance),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process subscription. Please try again.',
            ], 500);
        }
    }

    /**
     * Get subscription history.
     *
     * GET /api/subscription/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscriptions = UserSubscription::query()
            ->forUser($user->id)
            ->with(['stage', 'currentLevel', 'transaction'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $subscriptions->map(fn ($sub) => $this->formatSubscription($sub)),
            'meta' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    /**
     * Format subscription for API response.
     */
    private function formatSubscription(UserSubscription $subscription): array
    {
        return [
            'uuid' => $subscription->uuid,
            'stage' => $subscription->stage ? [
                'name' => $subscription->stage->name,
                'slug' => $subscription->stage->slug,
            ] : null,
            'current_level' => $subscription->currentLevel ? [
                'name' => $subscription->currentLevel->name,
                'level_number' => $subscription->currentLevel->level_number,
            ] : null,
            'highest_level' => $subscription->highestLevel ? [
                'name' => $subscription->highestLevel->name,
            ] : null,
            'amount' => $subscription->amount,
            'amount_formatted' => MoneyService::format($subscription->amount),
            'status' => $subscription->status,
            'is_active' => $subscription->isActive(),
            'starts_at' => $subscription->starts_at?->toIso8601String(),
            'expires_at' => $subscription->expires_at?->toIso8601String(),
            'days_remaining' => $subscription->expires_at?->diffInDays(now()),
            'total_commission_earned' => $subscription->total_commission_earned,
            'total_commission_formatted' => MoneyService::format($subscription->total_commission_earned),
            'personal_pv' => $subscription->personal_pv,
            'team_pv' => $subscription->team_pv,
            'renewal_count' => $subscription->renewal_count,
            'paid_at' => $subscription->paid_at?->toIso8601String(),
            'created_at' => $subscription->created_at->toIso8601String(),
        ];
    }

    /**
     * Check if user can upgrade subscription.
     */
    private function canUpgrade(UserSubscription $subscription): bool
    {
        if (! $subscription->isActive()) {
            return false;
        }

        $nextStage = Stage::where('sort_order', '>', $subscription->stage->sort_order)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        return $nextStage !== null;
    }
}
