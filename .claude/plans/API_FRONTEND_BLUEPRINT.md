# API & Frontend Architecture Blueprint
## Enterprise-Grade Commerinity Pro - Full Stack Plan

**Date**: 2025-12-08
**Version**: 1.0
**Status**: Comprehensive Planning Phase

---

## 📋 Table of Contents

1. [Authentication & Security](#authentication--security)
2. [Two-Factor Authentication (2FA)](#two-factor-authentication-2fa)
3. [API Endpoint Structure](#api-endpoint-structure)
4. [Frontend Navigation (Nuxt 4)](#frontend-navigation-nuxt-4)
5. [WebPush Notifications](#webpush-notifications)
6. [Performance Optimization](#performance-optimization)

---

## 🔐 Authentication & Security

### **Authentication Flow**

```
┌─────────────────────────────────────────────────────────────┐
│                    AUTHENTICATION LAYERS                     │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Layer 1: Primary Auth (REQUIRED)                           │
│  ├── Mobile + OTP (Registration)                            │
│  ├── Mobile/Email + Password (Login)                        │
│  └── Mobile + OTP (Passwordless Login)                      │
│                                                              │
│  Layer 2: Two-Factor Auth (OPTIONAL - User Enabled)         │
│  ├── OTP via Mobile (SMS)                                   │
│  ├── OTP via Email                                          │
│  ├── Authenticator App (TOTP - Google/Microsoft)           │
│  └── Backup Codes (Recovery)                                │
│                                                              │
│  Layer 3: Session Management                                │
│  ├── Sanctum Bearer Tokens                                  │
│  ├── Multi-Device Sessions                                  │
│  ├── Device Tracking & Management                           │
│  └── Suspicious Activity Detection                          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### **Mobile-First Strategy**

```php
Primary: Mobile (required, unique, E.164 format)
Secondary: Email (optional, nullable, unique)
Password: Required for login (optional for OTP-only users)
```

**Rationale**:
- Mobile penetration in India: 95%+
- SMS OTP delivery: More reliable than email
- Mobile number verification: Stronger identity proof
- Email: Added for convenience, not mandatory

---

## 🛡️ Two-Factor Authentication (2FA)

### **System Architecture**

```
┌────────────────────────────────────────────────┐
│           2FA SECURITY LAYER                   │
├────────────────────────────────────────────────┤
│                                                 │
│  User Settings: Enable/Disable 2FA             │
│  ├── Default: Disabled (opt-in security)       │
│  └── Admin force-enable for sensitive accounts │
│                                                 │
│  Methods (User Choice):                        │
│  ├── 1. SMS OTP (Primary)                      │
│  ├── 2. Email OTP (Alternative)                │
│  ├── 3. Authenticator App (TOTP)               │
│  └── 4. Backup Codes (Recovery)                │
│                                                 │
│  Flow:                                          │
│  1. User logs in (username + password)         │
│  2. IF 2FA enabled → Request 2FA challenge     │
│  3. User provides 2FA code                     │
│  4. Verify 2FA → Issue Sanctum token           │
│  5. Remember device (optional, 30 days)        │
│                                                 │
└────────────────────────────────────────────────┘
```

### **Database Schema** (New Migration)

```php
// 2025_12_08_create_two_factor_auth_table.php

Schema::create('two_factor_auth', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // 2FA Settings
    $table->boolean('enabled')->default(false);
    $table->string('method')->nullable(); // sms, email, totp
    $table->timestamp('enabled_at')->nullable();

    // TOTP (Authenticator App)
    $table->text('totp_secret')->nullable(); // Encrypted
    $table->text('backup_codes')->nullable(); // JSON, encrypted

    // Recovery
    $table->integer('backup_codes_used')->default(0);
    $table->timestamp('last_backup_generated_at')->nullable();

    // Trusted Devices
    $table->timestamps();

    $table->index(['user_id', 'enabled']);
});

Schema::create('trusted_devices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->string('device_fingerprint', 64)->unique(); // Browser + OS + IP hash
    $table->string('device_name')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();

    $table->timestamp('trusted_at');
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at'); // 30 days default

    $table->timestamps();

    $table->index(['user_id', 'device_fingerprint']);
    $table->index('expires_at');
});
```

### **2FA API Endpoints**

```
POST   /api/auth/2fa/enable               Enable 2FA (choose method)
POST   /api/auth/2fa/disable              Disable 2FA
GET    /api/auth/2fa/status               Get 2FA status
POST   /api/auth/2fa/verify-setup         Verify TOTP setup
GET    /api/auth/2fa/backup-codes         Generate backup codes
POST   /api/auth/2fa/verify               Verify 2FA challenge
POST   /api/auth/2fa/trust-device         Trust current device
GET    /api/auth/devices                  List trusted devices
DELETE /api/auth/devices/{id}             Revoke device trust
```

### **Implementation Classes**

```
app/
├── Helpers/
│   ├── OtpManager.php ✅ (Already exists)
│   └── TwoFactorManager.php (NEW)
├── Models/
│   ├── TwoFactorAuth.php (NEW)
│   └── TrustedDevice.php (NEW)
├── Http/Controllers/Api/Auth/
│   └── TwoFactorController.php (NEW)
├── Http/Middleware/
│   └── RequireTwoFactor.php (NEW)
└── Notifications/
    └── TwoFactorCodeNotification.php (NEW)
```

---

## 🌐 API Endpoint Structure

### **RESTful API Design Principles**

```
Base URL: https://api.commerinity.com/v1
Pattern: {base}/{version}/{resource}/{action}
```

### **API Versioning Strategy**

```
v1/ (Current - Stable)
├── auth/           Authentication endpoints
├── user/           User profile & settings
├── mlm/            MLM tree & commissions
├── shop/           E-commerce (products, orders)
├── wallet/         Transactions & wallet
├── notifications/  Push & in-app notifications
└── admin/          Admin operations

v2/ (Future - Breaking changes)
└── (Same structure, backward incompatible changes)
```

### **Complete API Endpoint Map**

#### **🔓 Public Endpoints** (No Auth Required)

```
┌─────────────────────────────────────────────────────────┐
│ AUTHENTICATION & REGISTRATION                           │
├─────────────────────────────────────────────────────────┤

POST   /api/v1/auth/send-otp                Send OTP (mobile/email)
POST   /api/v1/auth/verify-otp              Verify OTP
POST   /api/v1/auth/register                Register new user
POST   /api/v1/auth/login                   Login (password)
POST   /api/v1/auth/login-otp               Login (OTP)
POST   /api/v1/auth/forgot-password         Request password reset (email)
POST   /api/v1/auth/forgot-password-mobile  Request password reset (mobile)
POST   /api/v1/auth/reset-password          Reset password (email token)
POST   /api/v1/auth/reset-password-mobile   Reset password (mobile OTP)
GET    /api/v1/auth/vapid-public-key        Get VAPID key for WebPush

┌─────────────────────────────────────────────────────────┐
│ PUBLIC DATA (Guest Access)                              │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/shop/products                List products (paginated)
GET    /api/v1/shop/products/{sku}          Product details
GET    /api/v1/shop/categories              List categories
GET    /api/v1/shop/featured                Featured products
GET    /api/v1/shop/deals                   Hot deals
GET    /api/v1/blog/posts                   Blog posts
GET    /api/v1/pages/{slug}                 Static pages
GET    /api/v1/config                       App configuration
```

#### **🔒 Protected Endpoints** (Auth Required - auth:sanctum)

```
┌─────────────────────────────────────────────────────────┐
│ USER PROFILE & SETTINGS                                 │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/user                         Current user data
PUT    /api/v1/user/profile                 Update profile
POST   /api/v1/user/avatar                  Upload avatar
PUT    /api/v1/user/mobile                  Change mobile (requires OTP)
PUT    /api/v1/user/email                   Change email (requires verification)
PUT    /api/v1/user/password                Change password
DELETE /api/v1/user/account                 Delete account

┌─────────────────────────────────────────────────────────┐
│ TWO-FACTOR AUTHENTICATION (2FA)                         │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/auth/2fa/status              Get 2FA status
POST   /api/v1/auth/2fa/enable              Enable 2FA
POST   /api/v1/auth/2fa/disable             Disable 2FA
POST   /api/v1/auth/2fa/verify-setup        Verify TOTP setup
GET    /api/v1/auth/2fa/backup-codes        Generate backup codes
POST   /api/v1/auth/2fa/verify              Verify 2FA challenge
POST   /api/v1/auth/2fa/trust-device        Trust device (30 days)
GET    /api/v1/auth/devices                 List trusted devices
DELETE /api/v1/auth/devices/{id}            Revoke device

┌─────────────────────────────────────────────────────────┐
│ SESSION & TOKEN MANAGEMENT                              │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/auth/sessions                List active sessions
DELETE /api/v1/auth/sessions/{id}           Revoke session
POST   /api/v1/auth/logout                  Logout current device
POST   /api/v1/auth/logout-all              Logout all devices
POST   /api/v1/auth/refresh-token           Refresh token (optional)

┌─────────────────────────────────────────────────────────┐
│ MLM SYSTEM (Type: PROMOTER, ADVISOR, MENTOR)           │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/mlm/tree                     My MLM tree (ancestors + descendants)
GET    /api/v1/mlm/downline                 Direct downline
GET    /api/v1/mlm/team-stats               Team statistics
GET    /api/v1/mlm/commissions              Commission history
GET    /api/v1/mlm/earnings                 Earnings summary
POST   /api/v1/mlm/validate-referral        Validate referral code

┌─────────────────────────────────────────────────────────┐
│ E-COMMERCE CART & ORDERS (All User Types)              │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/cart                         Get cart
POST   /api/v1/cart/add                     Add to cart
PUT    /api/v1/cart/update/{id}             Update quantity
DELETE /api/v1/cart/remove/{id}             Remove item
DELETE /api/v1/cart/clear                   Clear cart

POST   /api/v1/orders/create                Create order
GET    /api/v1/orders                       Order history
GET    /api/v1/orders/{id}                  Order details
POST   /api/v1/orders/{id}/cancel           Cancel order

GET    /api/v1/wishlist                     Get wishlist
POST   /api/v1/wishlist/add                 Add to wishlist
DELETE /api/v1/wishlist/remove/{id}         Remove from wishlist

┌─────────────────────────────────────────────────────────┐
│ WALLET & TRANSACTIONS                                   │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/wallet                       Wallet balance
GET    /api/v1/wallet/transactions          Transaction history
POST   /api/v1/wallet/withdraw              Request withdrawal
POST   /api/v1/wallet/transfer              Transfer to another user

┌─────────────────────────────────────────────────────────┐
│ NOTIFICATIONS & WEBPUSH                                 │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/notifications                Get notifications
PUT    /api/v1/notifications/{id}/read      Mark as read
POST   /api/v1/notifications/read-all       Mark all as read
DELETE /api/v1/notifications/{id}           Delete notification

POST   /api/v1/push/subscribe               Subscribe to WebPush
POST   /api/v1/push/unsubscribe             Unsubscribe from WebPush
GET    /api/v1/push/subscriptions           List subscriptions
POST   /api/v1/push/test                    Test push notification

┌─────────────────────────────────────────────────────────┐
│ ADDRESSES & SHIPPING                                    │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/addresses                    List addresses
POST   /api/v1/addresses                    Create address
PUT    /api/v1/addresses/{id}               Update address
DELETE /api/v1/addresses/{id}               Delete address
POST   /api/v1/addresses/{id}/default       Set default address
```

#### **🎯 Role-Based Endpoints**

```
┌─────────────────────────────────────────────────────────┐
│ MEMBER-ONLY (Type: MEMBER - Subscribed Users)          │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/membership/status            Subscription status
GET    /api/v1/membership/benefits          Membership benefits
POST   /api/v1/membership/renew             Renew subscription
POST   /api/v1/membership/upgrade           Upgrade plan

┌─────────────────────────────────────────────────────────┐
│ PROMOTER-ONLY (Type: PROMOTER - MLM Participants)      │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/promoter/dashboard           Promoter dashboard data
GET    /api/v1/promoter/performance         Performance metrics
GET    /api/v1/promoter/leaderboard         Team leaderboard
POST   /api/v1/promoter/invite              Invite new member

┌─────────────────────────────────────────────────────────┐
│ ADVISOR-ONLY (Type: ADVISOR - Company Agents)          │
├─────────────────────────────────────────────────────────┤

GET    /api/v1/advisor/recruits             List recruited members
GET    /api/v1/advisor/performance          Recruitment performance
POST   /api/v1/advisor/assign-team          Assign member to team
GET    /api/v1/advisor/commission           Salary & commission

┌─────────────────────────────────────────────────────────┐
│ ADMIN (Filament Panel - Separate)                      │
├─────────────────────────────────────────────────────────┤

Web-based Filament admin panel (not API)
URL: /admin (Laravel routes, not API)
```

---

## 🗺️ Frontend Navigation (Nuxt 4)

### **Navigation Architecture**

```
┌───────────────────────────────────────────────────────────┐
│                 NAVIGATION STRUCTURE                       │
│                    (Type-Based UI)                         │
├───────────────────────────────────────────────────────────┤
│                                                            │
│  Guest Navigation (Unauthenticated)                       │
│  ├── Home                                                 │
│  ├── Shop (Products, Categories, Search)                 │
│  ├── About Us                                             │
│  ├── Contact                                              │
│  ├── Blog                                                 │
│  ├── Join Us (Recruitment landing)                       │
│  ├── Login                                                │
│  └── Register                                             │
│                                                            │
│  REGULAR User (type: regular)                            │
│  ├── Home                                                 │
│  ├── Shop                                                 │
│  ├── My Orders                                            │
│  ├── Wishlist                                             │
│  ├── Cart                                                 │
│  ├── Account                                              │
│  │   ├── Profile                                          │
│  │   ├── Security (2FA, Password)                        │
│  │   ├── Addresses                                        │
│  │   └── Settings                                         │
│  └── Logout                                               │
│                                                            │
│  MEMBER User (type: member - Subscribed)                 │
│  ├── All REGULAR features +                              │
│  ├── Dashboard (Membership benefits)                     │
│  ├── Membership Status                                    │
│  ├── Exclusive Deals                                      │
│  └── Community Access                                     │
│                                                            │
│  PROMOTER User (type: promoter - MLM Active)             │
│  ├── All MEMBER features +                               │
│  ├── MLM Dashboard                                        │
│  │   ├── Team Tree View                                  │
│  │   ├── Downline List                                   │
│  │   ├── Commission Reports                              │
│  │   └── Performance Analytics                           │
│  ├── Invite & Share                                       │
│  ├── Training Materials                                   │
│  └── Wallet & Earnings                                    │
│                                                            │
│  ADVISOR User (type: advisor - Company Agent)            │
│  ├── All PROMOTER features +                             │
│  ├── Recruitment Dashboard                               │
│  ├── Assign Members to Teams                             │
│  ├── Performance Tracking                                 │
│  └── Salary Reports                                       │
│                                                            │
│  MENTOR User (type: mentor - Trainer)                    │
│  ├── Training Dashboard                                   │
│  ├── Assigned Trainees                                    │
│  ├── Training Materials Management                       │
│  └── Progress Tracking                                    │
│                                                            │
└───────────────────────────────────────────────────────────┘
```

### **Nuxt 4 File Structure**

```
client/
├── app/
│   ├── layouts/
│   │   ├── default.vue           # Guest + REGULAR users
│   │   ├── auth.vue              # Login/Register pages
│   │   ├── dashboard.vue         # MEMBER/PROMOTER/ADVISOR
│   │   └── admin.vue             # MENTOR (if needed)
│   │
│   ├── pages/
│   │   ├── index.vue                      # Homepage (guest)
│   │   ├── login.vue                      # Login page
│   │   ├── register.vue                   # Registration
│   │   ├── forgot-password.vue            # Password reset
│   │   │
│   │   ├── shop/
│   │   │   ├── index.vue                  # Product listing
│   │   │   ├── [sku].vue                  # Product details
│   │   │   └── category/[slug].vue        # Category products
│   │   │
│   │   ├── cart.vue                       # Shopping cart
│   │   ├── checkout.vue                   # Checkout
│   │   ├── wishlist.vue                   # Wishlist
│   │   │
│   │   ├── account/
│   │   │   ├── index.vue                  # Account overview
│   │   │   ├── profile.vue                # Edit profile
│   │   │   ├── security.vue               # 2FA, password
│   │   │   ├── addresses.vue              # Manage addresses
│   │   │   └── settings.vue               # Preferences
│   │   │
│   │   ├── orders/
│   │   │   ├── index.vue                  # Order history
│   │   │   └── [id].vue                   # Order details
│   │   │
│   │   ├── dashboard/
│   │   │   ├── index.vue                  # Main dashboard (type-based)
│   │   │   ├── membership.vue             # MEMBER only
│   │   │   ├── mlm/
│   │   │   │   ├── tree.vue               # PROMOTER MLM tree
│   │   │   │   ├── team.vue               # Downline list
│   │   │   │   ├── commissions.vue        # Commission reports
│   │   │   │   └── analytics.vue          # Performance
│   │   │   ├── advisor/
│   │   │   │   ├── recruits.vue           # ADVISOR recruited members
│   │   │   │   ├── assign.vue             # Assign to teams
│   │   │   │   └── salary.vue             # Salary reports
│   │   │   └── wallet/
│   │   │       ├── index.vue              # Wallet overview
│   │   │       ├── transactions.vue       # Transaction history
│   │   │       └── withdraw.vue           # Withdrawal request
│   │   │
│   │   ├── notifications/
│   │   │   └── index.vue                  # Notification center
│   │   │
│   │   └── about.vue                      # About page
│   │
│   ├── components/
│   │   ├── auth/
│   │   │   ├── LoginForm.vue              # Email/Mobile + Password
│   │   │   ├── RegisterForm.vue           # Multi-step registration
│   │   │   ├── OtpInput.vue               # OTP input component
│   │   │   ├── TwoFactorChallenge.vue     # 2FA verification
│   │   │   └── PasswordStrength.vue       # Password meter
│   │   │
│   │   ├── dashboard/
│   │   │   ├── DashboardCard.vue          # Stat cards
│   │   │   ├── MlmTreeView.vue            # Visual tree
│   │   │   ├── CommissionTable.vue        # Commission table
│   │   │   └── PerformanceChart.vue       # Charts
│   │   │
│   │   ├── navigation/
│   │   │   ├── Navbar.vue                 # Main navbar (type-aware)
│   │   │   ├── Sidebar.vue                # Dashboard sidebar
│   │   │   ├── MobileMenu.vue             # Mobile navigation
│   │   │   └── UserMenu.vue               # User dropdown
│   │   │
│   │   ├── shop/
│   │   │   ├── ProductCard.vue            # Product card
│   │   │   ├── ProductGrid.vue            # Product listing
│   │   │   ├── CartItem.vue               # Cart item
│   │   │   └── CheckoutForm.vue           # Checkout
│   │   │
│   │   └── ui/
│   │       ├── Button.vue                 # Base button
│   │       ├── Input.vue                  # Form input
│   │       ├── Modal.vue                  # Modal dialog
│   │       └── Notification.vue           # Toast notification
│   │
│   ├── middleware/
│   │   ├── auth.ts                        # Require authentication
│   │   ├── guest.ts                       # Redirect if authenticated
│   │   ├── 2fa.ts                         # Require 2FA verification
│   │   ├── type.ts                        # Check user type
│   │   ├── member-only.ts                 # MEMBER type required
│   │   ├── promoter-only.ts               # PROMOTER type required
│   │   └── advisor-only.ts                # ADVISOR type required
│   │
│   ├── stores/
│   │   ├── auth.ts                        # Authentication state
│   │   ├── user.ts                        # User profile state
│   │   ├── cart.ts                        # Shopping cart
│   │   ├── mlm.ts                         # MLM tree data
│   │   ├── wallet.ts                      # Wallet balance
│   │   ├── notifications.ts               # Notification center
│   │   └── 2fa.ts                         # 2FA state
│   │
│   └── composables/
│       ├── useAuth.ts                     # Auth helpers
│       ├── use2FA.ts                      # 2FA helpers
│       ├── useApi.ts                      # API client
│       ├── useWebPush.ts                  # WebPush handler
│       └── useUserType.ts                 # Type-based logic
│
└── nuxt.config.ts
```

---

## 🎨 Navigation Menus by User Type

### **Guest Navigation** (Unauthenticated)

```vue
<template>
  <nav>
    <MenuItem to="/" icon="home">Home</MenuItem>
    <MenuItem to="/shop" icon="shopping-bag">Shop</MenuItem>
    <MenuItem to="/about" icon="info">About</MenuItem>
    <MenuItem to="/blog" icon="newspaper">Blog</MenuItem>
    <MenuItem to="/join-us" icon="users">Join MLM</MenuItem>

    <div class="ml-auto">
      <Button to="/login" variant="ghost">Login</Button>
      <Button to="/register" variant="primary">Sign Up</Button>
    </div>
  </nav>
</template>
```

### **REGULAR User Navigation**

```vue
<template>
  <nav>
    <MenuItem to="/" icon="home">Home</MenuItem>
    <MenuItem to="/shop" icon="shopping-bag">Shop</MenuItem>
    <MenuItem to="/cart" icon="shopping-cart" :badge="cartCount">Cart</MenuItem>
    <MenuItem to="/orders" icon="receipt">Orders</MenuItem>
    <MenuItem to="/wishlist" icon="heart">Wishlist</MenuItem>

    <UserMenu>
      <MenuItem to="/account">Profile</MenuItem>
      <MenuItem to="/account/security">Security</MenuItem>
      <MenuItem to="/notifications">Notifications</MenuItem>
      <MenuItem @click="logout">Logout</MenuItem>
    </UserMenu>
  </nav>
</template>
```

### **MEMBER User Navigation** (All REGULAR + Membership)

```vue
<template>
  <nav>
    <!-- All REGULAR items + -->
    <MenuItem to="/dashboard" icon="chart-bar">Dashboard</MenuItem>
    <MenuItem to="/membership" icon="star">My Membership</MenuItem>
    <MenuItem to="/exclusive-deals" icon="gift">Deals</MenuItem>
  </nav>
</template>
```

### **PROMOTER User Navigation** (All MEMBER + MLM)

```vue
<template>
  <nav>
    <!-- All MEMBER items + -->
    <MenuItem to="/dashboard/mlm" icon="hierarchy">MLM Dashboard</MenuItem>

    <Submenu label="Team" icon="users">
      <MenuItem to="/dashboard/mlm/tree">Tree View</MenuItem>
      <MenuItem to="/dashboard/mlm/downline">Downline</MenuItem>
      <MenuItem to="/dashboard/mlm/invite">Invite Members</MenuItem>
    </Submenu>

    <Submenu label="Earnings" icon="dollar-sign">
      <MenuItem to="/dashboard/mlm/commissions">Commissions</MenuItem>
      <MenuItem to="/dashboard/wallet">Wallet</MenuItem>
      <MenuItem to="/dashboard/wallet/withdraw">Withdraw</MenuItem>
    </Submenu>
  </nav>
</template>
```

### **ADVISOR User Navigation** (All PROMOTER + Agent Tools)

```vue
<template>
  <nav>
    <!-- All PROMOTER items + -->
    <Submenu label="Recruitment" icon="briefcase">
      <MenuItem to="/dashboard/advisor/recruits">My Recruits</MenuItem>
      <MenuItem to="/dashboard/advisor/assign">Assign to Teams</MenuItem>
      <MenuItem to="/dashboard/advisor/performance">Performance</MenuItem>
    </Submenu>

    <MenuItem to="/dashboard/advisor/salary">Salary Reports</MenuItem>
  </nav>
</template>
```

---

## 📱 WebPush Notification System

### **Architecture**

```
┌────────────────────────────────────────────────┐
│         WEBPUSH NOTIFICATION FLOW              │
├────────────────────────────────────────────────┤
│                                                 │
│  Frontend (Nuxt):                              │
│  1. Request notification permission            │
│  2. Generate push subscription                 │
│  3. Send subscription to API                   │
│  4. Store subscription locally                 │
│                                                 │
│  Backend (Laravel):                            │
│  1. Receive subscription (endpoint + keys)     │
│  2. Store in push_subscriptions table          │
│  3. Associate with user_id                     │
│  4. Send test notification                     │
│                                                 │
│  Notification Triggers:                        │
│  ├── New order placed                          │
│  ├── Order shipped                             │
│  ├── New downline member (MLM)                 │
│  ├── Commission earned                         │
│  ├── Wallet transaction                        │
│  ├── System announcements                      │
│  └── Custom admin notifications                │
│                                                 │
└────────────────────────────────────────────────┘
```

### **VAPID Configuration**

```env
# .env
VAPID_SUBJECT=mailto:support@commerinity.com
VAPID_PUBLIC_KEY=<generate with php artisan webpush:vapid>
VAPID_PRIVATE_KEY=<auto-generated>
```

### **WebPush Flow**

```typescript
// composables/useWebPush.ts

export const useWebPush = () => {
  const { $api } = useNuxtApp()
  const authStore = useAuthStore()

  async function requestPermission() {
    const permission = await Notification.requestPermission()
    if (permission === 'granted') {
      await subscribe()
    }
    return permission
  }

  async function subscribe() {
    // 1. Get VAPID public key from API
    const { public_key } = await $api('/auth/vapid-public-key')

    // 2. Register service worker
    const registration = await navigator.serviceWorker.register('/sw.js')

    // 3. Subscribe to push
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(public_key)
    })

    // 4. Send subscription to API
    await $api('/push/subscribe', {
      method: 'POST',
      body: {
        subscription: subscription.toJSON(),
        user_id: authStore.user.id
      }
    })

    return subscription
  }

  async function unsubscribe() {
    const registration = await navigator.serviceWorker.getRegistration()
    const subscription = await registration?.pushManager.getSubscription()

    if (subscription) {
      await $api('/push/unsubscribe', {
        method: 'POST',
        body: { endpoint: subscription.endpoint }
      })
      await subscription.unsubscribe()
    }
  }

  return {
    requestPermission,
    subscribe,
    unsubscribe
  }
}
```

---

## ⚡ Performance Optimization Strategy

### **API Optimization**

```
1. Response Caching
   - Cache categories: 1 hour
   - Cache featured products: 30 minutes
   - Cache user session: 5 minutes

2. Database Optimization
   - Eager load relationships
   - Index all foreign keys
   - Use query caching for static data

3. Rate Limiting
   - Guest: 60 requests/minute
   - Authenticated: 120 requests/minute
   - Admin: Unlimited

4. Response Compression
   - Gzip all JSON responses
   - Minify API responses

5. Pagination
   - Default: 15 items per page
   - Products: 24 per page
   - Orders: 10 per page
   - MLM tree: 50 nodes per level
```

### **Frontend Optimization**

```
1. Code Splitting
   - Lazy load dashboard pages
   - Lazy load MLM components
   - Lazy load charts/heavy components

2. Image Optimization
   - Use WebP format
   - Lazy loading images
   - Responsive images (srcset)

3. State Management
   - Persist auth state (localStorage)
   - Cache cart locally
   - Sync with API on load

4. Prefetching
   - Prefetch user data on login
   - Prefetch cart on auth
   - Prefetch categories on mount

5. Bundle Optimization
   - Tree-shaking unused code
   - Minimize bundle size
   - Use CDN for static assets
```

---

## 🔒 Security Implementation Checklist

### **Backend Security**

- [x] OTP hashing (bcrypt)
- [x] Password hashing (bcrypt, rounds=12)
- [x] Rate limiting (OTP, login, password reset)
- [ ] 2FA implementation (TOTP + backup codes)
- [ ] Trusted device fingerprinting
- [ ] Suspicious activity detection
- [ ] IP-based blocking
- [ ] CORS configuration (Nuxt origin only)
- [ ] Sanctum CSRF protection
- [ ] API token expiry (configurable)
- [ ] Logout on password change
- [ ] Account lockout after failed attempts

### **Frontend Security**

- [ ] Environment variables for API URL
- [ ] Token storage (httpOnly cookies preferred)
- [ ] HTTPS enforcement
- [ ] CSP headers
- [ ] XSS prevention
- [ ] Input sanitization
- [ ] Route guards (middleware)
- [ ] Type-based access control
- [ ] Session timeout warning
- [ ] Auto-logout on inactivity

---

## 📊 API Response Format (Standardized)

### **Success Response**

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "user": {...},
    "meta": {
      "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
      }
    }
  }
}
```

### **Error Response**

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required"],
    "password": ["Password must be at least 8 characters"]
  },
  "code": 422
}
```

### **2FA Challenge Response**

```json
{
  "success": false,
  "requires_2fa": true,
  "message": "Two-factor authentication required",
  "data": {
    "challenge_id": "uuid-v4",
    "methods": ["sms", "email", "totp"],
    "expires_at": "2025-12-08T17:30:00Z"
  }
}
```

---

## 🎯 Implementation Priority

### **Phase 1: Core Authentication** (Week 1)
1. ✅ OtpManager (DONE)
2. ✅ User model + tests (DONE)
3. ✅ Auth tests written (DONE)
4. ⏳ Auth controllers (RegisterController, LoginController, OtpController)
5. ⏳ Form requests (validation)
6. ⏳ API routes
7. ⏳ Run tests → 100% pass

### **Phase 2: Two-Factor Auth** (Week 2)
1. TwoFactorAuth model + migration
2. TrustedDevice model + migration
3. TwoFactorManager helper
4. TwoFactorController
5. TOTP implementation (Google Authenticator)
6. Backup codes generation
7. Middleware (RequireTwoFactor)
8. Tests for 2FA flow

### **Phase 3: Frontend Core** (Week 2-3)
1. Nuxt 4 setup + Tailwind
2. Auth layouts (default, auth, dashboard)
3. Login/Register pages
4. OTP input component
5. 2FA challenge component
6. Pinia stores (auth, user, 2fa)
7. API composable (useApi)
8. Middleware (auth, guest, type-based)

### **Phase 4: WebPush** (Week 3)
1. Publish webpush config
2. Generate VAPID keys
3. Service worker (sw.js)
4. useWebPush composable
5. Notification component
6. Backend notification triggers
7. Test notifications

### **Phase 5: Dashboards** (Week 4)
1. Dashboard layouts by type
2. MLM tree visualization
3. Commission tables
4. Wallet integration
5. Analytics charts
6. Responsive design

---

## 📖 Navigation Rules & Logic

### **Route Access Control**

```typescript
// middleware/type.ts
export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()
  const user = authStore.user

  const requiredType = to.meta.requiresType

  if (requiredType && user?.type !== requiredType) {
    return navigateTo('/dashboard') // Redirect to appropriate dashboard
  }
})
```

### **Dynamic Navigation Component**

```vue
<!-- components/navigation/Navbar.vue -->
<template>
  <nav>
    <!-- Common items -->
    <MenuItem to="/" icon="home">Home</MenuItem>
    <MenuItem to="/shop" icon="shopping-bag">Shop</MenuItem>

    <!-- Guest only -->
    <template v-if="!authStore.isAuthenticated">
      <MenuItem to="/login">Login</MenuItem>
      <MenuItem to="/register">Sign Up</MenuItem>
    </template>

    <!-- Authenticated only -->
    <template v-else>
      <MenuItem to="/cart" :badge="cartCount">Cart</MenuItem>
      <MenuItem to="/orders">Orders</MenuItem>

      <!-- Member+ -->
      <MenuItem v-if="isMemberOrAbove" to="/dashboard">Dashboard</MenuItem>

      <!-- Promoter+ -->
      <MenuItem v-if="isPromoterOrAbove" to="/dashboard/mlm">MLM</MenuItem>

      <!-- Advisor+ -->
      <MenuItem v-if="isAdvisor" to="/dashboard/advisor">Recruitment</MenuItem>

      <UserMenu />
    </template>
  </nav>
</template>

<script setup lang="ts">
const authStore = useAuthStore()
const cartStore = useCartStore()
const { isType, isTypeOrAbove } = useUserType()

const cartCount = computed(() => cartStore.count)

const isMemberOrAbove = computed(() =>
  isTypeOrAbove(['member', 'promoter', 'advisor', 'mentor'])
)

const isPromoterOrAbove = computed(() =>
  isTypeOrAbove(['promoter', 'advisor', 'mentor'])
)

const isAdvisor = computed(() => isType('advisor'))
</script>
```

---

## 🚀 Performance Targets

### **API Response Times**

```
Login:              < 200ms
Registration:       < 500ms
Get User:           < 100ms
OTP Send:           < 300ms
Product List:       < 200ms
Cart Operations:    < 150ms
MLM Tree (50 nodes): < 500ms
```

### **Frontend Metrics**

```
First Contentful Paint:  < 1.5s
Time to Interactive:     < 3.0s
Largest Contentful Paint: < 2.5s
Cumulative Layout Shift:  < 0.1
Bundle Size:             < 250KB (gzipped)
```

---

## 🎯 Next Implementation Steps

### **Immediate (Today)**

1. Create `TwoFactorAuth` model + migration
2. Create `TrustedDevice` model + migration
3. Create `TwoFactorManager` helper
4. Update `User` model with 2FA relationships
5. Write 2FA tests

### **Tomorrow**

1. Implement auth controllers
2. Create form requests
3. Setup API routes
4. Run all auth tests
5. Fix any failing tests

### **This Week**

1. Nuxt 4 project setup
2. Layouts and navigation
3. Auth pages (login, register)
4. WebPush setup
5. First deployment

---

**Last Updated**: 2025-12-08 17:15 PM
**Next**: Implement 2FA models and TwoFactorManager helper
