# POST-MIGRATION VERIFICATION CHECKLIST

**Run this checklist IMMEDIATELY after moving to the old repo location**

---

## ✅ STEP 1: Verify Location

```bash
pwd
# Should show: /c/laragon/www/mintreu/server/commerinity
# NOT: /c/laragon/www/mintreu/server/commerinity_pro
```

**Expected Output:** `/c/laragon/www/mintreu/server/commerinity`

---

## ✅ STEP 2: Verify Directory Structure

```bash
ls -la
```

**Should See:**
- ✅ `.git/` (GitHub repo - from old project)
- ✅ `.gitignore` (updated)
- ✅ `.mcp.json` (updated)
- ✅ `.insomnia/` (API testing)
- ✅ `apiserver/` (current backend - from commerinity_pro)
- ✅ `client/` (current frontend - from commerinity_pro)
- ✅ `.claude/` (all memory - from commerinity_pro)
- ✅ `old_project/` (archived old code)
- ✅ `CLAUDE.md` (current project instructions)
- ✅ `README.md` (current project readme)

**Should NOT See:**
- ❌ `backend/` (should be in old_project/)
- ❌ `frontend/` (should be in old_project/)

---

## ✅ STEP 3: Verify .claude Folder

```bash
cd .claude
ls -la
```

**Critical Files (MUST EXIST):**
- ✅ `REFACTOR_COMPLETE_HISTORY.md` ← Complete refactor documentation
- ✅ `MIGRATION_INSTRUCTIONS.md` ← How to use old_project/ correctly
- ✅ `POST_MIGRATION_CHECKLIST.md` ← This file
- ✅ `ACTIVITY_LOG.md` ← Full work history
- ✅ `SESSION_MEMORY.json` ← Current state
- ✅ `FEATURE_COMPLETENESS_AUDIT.md` ← What's done
- ✅ `LAUNCH_BLOCKERS.md` ← What's missing

**Context Files (MUST EXIST):**
```bash
ls -la context/
```
- ✅ `TRANSACTION_SYSTEM_KNOWLEDGE.md` (665 lines)
- ✅ `PAYMENT_PROVIDERS_IMPLEMENTATION.md` (828 lines)
- ✅ `MINTREU_TOOLKIT_PATTERNS.md`
- ✅ `AUTH_TEST_COVERAGE.md`
- ✅ `API_FRONTEND_GAP_ANALYSIS.md`
- ✅ 12 other context files

**Plans (MUST EXIST):**
```bash
ls -la plans/
```
- ✅ `Affiliate_MATRIX_5X4_SYSTEM.md`
- ✅ `Affiliate_MEMBERSHIP_ENTERPRISE_PLAN.md`
- ✅ `ONBOARDING_ENTERPRISE_FINAL.md`
- ✅ `API_FRONTEND_BLUEPRINT.md`

---

## ✅ STEP 4: Verify Git Status

```bash
cd /c/laragon/www/mintreu/server/commerinity
git status
```

**Should Show:**
- Current branch: `refactor-v2` or `development`
- Many untracked files (apiserver/, client/, etc.)
- Ready to commit

```bash
git log --oneline -5
```

**Should Show:** Old commit history (from original commerinity repo)

---

## ✅ STEP 5: Verify Tests Still Pass

```bash
cd apiserver
php artisan test
```

**Expected Result:**
- ✅ 855 tests passing
- ❌ 0 failures
- Duration: ~235 seconds

**If tests fail:**
1. Check .env file exists
2. Check database connection
3. Run `php artisan migrate`
4. Run tests again

---

## ✅ STEP 6: Verify old_project/ Structure

```bash
cd ../old_project
ls -la
```

**Should See:**
- ✅ `README.md` (explains this is archived)
- ✅ `backend/` (old Laravel 11 code)
- ✅ `frontend/` (old Nuxt 3 code)
- ✅ `docs/`
- ✅ `plans/`
- ✅ All other old files

**Verify README:**
```bash
cat README.md
```
Should explain this folder is REFERENCE ONLY, not for production.

---

## ✅ STEP 7: Verify Frontend Still Runs

```bash
cd ../client
npm install  # If node_modules missing
npm run dev
```

**Expected:**
- ✅ Vite dev server starts
- ✅ No errors
- ✅ Opens on http://localhost:3000

**Press Ctrl+C to stop**

---

## ✅ STEP 8: Verify Backend Still Runs

```bash
cd ../apiserver
composer run dev
```

**Expected:**
- ✅ Laravel server starts on :8000
- ✅ Queue listener starts
- ✅ Vite dev server starts
- ✅ No errors

**Press Ctrl+C to stop**

---

## ✅ STEP 9: Read Critical Documents

**Read in this order:**

1. **First:** `MIGRATION_INSTRUCTIONS.md`
   - Prevents hallucination
   - Explains old_project/ vs current project
   - Critical rules for working correctly

2. **Second:** `REFACTOR_COMPLETE_HISTORY.md`
   - Complete refactor documentation
   - What was built over 15 days
   - What's missing
   - What's next

3. **Third:** `FEATURE_COMPLETENESS_AUDIT.md`
   - Current feature status
   - What's production-ready
   - What needs work

4. **Fourth:** `LAUNCH_BLOCKERS.md`
   - Critical missing pieces
   - Checkout/payout priorities
   - Timeline estimates

---

## ✅ STEP 10: Verify Git Remote

```bash
cd ..
git remote -v
```

**Should Show:**
```
origin  https://github.com/mintreu/commerinity.git (fetch)
origin  https://github.com/mintreu/commerinity.git (push)
```

---

## ✅ STEP 11: Ready to Commit Migration

**If all above checks pass:**

```bash
# Stage all changes
git add .

# Commit with proper message
git commit -m "refactor: Complete rewrite with Laravel 12 + Nuxt 4

BREAKING CHANGES:
- Complete codebase refactor for production
- Laravel 11 → Laravel 12
- Filament v3 → Filament v4
- Nuxt 3 → Nuxt 4 (with Nuxt UI v4)
- PHP 8.2 → PHP 8.3.22
- Enterprise-grade architecture

NEW FEATURES:
- Complete wallet system (855 tests passing)
- Affiliate 5x4 matrix with 4-level commissions
- Recruitment system
- Helpdesk/ticketing system
- Activity logging
- Enhanced security (PIN, 2FA ready)

OLD CODE:
- Moved to old_project/ folder for reference
- Backend: Laravel 11 + Filament v3
- Frontend: Nuxt 3

PENDING:
- Checkout/payout flow (next 3 days)
- E-commerce integration (post-launch)

Tests: 855 passing
"

# Push to GitHub
git push -u origin refactor-v2
```

---

## ✅ STEP 12: Final Verification

**Confirm these facts:**

1. ✅ Old code is in `old_project/` (reference only)
2. ✅ Current project is in root (apiserver/, client/)
3. ✅ 855 tests passing in `apiserver/tests/`
4. ✅ All `.claude/` documents present
5. ✅ Git history maintained
6. ✅ Ready to work on checkout/payout

**If ALL checks pass:**
🎉 **MIGRATION SUCCESSFUL!**

**You can now:**
- Start implementing checkout/payout system
- Reference old_project/ when needed (read-only)
- Work confidently with full context
- Push to GitHub with clean history

---

## 🚨 IF SOMETHING FAILS

### Tests Don't Pass
1. Check `apiserver/.env` exists
2. Run `php artisan key:generate`
3. Run `php artisan migrate`
4. Check database connection in .env
5. Run tests again

### Git Issues
1. Check you're in correct directory: `/c/laragon/www/mintreu/server/commerinity`
2. Check .git folder exists
3. Run `git status` to see current state
4. If issues, ask user before proceeding

### Files Missing
1. Check if you're in the right directory
2. Verify old_project/ has old files
3. Verify apiserver/ has new files
4. If files missing, migration wasn't complete

### Frontend Won't Start
1. Check `client/node_modules/` exists
2. Run `npm install` if needed
3. Check `client/.env` has correct API_BASE
4. Try `npm run dev` again

---

## 📞 EMERGENCY ROLLBACK

**If migration went wrong:**

```bash
# DON'T PANIC!
# Old code is still in old_project/
# Nothing is lost

# If you need to restore old structure:
cd /c/laragon/www/mintreu/server/commerinity

# Move old code back (if needed)
mv old_project/backend .
mv old_project/frontend .

# Remove new code (if needed)
rm -rf apiserver/
rm -rf client/

# Restore old git state
git reset --hard HEAD
```

**But this should NOT be needed if migration was done correctly!**

---

## ✅ SUCCESS CRITERIA

**Migration is successful when:**

1. ✅ All tests pass (855 in apiserver/)
2. ✅ Backend runs (`composer run dev`)
3. ✅ Frontend runs (`npm run dev`)
4. ✅ Git history preserved
5. ✅ Old code in old_project/
6. ✅ New code in root
7. ✅ All `.claude/` docs present
8. ✅ Can read old_project/ for reference
9. ✅ Ready to implement checkout/payout
10. ✅ No confusion about file paths

**If all 10 criteria met: 🎉 PERFECT MIGRATION!**

---

**Created:** 2025-12-23
**Purpose:** Verify migration success
**Run:** Immediately after moving to old repo
**Status:** ✅ READY TO USE
