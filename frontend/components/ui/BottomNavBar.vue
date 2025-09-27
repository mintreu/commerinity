<template>
  <!-- Enhanced Mobile Bottom Navigation -->
  <nav
      v-if="isLoggedIn"
      class="bottom-nav-container fixed bottom-0 left-0 right-0 z-50 md:hidden"
      ref="bottomNavRef"
  >
    <!-- Background Blur Effect -->
    <div class="nav-background absolute inset-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border-t border-white/30 dark:border-gray-800/50 shadow-2xl">
      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-white/20 to-transparent dark:from-gray-900/20 pointer-events-none"></div>
    </div>

    <!-- Navigation Content -->
    <div class="nav-content relative z-10 flex justify-around items-center h-20 px-2 py-2">
      <button
          v-for="(tab, index) in tabs"
          :key="tab.name"
          @click="navigate(tab.to)"
          class="nav-tab flex flex-col items-center justify-center text-center flex-1 relative transition-all duration-300 rounded-2xl p-2 group"
          :class="getTabClasses(tab.to)"
          :ref="el => { if (el) tabRefs[index] = el }"
      >
        <!-- Active Background -->
        <div
            v-if="activeTab === tab.to"
            class="tab-active-bg absolute inset-0 bg-gradient-to-t from-blue-500/20 to-purple-500/20 dark:from-blue-400/20 dark:to-purple-400/20 rounded-2xl transition-all duration-300"
        ></div>

        <!-- Icon Container -->
        <div class="icon-container relative mb-1 transition-all duration-300" :class="getIconClasses(tab.to)">
          <!-- Active Glow Effect -->
          <div
              v-if="activeTab === tab.to"
              class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur-lg opacity-30 scale-150"
          ></div>

          <!-- Icon -->
          <Icon
              :name="getTabIcon(tab)"
              class="relative z-10 transition-all duration-300"
              :class="getIconSizeClasses(tab.to)"
          />

          <!-- Badge -->
          <div
              v-if="tab.badge"
              class="badge absolute -top-2 -right-2 min-w-[18px] h-[18px] bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-black rounded-full flex items-center justify-center shadow-lg animate-pulse border-2 border-white dark:border-gray-900"
          >
            {{ tab.badge }}
          </div>

          <!-- Notification Dot -->
          <div
              v-if="tab.notification"
              class="notification-dot absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full border-2 border-white dark:border-gray-900 animate-ping"
          ></div>
        </div>

        <!-- Label -->
        <span
            class="tab-label text-xs font-semibold transition-all duration-300 leading-tight"
            :class="getLabelClasses(tab.to)"
        >
          {{ tab.label }}
        </span>

        <!-- Ripple Effect -->
        <div class="ripple absolute inset-0 rounded-2xl overflow-hidden">
          <div class="ripple-effect absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 scale-0 rounded-full transition-transform duration-300 group-active:scale-100"></div>
        </div>
      </button>
    </div>

    <!-- Active Tab Indicator -->
    <div
        class="tab-indicator absolute bottom-0 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-300 ease-out"
        :style="indicatorStyle"
    ></div>
  </nav>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSanctum } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Composables
const { user, isLoggedIn } = useSanctum()
const router = useRouter()
const route = useRoute()

// Refs
const bottomNavRef = ref<HTMLElement>()
const tabRefs = ref<(HTMLElement | null)[]>([])
const indicatorStyle = ref({ left: '0px', width: '0px' })

let gsapContext: any = null

// Computed properties
const activeTab = computed(() => {
  const path = route.path
  if (path.startsWith('/dashboard/orders') || path.startsWith('/cart')) return '/dashboard/orders'
  if (path.startsWith('/category')) return '/category'
  if (path.startsWith('/dashboard/wallet')) return '/dashboard/wallet'
  if (path.startsWith('/dashboard/account')) return '/dashboard/account'
  if (path.startsWith('/dashboard/subscribe-now')) return '/dashboard/subscribe-now'
  return '/dashboard'
})

const tabs = computed(() => {
  const baseItems = [
    {
      name: 'home',
      to: '/dashboard',
      label: 'Home',
      icon: 'mdi:home',
      activeIcon: 'mdi:home',
      color: 'blue'
    },
    {
      name: 'shop',
      to: '/category',
      label: 'Shop',
      icon: 'mdi:storefront-outline',
      activeIcon: 'mdi:storefront',
      color: 'purple'
    },
    {
      name: 'orders',
      to: '/dashboard/orders',
      label: 'Orders',
      icon: 'mdi:clipboard-list-outline',
      activeIcon: 'mdi:clipboard-list',
      color: 'green',
      notification: false // Set to true if there are new orders
    },
    {
      name: 'wallet',
      to: '/dashboard/wallet',
      label: 'Wallet',
      icon: 'mdi:wallet-outline',
      activeIcon: 'mdi:wallet',
      color: 'orange'
    },
    {
      name: 'account',
      to: '/dashboard/account',
      label: 'Account',
      icon: 'mdi:account-outline',
      activeIcon: 'mdi:account',
      color: 'indigo'
    }
  ]

  // Add subscription tab for draft users
  if (user.value?.status?.toLowerCase() === 'draft') {
    baseItems.push({
      name: 'subscribe',
      to: '/dashboard/subscribe-now',
      label: 'Subscribe',
      icon: 'mdi:star-outline',
      activeIcon: 'mdi:star',
      badge: 'New',
      color: 'pink'
    })
  }

  return baseItems
})

// Styling methods
const getTabClasses = (tabPath: string) => {
  const isActive = activeTab.value === tabPath
  return {
    'nav-tab-active': isActive,
    'nav-tab-inactive': !isActive,
    'transform scale-110': isActive,
    'hover:scale-105': !isActive
  }
}

const getIconClasses = (tabPath: string) => {
  const isActive = activeTab.value === tabPath
  return {
    'w-10 h-10 rounded-2xl flex items-center justify-center': true,
    'bg-gradient-to-r from-blue-500 to-purple-500 shadow-lg': isActive,
    'bg-gray-100 dark:bg-gray-800': !isActive,
    'scale-110': isActive
  }
}

const getIconSizeClasses = (tabPath: string) => {
  const isActive = activeTab.value === tabPath
  return {
    'w-6 h-6': true,
    'text-white': isActive,
    'text-gray-600 dark:text-gray-400': !isActive
  }
}

const getLabelClasses = (tabPath: string) => {
  const isActive = activeTab.value === tabPath
  return {
    'text-blue-600 dark:text-blue-400 font-bold': isActive,
    'text-gray-500 dark:text-gray-400': !isActive
  }
}

const getTabIcon = (tab: any) => {
  return activeTab.value === tab.to ? tab.activeIcon : tab.icon
}

// Navigation method
function navigate(to: string) {
  // Add haptic feedback on supported devices
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }

  router.push(to).catch((err) => {
    if (err.name !== 'NavigationDuplicated') console.error(err)
  })
}

// Update indicator position
const updateIndicator = async () => {
  await nextTick()

  const activeIndex = tabs.value.findIndex(tab => tab.to === activeTab.value)
  if (activeIndex === -1 || !tabRefs.value[activeIndex]) {
    indicatorStyle.value = { left: '0px', width: '0px' }
    return
  }

  const activeElement = tabRefs.value[activeIndex]
  if (!activeElement) return

  const rect = activeElement.getBoundingClientRect()
  const containerRect = bottomNavRef.value?.getBoundingClientRect()

  if (!containerRect) return

  const left = rect.left - containerRect.left + 8
  const width = rect.width - 16

  indicatorStyle.value = {
    left: `${left}px`,
    width: `${width}px`
  }
}

// GSAP animations
const initializeAnimations = () => {
  if (!process.client || !gsap || !bottomNavRef.value) return

  gsapContext = gsap.context(() => {
    // Initial entrance animation
    gsap.fromTo(bottomNavRef.value,
        { y: 100, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.8,
          ease: 'back.out(1.7)'
        }
    )

    // Tab entrance animations
    gsap.fromTo('.nav-tab',
        { y: 20, opacity: 0, scale: 0.8 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          duration: 0.6,
          stagger: 0.1,
          ease: 'back.out(1.7)',
          delay: 0.2
        }
    )
  })
}

// Watch for route changes
watch(activeTab, () => {
  updateIndicator()

  // Animate active tab change
  if (process.client && gsap) {
    const activeIndex = tabs.value.findIndex(tab => tab.to === activeTab.value)
    if (activeIndex !== -1 && tabRefs.value[activeIndex]) {
      gsap.fromTo(tabRefs.value[activeIndex],
          { scale: 0.9 },
          {
            scale: 1.1,
            duration: 0.3,
            ease: 'back.out(1.7)',
            yoyo: true,
            repeat: 1
          }
      )
    }
  }
}, { immediate: true })

// Lifecycle
onMounted(() => {
  setTimeout(() => {
    initializeAnimations()
    updateIndicator()
  }, 100)

  // Update indicator on resize
  window.addEventListener('resize', updateIndicator)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }

  if (process.client) {
    window.removeEventListener('resize', updateIndicator)
  }
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Bottom navigation container */
.bottom-nav-container {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Navigation background effects */
.nav-background {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
}

.nav-background::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.03), rgba(147, 51, 234, 0.03));
  pointer-events: none;
}

/* Navigation tabs */
.nav-tab {
  position: relative;
  overflow: hidden;
  min-height: 64px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-tab:active {
  transform: scale(0.95);
}

/* Active tab styling */
.nav-tab-active {
  transform: scale(1.05);
}

.nav-tab-inactive:hover {
  transform: scale(1.02);
}

/* Icon container effects */
.icon-container {
  position: relative;
  transition: all 0.3s ease;
}

.icon-container::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
  border-radius: 1rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.nav-tab:hover .icon-container::before {
  opacity: 1;
}

/* Badge animations */
.badge {
  animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Notification dot */
.notification-dot {
  animation: notificationPing 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes notificationPing {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

/* Label transitions */
.tab-label {
  transition: all 0.3s ease;
  white-space: nowrap;
}

/* Ripple effect */
.ripple {
  position: absolute;
  inset: 0;
  overflow: hidden;
  border-radius: 1rem;
}

.ripple-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0);
  border-radius: 50%;
  width: 100px;
  height: 100px;
  transition: transform 0.3s ease;
}

.nav-tab:active .ripple-effect {
  transform: translate(-50%, -50%) scale(1);
}

/* Tab indicator */
.tab-indicator {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  height: 3px;
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

/* Active background effect */
.tab-active-bg {
  border-radius: 1rem;
  animation: activeGlow 2s ease-in-out infinite alternate;
}

@keyframes activeGlow {
  0% {
    opacity: 0.1;
  }
  100% {
    opacity: 0.2;
  }
}

/* Hover effects for inactive tabs */
.nav-tab-inactive:hover .icon-container {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
}

.nav-tab-inactive:hover .tab-label {
  color: rgb(59 130 246 / 0.8);
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .nav-background {
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.3);
  }

  .nav-tab-inactive:hover .tab-label {
    color: rgb(96 165 250 / 0.8);
  }
}

/* Performance optimizations for mobile */
@media (max-width: 768px) {
  .nav-tab {
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
  }
}

/* Safe area for devices with notches */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .bottom-nav-container {
    padding-bottom: env(safe-area-inset-bottom);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .nav-tab,
  .icon-container,
  .tab-label,
  .tab-indicator,
  .ripple-effect {
    transition: none;
    animation: none;
  }

  .badge {
    animation: none;
  }

  .notification-dot {
    animation: none;
  }
}
</style>
