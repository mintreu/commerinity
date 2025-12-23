# Token Optimization Strategy

## Problem
High token usage from loading full context every session kills budget and limits productive work time.

## Solution: Lazy Loading Context Architecture

### 1. **Condensed References (Read First)**
- **Location**: `docs/QUICK_REF.md`
- **Size**: <2KB
- **Content**: File paths only with 1-line descriptions
- **Usage**: Claude reads this FIRST to know what exists, loads details ONLY when needed

### 2. **Modular Documentation (Load on Demand)**
```
docs/
├── QUICK_REF.md          # <2KB - Always load first
├── backend/              # Load only when working on backend
├── frontend/             # Load only when working on frontend
├── guides/               # Load specific guide when needed
└── status/               # Load only when asked about status
```

### 3. **Smart Context Protocol**

**❌ OLD WAY (Wasteful):**
```
Session starts → Load all 15 MD files → 30K+ tokens → Expensive
```

**✅ NEW WAY (Efficient):**
```
Session starts → Load QUICK_REF.md (2KB) → Work → Load specific file only if needed → Save 90% tokens
```

### 4. **Implementation Rules for Claude**

**Start of Every Session:**
1. Read `QUICK_REF.md` ONLY (2KB total)
2. Read `CLAUDE.md` for project rules
3. DO NOT read any other docs unless:
   - User asks specific question about that area
   - You need implementation details for current task
   - You're modifying code in that domain

**During Work:**
- Backend task? Load ONE backend doc needed
- Frontend task? Load ONE frontend doc needed
- Need status? Load ONE status file
- Don't load "just in case" - load when required

**Example:**
```
User: "Fix login API"
Claude:
1. ✅ Read QUICK_REF.md (know what exists)
2. ✅ Read backend/01-ARCHITECTURE.md (need auth structure)
3. ✅ Read guides/API_PATTERN.md (need API conventions)
4. ❌ DON'T read frontend docs (not needed for backend fix)
5. ❌ DON'T read product/commission docs (not related)
```

### 5. **Planning Documents (Different Approach)**

**Plans should be IMPLEMENTATION-READY:**
- ✅ Include exact file paths to create/modify
- ✅ Include exact code snippets to use
- ✅ Include exact commands to run
- ✅ Include exact tests to write

**Example of Good Plan:**
```markdown
## Step 1: Create OTP Manager
**File**: `apiserver/app/Services/Auth/OtpManager.php`
**Code Template**:
```php
declare(strict_types=1);
namespace App\Services\Auth;
final class OtpManager {
    public function __construct(private readonly CacheContract $cache) {}
    public function generate(string $credential): int { /* logic */ }
}
```
**Test**: `tests/Feature/Auth/OtpManagerTest.php`
**Command**: `php artisan test --filter=OtpManager`
```

**Why?** When implementing, Claude loads ONLY this plan section, copies template, implements. No need to load 10 other docs for context.

### 6. **Measurement & Accountability**

Track token usage per session:
```markdown
Session [Date]
- Start: Load QUICK_REF.md (500 tokens)
- Task: Login fix (loaded 2 docs, 3K tokens)
- Total: ~3.5K tokens
- Saved: ~27K tokens vs old approach (88% reduction)
```

### 7. **User Instruction**

**For user at session start:**
```
"I've loaded minimal context (QUICK_REF.md).
What specific area are you working on?
I'll load only relevant docs to save your tokens."
```

### 8. **Emergency Full Context**

If user says "full context needed", THEN load all docs.
Otherwise, default to lazy loading.

---

## Expected Results

**Before optimization:**
- Session start: 30K+ tokens
- Average session: 100K+ tokens
- User can work: ~10 sessions per budget

**After optimization:**
- Session start: 2-3K tokens (90% reduction)
- Average session: 15-20K tokens (80% reduction)
- User can work: 50+ sessions per budget (5x improvement)

---

## Action Items

1. ✅ Create `QUICK_REF.md` with all file paths + 1-line desc
2. ✅ Update `CLAUDE.md` to enforce lazy loading protocol
3. ✅ Restructure plans to be implementation-ready
4. ✅ Claude always asks "What area?" before loading docs
5. ✅ Track token savings per session

---

**Bottom Line**: Don't load what you don't need. User's money matters.
