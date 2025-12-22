<template>
  <header
      class="h-16 w-full flex items-center justify-between relative"
      ref="topbar"
  >
    <!-- ✅ Optimized Background -->
    <div class="absolute inset-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-800/50">
      <!-- Subtle gradient -->
      <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-pink-500/5 dark:from-blue-400/10 dark:via-purple-400/10 dark:to-pink-400/10"></div>
    </div>

    <!-- Left Section -->
    <div class="relative z-10 flex items-center gap-4 px-4">

      <!-- Mobile Menu Toggle -->
      <button
          v-if="isMobile"
          @click="toggleMobileSidebar"
          class="btn-action w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 group bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white"
          aria-label="Open Menu"
      >
        <Icon name="mdi:menu" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
      </button>

      <!-- Desktop Sidebar Toggle -->
      <button
          v-else
          @click="toggleSidebar"
          class="btn-action w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 group bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      >
        <Icon
            :name="collapsed ? 'mdi:chevron-right' : 'mdi:chevron-left'"
            class="w-5 h-5 transition-all duration-300 group-hover:scale-110 group-hover:text-white"
        />
      </button>

      <!-- Mobile Logo -->
      <NuxtLink
          v-if="isMobile"
          to="/"
          class="flex items-center gap-3 group"
          aria-label="Homepage"
      >
        <div class="relative">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
          <div class="relative w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
            <img
                src="/logo.png"
                loading="lazy"
                class="w-6 h-6 object-contain brightness-0 invert"
                alt="Logo"
            />
          </div>
        </div>
        <span class="text-xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
          {{ companyName }}
        </span>
      </NuxtLink>
    </div>

    <!-- Center Section - Breadcrumbs (Desktop) -->
    <div class="relative z-10 flex-1 mx-6 hidden md:block" v-if="showBreadcrumbs">
      <div class="p-2 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl backdrop-blur-sm border border-gray-200/30 dark:border-gray-700/30">
        <nav class="flex items-center space-x-2 text-sm">
          <NuxtLink
              to="/dashboard"
              class="flex items-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
          >
            <Icon name="mdi:view-dashboard" class="w-4 h-4 mr-1" />
            Dashboard
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-gray-400" />
          <span class="text-gray-900 dark:text-white font-medium">{{ currentPageTitle }}</span>
        </nav>
      </div>
    </div>

    <!-- Right Section - Actions -->
    <div class="relative z-10 flex items-center gap-3 px-4">

      <!-- Search Button -->
      <button
          @click="toggleSearch"
          class="btn-action w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 group"
          aria-label="Search"
          ref="searchBtn"
      >
        <Icon name="mdi:magnify" class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
      </button>

      <!-- Notifications -->
      <div ref="notificationBtn">
        <NotificationDropdown />
      </div>

      <!-- Settings (Desktop) -->
      <button
          v-if="!isMobile"
          @click="toggleSettings"
          class="btn-action w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-purple-500 hover:to-pink-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 group"
          aria-label="Settings"
          ref="settingsBtn"
      >
        <Icon name="mdi:cog" class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90" />
      </button>

      <!-- Divider (Desktop) -->
      <div v-if="!isMobile" class="w-px h-8 bg-gradient-to-b from-transparent via-gray-300 to-transparent dark:via-gray-600"></div>

      <!-- Dark Mode Toggle -->
      <div ref="darkModeBtn">
        <DarkModeToggle />
      </div>

      <!-- User Dropdown -->
      <div ref="userDropdownBtn">
        <UserDropdown />
      </div>
    </div>

    <!-- Search Modal -->
    <SearchModal v-if="searchOpen" @close="toggleSearch" />
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import DarkModeToggle from "~/components/ui/DarkModeToggle.vue"
import UserDropdown from "~/components/ui/UserDropdown.vue"
import SearchModal from "~/components/ui/SearchModal.vue"
import NotificationDropdown from "~/components/ui/NotificationDropdown.vue"

// ✅ Optimized GSAP - Load once, shared context
let gsap: any = null
let gsapContext: any = null

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
const settingsOpen = ref(false)
const showBreadcrumbs = ref(true)
const isMobile = ref(false)

// Refs for GSAP
const topbar = ref<HTMLElement>()
const searchBtn = ref<HTMLElement>()
const notificationBtn = ref<HTMLElement>()
const settingsBtn = ref<HTMLElement>()
const darkModeBtn = ref<HTMLElement>()
const userDropdownBtn = ref<HTMLElement>()

// Computed
const currentPageTitle = computed(() => {
  const segments = route.path.split('/').filter(Boolean)
  if (segments.length > 1) {
    return segments[segments.length - 1].charAt(0).toUpperCase() +
        segments[segments.length - 1].slice(1)
  }
  return 'Overview'
})

// Methods
function toggleSidebar() {
  emit('toggle-sidebar')
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}

function toggleMobileSidebar() {
  emit('toggle-mobile-sidebar')
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}

function toggleSearch() {
  searchOpen.value = !searchOpen.value
}

function toggleSettings() {
  settingsOpen.value = !settingsOpen.value
  console.log('Settings toggled')
}

// Responsive handler
function handleResize() {
  if (process.client) {
    isMobile.value = window.innerWidth < 768
  }
}

// ✅ OPTIMIZED GSAP Animations
async function initGSAP() {
  if (!process.client || gsap) return

  try {
    const gsapModule = await import('gsap')
    gsap = gsapModule.default

    gsapContext = gsap.context(() => {
      // ✅ Smooth topbar entrance
      if (topbar.value) {
        gsap.fromTo(topbar.value,
            { y: -20, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 0.5,
              ease: 'power2.out',
              force3D: true
            }
        )
      }

      // ✅ Stagger action buttons (optimized)
      const buttons = [
        searchBtn.value,
        notificationBtn.value,
        settingsBtn.value,
        darkModeBtn.value,
        userDropdownBtn.value
      ].filter(Boolean)

      if (buttons.length) {
        gsap.fromTo(buttons,
            { scale: 0.8, opacity: 0 },
            {
              scale: 1,
              opacity: 1,
              duration: 0.4,
              stagger: 0.08,
              ease: 'back.out(1.5)',
              force3D: true
            }
        )
      }
    })
  } catch (error) {
    console.warn('GSAP failed to load:', error)
  }
}

// Lifecycle
onMounted(() => {
  if (!process.client) return

  isMobile.value = window.innerWidth < 768
  window.addEventListener('resize', handleResize, { passive: true })

  // ✅ Initialize GSAP after mount (non-blocking)
  requestAnimationFrame(() => {
    initGSAP()
  })
})

onUnmounted(() => {
  if (!process.client) return

  window.removeEventListener('resize', handleResize)
  if (gsapContext) gsapContext.kill()
})
</script>

<style scoped>
/* ✅ Optimized Button Styles */
.btn-action {
  position: relative;
  overflow: hidden;
}

.btn-action::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
  transform: translateX(-100%);
  transition: transform 0.5s;
}

.btn-action:hover::after {
  transform: translateX(100%);
}

/* ✅ Responsive */
@media (max-width: 768px) {
  .btn-action {
    width: 2.25rem;
    height: 2.25rem;
  }
}

/* ✅ Focus States */
.btn-action:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
