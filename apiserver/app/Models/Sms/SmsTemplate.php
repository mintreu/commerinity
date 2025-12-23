<?php

declare(strict_types=1);

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SMS Template Model - DLT approved message templates.
 *
 * @property int $id
 * @property int $sms_provider_id
 * @property string $name
 * @property string $slug
 * @property string $message_id
 * @property string|null $entity_id
 * @property string|null $template_id
 * @property string $sender_id
 * @property string $content
 * @property array<string>|null $variables
 * @property int $variable_count
 * @property string $category
 * @property string $language
 * @property bool $is_active
 * @property bool $is_dlt_approved
 * @property \Carbon\Carbon|null $dlt_approved_at
 * @property int $usage_count
 * @property \Carbon\Carbon|null $last_used_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class SmsTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sms_provider_id',
        'name',
        'slug',
        'message_id',
        'entity_id',
        'template_id',
        'sender_id',
        'content',
        'variables',
        'variable_count',
        'category',
        'language',
        'is_active',
        'is_dlt_approved',
        'dlt_approved_at',
        'usage_count',
        'last_used_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'variable_count' => 'integer',
            'is_active' => 'boolean',
            'is_dlt_approved' => 'boolean',
            'dlt_approved_at' => 'datetime',
            'usage_count' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * @return BelongsTo<SmsProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'sms_provider_id');
    }

    /**
     * @return HasMany<SmsLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Get active templates.
     *
     * @param  Builder<SmsTemplate>  $query
     * @return Builder<SmsTemplate>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get DLT approved templates.
     *
     * @param  Builder<SmsTemplate>  $query
     * @return Builder<SmsTemplate>
     */
    public function scopeDltApproved(Builder $query): Builder
    {
        return $query->where('is_dlt_approved', true);
    }

    /**
     * Get templates by category.
     *
     * @param  Builder<SmsTemplate>  $query
     * @return Builder<SmsTemplate>
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Get OTP templates.
     *
     * @param  Builder<SmsTemplate>  $query
     * @return Builder<SmsTemplate>
     */
    public function scopeOtp(Builder $query): Builder
    {
        return $query->category('otp');
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Render template with variables.
     *
     * @param  array<string, string>  $values
     */
    public function render(array $values): string
    {
        $content = $this->content;

        foreach ($values as $key => $value) {
            $content = str_replace("{#$key#}", $value, $content);
        }

        return $content;
    }

    /**
     * Get variables as pipe-separated string for Fast2SMS.
     *
     * @param  array<string, string>  $values
     */
    public function getVariablesPipeString(array $values): string
    {
        if (empty($this->variables)) {
            return '';
        }

        $orderedValues = [];
        foreach ($this->variables as $variable) {
            $orderedValues[] = $values[$variable] ?? '';
        }

        return implode('|', $orderedValues);
    }

    /**
     * Record template usage.
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Mark as DLT approved.
     */
    public function markAsDltApproved(): void
    {
        $this->update([
            'is_dlt_approved' => true,
            'dlt_approved_at' => now(),
        ]);
    }
}
