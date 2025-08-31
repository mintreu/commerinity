<?php

namespace Mintreu\LaravelIntegration\Contracts;

use Illuminate\Http\Request;

interface PaymentIntegrationContract
{

//    /* ============================================================
//     *  PAYMENT METHODS
//     * ============================================================
//     */
//
//    /**
//     * Create a normal payment request (card, UPI, net banking, wallet, etc.)
//     *
//     * @param array $data
//     * @return array
//     */
//    public function createPayment(array $data): array;
//
//    /**
//     * Create a payment request with QR code (static/dynamic)
//     *
//     * @param array $data
//     * @return array
//     */
//    public function createQrPayment(array $data): array;
//
//    /**
//     * Create a payment link (for email/SMS/invoice)
//     *
//     * @param array $data
//     * @return array
//     */
//    public function createPaymentLink(array $data): array;
//
//    /**
//     * Capture a pre-authorized payment
//     *
//     * @param string $paymentId
//     * @param float|null $amount
//     * @return array
//     */
//    public function capturePayment(string $paymentId, ?float $amount = null): array;
//
//    /**
//     * Cancel/void a pre-authorized or pending payment
//     *
//     * @param string $paymentId
//     * @return array
//     */
//    public function cancelPayment(string $paymentId): array;
//
//    /**
//     * Fetch payment status by provider's payment/order ID
//     *
//     * @param string $paymentId
//     * @return array
//     */
//    public function fetchPaymentStatus(string $paymentId): array;
//
//    /**
//     * Refund a payment (full or partial)
//     *
//     * @param string $paymentId
//     * @param float $amount
//     * @param string|null $reason
//     * @return array
//     */
//    public function refundPayment(string $paymentId, float $amount, ?string $reason = null): array;
//
//    /**
//     * Handle payment-related webhooks
//     *
//     * @param Request $request
//     * @return array
//     */
//    public function handlePaymentWebhook(Request $request): array;
//
//
//
//    /* ============================================================
//     *  SUBSCRIPTION METHODS
//     * ============================================================
//     */
//
//    public function createSubscription(array $data): array;
//    public function cancelSubscription(string $subscriptionId): array;
//    public function fetchSubscription(string $subscriptionId): array;
//
//
//    /* ============================================================
//     *  PAYOUT METHODS
//     * ============================================================
//     */
//
//    /**
//     * Create a payout (bank transfer, UPI, wallet, etc.)
//     *
//     * @param array $data
//     * @return array
//     */
//    public function createPayout(array $data): array;
//
//    /**
//     * Fetch payout status
//     *
//     * @param string $payoutId
//     * @return array
//     */
//    public function fetchPayoutStatus(string $payoutId): array;
//
//    /**
//     * Cancel a pending payout (if supported)
//     *
//     * @param string $payoutId
//     * @return array
//     */
//    public function cancelPayout(string $payoutId): array;
//
//    /**
//     * Handle payout-related webhooks
//     *
//     * @param Request $request
//     * @return array
//     */
//    public function handlePayoutWebhook(Request $request): array;
//
//
//    /* ============================================================
//     *  PAYOUT BENEFICIARY / CONTACT MANAGEMENT
//     * ============================================================
//     */
//
//    /**
//     * Create a beneficiary/contact for payouts
//     *
//     * @param array $data
//     * @return array
//     */
//    public function createPayoutContact(array $data): array;
//
//    /**
//     * Fetch payout contact
//     *
//     * @param string $contactId
//     * @return array
//     */
//    public function fetchPayoutContact(string $contactId): array;
//
//    /**
//     * Delete payout contact
//     *
//     * @param string $contactId
//     * @return bool
//     */
//    public function deletePayoutContact(string $contactId): bool;
//
//
//    /* ============================================================
//     *  FUNDING / PROVIDER ACCOUNT MANAGEMENT
//     * ============================================================
//     */
//
//    /**
//     * Add funds to provider account (if supported)
//     *
//     * @param array $data
//     * @return array
//     */
//    public function fundAccount(array $data): array;
//
//    /**
//     * Fetch account balance
//     *
//     * @return array
//     */
//    public function getBalance(): array;
//
//    /**
//     * Fetch provider-supported currencies
//     *
//     * @return array
//     */
//    public function getSupportedCurrencies(): array;
//
//    /**
//     * Fetch provider account credentials/info
//     *
//     * @return array
//     */
//    public function getAccountCredentials(): array;


}
