<template>
  <header
      class="dashboard-topbar h-16 w-full flex items-center justify-between relative transition-all duration-300"
      :class="topbarClasses"
      ref="topbarRef"
  >
    <!-- Enhanced Background -->
    <div class="topbar-background absolute inset-0 backdrop-blur-xl">
      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/98 to-white/95 dark:from-gray-900/95 dark:via-gray-900/98 dark:to-gray-900/95"></div>

      <!-- Glow Effect -->
      <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-pink-500/5 dark:from-blue-400/10 dark:via-purple-400/10 dark:to-pink-400/10"></div>

      <!-- Border -->
      <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-200/50 to-transparent dark:via-gray-700/50"></div>
    </div>

    <!-- Left Section - Logo & Toggle (Mobile) / Toggle Only (Desktop) -->
    <div class="left-section relative z-10 flex items-center gap-4 px-4">

      <!-- Mobile Menu Toggle -->
      <button
          v-if="isMobile"
          @click="toggleMobileSidebar"
          class="mobile-menu-toggle w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 group bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white"
          aria-label="Open Menu"
          type="button"
      >
        <Icon name="mdi:menu" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
      </button>

      <!-- Desktop Sidebar Toggle (Only show on desktop) -->
      <button
          v-else
          @click="toggleSidebar"
          class="sidebar-toggle relative w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 group"
          :class="toggleButtonClasses"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          type="button"
          ref="toggleButtonRef"
      >
        <!-- Button Background -->
        <div class="absolute inset-0 bg-gray-100 dark:bg-gray-800 rounded-xl transition-all duration-300 group-hover:bg-gradient-to-r group-hover:from-blue-500 group-hover:to-purple-500"></div>

        <!-- Shimmer Effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transform -skew-x-12 transition-all duration-500 group-hover:translate-x-full rounded-xl"></div>

        <!-- Icon -->
        <Icon
            :name="collapsed ? 'mdi:chevron-right' : 'mdi:chevron-left'"
            class="relative z-10 w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-white transition-all duration-300 group-hover:scale-110"
            aria-hidden="true"
        />
      </button>

      <!-- Mobile Logo (Only show on mobile) -->
      <NuxtLink
          v-if="isMobile"
          to="/"
          class="mobile-logo flex items-center gap-3 select-none group transition-all duration-300 hover:scale-105"
          aria-label="Homepage"
      >
        <div class="logo-wrapper relative">
          <div class="logo-glow absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
          <div class="logo-bg relative w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg transition-all duration-300 group-hover:shadow-xl">
            <img
                src="/logo.png"
                loading="lazy"
                class="object-contain w-6 h-6 filter brightness-0 invert transition-transform duration-300 group-hover:scale-110"
                alt="Logo"
            />
          </div>
        </div>
        <div class="brand-text">
          <span class="brand-name text-xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent tracking-tight">
            {{ companyName }}
          </span>
        </div>
      </NuxtLink>
    </div>

    <!-- Center Section - Breadcrumbs (Desktop Only) -->
    <div class="center-section relative z-10 flex-1 mx-6 hidden md:block" v-if="showBreadcrumbs">
      <div class="breadcrumbs-container p-2 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm border border-gray-200/30 dark:border-gray-700/30">
        <nav class="flex items-center space-x-2 text-sm">
          <NuxtLink
              to="/dashboard"
              class="breadcrumb-link text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200"
          >
            <Icon name="mdi:view-dashboard" class="w-4 h-4" />
            <span class="ml-1">Dashboard</span>
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-gray-400" />
          <span class="text-gray-900 dark:text-white font-medium">{{ currentPageTitle }}</span>
        </nav>
      </div>
    </div>

    <!-- Right Section - Actions -->
    <div class="right-section relative z-10 flex items-center gap-3 px-4">

      <!-- Search Button -->
      <button
          @click="toggleSearch"
          class="action-btn w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg group"
          aria-label="Search"
          type="button"
      >
        <Icon name="mdi:magnify" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transform -skew-x-12 transition-all duration-500 group-hover:translate-x-full rounded-xl"></div>
      </button>

      <!-- Notifications -->
      <button
          @click="toggleNotifications"
          class="action-btn relative w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-orange-500 hover:to-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg group"
          aria-label="Notifications"
          type="button"
      >
        <Icon name="mdi:bell-outline" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />

        <!-- Notification Badge -->
        <div
            v-if="notificationCount > 0"
            class="notification-badge absolute -top-1 -right-1 min-w-[1.25rem] h-5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900 animate-pulse"
        >
          {{ notificationCount > 99 ? '99+' : notificationCount }}
        </div>

        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transform -skew-x-12 transition-all duration-500 group-hover:translate-x-full rounded-xl"></div>
      </button>

      <!-- Settings (Desktop Only) -->
      <button
          v-if="!isMobile"
          @click="toggleSettings"
          class="action-btn w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-purple-500 hover:to-pink-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg group"
          aria-label="Settings"
          type="button"
      >
        <Icon name="mdi:cog" class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90 group-hover:scale-110" />
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transform -skew-x-12 transition-all duration-500 group-hover:translate-x-full rounded-xl"></div>
      </button>

      <!-- Divider (Desktop Only) -->
      <div v-if="!isMobile" class="w-px h-8 bg-gradient-to-b from-transparent via-gray-300 to-transparent dark:via-gray-600"></div>

      <!-- Dark Mode Toggle -->
      <DarkModeToggle />

      <!-- User Dropdown -->
      <UserDropdown />
    </div>

    <!-- Search Modal -->
    <SearchModal v-if="searchOpen" @close="toggleSearch" />

    <!-- Notification Panel -->
    <NotificationPanel v-if="notificationOpen" @close="toggleNotifications" />
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import DarkModeToggle from "~/components/ui/DarkModeToggle.vue"
import UserDropdown from "~/components/ui/UserDropdown.vue"
import SearchModal from "~/components/ui/SearchModal.vue"

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props & Emits
const props = defineProps<{
  collapsed: boolean
  mobileOpen?: boolean
}>()

const emit = defineEmits(['toggle-sidebar', 'toggle-mobile-sidebar'])

// Composables
const config = useRuntimeConfig()
const route = useRoute()
const companyName = config.public.companyName

// State
const searchOpen = ref(false)
const notificationOpen = ref(false)
const settingsOpen = ref(false)
const notificationCount = ref(3) // Example notification count
const showBreadcrumbs = ref(true)
const isMobile = ref(false)

// Refs
const topbarRef = ref<HTMLElement>()
const toggleButtonRef = ref<HTMLElement>()

let gsapContext: any = null

// Computed
const topbarClasses = computed(() => ({
  'border-b border-gray-200/50 dark:border-gray-800/50': true,
  'bg-white/95 dark:bg-gray-900/95': true,
  'shadow-sm backdrop-blur-xl': true,
}))

const toggleButtonClasses = computed(() => ({
  'hover:shadow-lg': true,
  'transform transition-all duration-300': true,
  'hover:scale-110': true,
}))

const currentPageTitle = computed(() => {
  // Extract page title from route
  const pathSegments = route.path.split('/').filter(Boolean)
  if (pathSegments.length > 1) {
    return pathSegments[pathSegments.length - 1].charAt(0).toUpperCase() +
        pathSegments[pathSegments.length - 1].slice(1)
  }
  return 'Overview'
})

// Methods
function toggleSidebar() {
  emit('toggle-sidebar')

  // Add haptic feedback
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}

function toggleMobileSidebar() {
  emit('toggle-mobile-sidebar')

  // Add haptic feedback
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}

function toggleSearch() {
  searchOpen.value = !searchOpen.value
}

function toggleNotifications() {
  notificationOpen.value = !notificationOpen.value
}

function toggleSettings() {
  settingsOpen.value = !settingsOpen.value
  // You can emit an event or handle settings here
  console.log('Settings toggled')
}

// Responsive handler
function handleResize() {
  if (process.client) {
    isMobile.value = window.innerWidth < 768
  }
}

// Animations
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Topbar entrance animation
    if (topbarRef.value) {
      gsap.fromTo(topbarRef.value,
          { y: -20, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.6,
            ease: 'back.out(1.7)'
          }
      )
    }

    // Button hover animations
    const actionButtons = topbarRef.value?.querySelectorAll('.action-btn')
    if (actionButtons) {
      actionButtons.forEach((button, index) => {
        gsap.fromTo(button,
            { scale: 0, opacity: 0 },
            {
              scale: 1,
              opacity: 1,
              duration: 0.4,
              delay: 0.1 * index,
              ease: 'back.out(1.7)'
            }
        )
      })
    }
  })
}

// Lifecycle
onMounted(() => {
  if (process.client) {
    isMobile.value = window.innerWidth < 768
    window.addEventListener('resize', handleResize, { passive: true })
  }

  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (process.client) {
    window.removeEventListener('resize', handleResize)
  }

  if (gsapContext) {
    gsapContext.kill()
  }
})
</script>

<style scoped>
/* Dashboard Topbar */
.dashboard-topbar {
  position: relative;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Topbar Background */
.topbar-background {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Logo Enhancements */
.logo-wrapper:hover .logo-bg {
  transform: scale(1.05);
}

.brand-name {
  background: linear-gradient(135deg, #1f2937 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Sidebar Toggle */
.sidebar-toggle,
.mobile-menu-toggle {
  position: relative;
  overflow: hidden;
}

.sidebar-toggle::before,
.mobile-menu-toggle::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
  border-radius: 0.75rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar-toggle:hover::before,
.mobile-menu-toggle:hover::before {
  opacity: 1;
}

/* Action Buttons */
.action-btn {
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.action-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
  border-radius: 0.75rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.action-btn:hover::before {
  opacity: 1;
}

/* Notification Badge */
.notification-badge {
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

/* Breadcrumbs */
.breadcrumbs-container {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.breadcrumb-link {
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.breadcrumb-link:hover {
  transform: translateX(2px);
}

/* Enhanced Effects */
.shimmer {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transform: translateX(-100%);
  transition: transform 0.6s;
}

.group:hover .shimmer {
  transform: translateX(100%);
}

/* Responsive Design */
@media (max-width: 768px) {
  .right-section {
    gap: 0.5rem;
  }

  .action-btn {
    width: 2.25rem;
    height: 2.25rem;
  }
}

/* Dark Mode Enhancements */
@media (prefers-color-scheme: dark) {
  .dashboard-topbar {
    background: rgba(17, 24, 39, 0.95);
  }
}

/* Focus States */
.action-btn:focus-visible,
.sidebar-toggle:focus-visible,
.mobile-menu-toggle:focus-visible,
.breadcrumb-link:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Animation Classes */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
