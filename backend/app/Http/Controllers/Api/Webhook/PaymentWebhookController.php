<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Models\Transaction;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Razorpay webhooks
     */
    public function razorpay(Request $request): Response
    {
        try {
            $provider = LaravelIntegration::payment('razorpay-payment');
            $result = $provider->verify()->viaWebhook($request);

            if ($result['success']) {
                $this->updateTransactionStatus($result['transaction'], TransactionStatusCast::COMPLETED);
                return response('OK', 200);
            }

            return response('Verification Failed', 400);
        } catch (\Exception $e) {
            report($e);
            return response('Error', 500);
        }
    }

    /**
     * Handle Cashfree webhooks
     */
    public function cashfree(Request $request): Response
    {
        try {
            $provider = LaravelIntegration::payment('cash-free-payment');
            $result = $provider->verify()->viaWebhook($request);

            if ($result['success']) {
                $this->updateTransactionStatus($result['transaction'], TransactionStatusCast::COMPLETED);
                return response('OK', 200);
            }

            return response('Verification Failed', 400);
        } catch (\Exception $e) {
            report($e);
            return response('Error', 500);
        }
    }

    /**
     * Handle Paytm webhooks
     */
    public function paytm(Request $request): Response
    {
        try {
            $provider = LaravelIntegration::payment('paytm-payment');
            $result = $provider->verify()->viaWebhook($request);

            if ($result['success']) {
                $this->updateTransactionStatus($result['transaction'], TransactionStatusCast::COMPLETED);
                return response('OK', 200);
            }

            return response('Verification Failed', 400);
        } catch (\Exception $e) {
            report($e);
            return response('Error', 500);
        }
    }

    /**
     * Update transaction status based on webhook
     */
    private function updateTransactionStatus(Transaction $transaction, TransactionStatusCast $status): void
    {
        $transaction->update([
            'status' => $status->value,
            'verified' => $status === TransactionStatusCast::COMPLETED,
        ]);

        // Trigger appropriate events
        if ($status === TransactionStatusCast::COMPLETED) {
            event(new \Mintreu\LaravelTransaction\Events\TransactionConfirmed($transaction));
        } else {
            event(new \Mintreu\LaravelTransaction\Events\TransactionFailed($transaction));
        }
    }
}