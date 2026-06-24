<template>
  <div class="flex items-center justify-around h-16 px-2 safe-area-bottom">
    <!-- ========== GUEST NAV ========== -->
    <template v-if="!isLoggedIn">
      <!-- Home -->
      <NuxtLink
        to="/"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:home"
            class="w-6 h-6"
          />
          <div
            v-if="isActiveRoute('/')"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">Home</span>
      </NuxtLink>

      <!-- Shop -->
      <NuxtLink
        to="/shop"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/shop') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:shopping-bag"
            class="w-6 h-6"
          />
          <div
            v-if="isActiveRoute('/shop')"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">Shop</span>
      </NuxtLink>

      <!-- Categories -->
      <NuxtLink
        to="/categories"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/categories') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:grid-2x2"
            class="w-6 h-6"
          />
          <div
            v-if="isActiveRoute('/categories')"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">Category</span>
      </NuxtLink>

      <!-- Login -->
      <NuxtLink
        to="/login"
        class="nav-item nav-item-primary"
        :class="{ 'nav-item-active': isActiveRoute('/login') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <div class="nav-icon-primary">
            <Icon
              name="lucide:log-in"
              class="w-5 h-5 text-white"
            />
          </div>
        </div>
        <span class="nav-label">Login</span>
      </NuxtLink>
    </template>

    <!-- ========== AUTH NAV ========== -->
    <template v-else>
      <!-- Shop (Products with filters) -->
      <NuxtLink
        to="/shop"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/shop') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:shopping-bag"
            class="w-6 h-6"
          />
          <div
            v-if="isActiveRoute('/shop')"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">Shop</span>
      </NuxtLink>

      <!-- Category -->
      <NuxtLink
        to="/categories"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/categories') || isActiveRoute('/category') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:grid-2x2"
            class="w-6 h-6"
          />
          <div
            v-if="isActiveRoute('/categories') || isActiveRoute('/category')"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">Category</span>
      </NuxtLink>

      <!-- Wallet (Center - Primary) -->
      <NuxtLink
        to="/dashboard/wallet"
        class="nav-item nav-item-primary"
        :class="{ 'nav-item-active': isActiveRoute('/dashboard/wallet') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <div class="nav-icon-primary">
            <Icon
              name="lucide:wallet"
              class="w-5 h-5 text-white"
            />
          </div>
        </div>
        <span class="nav-label">Wallet</span>
      </NuxtLink>

      <!-- Account (Profile + Stats) -->
      <NuxtLink
        to="/dashboard"
        class="nav-item"
        :class="{ 'nav-item-active': route.path === '/dashboard' || isActiveRoute('/dashboard/account') || isActiveRoute('/dashboard/profile') }"
        @click="handleNavClick"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:user-circle"
            class="w-6 h-6"
          />
          <div
            v-if="route.path === '/dashboard' || isActiveRoute('/dashboard/account') || isActiveRoute('/dashboard/profile')"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">Account</span>
      </NuxtLink>

      <!-- More Menu -->
      <button
        class="nav-item"
        :class="{ 'nav-item-active': isMoreMenuOpen }"
        @click="toggleMoreMenu"
      >
        <div class="nav-icon-wrapper">
          <Icon
            name="lucide:menu"
            class="w-6 h-6"
          />
          <div
            v-if="isMoreMenuOpen"
            class="nav-indicator"
          />
        </div>
        <span class="nav-label">More</span>
      </button>
    </template>
  </div>

  <!-- More Menu Drawer -->
  <USlideover
    v-model:open="isMoreMenuOpen"
    side="right"
  >
    <template #header>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
          <Icon
            name="lucide:menu"
            class="w-5 h-5 text-white"
          />
        </div>
        <div>
          <h3 class="font-bold text-slate-900 dark:text-white">
            Menu
          </h3>
          <p class="text-xs text-slate-500">
            Quick access
          </p>
        </div>
      </div>
    </template>

    <div class="p-4 space-y-2">
      <!-- Dashboard -->
      <NuxtLink
        to="/dashboard"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-blue-100 dark:bg-blue-900/30 text-blue-600">
          <Icon
            name="lucide:layout-dashboard"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Dashboard</p>
          <p class="text-xs text-slate-500">Overview & stats</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Orders -->
      <NuxtLink
        to="/dashboard/orders"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600">
          <Icon
            name="lucide:package"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">My Orders</p>
          <p class="text-xs text-slate-500">Track your orders</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Team -->
      <NuxtLink
        to="/dashboard/team"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-purple-100 dark:bg-purple-900/30 text-purple-600">
          <Icon
            name="lucide:users"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">My Team</p>
          <p class="text-xs text-slate-500">Community & Team</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Commissions -->
      <NuxtLink
        v-if="showEarnings"
        to="/dashboard/commissions"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-amber-100 dark:bg-amber-900/30 text-amber-600">
          <Icon
            name="lucide:coins"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Commissions</p>
          <p class="text-xs text-slate-500">Earnings & payouts</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Wishlist -->
      <NuxtLink
        to="/dashboard/wishlist"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-pink-100 dark:bg-pink-900/30 text-pink-600">
          <Icon
            name="lucide:heart"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Wishlist</p>
          <p class="text-xs text-slate-500">Saved products</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Cart -->
      <NuxtLink
        to="/cart"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-orange-100 dark:bg-orange-900/30 text-orange-600">
          <Icon
            name="lucide:shopping-cart"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Cart</p>
          <p class="text-xs text-slate-500">View cart items</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <div class="border-t border-slate-200 dark:border-slate-700 my-4" />

      <!-- Addresses -->
      <NuxtLink
        to="/dashboard/addresses"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
          <Icon
            name="lucide:map-pin"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Addresses</p>
          <p class="text-xs text-slate-500">Delivery addresses</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- KYC -->
      <NuxtLink
        to="/dashboard/kyc"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
          <Icon
            name="lucide:shield-check"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">KYC Verification</p>
          <p class="text-xs text-slate-500">Identity verification</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Support -->
      <NuxtLink
        to="/dashboard/faq"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
          <Icon
            name="lucide:headphones"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Help & Support</p>
          <p class="text-xs text-slate-500">Get assistance</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>

      <!-- Settings -->
      <NuxtLink
        to="/dashboard/settings"
        class="menu-item"
        @click="isMoreMenuOpen = false"
      >
        <div class="menu-icon bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
          <Icon
            name="lucide:settings"
            class="w-5 h-5"
          />
        </div>
        <div class="flex-1">
          <p class="font-medium text-slate-900 dark:text-white">Settings</p>
          <p class="text-xs text-slate-500">App preferences</p>
        </div>
        <Icon
          name="lucide:chevron-right"
          class="w-5 h-5 text-slate-400"
        />
      </NuxtLink>
    </div>
  </USlideover>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useSanctum } from '#imports'

const route = useRoute()
const { isLoggedIn } = useSanctum()
const { isMember, isPromoter, isAdvisor } = useUserType()
const showEarnings = computed(() => isMember.value || isPromoter.value || isAdvisor.value)

// More menu state
const isMoreMenuOpen = ref(false)

// Check if route is active
function isActiveRoute(path: string): boolean {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}

// Toggle more menu
function toggleMoreMenu() {
  isMoreMenuOpen.value = !isMoreMenuOpen.value
  handleNavClick()
}

// Handle navigation click (for haptic feedback)
function handleNavClick() {
  if (import.meta.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}
</script>

<style scoped>
/* Safe area for notched devices */
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}

/* Nav Item */
.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 0.5rem;
  color: rgb(107, 114, 128);
  transition: all 0.2s ease;
  position: relative;
  -webkit-tap-highlight-color: transparent;
}

.dark .nav-item {
  color: rgb(156, 163, 175);
}

.nav-item:active {
  transform: scale(0.95);
}

/* Icon Wrapper */
.nav-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.25rem;
}

/* Primary Icon (Dashboard - Special) */
.nav-icon-primary {
  width: 2.75rem;
  height: 2.75rem;
  background: linear-gradient(135deg, rgb(59, 130, 246), rgb(147, 51, 234));
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  transition: all 0.2s ease;
}

.nav-item-primary:active .nav-icon-primary {
  transform: scale(0.9);
}

/* Active Indicator */
.nav-indicator {
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 0.25rem;
  height: 0.25rem;
  background: rgb(59, 130, 246);
  border-radius: 9999px;
  animation: pulse-indicator 2s ease-in-out infinite;
}

.dark .nav-indicator {
  background: rgb(96, 165, 250);
}

@keyframes pulse-indicator {
  0%, 100% {
    opacity: 1;
    transform: translateX(-50%) scale(1);
  }
  50% {
    opacity: 0.6;
    transform: translateX(-50%) scale(1.2);
  }
}

/* Label */
.nav-label {
  font-size: 0.75rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

/* Active State */
.nav-item-active {
  color: rgb(59, 130, 246);
}

.dark .nav-item-active {
  color: rgb(96, 165, 250);
}

.nav-item-active .nav-label {
  font-weight: 600;
}

/* Hover States (for devices that support hover) */
@media (hover: hover) {
  .nav-item:hover {
    color: rgb(59, 130, 246);
  }

  .dark .nav-item:hover {
    color: rgb(96, 165, 250);
  }

  .nav-item:hover .nav-icon-primary {
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
    transform: translateY(-2px);
  }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .nav-item,
  .nav-icon-primary,
  .nav-label {
    transition: none !important;
  }

  .nav-indicator {
    animation: none !important;
  }
}

/* More Menu Item */
.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
  text-decoration: none;
}

.menu-item:hover {
  background-color: rgb(241, 245, 249);
}

.dark .menu-item:hover {
  background-color: rgb(30, 41, 59);
}

.menu-item:active {
  transform: scale(0.98);
}

.menu-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>
