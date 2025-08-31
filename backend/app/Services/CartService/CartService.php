<?php

namespace App\Services\CartService;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mintreu\LaravelProductCatalogue\Models\Product;

class CartService
{
    protected bool $changed = false;
    protected User|Model|null $customer;
    protected Request $request;
    protected ?string $error = null;
    protected array $meta = [];
    protected int $totalQuantity = 0;
    protected ?string $couponCode = null;
    protected bool $validCoupon = false;

    protected bool $hasGuest = false;
    protected ?string $guestId = null;
    protected ?string $token = null;
    protected bool $validToken = false;
    protected int $tokenTTL = 3600 * 24 * 7; // 7 days

    public function __construct(User|Model|null $user = null)
    {
        $this->customer = $user;
    }

    public function capture(Request $request): self
    {
        $this->request = $request;

        $this->guestId = $request->header('x-guest-id');
        $this->token = $request->header('x-guest-token');

        if ($this->guestId && $this->token) {
            $this->hasGuest = true;
            $this->validToken = $this->validateGuestToken($this->guestId, $this->token);
        }

        if (!is_null($this->customer) && $this->hasGuest && $this->validToken)
        {
            $this->mergeGuestCartToCustomer();
        }

        return $this;
    }

    public function ensureGuestCredential(): JsonResponse
    {
        $tokenTTL = now()->addDays(15);

        if ($this->guestId && $this->token && $this->validToken) {
            return response()->json([
                'guest_id' => $this->guestId,
                'guest_token' => $this->token,
                'expires_at' => $tokenTTL->toISOString()
            ]);
        }

        $newGuestId = (string) Str::uuid();
        $newToken = bin2hex(random_bytes(32));
        $this->storeGuestToken($newGuestId, $newToken, $tokenTTL);

        return response()->json([
            'data' => [
                'guest_id' => $newGuestId,
                'guest_token' => $newToken,
                'expires_at' => $tokenTTL->toISOString()
            ]
        ]);
    }

    protected function validateGuestToken(string $guestId, string $token): bool
    {
        $storedToken = Cache::get("guest_cart_token_{$guestId}");
        return hash_equals($storedToken ?? '', $token);
    }

    protected function storeGuestToken(string $guestId, string $token, Carbon $expiresAt): void
    {
        Cache::put("guest_cart_token_{$guestId}", $token, $expiresAt);
    }


    protected function mergeGuestCartToCustomer()
    {
        $this->items()->each(function (Cart $cart){
           $cart->update([
            'ownerable_type' => get_class($this->customer),
             'ownerable_id' => $this->customer->id
           ]);
        });
        if ($this->guestId)
        {
            Cache::forget("guest_cart_token_{$this->guestId}");
        }

    }




    public function items()
    {
        if ($this->customer) {
            return $this->customer->cart;
        }

        if ($this->hasGuest && $this->validToken) {
            return Cart::where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token)
                ->get();
        }
        $this->setError('cart credential not validated!');
        return null; // fallback null when nothing found
    }

    public function add(Model|Product $item, int $quantity): void
    {
        $maxPerOrder = $item->max_quantity ?? 1;
        $approvedQuantity = min($quantity, $maxPerOrder);


        $query = Cart::query()
            ->where('cartable_type', get_class($item))
            ->where('cartable_id', $item->id);

        if ($this->customer) {
            $query->where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id);
        } elseif ($this->hasGuest && $this->validToken) {
            $query->where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token);
        } else {
            $this->setError('cart credential not validated!');
        }

        $existing = $query->first();

        if ($existing) {
            $newQty = min($existing->quantity + $quantity, $maxPerOrder);
            $existing->update(['quantity' => $newQty]);
        } else {
            Cart::create([
                'cartable_id'   => $item->id,
                'cartable_type' => get_class($item),
                'quantity'      => $approvedQuantity,
                'ownerable_type'=> $this->customer ? get_class($this->customer) : null,
                'ownerable_id'  => $this->customer?->id,
                'is_guest'      => !$this->customer,
                'guest_id'      => $this->guestId,
                'guest_token'   => $this->token,
            ]);
        }
    }

    public function update(Model|Product $item, int $quantity): void
    {
        $query = Cart::query()
            ->where('cartable_type', get_class($item))
            ->where('cartable_id', $item->id);

        if ($this->customer) {
            $query->where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id);
        } elseif ($this->hasGuest && $this->validToken) {
            $query->where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token);
        } else {
            $this->setError('cart credential not validated!');
        }

        $cart = $query->first();
        if (!$cart) return;

        $product = Product::find($item->cartable_id ?? $item->id);
        $maxPerOrder = $product->max_quantity ?? 1;


        $cart->update([
            'quantity' => min($quantity, $maxPerOrder)
        ]);
    }

    public function delete(Model $item): void
    {
        $query = Cart::query()
            ->where('cartable_type', get_class($item))
            ->where('cartable_id', $item->id);

        if ($this->customer) {
            $query->where('ownerable_type', get_class($this->customer))
                ->where('ownerable_id', $this->customer->id);
        } elseif ($this->hasGuest && $this->validToken) {
            $query->where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token);
        } else {
            $this->setError('cart credential not validated!');
        }

        $query->delete();
    }

    public function empty(): void
    {
        if ($this->customer) {
            $this->customer->cart()->delete();
        } elseif ($this->hasGuest && $this->validToken) {
            Cart::where('is_guest', true)
                ->where('guest_id', $this->guestId)
                ->where('guest_token', $this->token)
                ->delete();
        }
    }

    public function getTotalQuantity(): int
    {
        $this->totalQuantity = $this->items()->sum('quantity');
        return $this->totalQuantity;
    }

    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    public function setError(string $msg): void
    {
        $this->error = $msg;
    }

    public function getErrors(): ?string
    {
        return $this->error;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function howToUse(): string
    {
        return <<<INSTRUCTIONS
        === Guest Cart Usage Instructions ===
        1. On first cart interaction, call POST /cart/guest-credential
           to get guest_id and guest_token.

        2. Save them in cookies or localStorage and send in headers:
           - x-guest-id
           - x-guest-token

        3. On login, call /cart/merge to merge guest cart into user cart.

        4. All cart APIs work the same for guest or logged-in users.
        ========================================
        INSTRUCTIONS;
    }
}
