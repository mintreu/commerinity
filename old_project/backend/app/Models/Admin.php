<?php

namespace App\Models;

use App\Models\Traits\Cart\HasCartOwner;
use App\Models\Traits\HasKyc;
use App\Models\Traits\HasOrder;
use App\Models\Traits\HasProductEngagement;
use App\Models\Traits\HasProductWishlist;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Mintreu\LaravelCommerinity\Traits\HasVoucherAccess;
use Mintreu\LaravelGeokit\Traits\HasAddress;
use Mintreu\LaravelHelpdesk\Traits\HasSupportTicket;
use Mintreu\LaravelTransaction\Traits\HasBeneficiary;
use Mintreu\LaravelTransaction\Traits\HasWallet;
use Mintreu\Toolkit\Traits\HasFingerprint;
use Mintreu\Toolkit\Traits\HasUnique;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Admin extends Authenticatable implements FilamentUser,HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasPushSubscriptions,InteractsWithMedia,HasFingerprint,
        HasAddress,HasCartOwner,HasKyc,HasUnique,
        HasOrder,HasFingerprint,
        HasSupportTicket,HasWallet,HasBeneficiary,HasVoucherAccess,HasProductEngagement,HasProductWishlist;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
            'password' => 'hashed',
        ];
    }

    protected $appends = ['avatar'];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatarImage')
            //->useFallbackUrl(asset('images/placeholder/user_placeholder.png'));
            ->useFallbackUrl('https://i.pravatar.cc/'.random_int(250,600));

    }


    /**
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
