<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface UserWalletServiceInterface
{
    /**
     * Get or create wallet for any walletable model
     */
    public function getOrCreateWallet(Model $owner, string $currency = 'INR'): Wallet;

    /**
     * Get wallet for a model
     */
    public function getWallet(Model $owner): ?Wallet;

    /**
     * Credit money to wallet
     *
     * @throws \RuntimeException
     */
    public function credit(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?string $description = null,
        ?Model $relatedModel = null
    ): Transaction;

    /**
     * Debit money from wallet
     *
     * @throws \RuntimeException
     */
    public function debit(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?string $description = null,
        ?Model $relatedModel = null
    ): Transaction;

    /**
     * Hold funds for pending payouts
     *
     * @throws \RuntimeException
     */
    public function hold(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?string $description = null
    ): Transaction;

    /**
     * Release held funds back to available balance
     *
     * @throws \RuntimeException
     */
    public function release(
        Wallet $wallet,
        int $amountInPaisa,
        string $purpose,
        ?Transaction $holdTransaction = null
    ): Transaction;

    /**
     * Transfer money between wallets
     *
     * @return array{debit: Transaction, credit: Transaction}
     *
     * @throws \RuntimeException
     */
    public function transfer(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $amountInPaisa,
        string $purpose = 'transfer',
        ?string $description = null
    ): array;

    /**
     * Process refund to wallet
     */
    public function refund(
        Wallet $wallet,
        int $amountInPaisa,
        Transaction $originalTransaction,
        ?string $reason = null
    ): Transaction;

    /**
     * Admin adjustment (credit or debit)
     *
     * @param  int  $amountInPaisa  Positive for credit, negative for debit
     *
     * @throws \RuntimeException
     */
    public function adjustment(
        Wallet $wallet,
        int $amountInPaisa,
        string $reason,
        int $adminId
    ): Transaction;

    /**
     * Get transaction history for a wallet
     *
     * @return Collection<int, Transaction>
     */
    public function getTransactionHistory(Wallet $wallet, int $limit = 20): Collection;

    /**
     * Get wallet summary/stats
     *
     * @return array<string, mixed>
     */
    public function getWalletSummary(Wallet $wallet): array;

    /**
     * Add points to wallet
     */
    public function addPoints(Wallet $wallet, int $points): void;

    /**
     * Deduct points from wallet
     *
     * @throws \RuntimeException
     */
    public function deductPoints(Wallet $wallet, int $points): void;

    // ========================================
    // PIN Management
    // ========================================

    /**
     * Set wallet PIN (first time)
     *
     * @throws \RuntimeException if PIN already exists
     */
    public function setPin(Wallet $wallet, string $pin): bool;

    /**
     * Change wallet PIN
     *
     * @throws \RuntimeException if current PIN is incorrect
     */
    public function changePin(Wallet $wallet, string $currentPin, string $newPin): bool;

    /**
     * Verify wallet PIN
     */
    public function verifyPin(Wallet $wallet, string $pin): bool;

    /**
     * Reset wallet PIN (admin operation)
     */
    public function resetPin(Wallet $wallet, string $newPin, int $adminId): bool;

    /**
     * Check if wallet has PIN set
     */
    public function hasPin(Wallet $wallet): bool;

    // ========================================
    // P2P Transfer Operations
    // ========================================

    /**
     * Send money to another user's wallet (P2P transfer)
     *
     * @return array{debit: Transaction, credit: Transaction}
     *
     * @throws \RuntimeException
     */
    public function sendMoney(
        Wallet $senderWallet,
        string $recipientIdentifier,
        int $amountInPaisa,
        ?string $note = null
    ): array;

    /**
     * Request money from another user
     *
     * @return array{request_id: string, message: string}
     */
    public function requestMoney(
        Wallet $requesterWallet,
        string $fromIdentifier,
        int $amountInPaisa,
        ?string $note = null
    ): array;

    /**
     * Convert points to wallet balance
     *
     * @throws \RuntimeException
     */
    public function convertPointsToBalance(Wallet $wallet, int $points): Transaction;

    /**
     * Get wallet QR code data
     *
     * @return array{uuid: string, qr_data: string}
     */
    public function getWalletQrCode(Wallet $wallet): array;
}
