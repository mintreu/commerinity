<template>
  <div class="dashboard-layout min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-blue-950 dark:to-purple-950 transition-all duration-500">

    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <!-- Floating Orbs -->
      <div ref="dashboardOrb1" class="dashboard-orb-1 absolute -top-20 -left-20 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 rounded-full blur-3xl opacity-60 will-change-transform"></div>
      <div ref="dashboardOrb2" class="dashboard-orb-2 absolute -bottom-20 -right-20 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl opacity-70 will-change-transform"></div>

      <!-- Grid Pattern -->
      <div class="dashboard-grid absolute inset-0 opacity-[0.02] dark:opacity-[0.05]"
           style="background-image: radial-gradient(circle, #3b82f6 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <!-- Dashboard Container -->
    <div class="dashboard-container relative z-10 flex min-h-screen">

      <!-- Desktop Sidebar -->
      <aside
          class="dashboard-sidebar hidden md:block fixed left-0 top-0 h-full z-30 transition-all duration-300 ease-in-out"
          :class="collapsed ? 'w-20' : 'w-64'"
          ref="desktopSidebar"
      >
        <DashboardSidebar
            :collapsed="collapsed"
            :isMobile="false"
            :mobileOpen="false"
            @toggle-sidebar="toggleSidebar"
        />
      </aside>

      <!-- Main Content Area -->
      <div
          class="dashboard-main flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out"
          :class="collapsed ? 'md:ml-20' : 'md:ml-64'"
      >

        <!-- Full-Width Top Bar -->
        <header class="dashboard-topbar sticky top-0 z-40 w-full" ref="dashboardTopbar">
          <DashboardTopbar
              :collapsed="collapsed"
              :mobileOpen="mobileOpen"
              @toggle-sidebar="toggleSidebar"
              @toggle-mobile-sidebar="toggleMobileSidebar"
          />
        </header>

        <!-- Onboarding Banner -->
        <div v-if="showOnboarding" class="onboarding-section" ref="onboardingSection">
          <OnboardingBanner @close="showOnboarding = false" />
        </div>

        <!-- Main Content -->
        <main class="dashboard-content flex-1 overflow-y-auto" ref="dashboardContent">
          <div class="content-wrapper p-4 md:p-8">
            <div class="content-container max-w-full mx-auto">
              <!-- Content slot with fade transition -->
              <transition name="content-fade" mode="out-in">
                <slot />
              </transition>
            </div>
          </div>
        </main>

        <!-- Footer -->
        <footer class="dashboard-footer mt-auto p-6 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm border-t border-gray-200/50 dark:border-gray-800/50">
          <div class="footer-content max-w-full mx-auto">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
              <div class="footer-info text-sm text-gray-600 dark:text-gray-400">
                <p>&copy; {{ currentYear }} {{ websiteName }}. All rights reserved.</p>
              </div>
              <div class="footer-links flex items-center gap-6">
                <NuxtLink to="/privacy" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Privacy
                </NuxtLink>
                <NuxtLink to="/terms" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Terms
                </NuxtLink>
                <NuxtLink to="/support" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Support
                </NuxtLink>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <transition name="mobile-overlay">
      <div
          v-if="mobileOpen"
          @click="closeMobileSidebar"
          class="mobile-sidebar-overlay fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] md:hidden"
      ></div>
    </transition>

    <!-- Mobile Sidebar -->
    <transition name="mobile-sidebar">
      <aside
          v-if="mobileOpen"
          class="mobile-sidebar-drawer fixed top-0 left-0 h-full w-80 max-w-[85vw] z-[70] md:hidden"
          ref="mobileSidebar"
      >
        <DashboardSidebar
            :collapsed="false"
            :isMobile="true"
            :mobileOpen="mobileOpen"
            @close-mobile-sidebar="closeMobileSidebar"
        />
      </aside>
    </transition>

    <!-- Enhanced Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav md:hidden">
      <BottomNavBar />
    </nav>

    <!-- Loading Overlay -->
    <transition name="loading-overlay">
      <div v-if="isLoading" class="loading-overlay fixed inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-[100] flex items-center justify-center">
        <div class="loading-content flex flex-col items-center gap-4">
          <div class="loading-spinner w-12 h-12 border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
          <p class="text-gray-600 dark:text-gray-400 font-semibold">Loading...</p>
        </div>
      </div>
    </transition>

    <!-- Performance Monitoring (Development Only) -->
    <div v-if="isDevelopment" class="dev-tools fixed bottom-4 left-4 z-[90] opacity-50 hover:opacity-100 transition-opacity">
      <div class="dev-info bg-black/80 text-white text-xs p-2 rounded">
        <div>Collapsed: {{ collapsed }}</div>
        <div>Mobile: {{ isMobile }}</div>
        <div>Mobile Open: {{ mobileOpen }}</div>
        <div>Screen: {{ screenSize }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import DashboardSidebar from '~/components/ui/Navbar/DashboardSidebar.vue'
import DashboardTopbar from '~/components/ui/Navbar/DashboardTopbar.vue'
import BottomNavBar from '~/components/ui/BottomNavBar.vue'
import OnboardingBanner from '~/components/onboarding/OnboardingBanner.vue'

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Composables
const route = useRoute()
const config = useRuntimeConfig()
const websiteName = config.public.websiteName

// State Management
const collapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(false)
const isLoading = ref(false)
const showOnboarding = ref(true)
const screenSize = ref({ width: 0, height: 0 })

// Refs for animations
const dashboardOrb1 = ref<HTMLElement>()
const dashboardOrb2 = ref<HTMLElement>()
const desktopSidebar = ref<HTMLElement>()
const dashboardTopbar = ref<HTMLElement>()
const onboardingSection = ref<HTMLElement>()
const dashboardContent = ref<HTMLElement>()
const mobileSidebar = ref<HTMLElement>()

let gsapContext: any = null
let resizeTimeout: number | null = null

// Computed Properties
const currentYear = computed(() => new Date().getFullYear())

const isDevelopment = computed(() => {
  return process.client && process.env.NODE_ENV === 'development'
})

// Responsive Handler
function handleResize() {
  if (resizeTimeout) {
    clearTimeout(resizeTimeout)
  }

  resizeTimeout = setTimeout(() => {
    if (process.client) {
      const newIsMobile = window.innerWidth < 768
      screenSize.value = {
        width: window.innerWidth,
        height: window.innerHeight
      }

      if (isMobile.value !== newIsMobile) {
        isMobile.value = newIsMobile

        // Close mobile sidebar when switching to desktop
        if (!newIsMobile && mobileOpen.value) {
          mobileOpen.value = false
        }
      }
    }
  }, 100)
}

// Sidebar Controls
function toggleSidebar() {
  if (isMobile.value) {
    toggleMobileSidebar()
  } else {
    collapsed.value = !collapsed.value
    saveCollapsedState()
  }
}

function toggleMobileSidebar() {
  mobileOpen.value = !mobileOpen.value

  // Add haptic feedback
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}

function closeMobileSidebar() {
  mobileOpen.value = false
}

// Persistence
function saveCollapsedState() {
  if (process.client) {
    try {
      localStorage.setItem('dashboard-sidebar-collapsed', collapsed.value.toString())
    } catch (error) {
      console.warn('Could not save sidebar state:', error)
    }
  }
}

function loadCollapsedState() {
  if (process.client) {
    try {
      const saved = localStorage.getItem('dashboard-sidebar-collapsed')
      if (saved !== null) {
        collapsed.value = saved === 'true'
      }
    } catch (error) {
      console.warn('Could not load sidebar state:', error)
    }
  }
}

// Animations
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (dashboardOrb1.value && dashboardOrb2.value) {
      gsap.to(dashboardOrb1.value, {
        x: 100,
        y: 50,
        rotation: 180,
        duration: 20,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })

      gsap.to(dashboardOrb2.value, {
        x: -80,
        y: -60,
        rotation: -120,
        duration: 25,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })
    }

    // Entrance animations
    const elements = [
      desktopSidebar.value,
      dashboardTopbar.value,
      dashboardContent.value
    ].filter(Boolean)

    if (elements.length > 0) {
      gsap.fromTo(elements,
          { opacity: 0, y: 20 },
          {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.1,
            ease: 'power2.out'
          }
      )
    }
  })
}

// Route Change Handler
function handleRouteChange() {
  // Close mobile sidebar on route change
  if (mobileOpen.value) {
    mobileOpen.value = false
  }

  // Add loading state for smooth transitions
  isLoading.value = true

  nextTick(() => {
    setTimeout(() => {
      isLoading.value = false
    }, 300)
  })
}

// Watchers
watch(() => route.path, handleRouteChange)

// Keyboard Shortcuts
function handleKeydown(event: KeyboardEvent) {
  // Toggle sidebar with Ctrl/Cmd + B
  if ((event.ctrlKey || event.metaKey) && event.key === 'b') {
    event.preventDefault()
    toggleSidebar()
  }

  // Close mobile sidebar with Escape
  if (event.key === 'Escape' && mobileOpen.value) {
    event.preventDefault()
    closeMobileSidebar()
  }
}

// Initialize client-side functionality
function initializeClientSide() {
  if (process.client) {
    // Set initial mobile state
    isMobile.value = window.innerWidth < 768
    screenSize.value = {
      width: window.innerWidth,
      height: window.innerHeight
    }

    // Add event listeners
    window.addEventListener('resize', handleResize, { passive: true })
    document.addEventListener('keydown', handleKeydown)

    // Load saved preferences
    loadCollapsedState()

    // Initialize animations
    setTimeout(() => {
      initializeAnimations()
    }, 100)
  }
}

// Cleanup function
function cleanup() {
  if (process.client) {
    window.removeEventListener('resize', handleResize)
    document.removeEventListener('keydown', handleKeydown)

    if (resizeTimeout) {
      clearTimeout(resizeTimeout)
    }
  }

  if (gsapContext) {
    gsapContext.kill()
  }
}

// Lifecycle
onMounted(() => {
  initializeClientSide()
})

onUnmounted(() => {
  cleanup()
})

// SEO and Meta
useHead({
  bodyAttrs: {
    class: 'dashboard-page'
  }
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Dashboard Layout */
.dashboard-layout {
  min-height: 100vh;
  min-height: 100dvh; /* For better mobile support */
}

.dashboard-container {
  min-height: inherit;
}

/* Background Effects */
.dashboard-orb-1,
.dashboard-orb-2 {
  will-change: transform;
}

/* Sidebar */
.dashboard-sidebar {
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Main Content */
.dashboard-main {
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 0; /* Prevent flex item from overflowing */
}

.dashboard-topbar {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.dashboard-content {
  min-height: calc(100vh - 80px); /* Adjust based on topbar height */
}

.content-wrapper {
  min-height: 100%;
}

/* Footer */
.dashboard-footer {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Mobile Sidebar */
.mobile-sidebar-drawer {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Transitions */
.mobile-overlay-enter-active,
.mobile-overlay-leave-active {
  transition: opacity 0.3s ease;
}

.mobile-overlay-enter-from,
.mobile-overlay-leave-to {
  opacity: 0;
}

.mobile-sidebar-enter-active,
.mobile-sidebar-leave-active {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-sidebar-enter-from,
.mobile-sidebar-leave-to {
  transform: translateX(-100%);
}

.content-fade-enter-active,
.content-fade-leave-active {
  transition: all 0.3s ease;
}

.content-fade-enter-from,
.content-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.loading-overlay-enter-active,
.loading-overlay-leave-active {
  transition: all 0.3s ease;
}

.loading-overlay-enter-from,
.loading-overlay-leave-to {
  opacity: 0;
}

/* Development Tools */
.dev-tools {
  font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', 'Fira Mono', 'Roboto Mono', monospace;
}

.dev-info {
  min-width: 120px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Responsive Design */
@media (max-width: 767px) {
  .dashboard-main {
    margin-left: 0 !important;
  }

  .dashboard-content {
    min-height: calc(100vh - 140px); /* Account for top bar and bottom nav */
  }
}

@media (min-width: 768px) {
  .mobile-bottom-nav {
    display: none;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .dashboard-footer {
    background: rgba(17, 24, 39, 0.5);
  }
}

/* Smooth scrolling */
.dashboard-content {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175 / 0.3) transparent;
}

.dashboard-content::-webkit-scrollbar {
  width: 8px;
}

.dashboard-content::-webkit-scrollbar-track {
  background: transparent;
}

.dashboard-content::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175 / 0.3);
  border-radius: 4px;
}

.dashboard-content::-webkit-scrollbar-thumb:hover {
  background-color: rgb(107 114 128 / 0.5);
}

/* Loading states */
.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Focus management */
.dashboard-layout:focus-within .dashboard-content {
  scroll-behavior: smooth;
}

/* Print styles */
@media print {
  .dashboard-sidebar,
  .dashboard-topbar,
  .mobile-bottom-nav,
  .dashboard-footer,
  .dev-tools {
    display: none !important;
  }

  .dashboard-main {
    margin: 0 !important;
  }

  .dashboard-content {
    overflow: visible !important;
  }
}

/* Performance improvements */
@media (prefers-reduced-motion: reduce) {
  .dashboard-orb-1,
  .dashboard-orb-2 {
    animation: none !important;
  }

  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
