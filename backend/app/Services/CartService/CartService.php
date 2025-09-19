<?php

namespace App\Services\CartService;

use App\Models\Cart;
use App\Models\User;
use App\Services\CartService\Support\HasVoucherCodeValidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mintreu\LaravelCommerinity\Models\VoucherCode;
use Mintreu\LaravelProductCatalogue\Models\Product;

class CartService
{
    use HasVoucherCodeValidator;

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

    // Backward-compatible default; overridden by config when capture() runs
    protected int $tokenTTL = 3600 * 24 * 7; // seconds

    public function __construct(User|Model|null $user = null)
    {
        $this->customer = $user;
    }

    public function capture(Request $request): self
    {
        $this->request = $request;

        $headerId    = config('laravel-commerinity.cart.guest.header_id', 'x-guest-id');
        $headerToken = config('laravel-commerinity.cart.guest.header_token', 'x-guest-token');

        $this->guestId = $request->header($headerId);
        $this->token   = $request->header($headerToken);

        // Read token TTL from config (seconds), keep default if missing
        $this->tokenTTL = (int) config('laravel-commerinity.cart.guest.token_ttl_seconds', $this->tokenTTL);

        if ($this->guestId && $this->token) {
            $this->hasGuest   = true;
            $this->validToken = $this->validateGuestToken($this->guestId, $this->token);
        }

        if (!is_null($this->customer) && $this->hasGuest && $this->validToken) {
            $this->mergeGuestCartToCustomer();
        }

        return $this;
    }

    public function ensureGuestCredential(): JsonResponse
    {
        // Prefer configured days for the outward-facing expires_at hint if provided
        $days = (int) config('laravel-commerinity.cart.guest.token_ttl_days', 15);
        $tokenTTL = now()->addDays($days);

        if ($this->guestId && $this->token && $this->validToken) {
            return response()->json([
                'guest_id'    => $this->guestId,
                'guest_token' => $this->token,
                'expires_at'  => $tokenTTL->toISOString(),
            ]);
        }

        $newGuestId = (string) Str::uuid();
        $newToken   = bin2hex(random_bytes(32));
        $this->storeGuestToken($newGuestId, $newToken, $tokenTTL);

        return response()->json([
            'data' => [
                'guest_id'    => $newGuestId,
                'guest_token' => $newToken,
                'expires_at'  => $tokenTTL->toISOString(),
            ],
        ]);
    }

    protected function validateGuestToken(string $guestId, string $token): bool
    {
        $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');
        $storedToken = Cache::get($prefix . $guestId);
        return hash_equals($storedToken ?? '', $token);
    }

    protected function storeGuestToken(string $guestId, string $token, Carbon $expiresAt): void
    {
        $prefix = config('laravel-commerinity.cart.guest.token_cache_prefix', 'guest_cart_token_');
        // Use DateTimeInterface for TTL clarity across drivers
        Cache::put($prefix . $guestId, $token, $expiresAt);
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
        return null;
    }

    public function add(Model|Product $item, int $quantity): void
    {
        $defaultMax = (int) config('laravel-commerinity.cart.limits.max_per_order_default', 1);
        $maxPerOrder = $item->max_quantity ?? $defaultMax;
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
                'cartable_id'    => $item->id,
                'cartable_type'  => get_class($item),
                'quantity'       => $approvedQuantity,
                'ownerable_type' => $this->customer ? get_class($this->customer) : null,
                'ownerable_id'   => $this->customer?->id,
                'is_guest'       => !$this->customer,
                'guest_id'       => $this->guestId,
                'guest_token'    => $this->token,
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

        // Find product safely; fallback max from config
        $product = Product::find($item->cartable_id ?? $item->id);
        $defaultMax = (int) config('laravel-commerinity.cart.limits.max_per_order_default', 1);
        $maxPerOrder = $product->max_quantity ?? $defaultMax;

        $cart->update([
            'quantity' => min($quantity, $maxPerOrder),
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
        $items = $this->items();
        $this->totalQuantity = $items ? $items->sum('quantity') : 0;
        return $this->totalQuantity;
    }

    public function isEmpty(): bool
    {
        $items = $this->items();
        return !$items || $items->count() === 0;
    }

    public function setError(?string $msg): void
    {
        $this->error = $msg; // null means “no error”
    }

    public function getErrors(): ?string
    {
        return $this->error;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }

    public function setCouponCode(string|VoucherCode $voucherCode): void
    {
        if (is_string($voucherCode)) {
            $voucherCode = VoucherCode::firstWhere('code', $voucherCode);
            if (!$voucherCode) {
                $this->setError('Invalid coupon code.');
                $this->validCoupon = false;
                $this->couponCode = null;
                $this->forgetCouponInSession();
                return;
            }
        }

        if ($this->validateCouponCode($voucherCode, $this->customer ?? null)) {
            $this->validCoupon = true;
            $this->couponCode = $voucherCode->code;
            $this->putCouponInSession($this->couponCode);
            return;
        }

        $this->validCoupon = false;
        $this->couponCode = null;
        $this->forgetCouponInSession();
    }

    public function getCouponCode(): ?string
    {
        if (!empty($this->couponCode)) {
            return $this->couponCode;
        }

        $code = $this->getCouponFromSession();
        if (is_string($code) && $code !== '') {
            $this->couponCode = $code;
            $this->validCoupon = true;
            return $this->couponCode;
        }

        return null;
    }

    public function howToUse(): string
    {
        return <<<INSTRUCTIONS
        === Guest Cart Usage Instructions ===
        1. On first cart interaction, call POST /cart/guest-credential to get guest_id and guest_token.
        2. Save them in cookies or localStorage and send in headers:
           - configured header_id
           - configured header_token
        3. On login, call /cart/merge to merge guest cart into user cart.
        4. All cart APIs work the same for guest or logged-in users.
        ========================================
        INSTRUCTIONS;
    }
}
