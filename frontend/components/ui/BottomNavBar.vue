<template>
  <div class="flex items-center justify-around h-16 px-2 safe-area-bottom">

    <!-- Home -->
    <NuxtLink
        to="/"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/') }"
        @click="handleNavClick"
    >
      <div class="nav-icon-wrapper">
        <Icon name="mdi:home" class="w-6 h-6" />
        <div v-if="isActiveRoute('/')" class="nav-indicator" />
      </div>
      <span class="nav-label">Home</span>
    </NuxtLink>

    <!-- Categories -->
    <NuxtLink
        to="/category"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/category') }"
        @click="handleNavClick"
    >
      <div class="nav-icon-wrapper">
        <Icon name="mdi:shopping" class="w-6 h-6" />
        <div v-if="isActiveRoute('/category')" class="nav-indicator" />
      </div>
      <span class="nav-label">Shop</span>
    </NuxtLink>

    <!-- Dashboard (Only for logged in users) -->
    <NuxtLink
        v-if="isLoggedIn"
        to="/dashboard"
        class="nav-item nav-item-primary"
        :class="{ 'nav-item-active': isActiveRoute('/dashboard') }"
        @click="handleNavClick"
    >
      <div class="nav-icon-wrapper">
        <div class="nav-icon-primary">
          <Icon name="mdi:view-dashboard" class="w-6 h-6 text-white" />
        </div>
        <div v-if="isActiveRoute('/dashboard')" class="nav-indicator" />
      </div>
      <span class="nav-label">Dashboard</span>
    </NuxtLink>

    <!-- Orders (Only for logged in users) -->
    <NuxtLink
        v-if="isLoggedIn"
        to="/dashboard/orders"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/dashboard/orders') }"
        @click="handleNavClick"
    >
      <div class="nav-icon-wrapper">
        <Icon name="mdi:package-variant" class="w-6 h-6" />
        <div v-if="isActiveRoute('/dashboard/orders')" class="nav-indicator" />
      </div>
      <span class="nav-label">Orders</span>
    </NuxtLink>

    <!-- Account (Only for logged in users) -->
    <NuxtLink
        v-if="isLoggedIn"
        to="/dashboard/account"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/dashboard/account') }"
        @click="handleNavClick"
    >
      <div class="nav-icon-wrapper">
        <Icon name="mdi:account" class="w-6 h-6" />
        <div v-if="isActiveRoute('/dashboard/account')" class="nav-indicator" />
      </div>
      <span class="nav-label">Account</span>
    </NuxtLink>

    <!-- Login (Only for guests) -->
    <NuxtLink
        v-if="!isLoggedIn"
        to="/login"
        class="nav-item"
        :class="{ 'nav-item-active': isActiveRoute('/login') }"
        @click="handleNavClick"
    >
      <div class="nav-icon-wrapper">
        <Icon name="mdi:login" class="w-6 h-6" />
        <div v-if="isActiveRoute('/login')" class="nav-indicator" />
      </div>
      <span class="nav-label">Login</span>
    </NuxtLink>

  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctum } from '#imports'

const route = useRoute()
const { isLoggedIn } = useSanctum()

// Check if route is active
function isActiveRoute(path: string): boolean {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}

// Handle navigation click (for haptic feedback)
function handleNavClick() {
  if (process.client && 'vibrate' in navigator) {
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
</style>
