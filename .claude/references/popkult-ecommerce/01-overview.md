# Popkult E-commerce Project - Overview

**Location**: `C:\laragon\www\iotron\popkult`

## Project Type
**Modern Laravel 12 E-commerce Platform** for creator merchandise and custom products

## Architecture
**Decoupled/Headless Architecture**:
- **Backend**: Laravel 12 REST API
- **Frontend**: Nuxt 4 (Vue 3) SPA with SSR capabilities
- **Admin**: Filament 3
- **Authentication**: Laravel Sanctum (token-based)

## Technology Stack

### Backend
- **PHP**: 8.4.11
- **Laravel**: 12 (latest, streamlined architecture)
- **Admin Panel**: Filament 3
- **Authentication**: Laravel Sanctum 4.0
- **Database**: SQLite (dev), MySQL/PostgreSQL (prod)
- **Money Handling**: MoneyPHP 4.7 (precision currency)
- **PDF**: Laravel DomPDF 3.1
- **Testing**: Pest 3.8, PHPUnit 11.5

### Frontend
- **Framework**: Nuxt 4.1.1
- **Vue**: 3.5.21
- **Styling**: Tailwind CSS 4.1.13
- **State**: Pinia 3.0
- **UI Components**: Reka UI 2.5 (headless)
- **Testing**: Vitest 3.2, Playwright 1.49

### Key Packages
```json
{
  "filament/filament": "^3.3",
  "awcodes/filament-curator": "^3.6",
  "awcodes/filament-tiptap-editor": "^3.0",
  "staudenmeir/laravel-adjacency-list": "^1.23",
  "moneyphp/money": "^4.7"
}
```

## Core Features

### 1. Product Management
- Simple and Configurable products
- Parent-child variant system
- Multi-warehouse inventory
- Advanced filtering (3-tier system)
- Stock tracking with priority
- Low stock alerts

### 2. Catalog System
- Hierarchical categories (unlimited nesting)
- Filter groups with options
- Swatch support (colors/patterns)
- Dynamic scoping for queries

### 3. Shopping Experience
- Shopping cart with tax calculation
- Multi-step checkout
- Real-time shipping estimation
- Stock validation
- Cart persistence

### 4. Order Management
- Order workflow (8 states)
- Order items with snapshots
- Invoice generation (PDF)
- Order tracking
- Email notifications

### 5. Payment System
- **Razorpay** integration (primary)
- **Cash on Delivery** support
- Signature verification
- Webhook handling
- Refund processing

### 6. Shipping & Fulfillment
- Multi-warehouse fulfillment
- Priority-based stock allocation
- **Shiprocket** integration
- **Native** provider (built-in)
- Weight-based shipping rates
- Tracking sync

### 7. Indian Market Focus
- **GST compliance** (CGST/SGST/IGST)
- Tax invoice generation
- State-based tax calculation
- Union Territory handling
- Paise-based pricing (₹)

## Key Differentiators

### 1. Multi-Warehouse Inventory
- Database-computed stock columns
- Priority-based fulfillment
- Per-warehouse low stock alerts
- Stock aggregation

### 2. Money Precision (MoneyPHP)
- Zero float arithmetic
- Paise-based storage (integers)
- Immutable Money objects
- API response format with rupees/paise

### 3. Advanced Filtering
- Three-tier architecture (Groups → Filters → Options)
- Dynamic scoping system
- Swatch support
- Category-specific filters

### 4. GST Compliance
- Automatic tax type determination
- CGST+SGST for intra-state
- IGST for inter-state
- Tax breakdown in orders

### 5. Flexible Product Variants
- Parent-child relationship
- Filter-based differentiation
- Independent pricing per variant
- Shared inventory management

## Directory Structure

```
popkult/
├── apiserver/              # Laravel backend
│   ├── app/
│   │   ├── Casts/         # Enum casts
│   │   ├── Filament/      # Admin resources
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   └── Resources/  # API transformers
│   │   ├── Models/
│   │   ├── Observers/
│   │   ├── Scoping/       # Query scopes
│   │   └── Services/      # Business logic
│   ├── config/
│   ├── database/
│   └── routes/api.php
├── client/                 # Nuxt 4 frontend
└── docs/                   # Documentation
```

## Database Schema Overview

### Core Tables
- **categories** - Hierarchical product categories
- **products** - Products with variants (parent_id)
- **product_stocks** - Multi-warehouse inventory
- **filter_groups**, **filters**, **filter_options** - 3-tier filtering
- **customers** - Customer accounts
- **addresses** - Polymorphic addresses
- **cart_customer** - Shopping cart (pivot)
- **orders**, **order_items** - Order management
- **payments** - Payment tracking
- **shipments**, **shipment_items** - Fulfillment
- **order_invoices** - Invoice records

## API Structure

### Base URL
`/api/`

### Authentication
- Sanctum token-based
- `Authorization: Bearer {token}` header
- `X-Customer-Id` header for customer context

### Key Endpoints
- Products: `/api/products/{sku}`
- Categories: `/api/categories/{url}`
- Cart: `/api/cart`
- Orders: `/api/orders`
- Addresses: `/api/addresses`
- Shipping: `/api/shipping/estimate`

## Design Patterns

### Service Layer
- **CartService** - Cart operations
- **OrderService** - Order creation
- **InvoiceService** - Invoice generation
- **MoneyService** - Currency handling
- **ShippingRateService** - Shipping calculation
- **ShipmentManager** - Shipment coordination

### Strategy Pattern
- **PaymentProviderInterface** (Razorpay, COD)
- **ShipmentProviderInterface** (Native, Shiprocket)

### Observer Pattern
- **AddressObserver** - Default address logic

### Scoping System
- **FilterScope** - Dynamic product filtering
- **CategoryScope** - Category filtering

## Money Handling

### Core Principle
**Store all prices as integers in paise (₹ × 100)**

```php
// Database: 49900 (integer)
// Display: ₹499.00

// API Response
{
  "paise": 49900,
  "rupees": "499.00",
  "formatted": "₹499.00",
  "display_value": "499.00"
}
```

### Benefits
- Zero precision loss
- Consistent calculations
- Database performance
- Tax accuracy

## GST Tax System

### Tax Types
- **NONE** (0%)
- **GST_5** (5%)
- **GST_18** (18%)
- **GST_40** (40%)

### Tax Determination
- **Intra-state**: CGST + SGST (or UTGST for UTs)
- **Inter-state**: IGST
- Based on shipping state vs warehouse state

## Order Workflow

```
PENDING → CONFIRMED → PROCESSING → SHIPPED → DELIVERED
         ↓
      CANCELLED
         ↓
      REFUNDED
```

## Strengths

1. ✅ Modern tech stack (Laravel 12, Nuxt 4, Filament 3)
2. ✅ Clean architecture (service layer, controllers, models)
3. ✅ Precise money handling (MoneyPHP)
4. ✅ Comprehensive admin (Filament 3)
5. ✅ Multi-warehouse inventory with priority
6. ✅ Indian market focus (GST, Razorpay, Shiprocket)
7. ✅ Flexible product filtering
8. ✅ API-first design
9. ✅ Type-safe enums for states
10. ✅ Well-tested (Pest + Vitest)

## Areas for Improvement

1. ⚠️ No full-text search (Algolia/Meilisearch)
2. ⚠️ No wishlist feature
3. ⚠️ No product reviews/ratings
4. ⚠️ Guest checkout requires customer record
5. ⚠️ No API versioning strategy
6. ⚠️ Limited caching implementation
7. ⚠️ Partial return workflow
8. ⚠️ Basic discount system
9. ⚠️ Queue infrastructure underutilized
10. ⚠️ Minimal event-listener usage

## Suitable For

- Creator merchandise stores
- Print-on-demand businesses
- Multi-warehouse e-commerce
- Indian market (GST compliance)
- Custom product configurators
- Small to medium catalogs (< 10K products)
- B2C focused businesses

## Key Learnings for Refactoring

### Adopt
- ✅ Service layer pattern for business logic
- ✅ MoneyService for currency precision
- ✅ Three-tier filtering architecture
- ✅ Enum-based status management
- ✅ API Resource transformers
- ✅ Observer pattern for model hooks

### Adapt
- ⚙️ Multi-warehouse inventory (if needed)
- ⚙️ GST compliance logic
- ⚙️ Shiprocket integration pattern
- ⚙️ Scoping system for filters

### Improve
- 🔧 Add full-text search
- 🔧 Implement wishlist
- 🔧 Add reviews/ratings
- 🔧 Support guest checkout
- 🔧 Add API versioning
- 🔧 Implement comprehensive caching
- 🔧 Complete return workflow
- 🔧 Enhance discount system
- 🔧 Utilize queue jobs more
- 🔧 Implement event system

## Comparison with Old Commerinity

### Similarities
- Laravel 12 backend
- Sanctum authentication
- Filament admin
- Multi-step checkout
- Order management
- Payment gateway integration
- Shipping integration

### Differences

| Feature | Popkult | Old Commerinity |
|---------|---------|-----------------|
| **Frontend** | Nuxt 4 + Reka UI | Nuxt 3 + Custom |
| **Inventory** | Multi-warehouse with priority | Basic single warehouse |
| **Money** | MoneyPHP (paise) | Float with LaravelMoney cast |
| **Filtering** | 3-tier (Groups → Filters → Options) | Basic filter_options |
| **Tax** | GST compliance (CGST/SGST/IGST) | Basic tax slab |
| **Variants** | Parent-child with filter options | Parent-child with filter options |
| **Stock** | Computed columns, constraints | Basic quantity field |
| **MLM** | ❌ Not present | ✅ Full MLM system |
| **Wallet** | ❌ Not present | ✅ Digital wallet |
| **Commission** | ❌ Not present | ✅ Incentive system |
| **Membership** | ❌ Not present | ✅ Lifecycle stages |
| **Content** | ❌ Minimal | ✅ Blog, CMS |
| **Support** | ❌ Not present | ✅ Helpdesk |
| **Recruitment** | ❌ Not present | ✅ Job postings |

### Best of Both Worlds

For the refactoring project, combine:

**From Popkult**:
- MoneyPHP precision
- Multi-warehouse inventory
- 3-tier filtering
- GST compliance
- Service layer pattern
- Scoping system

**From Old Commerinity**:
- MLM system
- Digital wallet
- Commission calculation
- Membership lifecycle
- Content management
- Support system
- Premium UI/UX design

---

**Analysis Date**: 2025-12-08
**Status**: ✅ COMPLETE
