# Membership & Subscription System - Complete Plan
**Version**: 1.0
**Date**: 2025-12-10
**Priority**: HIGH
**Phase**: Planning → Implementation Ready

---

## Executive Summary

**What**: Multi-tier membership subscription system with stage-based progression (Bronze → Silver → Gold → Platinum → Diamond), level-based tasks, and feature unlocking.

**Why**: Core MLM business model - users subscribe to stages/levels to unlock earning potential, features, and commission rates.

**Reference**: Old commerinity has complete implementation - we'll build better with current tech stack.

---

## System Architecture

### Components
1. **Stages** - Major tiers (Bronze, Silver, Gold, Platinum, Diamond)
2. **Levels** - Sub-levels within each stage (e.g., Bronze L1, L2, L3)
3. **Subscriptions** - User's active membership with stage/level
4. **Tasks** - Requirements to progress to next level
5. **Benefits** - Features/perks unlocked at each stage/level
6. **Progression** - System to track and upgrade users

### Flow
```
User registers (Free tier)
→ Browse stages/levels
→ Subscribe to stage/level (payment)
→ Receive tasks
→ Complete tasks
→ Unlock progression
→ Upgrade to next level
→ Unlock new benefits/features
→ Higher commission rates
```

---

## Database Schema

### 1. `stages` Table
```php
Schema::create('stages', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Bronze, Silver, Gold, Platinum, Diamond
    $table->string('slug')->unique(); // bronze, silver, gold
    $table->text('description')->nullable();
    $table->string('color_code')->nullable(); // #CD7F32, #C0C0C0, #FFD700
    $table->string('icon')->nullable(); // badge icon
    $table->integer('order')->default(0); // Display order
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 2. `stage_levels` Table
```php
Schema::create('stage_levels', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
    $table->integer('level_number'); // 1, 2, 3...
    $table->string('name'); // "Bronze Level 1"
    $table->text('description')->nullable();

    // Pricing
    $table->decimal('base_price', 10, 2); // Subscription cost
    $table->integer('duration_days')->default(365); // Validity period

    // Commission rates (JSON)
    $table->json('commission_rates')->nullable(); // {affiliate: 5, team: [3,2,1]}

    // Requirements to reach this level
    $table->json('requirements')->nullable(); // {prev_level: 1, tasks_complete: true}

    // Benefits unlocked (JSON)
    $table->json('benefits')->nullable(); // {max_team_size: 100, features: [...]}

    $table->boolean('is_active')->default(true);
    $table->integer('order')->default(0);
    $table->timestamps();

    $table->unique(['stage_id', 'level_number']);
});
```

### 3. `user_subscriptions` Table
```php
Schema::create('user_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('stage_level_id')->constrained()->cascadeOnDelete();

    // Subscription details
    $table->decimal('amount_paid', 10, 2);
    $table->date('started_at');
    $table->date('expires_at');
    $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');

    // Payment reference
    $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();

    // Auto-renewal
    $table->boolean('auto_renew')->default(false);

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index('expires_at');
});
```

### 4. `level_tasks` Table
```php
Schema::create('level_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stage_level_id')->constrained()->cascadeOnDelete();

    $table->string('name'); // "Recruit 5 members"
    $table->text('description')->nullable();
    $table->enum('type', ['recruitment', 'sales', 'team_volume', 'custom']);

    // Task target
    $table->json('target')->nullable(); // {type: 'recruitment', count: 5}
    $table->integer('points')->default(0); // Points awarded

    // Reward
    $table->json('reward')->nullable(); // {type: 'bonus', amount: 100}

    $table->boolean('is_required')->default(true); // Must complete to progress
    $table->integer('order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 5. `user_task_progress` Table
```php
Schema::create('user_task_progress', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('level_task_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subscription_id')->constrained('user_subscriptions')->cascadeOnDelete();

    // Progress tracking
    $table->integer('current_value')->default(0); // e.g., recruited 3 out of 5
    $table->integer('target_value'); // 5
    $table->decimal('progress_percentage', 5, 2)->default(0); // 60%

    $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
    $table->timestamp('completed_at')->nullable();

    $table->timestamps();

    $table->unique(['user_id', 'level_task_id', 'subscription_id']);
    $table->index(['user_id', 'status']);
});
```

### 6. `level_upgrades` Table (History)
```php
Schema::create('level_upgrades', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('from_level_id')->constrained('stage_levels')->cascadeOnDelete();
    $table->foreignId('to_level_id')->constrained('stage_levels')->cascadeOnDelete();

    $table->decimal('upgrade_fee', 10, 2)->default(0); // If applicable
    $table->text('notes')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

    $table->timestamp('requested_at');
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'status']);
});
```

---

## Models (Laravel 12 + PHP 8.3.22)

### Stage.php
```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Factories\HasFactory, Relations\HasMany};

final class Stage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'color_code', 'icon', 'order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function levels(): HasMany
    {
        return $this->hasMany(StageLevel::class)->orderBy('level_number');
    }
}
```

### StageLevel.php
```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Factories\HasFactory, Relations\BelongsTo, Relations\HasMany};

final class StageLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id', 'level_number', 'name', 'description', 'base_price',
        'duration_days', 'commission_rates', 'requirements', 'benefits',
        'is_active', 'order'
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'duration_days' => 'integer',
            'commission_rates' => 'array',
            'requirements' => 'array',
            'benefits' => 'array',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LevelTask::class)->orderBy('order');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }
}
```

### UserSubscription.php
```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Factories\HasFactory, Relations\BelongsTo, Relations\HasMany, SoftDeletes};

final class UserSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'stage_level_id', 'amount_paid',
        'started_at', 'expires_at', 'status', 'payment_id', 'auto_renew'
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'started_at' => 'date',
            'expires_at' => 'date',
            'auto_renew' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stageLevel(): BelongsTo
    {
        return $this->belongsTo(StageLevel::class);
    }

    public function taskProgress(): HasMany
    {
        return $this->hasMany(UserTaskProgress::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
```

### LevelTask.php
```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Factories\HasFactory, Relations\BelongsTo, Relations\HasMany};

final class LevelTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_level_id', 'name', 'description', 'type',
        'target', 'points', 'reward', 'is_required', 'order', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'target' => 'array',
            'points' => 'integer',
            'reward' => 'array',
            'is_required' => 'boolean',
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function stageLevel(): BelongsTo
    {
        return $this->belongsTo(StageLevel::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserTaskProgress::class);
    }
}
```

### UserTaskProgress.php
```php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Factories\HasFactory, Relations\BelongsTo};

final class UserTaskProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'level_task_id', 'subscription_id',
        'current_value', 'target_value', 'progress_percentage',
        'status', 'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
            'target_value' => 'integer',
            'progress_percentage' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(LevelTask::class, 'level_task_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function updateProgress(int $value): void
    {
        $this->current_value = $value;
        $this->progress_percentage = ($value / $this->target_value) * 100;

        if ($value >= $this->target_value) {
            $this->status = 'completed';
            $this->completed_at = now();
        } else {
            $this->status = 'in_progress';
        }

        $this->save();
    }
}
```

---

## API Endpoints

### Public (Guest)
```
GET    /api/stages                      # List all active stages
GET    /api/stages/{slug}               # Stage details with levels
GET    /api/stage-levels/{id}           # Level details with tasks/benefits
```

### Authenticated
```
GET    /api/my/subscription              # Current subscription
POST   /api/subscriptions/subscribe      # Subscribe to stage/level
POST   /api/subscriptions/cancel         # Cancel subscription
GET    /api/my/tasks                     # Current level tasks
GET    /api/my/tasks/{id}/progress       # Task progress
POST   /api/my/upgrade/request           # Request level upgrade
GET    /api/my/upgrade/eligibility       # Check if eligible for upgrade
```

### Admin (Filament)
```
CRUD /admin/stages                       # Manage stages
CRUD /admin/stage-levels                 # Manage levels
CRUD /admin/level-tasks                  # Manage tasks
VIEW /admin/subscriptions                # View all subscriptions
VIEW /admin/upgrades                     # Approve/reject upgrades
```

---

## Services

### SubscriptionService.php
```php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Models\{User, StageLevel, UserSubscription, UserTaskProgress};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

final class SubscriptionService
{
    public function __construct(
        private readonly TaskProgressService $taskService
    ) {}

    public function subscribe(User $user, StageLevel $level, float $amount): UserSubscription
    {
        return DB::transaction(function () use ($user, $level, $amount) {
            // Cancel existing subscriptions
            $this->cancelActiveSubscriptions($user);

            // Create new subscription
            $subscription = UserSubscription::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'stage_level_id' => $level->id,
                'amount_paid' => $amount,
                'started_at' => Carbon::today(),
                'expires_at' => Carbon::today()->addDays($level->duration_days),
                'status' => 'active',
            ]);

            // Initialize task progress
            $this->taskService->initializeTasks($subscription);

            return $subscription;
        });
    }

    public function checkEligibilityForUpgrade(UserSubscription $subscription): array
    {
        $currentLevel = $subscription->stageLevel;
        $nextLevel = $this->getNextLevel($currentLevel);

        if (!$nextLevel) {
            return ['eligible' => false, 'reason' => 'Already at max level'];
        }

        // Check task completion
        $tasksComplete = $this->taskService->areAllTasksComplete($subscription);

        if (!$tasksComplete) {
            return ['eligible' => false, 'reason' => 'Tasks not completed'];
        }

        return [
            'eligible' => true,
            'next_level' => $nextLevel,
            'upgrade_fee' => $nextLevel->base_price,
        ];
    }

    private function getNextLevel(StageLevel $current): ?StageLevel
    {
        // Same stage, next level
        $sameStageNext = StageLevel::where('stage_id', $current->stage_id)
            ->where('level_number', $current->level_number + 1)
            ->first();

        if ($sameStageNext) {
            return $sameStageNext;
        }

        // Next stage, level 1
        $nextStage = Stage::where('order', '>', $current->stage->order)
            ->orderBy('order')
            ->first();

        if ($nextStage) {
            return $nextStage->levels()->where('level_number', 1)->first();
        }

        return null;
    }

    private function cancelActiveSubscriptions(User $user): void
    {
        $user->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);
    }
}
```

### TaskProgressService.php
```php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Models\{UserSubscription, UserTaskProgress, LevelTask};

final class TaskProgressService
{
    public function initializeTasks(UserSubscription $subscription): void
    {
        $tasks = $subscription->stageLevel->tasks()->where('is_active', true)->get();

        foreach ($tasks as $task) {
            UserTaskProgress::create([
                'user_id' => $subscription->user_id,
                'level_task_id' => $task->id,
                'subscription_id' => $subscription->id,
                'current_value' => 0,
                'target_value' => $task->target['count'] ?? 1,
                'progress_percentage' => 0,
                'status' => 'pending',
            ]);
        }
    }

    public function updateTaskProgress(UserTaskProgress $progress, int $value): void
    {
        $progress->updateProgress($value);
    }

    public function areAllTasksComplete(UserSubscription $subscription): bool
    {
        $requiredTasks = $subscription->taskProgress()
            ->whereHas('task', fn($q) => $q->where('is_required', true))
            ->get();

        return $requiredTasks->every(fn($p) => $p->status === 'completed');
    }
}
```

---

## Filament Resources

### StageResource.php (Admin Panel)
```php
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StageResource\Pages;
use App\Models\Stage;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

final class StageResource extends Resource
{
    protected static ?string $model = Stage::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Membership';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(),
            Forms\Components\Textarea::make('description'),
            Forms\Components\ColorPicker::make('color_code'),
            Forms\Components\TextInput::make('icon'),
            Forms\Components\TextInput::make('order')->numeric(),
            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\ColorColumn::make('color_code'),
                Tables\Columns\TextColumn::make('order')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'edit' => Pages\EditStage::route('/{record}/edit'),
        ];
    }
}
```

---

## Frontend (Nuxt 4 + Nuxt UI v4)

### `/pages/membership/index.vue`
```vue
<script setup lang="ts">
const config = useRuntimeConfig()

// Fetch stages
const { data: stages, pending } = await useAsyncData('stages', async () => {
  return await useSanctumFetch(`${config.public.apiBase}/api/stages`)
})
</script>

<template>
  <UContainer>
    <h1 class="text-3xl font-bold mb-6">Choose Your Membership</h1>

    <div v-if="pending">Loading...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <UCard v-for="stage in stages" :key="stage.id">
        <template #header>
          <h3 class="text-xl font-semibold" :style="{color: stage.color_code}">
            {{ stage.name }}
          </h3>
        </template>

        <p class="text-gray-600 mb-4">{{ stage.description }}</p>

        <div class="space-y-2">
          <UButton
            v-for="level in stage.levels"
            :key="level.id"
            block
            :to="`/membership/level/${level.id}`"
          >
            {{ level.name }} - ${{ level.base_price }}
          </UButton>
        </div>
      </UCard>
    </div>
  </UContainer>
</template>
```

### `/pages/membership/level/[id].vue`
```vue
<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()

const { data: level } = await useAsyncData(`level-${route.params.id}`, async () => {
  return await useSanctumFetch(
    `${config.public.apiBase}/api/stage-levels/${route.params.id}`
  )
})

async function subscribe() {
  await useSanctumFetch(`${config.public.apiBase}/api/subscriptions/subscribe`, {
    method: 'POST',
    body: { stage_level_id: level.value.id }
  })
  navigateTo('/dashboard')
}
</script>

<template>
  <UContainer>
    <UCard>
      <h2 class="text-2xl font-bold mb-4">{{ level.name }}</h2>
      <p class="mb-4">{{ level.description }}</p>

      <div class="mb-6">
        <h3 class="font-semibold mb-2">Benefits:</h3>
        <ul>
          <li v-for="(benefit, i) in level.benefits" :key="i">
            {{ benefit }}
          </li>
        </ul>
      </div>

      <div class="mb-6">
        <h3 class="font-semibold mb-2">Tasks to Complete:</h3>
        <ul>
          <li v-for="task in level.tasks" :key="task.id">
            {{ task.name }} - {{ task.points }} points
          </li>
        </ul>
      </div>

      <UButton @click="subscribe" size="lg" block>
        Subscribe - ${{ level.base_price }}
      </UButton>
    </UCard>
  </UContainer>
</template>
```

---

## Testing (Pest v4)

### `tests/Feature/Membership/SubscriptionTest.php`
```php
declare(strict_types=1);

use App\Models\{User, Stage, StageLevel, UserSubscription};
use function Pest\Laravel\{actingAs, postJson, assertDatabaseHas};

it('allows user to subscribe to a stage level', function () {
    $user = User::factory()->create();
    $level = StageLevel::factory()->create(['base_price' => 99.00]);

    actingAs($user)->postJson('/api/subscriptions/subscribe', [
        'stage_level_id' => $level->id,
    ])->assertOk();

    assertDatabaseHas('user_subscriptions', [
        'user_id' => $user->id,
        'stage_level_id' => $level->id,
        'status' => 'active',
    ]);
});

it('initializes tasks when subscribing', function () {
    $user = User::factory()->create();
    $level = StageLevel::factory()->create();
    $tasks = LevelTask::factory(3)->create(['stage_level_id' => $level->id]);

    actingAs($user)->postJson('/api/subscriptions/subscribe', [
        'stage_level_id' => $level->id,
    ]);

    expect(UserTaskProgress::where('user_id', $user->id)->count())->toBe(3);
});

it('checks upgrade eligibility correctly', function () {
    $user = User::factory()->create();
    $level = StageLevel::factory()->create(['level_number' => 1]);
    $subscription = UserSubscription::factory()->create([
        'user_id' => $user->id,
        'stage_level_id' => $level->id,
    ]);

    // Complete all tasks
    $subscription->taskProgress->each->update(['status' => 'completed']);

    $response = actingAs($user)->getJson('/api/my/upgrade/eligibility')
        ->assertOk();

    expect($response->json('eligible'))->toBeTrue();
});
```

---

## Implementation Checklist

### Phase 1: Database (Day 1)
- [ ] Create migrations (6 tables)
- [ ] Create models (6 models with relationships)
- [ ] Create factories (6 factories)
- [ ] Create seeders (sample data)
- [ ] Run migrations + seed

### Phase 2: Backend API (Day 2-3)
- [ ] Create SubscriptionService
- [ ] Create TaskProgressService
- [ ] Create API controllers (MembershipController, SubscriptionController)
- [ ] Create Form Requests (SubscribeRequest, UpgradeRequest)
- [ ] Add routes
- [ ] Write Pest tests (15+ tests)

### Phase 3: Filament Admin (Day 4)
- [ ] Create StageResource
- [ ] Create StageLevelResource
- [ ] Create LevelTaskResource
- [ ] Create UserSubscriptionResource (view-only)
- [ ] Create custom actions (approve upgrade)

### Phase 4: Frontend (Day 5-6)
- [ ] Create membership pages (index, level detail)
- [ ] Create dashboard widgets (current subscription, tasks)
- [ ] Create progress tracking UI
- [ ] Create upgrade flow
- [ ] Test full user journey

### Phase 5: Integration (Day 7)
- [ ] Connect to payment system
- [ ] Connect to commission system (update rates based on level)
- [ ] Connect to MLM tree (restrict team size by level)
- [ ] Test end-to-end flows

---

## Token Optimization Strategy

**This Plan Size**: ~18KB

**Implementation Without This Plan**:
- Read old commerinity code (50KB)
- Read commission system docs (12KB)
- Read MLM docs (15KB)
- Research best practices (30KB)
- Trial and error (50KB+)
- **Total**: 157KB+ tokens

**Implementation With This Plan**:
- Read this plan (18KB)
- Reference specific sections as needed (2-3KB per section)
- Implement directly from templates
- **Total**: ~30KB tokens

**Savings**: 127KB tokens per implementation (80% reduction)

---

## Next Steps

1. **User approval**: Review plan, confirm business logic matches expectations
2. **Implementation**: Start Phase 1 (database) tomorrow
3. **Testing**: Test each phase before proceeding
4. **Documentation**: Update `.claude/PROJECT_SNAPSHOT.json` with progress

---

**Status**: ✅ Planning Complete - Ready for Implementation
**Expected Duration**: 7 days (phased approach)
**Token Budget**: ~150KB total (vs 500KB+ without plan)
