<?php

namespace Mintreu\LaravelCommerinity\Services\CartService\Support;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait HasGuestCartSupport
{
    // === GUEST TOKEN ===

    public function ensureGuestCredential(): JsonResponse
    {
        // Prefer configured days for the outward-facing expires_at hint if provided
        $days = (int) config('laravel-commerinity.cart.guest.token_ttl_days', 15);
        $tokenTTL = now()->addDays($days);

        $existingGuest = $this->getGuestTokenFromCache($this->guestId);

        if ($this->validateGuest($existingGuest))
        {
            if (isset($existingGuest['token']) && hash_equals($existingGuest['token'],$this->token ?? ''))
            {
                return response()->json([
                    'data' => [
                        'status'      => 'exist',
                        'guest_id'    => $this->guestId,
                        'guest_token' => $this->token,
                        'expires_at'  => $existingGuest['expires_at'],
                    ]
                ]);
            }

        }


        if ($existingGuest && isset($existingGuest['guest_id']) && Cache::has($existingGuest['guest_id']))
        {
            Cache::forget($existingGuest['guest_id']);
        }

        $newGuestCredential = $this->newGuestCredential($this->guestId,$tokenTTL);

        return response()->json([
            'data' => [
                'status'      => 'fresh',
                'guest_id'    => $newGuestCredential['guest_id'],
                'guest_token' => $newGuestCredential['guest_token'],
                'expires_at'  => $newGuestCredential['expires_at'],
            ]
        ]);

    }


    public function newGuestCredential(?string $guestId, Carbon|\Carbon\CarbonInterface $tokenTTL): array
    {
        $newGuestId = !empty($guestId) && !is_null($guestId) ? $guestId : (string) Str::slug($this->request->ip().'-'.Str::uuid());
        $newToken   = bin2hex(random_bytes(32));
        $this->storeGuestToken($newGuestId, $newToken, $tokenTTL);

        return [
            'guest_id'    => $newGuestId,
            'guest_token' => $newToken,
            'expires_at'  => $tokenTTL->toISOString(),
        ];
    }

    protected function storeGuestToken(string $guestId, string $token, Carbon $expiresAt): void
    {
        $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');

        // Store token with metadata
        Cache::put($prefix . $guestId, [
            'guest_id' => $prefix . $guestId,
            'token' => $token,
            'expires_at' => $expiresAt->toISOString(),
            'ip' => $this->request->getClientIp(),
            'created_at' => now()->timestamp,
        ], $expiresAt);
    }


    protected function validateGuest(string|null|array|Cache $credential = null):bool
    {
        if (empty($credential))
        {
            return false;
        }
        if (is_null($credential))
        {
            return false;
        }

        if (!isset($credential['guest_id']))
        {
            return false;
        }

        if (!isset($credential['expires_at']))
        {
            return false;
        }

        $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');
        $guestId = $prefix . $this->guestId;
        if ($credential['guest_id'] !== $guestId) {
            return false;
        }


        if (!empty($credential['expires_at']) && now()->greaterThan(Carbon::parse($credential['expires_at']))) {
            return false;
        }


        return true;

    }
















    protected function getGuestTokenFromCache(?string $guestId = null)
    {
        $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');
        return Cache::get($prefix . $guestId) ?? $guestId;
    }

    public function validateGuestToken(?string $guestId = null, ?string $token = null): bool
    {
        $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');
        $cached = $this->getGuestTokenFromCache($guestId);

        // Not found in cache
        if (!$cached || !is_array($cached) || empty($cached['token'])) {
            return false;
        }

        // Token mismatch
        if (!hash_equals($cached['token'], $token)) {
            return false;
        }

        // Expiry check (safety even though Cache TTL also expires automatically)
        if (!empty($cached['expires_at']) && now()->greaterThan(Carbon::parse($cached['expires_at']))) {
            Cache::forget($prefix . $guestId);
            return false;
        }

        return true;
    }




    protected function mergeGuestCartToCustomer(): void
    {
        $items = $this->items();
        if ($items) {
            $items->each(function (Cart $cart) {
                $cart->update([
                    'ownerable_type' => get_class($this->customer),
                    'ownerable_id'   => $this->customer->id,
                ]);
            });
        }

        if ($this->guestId) {
            $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');
            Cache::forget($prefix . $this->guestId);
        }
    }

}
