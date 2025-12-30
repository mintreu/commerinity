# 🎯 NEXT SESSION - START HERE

**Read this FIRST when opening the project at new location!**

---

## ✅ YOU ARE NOW HERE:
```
📍 C:\laragon\www\mintreu\server\commerinity
```

**NOT HERE ANYMORE:**
```
❌ C:\laragon\www\mintreu\server\commerinity_pro (moved to commerinity/)
```

---

## 🚨 CRITICAL: PREVENT HALLUCINATION

### Step 1: Verify Location (30 seconds)
```bash
pwd
# MUST show: /c/laragon/www/mintreu/server/commerinity

ls -la
# MUST see: apiserver/, client/, .claude/, old_project/
```

### Step 2: Read These 3 Files IN ORDER (15 minutes)

1. **MIGRATION_INSTRUCTIONS.md** ← MUST READ FIRST!
   - Explains old_project/ vs current project
   - Anti-hallucination rules
   - Path changes documented

2. **REFACTOR_COMPLETE_HISTORY.md** ← Complete context
   - 15 days of refactoring documented
   - All 855 tests explained
   - What's done, what's missing

3. **POST_MIGRATION_CHECKLIST.md** ← Verify everything
   - 12-step verification
   - Ensure tests still pass
   - Ready to work checklist

---

## 📁 DIRECTORY STRUCTURE (After Migration)

```
commerinity/  ← YOU ARE HERE NOW
├── apiserver/         ← CURRENT BACKEND (work here)
├── client/            ← CURRENT FRONTEND (work here)
├── .claude/           ← CURRENT MEMORY (read from here)
│   ├── MIGRATION_INSTRUCTIONS.md ⭐ READ FIRST
│   ├── REFACTOR_COMPLETE_HISTORY.md ⭐ READ SECOND
│   └── POST_MIGRATION_CHECKLIST.md ⭐ RUN THIRD
├── old_project/       ← OLD CODE (reference only, READ ONLY)
│   ├── backend/       ← Old Laravel 11 code
│   ├── frontend/      ← Old Nuxt 3 code
│   └── README.md      ← Explains this is archived
└── .git/              ← GitHub repo (preserved)
```

---

## 🎯 WHAT TO DO NEXT

### Priority 1: Verify Migration (15 minutes)
```bash
cd /c/laragon/www/mintreu/server/commerinity

# Check tests still pass
cd apiserver
php artisan test
# MUST show: 855 passing

# Check frontend runs
cd ../client
npm run dev
# MUST open on localhost:3000
```

### Priority 2: Start Implementing Checkout (Day 1-3)

**Files to create:**
```
apiserver/app/Http/Controllers/Api/CheckoutController.php
client/app/pages/checkout/index.vue
client/app/pages/checkout/payment.vue
client/app/pages/checkout/success.vue
client/app/pages/checkout/failed.vue
```

**What it does:**
- Initiates Cashfree/Razorpay payment
- Handles payment callback
- Updates transaction status
- Credits commission to uplines

### Priority 3: Wire Payout Job (Day 4-5)

**File to modify:**
```
apiserver/app/Jobs/Wallet/ProcessPayoutJob.php
```

**What to add:**
- Call CashfreePayoutProvider->initiate()
- Handle success/failure
- Add retry logic
- Update transaction status

---

## 🚫 WHAT NOT TO DO

### ❌ Never Use These Paths:
```
C:/laragon/www/mintreu/server/commerinity_pro/  ← DOESN'T EXIST
C:/laragon/www/mintreu/server/commerinity/backend/  ← IN old_project/
C:/laragon/www/mintreu/server/commerinity/frontend/  ← IN old_project/
```

### ✅ Always Use These Paths:
```
C:/laragon/www/mintreu/server/commerinity/apiserver/  ← CURRENT
C:/laragon/www/mintreu/server/commerinity/client/  ← CURRENT
C:/laragon/www/mintreu/server/commerinity/old_project/  ← REFERENCE
```

### ❌ Never Do This:
```bash
cd old_project/backend
php artisan test  # NO! Don't run commands here
```

### ✅ Always Do This:
```bash
cd apiserver
php artisan test  # YES! Run commands in current project
```

---

## 📊 CURRENT STATUS

### What's Complete ✅
- 855 tests passing
- Complete auth system
- Complete wallet (95% - needs checkout/payout)
- Complete dashboard (5 types)
- Complete recruitment system
- Complete helpdesk system
- Complete messaging system
- Payment providers implemented (Cashfree, Razorpay)

### What's Missing ❌
1. **Checkout flow** (3 days) ← START HERE
2. **Payout processing** (2 days)
3. **Add money flow** (1.5 days)
4. **Affiliate frontend** (2 days)
5. **E-commerce** (deferred)

### Total to Launch: 3-5 days

---

## 🧠 QUICK MEMORY ANCHORS

### MoneyService Pattern
```php
// ✅ CORRECT
MoneyService::format($paisa); // Display
$wallet->balance; // Integer in DB

// ❌ WRONG
$wallet->balance->formatted(); // No magic!
```

### API Pattern
```typescript
// ✅ CORRECT
const config = useRuntimeConfig()
await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {})

// ❌ WRONG
await $fetch('/api/endpoint')
```

### Commission Structure
```
Level 1: 5% (direct sponsor)
Level 2: 4%
Level 3: 3%
Level 4: 2%
Originator: 5% (agent)
```

---

## 🎓 REFERENCE LOCATIONS

### Current Project
- **Backend:** `commerinity/apiserver/`
- **Frontend:** `commerinity/client/`
- **Tests:** `commerinity/apiserver/tests/` (855 passing)

### Old Project (Reference Only)
- **Backend:** `commerinity/old_project/backend/`
- **Frontend:** `commerinity/old_project/frontend/`
- **Purpose:** READ ONLY for understanding business logic

### Other References
- **Popkult:** `C:/laragon/www/iotron/popkult` (e-commerce)
- **JetPax:** `C:/laragon/www/iotron/JetPax-Production` (payments)

---

## ✅ READY TO WORK CHECKLIST

Before starting implementation, confirm:

- [ ] Location: `pwd` shows `commerinity/`
- [ ] Structure: `ls` shows `apiserver/`, `client/`, `old_project/`
- [ ] Tests: `php artisan test` shows 855 passing
- [ ] Read: `MIGRATION_INSTRUCTIONS.md` (anti-hallucination)
- [ ] Read: `REFACTOR_COMPLETE_HISTORY.md` (complete context)
- [ ] Verified: `POST_MIGRATION_CHECKLIST.md` (all checks pass)

**If all checked:** 🎉 **Ready to implement checkout/payout!**

---

## 🚀 LET'S BUILD!

**Goal:** Complete checkout/payout in 3-5 days → Launch v1.0

**Remember:**
- old_project/ = reference only (read, don't modify)
- apiserver/ = current work (test-driven)
- 855 tests must keep passing
- Document as you go

**You got this! Let's finish strong! 💪**

---

**Created:** 2025-12-23
**For:** Next session after migration
**Status:** ✅ READY TO START
