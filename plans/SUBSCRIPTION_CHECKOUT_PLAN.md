# Subscription Checkout Implementation Plan

## 📋 Overview

Complete subscription checkout system with multiple payment scenarios, auto-placement logic, and MLM commission triggering.

**Created**: 2025-12-25
**Status**: Planning
**Priority**: CRITICAL (Launch Blocker)

---

## 🎯 Business Requirements

### Scenario 1: Self-Subscribe (Regular → Member)
**Flow**: Regular user clicks "Subscribe Now" → Checkout → Payment → Becomes Member
- **User Status**: Regular (type: REGULAR, no parent_id initially)
- **Payment Options**: Wallet OR Online Gateway (Cashfree/Razorpay)
- **Auto-Placement**: IF user has `parent_id` null → stays null (no MLM)
- **Auto-Placement**: IF user has `parent_id` → find available spot in tree
- **Level Assignment**: Assign first level of first stage (e.g., "Starter Bronze")
- **Commission Trigger**: YES (trigger upline commissions if in tree)

**5-Hand Limit Logic**:
- Parent has max 5 direct children
- If parent's 5 slots full → search children recursively
- Place in first available spot (breadth-first search)

### Scenario 2: Member Gifts Subscription to Promoter
**Flow**: Member pays for another user → Target becomes Promoter
- **Originator**: Member (stores in `originator_id/type`)
- **Payment**: From Member's wallet OR gateway
- **Target**: Regular user becomes PROMOTER (not MEMBER)
- **Auto-Placement**: Target gets placed under originator as parent
- **Level Assignment**: Assign promoter-appropriate level
- **Commission Trigger**: YES (commissions to originator's upline)

### Scenario 3: Advisor Gifts Subscription to Regular
**Flow**: Advisor pays from wallet → Regular becomes Member
- **Originator**: Advisor (stores in `originator_id/type`)
- **Payment**: ONLY from Advisor's wallet (advisors can't use gateway)
- **Target**: Regular user becomes MEMBER
- **Auto-Placement**: Place under advisor OR in advisor's team
- **Restrictions**: Advisor CANNOT make user a PROMOTER (only Member)
- **Commission Trigger**: YES

### Scenario 4: Admin Gifts Subscription
**Flow**: Admin pays from any source → Target gets subscription
- **Originator**: Admin user
- **Payment**: Wallet OR gateway
- **Target**: Can become Member OR Promoter (admin decides)
- **Auto-Placement**: Admin can specify parent_id OR auto-place
- **Commission Trigger**: YES

---

## 🗄️ Database Structure (Already Exists)

### user_subscriptions Table
```sql
user_id              (FK → users.id)
stage_id             (FK → stages.id)
level_id             (FK → levels.id)
current_level_id     (FK → levels.id) - Current achieved level
highest_level_id     (FK → levels.id) - Highest ever achieved
amount               (integer, paisa)
is_paid              (boolean)
paid_at              (timestamp)
transaction_id       (FK → transactions.id)
wallet_id            (FK → wallets.id) - If paid from wallet
starts_at            (timestamp)
expires_at           (timestamp)
status               (enum: pending, active, expired, cancelled)
originator_type      (string, nullable) - Polymorphic
originator_id        (bigint, nullable) - Who paid for this
previous_subscription_id (FK → user_subscriptions.id) - For renewals
```

### users Table
```sql
parent_id            (FK → users.id) - MLM parent
originator_id        (bigint, nullable) - Who recruited/paid
originator_type      (string, nullable) - Polymorphic
type                 (enum: REGULAR, MEMBER, PROMOTER, ADVISOR, MENTOR)
status               (enum: DRAFT, PENDING, ACTIVE, INACTIVE, SUSPENDED)
level_id             (FK → levels.id) - Current level
```

---

## 🏗️ Backend Architecture

### Services Needed

#### 1. SubscriptionCheckoutService ✨ NEW
**Location**: `app/Services/Membership/SubscriptionCheckoutService.php`

**Responsibilities**:
- Validate user eligibility (not already subscribed)
- Determine stage/level for user based on context
- Create pending subscription record
- Handle payment (wallet vs gateway)
- Return checkout URL or instant success

**Methods**:
```php
public function __construct(
    private readonly User $user,
    private readonly ?User $originator = null,
) {}

public function subscribe(
    int|string $stageId,
    PaymentMethodCast $paymentMethod,
    bool $forPromoter = false
): SubscriptionCheckoutResult;

protected function validateEligibility(): void;
protected function determineTargetLevel(Stage $stage, bool $forPromoter): Level;
protected function createPendingSubscription(Stage $stage, Level $level): UserSubscription;
protected function processPayment(UserSubscription $subscription, PaymentMethodCast $method): Transaction;
```

#### 2. MlmPlacementService ✨ NEW
**Location**: `app/Services/Mlm/MlmPlacementService.php`

**Responsibilities**:
- Find available parent spot in tree (5-hand limit)
- Place user under parent
- Update genealogy

**Methods**:
```php
public function __construct(
    private readonly User $user,
    private readonly ?User $preferredParent = null,
) {}

public function findAvailableParent(): ?User;
public function placeInTree(): void;
protected function getChildrenCount(User $parent): int;
protected function searchDescendants(User $parent): ?User;
```

**Algorithm** (From old_project):
```php
public function findAvailableParent(): ?User
{
    // 1. If user has no parent_id → no placement needed
    if (!$this->user->parent_id) {
        return null;
    }

    // 2. Get the intended parent
    $parent = $this->preferredParent ?? User::find($this->user->parent_id);

    // 3. Check parent's direct children count (only SUBSCRIBED users)
    $subscribedChildrenCount = $parent->children()
        ->whereHas('activeSubscription')
        ->count();

    // 4. If parent has space (< 5), place here
    if ($subscribedChildrenCount < 5) {
        return $parent;
    }

    // 5. Search descendants breadth-first
    return $this->searchDescendants($parent);
}

protected function searchDescendants(User $parent): ?User
{
    // Get all descendants with space
    $descendants = User::whereIn('id', $parent->getDescendantIds())
        ->withCount([
            'children as subscribed_children_count' => function ($q) {
                $q->whereHas('activeSubscription');
            }
        ])
        ->having('subscribed_children_count', '<', 5)
        ->orderBy('id') // Breadth-first (lowest ID first)
        ->get();

    return $descendants->first();
}
```

#### 3. SubscriptionActivationService ✨ NEW (or merge into existing)
**Location**: `app/Services/Membership/SubscriptionActivationService.php`

**Responsibilities**:
- Activate subscription after payment
- Update user type/status/level
- Place user in MLM tree
- Trigger commissions
- Send notifications

**Methods**:
```php
public function __construct(
    private readonly UserSubscription $subscription,
) {}

public function activate(Transaction $transaction): void;
protected function updateUserProfile(): void;
protected function placeInMlmTree(): void;
protected function triggerCommissions(): void;
protected function sendNotifications(): void;
```

---

### Controllers

#### 1. SubscriptionController ✨ MODIFY EXISTING
**Location**: `app/Http/Controllers/Api/SubscriptionController.php`

**New Endpoints**:
```php
// User subscribes themselves
POST /api/subscriptions
Body: {
    stage_id: 1,
    payment_method: 'wallet' | 'cashfree' | 'razorpay',
}
Response: {
    subscription_uuid: 'abc-123',
    checkout_url: 'https://.../checkout/TXN-XXX' OR null (if wallet paid instantly)
    status: 'pending' | 'active'
}

// Get available stages for subscription
GET /api/subscriptions/stages
Response: [
    {
        uuid: 'xxx',
        name: 'Starter',
        slug: 'starter',
        price: 999900, // ₹9,999 in paisa
        formatted_price: '₹9,999.00',
        levels: [...],
        benefits: [...]
    }
]

// Get current subscription status
GET /api/subscriptions/status
Response: {
    has_active: true/false,
    subscription: {...} OR null,
    next_available_stage: {...} OR null
}
```

#### 2. GiftSubscriptionController ✨ NEW
**Location**: `app/Http/Controllers/Api/GiftSubscriptionController.php`

**Endpoints**:
```php
// Gift subscription to another user
POST /api/subscriptions/gift
Body: {
    target_user_id: 123,
    stage_id: 1,
    payment_method: 'wallet',
    as_promoter: false, // true = make them promoter
}
Validation:
- Originator must be Member+ (not Regular)
- Originator cannot be Advisor if as_promoter=true
- Originator must have wallet balance if payment_method=wallet
- Target user must not have active subscription

Response: {
    subscription_uuid: 'abc-123',
    checkout_url: '...' OR null,
    status: 'pending' | 'active',
    target_user: {...}
}
```

---

### Form Requests

#### 1. SubscribeStageRequest ✨ NEW
```php
public function rules(): array
{
    return [
        'stage_id' => 'required|exists:stages,id',
        'payment_method' => ['required', new Enum(PaymentMethodCast::class)],
    ];
}
```

#### 2. GiftSubscriptionRequest ✨ NEW
```php
public function rules(): array
{
    return [
        'target_user_id' => 'required|exists:users,id',
        'stage_id' => 'required|exists:stages,id',
        'payment_method' => ['required', new Enum(PaymentMethodCast::class)],
        'as_promoter' => 'boolean',
    ];
}

public function authorize(): bool
{
    // Only Member+ can gift
    return in_array($this->user()->type, [
        UserTypeCast::MEMBER,
        UserTypeCast::PROMOTER,
        UserTypeCast::ADVISOR,
        UserTypeCast::MENTOR,
    ]);
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Advisor cannot make promoters
        if ($this->user()->type === UserTypeCast::ADVISOR && $this->as_promoter) {
            $validator->errors()->add('as_promoter', 'Advisors cannot gift promoter subscriptions.');
        }

        // Check target doesn't have active subscription
        $target = User::find($this->target_user_id);
        if ($target && UserSubscription::hasActiveSubscription($target->id)) {
            $validator->errors()->add('target_user_id', 'Target user already has an active subscription.');
        }
    });
}
```

---

### Listener Updates

#### HandlePaymentCompleted Listener ✨ MODIFY EXISTING
**File**: `app/Listeners/Payment/HandlePaymentCompleted.php`

**Add UserSubscription Handler**:
```php
if ($transactionable instanceof UserSubscription) {
    SubscriptionActivationService::make($transactionable)->activate($transaction);
}
```

---

## 🎨 Frontend Architecture

### Composables

#### 1. useSubscription.ts ✨ NEW
**Location**: `client/app/composables/useSubscription.ts`

**Features**:
```typescript
export const useSubscription = () => {
  const config = useRuntimeConfig()
  const user = useSanctumUser()

  // State
  const currentSubscription = ref(null)
  const availableStages = ref([])
  const loading = ref(false)

  // Fetch current subscription status
  const fetchStatus = async () => { ... }

  // Fetch available stages
  const fetchStages = async () => { ... }

  // Subscribe to stage
  const subscribe = async (stageId: number, paymentMethod: string) => {
    const res = await useSanctumFetch(`${config.public.apiBase}/api/subscriptions`, {
      method: 'POST',
      body: { stage_id: stageId, payment_method: paymentMethod }
    })

    if (res.checkout_url) {
      navigateTo(`/checkout/${res.transaction_uuid}`)
    } else {
      // Instant success (wallet payment)
      await fetchStatus()
      showSuccess('Subscription activated!')
    }
  }

  // Gift subscription
  const giftSubscription = async (targetUserId: number, stageId: number, asPromoter: boolean) => { ... }

  // Computed
  const hasActiveSubscription = computed(() => currentSubscription.value?.status === 'active')
  const canSubscribe = computed(() => !hasActiveSubscription.value)

  return {
    currentSubscription,
    availableStages,
    loading,
    fetchStatus,
    fetchStages,
    subscribe,
    giftSubscription,
    hasActiveSubscription,
    canSubscribe,
  }
}
```

---

### Pages

#### 1. /dashboard/subscribe.vue ✨ CREATE NEW
**Full-featured subscription wizard**

**Sections**:
1. **Current Status**
   - Show if already subscribed (current plan, expiry)
   - Show upgrade option if available

2. **Available Plans** (Stage Selection)
   - Cards for each stage
   - Price, benefits, team capacity
   - "Select Plan" button

3. **Payment Method Selection**
   - Wallet (show balance)
   - Online Payment (Cashfree logo)
   - Auto-select wallet if sufficient balance

4. **Confirmation**
   - Summary
   - Terms acceptance
   - "Subscribe Now" button

**Flow**:
```vue
<template>
  <div class="container mx-auto p-6">
    <!-- Step 1: Status Check -->
    <div v-if="hasActiveSubscription">
      <UCard>
        <h2>Current Subscription</h2>
        <p>{{ currentSubscription.stage.name }}</p>
        <p>Expires: {{ formatDate(currentSubscription.expires_at) }}</p>
      </UCard>
    </div>

    <!-- Step 2: Stage Selection -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <UCard v-for="stage in availableStages" :key="stage.id">
        <h3>{{ stage.name }}</h3>
        <p class="text-3xl font-bold">{{ stage.formatted_price }}</p>
        <ul>
          <li v-for="benefit in stage.benefits" :key="benefit">
            {{ benefit }}
          </li>
        </ul>
        <UButton @click="selectStage(stage)">Select Plan</UButton>
      </UCard>
    </div>

    <!-- Step 3: Payment Method (Modal) -->
    <UModal v-model="showPaymentModal">
      <h3>Choose Payment Method</h3>
      <URadioGroup v-model="paymentMethod">
        <URadio value="wallet">
          Wallet (Balance: {{ walletBalance }})
        </URadio>
        <URadio value="cashfree">
          Online Payment (Cards, UPI, Net Banking)
        </URadio>
      </URadioGroup>

      <UButton @click="confirmSubscribe">Subscribe Now</UButton>
    </UModal>
  </div>
</template>
```

#### 2. /admin/users/[id]/gift-subscription.vue ✨ CREATE NEW
**Admin/Member gifting interface**

**Features**:
- Search user by ID/name/mobile
- Select stage
- Select if target becomes Member or Promoter
- Payment method (wallet only for advisors)
- Confirm and gift

---

## 🧪 Testing Strategy

### Backend Tests (Pest)

#### 1. SubscriptionCheckoutTest.php
```php
it('allows regular user to subscribe with wallet payment')
it('allows regular user to subscribe with cashfree payment')
it('prevents subscribing if already has active subscription')
it('assigns first level when subscribing')
it('places user in MLM tree if has parent_id')
it('finds available parent when direct parent is full')
it('calculates correct pricing with tax and discount')
it('expires transaction after 60 minutes')
```

#### 2. GiftSubscriptionTest.php
```php
it('allows member to gift subscription to regular user')
it('allows member to gift promoter subscription')
it('prevents advisor from gifting promoter subscription')
it('requires advisor to use wallet payment only')
it('sets originator correctly on target subscription')
it('places target under gifter in MLM tree')
it('prevents gifting to user with active subscription')
```

#### 3. MlmPlacementServiceTest.php
```php
it('places user directly under parent when space available')
it('finds next available spot when parent is full')
it('searches breadth-first through tree')
it('only counts subscribed users toward 5-hand limit')
it('handles deep trees (5+ levels)')
```

#### 4. SubscriptionActivationTest.php
```php
it('activates subscription after payment confirmed')
it('updates user type from REGULAR to MEMBER')
it('updates user level_id to subscription level')
it('places user in MLM tree')
it('triggers upline commissions')
it('sends activation notification')
```

---

## 📅 Implementation Timeline

### Day 1: Backend Services (6-8 hours)
- ✅ Create MlmPlacementService with tests (2h)
- ✅ Create SubscriptionCheckoutService with tests (3h)
- ✅ Create SubscriptionActivationService with tests (2h)
- ✅ Run all tests, ensure passing

### Day 2: Backend APIs (6-8 hours)
- ✅ Create/update SubscriptionController (2h)
- ✅ Create GiftSubscriptionController (2h)
- ✅ Create Form Requests with validation (1h)
- ✅ Update HandlePaymentCompleted listener (1h)
- ✅ Write API tests (2h)

### Day 3: Frontend (6-8 hours)
- ✅ Create useSubscription composable (2h)
- ✅ Create /dashboard/subscribe.vue page (3h)
- ✅ Create /admin/users/[id]/gift-subscription.vue (2h)
- ✅ Manual testing with Cashfree sandbox (1h)

### Day 4: Integration & Testing (4-6 hours)
- ✅ End-to-end testing (2h)
- ✅ Edge case testing (1h)
- ✅ Bug fixes (2h)
- ✅ Git commit & push

---

## 🚦 Acceptance Criteria

### Must Have (Launch Blockers)
- ✅ Regular user can subscribe and become Member
- ✅ Payment works via wallet AND Cashfree
- ✅ User gets placed in MLM tree correctly
- ✅ 5-hand limit enforced with auto-placement
- ✅ Level assigned correctly
- ✅ Commissions triggered after subscription
- ✅ Member can gift subscription to make someone Promoter
- ✅ Advisor can gift Member subscription from wallet

### Should Have
- ✅ Subscription expiry handled
- ✅ Renewals supported
- ✅ Upgrades to next stage
- ✅ Admin can gift any subscription

### Nice to Have
- ⏳ Auto-renewal (future)
- ⏳ Promo codes/discounts (future)
- ⏳ Bulk gifting (future)

---

## 📝 Notes & Decisions

### Key Decisions Made:
1. **Use HasTransaction trait** - Already implemented, supports all payment providers ✅
2. **UserSubscription already has originator** - Polymorphic relationship ready ✅
3. **5-hand limit** - Hardcoded to 5 (config('app.matrix') in old project) ✅
4. **Only SUBSCRIBED users count** - Pending/draft users don't fill slots ✅
5. **Breadth-first search** - Lowest ID first for fairness ✅
6. **Advisor restrictions** - Can only gift Member, must use wallet ✅
7. **Transaction expiry** - 60 minutes default ✅

### Data Already Seeded:
- Stages: 4 stages (Starter, Premium, Gold, Platinum) via StageSeeder
- Levels: 16 levels (4 per stage) via LevelSeeder
- Demo users: 71+ users in MLM tree via DemoMlmSeeder

---

## 🎯 Success Metrics

After implementation, we should achieve:
- ✅ All 978+ existing tests still passing
- ✅ 40+ new subscription tests passing
- ✅ Zero TypeScript errors in frontend
- ✅ Successfully process test subscription via Cashfree sandbox
- ✅ MLM tree auto-placement working correctly
- ✅ Commissions calculated and distributed

---

**Status**: Ready to Begin Implementation
**Next Action**: Start Day 1 - Backend Services
