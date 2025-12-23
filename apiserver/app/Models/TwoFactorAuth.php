<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

final class TwoFactorAuth extends Model
{
    protected $fillable = [
        'user_id',
        'enabled',
        'method',
        'enabled_at',
        'totp_secret',
        'totp_algorithm',
        'totp_digits',
        'totp_period',
        'backup_codes',
        'backup_codes_used',
        'backup_codes_total',
        'backup_codes_generated_at',
        'failed_attempts',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'enabled_at' => 'datetime',
            'backup_codes_generated_at' => 'datetime',
            'locked_until' => 'datetime',
            'totp_digits' => 'integer',
            'totp_period' => 'integer',
            'backup_codes_used' => 'integer',
            'backup_codes_total' => 'integer',
            'failed_attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get decrypted TOTP secret.
     */
    public function getDecryptedTotpSecret(): ?string
    {
        return $this->totp_secret ? Crypt::decryptString($this->totp_secret) : null;
    }

    /**
     * Set encrypted TOTP secret.
     */
    public function setEncryptedTotpSecret(string $secret): void
    {
        $this->totp_secret = Crypt::encryptString($secret);
    }

    /**
     * Get decrypted backup codes.
     */
    public function getDecryptedBackupCodes(): array
    {
        if (! $this->backup_codes) {
            return [];
        }

        $decrypted = Crypt::decryptString($this->backup_codes);

        return json_decode($decrypted, true) ?? [];
    }

    /**
     * Set encrypted backup codes.
     */
    public function setEncryptedBackupCodes(array $codes): void
    {
        $json = json_encode($codes);
        $this->backup_codes = Crypt::encryptString($json);
        $this->backup_codes_total = count($codes);
        $this->backup_codes_used = 0;
        $this->backup_codes_generated_at = now();
    }

    /**
     * Check if account is temporarily locked due to failed attempts.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Increment failed attempts and lock if necessary.
     */
    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_attempts');

        if ($this->failed_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
            $this->save();
        }
    }

    /**
     * Reset failed attempts on successful verification.
     */
    public function resetFailedAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }
}
