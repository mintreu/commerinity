<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Integration Model - Stores payment provider configurations
 *
 * Types: payment, payout, sms, shipping
 */
class Integration extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_PAYMENT = 'payment';

    public const TYPE_PAYOUT = 'payout';

    public const TYPE_SMS = 'sms';

    public const TYPE_SHIPPING = 'shipping';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'type',
        'credentials',
        'settings',
        'is_sandbox',
        'is_active',
        'is_default',
        'last_tested_at',
        'last_test_result',
        'last_test_message',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'credentials' => 'array',
            'is_sandbox' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Integration $integration) {
            if (! $integration->uuid) {
                $integration->uuid = Str::uuid()->toString();
            }
        });
    }

    // ========================================
    // Credentials (Encrypted)
    // ========================================

    /**
     * Get decrypted credentials
     */
    public function getCredentialsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($value), true) ?? [];
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * Set encrypted credentials
     */
    public function setCredentialsAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['credentials'] = null;

            return;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['credentials'] = Crypt::encryptString($value);
    }

    /**
     * Get a specific credential key
     */
    public function getCredential(string $key, mixed $default = null): mixed
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Get API key from credentials
     */
    public function getApiKey(): ?string
    {
        return $this->getCredential('api_key') ?? $this->getCredential('key');
    }

    /**
     * Get API secret from credentials
     */
    public function getApiSecret(): ?string
    {
        return $this->getCredential('api_secret') ?? $this->getCredential('secret');
    }

    /**
     * Get webhook secret
     */
    public function getWebhookSecret(): ?string
    {
        return $this->getCredential('webhook_secret');
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get transactions using this integration
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get beneficiary accounts verified with this integration
     */
    public function beneficiaryAccounts(): HasMany
    {
        return $this->hasMany(BeneficiaryAccount::class);
    }

    // ========================================
    // Status Methods
    // ========================================

    /**
     * Check if integration is usable
     */
    public function isUsable(): bool
    {
        return $this->is_active && ! empty($this->credentials);
    }

    /**
     * Make this the default integration for its type
     */
    public function makeDefault(): void
    {
        // Remove default from other integrations of same type
        static::where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->is_default = true;
        $this->save();
    }

    /**
     * Record test result
     */
    public function recordTestResult(bool $success, ?string $message = null): void
    {
        $this->last_tested_at = now();
        $this->last_test_result = $success ? 'success' : 'failed';
        $this->last_test_message = $message;
        $this->save();
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePayment($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopePayout($query)
    {
        return $query->where('type', self::TYPE_PAYOUT);
    }

    public function scopeSms($query)
    {
        return $query->where('type', self::TYPE_SMS);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // ========================================
    // Static Helpers
    // ========================================

    /**
     * Get default integration for a type
     */
    public static function getDefault(string $type): ?self
    {
        return static::active()
            ->ofType($type)
            ->default()
            ->first();
    }

    /**
     * Get default payment integration
     */
    public static function getDefaultPayment(): ?self
    {
        return static::getDefault(self::TYPE_PAYMENT);
    }

    /**
     * Get default payout integration
     */
    public static function getDefaultPayout(): ?self
    {
        return static::getDefault(self::TYPE_PAYOUT);
    }

    /**
     * Get integration by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::bySlug($slug)->first();
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
