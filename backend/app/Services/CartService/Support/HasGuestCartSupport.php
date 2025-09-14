<?php

namespace App\Services\CartService\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait HasGuestCartSupport
{
    // === GUEST TOKEN ===

    private function ensureTokenValidation(bool $force = false): void
    {
        if ($this->customer) {
            $this->token = 'authenticated';
            $this->validToken = true;
            $this->hasGuest = false;
            return;
        }

        if ($force) {
            $this->createGuestToken();
            return;
        }

        $incomingToken = $this->request->header('x-guest-token');

        if ($incomingToken) {
            $this->validateGuestToken($incomingToken);
        } else {
            $this->createGuestToken();
        }
    }

    private function validateGuestToken(string $encoded): void
    {
        $clientIp = $this->request->ip();
        $agent = $this->request->userAgent() ?? 'unknown-agent';
        $secret = config('app.key');

        $decoded = base64_decode($encoded, true);
        if (!$decoded) {
            $this->error = 'Malformed guest token.';
            return;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 4) {
            $this->error = 'Invalid guest token format.';
            return;
        }

        [$ip, $ulid, $timestamp, $hmac] = $parts;

        if (!ctype_digit($timestamp) || now()->timestamp - (int)$timestamp > $this->tokenTTL) {
            $this->error = 'Guest token expired.';
            return;
        }

        if ($ip !== $clientIp) {
            $this->error = 'IP mismatch on guest token.';
            return;
        }

        $raw = "$ulid|$agent|$ip|$timestamp";
        $expectedHmac = hash_hmac('sha256', $raw, $secret);

        if (!hash_equals($expectedHmac, $hmac)) {
            $this->error = 'Invalid guest token signature.';
            return;
        }

        $this->token = $encoded;
        $this->validToken = true;
        $this->resetToken = false;
        $this->hasGuest = true;
    }



    private function createGuestToken(): void
    {
        $clientIp = $this->request->ip();
        $agent = $this->request->userAgent() ?? 'unknown-agent';
        $secret = config('app.key');

        $ulid = Str::ulid()->toString();
        $timestamp = now()->timestamp;
        $raw = "$ulid|$agent|$clientIp|$timestamp";
        $hmac = hash_hmac('sha256', $raw, $secret);

        $this->token = base64_encode("$clientIp|$ulid|$timestamp|$hmac");
        $this->validToken = true;
        $this->resetToken = true;
        $this->hasGuest = true;
        $this->error = null;
    }







    // === CACHE OPERATIONS ===

    protected function getGuestCartCacheKey(): ?string
    {
        return $this->validToken && $this->token ? 'guest_cart:' . $this->token : null;
    }

    protected function getGuestCart(): array
    {
        $key = $this->getGuestCartCacheKey();
        return $key ? Cache::get($key, []) : [];
    }

    protected function putGuestCart(array $items): void
    {
        $key = $this->getGuestCartCacheKey();
        if ($key) {
            Cache::put($key, $items, now()->addSeconds($this->tokenTTL));
        }
    }

    // === GUEST CART ACTIONS ===

    protected function addToGuestCart(Model $item, int $quantity, int $maxPerOrder = 1): void
    {
        if (!$this->validToken) return;

        $cart = collect($this->getGuestCart());

        $existing = $cart->first(fn($i) =>
            $i['cartable_id'] === $item->id &&
            $i['cartable_type'] === get_class($item)
        );

        $newQuantity = $existing
            ? min($existing['quantity'] + $quantity, $maxPerOrder)
            : min($quantity, $maxPerOrder);

        $updatedCart = $cart
            ->reject(fn($i) =>
                $i['cartable_id'] === $item->id &&
                $i['cartable_type'] === get_class($item)
            )
            ->push([
                'cartable_id' => $item->id,
                'cartable_type' => get_class($item),
                'quantity' => $newQuantity,
            ])
            ->values()
            ->all();

        $this->putGuestCart($updatedCart);
    }

    protected function updateGuestCart(Model $item, int $quantity, int $maxPerOrder = 1): void
    {
        if (!$this->validToken) return;

        $cart = collect($this->getGuestCart());

        $updatedCart = $cart
            ->map(function ($i) use ($item, $quantity, $maxPerOrder) {
                if (
                    $i['cartable_id'] === $item->id &&
                    $i['cartable_type'] === get_class($item)
                ) {
                    return [
                        'cartable_id' => $item->id,
                        'cartable_type' => get_class($item),
                        'quantity' => min($quantity, $maxPerOrder),
                    ];
                }
                return $i;
            })
            ->values()
            ->all();

        $this->putGuestCart($updatedCart);
    }

    protected function removeFromGuestCart(Model $item): void
    {
        if (!$this->validToken) return;

        $cart = collect($this->getGuestCart())
            ->reject(fn($i) =>
                $i['cartable_id'] === $item->id &&
                $i['cartable_type'] === get_class($item)
            )
            ->values()
            ->all();

        $this->putGuestCart($cart);
    }

    protected function clearGuestCart(): void
    {
        $key = $this->getGuestCartCacheKey();
        if ($key) {
            Cache::forget($key);
        }
    }

    public function getGuestCartItems(): \Illuminate\Support\Collection
    {
        if (!$this->hasGuest) return collect();

        return collect($this->getGuestCart())->map(function ($item) {
            $modelClass = $item['cartable_type'] ?? null;
            if (!class_exists($modelClass)) return null;

            $cartable = app($modelClass)->find($item['cartable_id']);
            if (!$cartable) return null;

            $object = new \stdClass();
            $object->cartable_type = $item['cartable_type'];
            $object->cartable_id   = $item['cartable_id'];
            $object->quantity      = $item['quantity'];
            $object->cartable      = $cartable;

            return $object;
        })->filter()->values();
    }


    public function mergeGuestCartToUserCart(): void
    {
        if (!$this->customer || !$this->hasGuest || !$this->validToken) return;

        $guestItems = $this->getGuestCart();
        if (empty($guestItems)) return;

        $existingItems = $this->customer->cart()->get();

        foreach ($guestItems as $item) {
            $modelClass = $item['cartable_type'] ?? null;
            if (!class_exists($modelClass)) continue;

            $cartable = app($modelClass)->find($item['cartable_id']);
            if (!$cartable) continue;

            $quantity = (int) $item['quantity'];
            $maxPerOrder = $cartable->max_per_order ?? 1;

            $existing = $existingItems->first(function ($cartItem) use ($cartable) {
                return $cartItem->cartable_id === $cartable->id &&
                    $cartItem->cartable_type === get_class($cartable);
            });

            if ($existing) {
                $existing->update([
                    'quantity' => min($existing->quantity + $quantity, $maxPerOrder)
                ]);
            } else {
                $this->customer->cart()->create([
                    'cartable_id' => $cartable->id,
                    'cartable_type' => get_class($cartable),
                    'quantity' => min($quantity, $maxPerOrder),
                ]);
            }
        }

        $this->clearGuestCart();
    }
}
