# Frontend Components - Old Commerinity

## Component Architecture

**Pattern**: Custom-built components with Composition API + TypeScript
**No UI Framework**: Pure Tailwind CSS styling

## Component Structure

```
components/
├── ui/                          # Core UI components
├── card/                        # Card components
├── charts/                      # Data visualization
├── dashboard/                   # Dashboard-specific
├── home/                        # Homepage sections
├── [feature]/                   # Feature-based organization
└── [shared]/                    # Shared utilities
```

## Core UI Components

### 1. Navbar (`components/ui/Navbar/DefaultNavbar.vue`)

**Features**:
- Glassmorphism with backdrop blur
- Gradient background layers
- Fixed positioning with scroll detection
- Mobile responsive with hamburger menu
- Dark mode support
- Progressive loading (deferred 100ms)

**Structure**:
```vue
<header class="fixed top-0 left-0 right-0 z-50
       bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl
       border-b border-gray-200/50 dark:border-gray-800/50">
  <div class="max-w-7xl mx-auto px-4">
    <!-- Logo -->
    <!-- Desktop Navigation -->
    <!-- Mobile Menu Button -->
    <!-- User Menu / Auth Buttons -->
  </div>
</header>
```

**Navigation Items**:
- Home
- Shop
- Products
- Categories
- Careers
- Blogs
- About

### 2. Dashboard Sidebar (`components/ui/Navbar/DashboardSidebar.vue`)

**Features**:
- Collapsible (desktop: 20px ↔ 256px)
- Slide-in (mobile) with overlay
- LocalStorage state persistence
- Keyboard shortcut (Ctrl+B)
- Active route highlighting
- Icon-only collapsed state
- Tooltip on collapsed items

**Navigation Structure**:
```typescript
const menuItems = [
  { icon: 'mdi:view-dashboard', label: 'Dashboard', to: '/dashboard' },
  { icon: 'mdi:account-circle', label: 'Profile', to: '/dashboard/profile' },
  { icon: 'mdi:cart', label: 'Orders', to: '/dashboard/orders' },
  { icon: 'mdi:wallet', label: 'Wallet', to: '/dashboard/wallet' },
  { icon: 'mdi:account-group', label: 'My Team', to: '/dashboard/team' },
  { icon: 'mdi:currency-inr', label: 'Incentives', to: '/dashboard/incentives' },
  { icon: 'mdi:cog', label: 'Settings', to: '/dashboard/settings' }
]
```

### 3. Dark Mode Toggle (`components/ui/DarkModeToggle.vue`)

**Features**:
- Sun/Moon icon with smooth transition
- LocalStorage persistence
- System preference detection
- MediaQuery listener
- Smooth rotation animation
- Label for accessibility

**Implementation**:
```vue
<button @click="toggleDarkMode"
        class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800
               hover:bg-gray-200 dark:hover:bg-gray-700
               transition-all duration-300"
        aria-label="Toggle dark mode">
  <Icon :name="isDark ? 'mdi:white-balance-sunny' : 'mdi:moon-waning-crescent'"
        class="w-5 h-5 transition-transform duration-300"
        :class="{ 'rotate-180': isDark }" />
</button>
```

### 4. Bottom Navigation (`components/ui/BottomNavBar.vue`)

**Features**:
- Mobile-only (hidden md:)
- Fixed bottom positioning
- Glassmorphism background
- Active state highlighting
- Icon + label layout
- Safe area padding (iOS)

**Navigation Items**:
```typescript
const bottomNavItems = [
  { icon: 'mdi:home', label: 'Home', to: '/' },
  { icon: 'mdi:store', label: 'Shop', to: '/shop' },
  { icon: 'mdi:cart', label: 'Cart', to: '/cart' },
  { icon: 'mdi:account', label: 'Account', to: '/dashboard' }
]
```

### 5. Footer (`components/ui/Footer/DefaultFooter.vue`)

**Features**:
- Lazy loaded (deferred 3s)
- Scroll trigger (appears after 400px scroll)
- 4-column grid (mobile: 2-column)
- Company info + links
- Social media icons
- Newsletter signup
- Copyright notice

### 6. Global Loader (`components/GlobalLoader.vue`)

**Premium Features**:
- Full-screen overlay
- Gradient animated background
- 3 floating orbs with different speeds
- Brand logo with shimmer effect
- Triple rotating rings
- Pulsing center dot
- Animated dots (bounce with stagger)
- Optional progress bar
- Smooth fade in/out

**Structure**:
```vue
<div class="fixed inset-0 z-50 flex items-center justify-center
     bg-gradient-to-br from-purple-600 via-pink-600 to-blue-600
     backdrop-blur-xl">
  <!-- Floating Orbs (3) -->
  <!-- Logo with Shimmer -->
  <!-- Triple Rings (rotating at different speeds) -->
  <!-- Center Dot (pulsing) -->
  <!-- Animated Dots (bouncing) -->
  <!-- Progress Bar (optional) -->
</div>
```

### 7. Toast Notification (`components/Toast.vue`)

**Advanced Features**:
- Multiple positions (9 total: topLeft, topCenter, topRight, bottomLeft, bottomCenter, bottomRight, center)
- 5 types: success, error, warning, info, question
- 3 sizes: sm, md, lg
- Gradient icon backgrounds
- Progress bar with gradient
- Action buttons (for question type)
- Teleport to body
- TransitionGroup with stagger
- Auto-dismiss with timeout
- Manual close button
- Haptic feedback on mobile
- Glassmorphism background

**Usage via Composable**:
```typescript
const toast = useToast()

// Simple notifications
await toast.success('Order placed successfully!')
await toast.error('Payment failed')
await toast.warning('Low stock')
await toast.info('New message received')

// Question dialog
const confirmed = await toast.question(
  'Delete this item?',
  [
    { label: 'Delete', value: true, color: 'red' },
    { label: 'Cancel', value: false, color: 'gray' }
  ]
)
```

## Card Components

### 1. Product Card (`components/card/ProductCardComponent.vue`)

**Premium Features**:
- Responsive heights (9rem mobile, 13rem desktop)
- Image with hover scale (1.04)
- Badge system (colors: success, warn, danger)
- Star rating with half-stars
- Price with strike-through for discounts
- Discount percentage badge
- Wishlist heart button (outline → filled)
- Quick view button (eye icon)
- Add to cart button
- View product button
- Like/Dislike feedback buttons
- Dark mode support

**Structure**:
```vue
<div class="group border rounded-2xl overflow-hidden
     hover:-translate-y-1 hover:shadow-xl transition-all">
  <!-- Image Container -->
  <div class="relative overflow-hidden">
    <img class="group-hover:scale-104 transition-transform" />
    <!-- Badge (New, Sale, etc.) -->
    <!-- Wishlist Button -->
    <!-- Quick View Button -->
  </div>

  <!-- Content -->
  <div class="p-4">
    <!-- Title -->
    <!-- Rating -->
    <!-- Price (with discount) -->
    <!-- Action Buttons -->
  </div>
</div>
```

### 2. Dashboard Stat Card (`components/dashboard/cards/DashboardStatCard.vue`)

**Features**:
- Gradient icon background
- Label + large value display
- Trend indicator (up/down arrow)
- Percentage change
- Color variants: green, blue, purple, orange, emerald, teal, cyan
- Loading skeleton state
- Hover shadow effect

**Structure**:
```vue
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6
     shadow-sm hover:shadow-md transition-all">
  <!-- Icon Container (gradient background) -->
  <div class="w-12 h-12 rounded-xl bg-gradient-to-br
       from-green-500 to-emerald-600 flex items-center justify-center">
    <Icon name="mdi:currency-inr" class="w-6 h-6 text-white" />
  </div>

  <!-- Stats -->
  <div class="mt-4">
    <p class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</p>
    <p class="text-2xl font-bold">₹1,25,000</p>
    <div class="flex items-center gap-1 text-sm text-green-500">
      <Icon name="mdi:trending-up" />
      <span>+12.5%</span>
    </div>
  </div>
</div>
```

### 3. Benefit Card (`components/home/AffiliateBenefitsSection.vue`)

**Premium Features**:
- Glassmorphism background
- Decorative corner gradient (16x16 blur-lg)
- Numbered badge (1-6)
- Gradient icon container
- Pulsing ring on icon hover
- Feature list with checkmarks
- "Learn More" link with arrow
- Bottom gradient glow on hover
- Shine animation overlay on hover

**Structure**:
```vue
<div class="group relative bg-white/90 dark:bg-gray-800/90
     backdrop-blur-xl rounded-3xl p-8 border
     hover:-translate-y-2 hover:shadow-2xl transition-all">
  <!-- Decorative Corner -->
  <div class="absolute top-0 right-0 w-16 h-16
       bg-gradient-to-br from-purple-500/20 to-transparent
       rounded-bl-3xl blur-lg" />

  <!-- Number Badge -->
  <div class="w-10 h-10 rounded-full bg-gradient-to-br
       from-purple-500 to-pink-500 flex items-center justify-center">
    <span class="text-white font-bold">1</span>
  </div>

  <!-- Icon with Pulsing Ring -->
  <div class="relative mt-4">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br
         from-purple-500 to-pink-600 flex items-center justify-center">
      <Icon name="mdi:rocket-launch" class="w-8 h-8 text-white" />
    </div>
    <!-- Pulsing ring (visible on hover) -->
    <div class="absolute inset-0 rounded-2xl border-2 border-purple-500
         opacity-0 group-hover:opacity-100 group-hover:animate-ping" />
  </div>

  <!-- Content -->
  <h3 class="mt-6 text-xl font-bold">Benefit Title</h3>
  <p class="mt-2 text-gray-600 dark:text-gray-400">Description...</p>

  <!-- Features List -->
  <ul class="mt-4 space-y-2">
    <li class="flex items-center gap-2">
      <Icon name="mdi:check-circle" class="text-green-500" />
      <span>Feature 1</span>
    </li>
  </ul>

  <!-- Learn More Link -->
  <a class="inline-flex items-center gap-2 mt-6 text-purple-600
       hover:text-purple-700 font-semibold group-hover:gap-3 transition-all">
    Learn More <Icon name="mdi:arrow-right" />
  </a>

  <!-- Shine Effect (on hover) -->
  <div class="absolute inset-0 opacity-0 group-hover:opacity-100">
    <div class="absolute inset-0 bg-gradient-to-r
         from-transparent via-white/20 to-transparent
         -translate-x-full group-hover:translate-x-full
         transition-transform duration-700" />
  </div>

  <!-- Bottom Glow -->
  <div class="absolute bottom-0 left-0 right-0 h-1
       bg-gradient-to-r from-purple-500 via-pink-500 to-blue-500
       opacity-0 group-hover:opacity-100 rounded-b-3xl transition-all" />
</div>
```

### 4. Profile Card (`components/dashboard/cards/ProfileCard.vue`)

**Features**:
- User avatar with gradient border
- Username and email
- Membership badge (stage/level)
- Referral code display
- Quick action buttons
- Stats summary (team size, etc.)

## Chart Components

### Orders Trend Chart (`components/charts/OrdersTrendChart.vue`)

**Features**:
- ECharts integration
- Type toggle: line or bar
- Smooth curves with area fill
- Gradient colors by theme
- Responsive legend
- Tooltip on hover
- Date range filter (today, week, month, year)
- Metric toggle (count, revenue)
- Empty state overlay
- Loading skeleton
- Dark mode color adaptation
- Debounced filter updates (200ms)
- Abort controller for request cancellation

**Color Palette**:
- Light: blue-600, green-600, orange-500, red-600
- Dark: blue-400, green-400, orange-400, red-400

**Structure**:
```vue
<div class="bg-white dark:bg-gray-800 rounded-2xl p-6">
  <!-- Header with Filters -->
  <div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-bold">Orders Trend</h3>
    <div class="flex gap-2">
      <!-- Date Range Select -->
      <!-- Metric Toggle -->
      <!-- Chart Type Toggle -->
    </div>
  </div>

  <!-- Chart Container -->
  <ClientOnly>
    <Suspense>
      <VChart :option="chartOptions" class="h-80" />
      <template #fallback>
        <div class="h-80 animate-pulse bg-gray-100 dark:bg-gray-700" />
      </template>
    </Suspense>
  </ClientOnly>
</div>
```

## Form Components

### Button Patterns

**Primary Button**:
```vue
<button class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600
       text-white font-bold rounded-xl shadow-xl
       hover:shadow-2xl hover:scale-105 active:scale-95
       transition-all duration-300">
  Button Text
</button>
```

**Secondary Button**:
```vue
<button class="px-6 py-3 bg-white dark:bg-gray-800
       text-gray-900 dark:text-white font-bold rounded-xl
       border-2 border-gray-200 dark:border-gray-700
       hover:border-purple-500 transition-all">
  Button Text
</button>
```

**Icon Button**:
```vue
<button class="w-10 h-10 bg-gray-100 dark:bg-gray-800
       hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500
       text-gray-600 dark:text-gray-400 hover:text-white
       rounded-xl flex items-center justify-center
       transition-all duration-300 hover:scale-110">
  <Icon name="mdi:heart" class="w-5 h-5" />
</button>
```

### Input Field Pattern

```vue
<input type="text"
       class="w-full px-4 py-3
              bg-slate-50 dark:bg-slate-800
              border border-slate-300 dark:border-slate-600
              rounded-xl
              focus:ring-2 focus:ring-blue-500 focus:border-transparent
              transition-all shadow-sm
              placeholder:text-gray-400" />
```

### Select Dropdown Pattern

```vue
<select class="px-3 py-2
        border border-slate-300 dark:border-slate-600
        rounded-lg
        bg-white dark:bg-slate-800
        hover:border-slate-400
        transition-all">
  <option>Option 1</option>
</select>
```

## Layout Components

### Page Wrapper Pattern

```vue
<div class="min-h-screen bg-gradient-to-br
     from-gray-50 via-blue-50 to-purple-50
     dark:from-gray-950 dark:via-blue-950 dark:to-purple-950">

  <!-- Animated Background Orbs -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div class="w-80 h-80 bg-gradient-to-r from-blue-400/20 to-purple-400/20
         rounded-full blur-3xl opacity-60 animate-float-slow" />
    <div class="w-72 h-72 bg-gradient-to-r from-purple-400/20 to-pink-400/20
         rounded-full blur-3xl opacity-70 animate-float-slower" />
  </div>

  <!-- Content -->
  <div class="relative z-10">
    <slot />
  </div>
</div>
```

### Section Container Pattern

```vue
<section class="py-12 md:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Section Content -->
  </div>
</section>
```

## Component Composition Patterns

### Lazy Loading

```vue
<script setup lang="ts">
const LazyChart = defineAsyncComponent(() =>
  import('~/components/charts/OrdersTrendChart.vue')
)
</script>

<template>
  <ClientOnly>
    <Suspense>
      <LazyChart />
      <template #fallback>
        <div class="h-80 animate-pulse bg-gray-100 dark:bg-gray-700 rounded-2xl" />
      </template>
    </Suspense>
  </ClientOnly>
</template>
```

### Progressive Loading

```vue
<script setup lang="ts">
const showQuickActions = ref(false)
const showCharts = ref(false)

onMounted(() => {
  setTimeout(() => showQuickActions.value = true, 300)
  setTimeout(() => showCharts.value = true, 1500)
})
</script>
```

### State Management in Components

```vue
<script setup lang="ts">
// Props
const props = defineProps<{
  title: string
  icon?: string
  variant?: 'primary' | 'secondary'
}>()

// Emits
const emit = defineEmits<{
  (e: 'click'): void
  (e: 'update', value: string): void
}>()

// Local state
const isLoading = ref(false)
const data = ref<any[]>([])

// Computed
const isEmpty = computed(() => data.value.length === 0)

// Methods
const fetchData = async () => {
  isLoading.value = true
  try {
    const response = await useSanctumFetch('/api/endpoint')
    data.value = response.data
  } catch (error) {
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchData()
})
</script>
```

## Component Best Practices

### 1. Naming Convention
- PascalCase for files: `ProductCardComponent.vue`
- Suffix patterns: `-Component`, `-Card`, `-Section`, `-Modal`

### 2. Single Responsibility
- Each component does one thing well
- Extract complex logic to composables
- Keep components under 300 lines

### 3. Props Validation
```typescript
interface Props {
  title: string
  icon?: string
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
})
```

### 4. Accessibility
- ARIA labels on interactive elements
- Semantic HTML
- Keyboard navigation support
- Focus states

### 5. Performance
- Lazy load heavy components
- Use `v-show` for frequently toggled elements
- Use `v-if` for conditionally rendered elements
- Memoize expensive computations

### 6. Dark Mode
- Always provide dark mode variants
- Use `dark:` prefix consistently
- Test both modes

### 7. Responsive Design
- Mobile-first approach
- Test all breakpoints
- Consider touch targets (44x44 minimum)

## Migration to Nuxt UI

### Components to Replace

1. **UButton** - Replace custom buttons
2. **UInput** - Replace input fields
3. **USelect** - Replace select dropdowns
4. **UCard** - Base for custom cards
5. **UBadge** - Badge components
6. **UAvatar** - User avatars
7. **UModal** - Modals and dialogs
8. **UDropdown** - Dropdown menus
9. **UTable** - Data tables
10. **UToast** - Toast notifications (or keep custom)

### Components to Keep

1. **Global Loader** - Unique design
2. **Charts** - ECharts integration
3. **Benefit Cards** - Custom premium design
4. **Product Cards** - Specific e-commerce design
5. **Dashboard Layouts** - Custom layout logic
6. **GSAP Animations** - Complex animations

### Strategy

1. Start with low-hanging fruit (buttons, inputs)
2. Configure Nuxt UI theme to match existing design
3. Gradually replace components
4. Keep custom classes for unique styling
5. Test thoroughly in both light and dark modes
