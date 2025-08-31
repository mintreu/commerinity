<?php

namespace Mintreu\LaravelIntegration\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Mintreu\Toolkit\Contracts\Fingerprintable;

/**
 * Class ProviderOrder
 *
 * Represents an order request payload for a payment provider.
 */
class ProviderOrder
{
    protected ?string $currency = null;
    protected string|int|float|null $amount = null;
    protected ?Model $customer = null;
    protected ?string $receipt = null;
    protected array $notes = [];

    protected ?string $customerId = null;
    protected ?string $customerName = null;
    protected ?string $customerEmail = null;
    protected ?string $customerMobile = null;

    protected ?string $successUrl = null;
    protected ?string $failureUrl = null;
    protected ?Carbon $expireAt = null;

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
    // Builder methods (Fluent API)
    // ---------------------------

    public function receipt(string $receipt): static
    {
        $this->receipt = $receipt;
        return $this;
    }

    public function currency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function amount(float|int|string $amount): static
    {
        // Normalize numeric strings into int/float where possible
        if (is_numeric($amount)) {
            $amount = $amount + 0; // cast to int/float
        }
        $this->amount = $amount;
        return $this;
    }

    public function customer(?Model $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function customerId(string $customerId): static
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function customerName(string $customerName): static
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function customerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    public function customerMobile(string $customerMobile): static
    {
        $this->customerMobile = $customerMobile;
        return $this;
    }

    public function notes(array $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function successUrl(string $url): static
    {
        $this->successUrl = $url;
        return $this;
    }

    public function failureUrl(string $url): static
    {
        $this->failureUrl = $url;
        return $this;
    }

    public function expireAt(Carbon $expireAt): static
    {
        $this->expireAt = $expireAt;
        return $this;
    }

    public function expireAfter(int $minutes): static
    {
        $this->expireAt = now()->addMinutes($minutes);
        return $this;
    }

    public function expireAfterDays(int $days): static
    {
        $this->expireAt = now()->addDays($days);
        return $this;
    }

    // ---------------------------
    // Getters
    // ---------------------------

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getAmount(): float|int|string|null
    {
        return $this->amount;
    }

    public function getReceipt(): ?string
    {
        return $this->receipt;
    }

    public function getNotes(): array
    {
        return $this->notes;
    }

    public function getCustomer(): ?Model
    {
        return $this->customer;
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
        if (!empty($this->customerId)) {
            return $this->customerId;
        }

        if ($this->customer && $this->customer instanceof Fingerprintable) {
            return $this->customer->fingerprint();
        }

        return null;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName ?? $this->customer?->name;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail ?? $this->customer?->email;
    }

    public function getCustomerMobile(): ?string
    {
        return $this->customerMobile ?? $this->customer?->mobile;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->successUrl;
    }

    public function getFailureUrl(): ?string
    {
        return $this->failureUrl;
    }

    /**
     * Always return a Carbon instance.
     * Defaults to now() + 15 minutes if not set.
     */
    public function getExpireAt(): Carbon
    {
        return $this->expireAt instanceof Carbon
            ? $this->expireAt
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
