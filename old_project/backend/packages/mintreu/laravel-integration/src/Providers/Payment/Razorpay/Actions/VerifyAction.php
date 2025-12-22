<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Actions;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mintreu\LaravelIntegration\Casts\PaymentMethodTypeCast;
use Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider;
use Throwable;

/**
 * Class VerifyAction
 *
 * Handles the verification of payments via Razorpay, including callback and webhook verifications.
 */
class VerifyAction
{
    /**
     * The instance of LaravelRazorpay service.
     *
     * @var RazorpayPaymentProvider
     */
    protected RazorpayPaymentProvider $provider;

    /**
     * The payment model instance.
     *
     * @var RazorpayPaymentProvider|null
     */
    protected ?RazorpayPaymentProvider $transaction = null;

    /**
     * VerifyAction constructor.
     *
     * @param RazorpayPaymentProvider $razorpay The instance of LaravelRazorpay service.
     */
    public function __construct(RazorpayPaymentProvider $razorpay)
    {
        $this->provider = $razorpay;
    }

    /**
     * Verify payment via a callback request.
     *
     * @param Request $request The HTTP request containing the callback data.
     * @param Model|null $transaction Optional payment model instance.
     * @return bool True if verification is successful, false otherwise.
     * @throws Throwable If an error occurs during verification.
     */
    public function viaCallback(Request $request, ?Model $transaction = null): bool
    {
        $this->transaction = $transaction ?? $this->getPaymentRecordFromRequest($request);
        $success = false;
        $validateAttributes = [];

        try {
            $validateAttributes = $this->getValidateRazorpayCallbackRequestAttributes($request);

            if (is_null($this->provider->getError())) {
                $verify = $this->provider->getApi()->utility->verifyPaymentSignature($validateAttributes);
                if (is_null($verify)) {
                    $success = true;
                }
            }
        } catch (Throwable $e) {
            report($e);
            $this->provider->setError($e->getMessage());
        }

        // Update the payment model if verified and no error.
        if ($success && is_null($this->provider->getError())) {
            $this->transaction->fill([
                'provider_transaction_id' => $validateAttributes['razorpay_payment_id'],
                'provider_gen_sign' => $validateAttributes['razorpay_signature'],
                'verified' => true,
            ])->save();
            return true;
        }

        return false;
    }

    /**
     * Verify payment via a webhook request.
     *
     * @param Request $request The HTTP request containing the webhook data.
     * @return array An array containing verification status, error message, and payment record.
     */
    public function viaWebhook(Request $request): array
    {
        $success = true;
        $attributes = [];

        try {
            $this->transaction = $this->getPaymentRecordFromRequest($request);
            $attributes = $this->getValidatedWebhookRequestAttributes($request);

            if ($this->transaction->checkout_type == PaymentMethodTypeCast::QR) {
                // Validate QR code payment.
                $success = $this->validatePaymentForQrCode($attributes);
            } else {
                $this->provider->getApi()->utility->verifyWebhookSignature(
                    $attributes['body'],
                    $attributes['signature'],
                    $this->provider->getWebhook()
                );
            }
        } catch (Throwable $e) {
            report($e);
            $success = false;
            $this->provider->setError($e->getMessage());
        }

        $this->transaction->fill([
            'provider_transaction_id' => $attributes['razorpay_payment_id'],
            'provider_gen_sign' => $attributes['razorpay_signature'],
            'verified' => $success,
        ])->save();

        return [
            'status' => $success,
            'error' => $this->provider->getError(),
            'record' => $this->transaction,
        ];
    }

    // Private Methods

    /**
     * Retrieve the payment model from the request.
     *
     * @param Request $request The HTTP request containing payment data.
     * @return Model|null The payment model or null if not found.
     */
    protected function getPaymentRecordFromRequest(Request $request): ?Model
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'nullable|string',
            'razorpay_payment_link_id' => 'nullable|string',
            'payload.order.entity.id' => 'nullable|string',
        ]);

        // Determine which key is present in the validated data.
        $value = $validated['razorpay_order_id'] ?? $validated['razorpay_payment_link_id'] ?? $validated['payload']['order']['entity']['id'] ?? null;

        return $this->provider->getModel();
    }

    /**
     * Validate the callback request attributes from Razorpay.
     *
     * @param Request $request The HTTP request containing the callback data.
     * @return array The validated attributes.
     * @throws Throwable If the payment record is null.
     */
    protected function getValidateRazorpayCallbackRequestAttributes(Request $request): array
    {
        throw_unless($this->transaction, 'Payment record cannot be null');

        $attributes = [];

        if ($this->transaction->checkout_type == PaymentMethodTypeCast::STANDARD) {
            $attributes = $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            if ($this->transaction->provider_gen_id != $attributes['razorpay_order_id']) {
                $this->provider->setError('Callback provider order ID does not match!');
            }
        }

        if ($this->transaction->type == PaymentMethodTypeCast::LINK) {
            $attributes = $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_payment_link_id' => 'required|string',
                'razorpay_payment_link_reference_id' => 'required|string',
                'razorpay_payment_link_status' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            if ($attributes['razorpay_payment_link_status'] != 'paid') {
                $this->provider->setError('Callback provider payment link status must be paid! Found: ' . $attributes['razorpay_payment_link_status']);
            }

            if ($this->transaction->provider_gen_id != $attributes['razorpay_payment_link_id']) {
                $this->provider->setError('Callback provider payment link ID does not match!');
            }
        }

        return $attributes;
    }

    /**
     * Validate the webhook request attributes from Razorpay.
     *
     * @param Request $request The HTTP request containing the webhook data.
     * @return array The validated attributes.
     */
    protected function getValidatedWebhookRequestAttributes(Request $request): array
    {
        $validated = $request->validate([
            'payload.order.entity.id' => 'required|string',
            'payload.payment.entity.id' => 'required|string',
        ]);

        return [
            'body' => $request->getContent(),
            'signature' => $request->header('x-razorpay-signature'),
            'razorpay_payment_id' => $validated['payload']['payment']['entity']['id']
        ];
    }

    /**
     * Validate the payment for a QR code.
     *
     * @param array $attributes The attributes from the QR code payment request.
     * @return bool True if the payment is valid, false otherwise.
     */
    private function validatePaymentForQrCode(array $attributes): bool
    {
        $response = $this->provider->qr()->fetch($this->transaction->provider_gen_id);
        $responseData = $response->toArray();

        // Fetch QR code payments and check validity.
        $paymentResponse = $this->provider->qr()->fetch($this->transaction->provider_gen_id)->fetchAllPayments();
        $paymentResponseData = $paymentResponse->toArray();

        return $paymentResponseData['count'] > 0
            && $paymentResponseData['items'][0]['captured']
            && $paymentResponseData['items'][0]['id'] == $attributes['razorpay_payment_id']
            && $responseData['amount'] == $responseData['payments_amount_received'];
    }
}
