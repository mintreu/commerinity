<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService\Support;

use App\Models\Ecommerce\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait HasGuestCartSupport
{
    /**
     * Ensure guest has valid credentials, generate new if needed
     */
    public function ensureGuestCredential(): JsonResponse
    {
        $days = (int) config('cart.guest.token_ttl_days', 15);
        $tokenTTL = now()->addDays($days);

        $existingGuest = $this->getGuestTokenFromCache($this->guestId);

        if ($this->validateGuest($existingGuest)) {
            if (isset($existingGuest['token']) && hash_equals($existingGuest['token'], $this->token ?? '')) {
                return response()->json([
                    'data' => [
                        'status' => 'exist',
                        'guest_id' => $this->guestId,
                        'guest_token' => $this->token,
                        'expires_at' => $existingGuest['expires_at'],
                    ],
                ]);
            }
        }

        if ($existingGuest && isset($existingGuest['guest_id']) && Cache::has($existingGuest['guest_id'])) {
            Cache::forget($existingGuest['guest_id']);
        }

        $newGuestCredential = $this->newGuestCredential($this->guestId, $tokenTTL);

        return response()->json([
            'data' => [
                'status' => 'fresh',
                'guest_id' => $newGuestCredential['guest_id'],
                'guest_token' => $newGuestCredential['guest_token'],
                'expires_at' => $newGuestCredential['expires_at'],
            ],
        ]);
    }

    /**
     * Generate new guest credentials
     */
    public function newGuestCredential(?string $guestId, Carbon $tokenTTL): array
    {
        $newGuestId = ! empty($guestId) && ! is_null($guestId)
            ? $guestId
            : (string) Str::slug($this->request->ip().'-'.Str::uuid());
        $newToken = bin2hex(random_bytes(32));

        $this->storeGuestToken($newGuestId, $newToken, $tokenTTL);

        return [
            'guest_id' => $newGuestId,
            'guest_token' => $newToken,
            'expires_at' => $tokenTTL->toIso8601String(),
        ];
    }

    /**
     * Store guest token in cache
     */
    protected function storeGuestToken(string $guestId, string $token, Carbon $expiresAt): void
    {
        $prefix = config('cart.guest.token_cache_prefix', 'guest_cart_token_');

        Cache::put($prefix.$guestId, [
            'guest_id' => $prefix.$guestId,
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'ip' => $this->request?->ip(),
            'created_at' => now()->timestamp,
        ], $expiresAt);
    }

    /**
     * Validate guest credentials
     */
    protected function validateGuest(string|null|array $credential = null): bool
    {
        if (empty($credential) || is_null($credential)) {
            return false;
        }

        if (! isset($credential['guest_id']) || ! isset($credential['expires_at'])) {
            return false;
        }

        $prefix = config('cart.guest.token_cache_prefix', 'guest_cart_token_');
        $guestId = $prefix.$this->guestId;

        if ($credential['guest_id'] !== $guestId) {
            return false;
        }

        if (! empty($credential['expires_at']) && now()->greaterThan(Carbon::parse($credential['expires_at']))) {
            return false;
        }

        return true;
    }

    /**
     * Get guest token from cache
     */
    protected function getGuestTokenFromCache(?string $guestId = null): mixed
    {
        $prefix = config('cart.guest.token_cache_prefix', 'guest_cart_token_');

        return Cache::get($prefix.$guestId) ?? $guestId;
    }

    /**
     * Validate guest token
     */
    public function validateGuestToken(?string $guestId = null, ?string $token = null): bool
    {
        $prefix = config('cart.guest.token_cache_prefix', 'guest_cart_token_');
        $cached = $this->getGuestTokenFromCache($guestId);

        if (! $cached || ! is_array($cached) || empty($cached['token'])) {
            return false;
        }

        if (! hash_equals($cached['token'], $token ?? '')) {
            return false;
        }

        if (! empty($cached['expires_at']) && now()->greaterThan(Carbon::parse($cached['expires_at']))) {
            Cache::forget($prefix.$guestId);

            return false;
        }

        return true;
    }

    /**
     * Merge guest cart to authenticated customer
     */
    protected function mergeGuestCartToCustomer(): void
    {
        $items = Cart::where('is_guest', true)
            ->where('guest_id', $this->guestId)
            ->where('guest_token', $this->token)
            ->get();

        foreach ($items as $cart) {
            $cart->update([
                'ownerable_type' => get_class($this->customer),
                'ownerable_id' => $this->customer->id,
                'is_guest' => false,
                'guest_id' => null,
                'guest_token' => null,
            ]);
        }

        if ($this->guestId) {
            $prefix = config('cart.guest.token_cache_prefix', 'guest_cart_token_');
            Cache::forget($prefix.$this->guestId);
        }
    }
}
