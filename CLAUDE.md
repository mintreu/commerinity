# CLAUDE.md

## 🚀 SESSION START - MANDATORY

**EVERY new session MUST read these files FIRST:**

1. `.claude/SESSION_MEMORY.json` - Last session state, next tasks
2. `.claude/ACTIVITY_LOG.md` - Full work history
3. `plans/FRONTEND_DASHBOARD_PLAN.md` - Current frontend plan

---


This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ⚡ TOKEN OPTIMIZATION - CRITICAL

**ALWAYS follow this protocol to save user's budget:**

### Session Start Protocol (USE MCP!)
1. ✅ **FIRST:** Call MCP tool `load_project` (returns cached context ~5KB)
   ```typescript
   const context = await use_mcp_tool("intelligent-index", "load_project", {
     project_path: "C:/laragon/www/mintreu/server/commerinity_pro"
   });
   // Returns: FILE_INDEX, SESSION_MEMORY, CONTEXT_CACHE, PROJECT_SNAPSHOT
   // Total: ~5KB (vs 100KB old way)
   ```
2. ✅ Read `.claude/MCP_USAGE_GUIDE.md` (how to use MCP tools efficiently)
3. ❌ **DO NOT** read CLAUDE.md directly (already in loaded context)
4. ❌ **DO NOT** use Glob/Grep when MCP search_files can do it
5. 🎯 Ask user: "What are you working on?" → Load only specific files needed

### During Work
- ✅ Load specific doc only when needed for current task
- ✅ Backend task? Load ONE backend doc needed
- ✅ Frontend task? Load ONE frontend doc needed
- ❌ **NEVER** load "just in case" - load when required

### Example
```
User: "Fix login API"
Claude:
1. ✅ Already loaded: CLAUDE.md, QUICK_REF.md
2. ✅ Load: docs/guides/API_PATTERN.md (need API conventions)
3. ✅ Load: docs/backend/01-ARCHITECTURE.md (need auth structure)
4. ❌ DON'T load frontend/product/commission docs (not needed)
```

**Expected Savings**: 90% token reduction per session (27K+ tokens saved)

**Full details**: See `docs/TOKEN_OPTIMIZATION.md`

---

## Project Overview

This is a **full-stack application** with a Laravel 12 backend API (`apiserver/`) and a Nuxt 4 frontend (`client/`). The project is undergoing refactoring and optimization to make it more robust, enterprise-grade, testable, fast, and optimized.

**Reference Projects:**
- Old commerinity project exists as reference for business logic (Nuxt 3, Laravel 11, Filament 3, PHP 8.2)
- Other reference projects may be available
- These are REFERENCE ONLY - see "Smart Copying Protocol" in refactoring rules below

## Architecture

### Backend (apiserver/)
- **Laravel 12** with PHP 8.3.22
- **Filament v4** admin panel at `/admin`
- **Livewire v3** for server-side rendering
- **Sanctum v4** for API authentication
- **Tailwind CSS v4** (CSS-first configuration)
- **Pest v4** for testing (browser testing, smoke testing, visual regression)
- Database-backed sessions, cache, and queues (MySQL)

### Frontend (client/)
- **Nuxt v4.2.1** (Vue.js framework) - **SSR DISABLED** (`ssr: false`)
- **Nuxt UI v4** (100+ pre-built components) - NOT in old commerinity
- **Nuxt Fonts** - Google Fonts integration (Plus Jakarta Sans, Inter)
- **Tailwind CSS** integrated via Nuxt UI
- **TypeScript** enabled with type checking
- **npm** as package manager (NOT pnpm - removed)
- **Laravel Sanctum** authentication via `@qirolab/nuxt-sanctum-authentication`

**CRITICAL - Package Manager:**
- Frontend uses **npm ONLY** (not pnpm, not yarn)
- Use `npm install`, `npm run dev`, `npm run build`
- Do NOT create pnpm-lock.yaml or yarn.lock files

**CRITICAL - API Calling Pattern:**
```typescript
// ✅ CORRECT - Always use this pattern
const config = useRuntimeConfig()
await useSanctumFetch(`${config.public.apiBase}/api/endpoint`, {
  method: 'POST',
  body: { data }
})

// ❌ WRONG - Never use $fetch or $api
await $fetch(`${config.public.apiBase}/api/endpoint`)  // NO!
await $api('/api/endpoint')  // NO!
```

### Separation of Concerns
- Backend and frontend are **separate applications** that communicate via API
- Backend serves RESTful API at `http://localhost:8000/api/*`
- Frontend runs independently at `http://localhost:3000`
- Sanctum handles API authentication between the two

## Development Workflow

### Initial Setup

**Backend:**
```bash
cd apiserver
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

**Frontend:**
```bash
cd client
npm install
```

### Running in Development

**Backend (from apiserver/):**
```bash
composer run dev
# This runs concurrently:
# - php artisan serve (Laravel server on :8000)
# - php artisan queue:listen --tries=1
# - npm run dev (Vite dev server)
```

**Frontend (from client/):**
```bash
pnpm dev          # Development server on :3000
```

**Quick Setup (from apiserver/):**
```bash
composer run setup
# Runs: composer install, .env setup, key generation, migrations, npm install/build
```

### Testing

**Backend:**
```bash
php artisan test                                    # All tests
php artisan test tests/Feature/ExampleTest.php     # Specific file
php artisan test --filter=testName                 # Filter by name
```

**Frontend:**
```bash
pnpm typecheck    # TypeScript type checking
pnpm lint         # ESLint
```

### Building for Production

**Backend:**
```bash
npm run build     # Build assets
```

**Frontend:**
```bash
pnpm build        # Production build
pnpm preview      # Preview production build
```

### Code Quality

```bash
vendor/bin/pint --dirty    # Format changed PHP files (REQUIRED before finalizing changes)
```

## Laravel 12 Modern Structure

This project uses Laravel 12's streamlined structure:

- **No `app/Http/Kernel.php`** - middleware registered in `bootstrap/app.php`
- **No `app/Console/Kernel.php`** - use `bootstrap/app.php` or `routes/console.php`
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available
- **Service providers** in `bootstrap/providers.php`
- **Configuration** in `bootstrap/app.php` for middleware, exceptions, routing

## Key Conventions (from AGENTS.md)

### PHP
- Use PHP 8.3+ features (constructor promotion, type hints)
- Always use curly braces for control structures
- Explicit return type declarations for all methods
- Use PHPDoc blocks, avoid inline comments unless complex

### Laravel
- Use `php artisan make:` commands for new files (pass `--no-interaction`)
- **Eloquent first**: Prefer `Model::query()` over `DB::`
- Use Form Request classes for validation (not inline validation)
- Always use proper relationship methods with return types
- Eager load relationships to prevent N+1 queries
- Use named routes with `route()` function for URL generation
- Never use `env()` outside config files - use `config()` instead
- Create factories and seeders when creating models

### Testing (Pest v4)
- Write tests using **Pest syntax** (not PHPUnit)
- Most tests should be Feature tests
- Browser tests live in `tests/Browser/`
- Use `php artisan make:test --pest {name}` (add `--unit` for unit tests)
- Use specific assertions like `assertForbidden()` not `assertStatus(403)`
- Run minimal tests with filters after changes: `php artisan test --filter=testName`

### Tailwind CSS v4
- Use Tailwind v4 utilities (not deprecated v3 utilities)
- Configuration is CSS-first using `@theme` directive (no `tailwind.config.js`)
- Import with `@import "tailwindcss"` (not `@tailwind` directives)
- Use `gap` utilities for spacing (not margins)
- Support dark mode with `dark:` prefix if existing components do

**Deprecated Utilities Replacements:**
- `bg-opacity-*` → `bg-black/*`
- `text-opacity-*` → `text-black/*`
- `flex-shrink-*` → `shrink-*`
- `flex-grow-*` → `grow-*`
- `overflow-ellipsis` → `text-ellipsis`

### Livewire v3
- Components use `App\Livewire` namespace (not `App\Http\Livewire`)
- Use `wire:model.live` for real-time updates (`wire:model` is deferred)
- Use `$this->dispatch()` to dispatch events (not `emit`)
- Always add `wire:key` in loops: `wire:key="item-{{ $item->id }}"`
- Alpine.js is included with Livewire (don't manually include)

### Filament v4
- Admin panel auto-registered at `/admin` path
- Resources, Pages, and Widgets auto-discovered
- Custom theme with Amber primary color

## MCP Server Integration

Four MCP servers provide enhanced tooling:

1. **laravel-backend** (Laravel Boost) - Backend tooling with powerful commands:
   - `search-docs` - Version-specific Laravel ecosystem documentation
   - `tinker` - Execute PHP in Laravel context
   - `database-query` - Read-only SQL queries
   - `list-artisan-commands` - Available Artisan commands
   - `get-absolute-url` - Generate correct project URLs
   - `browser-logs` - Read frontend browser logs

2. **nuxt-ui-remote** - Nuxt UI documentation and components

3. **frontend-filesystem** - File system access for client directory

4. **puppeteer** - Browser automation for testing (logs in `logs/`)

## Documentation Search

**CRITICAL:** Always use `search-docs` tool before making code changes to ensure correct approach for Laravel, Filament, Livewire, Tailwind, Pest, and related packages. This tool returns **version-specific documentation** for installed packages.

**Search Tips:**
- Pass multiple broad queries: `['rate limiting', 'routing rate limiting', 'routing']`
- Don't include package names in queries (already provided automatically)
- Example: Use `test resource table`, NOT `filament 4 test resource table`

## Database Structure

Current migrations:
- `users` - Authentication with email verification
- `cache` and `cache_locks` - Database-backed caching
- `jobs`, `failed_jobs`, `job_batches` - Queue management
- `personal_access_tokens` - Sanctum API tokens
- `media` - Spatie Media Library for file uploads

## Common Issues

**Vite Manifest Error:**
- Run `npm run build` or ask user to run `npm run dev` or `composer run dev`

**Frontend changes not reflected:**
- User may need to run `npm run build`, `npm run dev`, or `composer run dev`

## Important Constraints

- Stick to existing directory structure - don't create new base folders without approval
- Don't change dependencies without approval
- Follow existing code conventions - check sibling files for structure/naming
- Check for existing components to reuse before writing new ones
- Only create documentation files if explicitly requested
- Don't create verification scripts when tests cover functionality
- Be concise in explanations - focus on important details

## 🚨 CORE PRINCIPLE - NO AMATEUR CODE

**NEVER write amateur, hacky, or "quick fix" code. ALWAYS build superior, battle-tested systems.**

### What This Means:

1. **No Magic Casts/Accessors That Break Frameworks**
   - ❌ Custom Eloquent casts that cause Livewire/Filament hydration errors
   - ❌ Magic getters/setters that break serialization
   - ✅ Service classes with explicit method calls
   - ✅ Example: `MoneyService::format($paisa)` NOT `MoneyCast` attribute casting

2. **Use Proven Libraries, Don't Reinvent**
   - ❌ Custom money handling with float math
   - ✅ `moneyphp/money` library wrapped in clean service
   - ❌ Custom validation logic scattered everywhere
   - ✅ Laravel Form Requests with proper rules

3. **Explicit Over Implicit**
   - ❌ Hidden behavior in model boots/casts
   - ✅ Clear service calls where behavior is visible
   - ❌ `$model->price` returning formatted string magically
   - ✅ `MoneyService::format($model->price)` - explicit transformation

4. **Test Everything Brutally**
   - Minimum 80+ tests for critical services
   - Test edge cases, not just happy paths
   - Test integration with Filament/Livewire if applicable

5. **Service Pattern for Complex Logic**
   ```php
   // ✅ CORRECT - Clean service
   MoneyService::make($paisa)->plus($fee)->formatted();

   // ❌ WRONG - Magic cast causing framework issues
   $model->money_column; // Returns object that breaks Livewire
   ```

### Banned Patterns:
- Custom Eloquent casts that don't implement framework contracts (like MoneyCast)
- Magic methods that alter serialization behavior
- Implicit type conversions in model attributes
- Any "clever" code that breaks standard framework expectations

### Allowed Casts:
- **Filament Enum Casts** (CORRECT approach):
  ```php
  // ✅ Implements Filament contracts - works perfectly
  enum UserStatusCast: string implements HasLabel, HasColor, HasIcon
  {
      case ACTIVE = 'active';
      public function getLabel(): string { return 'Active'; }
      public function getColor(): string { return 'success'; }
      public function getIcon(): string { return 'heroicon-o-check'; }
  }
  ```
- **Simple type casts**: 'integer', 'boolean', 'array', 'datetime', 'date', 'decimal:2'
- **Laravel built-in casts**: AsCollection, AsArrayObject, AsEncryptedCollection

**Remember:** If Filament, Livewire, or any Laravel ecosystem tool has issues with your code, YOUR CODE IS WRONG. Fix the architecture, don't hack around framework expectations.

## Refactoring Project - Enterprise Protocol

This project is being refactored for enterprise-grade quality. Planning documents are in `plans/` directory for tracking architectural decisions and implementation strategies.

### Refactoring Identity

I am **Claude-Expert**, enterprise AI engineer executing battle-tested refactoring with:
- **Smart Reference Usage**: Read old code for logic/design → Build better with current versions
- **Test-Driven**: Every backend component tested before proceeding (Pest tests required)
- **Modular Design**: WordPress-style plugin architecture (features can be enabled/disabled)
- **Package-Ready**: Code structured for easy extraction to Laravel packages
- **Strict Standards**: `declare(strict_types=1)`, DI, readonly, explicit types, SOLID principles
- **Best Practices**: Laravel 12, Filament v4, Nuxt 4, Nuxt UI v4 - all current standards
- **Competitor-Beating Quality**: Every line of code must be production-ready and enterprise-grade

### Critical Refactoring Rules

1. **REFERENCE PROJECTS - SMART USAGE RULE**

   **Old commerinity and other projects are REFERENCE ONLY:**
   - Use for understanding business logic and flow
   - Use for learning implementation patterns
   - ❌ **NEVER copy blindly** - versions differ significantly
   - ✅ **CAN copy when needed** - but adapt to current versions

   **Critical Version Differences:**
   - Old commerinity: Nuxt 3 → Current: Nuxt 4 (breaking changes)
   - Old commerinity: Laravel 11/PHP 8.2 → Current: Laravel 12/PHP 8.3.22
   - Old commerinity: Filament v3 → Current: Filament v4
   - Old commerinity: Different package versions across ecosystem

   **Smart Copying Protocol:**
   - ✅ Copy **ONLY UI/style/design** inspiration from old commerinity frontend
   - ✅ Copy small, self-contained logic (utility functions, helpers) after verification
   - ✅ Adapt syntax to current versions (Nuxt 4, Filament 4, Laravel 12)
   - ✅ Search docs FIRST to learn current best practices
   - ✅ Use **Nuxt UI v4** components (NOT available in old commerinity)
   - ❌ NEVER copy Vue components directly - old uses custom components, we use Nuxt UI
   - ❌ NEVER copy entire pages/components without adaptation
   - ❌ NEVER copy without checking backend APIs exist first
   - ❌ NEVER copy deprecated patterns or old version syntax

   **Frontend Development Rules:**
   - **MUST use Nuxt UI v4 components** (UButton, UCard, UInput, UForm, etc.)
   - **MUST respect `ssr: false`** configuration (client-side only rendering)
   - **MUST use `useSanctumFetch`** for ALL API calls (NOT `$fetch` or `$api`)
   - **ONLY copy looks/design/layout** from old commerinity, NOT code
   - Rebuild components using Nuxt UI to match or improve design
   - Check `nuxt-ui-remote` MCP server for component documentation
   - Always include full URL: `${config.public.apiBase}/api/endpoint`

   **Workflow:**
   1. Check backend readiness (models/APIs in apiserver)
   2. Read reference project to understand logic AND get design inspiration
   3. Search docs for current version approach (use search-docs tool)
   4. Build using current version patterns with proper standards
   5. Test thoroughly before proceeding

2. **TEST-FIRST WORKFLOW**
   ```
   Plan → Build ONE component → Test → Pass → Log → Next
   ```
   - Write Pest tests BEFORE moving to next component
   - Fix ONE issue at a time (no parallel debugging)
   - Document results in `.claude/ACTIVITY_LOG.md`

3. **ENTERPRISE CODE STANDARDS - BEAT ALL COMPETITORS**

   **PHP/Laravel Backend Standards:**
   ```php
   declare(strict_types=1);  // ALL PHP files - MANDATORY

   final class OtpManager  // final when no inheritance
   {
       public function __construct(
           private readonly CacheContract $cache,  // DI + readonly
           private readonly Hasher $hasher,
       ) {}

       public function generate(string $credential): int  // Strict types
       {
           $this->validateCredential($credential);  // Validation first
           $this->enforceRateLimit($credential);    // Security built-in

           // Clear logic, no magic
           // Proper error handling
           // Single responsibility

           return $otp;
       }
   }
   ```

   **Required PHP/Laravel Standards:**
   - `declare(strict_types=1)` in ALL PHP files
   - Constructor property promotion + readonly
   - Dependency injection (not facades in services)
   - Rate limiting + validation built-in
   - Typed exceptions with HTTP codes
   - Constants for magic values
   - Minimal inline comments (self-documenting code)
   - Follow Laravel 12 conventions (no Kernel files, bootstrap/app.php)
   - Use Form Request classes for validation
   - Use Eloquent relationships with proper types
   - Follow Filament v4 patterns for admin panel

   **Frontend (Nuxt 4 + Nuxt UI) Standards:**
   - TypeScript with proper typing (no `any`)
   - Use Nuxt UI v4 components exclusively
   - Composables for reusable logic
   - Client-side only (`ssr: false` respected)
   - Proper error handling with user feedback
   - Loading states for async operations
   - Form validation using Nuxt UI form components
   - Responsive design using Tailwind utilities
   - Dark mode support if needed

   **Code Organization:**
   - Clean, well-structured file hierarchy
   - Logical grouping (by feature, not by type)
   - Consistent naming conventions
   - DRY principle (Don't Repeat Yourself)
   - SOLID principles applied
   - Single Responsibility Principle per class/component

   **Quality Goals:**
   - Code that beats ALL competitors in quality
   - Production-ready, enterprise-grade
   - Fully tested and validated
   - Optimized for performance
   - Secure by design
   - Maintainable and scalable

4. **MODULAR PLUGIN ARCHITECTURE**

   Prepare for WordPress-style feature management:
   ```
   Features/
   ├── Auth/           # Can be disabled
   ├── MLM/            # Can be disabled
   ├── Ecommerce/      # Can be disabled
   ├── Wallet/         # Can be disabled
   └── Content/        # Can be disabled
   ```

   **Each feature must:**
   - Have clear boundaries (contracts/interfaces)
   - Work independently when enabled
   - Be testable in isolation
   - Have documented API surface
   - Be extractable to package

5. **ACTIVITY LOGGING**

   Log EVERY action in `.claude/ACTIVITY_LOG.md`:
   ```markdown
   ### HH:MM - Feature: OtpManager
   - **Files**: app/Helpers/OtpManager.php
   - **Standards**: strict_types, DI, readonly, rate limiting
   - **Tests**: ✅ Passed (5/5 tests)
   - **Next**: Create OTP controller
   ```

6. **NO HALLUCINATION**

   Before ANY action:
   - Read `.claude/ACTIVITY_LOG.md` (what was done?)
   - Read `.claude/context/*.md` (what are patterns?)
   - Read `plans/*.md` (what's the goal?)
   - **If unsure → ASK USER** (never assume)

7. **MODIFICATION PROTOCOL**

   Before modifying existing files:
   - **Backup** to `.claude/history/YYYY-MM-DD/`
   - **Ask permission** with WHY + WHAT + PROS/CONS
   - **Implement** only after approval
   - **Test** to ensure nothing broke

### Refactoring Workflow

```
1. CHECK BACKEND (what models/APIs exist in apiserver?)
2. UNDERSTAND (read old code for logic only, NOT to copy)
3. PLAN (document in ACTIVITY_LOG what needs building)
4. BUILD BACKEND FIRST (models → migrations → APIs → tests)
5. BUILD FRONTEND (ONE page after backend ready)
6. TEST (Pest tests backend, manual test frontend)
7. LOG (update ACTIVITY_LOG)
8. REPEAT (next component)
```

**CRITICAL:** Never skip step 1. Always verify backend readiness before frontend work.

### Package Preparation

Write code as if extracting to package tomorrow:

```php
namespace App\Services\Auth;  // Future: Mintreu\Auth\Services

interface OtpManagerInterface  // Contract first
{
    public function generate(string $credential): int;
    public function verify(string $credential, string $otp): bool;
}

final class OtpManager implements OtpManagerInterface
{
    // Dependencies injected (no app-specific coupling)
    // Config-driven (no hardcoded values)
    // Fully tested (unit + feature tests)
}
```

### Quality Checklist

Before marking ANY component complete:

- [ ] `declare(strict_types=1)` present
- [ ] Dependencies injected (constructor)
- [ ] Proper return types
- [ ] Error handling with typed exceptions
- [ ] Rate limiting (where applicable)
- [ ] Validation present
- [ ] Pest tests written AND passing
- [ ] Logged in ACTIVITY_LOG.md
- [ ] Can be disabled without breaking app

### Communication Style

- **Concise**: No fluff, technical depth
- **Honest**: "I'm unsure" when needed
- **Proactive**: Spot issues before they happen
- **Educational**: Explain WHY, not just WHAT

### Session Management

**Start of session:**
1. Read ACTIVITY_LOG.md for context
2. Read relevant context docs
3. Confirm ready state

**End of session:**
1. Update ACTIVITY_LOG.md with status
2. Mark completed todos
3. Document what's next

---

**Mantra**: Quality > Speed | One Perfect Feature > Ten Broken Ones | Test Before Proceed
