<?php

namespace App\Services\PaymentProviders;

use App\Models\Order;
use App\Models\Payment;

interface PaymentProviderInterface
{
    /**
     * Initialize payment for an order
     */
    public function initializePayment(Order $order): array;

    /**
     * Verify payment
     */
    public function verifyPayment(Payment $payment, array $data): bool;

    /**
     * Process refund
     */
    public function refund(Payment $payment, int $amount): bool;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Get the Payment model instance managed by the provider
     */
    public function getPayment(): ?Payment;
}
