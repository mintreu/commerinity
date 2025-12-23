# Admin Hierarchy & Profit Sharing System

## Overview

A hierarchical admin system where SuperAdmin (company) distributes monthly profit shares to admin team based on their role-based percentage.

---

## 1. Admin Type Hierarchy (5 Levels)

```
SUPERADMIN (Company - Single Instance)
    ├── CEO (1-2, can create below)
    │   ├── DIRECTOR (can create below)
    │   │   ├── MANAGER (can create below)
    │   │   │   ├── LEAD (can create below)
    │   │   │   │   └── EXECUTIVE (lowest admin level)
```

### AdminTypeCast Enum

| Type | Level | Profit Share % | Can Create | Description |
|------|-------|----------------|------------|-------------|
| SUPERADMIN | 0 | 100% (source) | CEO | Company's main account, cannot be duplicated |
| CEO | 1 | 15% | DIRECTOR | Chief Executive, top management |
| DIRECTOR | 2 | 10% | MANAGER | Department heads |
| MANAGER | 3 | 5% | LEAD | Team managers |
| LEAD | 4 | 3% | EXECUTIVE | Team leads |
| EXECUTIVE | 5 | 1% | None | Operations staff |

---

## 2. Database Design

### admins Table Migration

```php
Schema::create('admins', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 24)->unique();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('mobile', 15)->nullable()->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('type')->default('executive'); // AdminTypeCast
    $table->string('status')->default('active');  // AdminStatusCast
    
    // Hierarchy
    $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
    $table->unsignedTinyInteger('level')->default(5); // 0=SuperAdmin, 5=Executive
    
    // Profit sharing
    $table->decimal('profit_share_percent', 5, 2)->default(0.00); // Custom override
    $table->boolean('profit_share_active')->default(true);
    
    // Settings
    $table->string('locale', 5)->default('en'); // en, bn, hi
    $table->json('preferences')->nullable();
    
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

### Wallet Already Polymorphic ✅

The existing Wallet model uses `walletable_type` and `walletable_id`, so:
- Admin can have wallet via: `walletable_type = 'App\Models\Admin'`
- No additional migration needed for wallet relationship

### admin_profit_distributions Table

```php
Schema::create('admin_profit_distributions', function (Blueprint $table) {
    $table->id();
    $table->string('uuid', 24)->unique();
    $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
    $table->foreignId('source_wallet_id')->constrained('wallets'); // SuperAdmin's wallet
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
    
    $table->unique(['admin_id', 'period']); // One distribution per admin per month
});
```

---

## 3. Models

### Admin Model

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AdminStatusCast;
use App\Casts\AdminTypeCast;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable implements FilamentUser
{
    use SoftDeletes;
    
    protected $guard = 'admin';
    
    // Relationships
    public function creator(): BelongsTo;      // Who created this admin
    public function subordinates(): HasMany;    // Admins created by this admin
    public function wallet(): MorphOne;         // Polymorphic wallet
    public function profitDistributions(): HasMany;
    
    // Methods
    public function canAccessPanel(Panel $panel): bool;
    public function canCreateAdminOfType(AdminTypeCast $type): bool;
    public function getVisibleAdminLevels(): array; // For stats visibility
    public function isSuperAdmin(): bool;
    public function getProfitSharePercent(): float; // From type or custom override
}
```

---

## 4. AdminTypeCast Enum

```php
<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AdminTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SUPERADMIN = 'superadmin';
    case CEO = 'ceo';
    case DIRECTOR = 'director';
    case MANAGER = 'manager';
    case LEAD = 'lead';
    case EXECUTIVE = 'executive';
    
    public function getLabel(): string
    {
        return match ($this) {
            self::SUPERADMIN => __('admin.types.superadmin'),
            self::CEO => __('admin.types.ceo'),
            self::DIRECTOR => __('admin.types.director'),
            self::MANAGER => __('admin.types.manager'),
            self::LEAD => __('admin.types.lead'),
            self::EXECUTIVE => __('admin.types.executive'),
        };
    }
    
    public function getLevel(): int
    {
        return match ($this) {
            self::SUPERADMIN => 0,
            self::CEO => 1,
            self::DIRECTOR => 2,
            self::MANAGER => 3,
            self::LEAD => 4,
            self::EXECUTIVE => 5,
        };
    }
    
    public function getDefaultProfitSharePercent(): float
    {
        return match ($this) {
            self::SUPERADMIN => 0.00, // Source, not recipient
            self::CEO => 15.00,
            self::DIRECTOR => 10.00,
            self::MANAGER => 5.00,
            self::LEAD => 3.00,
            self::EXECUTIVE => 1.00,
        };
    }
    
    public function canCreate(): ?self
    {
        return match ($this) {
            self::SUPERADMIN => self::CEO,
            self::CEO => self::DIRECTOR,
            self::DIRECTOR => self::MANAGER,
            self::MANAGER => self::LEAD,
            self::LEAD => self::EXECUTIVE,
            self::EXECUTIVE => null, // Cannot create admins
        };
    }
    
    /**
     * Get types this admin can view stats of
     * Each admin sees their level and below, but NOT above
     */
    public function getVisibleTypes(): array
    {
        $level = $this->getLevel();
        return collect(self::cases())
            ->filter(fn($type) => $type->getLevel() >= $level)
            ->values()
            ->all();
    }
    
    public function getColor(): string { /* ... */ }
    public function getIcon(): ?string { /* ... */ }
}
```

---

## 5. Monthly Profit Distribution System

### ProcessMonthlyAdminProfitSharing Job

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Admin;
use App\Models\AdminProfitDistribution;
use App\Models\Wallet;
use App\Casts\AdminTypeCast;
use App\Services\MoneyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessMonthlyAdminProfitSharing implements ShouldQueue
{
    use Queueable;
    
    public function __construct(
        private readonly string $period, // YYYY-MM
        private readonly int $companyProfitPaisa,
    ) {}
    
    public function handle(): void
    {
        // 1. Get SuperAdmin's wallet (source)
        $superAdmin = Admin::where('type', AdminTypeCast::SUPERADMIN)->firstOrFail();
        $sourceWallet = $superAdmin->wallet;
        
        if (!$sourceWallet || !$sourceWallet->hasSufficientBalance($this->calculateTotalDistribution())) {
            // Log insufficient funds, notify admins
            return;
        }
        
        // 2. Get all eligible admins (not superadmin, active, profit_share_active)
        $admins = Admin::where('type', '!=', AdminTypeCast::SUPERADMIN)
            ->where('status', 'active')
            ->where('profit_share_active', true)
            ->with('wallet')
            ->get();
        
        // 3. Process each admin
        foreach ($admins as $admin) {
            $this->processAdminShare($admin, $sourceWallet);
        }
    }
    
    private function processAdminShare(Admin $admin, Wallet $sourceWallet): void
    {
        $sharePercent = $admin->getProfitSharePercent();
        $shareAmount = (int) round($this->companyProfitPaisa * ($sharePercent / 100));
        
        if ($shareAmount <= 0) return;
        
        // Create distribution record
        $distribution = AdminProfitDistribution::create([
            'admin_id' => $admin->id,
            'source_wallet_id' => $sourceWallet->id,
            'destination_wallet_id' => $admin->wallet->id,
            'period' => $this->period,
            'company_profit' => $this->companyProfitPaisa,
            'share_percent' => $sharePercent,
            'amount' => $shareAmount,
            'status' => 'pending',
        ]);
        
        // Transfer funds
        // ... wallet transfer logic with transaction creation
    }
}
```

### Schedule in Console

```php
// routes/console.php or scheduler
Schedule::job(new ProcessMonthlyAdminProfitSharing(
    now()->subMonth()->format('Y-m'),
    config('admin.monthly_profit_source') // Or calculate from transactions
))->monthlyOn(1, '00:00'); // 1st of every month at midnight
```

---

## 6. Filament Integration

### AdminPanelProvider Updates

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()  // Keep login, NO registration
        ->authGuard('admin') // Separate guard for admins
        ->colors([
            'primary' => Color::Amber,
        ])
        // ...existing config...
}
```

### Dashboard Widgets

#### CompanyStatsWidget (For All Admins)
Shows: Total Business, Total Profit, Company Fund, Active Users

#### AdminTeamStatsWidget (Hierarchy-Aware)
Shows stats of admins at same level and below only

#### PersonalEarningsWidget
Shows: My Profit Share, Last Distribution, Wallet Balance

#### ProfitDistributionChartWidget
Monthly profit distribution trend using existing TrendServices

---

## 7. Visibility Rules

### Stats Visibility Matrix

| Viewer | Can See Stats Of |
|--------|------------------|
| SUPERADMIN | All admins, all stats |
| CEO | Directors, Managers, Leads, Executives |
| DIRECTOR | Managers, Leads, Executives |
| MANAGER | Leads, Executives |
| LEAD | Executives only |
| EXECUTIVE | Own stats only |

### Global Stats (Visible to All)
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

## 8. Seeder

### AdminSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Casts\AdminTypeCast;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SuperAdmin (Company Account) - UNIQUE
        $superAdmin = Admin::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Commerinity Pro',
                'password' => bcrypt('SuperAdmin@123'),
                'type' => AdminTypeCast::SUPERADMIN,
                'level' => 0,
                'profit_share_percent' => 0,
                'locale' => 'en',
            ]
        );
        
        // Create SuperAdmin wallet (company fund)
        $superAdmin->wallet()->firstOrCreate([
            'uuid' => 'WAL-COMPANY-MAIN',
            'balance' => 0,
            'currency' => 'INR',
            'status' => 'active',
        ]);
        
        // 2. CEO
        $ceo = Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Chief Executive',
                'password' => bcrypt('Admin@123'),
                'type' => AdminTypeCast::CEO,
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

## 9. Language Support Infrastructure

### Directory Structure

```
apiserver/lang/
├── en/
│   ├── admin.php       # Admin-specific translations
│   ├── auth.php        # Auth messages
│   ├── validation.php  # Validation messages
│   ├── notifications.php
│   ├── wallet.php
│   └── general.php     # Common UI strings
├── bn/                 # Bengali (future)
│   └── ... same files
└── hi/                 # Hindi (future)
    └── ... same files
```

### Example Translation File

```php
// lang/en/admin.php
return [
    'types' => [
        'superadmin' => 'Super Admin',
        'ceo' => 'CEO',
        'director' => 'Director',
        'manager' => 'Manager',
        'lead' => 'Team Lead',
        'executive' => 'Executive',
    ],
    'profit_share' => [
        'title' => 'Profit Sharing',
        'monthly_distribution' => 'Monthly Distribution',
        'share_percent' => 'Share Percentage',
        'amount_received' => 'Amount Received',
    ],
    'dashboard' => [
        'welcome' => 'Welcome, :name',
        'total_business' => 'Total Business',
        'company_fund' => 'Company Fund',
    ],
];
```

### Incremental Language Updates

When touching any file:
1. Replace hardcoded strings with `__('file.key')`
2. Add key to `lang/en/*.php`
3. Leave placeholder for `bn` and `hi`

Example:
```php
// Before
return 'Invalid credentials';

// After
return __('auth.failed');
```

---

## 10. Implementation Order

### Phase 1: Foundation (Day 1)
1. ✅ Create `AdminTypeCast` enum with hierarchy
2. ✅ Create `AdminStatusCast` enum
3. ✅ Create `admins` migration
4. ✅ Create `admin_profit_distributions` migration
5. ✅ Create `Admin` model with relationships
6. ✅ Create `AdminProfitDistribution` model

### Phase 2: Auth & Panel (Day 2)
1. Configure `admin` guard in `auth.php`
2. Update `AdminPanelProvider` for admin guard
3. Create `AdminSeeder` (SuperAdmin + CEO)
4. Run migrations & seeders
5. Test admin login

### Phase 3: Filament Resources (Day 3)
1. Create `AdminResource` with hierarchy-aware CRUD
2. Implement `canCreate()` based on admin type
3. Add profit share editing for SuperAdmin only
4. Create `AdminProfitDistributionResource`

### Phase 4: Widgets & Dashboard (Day 4)
1. `CompanyOverviewWidget` - global stats
2. `AdminTeamWidget` - hierarchy-aware team stats
3. `PersonalProfitWidget` - own earnings
4. `ProfitTrendChartWidget` - monthly trends

### Phase 5: Profit Sharing Job (Day 5)
1. Create `ProcessMonthlyAdminProfitSharing` job
2. Add to scheduler
3. Test distribution logic
4. Add notifications for distributions

### Phase 6: Language Infrastructure (Ongoing)
1. Create `lang/en/` directory structure
2. Start with admin translations
3. Incrementally update touched files
4. Add locale preference to Admin model

---

## 11. Security Considerations

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
   - Profit distributions require approval (optional)

---

## 12. Questions for Clarification

1. **Profit Source**: Should monthly profit be:
   - Manually entered by SuperAdmin?
   - Auto-calculated from transactions (commission earned)?
   - Set from configuration?

2. **Distribution Approval**: Should profit distributions:
   - Auto-process on 1st of month?
   - Require SuperAdmin approval?
   - Have a review period?

3. **Custom Share Override**: Can SuperAdmin set custom profit share % for specific admins (override default)?

4. **Admin Wallet Withdrawal**: Same flow as user wallet or separate bank accounts?

---

## Ready for Implementation

Once you confirm the plan and answer the questions above, I'll start with Phase 1: Creating the casts and migrations.
