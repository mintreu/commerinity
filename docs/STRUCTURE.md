# Documentation Structure

**Last Updated**: 2025-12-10

## Directory Layout

```
Root/
├── CLAUDE.md                           # Project instructions (ALWAYS READ)
├── README.md                           # Main project readme
│
└── docs/                               # All documentation (load on demand)
    ├── QUICK_REF.md                    # File map - START HERE (<2KB)
    ├── TOKEN_OPTIMIZATION.md           # Token saving strategy
    ├── README.md                       # Plans folder readme
    ├── PLANNING_COMPLETE.md            # Planning phase status
    │
    ├── backend/                        # Backend documentation (7 files)
    │   ├── 00-MASTER-PLAN.md           # Overall roadmap
    │   ├── 01-ARCHITECTURE.md          # System architecture
    │   ├── 03-PRODUCT-SYSTEM.md        # Product/inventory
    │   ├── 04-COMMISSION-SYSTEM.md     # Affiliate commissions
    │   ├── BUILD_BACKEND.md            # Build script
    │   ├── address-system-implementation.md
    │   └── onboarding-process.md
    │
    ├── frontend/                       # Frontend documentation (2 files)
    │   ├── 07-FRONTEND-NUXT4.md        # Architecture & components
    │   └── IMPLEMENTATION_GUIDE.md     # Implementation guide
    │
    ├── guides/                         # Quick reference guides (4 files)
    │   ├── API_PATTERN.md              # API calling patterns
    │   ├── MIDDLEWARE_FIX.md           # Auth middleware notes
    │   ├── ONBOARDING.md               # Onboarding system
    │   └── TESTING_GUIDE.md            # Testing instructions
    │
    └── status/                         # Session history (4 files)
        ├── STATUS.md                   # Current status
        ├── FINAL_STATUS.md             # Last session result
        ├── SESSION_SUMMARY.md          # Session notes
        └── TODO_NEXT_SESSION.md        # Next tasks
```

## File Count

- **Root**: 2 files (CLAUDE.md, README.md)
- **Backend**: 7 files
- **Frontend**: 2 files
- **Guides**: 4 files
- **Status**: 4 files
- **Meta**: 4 files (QUICK_REF, TOKEN_OPTIMIZATION, README, PLANNING_COMPLETE)
- **Total**: 23 files properly organized

## Token Optimization

**Old Structure** (messy root):
- 15+ MD files in root and plans/
- Claude loads all docs → 30K+ tokens per session
- Expensive, limits work time

**New Structure** (organized):
- Clean root (only 2 files)
- Grouped by domain (backend/frontend/guides/status)
- Claude loads QUICK_REF.md first → 500 tokens
- Loads specific docs only when needed → 90% savings

## Usage Protocol

1. **Session Start**: Read QUICK_REF.md (<2KB)
2. **Know What Exists**: File map shows all docs
3. **Load on Demand**: Only load docs needed for current task
4. **Save Tokens**: Work 5x longer on same budget

## Benefits

✅ Clean root directory (2 files only)
✅ Logical grouping (easy to find docs)
✅ Token optimization (90% reduction)
✅ Scalable (easy to add new docs)
✅ Clear separation (backend/frontend/guides/status)

---

**See also**: `TOKEN_OPTIMIZATION.md` for full strategy
