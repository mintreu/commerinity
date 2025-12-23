# Nuxt Sanctum Authentication - Complete Guide
**Documentation Source**: https://qirolab.github.io/nuxt-sanctum-authentication/
**Date**: 2025-12-08
**Purpose**: Enterprise-grade Nuxt 4 + Laravel 12 Sanctum integration for Commerinity Pro

---

## 🎯 Two Authentication Modes

### 1. **Cookie-Based (SPA Mode)** - Old Commerinity Used This
```typescript
// nuxt.config.ts
laravelSanctum: {
    apiUrl: 'http://localhost:8000',
    authMode: 'cookie',  // ← SPA mode
    userResponseWrapperKey: 'data',
    sanctumEndpoints: {
        csrf: '/sanctum/csrf-cookie',
        login: '/api/login',
        logout: '/api/logout',
    },
}
```

**How It Works:**
- Nuxt and Laravel share same top-level domain (e.g., `app.domain.com` + `api.domain.com`)
- Uses session cookies + CSRF tokens
- Backend must be in `SANCTUM_STATEFUL_DOMAINS`
- **Limitations**: Web-only, no mobile app support

### 2. **Token-Based (API Mode)** - ✅ OUR CHOICE
```typescript
// nuxt.config.ts
laravelSanctum: {
    apiUrl: 'http://localhost:8000',
    authMode: 'token',  // ← Token mode
    token: {
        storageKey: 'AUTH_TOKEN',
        provider: 'cookie',  // or 'localStorage'
        responseKey: 'token',  // Extract from response.data.token
    },
}
```

**How It Works:**
- Login returns: `{ data: { token: 'plain_token_value' } }`
- Module stores token in cookie/localStorage
- Automatically adds `Authorization: Bearer <token>` to all requests
- **Advantages**: Multi-platform (web + mobile), scalable, different domains

---

## 📦 Installation

### Automatic (Recommended)
```bash
npx nuxi@latest module add @qirolab/nuxt-sanctum-authentication
```

### Manual
```bash
pnpm add @qirolab/nuxt-sanctum-authentication
```

Then add to `nuxt.config.ts`:
```typescript
export default defineNuxtConfig({
    modules: ['@qirolab/nuxt-sanctum-authentication'],
})
```

---

## ⚙️ Complete Configuration Options

| Option | Description | Default | Our Value |
|--------|-------------|---------|-----------|
| **apiUrl** | Laravel API base URL | Required | `http://localhost:8000` |
| **authMode** | `'cookie'` or `'token'` | `'cookie'` | `'token'` ✅ |
| **userResponseWrapperKey** | Extract user from `response.data` | `null` | `'data'` |
| **token.storageKey** | Token storage key | `'AUTH_TOKEN'` | `'AUTH_TOKEN'` |
| **token.provider** | `'cookie'` or `'localStorage'` | `'cookie'` | `'cookie'` (HTTP-only) |
| **token.responseKey** | Extract token from response | `'token'` | `'token'` |
| **sanctumEndpoints.login** | Login endpoint | `'/login'` | `'/api/auth/login'` |
| **sanctumEndpoints.logout** | Logout endpoint | `'/logout'` | `'/api/auth/logout'` |
| **sanctumEndpoints.user** | Get user endpoint | `'/api/user'` | `'/api/user'` |
| **redirect.loginPath** | Redirect when unauthenticated | `'/login'` | `'/auth/login'` |
| **redirect.redirectToAfterLogin** | After login redirect | `'/'` | `'/dashboard'` |
| **redirect.redirectToAfterLogout** | After logout redirect | `'/'` | `'/'` |
| **middlewareNames.auth** | Auth middleware name | `'$auth'` | `'$auth'` |
| **middlewareNames.guest** | Guest middleware name | `'$guest'` | `'$guest'` |

---

## 🔧 Recommended Configuration for Commerinity Pro

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
    modules: ['@qirolab/nuxt-sanctum-authentication'],

    laravelSanctum: {
        // API Base URL
        apiUrl: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',

        // ✅ TOKEN MODE (for mobile + web support)
        authMode: 'token',

        // Token Configuration
        token: {
            storageKey: 'commerinity_auth_token',
            provider: 'cookie',  // HTTP-only cookie for security
            responseKey: 'token',  // Extract from response.data.token
        },

        // User Response Wrapper
        userResponseWrapperKey: 'data',  // Extract user from response.data

        // API Endpoints
        sanctumEndpoints: {
            login: '/api/auth/login',
            logout: '/api/auth/logout',
            user: '/api/user',
        },

        // Redirects
        redirect: {
            enableIntendedRedirect: true,  // Return to intended page after login
            loginPath: '/auth/login',
            guestOnlyRedirect: '/dashboard',
            redirectToAfterLogin: '/dashboard',
            redirectToAfterLogout: '/',
        },

        // Middleware Names
        middlewareNames: {
            auth: '$auth',   // For protected routes
            guest: '$guest', // For guest-only routes (login, register)
        },

        // Logging (disable in production)
        logLevel: process.env.NODE_ENV === 'development' ? 3 : 1,
    },
})
```

---

## 🎨 Primary Composables

### 1. **useSanctum()** - Main Authentication Composable

```typescript
interface User {
    id: number
    uuid: string
    name: string
    mobile: string
    email: string | null
    type: string
    status: string
    referral_code: string
}

const {
    user,           // Reactive user object
    isLoggedIn,     // Boolean auth status
    login,          // Login method
    logout,         // Logout method
    refreshUser,    // Refresh user data
    options,        // Module config
} = useSanctum<User>()
```

#### **login() Method**

```typescript
// Basic login
await login({
    mobile: '+919876543210',
    password: 'Password123!',
})

// Login with device name
await login({
    email: 'user@example.com',
    password: 'Password123!',
    device_name: 'iPhone 14 Pro',
})

// Login with OTP
await login({
    mobile: '+919876543210',
    otp: '123456',
})

// With callback for 2FA or custom redirect
await login(credentials, {}, async (response, user) => {
    if (response.requires_2fa) {
        return await navigateTo('/auth/2fa')
    }

    // Type-based redirect
    if (user?.type === 'PROMOTER') {
        return await navigateTo('/mlm/dashboard')
    } else if (user?.type === 'MEMBER') {
        return await navigateTo('/member/dashboard')
    }

    return await navigateTo('/dashboard')
})
```

#### **logout() Method**

```typescript
// Logout current device
await logout()

// Custom logout with API call to logout-all
const $sanctumFetch = useSanctumFetch()
await $sanctumFetch('/api/auth/logout-all', { method: 'POST' })
await logout() // Then clear local state
```

#### **refreshUser() Method**

```typescript
// Refresh user data after profile update
await refreshUser()
```

### 2. **useCurrentUser()** - Access User Only

```typescript
const user = useCurrentUser<User>()

// Usage in template
<template>
    <div v-if="user">
        <h1>Welcome, {{ user.name }}</h1>
        <p>{{ user.mobile }}</p>
    </div>
</template>
```

### 3. **useSanctumFetch()** - Authenticated API Requests

```typescript
const $sanctumFetch = useSanctumFetch()

// GET request
const users = await $sanctumFetch('/api/users')

// POST request
const newOrder = await $sanctumFetch('/api/orders', {
    method: 'POST',
    body: {
        product_id: 123,
        quantity: 2,
    },
})

// With useAsyncData (SSR-friendly)
const { data: products, refresh } = await useAsyncData(
    'products',
    () => $sanctumFetch('/api/products')
)

// Error handling
try {
    await $sanctumFetch('/api/protected-resource')
} catch (error) {
    console.error('API Error:', error)
}
```

---

## 🛡️ Middleware Usage

### Protect Routes (Auth Required)

```typescript
// pages/dashboard.vue
definePageMeta({
    middleware: ['$auth']  // Only authenticated users
})

// OR in nuxt.config.ts for multiple routes
export default defineNuxtConfig({
    routeRules: {
        '/dashboard/**': { middleware: ['$auth'] },
        '/mlm/**': { middleware: ['$auth'] },
        '/member/**': { middleware: ['$auth'] },
    }
})
```

### Guest-Only Routes

```typescript
// pages/auth/login.vue
definePageMeta({
    middleware: ['$guest']  // Only unauthenticated users
})
```

### Custom Middleware Names

```typescript
// nuxt.config.ts
laravelSanctum: {
    middlewareNames: {
        auth: 'authenticated',  // Use as middleware: ['authenticated']
        guest: 'guestOnly',     // Use as middleware: ['guestOnly']
    },
}
```

---

## 🚀 Authentication Patterns for Commerinity Pro

### Pattern 1: Mobile + OTP Registration

```vue
<script setup lang="ts">
const { login } = useSanctum()
const mobile = ref('')
const otp = ref('')
const name = ref('')
const password = ref('')

// Step 1: Send OTP
const sendOtp = async () => {
    const $sanctumFetch = useSanctumFetch()
    await $sanctumFetch('/api/auth/send-otp', {
        method: 'POST',
        body: {
            type: 'mobile',
            value: mobile.value,
        },
    })
}

// Step 2: Register
const register = async () => {
    const $sanctumFetch = useSanctumFetch()
    const response = await $sanctumFetch('/api/auth/register', {
        method: 'POST',
        body: {
            mobile: mobile.value,
            otp: otp.value,
            name: name.value,
            password: password.value,
            password_confirmation: password.value,
        },
    })

    // Manually trigger login with returned token
    if (response.data?.token) {
        // The module will auto-store token and fetch user
        await login({
            mobile: mobile.value,
            password: password.value,
        })
    }
}
</script>
```

### Pattern 2: Multi-Method Login

```vue
<script setup lang="ts">
const { login, isLoggedIn, user } = useSanctum<User>()
const loginMethod = ref<'password' | 'otp'>('password')
const credential = ref('')  // Email or mobile
const password = ref('')
const otp = ref('')

const handleLogin = async () => {
    if (loginMethod.value === 'password') {
        // Email/Mobile + Password
        const creds: any = { password: password.value }

        if (credential.value.includes('@')) {
            creds.email = credential.value
        } else {
            creds.mobile = credential.value
        }

        await login(creds)
    } else {
        // Email/Mobile + OTP
        const creds: any = { otp: otp.value }

        if (credential.value.includes('@')) {
            creds.email = credential.value
        } else {
            creds.mobile = credential.value
        }

        await login(creds, {}, async (response, user) => {
            // Type-based redirect
            if (user?.type === 'PROMOTER') {
                return await navigateTo('/mlm/dashboard')
            } else if (user?.type === 'MEMBER') {
                return await navigateTo('/member/dashboard')
            }
            return await navigateTo('/dashboard')
        })
    }
}

const sendOtp = async () => {
    const $sanctumFetch = useSanctumFetch()
    await $sanctumFetch('/api/auth/send-otp', {
        method: 'POST',
        body: {
            type: credential.value.includes('@') ? 'email' : 'mobile',
            value: credential.value,
        },
    })
}
</script>
```

### Pattern 3: Password Reset (Email Token)

```vue
<script setup lang="ts">
const email = ref('')
const token = ref('')  // From email link: ?token=xxx
const password = ref('')

const requestReset = async () => {
    const $sanctumFetch = useSanctumFetch()
    await $sanctumFetch('/api/auth/forgot-password', {
        method: 'POST',
        body: {
            type: 'email',
            email: email.value,
        },
    })
}

const resetPassword = async () => {
    const $sanctumFetch = useSanctumFetch()
    await $sanctumFetch('/api/auth/reset-password', {
        method: 'POST',
        body: {
            type: 'email',
            email: email.value,
            token: token.value,
            password: password.value,
            password_confirmation: password.value,
        },
    })

    // Redirect to login
    await navigateTo('/auth/login')
}
</script>
```

### Pattern 4: Password Reset (Mobile OTP)

```vue
<script setup lang="ts">
const mobile = ref('')
const otp = ref('')
const password = ref('')

const requestOtp = async () => {
    const $sanctumFetch = useSanctumFetch()
    await $sanctumFetch('/api/auth/forgot-password', {
        method: 'POST',
        body: {
            type: 'mobile',
            mobile: mobile.value,
        },
    })
}

const resetPassword = async () => {
    const $sanctumFetch = useSanctumFetch()
    await $sanctumFetch('/api/auth/reset-password', {
        method: 'POST',
        body: {
            type: 'mobile',
            mobile: mobile.value,
            otp: otp.value,
            password: password.value,
            password_confirmation: password.value,
        },
    })

    await navigateTo('/auth/login')
}
</script>
```

### Pattern 5: Type-Based Navigation

```vue
<script setup lang="ts">
const { user, isLoggedIn } = useSanctum<User>()
const router = useRouter()

// Watch user type and redirect accordingly
watch(user, (newUser) => {
    if (!newUser) return

    const typeRoutes = {
        'REGULAR': '/dashboard',
        'MEMBER': '/member/dashboard',
        'PROMOTER': '/mlm/dashboard',
        'ADVISOR': '/advisor/recruitment',
        'MENTOR': '/mentor/overview',
    }

    const targetRoute = typeRoutes[newUser.type] || '/dashboard'
    router.push(targetRoute)
}, { immediate: true })
</script>
```

---

## 🔐 Laravel Backend Requirements

### 1. API Routes (Already Done ✅)
```php
// routes/api.php
Route::post('/auth/send-otp', [OtpController::class, 'send']);
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::post('/auth/login', [LoginController::class, 'login']);
Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/auth/logout', [LoginController::class, 'logout']);
    Route::post('/auth/logout-all', [LoginController::class, 'logoutAll']);
});
```

### 2. Controller Response Format
```php
// LoginController::login()
return response()->json([
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'user' => [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'type' => $user->type,
            'status' => $user->status,
            'referral_code' => $user->referral_code,
        ],
        'token' => $token,  // ← Module extracts this
    ],
]);
```

### 3. CORS Configuration (config/cors.php)
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',  // Nuxt dev
        'https://app.commerinity.com',  // Production
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,  // ← CRITICAL for cookies
];
```

### 4. Sanctum Configuration (config/sanctum.php)
```php
return [
    // ✅ IMPORTANT: LEAVE EMPTY for token mode
    // Only use stateful domains for cookie mode
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),

    'guard' => ['web'],
    'expiration' => null,  // Tokens don't expire
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
```

---

## 📱 Composable Usage Examples

### Check Authentication Status
```vue
<template>
    <div>
        <nav v-if="isLoggedIn">
            <p>Welcome, {{ user?.name }}</p>
            <button @click="handleLogout">Logout</button>
        </nav>
        <nav v-else>
            <NuxtLink to="/auth/login">Login</NuxtLink>
        </nav>
    </div>
</template>

<script setup lang="ts">
const { user, isLoggedIn, logout } = useSanctum<User>()

const handleLogout = async () => {
    await logout()
    await navigateTo('/auth/login')
}
</script>
```

### Fetch Protected Data
```vue
<script setup lang="ts">
const $sanctumFetch = useSanctumFetch()

// Server-side data fetching (SSR)
const { data: orders } = await useAsyncData(
    'user-orders',
    () => $sanctumFetch('/api/orders')
)

// Client-side data fetching
const loadCart = async () => {
    const cart = await $sanctumFetch('/api/cart')
    return cart
}
</script>
```

### Conditional Rendering by User Type
```vue
<template>
    <div>
        <!-- Regular User -->
        <DashboardRegular v-if="user?.type === 'REGULAR'" />

        <!-- Member Dashboard -->
        <DashboardMember v-else-if="user?.type === 'MEMBER'" />

        <!-- Promoter MLM Dashboard -->
        <DashboardPromoter v-else-if="user?.type === 'PROMOTER'" />

        <!-- Advisor Recruitment Tools -->
        <DashboardAdvisor v-else-if="user?.type === 'ADVISOR'" />

        <!-- Mentor Overview -->
        <DashboardMentor v-else-if="user?.type === 'MENTOR'" />
    </div>
</template>

<script setup lang="ts">
const user = useCurrentUser<User>()
</script>
```

---

## 🎯 Key Differences: Cookie vs Token Mode

| Feature | Cookie Mode | Token Mode (Our Choice) |
|---------|-------------|------------------------|
| **Auth Method** | Session cookies + CSRF | Bearer tokens |
| **Domain Requirement** | Same top-level domain | Any domain |
| **Mobile Apps** | ❌ Not supported | ✅ Fully supported |
| **Storage** | HTTP-only cookies | Cookie or localStorage |
| **CSRF Handling** | Auto-managed by module | Not needed |
| **Stateful Domains** | Required in env | Not used |
| **Token in Response** | Not needed | Required: `{ data: { token } }` |
| **Scalability** | Limited (same domain) | High (different domains) |
| **Security** | Session-based | Token-based |

---

## 🚨 Critical Implementation Notes

### ✅ **DO's for Token Mode:**

1. **Return token in login/register response:**
   ```php
   return response()->json([
       'data' => [
           'user' => $user,
           'token' => $token,  // ← REQUIRED
       ],
   ]);
   ```

2. **Configure token extraction:**
   ```typescript
   token: {
       responseKey: 'token',  // Extracts from response.data.token
   }
   ```

3. **Leave SANCTUM_STATEFUL_DOMAINS empty** - not needed for token mode

4. **Enable CORS with credentials:**
   ```php
   'supports_credentials' => true,
   ```

### ❌ **DON'Ts for Token Mode:**

1. ❌ Don't add domains to `SANCTUM_STATEFUL_DOMAINS` (token mode doesn't use it)
2. ❌ Don't expect CSRF cookie handling (token mode uses Bearer tokens)
3. ❌ Don't use `/sanctum/csrf-cookie` endpoint (only for cookie mode)
4. ❌ Don't set `authMode: 'cookie'` if you want mobile app support

---

## 🎓 Learning from Old Commerinity

**What They Used:**
```typescript
laravelSanctum: {
    apiUrl: 'http://localhost:8000',
    authMode: 'cookie',  // ← SPA mode
    userResponseWrapperKey: 'data',
    sanctumEndpoints: {
        csrf: '/sanctum/csrf-cookie',
        login: '/api/login',
        logout: '/api/logout',
    },
}
```

**Why We're Different:**
- Old commerinity: **Cookie mode** (web-only)
- Commerinity Pro: **Token mode** (web + mobile)

**Migration Benefits:**
1. ✅ Same module, just change `authMode: 'token'`
2. ✅ All composables work identically
3. ✅ Mobile app ready (React Native/Flutter)
4. ✅ Better scalability (separate API domain)

---

## 📝 Complete Example: Login Page

```vue
<template>
    <div class="login-page">
        <h1>Login to Commerinity Pro</h1>

        <!-- Login Method Toggle -->
        <div class="method-toggle">
            <button @click="method = 'password'" :class="{ active: method === 'password' }">
                Password
            </button>
            <button @click="method = 'otp'" :class="{ active: method === 'otp' }">
                OTP
            </button>
        </div>

        <!-- Credential Input (Mobile or Email) -->
        <input
            v-model="credential"
            :placeholder="credentialType === 'mobile' ? '+919876543210' : 'email@example.com'"
            type="text"
        />

        <!-- Password Method -->
        <input
            v-if="method === 'password'"
            v-model="password"
            type="password"
            placeholder="Password"
        />

        <!-- OTP Method -->
        <div v-else>
            <button @click="sendOtp" :disabled="otpSent">
                {{ otpSent ? 'OTP Sent' : 'Send OTP' }}
            </button>
            <input
                v-model="otp"
                type="text"
                placeholder="Enter 6-digit OTP"
                maxlength="6"
            />
        </div>

        <!-- Login Button -->
        <button @click="handleLogin" :disabled="loading">
            {{ loading ? 'Logging in...' : 'Login' }}
        </button>

        <p v-if="error" class="error">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
definePageMeta({
    middleware: ['$guest']  // Guest-only page
})

interface User {
    id: number
    uuid: string
    name: string
    mobile: string
    email: string | null
    type: 'REGULAR' | 'MEMBER' | 'PROMOTER' | 'ADVISOR' | 'MENTOR'
    status: string
    referral_code: string
}

const { login } = useSanctum<User>()
const $sanctumFetch = useSanctumFetch()

const credential = ref('')
const password = ref('')
const otp = ref('')
const method = ref<'password' | 'otp'>('password')
const otpSent = ref(false)
const loading = ref(false)
const error = ref('')

const credentialType = computed(() => {
    return credential.value.includes('@') ? 'email' : 'mobile'
})

const sendOtp = async () => {
    try {
        loading.value = true
        error.value = ''

        await $sanctumFetch('/api/auth/send-otp', {
            method: 'POST',
            body: {
                type: credentialType.value,
                value: credential.value,
            },
        })

        otpSent.value = true
    } catch (e: any) {
        error.value = e.data?.message || 'Failed to send OTP'
    } finally {
        loading.value = false
    }
}

const handleLogin = async () => {
    try {
        loading.value = true
        error.value = ''

        const loginData: any = {}

        // Add credential (email or mobile)
        if (credentialType.value === 'email') {
            loginData.email = credential.value
        } else {
            loginData.mobile = credential.value
        }

        // Add password or OTP
        if (method.value === 'password') {
            loginData.password = password.value
        } else {
            loginData.otp = otp.value
        }

        // Login with callback for type-based redirect
        await login(loginData, {}, async (response, user) => {
            if (!user) return await navigateTo('/dashboard')

            // Type-based redirect
            const typeRoutes = {
                'REGULAR': '/dashboard',
                'MEMBER': '/member/dashboard',
                'PROMOTER': '/mlm/dashboard',
                'ADVISOR': '/advisor/recruitment',
                'MENTOR': '/mentor/overview',
            }

            const targetRoute = typeRoutes[user.type] || '/dashboard'
            return await navigateTo(targetRoute)
        })
    } catch (e: any) {
        error.value = e.data?.message || 'Login failed'
    } finally {
        loading.value = false
    }
}
</script>
```

---

## 🎓 Summary: What We Learned

### **Module Features:**
1. ✅ **useSanctum()** - Main composable (login, logout, user, isLoggedIn)
2. ✅ **useCurrentUser()** - Access user data anywhere
3. ✅ **useSanctumFetch()** - Pre-configured ofetch with auto-auth headers
4. ✅ **Middleware** - `$auth` (protected) + `$guest` (guest-only)
5. ✅ **Auto-token management** - Stores token, adds Bearer header automatically

### **Token Mode Advantages:**
- ✅ Mobile app ready (same API for web + mobile)
- ✅ Scalable (API + Frontend on different domains)
- ✅ Clean separation (API is pure REST)
- ✅ No CSRF complexity (just Bearer tokens)
- ✅ Supports multiple frontends (Nuxt, React Native, Flutter)

### **Old Commerinity vs Commerinity Pro:**

| Aspect | Old Commerinity | Commerinity Pro |
|--------|-----------------|-----------------|
| Auth Mode | Cookie (SPA) | Token (API) |
| Platform | Web only | Web + Mobile |
| Domain | Same domain | Any domain |
| Architecture | Monolithic | Microservices-ready |
| Scalability | Limited | High |

### **Decision: Token Mode is CORRECT! ✅**

---

**Next Steps:**
1. ✅ Keep token-based Laravel API (done)
2. Create Nuxt 4 project with token mode config
3. Build auth pages (login, register, password reset)
4. Implement type-based layouts/navigation
5. Test end-to-end authentication flow
