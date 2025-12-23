# MIGRATION INSTRUCTIONS - READ THIS FIRST IN NEW LOCATION

**CRITICAL:** This document prevents hallucination after moving to the old GitHub repo.

---

## 🎯 WHAT HAPPENED

### Before Migration
- **Old project:** `C:\laragon\www\mintreu\server\commerinity` (has .git repo)
  - Had `backend/` and `frontend/` folders
  - Old codebase (Laravel 11, Nuxt 3, Filament v3)

- **Refactored project:** `C:\laragon\www\mintreu\server\commerinity_pro` (no git)
  - Had `apiserver/` and `client/` folders
  - New codebase (Laravel 12, Nuxt 4, Filament v4, 855 tests)

### After Migration
- **Same location:** `C:\laragon\www\mintreu\server\commerinity` (same .git repo)
  - `old_project/` ← OLD CODE (backend/, frontend/) **REFERENCE ONLY**
  - `apiserver/` ← CURRENT PROJECT (from commerinity_pro)
  - `client/` ← CURRENT PROJECT (from commerinity_pro)
  - `.claude/` ← CURRENT PROJECT (from commerinity_pro)

---

## 🚨 CRITICAL RULES - NO HALLUCINATION

### Rule #1: old_project/ is READ-ONLY REFERENCE
```
❌ NEVER write code in old_project/
❌ NEVER modify old_project/ files
❌ NEVER run commands in old_project/
❌ NEVER reference old_project/ paths in new code

✅ ONLY read old_project/ to understand business logic
✅ ONLY use old_project/ for design inspiration
✅ ONLY refer to old_project/ for flow understanding
```

### Rule #2: Current Project Paths (ALWAYS USE THESE)
```
✅ CORRECT PATHS (after migration):
C:\laragon\www\mintreu\server\commerinity\apiserver\
C:\laragon\www\mintreu\server\commerinity\client\
C:\laragon\www\mintreu\server\commerinity\.claude\

❌ WRONG PATHS (don't exist anymore):
C:\laragon\www\mintreu\server\commerinity_pro\  ← MOVED
C:\laragon\www\mintreu\server\commerinity\backend\  ← IN old_project/
C:\laragon\www\mintreu\server\commerinity\frontend\  ← IN old_project/
```

### Rule #3: File Structure Mapping
```
OLD PROJECT (old_project/):
├── backend/           ← READ ONLY (reference)
├── frontend/          ← READ ONLY (reference)
├── docs/              ← READ ONLY (reference)
└── ... (all old files)

CURRENT PROJECT (root):
├── apiserver/         ← WORK HERE (from commerinity_pro)
├── client/            ← WORK HERE (from commerinity_pro)
├── .claude/           ← WORK HERE (from commerinity_pro)
└── old_project/       ← READ ONLY (reference)
```

### Rule #4: When to Use old_project/
```
✅ GOOD REASONS:
- "What was the old subscription flow?" → Read old_project/backend/
- "How did the old UI look?" → Read old_project/frontend/
- "What was the old commission logic?" → Read old_project/backend/
- "Need design inspiration" → Look at old_project/frontend/

❌ BAD REASONS:
- "Let me copy this file from old_project/" → NO, rebuild in apiserver/
- "Let me run tests in old_project/" → NO, only run in apiserver/
- "Let me modify old_project/backend/" → NO, never modify old_project/
```

### Rule #5: Code References
```
✅ CORRECT:
"The subscription logic in old_project/backend/app/Services/SubscriptionService.php
shows we need X, Y, Z. Let me implement this in apiserver/app/Services/SubscriptionService.php"

❌ WRONG:
"Let me use old_project/backend/app/Services/SubscriptionService.php in our current project"
```

---

## 📂 DIRECTORY STRUCTURE (After Migration)

```
C:\laragon\www\mintreu\server\commerinity/  ← WORKING DIRECTORY
├── .git/                      ← GitHub repo (kept from old project)
├── .gitignore                 ← Updated (merged old + new)
├── .mcp.json                  ← Updated
├── .insomnia/                 ← Kept (API testing)
├── CLAUDE.md                  ← Current project instructions
├── README.md                  ← Current project readme
│
├── apiserver/                 ← CURRENT BACKEND (from commerinity_pro)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── tests/                 ← 855 PASSING TESTS
│   └── ...
│
├── client/                    ← CURRENT FRONTEND (from commerinity_pro)
│   ├── app/
│   ├── nuxt.config.ts
│   ├── package.json
│   └── ...
│
├── .claude/                   ← CURRENT MEMORY (from commerinity_pro)
│   ├── ACTIVITY_LOG.md
│   ├── SESSION_MEMORY.json
│   ├── REFACTOR_COMPLETE_HISTORY.md  ← THIS DOCUMENT
│   ├── MIGRATION_INSTRUCTIONS.md      ← YOU'RE READING THIS
│   ├── context/
│   ├── plans/
│   ├── references/
│   └── ...
│
└── old_project/               ← OLD CODE (REFERENCE ONLY)
    ├── README.md              ← Explains this is archived
    ├── backend/               ← Laravel 11, PHP 8.2, Filament v3
    ├── frontend/              ← Nuxt 3, custom components
    ├── docs/
    ├── plans/
    └── ...
```

---

## 🎓 HOW TO USE old_project/ CORRECTLY

### Example 1: Understanding Business Logic ✅
```
USER: "How did the old subscription work?"
CLAUDE:
1. Reads old_project/backend/app/Services/SubscriptionService.php
2. Understands the logic (stages, commissions, upgrades)
3. Documents findings
4. Implements NEW version in apiserver/app/Services/SubscriptionService.php
   (using current Laravel 12 patterns, tested, better architecture)
```

### Example 2: Design Inspiration ✅
```
USER: "Make the dashboard look like the old one"
CLAUDE:
1. Reads old_project/frontend/pages/dashboard.vue
2. Notes the layout, colors, card structure
3. Recreates SIMILAR design in client/app/pages/dashboard/index.vue
   (using Nuxt UI v4 components, not copying old code)
```

### Example 3: Commission Calculation ✅
```
USER: "How were commissions calculated before?"
CLAUDE:
1. Reads old_project/backend/app/Services/CommissionService.php
2. Documents: 5%, 4%, 3%, 2% for levels 1-4
3. Implements in apiserver/app/Services/Mlm/CommissionProcessorService.php
   (with tests, proper money handling, better error handling)
```

---

## 🚫 WHAT NOT TO DO

### ❌ Example 1: Copying Files
```
WRONG:
"Let me copy old_project/backend/app/Services/PaymentService.php
to apiserver/app/Services/PaymentService.php"

CORRECT:
"Let me READ old_project/backend/app/Services/PaymentService.php
to understand the payment flow, then BUILD a new PaymentService.php
in apiserver/app/Services/Payment/ with better architecture"
```

### ❌ Example 2: Referencing Old Paths
```
WRONG:
"The payment service is at C:\laragon\www\mintreu\server\commerinity\backend\app\Services\PaymentService.php"

CORRECT:
"The OLD payment service was at old_project/backend/app/Services/PaymentService.php (reference only).
The CURRENT payment service is at apiserver/app/Services/Payment/PaymentService.php"
```

### ❌ Example 3: Running Old Commands
```
WRONG:
cd C:\laragon\www\mintreu\server\commerinity\backend
php artisan test

CORRECT:
cd C:\laragon\www\mintreu\server\commerinity\apiserver
php artisan test  # 855 tests in CURRENT project
```

---

## ✅ CORRECT WORKFLOW EXAMPLES

### Scenario 1: User asks about old subscription flow
```
1. Read old_project/backend/app/Services/MembershipSubscriptionService.php
2. Document findings: "Old flow had 3 stages (basic/premium/elite),
   processed commissions on activation, checked parent eligibility"
3. Check if CURRENT implementation exists: apiserver/app/Services/SubscriptionService.php
4. If exists: Compare and note differences
5. If missing: Implement NEW version with CURRENT patterns (Laravel 12, tests, etc.)
6. Never copy old code directly
```

### Scenario 2: User wants old dashboard design
```
1. Read old_project/frontend/pages/dashboard/member.vue
2. Take notes: "Had stat cards in grid, used gradient backgrounds,
   showed wallet balance, team size, earnings"
3. Check CURRENT implementation: client/app/components/dashboard/DashboardMember.vue
4. If exists: Good, already done
5. If missing: Build NEW version using Nuxt UI v4 components,
   matching OLD design style but with CURRENT component library
```

### Scenario 3: User asks "what's the difference between old and new?"
```
1. Reference REFACTOR_COMPLETE_HISTORY.md (has complete comparison)
2. Key points:
   - Old: Laravel 11, Nuxt 3, custom components, minimal tests
   - New: Laravel 12, Nuxt 4, Nuxt UI v4, 855 tests, enterprise patterns
3. Both have same business logic, NEW has better implementation
```

---

## 📝 COMMANDS REFERENCE

### Working with Current Project (ALWAYS)
```bash
# Backend
cd /c/laragon/www/mintreu/server/commerinity/apiserver
composer install
php artisan test
php artisan migrate
vendor/bin/pint --dirty

# Frontend
cd /c/laragon/www/mintreu/server/commerinity/client
npm install
npm run dev

# Git (from root)
cd /c/laragon/www/mintreu/server/commerinity
git status
git branch
git checkout development
```

### Reading Old Project (REFERENCE ONLY)
```bash
# Read old files (but don't run commands there)
cd /c/laragon/www/mintreu/server/commerinity/old_project
cat backend/app/Services/SomeService.php
grep -r "subscription" backend/app/
# But DON'T run: php artisan test, composer install, etc.
```

---

## 🎯 NEXT SESSION CHECKLIST

When starting work after migration:

1. ✅ Verify location: `pwd` should show `C:\laragon\www\mintreu\server\commerinity`
2. ✅ Check project structure: `ls -la` should show `apiserver/`, `client/`, `old_project/`
3. ✅ Read this document: `MIGRATION_INSTRUCTIONS.md`
4. ✅ Read refactor history: `REFACTOR_COMPLETE_HISTORY.md`
5. ✅ Check tests: `cd apiserver && php artisan test` (should show 855 passing)
6. ✅ Verify git: `git status` (should show refactor-v2 branch)
7. ✅ Begin work: Start implementing checkout/payout (NOT reading old_project unnecessarily)

---

## 🧠 MEMORY ANCHORS

### What This Project IS
- **Name:** Commerinity Pro (refactored version)
- **Location:** `C:\laragon\www\mintreu\server\commerinity` (root)
- **Backend:** `apiserver/` (Laravel 12, PHP 8.3.22, Filament v4)
- **Frontend:** `client/` (Nuxt 4, Nuxt UI v4, ssr: false)
- **Tests:** 855 passing (in apiserver/tests/)
- **Status:** 95% complete, needs checkout/payout
- **Git:** Has history, branch refactor-v2

### What old_project/ IS
- **Purpose:** Reference only for understanding old business logic
- **Technology:** Laravel 11, Nuxt 3, Filament v3 (outdated)
- **Location:** `old_project/` subfolder
- **Status:** Archived, read-only, not for direct use
- **Usage:** Read to understand, rebuild in current project

### What NOT to Confuse
```
❌ Don't think old_project/ is the current project
❌ Don't think we work in both codebases
❌ Don't think we can copy code from old_project/
❌ Don't think old_project/ has newer code
✅ We work ONLY in apiserver/ and client/
✅ old_project/ is REFERENCE LIBRARY
✅ Current project IS the production codebase
✅ Current project HAS all the modern improvements
```

---

## 🚀 READY TO WORK

After reading this document, you should:

1. **Know where you are:** `C:\laragon\www\mintreu\server\commerinity`
2. **Know what's what:** `apiserver/` (current), `old_project/` (reference)
3. **Know what to do:** Work in `apiserver/` and `client/`, read `old_project/` when needed
4. **Know what NOT to do:** Never modify `old_project/`, never use old paths

**If you understand all of this, proceed with confidence to implement checkout/payout system!**

---

**Document Created:** 2025-12-23
**Purpose:** Prevent hallucination after migration
**Read This:** FIRST THING in every new session after migration
**Status:** ✅ MIGRATION READY
