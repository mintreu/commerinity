# Implementation Guide - Phase 1
## Auth System + Dashboard (Old Commerinity Design)

**Source**: Copy from `C:\laragon\www\mintreu\server\commerinity\`
**Target**: Build in `C:\laragon\www\mintreu\server\commerinity_pro\`

---

## ✅ **Configuration Updated**

- [x] Database name: `commerinity_pro`
- [x] App name: "Commerinity Pro"
- [x] App URL: http://localhost:8000

---

## 📋 **Implementation Checklist**

### **BACKEND (apiserver/) - 2-3 hours**

#### 1. Install Packages (5 min)
```bash
cd apiserver
composer require moneyphp/money
composer require laravel-notification-channels/webpush
composer require --dev pestphp/pest-plugin-laravel
```

#### 2. Create User Migration (10 min)
**Copy from**: `commerinity/backend/database/migrations/create_users_table.php`
**Include fields**:
- name, email, mobile, password
- referral_code (8 chars, unique)
- parent_id (self-referencing FK)
- email_verified_at, mobile_verified_at
- status (draft, active, suspended)
- type (regular, premium)

#### 3. Create OTP System (20 min)
**Create**: `app/Models/User/Otp.php`
**Migration**: otps table (mobile, code, expires_at)

#### 4. Auth Controllers (40 min)
**Create**:
- `app/Http/Controllers/Api/Auth/RegisterController.php`
- `app/Http/Controllers/Api/Auth/LoginController.php`
- `app/Http/Controllers/Api/Auth/OtpController.php`

**Register Flow**:
1. POST /api/register → Send OTP to mobile
2. POST /api/verify-otp → Verify + create user
3. Auto-login after verification

**Login Flow**:
1. POST /api/login → mobile OR email + password
2. Return user + Sanctum token

#### 5. Create app:reset Command (15 min)
**Copy from**: Old commerinity or Popkult
**File**: `app/Console/Commands/ResetCommand.php`
```bash
php artisan app:reset
# - Drop all tables
# - Run migrations
# - Seed database
# - Clear cache
```

#### 6. Setup Sanctum Config (10 min)
**Update**: `config/sanctum.php`
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:3000')),
```

#### 7. API Routes (10 min)
```php
// routes/api.php
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/send-otp', [OtpController::class, 'send']);
Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [LoginController::class, 'logout']);
});
```

#### 8. Write Tests (30 min)
**Create**:
- `tests/Feature/Auth/RegisterTest.php`
- `tests/Feature/Auth/LoginTest.php`
- `tests/Feature/Auth/OtpTest.php`

---

### **FRONTEND (client/) - 3-4 hours**

#### 1. Install Packages (10 min)
```bash
cd client
pnpm add @qirolab/nuxt-sanctum-authentication
pnpm add @vueuse/nuxt @vueuse/core
pnpm add @nuxt/fonts
pnpm add gsap  # For animations (from old)
pnpm add --save-dev vitest @vue/test-utils @playwright/test
```

#### 2. Configure Nuxt (15 min)
**File**: `nuxt.config.ts`
**Copy from**: Old commerinity `frontend/nuxt.config.ts`
**Key config**:
- laravelSanctum setup
- runtimeConfig (apiBase, webBase, authConfig)
- Modules: @nuxt/ui, @qirolab/nuxt-sanctum-authentication, @nuxt/fonts

#### 3. Create Theme System (20 min)

**File**: `app.config.ts`
**Reference**: Old commerinity design (purple-pink-blue, glassmorphism)

**File**: `assets/css/main.css`
**Copy glassmorphism utilities from old**:
- .glass-card
- .btn-gradient-primary
- .text-gradient
- Animation keyframes

#### 4. Create Reusable Components (60 min)

**Forms** (`components/forms/`):
```
- FormField.vue          # Label + Input + Error wrapper
- OTPInput.vue           # 6-digit OTP (copy from old)
- PasswordInput.vue      # Password with toggle visibility
- MobileInput.vue        # Mobile with country code
```

**UI** (`components/ui/`):
```
- GlassCard.vue          # Wrapper for UCard with glass effect
- GradientButton.vue     # Wrapper for UButton with gradients
- LoadingSpinner.vue     # Loading state
- Toast.vue              # Copy from old (or use Nuxt UI toast)
```

#### 5. Create Layouts (45 min)

**Copy structure from old**, **simplify**:

**`layouts/default.vue`**:
- Navbar (glassmorphism)
- Background effects (animated orbs)
- Footer
- Bottom nav (mobile)

**`layouts/dashboard.vue`**:
- Collapsible sidebar
- Topbar
- Background orbs with GSAP
- Bottom nav (mobile)

**`layouts/auth.vue`**:
- **SIMPLIFIED** version (not 20KB!)
- Logo + theme toggle
- Minimal decoration
- Footer links

#### 6. Build Auth Pages (90 min)

**`pages/auth/register.vue`**:
**Copy from**: Old commerinity `frontend/pages/auth/register.vue`
**Features**:
- Mobile number input (default)
- Send OTP button
- OTP verification (6 digits)
- Basic info form (name, password, referral code)
- Switch to email option (configurable)
- Glassmorphism design
- GSAP animations

**`pages/auth/login.vue`**:
**Copy from**: Old commerinity `frontend/pages/auth/login.vue`
**Features**:
- Mobile OR Email input (flexible)
- Password input
- "Forgot password" link
- Remember me checkbox
- Glassmorphism design

#### 7. Create Homepage (30 min)

**`pages/index.vue`**:
**Copy structure from**: Old commerinity `frontend/pages/index.vue`
**Sections**:
- Hero section (gradient text, GSAP animation)
- Stats counter
- Features section
- CTA buttons

**Keep it simple for now** - Full content comes later

#### 8. Create Dashboard (45 min)

**`pages/dashboard/index.vue`**:
**Copy from**: Old commerinity `frontend/pages/dashboard/index.vue`
**Features**:
- Greeting header
- Stats cards (4 cards in grid)
- Quick actions
- Recent activity
- Glassmorphism design

#### 9. Create Dashboard Components (30 min)

**`components/dashboard/StatCard.vue`**:
**Copy from**: Old commerinity
**Features**:
- Gradient icon background
- Value + label
- Trend indicator

#### 10. Setup Testing (20 min)

**Backend**:
```bash
php artisan test  # Should work with Pest
```

**Frontend**:
```bash
pnpm add --save-dev vitest @nuxt/test-utils
# Create vitest.config.ts
# Create tests/components/OTPInput.spec.ts
```

---

## 🎨 **Design Reference (Old Commerinity)**

### **Copy These Exact Patterns**:

#### **Colors**:
- Primary gradient: `from-blue-600 to-purple-600`
- Success gradient: `from-green-600 to-emerald-600`
- Danger gradient: `from-red-600 to-pink-600`
- Hero gradient: `from-purple-600 via-pink-600 to-blue-600`

#### **Glassmorphism**:
```css
bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl
border border-white/30 dark:border-slate-700/50
rounded-2xl shadow-2xl
```

#### **Buttons**:
```vue
<UButton
  class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600
         hover:from-blue-700 hover:to-purple-700 text-white
         rounded-xl font-bold shadow-lg hover:shadow-xl
         transition-all duration-300 hover:-translate-y-1"
>
```

#### **Cards**:
```vue
<UCard
  :ui="{
    base: 'bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl',
    rounded: 'rounded-2xl',
    shadow: 'shadow-2xl'
  }"
>
```

#### **Animations** (GSAP):
```typescript
// Hero entrance
gsap.from('.hero-title', {
  y: 100,
  opacity: 0,
  duration: 1.2,
  ease: 'back.out(1.7)'
})

// Background orbs
gsap.to(orb, {
  x: 80, y: 40, rotation: 180,
  duration: 30, repeat: -1, yoyo: true,
  ease: 'sine.inOut'
})
```

---

## 🔑 **Key Files to Copy & Clean**

### From `commerinity/frontend/`:

**Components**:
- `components/ui/Navbar/DefaultNavbar.vue` → Clean up
- `components/ui/Navbar/DashboardSidebar.vue` → Simplify
- `components/ui/BottomNavBar.vue` → Copy as-is
- `components/ui/DarkModeToggle.vue` → Simplify (900 lines → 100 lines)
- `components/GlobalLoader.vue` → Copy with GSAP
- `components/Toast.vue` → Copy (or use Nuxt UI toast)

**Layouts**:
- `layouts/default.vue` → Copy structure, lazy load components
- `layouts/dashboard.vue` → Copy sidebar logic
- `layouts/auth.vue` → **SIMPLIFY** (20KB → 2KB)

**Pages**:
- `pages/index.vue` → Copy hero + features
- `pages/auth/register.vue` → Copy mobile OTP flow
- `pages/auth/login.vue` → Copy flexible login
- `pages/dashboard/index.vue` → Copy stat cards

**Composables**:
- `composables/useToast.ts` → Copy
- `composables/usePageMeta.ts` → Copy

**CSS**:
- `assets/css/main.css` → Copy utilities, convert to Tailwind 4

---

## ⚡ **Quick Start Commands**

### Backend:
```bash
cd apiserver

# Create database
mysql -u root -e "CREATE DATABASE commerinity_pro;"

# Run migrations
php artisan migrate:fresh

# Start server
php artisan serve
```

### Frontend:
```bash
cd client

# Install dependencies
pnpm install

# Start dev server
pnpm dev
```

### Both (Development):
```bash
# Terminal 1: Backend
cd apiserver && php artisan serve

# Terminal 2: Queue (if needed)
cd apiserver && php artisan queue:listen

# Terminal 3: Frontend
cd client && pnpm dev
```

---

## 🎯 **Success Criteria**

After this phase, you should have:

✅ User can register with mobile + OTP
✅ User can login with mobile OR email + password
✅ Homepage with glassmorphism design (exact copy)
✅ Dashboard with sidebar, stat cards (exact copy)
✅ Authentication working (Sanctum cookie-based)
✅ Tests passing (backend auth tests)
✅ Design matches old commerinity exactly
✅ Performance: <500KB bundle

---

## 🚨 **Important Notes**

1. **No Pinia** - Use `useState` for state management
2. **Exact design** - Copy colors, animations, layouts from old
3. **Old Commerinity frontend** - Source of all UI/UX
4. **Popkult** - Only for backend logic patterns
5. **Full testing** - Write tests as you build
6. **Glassmorphism** - Maintain premium aesthetic

---

## 📞 **Need Help?**

This is a comprehensive 5-6 hour build. I've set up:
- ✅ Database configuration
- ✅ All plans documented
- ✅ Implementation guide created

**You can either**:
1. Build following this guide yourself
2. Or I can help build section by section (tell me which part to start with)

**Which would you prefer?** 🚀
