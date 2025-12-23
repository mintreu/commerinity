<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AdminStatusCast;
use App\Casts\AdminTypeCast;
use App\Traits\HasHelpdeskTickets;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasHelpdeskTickets;
    use Notifiable;
    use SoftDeletes;

    protected $guard = 'admin';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'mobile',
        'password',
        'type',
        'status',
        'created_by_admin_id',
        'level',
        'profit_share_percent',
        'profit_share_active',
        'locale',
        'preferences',
        'two_factor_secret',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'type' => AdminTypeCast::class,
            'status' => AdminStatusCast::class,
            'email_verified_at' => 'datetime',
            'profit_share_percent' => 'decimal:2',
            'profit_share_active' => 'boolean',
            'preferences' => 'array',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Admin $admin) {
            if (! $admin->uuid) {
                $admin->uuid = Str::uuid()->toString();
            }

            // Set level from type
            if ($admin->type) {
                $admin->level = $admin->type->getLevel();
            }

            // Set default profit share from type if not set
            if ($admin->type && ! $admin->profit_share_percent) {
                $admin->profit_share_percent = $admin->type->getDefaultProfitSharePercent();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the admin who created this admin
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Get all admins created by this admin
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Admin::class, 'created_by_admin_id');
    }

    /**
     * Get the admin's wallet (polymorphic)
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }

    // ========================================
    // Filament Methods
    // ========================================

    /**
     * Determine if the admin can access the Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === AdminStatusCast::Active;
    }

    // ========================================
    // Hierarchy Methods
    // ========================================

    /**
     * Check if this admin is the SuperAdmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->type === AdminTypeCast::SuperAdmin;
    }

    /**
     * Check if this admin can create an admin of the given type
     */
    public function canCreateAdminOfType(AdminTypeCast $type): bool
    {
        $canCreate = $this->type->canCreate();

        if (! $canCreate) {
            return false;
        }

        // Can create their direct subordinate type or any below it
        return $type->getLevel() >= $canCreate->getLevel();
    }

    /**
     * Get admin types visible to this admin
     *
     * @return array<AdminTypeCast>
     */
    public function getVisibleAdminTypes(): array
    {
        return $this->type->getVisibleTypes();
    }

    /**
     * Check if this admin can manage the given admin
     */
    public function canManage(Admin $other): bool
    {
        return $this->type->canManage($other->type);
    }

    // ========================================
    // Profit Share Methods
    // ========================================

    /**
     * Get the profit share percentage (custom or default from type)
     */
    public function getProfitSharePercent(): float
    {
        // Custom override takes precedence if set
        if ($this->profit_share_percent > 0) {
            return (float) $this->profit_share_percent;
        }

        // Fall back to type default
        return $this->type->getDefaultProfitSharePercent();
    }

    /**
     * Get or create the admin's wallet
     */
    public function getOrCreateWallet(): Wallet
    {
        if ($this->wallet) {
            return $this->wallet;
        }

        return $this->wallet()->create([
            'balance' => 0,
            'currency' => 'INR',
            'status' => 'active',
        ]);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
