<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->numberBetween(10000, 500000); // 100 to 5000 rupees in paisa
        $fee = 0;
        $tax = 0;

        return [
            'wallet_id' => Wallet::factory(),
            'transactionable_type' => null,
            'transactionable_id' => null,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => $amount,
            'fee' => $fee,
            'tax' => $tax,
            'net_amount' => $amount - $fee - $tax,
            'currency' => 'INR',
            'payment_method' => PaymentMethodCast::WALLET,
            'integration_id' => null,
            'provider_order_id' => null,
            'provider_transaction_id' => null,
            'provider_signature' => null,
            'checkout_url' => null,
            'qr_code_url' => null,
            'is_verified' => false,
            'verified_at' => null,
            'description' => null,
            'purpose' => null,
            'notes' => null,
            'reference_number' => null,
            'parent_transaction_id' => null,
            'expires_at' => null,
            'balance_after' => null,
            'metadata' => null,
            'provider_response' => null,
        ];
    }

    /**
     * For a specific wallet
     */
    public function forWallet(Wallet $wallet): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_id' => $wallet->id,
        ]);
    }

    /**
     * With transactionable model
     */
    public function forModel($model): static
    {
        return $this->state(fn (array $attributes) => [
            'transactionable_type' => get_class($model),
            'transactionable_id' => $model->getKey(),
        ]);
    }

    /**
     * Set amount (in paisa)
     */
    public function withAmount(int $amount, int $fee = 0, int $tax = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'fee' => $fee,
            'tax' => $tax,
            'net_amount' => $amount - $fee - $tax,
        ]);
    }

    /**
     * Credit transaction
     */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::CREDIT,
        ]);
    }

    /**
     * Debit transaction
     */
    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::DEBIT,
        ]);
    }

    /**
     * Refund transaction
     */
    public function refund(?int $parentTransactionId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::REFUND,
            'parent_transaction_id' => $parentTransactionId,
        ]);
    }

    /**
     * Chargeback transaction
     */
    public function chargeback(?int $parentTransactionId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::CHARGEBACK,
            'parent_transaction_id' => $parentTransactionId,
        ]);
    }

    /**
     * Adjustment transaction
     */
    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::ADJUSTMENT,
        ]);
    }

    /**
     * Hold transaction
     */
    public function hold(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::HOLD,
        ]);
    }

    /**
     * Release transaction
     */
    public function release(?int $parentTransactionId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::RELEASE,
            'parent_transaction_id' => $parentTransactionId,
        ]);
    }

    /**
     * Pending status
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::PENDING,
            'is_verified' => false,
        ]);
    }

    /**
     * Processing status
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::PROCESSING,
        ]);
    }

    /**
     * Completed status
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::COMPLETED,
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Failed status
     */
    public function failed(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::FAILED,
            'notes' => $reason ?? 'Transaction failed',
        ]);
    }

    /**
     * Cancelled status
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::CANCELLED,
        ]);
    }

    /**
     * On hold status
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::ON_HOLD,
        ]);
    }

    /**
     * Expired status
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusCast::EXPIRED,
            'expires_at' => now()->subHour(),
        ]);
    }

    /**
     * Wallet payment method
     */
    public function viaWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::WALLET,
        ]);
    }

    /**
     * UPI payment method
     */
    public function viaUpi(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::UPI,
        ]);
    }

    /**
     * Cashfree payment method
     */
    public function viaCashfree(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::CASHFREE,
        ]);
    }

    /**
     * Razorpay payment method
     */
    public function viaRazorpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::RAZORPAY,
        ]);
    }

    /**
     * Bank transfer method
     */
    public function viaBankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::BANK_TRANSFER,
        ]);
    }

    /**
     * Cash method
     */
    public function viaCash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::CASH,
        ]);
    }

    /**
     * COD method
     */
    public function viaCod(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => PaymentMethodCast::COD,
        ]);
    }

    /**
     * With provider details
     */
    public function withProviderDetails(
        ?string $orderId = null,
        ?string $transactionId = null,
        ?string $signature = null
    ): static {
        return $this->state(fn (array $attributes) => [
            'provider_order_id' => $orderId ?? 'order_'.fake()->uuid(),
            'provider_transaction_id' => $transactionId ?? 'pay_'.fake()->uuid(),
            'provider_signature' => $signature ?? fake()->sha256(),
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * With integration
     */
    public function withIntegration(Integration $integration): static
    {
        return $this->state(fn (array $attributes) => [
            'integration_id' => $integration->id,
        ]);
    }

    /**
     * With expiration
     */
    public function expiresIn(int $minutes = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * With purpose
     */
    public function withPurpose(string $purpose): static
    {
        return $this->state(fn (array $attributes) => [
            'purpose' => $purpose,
        ]);
    }

    /**
     * With description
     */
    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }

    /**
     * Commission payout transaction
     */
    public function commissionPayout(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::CREDIT,
            'amount' => $amount,
            'net_amount' => $amount,
            'purpose' => 'commission_payout',
            'description' => 'Commission credited to wallet',
            'payment_method' => PaymentMethodCast::WALLET,
        ]);
    }

    /**
     * Subscription payment transaction
     */
    public function subscriptionPayment(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::DEBIT,
            'amount' => $amount,
            'net_amount' => $amount,
            'purpose' => 'subscription_payment',
            'description' => 'Subscription purchase',
        ]);
    }

    /**
     * Withdrawal transaction
     */
    public function withdrawal(int $amount, int $fee = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeCast::DEBIT,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $amount - $fee,
            'purpose' => 'withdrawal',
            'description' => 'Withdrawal to bank account',
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);
    }
}
