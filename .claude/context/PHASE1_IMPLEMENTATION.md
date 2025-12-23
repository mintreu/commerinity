# Phase 1 Implementation Guide
## Auth System + Dashboard Foundation

**Timeline**: Week 1-2
**Goal**: Working authentication with homepage + dashboard

---

## 🎯 **What We're Building**

### Backend (apiserver/)
1. ✅ User model with mobile/email support
2. ✅ Sanctum SPA authentication
3. ✅ Mobile OTP registration (default)
4. ✅ Flexible login (mobile OR email + password)
5. ✅ Web push notifications (VAPID)
6. ✅ app:reset command
7. ✅ Pest testing setup

### Frontend (client/)
1. ✅ Nuxt 4 + Nuxt UI configured
2. ✅ Glassmorphism theme system
3. ✅ Reusable form components (OTPInput, FormField)
4. ✅ Auth pages (login, register with mobile OTP)
5. ✅ Layouts (default, dashboard, auth)
6. ✅ Homepage (landing page)
7. ✅ Dashboard (user dashboard)
8. ✅ Sanctum authentication integrated

---

## 📋 **Step-by-Step Implementation**

### STEP 1: Backend Database Setup (15 min)

**Tasks**:
1. Update .env with database name from old commerinity
2. Update .env.example
3. Create User migration with mobile support
4. Run migrations

### STEP 2: Install Required Packages (10 min)

**Backend**:
- moneyphp/money
- laravel/sanctum (already installed)
- laravel-notification-channels/webpush

**Frontend**:
- @qirolab/nuxt-sanctum-authentication
- @vueuse/core
- pinia
- @nuxt/fonts

### STEP 3: Backend Auth System (30 min)

**Tasks**:
1. Create User model with mobile field
2. Create OTP system (SMS simulation for now)
3. Create auth controllers (Register, Login, Logout)
4. Create auth routes
5. Configure Sanctum for SPA

### STEP 4: Frontend Theme Setup (20 min)

**Tasks**:
1. Create app.config.ts (Nuxt UI theme)
2. Create assets/css/main.css (Tailwind 4 + glassmorphism)
3. Configure nuxt.config.ts (Sanctum + modules)

### STEP 5: Reusable Components (40 min)

**Tasks**:
1. Create FormField.vue
2. Create OTPInput.vue
3. Create PasswordInput.vue
4. Create GlassCard.vue (wrapper for UCard)

### STEP 6: Layouts (30 min)

**Tasks**:
1. Create layouts/default.vue (simplified from old)
2. Create layouts/dashboard.vue (sidebar + topbar)
3. Create layouts/auth.vue (minimal, not 20KB!)

### STEP 7: Auth Pages (60 min)

**Tasks**:
1. Create pages/auth/register.vue (mobile OTP)
2. Create pages/auth/login.vue (flexible: mobile OR email)
3. Create OTP verification component
4. Integrate with Sanctum

### STEP 8: Homepage + Dashboard (45 min)

**Tasks**:
1. Create pages/index.vue (landing page)
2. Create pages/dashboard/index.vue (user dashboard)
3. Create dashboard components (StatCard, etc.)

### STEP 9: Web Push Notifications (30 min)

**Tasks**:
1. Setup VAPID keys
2. Create notification service worker
3. Create push subscription API
4. Test notification sending

### STEP 10: Testing Setup (20 min)

**Tasks**:
1. Configure Pest
2. Write first tests (User creation, Login)
3. Setup Puppeteer for visual testing

---

## 🎯 **Configuration Details**

### Database (.env)
```env
APP_NAME="Commerinity Pro"
DB_CONNECTION=mysql
DB_DATABASE=commerinity_pro
```

### Nuxt Configuration
```typescript
modules: [
  '@nuxt/ui',
  '@qirolab/nuxt-sanctum-authentication',
  '@nuxt/fonts',
  '@vueuse/nuxt'
]

laravelSanctum: {
  apiUrl: 'http://localhost:8000',
  authMode: 'cookie',
  userResponseWrapperKey: 'data',
}
```

### Mobile vs Email Registration
```typescript
// nuxt.config.ts
runtimeConfig: {
  public: {
    authConfig: {
      registrationMode: 'mobile',     // 'mobile', 'email', 'both'
      loginMode: 'flexible',           // 'flexible', 'mobile', 'email'
      otpRequired: true,
      allowBoth: true,
    }
  }
}
```

---

**Total Time**: ~4.5 hours for complete Phase 1 foundation

**Ready to start?** I'll begin with STEP 1!
