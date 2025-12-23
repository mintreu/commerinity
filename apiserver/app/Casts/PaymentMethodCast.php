<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Payment Method - Covers both native and third-party methods
 */
enum PaymentMethodCast: string implements HasColor, HasIcon, HasLabel
{
    // Native payment methods (no third-party)
    case CASH = 'cash';
    case COD = 'cod';
    case WALLET = 'wallet';
    case BANK_TRANSFER = 'bank_transfer';

    // Third-party payment methods
    case CASHFREE = 'cashfree';
    case RAZORPAY = 'razorpay';
    case STRIPE = 'stripe';
    case PAYTM = 'paytm';
    case UPI = 'upi';

    // Payout methods
    case PAYOUT_BANK = 'payout_bank';
    case PAYOUT_UPI = 'payout_upi';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::COD => 'Cash on Delivery',
            self::WALLET => 'Wallet',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASHFREE => 'Cashfree',
            self::RAZORPAY => 'Razorpay',
            self::STRIPE => 'Stripe',
            self::PAYTM => 'Paytm',
            self::UPI => 'UPI',
            self::PAYOUT_BANK => 'Bank Payout',
            self::PAYOUT_UPI => 'UPI Payout',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CASH, self::COD => 'success',
            self::WALLET => 'primary',
            self::BANK_TRANSFER => 'info',
            self::CASHFREE => 'purple',
            self::RAZORPAY => 'blue',
            self::STRIPE => 'indigo',
            self::PAYTM => 'cyan',
            self::UPI => 'green',
            self::PAYOUT_BANK, self::PAYOUT_UPI => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CASH, self::COD => 'heroicon-o-banknotes',
            self::WALLET => 'heroicon-o-wallet',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::CASHFREE, self::RAZORPAY, self::STRIPE, self::PAYTM => 'heroicon-o-credit-card',
            self::UPI => 'heroicon-o-qr-code',
            self::PAYOUT_BANK => 'heroicon-o-arrow-up-on-square',
            self::PAYOUT_UPI => 'heroicon-o-arrow-up-on-square',
        };
    }

    /**
     * Check if this is a native payment method (no third-party integration).
     */
    public function isNative(): bool
    {
        return in_array($this, [
            self::CASH,
            self::COD,
            self::WALLET,
            self::BANK_TRANSFER,
        ]);
    }

    /**
     * Check if this requires third-party integration.
     */
    public function isThirdParty(): bool
    {
        return in_array($this, [
            self::CASHFREE,
            self::RAZORPAY,
            self::STRIPE,
            self::PAYTM,
            self::UPI,
        ]);
    }

    /**
     * Check if this is a payout method.
     */
    public function isPayout(): bool
    {
        return in_array($this, [
            self::PAYOUT_BANK,
            self::PAYOUT_UPI,
        ]);
    }

    /**
     * Check if this method requires immediate payment verification.
     */
    public function requiresVerification(): bool
    {
        return $this->isThirdParty();
    }

    /**
     * Get the provider service class slug for third-party methods.
     */
    public function getProviderSlug(): ?string
    {
        return match ($this) {
            self::CASHFREE => 'cashfree',
            self::RAZORPAY => 'razorpay',
            self::STRIPE => 'stripe',
            self::PAYTM => 'paytm',
            default => null,
        };
    }
}
