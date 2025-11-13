<?php

namespace App\Services\PaymentProviders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayPaymentProvider implements PaymentProviderInterface
{
    protected ?Payment $payment = null;

    private string $keyId;
    private string $keySecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key_id', '');
        $this->keySecret = config('services.razorpay.key_secret', '');
    }

    /**
     * Initialize payment for Razorpay order
     */
    public function initializePayment(Order $order): array
    {
        try {
            // Create Razorpay order
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl.'/orders', [
                    'amount' => $order->total, // Amount in paise
                    'currency' => 'INR',
                    'receipt' => $order->order_number,
                    'notes' => [
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay order creation failed', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to initialize payment',
                ];
            }

            $razorpayOrder = $response->json();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'online',
                'payment_provider' => 'razorpay',
                'status' => 'pending',
                'amount' => $order->total,
                'razorpay_order_id' => $razorpayOrder['id'],
                'metadata' => [
                    'razorpay_order' => $razorpayOrder,
                ],
            ]);

            $this->payment = $payment; // Set the payment instance

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'requires_action' => true,
                'razorpay_order_id' => $razorpayOrder['id'],
                'razorpay_key_id' => $this->keyId,
                'amount' => $order->total,
                'currency' => 'INR',
                'order_number' => $order->order_number,
                'customer' => [
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => $order->customer->phone,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay payment initialization failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment initialization failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get the Payment model instance managed by the provider
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * Verify Razorpay payment signature
     */
    public function verifyPayment(Payment $payment, array $data): bool
    {
        try {
            $razorpayOrderId = $data['razorpay_order_id'] ?? null;
            $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
            $razorpaySignature = $data['razorpay_signature'] ?? null;

            if (! $razorpayOrderId || ! $razorpayPaymentId || ! $razorpaySignature) {
                return false;
            }

            // Verify signature
            $expectedSignature = hash_hmac(
                'sha256',
                $razorpayOrderId.'|'.$razorpayPaymentId,
                $this->keySecret
            );

            if ($expectedSignature !== $razorpaySignature) {
                Log::error('Razorpay signature verification failed', [
                    'payment_id' => $payment->id,
                    'expected' => $expectedSignature,
                    'received' => $razorpaySignature,
                ]);

                return false;
            }

            // Fetch payment details from Razorpay
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get($this->baseUrl.'/payments/'.$razorpayPaymentId);

            if (! $response->successful()) {
                return false;
            }

            $paymentDetails = $response->json();

            // Update payment record
            $payment->update([
                'status' => 'completed',
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
                'transaction_id' => $razorpayPaymentId,
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'payment_details' => $paymentDetails,
                ]),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay payment verification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process Razorpay refund
     */
    public function refund(Payment $payment, int $amount): bool
    {
        try {
            if (! $payment->razorpay_payment_id) {
                return false;
            }

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl.'/payments/'.$payment->razorpay_payment_id.'/refund', [
                    'amount' => $amount, // Amount in paise
                    'notes' => [
                        'payment_id' => $payment->id,
                        'order_id' => $payment->order_id,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay refund failed', [
                    'payment_id' => $payment->id,
                    'response' => $response->json(),
                ]);

                return false;
            }

            $refundDetails = $response->json();

            // Update payment metadata
            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refund_details' => $refundDetails,
                ]),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return 'razorpay';
    }
}
