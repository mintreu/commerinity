<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Models\Traits\Cart\HasCartOwner;
use App\Models\Traits\HasKyc;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Mintreu\LaravelGeokit\Traits\HasAddress;
use Mintreu\Toolkit\Casts\GenderCast;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class User extends Authenticatable implements MustVerifyEmail,HasMedia,FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,HasFactory, Notifiable,InteractsWithMedia,HasRecursiveRelationships,HasAddress,HasCartOwner,HasKyc;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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
        'mobile_verified_at'
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
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => GenderCast::class,
            'type' => AuthTypeCast::class,
            'status' => AuthStatusCast::class,
        ];
    }

    protected $appends = ['avatar'];

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
            ->useFallbackUrl('https://i.pravatar.cc/300');

    }
}
