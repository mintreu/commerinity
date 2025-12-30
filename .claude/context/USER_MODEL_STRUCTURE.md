# User Model Structure
## Based on Old Commerinity (Improved & Minimal)

---

## 🎯 **Core Decision**

**Use `type` column as role** - No separate roles/permissions table needed.
- Each type gets full dashboard access for their features
- Simple type checking in code: `$user->type === UserType::MEMBER`
- Status controls account state (active, suspended, banned, etc.)

---

## 📊 **Users Table Schema**

```php
Schema::create('users', function (Blueprint $table) {
    // Primary
    $table->id();
    $table->uuid();

    // Identity
    $table->string('name');
    $table->string('email')->nullable()->unique();
    $table->string('mobile')->nullable()->unique();
    $table->string('password');

    // Verification
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamp('mobile_verified_at')->nullable();

    // Affiliate Tree
    $table->string('referral_code')->unique();
    $table->foreignId('parent_id')
        ->nullable()
        ->constrained('users')
        ->cascadeOnUpdate()
        ->nullOnDelete();

    // Originator - Which Agent recruited this member (for salary calculation)
    // Polymorphic: Can be User (Agent/Advisor) who recruited this member
    $table->nullableMorphs('originator');

    // Profile
    $table->text('bio')->nullable();
    $table->string('gender')->default('other'); // male, female, other
    $table->date('dob')->nullable();

    // Type & Status (THE CORE!)
    $table->string('type')
        ->default('regular')
        ->index();

    $table->string('status')
        ->default('draft')
        ->index();

    $table->text('status_feedback')->nullable(); // Rejection/suspension reason

    // Onboarding
    $table->boolean('onboarded')->default(false);

    // Membership (Phase 2 - add later)
    // $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();

    $table->rememberToken();
    $table->timestamps();
});
```

---

## 🏷️ **User Types (Enum)**

```php
enum UserType: string
{
    case REGULAR   = 'regular';    // Default - non-subscribed customer
    case MEMBER    = 'member';     // Subscribed with active membership
    case PROMOTER  = 'promoter';   // Actively refers others (Affiliate participant)
    case ADVISOR   = 'advisor';    // Company-appointed, gets salary
    case MENTOR    = 'mentor';     // Trains users, gets training fees
    case APPLICANT = 'applicant';  // Applied for mentor/advisor role
}
```

**What each type does:**

1. **Regular** (default):
   - Basic customer account
   - Can shop, create orders
   - Can refer others (everyone has referral_code)
   - No membership benefits

2. **Member**:
   - Paid subscription active
   - Access to member-only products/discounts
   - Enhanced commission rates
   - Member dashboard features

3. **Promoter**:
   - Actively recruiting in Affiliate
   - Gets team commissions
   - Promoter dashboard with team analytics
   - Can upgrade to higher levels

4. **Advisor** (Company Employee - OUTSIDE Affiliate):
   - Company-appointed agent
   - Recruits members to join Affiliate
   - Assists Affiliate team heads with onboarding
   - Gets fixed salary based on recruitment targets
   - Does NOT participate in Affiliate commissions
   - Dashboard shows recruitment performance

5. **Mentor**:
   - Approved trainer
   - Creates courses/content
   - Gets paid for training
   - Mentor dashboard for content management

6. **Applicant**:
   - Applied for mentor/advisor role
   - Limited access until approved
   - Application status tracked in originator

---

## 📍 **User Status (Enum)**

```php
enum UserStatus: string
{
    // Onboarding states
    case DRAFT        = 'draft';        // Just registered, not completed
    case INCOMPLETE   = 'incomplete';   // Missing required info
    case IN_REVIEW    = 'in_review';    // Application under review

    // Active states
    case PENDING      = 'pending';      // Waiting for approval
    case ACTIVE       = 'active';       // Active regular user
    case SUBSCRIBED   = 'subscribed';   // Active member with subscription

    // Inactive states
    case SUSPENDED    = 'suspended';    // Temporarily blocked
    case INACTIVE     = 'inactive';     // Dormant account
    case BANNED       = 'banned';       // Permanently blocked
    case UNSUBSCRIBED = 'unsubscribed'; // Was member, now cancelled
}
```

---

## 🔄 **Type + Status Combinations**

| Type | Common Status | Access |
|------|---------------|--------|
| regular | draft → active | Shop only |
| regular | suspended | No access |
| member | subscribed | Member dashboard |
| member | unsubscribed | Reverted to regular |
| promoter | active | Promoter dashboard |
| advisor | active | Advisor dashboard |
| mentor | active | Mentor dashboard |
| applicant | in_review | Limited access |

---

## 🔄 **Two Separate Systems Explained**

### **System 1: Affiliate Tree (parent_id + referral_code)**
```
Anyone can refer others using their referral_code
↓
New user joins with referral code
↓
parent_id is set (Affiliate upline relationship)
↓
Commissions flow through this tree
```

**Participants**: REGULAR, MEMBER, PROMOTER

### **System 2: Agent Recruitment (originator)**
```
Company appoints Agent/Advisor
↓
Agent recruits new members (assists onboarding)
↓
originator_id is set (tracks Agent's performance)
↓
Agent gets salary based on recruitment targets
```

**Participants**: ADVISOR only (OUTSIDE Affiliate)

### **Example Scenario**
```
Agent John recruits Alice to join Affiliate under Bob's team:
- Alice.originator = John (Agent) → John's salary calculation
- Alice.parent_id = Bob (Promoter) → Bob's Affiliate commissions
- Alice.referral_code = "ABC123" → Alice can refer others

Result:
- John gets salary for recruiting Alice
- Bob gets Affiliate commissions from Alice's purchases
- Alice participates in Affiliate, can refer others
```

---

## 🎯 **Why This Works**

### **1. Simple Authorization**
```php
// Middleware
if ($user->type !== UserType::MEMBER) {
    abort(403, 'Members only');
}

// Blade
@if($user->type === UserType::PROMOTER)
    <a href="/promoter/dashboard">My Team</a>
@endif

// Policy
public function viewPromotionDashboard(User $user): bool
{
    return $user->type === UserType::PROMOTER
        && $user->status === UserStatus::ACTIVE;
}
```

### **2. Type Progression**
```php
// User subscribes
$user->update([
    'type' => UserType::MEMBER,
    'status' => UserStatus::SUBSCRIBED,
]);

// Member becomes promoter
$user->update(['type' => UserType::PROMOTER]);

// Suspend user
$user->update([
    'status' => UserStatus::SUSPENDED,
    'status_feedback' => 'Payment fraud detected',
]);
```

### **3. Dashboard Routing**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return match (auth()->user()->type) {
            UserType::MEMBER => redirect('/member/dashboard'),
            UserType::PROMOTER => redirect('/promoter/dashboard'),
            UserType::ADVISOR => redirect('/advisor/dashboard'),
            UserType::MENTOR => redirect('/mentor/dashboard'),
            default => redirect('/shop'),
        };
    });
});
```

---

## 📝 **Additional Fields (Phase 2+)**

Add these columns when features require them:

```php
// Phase 2: Membership System
$table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();

// Phase 3: Avatar (Spatie Media Library)
// Uses media library collection 'avatarImage'

// Phase 4: Affiliate Analytics (if needed)
$table->integer('total_referrals')->default(0);
$table->integer('active_team_size')->default(0);
```

---

## ✅ **Summary**

**Old Commerinity got this RIGHT** ✅:
- Single `users` table with `type` column
- `status` for account state management
- `status_feedback` for admin communication
- `onboarded` flag for tracking completion
- Simple, flexible, performant

**No need for**:
- ❌ Separate profile tables
- ❌ Spatie roles/permissions
- ❌ Polymorphic user types
- ❌ God model with 50 columns

**Just use**: Type as role + Status as state = Perfect! 🚀

---

**Next**: Create the migration and Enum classes
