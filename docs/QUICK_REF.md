# Quick Reference - Commerinity Pro

**Purpose**: Minimal context map. Load specific docs only when needed.

---

## Project Structure

```
Root/
├── CLAUDE.md                          # Project instructions (ALWAYS READ)
├── README.md                          # Project overview
├── apiserver/                         # Laravel 12 backend
├── client/                            # Nuxt 4 frontend
└── docs/                              # Documentation (load on demand)
```

---

## Documentation Map

### Backend Documentation
- `docs/backend/00-MASTER-PLAN.md` - Overall project roadmap
- `docs/backend/01-ARCHITECTURE.md` - System architecture & patterns
- `docs/backend/03-PRODUCT-SYSTEM.md` - Product/inventory system
- `docs/backend/04-COMMISSION-SYSTEM.md` - MLM commission logic
- `docs/backend/BUILD_BACKEND.md` - Backend build script
- `docs/backend/address-system-implementation.md` - Address system plan
- `docs/backend/onboarding-process.md` - User onboarding flow

### Frontend Documentation
- `docs/frontend/07-FRONTEND-NUXT4.md` - Frontend architecture & components
- `docs/frontend/IMPLEMENTATION_GUIDE.md` - Frontend implementation guide

### Guides (Quick Reference)
- `docs/guides/API_PATTERN.md` - Sanctum API calling pattern (CRITICAL for API work)
- `docs/guides/MIDDLEWARE_FIX.md` - Auth middleware fix notes
- `docs/guides/ONBOARDING.md` - Onboarding system docs
- `docs/guides/TESTING_GUIDE.md` - How to test backend & frontend

### Status Files (Session History)
- `docs/status/STATUS.md` - Current project status
- `docs/status/FINAL_STATUS.md` - Last session final state
- `docs/status/SESSION_SUMMARY.md` - Previous session summary
- `docs/status/TODO_NEXT_SESSION.md` - Next session tasks

### Meta
- `docs/README.md` - Plans folder readme
- `docs/PLANNING_COMPLETE.md` - Planning phase completion
- `docs/TOKEN_OPTIMIZATION.md` - Token saving strategy (READ THIS)

---

## Quick Tech Stack

**Backend**: Laravel 12, PHP 8.3.22, Filament v4, Livewire v3, Sanctum v4, Pest v4, Tailwind CSS v4
**Frontend**: Nuxt 4, Nuxt UI v4, TypeScript, pnpm, SSR disabled
**Database**: MySQL (sessions, cache, queues)
**Testing**: Pest v4 (backend), Manual (frontend)

---

## Common Tasks → Load These Docs

| Task | Load |
|------|------|
| Backend API work | `API_PATTERN.md` + relevant backend doc |
| Frontend component | `07-FRONTEND-NUXT4.md` + `API_PATTERN.md` |
| Auth/Login issues | `01-ARCHITECTURE.md` + `MIDDLEWARE_FIX.md` |
| Testing | `TESTING_GUIDE.md` |
| Project status | `status/STATUS.md` |
| New feature planning | `00-MASTER-PLAN.md` + relevant system doc |

---

## Session Start Protocol

1. ✅ Read `CLAUDE.md` (project rules)
2. ✅ Read `QUICK_REF.md` (this file - you know what exists)
3. ❌ DO NOT load other docs until needed for specific task
4. 🎯 Ask user: "What are you working on?" → Load only relevant docs

**Token Savings**: ~27K tokens saved per session start (90% reduction)

---

**Last Updated**: 2025-12-10
