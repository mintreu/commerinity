# Affiliate E-Commerce Integration Plan - Enterprise Grade

**Project**: Commerinity Pro (Mintreu Platform)
**Objective**: Integrate battle-tested e-commerce from Popkult + Affiliate features from old_project
**Approach**: Smart copying + enhancement + Affiliate integration
**Timeline**: 12-15 days (grouped, end-to-end implementation)

---

## 📋 ANALYSIS SUMMARY

### ✅ What We Have (Popkult - Production Ready)
- **Product Catalog**: Products, variants, categories, filters
- **Inventory**: Multi-warehouse stock management with generated columns
- **Orders**: Complete order workflow with status tracking
- **Payments**: Provider pattern (Razorpay, COD)
- **Shipping**: Native + Shiprocket integration
- **Money Service**: Safe arithmetic with moneyphp/money
- **Images**: Curator integration (Spatie Media Library)
- **Admin Panel**: Filament resources with exports

### ✅ What We Need (old_project - Working, Needs Testing)
- **Cart System**: Database-backed, polymorphic, guest support
- **Voucher/Coupon**: Complex rules engine, usage tracking
- **Sales/Promotions**: Auto-applied, target-specific
- **Rewards**: Affiliate points/BV on products
- **Member Pricing**: Level-based discounts
- **Commission**: Product sales → Affiliate commissions
- **Frontend**: Vue pages (shop, cart, checkout, product detail)

### ✅ What We Must Build (Affiliate Integration)
- **BV/PV System**: Business Volume on products
- **Rank Qualification**: Purchase-based rank progression
- **Team Sales Tracking**: Downline purchase volumes
- **Commission Triggers**: Product sales → commission calculation
- **Member Dashboard**: Purchase history, BV tracking, team sales
- **Reward Redemption**: Points → wallet conversion

---

## 🎯 ARCHITECTURAL DECISIONS

### 1. Core Principles
- **Copy Smart, Test Everything**: Use Popkult as-is, adapt old_project with tests
- **Affiliate-First Design**: Every product sale must trigger Affiliate logic
- **Polymorphic Everything**: Products can be cartable, orderable, commissionable
- **Service Layer**: All business logic in testable services
- **Money as Integer**: All prices in paise (no floats)
- **Event-Driven**: Orders → Events → Commissions

### 2. Integration Strategy
```
Popkult (Core E-commerce)
    ↓
+ old_project (Cart, Vouchers, Sales)
    ↓
+ Affiliate Layer (Commissions, BV, Ranks)
    ↓
= Complete Affiliate E-commerce Platform
```

### 3. Technology Stack
- **Backend**: Laravel 12, PHP 8.3
- **Money**: moneyphp/money (from Popkult)
- **Images**: Spatie Media Library + Curator
- **Admin**: Filament v4
- **Testing**: Pest v4 (100% coverage target)
- **Frontend**: Nuxt 4, Nuxt UI v4

---

## 📦 DATABASE ARCHITECTURE

### Phase 1: Product Catalog (from Popkult)
```sql
-- Core Tables (Copy from Popkult)
products (id, name, sku, url, price, status, type, parent_id, tax_slab, view_count, dimensions)
product_stocks (id, product_id, address_id, init_quantity, sold_quantity, in_stock_quantity GENERATED, in_stock GENERATED)
categories (id, parent_id, name, url, category_image_id) -- Hierarchical
filter_groups (id, name, type)
filters (id, filter_group_id, name, type)
filter_options (id, filter_id, name, swatch_value)
product_filter_options (product_id, filter_option_id) -- Pivot

-- Media Integration
media (id, name, path, type) -- Curator table
product_gallery_media (product_id, media_id) -- Product images
```

### Phase 2: Cart & Checkout (from old_project, Enhanced)
```sql
-- Cart System (Database-backed)
carts (
    id, quantity, discount,
    cartable_id, cartable_type, -- Polymorphic (Product/Service)
    ownerable_id, ownerable_type, -- Polymorphic (User/Customer)
    guest_id, guest_token, is_guest,
    timestamps
)

-- Voucher System
vouchers (
    id, name, code, starts_from, ends_till,
    usage_per_customer, coupon_usage_limit, times_used,
    condition_type, conditions JSON, -- Rule engine
    action_type, discount_amount, free_shipping,
    apply_to_shipping, end_other_rules, sort_order
)

voucher_codes (
    id, code UNIQUE, voucher_id,
    coupon_usage_limit, usage_per_user, times_used,
    starts_from, ends_till
)

voucher_code_usages (
    voucher_code_id,
    usable_id, usable_type, -- Polymorphic user tracking
    used_at
)

voucher_targets (
    voucher_id,
    targetable_id, targetable_type -- Polymorphic (Product/Category)
)

-- Sales/Promotions (Auto-applied)
sales (
    id, name, uuid, starts_from, ends_till,
    condition_type, conditions JSON,
    action_type, discount_amount,
    end_other_rules, sort_order
)

sale_products (
    id, sale_id, product_id,
    starts_from, ends_till,
    action_type, sale_price, discount_amount,
    target_id, target_type
)

sale_targets (
    sale_id,
    targetable_id, targetable_type
)
```

### Phase 3: Orders (from Popkult, Enhanced)
```sql
-- Order System
orders (
    id, uuid UNIQUE, -- Format: YYYY-XXXXXXXXXXXXX
    subtotal, discount, tax, total, -- All in paise
    quantity, voucher, tracking_id,
    status, payment_success, expire_at,
    customerable_id, customerable_type, -- Polymorphic
    billing_address_id, shipping_address_id,
    admin_notes, timestamps
)

order_items (
    id, order_id, product_id,
    product_name, product_sku, -- Snapshot
    unit_price, total_price, quantity, -- Paise
    discount, tax, has_tax,
    status, status_feedback
)

payments (
    id, order_id, amount, -- Paise
    status, provider, -- cod, razorpay, cashfree
    transaction_id, metadata JSON,
    razorpay_order_id, razorpay_payment_id,
    redirect_success_url, redirect_failure_url
)

shipments (
    id, order_id,
    pickup_address_id, delivery_address_id,
    status, provider, tracking_number,
    tracking_data JSON, activities JSON,
    cod_amount, cod_status,
    shipment_charge, last_synced_at
)

shipment_items (
    shipment_id, order_item_id, quantity
)

order_invoices (
    id, order_id, invoice_number,
    file_path, status, timestamps
)
```

### Phase 4: Affiliate Integration (NEW)
```sql
-- Product Affiliate Attributes
product_affiliate_attributes (
    id, product_id,
    bv_points UNSIGNED INT, -- Business Volume
    pv_points UNSIGNED INT, -- Personal Volume
    commission_rate DECIMAL(5,2), -- % for direct sales
    rank_qualification_bv UNSIGNED INT, -- BV for rank progression
    member_only BOOLEAN, -- Member-exclusive products
    member_discount_percent DECIMAL(5,2)
)

-- Purchase Volume Tracking
user_purchase_volumes (
    id, user_id, period, -- year-month
    personal_pv UNSIGNED INT,
    personal_bv UNSIGNED INT,
    team_pv UNSIGNED INT,
    team_bv UNSIGNED INT,
    qualifying_sales_count UNSIGNED INT,
    updated_at
)

-- Product Sale Commissions (extends MlmCommission)
product_sale_commissions (
    id, affiliate_commission_id FK,
    order_id FK, order_item_id FK,
    product_id, product_name,
    bv_earned UNSIGNED INT,
    pv_earned UNSIGNED INT
)

-- Member Pricing
member_product_pricing (
    id, product_id, stage_id,
    discount_percent DECIMAL(5,2),
    special_price INT, -- Paise
    starts_from, ends_till
)
```

### Phase 5: Engagement (from old_project)
```sql
product_engagements (
    id, product_id,
    engageable_id, engageable_type, -- Polymorphic user
    type, -- view, click, favorite, share
    metadata JSON, timestamps
)

product_wishlists (
    id, product_id,
    wishlistable_id, wishlistable_type,
    timestamps
)
```

---

## 🏗️ SERVICE ARCHITECTURE

### Core Services (Copy from Popkult)
```php
MoneyService - Safe arithmetic, formatting (moneyphp/money)
CartService - Add, update, remove, calculate totals
OrderService - Create from cart, status management
PaymentProviderInterface - Razorpay, Cashfree, COD, Native
ShippingRateService - Calculate shipping costs
InvoiceService - Generate PDF invoices
```

### New Services (Adapt from old_project)
```php
VoucherService
├── validateCode(code, user, cart): bool
├── calculateDiscount(voucher, cart): int (paise)
├── applyToCart(cart, code): void
├── trackUsage(code, user): void
└── getActiveVouchers(user?): Collection

SaleService
├── getActiveSales(product): Collection
├── calculateBestPrice(product, user?): int (paise)
├── applyAutoPromotions(cart): void
└── indexProductSales(): void (background job)

CartVoucherValidator
├── validate(cart, code): bool
├── checkConditions(cart, voucher): bool
├── checkEligibility(user, voucher): bool
└── getErrors(): array
```

### Affiliate Integration Services (NEW)
```php
ProductAffiliateService
├── calculateBV(product, quantity): int
├── calculatePV(product, quantity): int
├── getMemberPrice(product, user): int (paise)
├── trackPurchaseVolume(user, order): void
├── checkRankQualification(user): bool
└── updateTeamVolumes(user, bv, pv): void

ProductCommissionService
├── triggerCommissions(order): void
├── calculateProductCommission(orderItem, level, user): int
├── distributeToUpline(order, user, levels): void
├── createCommissionRecord(data): MlmCommission
└── notifyRecipients(commissions): void

RewardRedemptionService
├── calculateRewardPoints(order): int
├── creditRewardsToWallet(user, points): void
├── getRedemptionRate(): float (points → rupees)
└── trackRedemptionHistory(user): Collection
```

---

## 📁 FILE STRUCTURE

```
apiserver/
├── app/
│   ├── Models/
│   │   ├── Ecommerce/          # From Popkult
│   │   │   ├── Product.php
│   │   │   ├── ProductStock.php
│   │   │   ├── Category.php
│   │   │   ├── Filter.php
│   │   │   ├── Order.php
│   │   │   ├── OrderItem.php
│   │   │   ├── Payment.php
│   │   │   ├── Shipment.php
│   │   │   └── ShipmentItem.php
│   │   │
│   │   ├── Cart/               # From old_project, tested
│   │   │   ├── Cart.php
│   │   │   ├── Voucher.php
│   │   │   ├── VoucherCode.php
│   │   │   ├── VoucherCodeUsage.php
│   │   │   ├── Sale.php
│   │   │   └── SaleProduct.php
│   │   │
│   │   ├── Mlm/                # Affiliate Integration
│   │   │   ├── ProductMlmAttribute.php
│   │   │   ├── UserPurchaseVolume.php
│   │   │   ├── ProductSaleCommission.php
│   │   │   └── MemberProductPricing.php
│   │   │
│   │   └── Engagement/         # From old_project
│   │       ├── ProductEngagement.php
│   │       └── ProductWishlist.php
│   │
│   ├── Services/
│   │   ├── Ecommerce/          # From Popkult
│   │   │   ├── MoneyService.php
│   │   │   ├── CartService.php
│   │   │   ├── OrderService.php
│   │   │   ├── InvoiceService.php
│   │   │   ├── ShippingRateService.php
│   │   │   └── PaymentProviders/
│   │   │       ├── PaymentProviderInterface.php
│   │   │       ├── RazorpayPaymentProvider.php
│   │   │       ├── CashfreePaymentProvider.php
│   │   │       └── CodPaymentProvider.php
│   │   │
│   │   ├── Cart/               # From old_project, enhanced
│   │   │   ├── VoucherService.php
│   │   │   ├── SaleService.php
│   │   │   ├── CartVoucherValidator.php
│   │   │   └── CartLineService.php
│   │   │
│   │   └── Mlm/                # NEW
│   │       ├── ProductAffiliateService.php
│   │       ├── ProductCommissionService.php
│   │       └── RewardRedemptionService.php
│   │
│   ├── Http/Controllers/Api/
│   │   ├── Ecommerce/
│   │   │   ├── ProductController.php      # List, show, filters
│   │   │   ├── CategoryController.php     # Tree, products
│   │   │   ├── CartController.php         # CRUD, coupon, guest
│   │   │   ├── OrderController.php        # Create, list, show, cancel
│   │   │   ├── WishlistController.php     # Add, remove, list
│   │   │   └── CheckoutController.php     # Unified checkout
│   │   │
│   │   └── Mlm/
│   │       ├── MemberProductController.php   # Member pricing
│   │       ├── PurchaseVolumeController.php  # BV/PV tracking
│   │       └── RewardController.php          # Redemption
│   │
│   ├── Events/
│   │   ├── Ecommerce/
│   │   │   ├── OrderCreated.php
│   │   │   ├── OrderPaid.php
│   │   │   └── OrderCancelled.php
│   │   │
│   │   └── Mlm/
│   │       ├── ProductPurchased.php       # Triggers commissions
│   │       ├── PurchaseVolumeUpdated.php
│   │       └── RankQualified.php
│   │
│   ├── Listeners/
│   │   ├── Ecommerce/
│   │   │   └── HandleOrderPayment.php
│   │   │
│   │   └── Mlm/
│   │       ├── TriggerProductCommissions.php
│   │       ├── UpdatePurchaseVolume.php
│   │       ├── CheckRankQualification.php
│   │       └── CreditRewardPoints.php
│   │
│   ├── Casts/                  # Type-safe enums
│   │   ├── ProductStatusCast.php
│   │   ├── ProductTypeCast.php
│   │   ├── OrderStatusCast.php
│   │   ├── ShipmentStatusCast.php
│   │   ├── VoucherActionType.php
│   │   ├── SaleActionType.php
│   │   └── GstTaxCast.php
│   │
│   └── Traits/
│       ├── Ecommerce/
│       │   ├── HasCartable.php         # Products/Services in cart
│       │   ├── HasCartOwner.php        # Users own carts
│       │   └── HasOrderAddresses.php   # Order address capture
│       │
│       └── Mlm/
│           ├── HasAffiliateAttributes.php    # Products with BV/PV
│           ├── TracksPurchaseVolume.php # Users track volumes
│           └── EarnsProductCommissions.php # Commissions
│
├── database/
│   ├── migrations/
│   │   ├── 2025_12_27_create_products_table.php
│   │   ├── 2025_12_27_create_product_stocks_table.php
│   │   ├── 2025_12_27_create_categories_table.php
│   │   ├── 2025_12_27_create_filters_table.php
│   │   ├── 2025_12_28_create_carts_table.php
│   │   ├── 2025_12_28_create_vouchers_table.php
│   │   ├── 2025_12_28_create_sales_table.php
│   │   ├── 2025_12_29_create_orders_table.php
│   │   ├── 2025_12_29_create_payments_table.php
│   │   ├── 2025_12_29_create_shipments_table.php
│   │   ├── 2025_12_30_create_product_affiliate_attributes_table.php
│   │   ├── 2025_12_30_create_user_purchase_volumes_table.php
│   │   └── 2025_12_30_create_member_product_pricing_table.php
│   │
│   ├── factories/
│   │   ├── ProductFactory.php
│   │   ├── CategoryFactory.php
│   │   ├── OrderFactory.php
│   │   └── VoucherFactory.php
│   │
│   └── seeders/
│       ├── ProductSeeder.php
│       ├── CategorySeeder.php
│       ├── DemoEcommerceSeeder.php   # 50+ products with BV/PV
│       └── VoucherSeeder.php
│
└── tests/Feature/
    ├── Ecommerce/
    │   ├── ProductTest.php              # Catalog tests
    │   ├── CartTest.php                 # Cart CRUD
    │   ├── VoucherTest.php              # Coupon validation
    │   ├── SaleTest.php                 # Auto promotions
    │   ├── OrderTest.php                # Order creation
    │   ├── CheckoutTest.php             # Full checkout flow
    │   └── PaymentTest.php              # Payment providers
    │
    └── Mlm/
        ├── ProductCommissionTest.php    # Commission calculation
        ├── PurchaseVolumeTest.php       # BV/PV tracking
        ├── RankQualificationTest.php    # Rank progression
        └── RewardRedemptionTest.php     # Reward points

client/
├── app/
│   ├── pages/
│   │   ├── shop/
│   │   │   ├── index.vue                # Store landing
│   │   │   ├── [category].vue           # Category products
│   │   │   └── product/
│   │   │       └── [slug].vue           # Product detail
│   │   │
│   │   ├── cart/
│   │   │   └── index.vue                # Shopping cart
│   │   │
│   │   ├── checkout/
│   │   │   └── index.vue                # Unified checkout
│   │   │
│   │   ├── orders/
│   │   │   ├── index.vue                # Order history
│   │   │   └── [uuid].vue               # Order detail
│   │   │
│   │   └── member/
│   │       ├── products.vue             # Member-only products
│   │       ├── purchases.vue            # Purchase BV/PV tracking
│   │       └── rewards.vue              # Reward redemption
│   │
│   ├── components/
│   │   ├── shop/
│   │   │   ├── ProductCard.vue
│   │   │   ├── ProductGrid.vue
│   │   │   ├── ProductFilters.vue
│   │   │   ├── CategoryTree.vue
│   │   │   └── ProductMediaSlider.vue
│   │   │
│   │   ├── cart/
│   │   │   ├── CartItemCard.vue
│   │   │   ├── CartSummary.vue
│   │   │   ├── AddToCartButton.vue
│   │   │   ├── CartCounter.vue
│   │   │   └── CouponInput.vue
│   │   │
│   │   ├── checkout/
│   │   │   ├── CheckoutStepper.vue
│   │   │   ├── AddressSelector.vue
│   │   │   ├── PaymentMethodSelector.vue
│   │   │   └── OrderSummary.vue
│   │   │
│   │   └── member/
│   │       ├── MemberPriceBadge.vue
│   │       ├── BVIndicator.vue
│   │       └── RewardPointsDisplay.vue
│   │
│   └── composables/
│       ├── useProducts.ts               # Product API
│       ├── useCart.ts                   # Cart operations
│       ├── useCheckout.ts               # Checkout flow
│       ├── useOrders.ts                 # Order management
│       ├── useVouchers.ts               # Coupon validation
│       └── useMemberProducts.ts         # Member pricing
```

---

## 🚀 IMPLEMENTATION PHASES

### **Phase 1: Product Catalog Foundation (Days 1-3)**

**Goal**: Copy & test Popkult product system

#### Day 1: Models & Migrations
- **Copy from Popkult**:
  - Product, ProductStock, Category models
  - Filter, FilterGroup, FilterOption models
  - Migrations for products, categories, filters
  - Media integration (Curator)
- **Adapt**:
  - Add `declare(strict_types=1)` to all models
  - Add proper return types
  - Use Filament enum casts pattern
- **Test**:
  - ProductTest.php (20+ tests)
  - CategoryTest.php (10+ tests)
  - Stock availability tests

#### Day 2: Services & APIs
- **Copy Services**:
  - MoneyService (critical - safe arithmetic)
  - Product query scopes
- **Create Controllers**:
  - ProductController (list, show, filters)
  - CategoryController (tree, products)
- **Test**:
  - ProductApiTest.php (15+ tests)
  - CategoryApiTest.php (8+ tests)

#### Day 3: Admin Panel & Seeding
- **Filament Resources**:
  - ProductResource (CRUD, stock management)
  - CategoryResource (hierarchical)
- **Seeders**:
  - CategorySeeder (Indian product categories)
  - ProductSeeder (50+ demo products)
- **Media**:
  - Curator integration
  - Product gallery support

**Deliverables**:
- ✅ 50+ products in database
- ✅ Complete catalog API
- ✅ Admin product management
- ✅ 50+ passing tests

---

### **Phase 2: Cart & Checkout System (Days 4-6)**

**Goal**: Adapt old_project cart with enhancements

#### Day 4: Cart Models & Database
- **Copy from old_project**:
  - Cart model (polymorphic)
  - HasCartable, HasCartOwner traits
- **Enhance**:
  - Guest cart support (token-based)
  - Better validation
  - Add tests
- **Migration**:
  - carts table with polymorphic relations
  - Indexes for performance
- **Test**:
  - CartModelTest.php (15+ tests)
  - Guest cart tests

#### Day 5: Cart Service & APIs
- **CartService**:
  - add(product, quantity)
  - update(product, quantity)
  - remove(product)
  - clear()
  - getMeta(address, formatted)
  - validateCart()
- **CartController**:
  - CRUD endpoints
  - Guest credential management
  - Merge guest cart after login
- **Test**:
  - CartServiceTest.php (25+ tests)
  - CartApiTest.php (20+ tests)

#### Day 6: Checkout Integration
- **CheckoutController**:
  - Unified checkout endpoint
  - Address validation
  - Payment method selection
  - Order preview
- **Integration**:
  - Connect to existing PaymentService
  - Use existing HasTransaction trait
- **Test**:
  - CheckoutTest.php (30+ tests)
  - Full checkout flow

**Deliverables**:
- ✅ Complete cart system
- ✅ Guest cart support
- ✅ Checkout API ready
- ✅ 90+ passing tests

---

### **Phase 3: Vouchers & Promotions (Days 7-8)**

**Goal**: Implement complex voucher system from old_project

#### Day 7: Voucher System
- **Models**:
  - Voucher (template with rules)
  - VoucherCode (individual codes)
  - VoucherCodeUsage (usage tracking)
- **Migrations**:
  - vouchers, voucher_codes, voucher_code_usages tables
  - voucher_targets polymorphic pivot
- **VoucherService**:
  - validateCode(code, user, cart)
  - calculateDiscount(voucher, cart)
  - trackUsage(code, user)
- **CartVoucherValidator**:
  - Rule engine for conditions
  - Error reporting
- **Test**:
  - VoucherTest.php (40+ tests)
  - Complex validation scenarios

#### Day 8: Sales & Auto Promotions
- **Models**:
  - Sale (promotion template)
  - SaleProduct (product-specific sales)
- **Migrations**:
  - sales, sale_products, sale_targets tables
- **SaleService**:
  - getActiveSales(product)
  - calculateBestPrice(product, user)
  - applyAutoPromotions(cart)
- **Integration**:
  - Auto-apply to cart items
  - Priority handling (sort_order)
- **Test**:
  - SaleTest.php (30+ tests)
  - Auto-promotion tests

**Deliverables**:
- ✅ Voucher system working
- ✅ Auto-promotions applying
- ✅ Complex rule validation
- ✅ 70+ passing tests

---

### **Phase 4: Order System (Days 9-10)**

**Goal**: Complete order workflow with payments

#### Day 9: Order Models & Workflow
- **Copy from Popkult**:
  - Order, OrderItem models
  - Payment, Shipment models
- **Enhance**:
  - Polymorphic customer support
  - Better status tracking
  - Integrate with existing Transaction system
- **OrderService**:
  - createOrderFromCart(cart, addresses, payment_method)
  - calculateTotals(cart, voucher, address)
  - updateStatus(order, status)
  - cancelOrder(order)
- **Test**:
  - OrderTest.php (35+ tests)
  - Order status workflow

#### Day 10: Payment & Shipment Integration
- **Payment Integration**:
  - Use existing PaymentService
  - Provider switching (Razorpay, Cashfree, COD)
  - Webhook handling
- **Shipment**:
  - ShippingRateService
  - Tracking integration
- **OrderController**:
  - Create order API
  - Order history
  - Order detail
  - Cancel order
- **Test**:
  - PaymentTest.php (25+ tests)
  - ShipmentTest.php (15+ tests)

**Deliverables**:
- ✅ Complete order system
- ✅ Payment provider integration
- ✅ Shipment tracking
- ✅ 75+ passing tests

---

### **Phase 5: Affiliate Integration (Days 11-12)**

**Goal**: Add Affiliate-specific features to products

#### Day 11: BV/PV System
- **ProductMlmAttribute Model**:
  - bv_points, pv_points per product
  - commission_rate
  - rank_qualification_bv
  - member_only flag
- **UserPurchaseVolume Model**:
  - Track monthly personal/team volumes
- **ProductAffiliateService**:
  - calculateBV(product, quantity)
  - calculatePV(product, quantity)
  - trackPurchaseVolume(user, order)
  - checkRankQualification(user)
- **Member Pricing**:
  - MemberProductPricing model
  - Stage-based discounts
- **Test**:
  - ProductAffiliateTest.php (30+ tests)
  - Volume tracking tests

#### Day 12: Product Commissions
- **ProductSaleCommission Model**:
  - Extends MlmCommission
  - Links to Order/OrderItem
- **ProductCommissionService**:
  - triggerCommissions(order)
  - calculateProductCommission(orderItem, level, user)
  - distributeToUpline(order, user, levels)
- **Events & Listeners**:
  - OrderPaid → TriggerProductCommissions
  - ProductPurchased → UpdatePurchaseVolume
  - PurchaseVolumeUpdated → CheckRankQualification
- **Test**:
  - ProductCommissionTest.php (40+ tests)
  - Multi-level commission distribution

**Deliverables**:
- ✅ BV/PV tracking working
- ✅ Product commissions triggering
- ✅ Rank qualification logic
- ✅ 70+ passing tests

---

### **Phase 6: Frontend Shopping (Days 13-14)**

**Goal**: Build complete shopping UI with Nuxt 4 + Nuxt UI

#### Day 13: Shop & Product Pages
- **Copy from old_project** (adapt to Nuxt 4):
  - `/shop/index.vue` - Store landing
  - `/shop/[category].vue` - Category products
  - `/shop/product/[slug].vue` - Product detail
- **Components**:
  - ProductCard.vue (Mintreu design system)
  - ProductGrid.vue (responsive grid)
  - ProductFilters.vue (category, price, attributes)
  - ProductMediaSlider.vue (image gallery)
  - AddToCartButton.vue (with quantity selector)
  - CartCounter.vue (badge with count)
- **Composables**:
  - useProducts.ts (list, show, filters)
  - useCart.ts (add, update, remove, clear)
- **Styling**:
  - Preserve Mintreu premium glassmorphic design
  - Use Nuxt UI components for behavior only
- **Test**:
  - Manual testing in browser

#### Day 14: Cart & Checkout Pages
- **Pages**:
  - `/cart/index.vue` - Shopping cart
  - `/checkout/index.vue` - Unified checkout
  - `/orders/index.vue` - Order history
  - `/orders/[uuid].vue` - Order detail
- **Components**:
  - CartItemCard.vue (product, qty, price, remove)
  - CartSummary.vue (subtotal, discount, tax, total)
  - CouponInput.vue (apply/remove voucher)
  - CheckoutStepper.vue (address → payment → review)
  - AddressSelector.vue (select or add address)
  - PaymentMethodSelector.vue (wallet, gateway)
  - OrderSummary.vue (final review before payment)
- **Composables**:
  - useCheckout.ts (full checkout flow)
  - useOrders.ts (history, detail, cancel)
  - useVouchers.ts (validate, apply)
- **Test**:
  - End-to-end checkout flow

**Deliverables**:
- ✅ Complete shop UI
- ✅ Cart with coupons
- ✅ Checkout flow working
- ✅ Order management

---

### **Phase 7: Member Features & Polish (Day 15)**

**Goal**: Affiliate member-specific shopping features

#### Day 15: Member Dashboard & Features
- **Pages**:
  - `/member/products.vue` - Member-only products
  - `/member/purchases.vue` - BV/PV tracking dashboard
  - `/member/rewards.vue` - Reward points redemption
- **Components**:
  - MemberPriceBadge.vue (show member discount)
  - BVIndicator.vue (BV/PV earned on product)
  - PurchaseVolumeChart.vue (monthly volume chart)
  - RewardPointsDisplay.vue (available points)
  - RedeemRewardsForm.vue (convert points → wallet)
- **Composables**:
  - useMemberProducts.ts (member pricing API)
  - usePurchaseVolume.ts (volume tracking)
  - useRewards.ts (redemption logic)
- **Polish**:
  - Responsive design verification
  - Loading states everywhere
  - Error handling
  - Dark mode consistency

**Deliverables**:
- ✅ Member features complete
- ✅ Reward system working
- ✅ Full responsive design
- ✅ Production-ready UI

---

## 🧪 TESTING STRATEGY

### Backend Testing (Pest v4)
**Target**: 400+ total tests (80%+ coverage)

```php
// Feature Tests
tests/Feature/Ecommerce/
  ├── ProductTest.php (20 tests) - Catalog, filters, variants
  ├── CategoryTest.php (10 tests) - Hierarchy, products
  ├── CartTest.php (25 tests) - CRUD, guest, validation
  ├── VoucherTest.php (40 tests) - Validation, usage tracking
  ├── SaleTest.php (30 tests) - Auto promotions, pricing
  ├── OrderTest.php (35 tests) - Creation, workflow, cancel
  ├── CheckoutTest.php (30 tests) - Full checkout flow
  ├── PaymentTest.php (25 tests) - Provider switching
  └── ShipmentTest.php (15 tests) - Tracking, status

tests/Feature/Mlm/
  ├── ProductAffiliateTest.php (30 tests) - BV/PV, member pricing
  ├── ProductCommissionTest.php (40 tests) - Commission calc
  ├── PurchaseVolumeTest.php (25 tests) - Volume tracking
  ├── RankQualificationTest.php (20 tests) - Rank progression
  └── RewardRedemptionTest.php (15 tests) - Point conversion

// Unit Tests
tests/Unit/Services/
  ├── MoneyServiceTest.php (30 tests) - Arithmetic, formatting
  ├── CartServiceTest.php (25 tests) - Business logic
  ├── VoucherServiceTest.php (35 tests) - Rule engine
  └── ProductAffiliateServiceTest.php (20 tests) - BV/PV calc
```

### Frontend Testing (Manual)
- ✅ Shop browsing (categories, filters, search)
- ✅ Product detail (images, variants, add to cart)
- ✅ Cart operations (add, update, remove, coupon)
- ✅ Guest cart (token persistence, merge after login)
- ✅ Checkout flow (address, payment, review)
- ✅ Order history (list, detail, tracking)
- ✅ Member features (pricing, BV/PV, rewards)
- ✅ Responsive design (mobile, tablet, desktop)

---

## 📊 Affiliate COMMISSION LOGIC

### Product Sale Commission Flow
```
User A buys Product X (BV: 100, Price: ₹500)
    ↓
OrderPaid Event fires
    ↓
TriggerProductCommissions Listener
    ↓
ProductCommissionService::triggerCommissions(order)
    ↓
For each OrderItem:
    1. Get product BV/PV
    2. Get buyer's upline (up to 10 levels or max depth)
    3. For each upline member:
        a. Calculate commission (BV × level_rate)
        b. Check if member qualifies (active subscription, rank)
        c. Create MlmCommission record
        d. Create ProductSaleCommission record (links order)
        e. Credit wallet
    4. Track purchase volume:
        - Buyer's personal PV/BV
        - All upline members' team PV/BV
    5. Check rank qualification for all affected users
    ↓
Notifications sent to all recipients
```

### Commission Calculation Example
```php
Product: Premium Tea (BV: 150, Price: ₹750)
Buyer: User E (Level 5 in genealogy)

Network Tree:
User A (Founder)
  ├── User B (Silver, Level 1)
  │   └── User C (Bronze, Level 2)
  │       └── User D (Member, Level 3)
  │           └── User E (Member, Level 4) → BUYER
  │
  └── User F (Gold, Level 1)

Commission Rates (from LevelCommissionCalculator):
Level 1: 10% of BV = 15 BV (₹15 if 1 BV = ₹1)
Level 2: 5% of BV = 7.5 BV (₹7.50)
Level 3: 3% of BV = 4.5 BV (₹4.50)
Level 4+: 1% of BV = 1.5 BV (₹1.50)

Commissions Generated:
User D (Level 1 from E): ₹15 (10% × 150 BV)
User C (Level 2 from E): ₹7.50 (5% × 150 BV)
User B (Level 3 from E): ₹4.50 (3% × 150 BV)
User A (Level 4 from E): ₹1.50 (1% × 150 BV)

Total Distributed: ₹28.50
Company Profit: ₹750 - ₹28.50 = ₹721.50 (96.2% margin)

Purchase Volume Updates:
User E: personal_bv += 150, personal_pv += 1
User D: team_bv += 150, team_pv += 1
User C: team_bv += 150, team_pv += 1
User B: team_bv += 150, team_pv += 1
User A: team_bv += 150, team_pv += 1
```

---

## 🎯 BUSINESS RULES

### Product Setup
- **BV/PV Configuration**: Admin sets per product
- **Member Pricing**: Optional discount for subscribed members
- **Member-Only Products**: Some products require active subscription
- **Commission Rate**: Per-product or global default

### Voucher/Sale Rules
- **Vouchers**: Require code entry, usage limits, date range
- **Sales**: Auto-applied, can target specific products/categories/user levels
- **Priority**: Sales have sort_order, first match wins unless `end_other_rules=false`
- **Stacking**: Vouchers + Sales can stack (configurable)

### Order Rules
- **Expiration**: Unpaid orders expire after 60 minutes
- **Cancellation**: Users can cancel pending/confirmed orders only
- **Refunds**: Admin-initiated, triggers commission reversal
- **Address**: Captured at order time (immutable history)

### Commission Rules
- **Triggers**: Only on completed payments (not pending)
- **Qualification**: Member must have active subscription
- **Max Depth**: 10 levels or genealogy depth, whichever is lower
- **Rates**: Defined in LevelCommissionCalculator (editable)
- **Reversal**: If order refunded/cancelled, commissions reversed

### Volume Tracking
- **Personal Volume**: User's own purchases (BV/PV)
- **Team Volume**: Cumulative downline purchases
- **Period**: Tracked monthly (year-month)
- **Rank Qualification**: Based on monthly team BV + active team size

---

## 🚧 POTENTIAL CHALLENGES

### 1. Performance Optimization
**Challenge**: Large catalogs (1000+ products) with complex filtering

**Solution**:
- Database indexes on filterable columns
- Eager loading (withStockInfo, withStocks scopes)
- Query result caching (Redis)
- Pagination (20-50 items per page)
- Search via Algolia/Meilisearch (future)

### 2. Stock Synchronization
**Challenge**: Concurrent cart adds/orders causing overselling

**Solution**:
- Generated columns (in_stock_quantity, in_stock)
- CHECK constraints (sold <= init)
- Database transactions for order creation
- Pessimistic locking during checkout
- Stock reservation system (future)

### 3. Commission Calculation at Scale
**Challenge**: Large networks (10,000+ users) slow commission processing

**Solution**:
- Queue jobs for commission calculation
- Batch processing (100 commissions per job)
- Async notifications
- Background volume recalculation
- Commission caching (daily snapshots)

### 4. Cart Abandonment
**Challenge**: Guest carts expire, users lose items

**Solution**:
- 30-day guest cart retention
- Email reminders for authenticated users
- Wishlist as alternative (save for later)
- Cart recovery campaigns (future)

### 5. Voucher Abuse
**Challenge**: Users exploiting vouchers, sharing codes

**Solution**:
- Per-user usage limits
- Global usage limits
- IP tracking (optional)
- Account verification requirements
- Single-use codes for high-value vouchers

---

## 📈 SUCCESS METRICS

### Week 1 (Phase 1-2)
- ✅ 50+ products in catalog
- ✅ Complete cart system
- ✅ 140+ tests passing

### Week 2 (Phase 3-5)
- ✅ Vouchers & sales working
- ✅ Complete order system
- ✅ Affiliate commissions triggering
- ✅ 330+ tests passing

### Week 3 (Phase 6-7)
- ✅ Full shopping UI live
- ✅ Member features working
- ✅ 400+ tests passing
- ✅ Production-ready system

### Final Checklist
- [ ] User can browse products by category
- [ ] User can filter by attributes (color, size, price)
- [ ] User can add to cart (guest or authenticated)
- [ ] User can apply coupon codes
- [ ] User can complete checkout with payment
- [ ] User receives order confirmation email
- [ ] Member sees member pricing
- [ ] Member earns BV/PV on purchase
- [ ] Affiliate commissions auto-calculated
- [ ] Purchase volumes tracked monthly
- [ ] Rank qualification triggered
- [ ] Reward points credited
- [ ] Admin can manage products/orders/shipments
- [ ] All 400+ tests passing
- [ ] Frontend responsive on all devices
- [ ] Performance acceptable (<500ms API response)

---

## 🔐 SECURITY CONSIDERATIONS

### Input Validation
- All prices validated (min/max)
- Quantity limits enforced
- Voucher codes sanitized
- Address fields validated (XSS prevention)

### Payment Security
- PCI-DSS compliance via provider (Razorpay/Cashfree)
- No card data stored locally
- Webhook signature verification
- HTTPS enforced

### Affiliate Protection
- Commission calculation server-side only
- BV/PV values immutable after order
- Genealogy manipulation prevented
- Admin-only rank overrides

### Data Privacy
- Order history visible to owner only
- Purchase volumes private
- Guest carts isolated by token
- GDPR-compliant data retention

---

## 📚 DOCUMENTATION

### Developer Documentation
- **API Spec**: OpenAPI/Swagger for all endpoints
- **Service Docs**: PHPDoc for all services
- **Testing Guide**: How to run tests, coverage requirements
- **Deployment Guide**: Environment setup, migrations, seeders

### User Documentation
- **Shopping Guide**: How to browse, filter, purchase
- **Member Guide**: Member pricing, BV/PV, rewards
- **Admin Guide**: Product management, order fulfillment

---

## 🎓 LEARNING FROM REFERENCES

### From Popkult (Use As-Is)
- ✅ MoneyService pattern (moneyphp/money)
- ✅ Provider pattern (payments, shipping)
- ✅ Enum casts with Filament contracts
- ✅ Query optimization scopes
- ✅ Generated columns for stock
- ✅ Snapshot pattern for orders

### From old_project (Adapt & Test)
- ✅ Polymorphic cart system
- ✅ Guest cart with tokens
- ✅ Voucher rule engine
- ✅ Auto-promotion matching
- ✅ Affiliate incentive tracking
- ✅ Purchase volume calculation

### New Patterns (Build)
- ✅ Product BV/PV system
- ✅ Multi-level product commissions
- ✅ Rank qualification triggers
- ✅ Reward point conversion
- ✅ Member pricing tiers

---

## ✅ DEFINITION OF DONE

**Each phase complete when**:
- All models have `declare(strict_types=1)`
- All methods have return types
- All tests passing (target 80%+ coverage per phase)
- Code formatted with Pint
- Migrations run cleanly (fresh + seed)
- API endpoints documented
- Manual testing completed
- No console errors in frontend
- Responsive design verified

**Project complete when**:
- All 7 phases done
- 400+ tests passing
- Demo seeder populates 50+ products
- Full shopping flow works end-to-end
- Affiliate commissions calculating correctly
- Member features functional
- Admin can manage everything via Filament
- Performance acceptable (<500ms response)
- Security review passed
- Ready for production deployment

---

## 🚀 NEXT STEPS

1. **User Approval**: Review this plan, ask questions, approve approach
2. **Start Phase 1**: Begin Day 1 - Product catalog foundation
3. **Daily Standups**: Brief check-ins at start of each phase
4. **Iterative Testing**: Run tests after each model/service/controller
5. **Progress Tracking**: Update ACTIVITY_LOG.md daily
6. **Git Commits**: Commit after each passing test suite
7. **Demo Regularly**: Show working features as they complete

---

**Ready to build the most complete Affiliate e-commerce system?** 🎯

Let's start with **Phase 1, Day 1** - copying and adapting the Popkult product catalog! 🚀
