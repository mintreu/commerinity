# Complete Admin & Activity Logging System

> **Status**: SAVED FOR FUTURE IMPLEMENTATION
> **Implement After**: MLM, E-commerce, Recruitment, User Journey, Task System complete
> **Created**: 2025-12-15

---

## Overview

This document contains the complete admin hierarchy and universal activity logging system design. The system is designed to be injected into the application after core features are complete.

---

## Part 1: Admin Hierarchy System

### 1.1 Admin Type Hierarchy (6 Levels)

```
SUPERADMIN (Company - Single Instance)
    ├── CEO (1-2, can create below)
    │   ├── DIRECTOR (can create below)
    │   │   ├── MANAGER (can create below)
    │   │   │   ├── LEAD (can create below)
    │   │   │   │   └── EXECUTIVE (lowest admin level)
```

### 1.2 AdminTypeCast Enum

| Type | Level | Profit Share % | Can Create | Description |
|------|-------|----------------|------------|-------------|
| SUPERADMIN | 0 | 100% (source) | CEO | Company's main account, cannot be duplicated |
| CEO | 1 | 15% | DIRECTOR | Chief Executive, top management |
| DIRECTOR | 2 | 10% | MANAGER | Department heads |
| MANAGER | 3 | 5% | LEAD | Team managers |
| LEAD | 4 | 3% | EXECUTIVE | Team leads |
| EXECUTIVE | 5 | 1% | None | Operations staff |

### 1.3 AdminTypeCast Implementation

```php
<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AdminTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SuperAdmin = 'superadmin';
    case Ceo = 'ceo';
    case Director = 'director';
    case Manager = 'manager';
    case Lead = 'lead';
    case Executive = 'executive';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => __('admin.types.superadmin'),
            self::Ceo => __('admin.types.ceo'),
            self::Director => __('admin.types.director'),
            self::Manager => __('admin.types.manager'),
            self::Lead => __('admin.types.lead'),
            self::Executive => __('admin.types.executive'),
        };
    }

    public function getLevel(): int
    {
        return match ($this) {
            self::SuperAdmin => 0,
            self::Ceo => 1,
            self::Director => 2,
            self::Manager => 3,
            self::Lead => 4,
            self::Executive => 5,
        };
    }

    public function getDefaultProfitSharePercent(): float
    {
        return match ($this) {
            self::SuperAdmin => 0.00,
            self::Ceo => 15.00,
            self::Director => 10.00,
            self::Manager => 5.00,
            self::Lead => 3.00,
            self::Executive => 1.00,
        };
    }

    public function canCreate(): ?self
    {
        return match ($this) {
            self::SuperAdmin => self::Ceo,
            self::Ceo => self::Director,
            self::Director => self::Manager,
            self::Manager => self::Lead,
            self::Lead => self::Executive,
            self::Executive => null,
        };
    }

    public function getVisibleTypes(): array
    {
        $level = $this->getLevel();
        return collect(self::cases())
            ->filter(fn($type) => $type->getLevel() >= $level)
            ->values()
            ->all();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::Ceo => 'warning',
            self::Director => 'primary',
            self::Manager => 'success',
            self::Lead => 'info',
            self::Executive => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SuperAdmin => 'heroicon-o-shield-check',
            self::Ceo => 'heroicon-o-star',
            self::Director => 'heroicon-o-briefcase',
            self::Manager => 'heroicon-o-user-group',
            self::Lead => 'heroicon-o-flag',
            self::Executive => 'heroicon-o-user',
        };
    }
}
```

### 1.4 AdminStatusCast Enum

```php
<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AdminStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => __('admin.status.active'),
            self::Inactive => __('admin.status.inactive'),
            self::Suspended => __('admin.status.suspended'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Suspended => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-pause-circle',
            self::Suspended => 'heroicon-o-x-circle',
        };
    }
}
```

---

## Part 2: Database Migrations

### 2.1 Admins Table

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile', 15)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('type')->default('executive');
            $table->string('status')->default('active');

            // Hierarchy
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(5);

            // Profit sharing
            $table->decimal('profit_share_percent', 5, 2)->default(0.00);
            $table->boolean('profit_share_active')->default(true);

            // Settings
            $table->string('locale', 5)->default('en');
            $table->json('preferences')->nullable();

            // Security
            $table->string('two_factor_secret', 100)->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['type', 'status']);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
```

### 2.2 Admin Profit Distributions Table

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_profit_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_wallet_id')->constrained('wallets');
            $table->foreignId('destination_wallet_id')->constrained('wallets');
            $table->foreignId('transaction_id')->nullable()->constrained();

            $table->string('period', 7); // YYYY-MM format
            $table->unsignedBigInteger('company_profit'); // Total profit in paisa
            $table->decimal('share_percent', 5, 2);
            $table->unsignedBigInteger('amount'); // Calculated share in paisa

            $table->string('status')->default('pending'); // pending, processed, failed
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['admin_id', 'period']);
            $table->index(['period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_profit_distributions');
    }
};
```

### 2.3 Business Snapshots Table

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->unique(); // YYYY-MM
            $table->string('type', 20)->default('monthly'); // daily, weekly, monthly, yearly

            // Revenue (in paisa)
            $table->unsignedBigInteger('total_revenue')->default(0);
            $table->unsignedBigInteger('subscription_revenue')->default(0);
            $table->unsignedBigInteger('order_revenue')->default(0);
            $table->unsignedBigInteger('recruitment_revenue')->default(0);
            $table->unsignedBigInteger('other_revenue')->default(0);

            // Expenses (in paisa)
            $table->unsignedBigInteger('total_expenses')->default(0);
            $table->unsignedBigInteger('commission_paid')->default(0);
            $table->unsignedBigInteger('withdrawal_processed')->default(0);
            $table->unsignedBigInteger('refunds')->default(0);
            $table->unsignedBigInteger('admin_profit_shared')->default(0);

            // Profit (in paisa)
            $table->bigInteger('net_profit')->default(0);
            $table->bigInteger('retained_profit')->default(0);

            // Users
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('active_members')->default(0);
            $table->unsignedInteger('churned_users')->default(0);

            // Transactions
            $table->unsignedInteger('total_transactions')->default(0);
            $table->unsignedBigInteger('total_volume')->default(0);

            // MLM specific
            $table->unsignedInteger('new_subscriptions')->default(0);
            $table->unsignedInteger('renewals')->default(0);
            $table->unsignedInteger('upgrades')->default(0);

            // Metadata
            $table->json('breakdown')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_snapshots');
    }
};
```

---

## Part 3: Universal Activity Logging System

### 3.1 Activity Logs Table

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();

            // WHO performed the action (polymorphic)
            $table->nullableMorphs('causer'); // Admin, User, System

            // WHAT was affected (polymorphic)
            $table->nullableMorphs('subject'); // User, Order, Wallet, etc.

            // Action details
            $table->string('action', 50); // created, updated, deleted, etc.
            $table->string('description');
            $table->string('log_name', 50)->default('default');

            // Data changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('properties')->nullable(); // Additional context

            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();

            // Environment
            $table->string('environment', 20)->default('production');
            $table->string('batch_uuid', 36)->nullable();

            $table->timestamps();

            // Indexes for fast queries
            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['action', 'created_at']);
            $table->index('log_name');
            $table->index('batch_uuid');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
```

### 3.2 ActivityLogService

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

final class ActivityLogService
{
    private ?Model $causer = null;
    private ?Model $subject = null;
    private string $logName = 'default';
    private array $properties = [];
    private ?string $batchUuid = null;

    public static function make(): self
    {
        return new self();
    }

    public function causedBy(?Model $causer): self
    {
        $this->causer = $causer;
        return $this;
    }

    public function performedOn(Model $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function useLog(string $logName): self
    {
        $this->logName = $logName;
        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->properties = array_merge($this->properties, $properties);
        return $this;
    }

    public function inBatch(?string $batchUuid = null): self
    {
        $this->batchUuid = $batchUuid ?? Str::uuid()->toString();
        return $this;
    }

    public function log(string $action, string $description, array $oldValues = [], array $newValues = []): ActivityLog
    {
        $causer = $this->causer ?? $this->resolveAuthenticatedUser();

        return ActivityLog::create([
            'uuid' => Str::uuid()->toString(),
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'subject_type' => $this->subject?->getMorphClass(),
            'subject_id' => $this->subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'log_name' => $this->logName,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'properties' => empty($this->properties) ? null : $this->properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'environment' => app()->environment(),
            'batch_uuid' => $this->batchUuid,
        ]);
    }

    public function logCreated(Model $model, ?string $description = null): ActivityLog
    {
        return $this->performedOn($model)->log(
            'created',
            $description ?? class_basename($model) . ' was created',
            [],
            $this->getLoggableAttributes($model)
        );
    }

    public function logUpdated(Model $model, array $oldValues, ?string $description = null): ActivityLog
    {
        $changes = $model->getChanges();

        return $this->performedOn($model)->log(
            'updated',
            $description ?? class_basename($model) . ' was updated',
            array_intersect_key($oldValues, $changes),
            array_intersect_key($model->getAttributes(), $changes)
        );
    }

    public function logDeleted(Model $model, ?string $description = null): ActivityLog
    {
        return $this->performedOn($model)->log(
            'deleted',
            $description ?? class_basename($model) . ' was deleted',
            $this->getLoggableAttributes($model),
            []
        );
    }

    public function logCustom(string $action, string $description): ActivityLog
    {
        return $this->log($action, $description);
    }

    private function resolveAuthenticatedUser(): ?Model
    {
        // Check admin guard first
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }

        // Then check web/api guard
        if (Auth::check()) {
            return Auth::user();
        }

        return null;
    }

    private function getLoggableAttributes(Model $model): array
    {
        $attributes = $model->getAttributes();

        // Remove sensitive fields
        $hidden = array_merge(
            $model->getHidden(),
            ['password', 'remember_token', 'two_factor_secret']
        );

        return array_diff_key($attributes, array_flip($hidden));
    }
}
```

### 3.3 LogsActivity Trait

```php
<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static array $oldAttributesForLogging = [];

    protected static function bootLogsActivity(): void
    {
        static::creating(function (Model $model) {
            // Store for potential use
        });

        static::created(function (Model $model) {
            if ($model->shouldLogActivity('created')) {
                ActivityLogService::make()
                    ->useLog($model->getActivityLogName())
                    ->logCreated($model);
            }
        });

        static::updating(function (Model $model) {
            // Store old values before update
            static::$oldAttributesForLogging[$model->getKey()] = $model->getOriginal();
        });

        static::updated(function (Model $model) {
            if ($model->shouldLogActivity('updated') && $model->wasChanged()) {
                $oldValues = static::$oldAttributesForLogging[$model->getKey()] ?? [];

                ActivityLogService::make()
                    ->useLog($model->getActivityLogName())
                    ->logUpdated($model, $oldValues);

                unset(static::$oldAttributesForLogging[$model->getKey()]);
            }
        });

        static::deleting(function (Model $model) {
            if ($model->shouldLogActivity('deleted')) {
                ActivityLogService::make()
                    ->useLog($model->getActivityLogName())
                    ->logDeleted($model);
            }
        });
    }

    public function shouldLogActivity(string $action): bool
    {
        // Override in model to customize
        $except = $this->activityLogExcept ?? [];
        return !in_array($action, $except);
    }

    public function getActivityLogName(): string
    {
        // Override in model to customize
        return $this->activityLogName ?? 'default';
    }

    public function activities()
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'subject');
    }

    public function causedActivities()
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'causer');
    }
}
```

### 3.4 ActivityLog Model

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'uuid',
        'causer_type',
        'causer_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'log_name',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'environment',
        'batch_uuid',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'properties' => 'array',
        ];
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForSubject($query, Model $subject)
    {
        return $query->where('subject_type', $subject->getMorphClass())
                     ->where('subject_id', $subject->getKey());
    }

    public function scopeCausedBy($query, Model $causer)
    {
        return $query->where('causer_type', $causer->getMorphClass())
                     ->where('causer_id', $causer->getKey());
    }

    public function scopeInLog($query, string $logName)
    {
        return $query->where('log_name', $logName);
    }

    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeInBatch($query, string $batchUuid)
    {
        return $query->where('batch_uuid', $batchUuid);
    }
}
```

---

## Part 4: Admin Model

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AdminStatusCast;
use App\Casts\AdminTypeCast;
use App\Traits\LogsActivity;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $guard = 'admin';

    protected string $activityLogName = 'admin';

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
            if (!$admin->uuid) {
                $admin->uuid = Str::uuid()->toString();
            }

            // Set level from type
            if ($admin->type) {
                $admin->level = $admin->type->getLevel();
            }

            // Set default profit share from type
            if ($admin->type && !$admin->profit_share_percent) {
                $admin->profit_share_percent = $admin->type->getDefaultProfitSharePercent();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Admin::class, 'created_by_admin_id');
    }

    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }

    public function profitDistributions(): HasMany
    {
        return $this->hasMany(AdminProfitDistribution::class);
    }

    // ========================================
    // Filament Methods
    // ========================================

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === AdminStatusCast::Active;
    }

    // ========================================
    // Hierarchy Methods
    // ========================================

    public function isSuperAdmin(): bool
    {
        return $this->type === AdminTypeCast::SuperAdmin;
    }

    public function canCreateAdminOfType(AdminTypeCast $type): bool
    {
        $canCreate = $this->type->canCreate();

        if (!$canCreate) {
            return false;
        }

        return $type->getLevel() >= $canCreate->getLevel();
    }

    public function getVisibleAdminTypes(): array
    {
        return $this->type->getVisibleTypes();
    }

    // ========================================
    // Profit Share Methods
    // ========================================

    public function getProfitSharePercent(): float
    {
        // Custom override takes precedence
        if ($this->profit_share_percent > 0) {
            return (float) $this->profit_share_percent;
        }

        // Fall back to type default
        return $this->type->getDefaultProfitSharePercent();
    }

    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet ?? $this->wallet()->create([
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
```

---

## Part 5: Profit Distribution Job

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Casts\AdminTypeCast;
use App\Models\Admin;
use App\Models\AdminProfitDistribution;
use App\Services\ActivityLogService;
use App\Services\MoneyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessMonthlyAdminProfitSharing implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $period,
        private readonly int $companyProfitPaisa,
    ) {}

    public function handle(): void
    {
        $superAdmin = Admin::where('type', AdminTypeCast::SuperAdmin)->firstOrFail();
        $sourceWallet = $superAdmin->getOrCreateWallet();

        $totalDistribution = $this->calculateTotalDistribution();

        if (!$sourceWallet->hasSufficientBalance($totalDistribution)) {
            ActivityLogService::make()
                ->causedBy($superAdmin)
                ->useLog('profit_distribution')
                ->logCustom('distribution_failed', "Insufficient balance for {$this->period} distribution");
            return;
        }

        $admins = Admin::query()
            ->where('type', '!=', AdminTypeCast::SuperAdmin)
            ->where('status', 'active')
            ->where('profit_share_active', true)
            ->with('wallet')
            ->get();

        $batchUuid = Str::uuid()->toString();

        DB::transaction(function () use ($admins, $sourceWallet, $batchUuid, $superAdmin) {
            foreach ($admins as $admin) {
                $this->processAdminShare($admin, $sourceWallet, $batchUuid);
            }

            ActivityLogService::make()
                ->causedBy($superAdmin)
                ->useLog('profit_distribution')
                ->inBatch($batchUuid)
                ->withProperties([
                    'period' => $this->period,
                    'company_profit' => $this->companyProfitPaisa,
                    'admins_count' => $admins->count(),
                ])
                ->logCustom('distribution_completed', "Profit distribution completed for {$this->period}");
        });
    }

    private function processAdminShare(Admin $admin, $sourceWallet, string $batchUuid): void
    {
        $sharePercent = $admin->getProfitSharePercent();
        $shareAmount = (int) round($this->companyProfitPaisa * ($sharePercent / 100));

        if ($shareAmount <= 0) {
            return;
        }

        $destWallet = $admin->getOrCreateWallet();

        $distribution = AdminProfitDistribution::create([
            'uuid' => Str::uuid()->toString(),
            'admin_id' => $admin->id,
            'source_wallet_id' => $sourceWallet->id,
            'destination_wallet_id' => $destWallet->id,
            'period' => $this->period,
            'company_profit' => $this->companyProfitPaisa,
            'share_percent' => $sharePercent,
            'amount' => $shareAmount,
            'status' => 'pending',
        ]);

        // Transfer funds
        $sourceWallet->decrement('balance', $shareAmount);
        $destWallet->increment('balance', $shareAmount);

        $distribution->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        ActivityLogService::make()
            ->performedOn($distribution)
            ->inBatch($batchUuid)
            ->useLog('profit_distribution')
            ->logCustom('share_distributed', sprintf(
                'Distributed %s to %s (%s%%)',
                MoneyService::format($shareAmount),
                $admin->name,
                $sharePercent
            ));
    }

    private function calculateTotalDistribution(): int
    {
        $admins = Admin::query()
            ->where('type', '!=', AdminTypeCast::SuperAdmin)
            ->where('status', 'active')
            ->where('profit_share_active', true)
            ->get();

        $total = 0;
        foreach ($admins as $admin) {
            $percent = $admin->getProfitSharePercent();
            $total += (int) round($this->companyProfitPaisa * ($percent / 100));
        }

        return $total;
    }
}
```

---

## Part 6: Admin Seeder

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\AdminTypeCast;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SuperAdmin (Company Account) - UNIQUE
        $superAdmin = Admin::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Commerinity Pro',
                'password' => bcrypt('SuperAdmin@123'),
                'type' => AdminTypeCast::SuperAdmin,
                'level' => 0,
                'profit_share_percent' => 0,
                'locale' => 'en',
            ]
        );

        // Create SuperAdmin wallet (company fund)
        $superAdmin->wallet()->firstOrCreate([
            'balance' => 0,
            'currency' => 'INR',
            'status' => 'active',
        ]);

        // 2. CEO
        $ceo = Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Chief Executive',
                'password' => bcrypt('Admin@123'),
                'type' => AdminTypeCast::Ceo,
                'level' => 1,
                'created_by_admin_id' => $superAdmin->id,
                'profit_share_percent' => 15.00,
                'locale' => 'en',
            ]
        );

        // Create CEO wallet
        $ceo->wallet()->firstOrCreate([
            'balance' => 0,
            'currency' => 'INR',
            'status' => 'active',
        ]);
    }
}
```

---

## Part 7: Language Files

### lang/en/admin.php

```php
<?php

return [
    'types' => [
        'superadmin' => 'Super Admin',
        'ceo' => 'CEO',
        'director' => 'Director',
        'manager' => 'Manager',
        'lead' => 'Team Lead',
        'executive' => 'Executive',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ],

    'profit_share' => [
        'title' => 'Profit Sharing',
        'monthly_distribution' => 'Monthly Distribution',
        'share_percent' => 'Share Percentage',
        'amount_received' => 'Amount Received',
        'pending' => 'Pending',
        'processed' => 'Processed',
        'failed' => 'Failed',
    ],

    'dashboard' => [
        'welcome' => 'Welcome, :name',
        'total_business' => 'Total Business',
        'company_fund' => 'Company Fund',
        'my_earnings' => 'My Earnings',
        'team_performance' => 'Team Performance',
    ],

    'activity' => [
        'created' => ':model was created',
        'updated' => ':model was updated',
        'deleted' => ':model was deleted',
        'logged_in' => 'Logged in from :ip',
        'logged_out' => 'Logged out',
    ],
];
```

---

## Part 8: Implementation Order

### Phase 1: Foundation
1. Create `AdminTypeCast` enum
2. Create `AdminStatusCast` enum
3. Create `admins` migration
4. Create `Admin` model
5. Create `AdminSeeder`
6. Run migrations and seeders
7. Configure `admin` guard in `auth.php`
8. Update `AdminPanelProvider`

### Phase 2: Activity Logging
1. Create `activity_logs` migration
2. Create `ActivityLog` model
3. Create `ActivityLogService`
4. Create `LogsActivity` trait
5. Add trait to existing models (User, Transaction, etc.)

### Phase 3: Profit Distribution
1. Create `admin_profit_distributions` migration
2. Create `AdminProfitDistribution` model
3. Create `ProcessMonthlyAdminProfitSharing` job
4. Add to scheduler

### Phase 4: Business Snapshots
1. Create `business_snapshots` migration
2. Create `BusinessSnapshot` model
3. Create `GenerateBusinessSnapshot` job
4. Add to scheduler

### Phase 5: Filament Resources
1. Create `AdminResource` with hierarchy-aware CRUD
2. Create `AdminProfitDistributionResource`
3. Create `ActivityLogResource`
4. Create dashboard widgets

### Phase 6: Security
1. Add 2FA support
2. Add session management
3. Add IP whitelisting (optional)
4. Add rate limiting for admin actions

---

## Part 9: Visibility Rules Matrix

| Viewer | Can See Stats Of |
|--------|------------------|
| SUPERADMIN | All admins, all stats |
| CEO | Directors, Managers, Leads, Executives |
| DIRECTOR | Managers, Leads, Executives |
| MANAGER | Leads, Executives |
| LEAD | Executives only |
| EXECUTIVE | Own stats only |

### Global Stats (Visible to All Admins)
- Total business volume
- Total company profit
- Total users
- Total transactions
- Monthly growth %

### Restricted Stats (Upper Admins Only)
- Individual admin profit shares
- Upper admin wallet balances
- Upper admin performance metrics

---

## Part 10: Security Considerations

1. **SuperAdmin Protection**
   - Cannot be deleted
   - Cannot change type
   - Only one instance allowed
   - Email cannot be changed after creation

2. **Hierarchy Enforcement**
   - Admin can only create types below their level
   - Admin cannot edit/delete admins above their level
   - Profit share % changes logged

3. **Wallet Security**
   - All transfers logged as transactions
   - SuperAdmin wallet is "company fund"
   - Profit distributions create audit trail

4. **Activity Logging**
   - Every action logged with context
   - IP addresses tracked
   - Changes diffed and stored

---

**END OF ADMIN COMPLETE SYSTEM PLAN**
