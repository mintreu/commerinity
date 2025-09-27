<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Models\Lifecycle\Level;
use App\Models\Lifecycle\UserSubscription;
use App\Models\Traits\Cart\HasCartOwner;
use App\Models\Traits\HasKyc;
use App\Models\Traits\HasLifecycle;
use App\Models\Traits\HasOrder;
use App\Models\Traits\HasProductEngagement;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kirschbaum\Commentions\Contracts\Commenter;
use Laravel\Sanctum\HasApiTokens;
use Mintreu\LaravelCommerinity\Traits\HasSaleAccess;
use Mintreu\LaravelCommerinity\Traits\HasVoucherAccess;
use Mintreu\LaravelGeokit\Traits\HasAddress;
use Mintreu\LaravelHelpdesk\Traits\HasSupportTicket;
use Mintreu\LaravelNaukriManager\Models\NaukriApplication;
use Mintreu\LaravelTransaction\Traits\HasBeneficiary;
use Mintreu\LaravelTransaction\Traits\HasWallet;
use Mintreu\Toolkit\Casts\GenderCast;
use Mintreu\Toolkit\Contracts\Fingerprintable;
use Mintreu\Toolkit\Traits\HasFingerprint;
use Mintreu\Toolkit\Traits\HasUnique;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class User extends Authenticatable implements MustVerifyEmail,HasMedia,FilamentUser,Fingerprintable, Commenter
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,HasFactory, Notifiable,InteractsWithMedia,HasRecursiveRelationships,
        HasAddress,HasCartOwner,HasKyc,HasUnique, HasLifecycle,HasOrder,HasFingerprint,
        HasSupportTicket,HasWallet,HasBeneficiary,HasVoucherAccess,HasProductEngagement;

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
        'type',
        'status',
        'status_feedback',
        'bio',
        'gender',
        'dob',
        'email_verified_at',
        'mobile_verified_at',
        'onboarded'
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
            'gender' => GenderCast::class,
            'type' => AuthTypeCast::class,
            'status' => AuthStatusCast::class,
            'onboarded' => 'boolean'
        ];
    }

    protected $appends = ['avatar'];


    protected static function booted()
    {
        static::creating(function ($user){
            $user->setUniqueCodeUpper('referral_code',8);
            $user->setUniqueCode('uuid',16,'REG'.now()->year);
        });
        parent::booted();
    }


    public function originator(): MorphTo
    {
        return $this->morphTo();
    }

    public function originatedUsers(): MorphMany
    {
        return $this->morphMany(User::class, 'originator');
    }


    public function getAvatarAttribute(): string
    {
        return $this->getFirstMediaUrl('avatarImage') ?: 'https://i.pravatar.cc/300';
    }


    /**
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatarImage')
            //->useFallbackUrl(asset('images/placeholder/user_placeholder.png'));
            ->useFallbackUrl('https://i.pravatar.cc/'.random_int(250,600));

    }




    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class,'level_id','id');
    }


    public function memberships(): HasMany
    {
        return $this->hasMany(UserSubscription::class,'user_id','id');
    }

    public function membership(): HasOne
    {
        return $this->hasOne(UserSubscription::class, 'user_id', 'id')
            //->where('expire_at', '>=', now())
            ->latest();
    }


    public function applications(): HasMany
    {
        return $this->hasMany(NaukriApplication::class,'user_id','id');
    }







}
