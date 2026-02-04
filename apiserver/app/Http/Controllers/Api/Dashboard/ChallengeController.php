<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\ChallengeResource;
use App\Models\Dashboard\Challenge;
use Illuminate\Http\Request;

final class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $query = Challenge::with(['targetable', 'targetLevel', 'targetStage']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('target_user_type')) {
            $query->where('target_user_type', $request->input('target_user_type'));
        }

        $perPage = (int) $request->input('per_page', 20);

        return ChallengeResource::collection($query->orderByDesc('created_at')->paginate($perPage));
    }

    public function active(Request $request)
    {
        $now = now();

        $query = Challenge::with(['targetable', 'targetLevel', 'targetStage'])
            ->where('status', 'active')
            ->where(function ($builder) use ($now) {
                $builder->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($builder) use ($now) {
                $builder->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });

        return ChallengeResource::collection($query->get());
    }

    public function show(Challenge $challenge)
    {
        return ChallengeResource::make($challenge->load(['targetable', 'targetLevel', 'targetStage']));
    }
}
