<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\WalletResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mintreu\LaravelTransaction\Models\Wallet;
use Mintreu\LaravelTransaction\Casts\WalletStatusCast;

class WalletController extends Controller
{
    /**
     * Get the wallet for the authenticated user along with transactions.
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        $wallet = $user->wallet()->with('transactions')->first();

        if (! $wallet) {
            return response()->json([
                'wallet' => null,
                'transactions' => [],
            ]);
        }

//        return response()->json([
//            'wallet' => $wallet,
//            'transactions' => $wallet->transactions()->latest()->get(),
//        ]);

        return WalletResource::make($wallet);

    }

    /**
     * Create a wallet for the authenticated user.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->wallet) {
            return response()->json([
                'message' => 'Wallet already exists.',
                'wallet' => $user->wallet,
            ], 200);
        }

        $wallet = $user->wallet()->create([
            'uuid' => (string) Str::uuid(),
            'pin' => $request->input('pin', '0000'), // auto-hashed in Wallet model
            'balance' => 0.00,
            'currency' => 'INR',
            'status' => WalletStatusCast::ACTIVE,
        ]);

        return WalletResource::make($wallet);
    }
}
