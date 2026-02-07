<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Affiliate;

use App\Http\Controllers\Controller;
use App\Http\Resources\Affiliate\AffiliateVolumeLedgerResource;
use App\Models\Affiliate\AffiliateVolumeLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AffiliateLedgerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,confirmed,reversed,expired'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
        ]);

        $user = $request->user();

        $query = AffiliateVolumeLedger::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $ledgers = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => AffiliateVolumeLedgerResource::collection($ledgers),
            'meta' => [
                'current_page' => $ledgers->currentPage(),
                'last_page' => $ledgers->lastPage(),
                'per_page' => $ledgers->perPage(),
                'total' => $ledgers->total(),
            ],
        ]);
    }
}
