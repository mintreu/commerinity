<?php

namespace App\Models;

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Traits\HasHelpdeskTickets;
use App\Traits\HasJobApplications;
use App\Traits\HasWallet;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasHelpdeskTickets;
    use HasJobApplications;
    use HasPushSubscriptions;
    use HasRecursiveRelationships;
    use HasWallet;
    use InteractsWithMedia;
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'mobile',
        'password',
        'referral_code',
        'parent_id',
        'level_id',
        'originator_type',
        'originator_id',
        'bio',
        'gender',
        'dob',
        'type',
        'status',
        'status_feedback',
        'onboarded',
        'email_verified_at',
        'mobile_verified_at',
        'subscribed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'gender' => GenderCast::class,
            'type' => UserTypeCast::class,
            'status' => UserStatusCast::class,
            'onboarded' => 'boolean',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // Auto-generate UUID (16 chars with REG prefix + year)
            if (! $user->uuid) {
                $user->uuid = 'REG'.now()->year.Str::upper(Str::random(12));
            }

            // Auto-generate unique referral code (8 chars uppercase)
            if (! $user->referral_code) {
                do {
                    $code = Str::upper(Str::random(8));
                } while (self::where('referral_code', $code)->exists());

                $user->referral_code = $code;
            }
        });
    }

    /**
     * MLM Parent (upline) relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * MLM Children (downline) relationship
     */
    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Agent/Advisor who recruited this member
     */
    public function originator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Members recruited by this Agent/Advisor
     */
    public function originatedUsers(): MorphMany
    {
        return $this->morphMany(User::class, 'originator');
    }

    /**
     * User addresses relationship
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * User's single KYC record (hasOne for current active KYC)
     */
    public function kyc(): MorphOne
    {
        return $this->morphOne(Kyc::class, 'kycable')->latestOfMany();
    }

    /**
     * User KYC history (all records)
     */
    public function kycs(): MorphMany
    {
        return $this->morphMany(Kyc::class, 'kycable');
    }

    /**
     * Check if user has approved KYC
     */
    public function hasApprovedKyc(): bool
    {
        return $this->kyc?->isApproved() ?? false;
    }

    /**
     * User subscriptions (all membership subscriptions)
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Membership\UserSubscription::class);
    }

    /**
     * Get active subscription
     */
    public function activeSubscription(): ?Membership\UserSubscription
    {
        return $this->subscriptions()
            ->where('status', Membership\UserSubscription::STATUS_ACTIVE)
            ->where('is_paid', true)
            ->latest()
            ->first();
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', Membership\UserSubscription::STATUS_ACTIVE)
            ->where('is_paid', true)
            ->exists();
    }

    /**
     * Get default address
     */
    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('default', true)->first();
    }

    /**
     * Check if onboarding is complete
     */
    public function isOnboardingComplete(): bool
    {
        return $this->onboarded
            && $this->addresses()->exists()
            && ($this->hasVerifiedEmail() || $this->hasVerifiedMobile());
    }

    /**
     * Check if mobile is verified
     */
    public function hasVerifiedMobile(): bool
    {
        return ! is_null($this->mobile_verified_at);
    }

    /**
     * MLM Genealogy record (one per user)
     */
    public function genealogy(): HasOne
    {
        return $this->hasOne(Mlm\MlmGenealogy::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'mobile',
                'type',
                'status',
                'onboarded',
                'email_verified_at',
                'mobile_verified_at',
                'subscribed_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}");
    }
}
