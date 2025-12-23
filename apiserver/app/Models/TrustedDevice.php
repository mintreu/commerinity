<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TrustedDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'ip_address',
        'user_agent',
        'trusted_at',
        'last_used_at',
        'expires_at',
        'country_code',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'trusted_at' => 'datetime',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if device trust has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Update last used timestamp.
     */
    public function updateLastUsed(): void
    {
        $this->last_used_at = now();
        $this->save();
    }

    /**
     * Generate device fingerprint from request.
     */
    public static function generateFingerprint(string $userAgent, string $ip): string
    {
        $data = $userAgent.$ip.config('app.key');

        return hash('sha256', $data);
    }

    /**
     * Check if this device is currently trusted and not expired.
     */
    public function isTrusted(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Extend trust for another 30 days.
     */
    public function extendTrust(int $days = 30): void
    {
        $this->expires_at = now()->addDays($days);
        $this->save();
    }
}
