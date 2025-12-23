# MASTER REFACTORING PLAN
## Commerinity Pro - Enterprise-Grade MLM + E-commerce Platform

**Project Type**: Fresh rebuild with battle-tested patterns
**Timeline**: 20 weeks (5 months)
**Goal**: Enterprise-grade, tested, scalable, fast, optimized

---

## 🎯 **Project Vision**

Build a **world-class MLM + E-commerce platform** that combines:
- ✅ **Best e-commerce patterns** from Popkult (multi-warehouse, money precision)
- ✅ **Complete MLM system** from Old Commerinity (referral tree, commissions)
- ✅ **Premium UI/UX** from Old Commerinity (glassmorphism design)
- ✅ **Modern stack** (Laravel 12, Nuxt 4, Filament 4, Pest 4)
- ✅ **Enterprise standards** (80%+ test coverage, API docs, monitoring)

---

## 🏗️ **Architecture Overview**

### Backend: Laravel 12 API + Filament 4 Admin
```
Standard Laravel Structure (No Custom Packages)

app/
├── Models/{Feature}/          # Grouped models
├── Services/{Feature}/        # Business logic
├── Actions/{Feature}/         # Single-purpose operations
├── Data/{Feature}/           # DTOs
├── Enums/{Feature}/          # Type-safe constants
├── Http/Controllers/Api/{Feature}/
├── Http/Requests/{Feature}/
├── Http/Resources/{Feature}/
├── Filament/Resources/{Feature}/
└── Shared/                   # Cross-cutting concerns
```

### Frontend: Nuxt 4 + Nuxt UI
- Premium glassmorphism design (from Old Commerinity)
- Nuxt UI components (for speed)
- GSAP animations (preserved)
- TypeScript throughout

### Key Features
1. **MLM System** - Referral tree, multi-tier commissions, lifecycle stages
2. **E-commerce** - Multi-warehouse, product variants, cart, orders
3. **Digital Wallet** - P2P transfers, commissions, withdrawals
4. **Product Rewards** - Commission on product purchases ⭐
5. **Content** - Blog, CMS, pages
6. **Support** - Helpdesk, tickets, FAQ
7. **Recruitment** - Job postings, applications

---

## 📅 **20-Week Timeline**

### **PHASE 1: Foundation & Critical Systems** (Weeks 1-6)

#### Week 1-2: Project Setup & Money System
- [x] ~~Analyze reference projects~~ ✅ DONE
- [ ] Setup fresh Laravel 12 + Nuxt 4
- [ ] **Install MoneyPHP** + Create MoneyService
- [ ] Database design (all tables planned)
- [ ] Setup Filament 4 admin
- [ ] Setup testing framework (Pest 4)

#### Week 3-4: Product Catalog Foundation
- [ ] Product models (Product, ProductStock, Category)
- [ ] Filter system (3-tier: FilterGroup → Filter → FilterOption)
- [ ] ProductCreationService (Popkult approach)
- [ ] ProductUpdateService (Commerinity smart updates)
- [ ] StockService (multi-warehouse)
- [ ] Filament ProductResource
- [ ] **Tests: 100% coverage for product services**

#### Week 5-6: Cart & Order System
- [ ] Cart models + CartService
- [ ] Order models (Order, OrderItem, OrderInvoice)
- [ ] OrderService + OrderCreator
- [ ] InvoiceService (PDF generation)
- [ ] GST calculation (CGST/SGST/IGST)
- [ ] Filament OrderResource
- [ ] **Tests: 100% coverage for cart/order**

---

### **PHASE 2: MLM & Financial Systems** (Weeks 7-11)

#### Week 7-8: User Hierarchy & Membership
- [ ] User models (User, Customer, Admin, Staff, Distributor)
- [ ] Referral system (adjacency list)
- [ ] Membership models (Stage, Level, UserSubscription)
- [ ] Level task system (LevelTask, UserTaskProgress)
- [ ] MembershipService
- [ ] Filament MembershipResource
- [ ] **Tests: 100% MLM tree operations**

#### Week 9-10: Commission System ⭐
- [ ] Commission models (Commission, AffiliateCommission, TeamCommission, BusinessCommission)
- [ ] **CommissionService** with product reward integration
- [ ] **Commission calculation logic**:
  - Affiliate: Direct referral gets X% on product purchase
  - Team: Upline gets Y% based on depth
  - Business: Volume-based bonuses
  - **Product-specific commissions** (configurable per product)
- [ ] Commission reversal logic (returns/refunds)
- [ ] **Tests: 100% commission accuracy**

#### Week 11: Digital Wallet
- [ ] Wallet models (Wallet, WalletTransaction)
- [ ] WalletService (add, withdraw, transfer, P2P)
- [ ] Beneficiary account management
- [ ] KYC verification system
- [ ] Filament WalletResource
- [ ] **Tests: 100% wallet operations (with locking)**

---

### **PHASE 3: Payment & Shipping** (Weeks 12-14)

#### Week 12-13: Payment Integration
- [ ] Payment models (Payment, Transaction)
- [ ] PaymentService + Providers (interface-based):
  - RazorpayProvider
  - CashfreeProvider
  - PaytmProvider (optional)
  - CodProvider
  - **WalletProvider** (use wallet balance)
- [ ] Razorpay Payouts (commission disbursement)
- [ ] Webhook handling + signature verification
- [ ] **Tests: Payment flow end-to-end**

#### Week 14: Shipping Integration
- [ ] Shipment models (Shipment, ShipmentItem)
- [ ] ShippingService + Providers:
  - NativeProvider (weight-based calculation)
  - ShiprocketProvider (multi-carrier)
- [ ] Tracking sync
- [ ] Filament ShipmentResource
- [ ] **Tests: Shipping calculations**

---

### **PHASE 4: Content & Support** (Weeks 15-16)

#### Week 15: Content Management
- [ ] Content models (Post, Page, PostCategory)
- [ ] ContentService
- [ ] Rich text editor (Filament TipTap)
- [ ] Media library (Filament Curator)
- [ ] Filament ContentResources
- [ ] **Tests: Content CRUD**

#### Week 16: Support & Recruitment
- [ ] Support models (Ticket, TicketTopic, TicketConversation, Faq)
- [ ] TicketService
- [ ] Recruitment models (JobPosting, JobApplication)
- [ ] Filament SupportResources
- [ ] **Tests: Ticket workflow**

---

### **PHASE 5: Frontend Development** (Weeks 17-19)

#### Week 17: Nuxt 4 Setup + Design System
- [ ] Install Nuxt 4 + Nuxt UI
- [ ] Configure theme (Purple-Pink-Blue brand)
- [ ] Setup Tailwind 4
- [ ] Implement glassmorphism design system
- [ ] Dark mode setup
- [ ] Layout components (Navbar, Sidebar, Footer)

#### Week 18: E-commerce Pages
- [ ] Product listing + filters
- [ ] Product detail page
- [ ] Cart page
- [ ] Checkout flow (multi-step)
- [ ] Order tracking
- [ ] User dashboard

#### Week 19: MLM Pages
- [ ] Genealogy tree (D3.js)
- [ ] Commission dashboard
- [ ] Wallet management
- [ ] Membership upgrade
- [ ] Referral links

---

### **PHASE 6: Testing, Optimization & Launch** (Week 20)

#### Week 20: Final Polish
- [ ] Full test suite (80%+ coverage)
- [ ] API documentation (OpenAPI/Swagger)
- [ ] Performance optimization (Redis caching)
- [ ] Security audit (rate limiting, 2FA)
- [ ] Load testing
- [ ] Production deployment
- [ ] Monitoring setup (Laravel Telescope)

---

## 🎯 **Success Criteria**

### Must Have (Non-Negotiable)
- ✅ **Zero float arithmetic** (MoneyPHP precision)
- ✅ **Multi-warehouse inventory** (enterprise-grade)
- ✅ **80%+ test coverage** (safe to refactor)
- ✅ **Commission accuracy** (product rewards working)
- ✅ **API documentation** (OpenAPI/Swagger)
- ✅ **Premium UI/UX** (glassmorphism maintained)
- ✅ **Performance** (< 200ms API response)
- ✅ **Security** (rate limiting, CSRF, XSS prevention)

### Nice to Have
- ⭐ API versioning (/api/v1/)
- ⭐ Full-text search (Algolia/Meilisearch)
- ⭐ Redis caching
- ⭐ Queue workers
- ⭐ Event sourcing (audit trail)

---

## 💰 **Product Reward System Design**

### Commission on Product Purchases

**Concept**: When a user buys a product, their upline earns commissions

**Configuration**:
```php
// Products can have custom commission rates
Product:
- base_commission_rate (default: 10%)
- affiliate_commission_rate (direct referrer: 5%)
- team_commission_rates (JSON: [level1: 3%, level2: 2%, level3: 1%])
```

**Commission Calculation Flow**:
```
1. User B (child) buys Product X for ₹1,000
2. System checks Product X commission config:
   - affiliate_commission_rate: 5%
   - team_commission_rates: {1: 3%, 2: 2%, 3: 1%}

3. Commission distribution:
   - User A (direct parent): ₹50 (5% affiliate)
   - User A's parent: ₹30 (3% team level 1)
   - User A's grandparent: ₹20 (2% team level 2)
   - User A's great-grandparent: ₹10 (1% team level 3)

4. Incentive records created:
   - AffiliateCommission for User A
   - TeamCommission for ancestors

5. Credits to wallets
6. Notifications sent
```

**On Return/Refund**:
```
1. User B returns Product X
2. Refund processed: ₹1,000
3. System reverses commissions:
   - Create negative incentive records
   - Deduct from wallet balances
   - Mark originals as reversed
4. If wallet balance insufficient → Flag for admin
```

---

## 📁 **Plans Folder Structure**

```
plans/
├── 00-MASTER-PLAN.md                    # This file
├── 01-architecture.md                   # System architecture
├── 02-database-schema.md                # Complete schema design
├── 03-product-system.md                 # Product + Stock + Rewards
├── 04-commission-system.md              # MLM commission logic
├── 05-wallet-system.md                  # Digital wallet
├── 06-service-layer.md                  # Service architecture
├── 07-frontend-design.md                # UI/UX design system
├── 08-testing-strategy.md               # Comprehensive testing
├── 09-api-documentation.md              # API design
└── 10-deployment.md                     # CI/CD & deployment
```

---

## 🎯 **Immediate Next Steps**

### This Week:
1. Review this master plan
2. Approve architecture approach
3. Create detailed plans (01-10)
4. Start Week 1 implementation

### Week 1:
- Fresh Laravel 12 + Nuxt 4 setup
- Install MoneyPHP
- Create MoneyService
- Database design finalized
- First migrations created

---

**Ready to create the detailed plan documents (01-10)?**

Each will be comprehensive with:
- Code examples
- Database migrations
- Service implementations
- Test examples
- Filament resources

**Type "yes" and I'll create all 10 detailed plans!** 🚀
