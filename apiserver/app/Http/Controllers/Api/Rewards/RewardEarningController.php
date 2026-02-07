<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rewards;

use App\Casts\RewardStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Rewards\RewardEarningResource;
use App\Models\Rewards\RewardEarning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RewardEarningController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', 'string', 'in:issued,claimed,used,expired'],
            'reward_type' => ['sometimes', 'string', 'in:coins,voucher'],
            'used' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
        ]);

        $user = $request->user();

        $query = RewardEarning::query()
            ->where('user_id', $user->id)
            ->with('voucherCode')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('reward_type')) {
            $query->where('reward_type', $request->input('reward_type'));
        }

        if ($request->has('used')) {
            $query->where('is_used', (bool) $request->input('used'));
        }

        $rewards = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => RewardEarningResource::collection($rewards),
            'meta' => [
                'current_page' => $rewards->currentPage(),
                'last_page' => $rewards->lastPage(),
                'per_page' => $rewards->perPage(),
                'total' => $rewards->total(),
            ],
        ]);
    }

    public function markUsed(Request $request, RewardEarning $reward): JsonResponse
    {
        $user = $request->user();
        if ($reward->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found',
            ], 404);
        }

        if ($reward->status === RewardStatusCast::EXPIRED) {
            return response()->json([
                'success' => false,
                'message' => 'Reward is expired',
            ], 422);
        }

        if ($reward->is_used) {
            return response()->json([
                'success' => true,
                'data' => RewardEarningResource::make($reward),
            ]);
        }

        $reward->update([
            'is_used' => true,
            'status' => RewardStatusCast::USED,
        ]);

        return response()->json([
            'success' => true,
            'data' => RewardEarningResource::make($reward->fresh()),
        ]);
    }
}
