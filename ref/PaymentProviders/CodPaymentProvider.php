<?php

namespace App\Services\PaymentProviders;

use App\Models\Order;
use App\Models\Payment;

class CodPaymentProvider implements PaymentProviderInterface
{
    protected ?Payment $payment = null;

    /**
     * Initialize payment for COD order
     */
    public function initializePayment(Order $order): array
    {
        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'payment_provider' => 'cod',
            'status' => 'pending',
            'amount' => $order->total,
            'transaction_id' => 'COD-'.$order->order_number,
        ]);

        $this->payment = $payment; // Set the payment instance

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'requires_action' => false,
            'message' => 'Cash on Delivery payment initialized',
        ];
    }

    /**
     * Get the Payment model instance managed by the provider
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * Verify COD payment (manual verification by admin)
     */
    public function verifyPayment(Payment $payment, array $data): bool
    {
        // COD payments are verified manually when delivery is completed
        return true;
    }

    /**
     * Process COD refund (manual process)
     */
    public function refund(Payment $payment, int $amount): bool
    {
        // COD refunds are processed manually
        return true;
    }

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return 'cod';
    }
}
