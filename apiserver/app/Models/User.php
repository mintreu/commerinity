<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasBeneficiary;
use App\Models\Traits\HasFingerprint;
use App\Models\Traits\HasProductEngagement;
use App\Models\Traits\HasProductWishlist;
use App\Models\Traits\HasSaleAccess;
use App\Models\Traits\HasUnique;
use App\Models\Traits\HasVoucherAccess;
use App\Models\Traits\HasWallet;
use App\Traits\HasHelpdeskTickets;
use App\Traits\HasJobApplications;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
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

class User extends Authenticatable implements FilamentUser, HasMedia, MustVerifyEmail
{
    use HasAddress;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;

    use HasBeneficiary;
    use HasFactory;
    use HasFingerprint;
    use HasHelpdeskTickets;
    use HasJobApplications;
    use HasProductEngagement;
    use HasProductWishlist;
    use HasPushSubscriptions;
    use HasRecursiveRelationships;
    use HasSaleAccess;
    use HasUnique;
    use HasVoucherAccess;
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
     * Affiliate Parent (upline) relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Affiliate Children (downline) relationship
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
     * Affiliate Genealogy record (one per user)
     */
    public function genealogy(): HasOne
    {
        return $this->hasOne(Affiliate\AffiliateGenealogy::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access only to Active users who are Advisors or Mentors (Management roles)
        // Adjust this logic if there's a specific 'is_admin' column or similar
        return $this->status === UserStatusCast::ACTIVE && (
            $this->type === UserTypeCast::ADVISOR ||
            $this->type === UserTypeCast::MENTOR
        );
    }

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useFallbackUrl(asset('images/placeholder-avatar.png'))
            //->useFallbackUrl($this->gender == GenderCast::FEMALE->value ? 'https://avatar.iran.liara.run/public/girl' : 'https://avatar.iran.liara.run/public/boy')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
        // File size validation done in controller (2MB max)
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
