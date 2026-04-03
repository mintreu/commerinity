# Frontend Architecture - Nuxt 4 + Nuxt UI
## Clean, Tested, Enterprise-Grade, Premium Design

---

## 🎯 **Goals**

1. ✅ **Maintain premium glassmorphism design** (from old)
2. ✅ **Clean component architecture** (atomic design)
3. ✅ **DRY principle** (no duplication)
4. ✅ **Performance optimized** (<500KB initial bundle)
5. ✅ **Fully tested** (Vitest + Playwright)
6. ✅ **Type-safe** (TypeScript throughout)
7. ✅ **Accessible** (WCAG AA compliance)

---

## 🏗️ **Clean Architecture**

### **Directory Structure**

```
client/app/
├── components/
│   ├── ui/                       # Nuxt UI wrappers + custom
│   │   ├── Button.vue           # UButton with variants
│   │   ├── Card.vue             # UCard with glass effect
│   │   ├── Input.vue            # UInput styled
│   │   └── Badge.vue            # UBadge styled
│   │
│   ├── forms/                    # Reusable form components
│   │   ├── FormField.vue        # Label + Input + Error
│   │   ├── OTPInput.vue         # 6-digit OTP
│   │   ├── PasswordInput.vue    # Password with visibility toggle
│   │   ├── AddressForm.vue      # Reusable address form
│   │   └── FormButton.vue       # Submit button with loading
│   │
│   ├── layout/                   # Layout components
│   │   ├── Navbar.vue
│   │   ├── Sidebar.vue
│   │   ├── Footer.vue
│   │   ├── BottomNav.vue
│   │   └── PageHeader.vue       # Breadcrumbs + title
│   │
│   └── features/                 # Feature-specific
│       ├── auth/
│       │   ├── LoginForm.vue
│       │   ├── RegisterWizard.vue
│       │   └── OTPVerification.vue
│       │
│       ├── product/
│       │   ├── ProductCard.vue  # UNIFIED card (compact/full variants)
│       │   ├── ProductFilters.vue
│       │   ├── ProductGrid.vue
│       │   ├── ProductPrice.vue
│       │   └── ProductReviews.vue
│       │
│       ├── cart/
│       │   ├── CartItem.vue
│       │   ├── CartSummary.vue
│       │   ├── CartEmpty.vue
│       │   └── CartButton.vue
│       │
│       ├── order/
│       │   ├── OrderCard.vue
│       │   ├── OrderTimeline.vue
│       │   └── OrderTracking.vue
│       │
│       ├── dashboard/
│       │   ├── StatCard.vue
│       │   ├── RevenueChart.vue
│       │   └── ActivityFeed.vue
│       │
│       └── affiliate/
│           ├── GenealgyTree.vue  # D3.js org chart
│           ├── CommissionCard.vue
│           └── ReferralLink.vue
│
├── composables/
│   ├── useAuth.ts               # Wrapper for useSanctum
│   ├── useCart.ts               # Cart state (Pinia)
│   ├── useWishlist.ts           # Wishlist state
│   ├── useToast.ts              # Toast notifications
│   └── useApi.ts                # API wrapper with retry
│
├── stores/                       # Pinia stores
│   ├── cart.ts
│   ├── wishlist.ts
│   └── user.ts
│
├── layouts/
│   ├── default.vue              # Main layout (simplified)
│   ├── dashboard.vue            # Dashboard layout
│   └── auth.vue                 # Auth layout (simplified)
│
├── pages/                        # File-based routing
│   ├── index.vue                # Homepage
│   ├── shop/
│   ├── product/
│   ├── cart/
│   ├── dashboard/
│   └── auth/
│
├── middleware/
│   ├── auth.ts                  # Auth guard
│   └── guest.ts                 # Guest only
│
├── assets/
│   └── css/
│       └── main.css             # Tailwind 4 + theme
│
├── app.config.ts                # Nuxt UI theme
├── nuxt.config.ts               # Nuxt configuration
└── tailwind.config.ts           # Tailwind 4 config
```

---

## 🎨 **Theming System**

### **app.config.ts (Nuxt UI Theme)**

```typescript
export default defineAppConfig({
  ui: {
    primary: 'blue',
    gray: 'slate',

    // Global button config
    button: {
      base: 'font-bold transition-all duration-300',
      rounded: 'rounded-xl',

      size: {
        lg: 'px-6 py-3 text-base',
        xl: 'px-8 py-4 text-lg'
      },

      variant: {
        // Custom gradient variants
        'gradient-primary': 'bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg hover:shadow-xl hover:-translate-y-1',
        'gradient-success': 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white shadow-lg hover:shadow-xl hover:-translate-y-1',
        'gradient-danger': 'bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white shadow-lg hover:shadow-xl',

        // Glass variant
        'glass': 'bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/30 dark:border-slate-700/50 hover:bg-white/95 dark:hover:bg-slate-800/95'
      }
    },

    // Card configuration
    card: {
      base: 'overflow-hidden transition-all duration-300',
      rounded: 'rounded-2xl',
      shadow: 'shadow-xl hover:shadow-2xl',

      variant: {
        'glass': 'bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60'
      },

      body: {
        padding: 'p-6'
      }
    },

    // Input configuration
    input: {
      base: 'w-full transition-all',
      rounded: 'rounded-xl',

      size: {
        lg: 'px-4 py-3 text-base'
      },

      variant: {
        'glass': 'bg-white/50 dark:bg-slate-700/50 backdrop-blur-xl border-2 border-white/30 dark:border-slate-600/50 focus:border-blue-500'
      }
    },

    // Modal configuration
    modal: {
      rounded: 'rounded-2xl',
      background: 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl',
      padding: 'p-6',

      overlay: {
        background: 'bg-black/60 backdrop-blur-sm'
      }
    }
  }
})
```

---

### **assets/css/main.css (Tailwind 4)**

```css
@import "tailwindcss";

@theme {
  /* === BRAND COLORS === */
  --color-brand-primary: #3b82f6;
  --color-brand-secondary: #a855f7;
  --color-brand-accent: #ec4899;

  /* === GLASS EFFECT VARIABLES === */
  --glass-bg: rgb(255 255 255 / 0.9);
  --glass-bg-dark: rgb(15 23 42 / 0.9);
  --glass-border: rgb(255 255 255 / 0.3);
  --glass-border-dark: rgb(51 65 85 / 0.6);
  --glass-blur: 24px;

  /* === BORDER RADIUS === */
  --radius-card: 16px;
  --radius-button: 12px;
  --radius-input: 12px;

  /* === SPACING === */
  --space-section: 80px;
  --space-card: 24px;
}

@layer components {
  /* === GLASS EFFECT === */
  .glass-card {
    background: var(--glass-bg);
    backdrop-filter: blur(var(--glass-blur));
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-card);
    box-shadow: 0 8px 32px 0 rgb(31 38 135 / 0.07);
  }

  @media (prefers-color-scheme: dark) {
    .glass-card {
      background: var(--glass-bg-dark);
      border-color: var(--glass-border-dark);
    }
  }

  /* === GRADIENT TEXT === */
  .text-gradient {
    @apply bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600
           bg-clip-text text-transparent;
  }

  /* === CONTAINER === */
  .container-section {
    @apply max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12
           py-12 sm:py-16 md:py-20;
  }

  /* === GRADIENT BUTTONS (if not using Nuxt UI variants) === */
  .btn-gradient-primary {
    @apply px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600
           hover:from-blue-700 hover:to-purple-700 text-white
           rounded-xl font-bold shadow-lg hover:shadow-xl
           transition-all duration-300 hover:-translate-y-1;
  }
}

@layer utilities {
  /* === ANIMATIONS === */
  .animate-float {
    animation: float 6s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
  }

  /* === FOCUS === */
  *:focus-visible {
    @apply outline-2 outline-offset-2 outline-blue-500;
  }
}
```

---

## 🧩 **Reusable Components**

### **1. Form Components**

```vue
<!-- components/forms/FormField.vue -->
<template>
  <UFormGroup
    :label="label"
    :error="error"
    :hint="hint"
    :required="required"
  >
    <slot />
  </UFormGroup>
</template>

<script setup lang="ts">
defineProps<{
  label?: string
  error?: string
  hint?: string
  required?: boolean
}>()
</script>
```

```vue
<!-- components/forms/OTPInput.vue -->
<template>
  <div class="flex gap-2 justify-center">
    <UInput
      v-for="(digit, index) in digits"
      :key="index"
      v-model="digits[index]"
      maxlength="1"
      type="text"
      inputmode="numeric"
      class="w-12 text-center text-2xl font-bold"
      @input="handleInput(index, $event)"
      @keydown.backspace="handleBackspace(index, $event)"
      @paste="handlePaste"
    />
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue?: string
  length?: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'complete', value: string): void
}>()

const digits = ref<string[]>(Array(props.length || 6).fill(''))

const handleInput = (index: number, event: any) => {
  const value = event.target.value
  if (value && index < digits.value.length - 1) {
    // Focus next input
    const nextInput = event.target.nextElementSibling
    nextInput?.focus()
  }

  updateModel()
}

const updateModel = () => {
  const code = digits.value.join('')
  emit('update:modelValue', code)

  if (code.length === digits.value.length) {
    emit('complete', code)
  }
}
</script>
```

---

### **2. Product Components**

```vue
<!-- components/features/product/ProductCard.vue - UNIFIED -->
<template>
  <UCard
    :ui="{
      base: 'group hover:-translate-y-1 transition-all',
      body: { padding: 'p-4' }
    }"
  >
    <!-- Image -->
    <template #header>
      <div class="relative aspect-square overflow-hidden">
        <NuxtImg
          :src="product.thumbnail"
          :alt="product.name"
          loading="lazy"
          sizes="sm:300px md:400px"
          format="webp"
          class="object-cover w-full h-full group-hover:scale-105 transition-transform"
        />

        <!-- Badges -->
        <div class="absolute top-2 left-2 flex flex-col gap-2">
          <UBadge v-if="product.discount" color="red" size="lg">
            {{ product.discount }}% OFF
          </UBadge>
          <UBadge v-if="product.isNew" color="green">
            NEW
          </UBadge>
        </div>

        <!-- Wishlist -->
        <button
          class="absolute top-2 right-2 w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm
                 flex items-center justify-center hover:scale-110 transition-transform"
          @click="toggleWishlist"
          aria-label="Add to wishlist"
        >
          <Icon
            :name="isWishlisted ? 'mdi:heart' : 'mdi:heart-outline'"
            :class="isWishlisted ? 'text-red-500' : 'text-gray-600'"
            class="w-5 h-5"
          />
        </button>
      </div>
    </template>

    <!-- Content -->
    <div class="space-y-2">
      <NuxtLink :to="`/product/${product.url}`">
        <h3 class="font-bold line-clamp-2 hover:text-blue-600">
          {{ product.name }}
        </h3>
      </NuxtLink>

      <ProductPrice :product="product" />

      <div v-if="product.rating" class="flex items-center gap-1">
        <Icon
          v-for="i in 5"
          :key="i"
          :name="i <= product.rating ? 'mdi:star' : 'mdi:star-outline'"
          class="w-4 h-4 text-yellow-500"
        />
        <span class="text-sm text-gray-600">({{ product.reviews }})</span>
      </div>
    </div>

    <!-- Footer -->
    <template #footer>
      <UButton
        variant="gradient-primary"
        block
        size="lg"
        :loading="adding"
        @click="addToCart"
      >
        <Icon name="mdi:cart-plus" />
        Add to Cart
      </UButton>
    </template>
  </UCard>
</template>

<script setup lang="ts">
const props = defineProps<{
  product: Product
  variant?: 'compact' | 'full'
}>()

const cartStore = useCartStore()
const { addToWishlist } = useWishlist()

const adding = ref(false)
const isWishlisted = ref(false)

const addToCart = async () => {
  adding.value = true
  try {
    await cartStore.addToCart(props.product.sku, 1)
    useToast().success('Added to cart')
  } finally {
    adding.value = false
  }
}

const toggleWishlist = async () => {
  await addToWishlist(props.product.url)
  isWishlisted.value = !isWishlisted.value
}
</script>
```

---

## 🔐 **Authentication Setup**

### **Package: @qirolab/nuxt-sanctum-authentication**

```typescript
// nuxt.config.ts

export default defineNuxtConfig({
  modules: [
    '@nuxt/ui',
    '@qirolab/nuxt-sanctum-authentication',
    // ... other modules
  ],

  laravelSanctum: {
    apiUrl: process.env.NUXT_PUBLIC_WEB_BASE || 'http://localhost:8000',
    authMode: 'cookie',
    userResponseWrapperKey: 'data',
    sanctumEndpoints: {
      csrf: '/sanctum/csrf-cookie',
      login: '/api/login',
      logout: '/api/logout',
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      webBase: process.env.NUXT_PUBLIC_WEB_BASE || 'http://localhost:8000',
    }
  }
})
```

### **Usage in Components**

```vue
<script setup lang="ts">
const { user, isLoggedIn, login, logout } = useSanctum()

// Login
const handleLogin = async (credentials) => {
  try {
    await login(credentials)
    navigateTo('/dashboard')
  } catch (error) {
    useToast().error('Login failed')
  }
}

// Logout
const handleLogout = async () => {
  await logout()
  navigateTo('/')
}

// Protected API calls
const fetchOrders = async () => {
  const { data } = await useSanctumFetch('/api/orders')
  return data
}
</script>
```

---

## 📦 **State Management (Pinia)**

### **Cart Store**

```typescript
// stores/cart.ts

import { defineStore } from 'pinia'
import { useLocalStorage } from '@vueuse/core'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
    summary: null as CartSummary | null,
    loading: false,

    // Guest cart credentials
    guestId: useLocalStorage('guest_id', null),
    guestToken: useLocalStorage('guest_token', null),
  }),

  getters: {
    itemCount: (state) => state.summary?.quantity || 0,
    total: (state) => state.summary?.total?.formatted || '₹0.00',
    isEmpty: (state) => state.items.length === 0,
  },

  actions: {
    async fetchCart() {
      this.loading = true
      try {
        const { data } = await useSanctumFetch('/api/cart')
        this.items = data.items
        this.summary = data.summary
      } finally {
        this.loading = false
      }
    },

    async addToCart(sku: string, quantity: number = 1) {
      const { data } = await useSanctumFetch(`/api/cart/add/${sku}`, {
        method: 'POST',
        body: { quantity }
      })

      this.items = data.items
      this.summary = data.summary
    },

    async updateQuantity(sku: string, quantity: number) {
      const { data } = await useSanctumFetch(`/api/cart/update/${sku}`, {
        method: 'POST',
        body: { quantity }
      })

      this.items = data.items
      this.summary = data.summary
    },

    async removeItem(sku: string) {
      await useSanctumFetch(`/api/cart/remove/${sku}`, {
        method: 'DELETE'
      })

      await this.fetchCart()
    },

    async clearCart() {
      await useSanctumFetch('/api/cart/clear', {
        method: 'POST'
      })

      this.items = []
      this.summary = null
    }
  }
})
```

---

## ⚡ **Performance Optimization**

### **1. Image Optimization**

```bash
# Install Nuxt Image
npm install @nuxt/image
```

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
  modules: ['@nuxt/image'],

  image: {
    quality: 80,
    format: ['webp', 'avif'],
    screens: {
      xs: 320,
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280,
    }
  }
})
```

### **2. Code Splitting**

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
  routeRules: {
    // Prerender static pages
    '/': { prerender: true },
    '/about': { prerender: true },

    // SWR for product pages (3600s cache)
    '/product/**': { swr: 3600 },

    // Client-only for dashboard
    '/dashboard/**': { ssr: false },
  }
})
```

### **3. Lazy Loading Heavy Components**

```vue
<script setup>
// Only import when needed
const GenealgyTree = defineAsyncComponent(() =>
  import('~/components/features/affiliate/GenealgyTree.vue')
)

const showTree = ref(false)

onMounted(() => {
  // Load after 1 second
  setTimeout(() => showTree.value = true, 1000)
})
</script>

<template>
  <ClientOnly>
    <Suspense v-if="showTree">
      <GenealgyTree />
      <template #fallback>
        <div class="h-96 glass-card animate-pulse" />
      </template>
    </Suspense>
  </ClientOnly>
</template>
```

---

## ✅ **Testing Strategy**

### **1. Component Tests (Vitest)**

```typescript
// tests/components/ProductCard.spec.ts

import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ProductCard from '~/components/features/product/ProductCard.vue'

describe('ProductCard', () => {
  const mockProduct = {
    name: 'Test Product',
    price: { formatted: '₹499.00' },
    thumbnail: '/test.jpg',
    url: 'test-product',
    sku: 'TEST-001'
  }

  it('renders product information', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct }
    })

    expect(wrapper.text()).toContain('Test Product')
    expect(wrapper.text()).toContain('₹499.00')
  })

  it('emits add-to-cart on button click', async () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct }
    })

    await wrapper.find('[data-test="add-to-cart"]').trigger('click')

    expect(wrapper.emitted('add-to-cart')).toBeTruthy()
  })

  it('shows wishlist button', () => {
    const wrapper = mount(ProductCard, {
      props: { product: mockProduct }
    })

    expect(wrapper.find('[aria-label="Add to wishlist"]').exists()).toBe(true)
  })
})
```

### **2. E2E Tests (Playwright)**

```typescript
// tests/e2e/checkout.spec.ts

import { test, expect } from '@playwright/test'

test.describe('Checkout Flow', () => {
  test('complete purchase flow', async ({ page }) => {
    // 1. Add product to cart
    await page.goto('/product/test-product')
    await page.click('[data-test="add-to-cart"]')
    await expect(page.locator('[data-test="toast-success"]')).toBeVisible()

    // 2. Go to cart
    await page.click('[data-test="cart-link"]')
    await expect(page).toHaveURL('/cart')

    // 3. Proceed to checkout
    await page.click('[data-test="checkout-button"]')

    // 4. Fill shipping address
    await page.fill('[name="address_line_1"]', '123 Test St')
    await page.fill('[name="city"]', 'Mumbai')
    await page.fill('[name="pincode"]', '400001')

    // 5. Select payment method
    await page.click('[data-test="payment-cod"]')

    // 6. Place order
    await page.click('[data-test="place-order"]')

    // 7. Verify success
    await expect(page.locator('[data-test="order-success"]')).toBeVisible()
  })
})
```

---

## 📊 **Performance Targets**

### **Lighthouse Scores**
- Performance: > 90
- Accessibility: > 95
- Best Practices: > 95
- SEO: > 95

### **Bundle Size**
- Initial bundle: < 500KB
- Route bundles: < 200KB each
- Lazy chunks: < 100KB each

### **Loading Metrics**
- First Contentful Paint: < 1.5s
- Time to Interactive: < 3.5s
- Largest Contentful Paint: < 2.5s

---

## 🎯 **Migration Checklist**

### Week 1-2: Setup
- [ ] Install Nuxt 4 + Nuxt UI
- [ ] Configure theme (app.config.ts)
- [ ] Setup Tailwind 4 with CSS variables
- [ ] Configure Sanctum auth
- [ ] Install dev tools (Vitest, Playwright)

### Week 3-4: Base Components
- [ ] Create form components (FormField, OTPInput, etc.)
- [ ] Create layout components (Navbar, Sidebar, Footer)
- [ ] Setup Pinia stores (cart, wishlist, user)
- [ ] Migrate composables

### Week 5-6: Feature Components
- [ ] **Consolidate ProductCard** (delete duplicates)
- [ ] Create product components (Filters, Grid, Detail)
- [ ] Create cart components
- [ ] Create order components
- [ ] Create dashboard components

### Week 7-8: Pages
- [ ] Migrate homepage
- [ ] Migrate product pages
- [ ] Migrate cart/checkout
- [ ] Migrate dashboard pages
- [ ] Simplify auth pages

### Week 9-10: Testing & Optimization
- [ ] Write component tests (80% coverage)
- [ ] Write E2E tests (critical flows)
- [ ] Performance audit
- [ ] Accessibility audit
- [ ] Bundle size optimization

---

## ✅ **Summary**

### Problems with Old Frontend:
❌ Component duplication (3 product cards, 2 registration forms)
❌ No theming system (inline Tailwind everywhere)
❌ Performance issues (GSAP in layouts, 1.2MB bundle)
❌ No tests (zero coverage)
❌ Overengineered layouts (20KB auth layout)

### New Frontend Will Have:
✅ **Clean architecture** (atomic design)
✅ **Unified components** (no duplicates)
✅ **Theming system** (Nuxt UI + Tailwind 4 CSS vars)
✅ **Performance optimized** (<500KB bundle)
✅ **Fully tested** (Vitest + Playwright)
✅ **Type-safe** (TypeScript)
✅ **Accessible** (WCAG AA)
✅ **Premium design** (glassmorphism preserved)

---

**Estimated Timeline**: 10 weeks
**Bundle Reduction**: 1.2MB → 500KB (58% smaller)
**Test Coverage**: 0% → 80%
