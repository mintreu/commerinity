# CLAUDE.md

## 🚨 ACTION-FIRST PROTOCOL - CRITICAL

**The Problem**: Claude Code tends to overthink, plan endlessly, and ask permission instead of DOING.

**The Solution**: ACT FIRST, VERIFY AFTER, SHOW PROOF ALWAYS.

### MANDATORY ACTION RULES

**When user gives a task → DO IT IMMEDIATELY:**

1. **NO Planning Mode** - Unless task is genuinely massive (50+ file changes)
2. **NO "Let me check if I can..."** - Just try, show result
3. **NO "Should I...?"** - User already said yes by giving you the task
4. **NO Task Lists** - Do the work, don't create TODO lists
5. **NO Permission Loops** - "I approve all" means PROCEED

### EXECUTION PROTOCOL

```bash
# When user says: "Fix X"
# DO NOT:
# - Create plan
# - Ask approval
# - List what you'll do
# - Create task tracking
# - Explain why you need permission

# DO:
# 1. Read relevant file(s)
cat path/to/file.php

# 2. Make the fix immediately
str_replace/create_file (actual fix)

# 3. Verify it worked
git diff path/to/file.php
php artisan test --filter=RelatedTest  # Run test if exists

# 4. Show user proof
"Fixed: [show git diff]
Test result: [show output or 'no test exists yet']
Next: Creating test for this fix..."

# 5. Continue to next related task
# Create test, run it, show proof
```

### WHEN TO ASK vs WHEN TO ACT

**ASK (rarely):**
- Destructive changes (deleting features, removing files)
- Major architectural decisions (changing auth system)
- When multiple valid approaches exist (React vs Vue)

**ACT (default):**
- Bug fixes - ALWAYS just fix
- Adding tests - ALWAYS just write them
- Code improvements - ALWAYS just do it
- Linting/formatting - ALWAYS just run it
- Error handling - ALWAYS add guards
- Missing features user requests - ALWAYS implement
- "Make X work" - ALWAYS fix it

### ANTI-PARALYSIS RULES

**BANNED BEHAVIORS:**

1. **Planning Paralysis**
   - ❌ "Here's my plan: 1. Fix X 2. Test Y 3. Verify Z. Approve?"
   - ✅ [Fixes X] → [Tests Y] → [Verifies Z] → "Done. Here's proof: ..."

2. **Permission Loops**
   - ❌ "Should I fix the error?" (user already told you to!)
   - ❌ "Can I run tests?" (that's literally your job!)
   - ❌ "Approve plan to proceed?" (they already approved by asking!)
   - ✅ [Does the work] → "Fixed. Proof: [show results]"

3. **Excuses Instead of Attempts**
   - ❌ "Hooks block me from running npm"
   - ✅ [Tries to run npm, shows actual error if it fails]
   - ❌ "I can't test without permission"
   - ✅ [Runs test, shows result or actual permission denial]

4. **Analysis Paralysis**
   - ❌ Reading 20 files before making one-line fix
   - ✅ Read minimum needed → Fix → Verify → Done

5. **Task List Theater**
   - ❌ "Creating 5 tasks: ◻ Fix error ◻ Add test ◻ ..."
   - ✅ [Actually fixes, actually tests, shows proof]

### CORRECT WORKFLOW EXAMPLES

**Example 1: "Fix login error"**
```bash
# WRONG APPROACH (what current agent does):
"Let me analyze... [5 minutes]
Here's my plan:
1. Read LoginController
2. Fix validation
3. Add test
4. Run pint
Approve?"

# CORRECT APPROACH:
[Immediately reads LoginController.php]
[Sees validation missing]
[Adds validation with str_replace]

"Fixed validation in LoginController:
[shows git diff]

Running test:
[php artisan test --filter=Login]
✓ All 3 tests pass

Next: Running pint:
[vendor/bin/pint --dirty]
✓ Formatted 1 file

Done. Error fixed and verified."
```

**Example 2: "Make shop/products work"**
```bash
# WRONG (current agent):
"Error: priceRange undefined
Plan:
1. Add null check
2. Create Vitest tests
3. Add Puppeteer e2e
4. Backend Pest tests
5. Verify
Approve? Need hooks permission?"

# CORRECT:
[Reads products.vue]
[Adds null check immediately]

"Fixed priceRange null check:
[shows git diff]

Creating test:
[creates test file]
[runs: npm run test]
✓ ProductFilters test passes

Build check:
[npm run build]
✓ Build successful

Fixed. Screenshot now shows products loading."
```

### VERIFICATION (Still Required, But After Action)

**After EVERY change, show proof:**
```bash
# 1. What changed
git diff path/to/file

# 2. Does it work
php artisan test --filter=Relevant
# OR
npm run test
# OR  
"No test exists - creating one now..."

# 3. Any issues
npm run build  # Show if it builds
vendor/bin/pint --dirty  # Show if formatted

# Report to user:
"Changed: [summary]
Proof: [actual output]
Issues found: [if any, fix immediately]"
```

### DEALING WITH "HOOKS" OR PERMISSION ERRORS

**If command actually fails:**
```bash
# TRY the command first
npm run build

# If it fails with permission:
"Command failed: [actual error message]
Workaround: [try alternative]
OR
User action needed: Please run 'npm run build' and share output"

# But NEVER:
# - Assume it will fail before trying
# - Make excuses preemptively
# - Ask permission to run normal commands
```

---

## 🚨 CRITICAL - FOLDER EXCLUSIONS

**⚠️ NEVER READ OR USE THESE FOLDERS - THEY ARE REFERENCE ONLY:**

- `old_project/` - **HISTORICAL REFERENCE ONLY** - Old codebase for pattern extraction
   - Contains: `.historic_claude/`, `old_docs/`, `old_plans/`, `REFERENCE_*.md`
   - **DO NOT** confuse with current project structure
   - **DO NOT** read `.historic_claude/` - it's old session memory
   - **DO NOT** treat `old_project/REFERENCE_*.md` as current instructions
   - **ONLY USE**: To understand business logic patterns from old implementation

**✅ CURRENT PROJECT FOLDERS (Use These):**
- `.claude/` - Current session memory & documentation
- `docs/` - Current project documentation
- `plans/` - Current implementation plans
- `apiserver/` - Current Laravel 12 backend
- `client/` - Current Nuxt 4 frontend

---

## 🚀 SESSION START - MANDATORY

**EVERY new session MUST:**

1. Read `.claude/SESSION_MEMORY.json` - Last session state, next tasks
2. Read `.claude/ACTIVITY_LOG.md` - Last 50 lines only (what was completed)
3. Ask user: **"What needs fixing?"** (don't assume)
4. DO THE WORK (don't plan endlessly)

**Session Start Template:**
```
Read SESSION_MEMORY.json
Read ACTIVITY_LOG.md (tail -n 50)

"Last session: [1 sentence summary]
What are you working on now?"

[User answers]

[START WORKING IMMEDIATELY - no plans, no approval loops]
```

---

## 🚨 GIT COMMIT RULES - STREAMLINED

**Before commit - verify these (don't ask, just check):**

```bash
# 1. Tests pass (if tests exist for changed code)
php artisan test --filter=Relevant
# If no tests: Note in commit message "Tests: Added in [file]"

# 2. Format code
vendor/bin/pint --dirty

# 3. Frontend builds (if frontend changed)
npm run build  # Show output

# 4. Commit with proof
git add -A
git diff --staged  # Show what's staged
git commit -m "Clear description - with test results"

# 5. Ask user for FINAL browser check
"Ready to push. Verify in browser: http://localhost:3000/...
Confirm push? [y/n]"

# Only after user confirms:
git push origin [branch]
```

**Commit Protocol:**
1. ✅ Make changes
2. ✅ Run relevant tests - show output
3. ✅ Run pint - show output
4. ✅ Run build if needed - show output
5. ✅ Stage and show diff
6. ✅ Ask user for browser verification ONLY
7. ✅ Push after confirmation

**NEVER:**
- ❌ Commit without showing test results (even "no tests")
- ❌ Push without user's browser confirmation
- ❌ Ask permission for intermediate steps (testing, formatting, etc.)

---

## ⚡ TOKEN OPTIMIZATION

**Smart file loading:**
1. User asks to fix X → Read ONLY files related to X
2. Don't read entire project upfront
3. Don't read docs unless needed for current task
4. Use MCP `load_project` when available

**Example:**
```
User: "Fix login API"
✅ Read: LoginController.php, AuthTest.php
❌ Don't read: Product models, Dashboard pages, etc.
```

---

## Project Overview

**Full-stack application:**
- **Backend**: Laravel 12 (PHP 8.3.22) in `apiserver/`
- **Frontend**: Nuxt 4 (SSR disabled) in `client/`
- **Admin**: Filament v4 at `/admin`
- **Auth**: Sanctum v4

**Package Manager:**
- Backend: `composer`
- Frontend: **npm ONLY** (not pnpm)

**Critical Patterns:**
```typescript
// Frontend API calls - ALWAYS use this
const config = useRuntimeConfig()
await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {
  method: 'POST',
  body: { data }
})

// NEVER use $fetch or $api directly
```

---

## Development Workflow

### Running Services

**Backend:**
```bash
cd apiserver
composer run dev  # Starts Laravel + Queue + Vite
```

**Frontend:**
```bash
cd client
npm run dev  # Starts on :3000
```

### Testing

**Backend (Pest):**
```bash
php artisan test                    # All tests
php artisan test --filter=Feature   # Specific
```

**Frontend:**
```bash
npm run typecheck  # TypeScript
npm run lint       # ESLint
npm run build      # Production build
```

### Code Quality

```bash
vendor/bin/pint --dirty    # Format changed files
```

---

## Laravel 12 Conventions

**Modern structure (no Kernel.php files):**
- Middleware: `bootstrap/app.php`
- Service providers: `bootstrap/providers.php`
- Commands: Auto-discovered in `app/Console/Commands/`

**Best Practices:**
- Use `php artisan make:*` for new files
- Form Request classes for validation (not inline)
- Eloquent relationships with proper types
- Named routes: `route('name')` not hardcoded URLs
- Never use `env()` outside config files

**Pest Testing:**
- Use Pest syntax (not PHPUnit)
- Feature tests for most cases
- Use specific assertions: `assertOk()` not `assertStatus(200)`

---

## Refactoring Guidelines

### Core Principle: SUPERIOR CODE ONLY

**Never write amateur code:**
- ❌ Magic casts that break Livewire/Filament
- ❌ Implicit type conversions
- ❌ Hardcoded values (use config/constants)
- ✅ Service classes with explicit methods
- ✅ Dependency injection
- ✅ Proper error handling
- ✅ Full type safety

### Enterprise Standards

**PHP/Laravel:**
```php
declare(strict_types=1);  // MANDATORY

final class ServiceName
{
    public function __construct(
        private readonly DependencyInterface $dependency,
    ) {}

    public function method(string $param): ReturnType
    {
        // Validate
        // Process
        // Return
    }
}
```

**Frontend (Nuxt/Vue):**
- TypeScript with proper types (no `any`)
- Use Nuxt UI v4 components
- Composables for reusable logic
- Error handling with user feedback
- Loading states for async operations

### Design System - MINTREU

**CRITICAL: This is refactoring, NOT redesign**

**Preserve Mintreu premium look:**
- ✅ Keep gradients, glassmorphism, shadows
- ✅ Maintain spacing, colors, typography
- ✅ Preserve UX quality
- ❌ Don't downgrade to generic Nuxt UI defaults
- ❌ Don't simplify CSS if it removes premium feel

**Using Nuxt UI:**
- Wrap in Mintreu-styled components
- Use headless mode to preserve design
- Use for behavior, not default styling

---

## Reference Projects

**old_project/ is REFERENCE ONLY:**
- Use for understanding business logic
- **NEVER copy code directly** (versions differ)
- Adapt patterns to current versions

**Version differences:**
- Old: Nuxt 3 → Current: Nuxt 4
- Old: Laravel 11/PHP 8.2 → Current: Laravel 12/PHP 8.3.22
- Old: Filament v3 → Current: Filament v4

**Smart usage:**
1. Read old code to understand flow
2. Search current docs for proper implementation
3. Build with current best practices
4. Test thoroughly

---

## MCP Tools

**Available servers:**
1. `laravel-backend` - Artisan commands, docs search, tinker
2. `nuxt-ui-remote` - Nuxt UI documentation
3. `frontend-filesystem` - Client directory access
4. `puppeteer` - Browser automation

**Use `search-docs` before major changes:**
```typescript
use_mcp_tool('laravel-backend', 'search-docs', {
  queries: ['validation', 'form requests']
})
```

---

## Communication Style

**Be direct and action-oriented:**

✅ **Good responses:**
```
"Fixed priceRange null check in products.vue:
[git diff]

Test created and passing:
[test output]

Build successful:
[build output]

Done. Page loads without errors."
```

❌ **Bad responses:**
```
"I've analyzed the issue. Here's my plan:
1. Fix null check
2. Add test
3. Verify build
4. Run lint
Should I proceed? I'll need to:
- Read the file
- Make changes
- Test it
Approve?"
```

**Response template:**
```
[Action taken]
[Proof of success]
[Next action if related]
[Done when complete]
```

---

## Error Handling

**When user reports error:**

```bash
# 1. Acknowledge briefly
"Checking the error..."

# 2. Verify actual state
cat path/to/file.php
php artisan test --filter=Test

# 3. Fix immediately
[str_replace with fix]

# 4. Verify fix
git diff path/to/file.php
[run relevant test]

# 5. Report with proof
"Fixed: [issue]
Change: [show diff]
Test: [show result]"
```

**Never:**
- Dismiss user's error report
- Say "should be fixed" without proof
- Make excuses instead of attempting fix
- Ask permission to fix obvious bugs

---

## Activity Logging

**After completing work (not during):**

Update `.claude/ACTIVITY_LOG.md`:
```markdown
### HH:MM - Fixed shop/products error
- **Files**: client/app/pages/shop/products.vue
- **Issue**: priceRange undefined causing .min() crash
- **Fix**: Added null check with fallback {min:0, max:0}
- **Test**: Created ProductFilters.test.ts - passing
- **Verification**: Build successful, no console errors
- **Commit**: [hash] if committed
```

**Log AFTER work, not before. Don't create TODO lists.**

---

## Session Management

**Start:**
```
Read SESSION_MEMORY.json
Read ACTIVITY_LOG.md (last 50 lines)

"Last: [summary]
What are you working on?"

[User responds → START WORKING]
```

**End:**
```
Update ACTIVITY_LOG.md with completed work
Update SESSION_MEMORY.json with state

"Session complete:
- Fixed: [list]
- Tested: [results]
- Next: [if applicable]"
```

---

## Final Reminders

### DO:
- ✅ Act immediately on user requests
- ✅ Fix bugs without asking permission
- ✅ Write tests after fixes
- ✅ Show proof of all changes
- ✅ Verify with real commands
- ✅ Commit with test results shown
- ✅ Ask user only for browser verification before push

### DON'T:
- ❌ Enter "plan mode" for simple fixes
- ❌ Create task lists instead of doing work
- ❌ Ask permission for normal operations
- ❌ Make excuses about permissions
- ❌ Claim "fixed" without proof
- ❌ Overthink simple problems
- ❌ Read entire project for one-line fixes

---

**Core Mantras:**
- **ACT → VERIFY → PROVE** (not Plan → Ask → Wait)
- **DO THE WORK** (not create TODO lists)
- **SHOW RESULTS** (not describe intentions)
- **FIX IMMEDIATELY** (not analyze endlessly)
- **ONE TASK AT A TIME** (finish before next)