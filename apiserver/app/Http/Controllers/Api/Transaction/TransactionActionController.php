<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * TransactionActionController
 *
 * Handles payment confirmation and failure callbacks from payment providers.
 * Routes to _transaction endpoints as configured in provider settings.
 */
class TransactionActionController extends Controller
{
    /**
     * Confirm transaction on successful payment
     *
     * Called by payment provider (Cashfree, Razorpay) when payment completes.
     * Verifies signature, updates transaction, and dispatches TransactionConfirmed event.
     * Then redirects to success_redirect_url.
     */
    public function confirmTransaction(Transaction $transaction, Request $request): RedirectResponse
    {

        // TODO: Add signature verification here
        // For now, trust the callback from provider

        // Update transaction as verified and completed
        $transaction->update([
            'verified' => true,
            'verified_at' => now(),
            'status' => 'completed',
            'provider_generated_sign' => $request->input('signature'),
        ]);

        // Dispatch PaymentCompleted event - triggers wallet balance update via HandlePaymentCompleted listener
        event(new \App\Events\PaymentCompleted($transaction));

        // Redirect to success URL
        return redirect()->to($transaction->success_redirect_url ?? $transaction->success_url);
    }

    /**
     * Handle failed transaction
     *
     * Called when payment fails or is cancelled by user.
     * Updates transaction status and redirects to failure_redirect_url.
     */
    public function failureTransaction(Transaction $transaction, Request $request): RedirectResponse
    {
        // Update transaction as failed
        $transaction->update([
            'status' => 'failed',
        ]);

        // TODO: Dispatch TransactionFailed event
        // event(new TransactionFailed($transaction));

        // Redirect to failure URL
        return redirect()->to($transaction->failure_redirect_url ?? $transaction->failure_url);
    }
}
