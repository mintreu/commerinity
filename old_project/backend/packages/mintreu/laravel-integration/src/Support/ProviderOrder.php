<?php

namespace Mintreu\LaravelIntegration\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mintreu\Toolkit\Contracts\Fingerprintable;

/**
 * Class ProviderOrder
 *
 * Represents an order request payload for a payment provider.
 */
class ProviderOrder
{
    protected string|Closure|null $currency = null;
    protected string|int|float|Closure|null $amount = null;
    protected Model|Closure|null $customer = null;
    protected string|Closure|null $receipt = null;
    protected array|Closure $notes = [];

    protected string|Closure|null $customerId = null;
    protected string|Closure|null $customerName = null;
    protected string|Closure|null $customerEmail = null;
    protected string|Closure|null $customerMobile = null;

    protected string|Closure|null $successUrl = null;
    protected string|Closure|null $failureUrl = null;
    protected Carbon|Closure|null $expireAt = null;

    /**
     * Optionally initialize ProviderOrder with data.
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    // ---------------------------
    // Helpers
    // ---------------------------

    protected function resolve(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }

    // ---------------------------
    // Builder methods (Fluent API)
    // ---------------------------

    public function receipt(string|Closure $receipt): static
    {
        $this->receipt = $receipt;
        return $this;
    }

    public function currency(string|Closure $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function amount(float|int|string|Closure $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function customer(Model|Closure|null $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function customerId(string|Closure $customerId): static
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function customerName(string|Closure $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function customerEmail(string|Closure $customerEmail): static
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    public function customerMobile(string|Closure $customerMobile): static
    {
        $this->customerMobile = $customerMobile;
        return $this;
    }

    public function notes(array|Closure $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function successUrl(string|Closure $url): static
    {
        $this->successUrl = $url;
        return $this;
    }

    public function failureUrl(string|Closure $url): static
    {
        $this->failureUrl = $url;
        return $this;
    }

    public function expireAt(Carbon|Closure $expireAt): static
    {
        $this->expireAt = $expireAt;
        return $this;
    }

    public function expireAfter(int $minutes): static
    {
        $this->expireAt = fn() => now()->addMinutes($minutes);
        return $this;
    }

    public function expireAfterDays(int $days): static
    {
        $this->expireAt = fn() => now()->addDays($days);
        return $this;
    }

    // ---------------------------
    // Getters
    // ---------------------------

    public function getCurrency(): ?string
    {
        return $this->resolve($this->currency);
    }

//    public function getAmount(): float|int|string|null
//    {
//        $value = $this->resolve($this->amount);
//        if (is_numeric($value)) {
//            return $value + 0; // normalize
//        }
//        return $value;
//    }


    public function getAmount(): int
    {
        $value = $this->resolve($this->amount);

        // Handle null/empty
        if ($value === null || $value === '') {
            return 0;
        }

        // If already integer, return as-is (assume already in smallest unit)
        if (is_int($value)) {
            return $value;
        }

        // Convert string to float first (handles "100.50", "100", etc.)
        if (is_string($value)) {
            // Remove any non-numeric characters except decimal point
            $value = preg_replace('/[^0-9.]/', '', $value);
            $value = (float) $value;
        }

        // If float, convert to integer (multiply by 100 for cents)
        if (is_float($value)) {
            // Round to 2 decimal places first to avoid floating point issues
            // Then multiply by 100 and cast to int
            return (int) round($value * 100);
        }

        // Fallback: try to cast to int
        return (int) $value;
    }


    public function getReceipt(): ?string
    {
        return $this->resolve($this->receipt);
    }

    public function getNotes(): array
    {
        return (array) $this->resolve($this->notes);
    }

    public function getCustomer(): ?Model
    {
        $value = $this->resolve($this->customer);
        return $value instanceof Model ? $value : null;
    }

    /**
     * Get a unique customer identifier (stable & deterministic).
     *
     * Priority:
     * 1. Explicit $customerId property (manual override)
     * 2. Customer model fingerprint (if model implements Fingerprintable)
     */
    public function getCustomerId(): ?string
    {
        $value = $this->resolve($this->customerId);

        if (!empty($value)) {
            return $value;
        }

        $customer = $this->getCustomer();
        if ($customer && $customer instanceof Fingerprintable) {
            return $customer->fingerprint();
        }

        return Str::ulid();
    }

    public function getCustomerName(): ?string
    {
        return $this->resolve($this->customerName) ?? $this->getCustomer()?->name;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->resolve($this->customerEmail) ?? $this->getCustomer()?->email;
    }

    public function getCustomerMobile(): ?string
    {
        return $this->resolve($this->customerMobile) ?? $this->getCustomer()?->mobile;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->resolve($this->successUrl);
    }

    public function getFailureUrl(): ?string
    {
        return $this->resolve($this->failureUrl);
    }

    /**
     * Always return a Carbon instance.
     * Defaults to now() + 20 minutes if not set.
     */
    public function getExpireAt(): Carbon
    {
        $value = $this->resolve($this->expireAt);

        return $value instanceof Carbon
            ? $value
            : now()->addMinutes(20);
    }

    // ---------------------------
    // Export helpers
    // ---------------------------

    /**
     * Convert the ProviderOrder into array (for APIs).
     */
    public function toArray(): array
    {
        return [
            'currency'        => $this->getCurrency(),
            'amount'          => $this->getAmount(),
            'receipt'         => $this->getReceipt(),
            'notes'           => $this->getNotes(),
            'customer_id'     => $this->getCustomerId(),
            'customer_name'   => $this->getCustomerName(),
            'customer_email'  => $this->getCustomerEmail(),
            'customer_mobile' => $this->getCustomerMobile(),
            'success_url'     => $this->getSuccessUrl(),
            'failure_url'     => $this->getFailureUrl(),
            'expire_at'       => $this->getExpireAt()->toIso8601String(),
        ];
    }

    /**
     * Convert to object (stdClass).
     */
    public function toObject(): object
    {
        return json_decode(json_encode($this->toArray()), false, 512, JSON_THROW_ON_ERROR);
    }
}
