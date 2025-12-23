# Project Decisions & Context

**Last Updated**: 2025-12-08

---

## 🎯 **Core Decisions**

### 1. Project Approach
✅ **Fresh rebuild** (not upgrade)
✅ **Laravel standard patterns** (no fancy abstractions)
✅ **No custom packages** (all code in app/)
✅ **Can refactor later** (DTOs, DDD, etc. if needed)

### 2. Architecture
✅ **Standard Laravel 12 structure**
✅ **Grouped by feature** (app/Models/Catalogue/, app/Models/Order/, etc.)
✅ **Service layer** for business logic
✅ **No DTOs initially** (use Form Requests)
✅ **Can add DTOs later** without breaking code

### 3. Folder Structure
```
app/
├── Models/{Feature}/           # Product, Order, Commission, etc.
├── Services/{Feature}/         # Business logic
├── Actions/{Feature}/          # Single-purpose operations
├── Enums/{Feature}/           # Type-safe constants
├── Http/
│   ├── Controllers/Api/{Feature}/
│   ├── Requests/{Feature}/    # Form validation (Laravel standard)
│   └── Resources/{Feature}/   # API transformers
├── Filament/Resources/{Feature}/
├── Traits/                    # Shared behaviors
└── Support/                   # Helpers
```

**No `Domain/` folder, no `Data/` folder - Keep it simple!**

---

## 🔑 **Technology Stack**

### Backend
- **Laravel**: 12.41.1
- **PHP**: 8.3.22
- **Admin**: Filament 4.0.0
- **Testing**: Pest 4.1.6
- **Money**: moneyphp/money (precision handling)
- **Auth**: Laravel Sanctum 4.2.1
- **Database**: MySQL

### Frontend
- **Nuxt**: 4.2.1
- **Vue**: 3.x
- **UI**: Nuxt UI 4.2.1
- **Styling**: Tailwind CSS 4.1.17
- **Auth**: `@qirolab/nuxt-sanctum-authentication` ⭐
- **Animations**: GSAP (preserve from old)
- **Charts**: ECharts, D3.js (MLM tree)

---

## 🔐 **Frontend Authentication**

### Package: `@qirolab/nuxt-sanctum-authentication`

**Configuration** (from old commerinity):
```typescript
// nuxt.config.ts

modules: [
  '@qirolab/nuxt-sanctum-authentication'
],

laravelSanctum: {
  apiUrl: process.env.NUXT_PUBLIC_WEB_BASE || 'http://localhost:8000',
  authMode: 'cookie',  // Cookie-based (SPA authentication)
  userResponseWrapperKey: 'data',  // API wraps user in { data: {...} }
  sanctumEndpoints: {
    csrf: '/sanctum/csrf-cookie',
    login: '/api/login',
    logout: '/api/logout',
  },
}
```

**Usage**:
```typescript
// In components/pages
const { user, isLoggedIn, login, logout } = useSanctum()

// Login
await login({ email, password })

// Logout
await logout()

// Check auth
if (isLoggedIn.value) {
  // User is authenticated
}

// Access user
console.log(user.value.name)
```

**API Calls**:
```typescript
// Authenticated requests
const { data } = await useSanctumFetch('/api/orders')
```

---

## 🎯 **Project Type**

**MLM + E-commerce + Essential Features**

### Core Features:
1. **MLM System**
   - Referral tree (binary/unilevel)
   - Multi-tier commissions
   - Membership lifecycle (stages/levels)
   - Genealogy visualization

2. **E-commerce**
   - Multi-warehouse inventory
   - Product variants & filters
   - Cart & checkout
   - Order management
   - **Product-based rewards** ⭐

3. **Financial**
   - Digital wallet
   - Commission payouts
   - KYC verification
   - Multiple payment gateways

4. **Essential Features**
   - Content management (Blog, CMS)
   - Support system (Tickets, FAQ)
   - Recruitment module
   - Push notifications
   - Analytics dashboard

---

## 💰 **Product Reward System**

**Key Requirement**: Products can have commission rates

**Implementation**:
```php
Product:
- affiliate_commission_rate (%)
- team_commission_rates (JSON: {level1: %, level2: %, ...})
- business_volume_points (for volume-based bonuses)

When purchase happens:
→ Calculate commissions based on product rates
→ Credit to upline wallets
→ Create commission records
→ On return/refund → Reverse automatically
```

---

## 📚 **Reference Projects**

### 1. Old Commerinity (Primary Reference)
**Location**: `C:\laragon\www\mintreu\server\commerinity\`
**Use For**: MLM, Wallet, Premium UI, Content, Support
**Documentation**: `.claude/references/old-commerinity/`

### 2. Popkult (E-commerce Reference)
**Location**: `C:\laragon\www\iotron\popkult\`
**Use For**: Product system, Multi-warehouse stock, Money precision, Clean architecture
**Documentation**: `.claude/references/popkult-ecommerce/`

---

## 🎯 **Best Practices to Follow**

### Code Quality
- ✅ **Laravel standard patterns** (no over-engineering)
- ✅ **Thin models** (only relationships, scopes, casts)
- ✅ **Service layer** (business logic)
- ✅ **Form Requests** (validation)
- ✅ **API Resources** (transformers)
- ✅ **Enums** for type safety
- ✅ **Pest** for testing

### Database
- ✅ **Money as integers** (paise) using MoneyPHP
- ✅ **Multi-warehouse stock** with computed columns
- ✅ **Database constraints** (prevent invalid data)
- ✅ **Indexes** on foreign keys and query columns

### Testing
- ✅ **80%+ coverage** (required)
- ✅ **100% critical paths** (money, commissions, wallet)
- ✅ **Pest 4** syntax
- ✅ **Feature + Unit + Browser** tests

### Security
- ✅ **Rate limiting** on all APIs
- ✅ **Input validation** (Form Requests)
- ✅ **Database transactions** for critical operations
- ✅ **Row locking** for wallet operations

---

## 🚫 **What NOT to Do**

### Don't Over-Engineer
- ❌ No custom packages (unless reusing across projects)
- ❌ No DTOs initially (can add later)
- ❌ No Domain-Driven Design complexity
- ❌ No Repository pattern (Eloquent is enough)
- ❌ No Event Sourcing initially

### Don't Reinvent Wheels
- ❌ Don't build custom auth (use Sanctum)
- ❌ Don't build custom money handling (use MoneyPHP)
- ❌ Don't build custom admin (use Filament)
- ❌ Don't build custom PDF (use DomPDF)
- ❌ Don't build custom media (use Curator)

### Keep It Simple
- ❌ Start simple, refactor when needed
- ❌ Ship features, get feedback, iterate
- ❌ 80% solution is good enough to launch
- ❌ Perfect is the enemy of done

---

## 📝 **Context Files**

Store ongoing decisions and learnings here:

```
.claude/context/
├── DECISIONS.md           # This file (architectural decisions)
├── PROGRESS.md           # Week-by-week progress tracking
├── ISSUES.md             # Known issues and blockers
└── LEARNINGS.md          # What we learned during development
```

---

## 🎯 **Current Status**

- [x] Reference projects analyzed ✅
- [x] Architecture decided ✅
- [x] Master plan created ✅
- [x] Frontend auth package identified ✅
- [ ] Detailed plans (01-10) - IN PROGRESS
- [ ] Development - NOT STARTED

---

**Next**: Create detailed implementation plans (01-10)
