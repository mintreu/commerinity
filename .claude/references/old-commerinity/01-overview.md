# Old Commerinity Project - Overview

**Location**: `C:\laragon\www\mintreu\server\commerinity`

## Project Type
**Comprehensive Affiliate + E-commerce Platform** built with Laravel 12 + Nuxt 3

## Architecture
**Decoupled Monorepo Architecture**:
- **Backend** (`backend/`): Laravel API + Filament Admin Panel
- **Frontend** (`frontend/`): Nuxt 3 SPA (SSR disabled)
- **Communication**: REST API via Laravel Sanctum (cookie-based authentication)

## Technology Stack

### Backend
- **PHP**: 8.3
- **Laravel**: 12.0
- **Admin Panel**: Filament 3.3
- **Authentication**: Laravel Sanctum 4.0
- **Database**: SQLite (dev), MySQL/PostgreSQL (prod)
- **Rich Text**: Filament TipTap Editor
- **Media**: Spatie Media Library

### Frontend
- **Framework**: Nuxt 3.17.6
- **Vue**: 3.5.17
- **Styling**: Tailwind CSS 3.4.17
- **Charts**: ECharts + D3.js (for Affiliate tree visualization)
- **PWA**: @vite-pwa/nuxt
- **Animations**: GSAP 3.13.0

### Custom Packages (11 Total)
1. **mintreu/toolkit** - Core utilities (unique IDs, publishable status)
2. **mintreu/laravel-money** - Monetary precision handling
3. **mintreu/laravel-category** - Product categorization
4. **mintreu/laravel-geokit** - Location/address management
5. **mintreu/laravel-product-catalogue** - Product management
6. **mintreu/laravel-commerinity** - E-commerce core (cart, vouchers, sales)
7. **mintreu/laravel-transaction** - Payment gateway integration
8. **mintreu/laravel-recruitment** - Job postings
9. **mintreu/laravel-helpdesk** - Support tickets
10. **mintreu/laravel-penpress** - Blog/CMS
11. **mintreu/laravel-integration** - Third-party integrations

### Payment Gateways
- Razorpay (primary)
- Cashfree
- Paytm
- Razorpay Payouts (commission disbursements)

### Third-Party Integrations
- **SMS**: Fast2SMS
- **Shipping**: Shiprocket
- **Social Login**: Laravel Socialite

## Core Features

### 1. Multi-Level Marketing (Affiliate)
- Hierarchical user tree (binary/unilevel structure)
- Referral system with codes
- Lifecycle stages & levels (Bronze, Silver, Gold, etc.)
- Membership subscriptions with payment
- Level task achievements
- Commission system:
  - Affiliate incentives (direct referral)
  - Team incentives (downline performance)
  - Business incentives (volume-based)
- Genealogy tree visualization (D3.js)

### 2. E-commerce
- Product catalog (simple, wholesale, configurable, downloadable, proxy)
- Product variants & tiers (bulk pricing)
- Shopping cart (guest + authenticated)
- Order management
- Wishlist
- Reviews & ratings (with nested replies)
- Vouchers & sales campaigns
- Flash deals

### 3. Financial System
- Digital wallet (add money, withdraw, P2P transfer)
- Payment gateway integration
- Transaction history
- Commission disbursements
- Beneficiary account management (bank/UPI)
- KYC verification

### 4. Content Management
- Blog posts (polymorphic authors)
- CMS pages
- Rich text editor (TipTap)
- SEO optimization

### 5. Support & HR
- Help desk system (tickets, conversations, FAQ)
- Job postings & applications

### 6. Notifications
- Database notifications
- Email notifications
- Web push notifications (VAPID)

## Database Schema
- **65+ migrations**
- **30+ core models** plus package models
- Key tables: users, products, orders, carts, wallets, transactions, incentives, stages, levels

## Directory Structure
```
commerinity/
├── backend/          # Laravel API + Admin
├── frontend/         # Nuxt 3 SPA
├── docs/            # Comprehensive documentation
├── plans/           # Project planning
└── .gemini/         # AI agent workspace
```

## Critical Issues Identified

### 1. CRITICAL: Money Handling Bug
- `LaravelMoneyCast::set()` has conversion logic commented out
- Monetary values stored as floats (precision errors)
- **Impact**: Financial calculation errors

### 2. CRITICAL: Lack of Testing
- Most custom packages have no automated tests
- High risk for regressions
- Difficult to refactor safely

### 3. HIGH: Documentation Gaps
- Package READMEs are templates with no practical info
- Missing API documentation
- No architecture decision records

### 4. MEDIUM: Model Complexity
- User model has 15+ traits (violates SRP)
- Difficult to maintain and debug

### 5. MEDIUM: Missing Features
- No API versioning
- Basic error handling
- No rate limiting

## Refactoring Priorities

1. **Fix money precision bug** (CRITICAL)
2. **Implement comprehensive testing** (CRITICAL)
3. **Add API documentation** (HIGH)
4. **Refactor heavy models** (MEDIUM)
5. **Add API versioning** (MEDIUM)
6. **Enhance error handling** (MEDIUM)
7. **Performance optimization** (LOW)
8. **Security hardening** (ONGOING)
