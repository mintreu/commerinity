# Commerinity Pro - Refactoring Plans

**Project**: Enterprise-Grade MLM + E-commerce Platform
**Approach**: Fresh rebuild with battle-tested patterns
**Timeline**: 20 weeks (5 months)
**Status**: 🟢 Ready to start development

---

## 📁 **Plans Overview**

### ✅ **Completed Plans**

1. **00-MASTER-PLAN.md** - Overall strategy, 20-week timeline, success criteria
2. **01-ARCHITECTURE.md** - System design, folder structure, technology stack
3. **03-PRODUCT-SYSTEM.md** - Product catalog with multi-warehouse & rewards
4. **04-COMMISSION-SYSTEM.md** - MLM commission calculation with reversal

### 📋 **To Be Created** (As Needed During Development)

5. **02-DATABASE-SCHEMA.md** - Complete migrations for all tables
6. **05-WALLET-SYSTEM.md** - Digital wallet with P2P transfers
7. **06-SERVICE-LAYER.md** - All services documented
8. **07-FRONTEND-DESIGN.md** - Nuxt UI theme configuration
9. **08-TESTING-STRATEGY.md** - Pest test coverage plan
10. **09-API-DOCUMENTATION.md** - API endpoints specification
11. **10-DEPLOYMENT.md** - Production deployment guide

---

## 🎯 **Core Decisions**

### Architecture
- ✅ **Standard Laravel** (no custom packages)
- ✅ **Feature-grouped structure** (`app/Models/Catalogue/`, etc.)
- ✅ **Service layer** for business logic
- ✅ **No DTOs initially** (Form Requests only)
- ✅ **Can refactor later** when needed

### Technology Stack
- **Backend**: Laravel 12 + Filament 4 + Pest 4
- **Frontend**: Nuxt 4 + Nuxt UI + Tailwind 4
- **Auth**: Laravel Sanctum + `@qirolab/nuxt-sanctum-authentication`
- **Money**: MoneyPHP (paise as integers)
- **Database**: MySQL

### Reference Projects
- **Old Commerinity**: MLM, Wallet, Premium UI
- **Popkult**: Product system, Multi-warehouse, Clean architecture

---

## 🚀 **Quick Start**

### Read Plans in Order:
1. Start with **00-MASTER-PLAN.md** (big picture)
2. Read **01-ARCHITECTURE.md** (structure)
3. Read **03-PRODUCT-SYSTEM.md** (product implementation)
4. Read **04-COMMISSION-SYSTEM.md** (MLM rewards)

### Context Files
- `.claude/context/DECISIONS.md` - Architectural decisions
- `.claude/references/` - Reference project analysis (80+ pages)

---

## 📊 **Key Features**

### E-commerce
- Multi-warehouse inventory
- Product variants (configurable products)
- 3-tier filtering (FilterGroup → Filter → FilterOption)
- Shopping cart
- Order management
- Payment gateways (Razorpay, COD)
- Shipping (Native + Shiprocket)
- GST compliance

### MLM (Network Marketing)
- Referral tree (adjacency list)
- **Product-based commissions** ⭐
- Membership lifecycle (stages/levels)
- Commission reversal (returns/refunds)
- Genealogy tree visualization

### Financial
- Digital wallet
- P2P transfers
- Commission payouts
- KYC verification
- Multiple payment methods

### Essential
- Content management
- Support tickets
- Job recruitment
- Analytics dashboard
- Push notifications

---

## 💡 **Implementation Principles**

### Keep It Simple
- Laravel standard patterns
- No over-engineering
- Ship features, iterate
- 80% solution is good enough

### Quality Standards
- 80%+ test coverage
- MoneyPHP precision (zero floats)
- Type safety everywhere
- API documentation

### Performance
- Query scopes (N+1 prevention)
- Database indexes
- Redis caching
- Queue jobs

---

## 📈 **Progress Tracking**

Track weekly progress in:
- `.claude/context/PROGRESS.md`

---

**Created**: 2025-12-08
**Status**: ✅ Ready for development
**Next**: Start Week 1 implementation
