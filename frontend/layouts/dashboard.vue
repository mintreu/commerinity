<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-blue-950 dark:to-purple-950">

    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="orb absolute -top-20 -left-20 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 rounded-full blur-3xl opacity-60"></div>
      <div ref="orb2" class="orb absolute -bottom-20 -right-20 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl opacity-70"></div>
      <div class="absolute inset-0 opacity-[0.02] dark:opacity-[0.05]" style="background-image:radial-gradient(circle,#3b82f6 1px,transparent 1px);background-size:50px 50px"></div>
    </div>

    <!-- Dashboard Container -->
    <div class="relative z-10 flex min-h-screen">

      <!-- Desktop Sidebar -->
      <aside
          class="hidden md:block fixed left-0 top-0 h-full z-30 transition-all duration-300"
          :class="collapsed ? 'w-20' : 'w-64'"
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
          class="flex-1 flex flex-col min-h-screen transition-all duration-300"
          :class="collapsed ? 'md:ml-20' : 'md:ml-64'"
      >

        <!-- Top Bar -->
        <header class="sticky top-0 z-40 w-full">
          <DashboardTopbar
              :collapsed="collapsed"
              :mobileOpen="mobileOpen"
              @toggle-sidebar="toggleSidebar"
              @toggle-mobile-sidebar="toggleMobileSidebar"
          />
        </header>

        <!-- Onboarding Banner -->
        <div v-if="showOnboarding">
          <OnboardingBanner @close="showOnboarding = false" />
        </div>

        <!-- Main Content - ✅ FIXED: No Transition wrapper -->
        <main class="flex-1 overflow-y-auto custom-scrollbar">
          <div class="p-4 md:p-8">
            <slot />
          </div>
        </main>

        <!-- Footer -->
        <footer class="mt-auto p-6 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm border-t border-gray-200/50 dark:border-gray-800/50">
          <div class="max-w-full mx-auto">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                &copy; {{ currentYear }} {{ websiteName }}. All rights reserved.
              </p>
              <div class="flex items-center gap-6">
                <NuxtLink to="/privacy" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Privacy
                </NuxtLink>
                <NuxtLink to="/terms" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Terms
                </NuxtLink>
                <NuxtLink to="/dashboard/helpdesk" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                  Support
                </NuxtLink>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <transition name="fade">
      <div
          v-if="mobileOpen"
          @click="closeMobileSidebar"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] md:hidden"
      />
    </transition>

    <!-- Mobile Sidebar -->
    <transition name="slide">
      <aside
          v-if="mobileOpen"
          class="fixed top-0 left-0 h-full w-80 max-w-[85vw] z-[70] md:hidden"
      >
        <DashboardSidebar
            :collapsed="false"
            :isMobile="true"
            :mobileOpen="mobileOpen"
            @close-mobile-sidebar="closeMobileSidebar"
        />
      </aside>
    </transition>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden">
      <BottomNavBar />
    </nav>

    <!-- Loading Overlay -->
    <transition name="fade">
      <div v-if="isLoading" class="fixed inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-[100] flex items-center justify-center">
        <div class="flex flex-col items-center gap-4">
          <div class="w-12 h-12 border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
          <p class="text-gray-600 dark:text-gray-400 font-semibold">Loading...</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import DashboardSidebar from '~/components/ui/Navbar/DashboardSidebar.vue'
import DashboardTopbar from '~/components/ui/Navbar/DashboardTopbar.vue'
import BottomNavBar from '~/components/ui/BottomNavBar.vue'
import OnboardingBanner from '~/components/onboarding/OnboardingBanner.vue'

// ✅ Optimized GSAP
let gsap: any = null
let gsapContext: any = null

// Composables
const route = useRoute()
const config = useRuntimeConfig()
const websiteName = config.public.websiteName

// State
const collapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(false)
const isLoading = ref(false)
const showOnboarding = ref(false) // ✅ Changed to false by default

// Refs for GSAP
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

let resizeTimeout: NodeJS.Timeout | null = null

// Computed
const currentYear = computed(() => new Date().getFullYear())

// ✅ Debounced Responsive Handler
function handleResize() {
  if (resizeTimeout) clearTimeout(resizeTimeout)

  resizeTimeout = setTimeout(() => {
    if (!process.client) return

    const newIsMobile = window.innerWidth < 768
    if (isMobile.value !== newIsMobile) {
      isMobile.value = newIsMobile
      if (!newIsMobile && mobileOpen.value) {
        mobileOpen.value = false
      }
    }
  }, 150)
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
      localStorage.setItem('dashboard-sidebar-collapsed', String(collapsed.value))
    } catch (e) {
      console.warn('Could not save sidebar state')
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
    } catch (e) {
      console.warn('Could not load sidebar state')
    }
  }
}

// ✅ OPTIMIZED GSAP
async function initGSAP() {
  if (!process.client || gsap) return

  try {
    const gsapModule = await import('gsap')
    gsap = gsapModule.default

    gsapContext = gsap.context(() => {
      if (orb1.value) {
        gsap.to(orb1.value, {
          x: 80,
          y: 40,
          rotation: 180,
          duration: 30,
          repeat: -1,
          yoyo: true,
          ease: 'sine.inOut',
          force3D: true
        })
      }

      if (orb2.value) {
        gsap.to(orb2.value, {
          x: -60,
          y: -50,
          rotation: -120,
          duration: 35,
          repeat: -1,
          yoyo: true,
          ease: 'sine.inOut',
          force3D: true
        })
      }
    })
  } catch (error) {
    console.warn('GSAP failed to load')
  }
}

// ✅ Route Change Handler - Fixed
function handleRouteChange() {
  if (mobileOpen.value) {
    mobileOpen.value = false
  }
}

// Watchers
watch(() => route.path, handleRouteChange)

// Keyboard Shortcuts
function handleKeydown(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
    e.preventDefault()
    toggleSidebar()
  }

  if (e.key === 'Escape' && mobileOpen.value) {
    e.preventDefault()
    closeMobileSidebar()
  }
}

// Lifecycle
onMounted(() => {
  if (!process.client) return

  isMobile.value = window.innerWidth < 768
  loadCollapsedState()

  window.addEventListener('resize', handleResize, { passive: true })
  document.addEventListener('keydown', handleKeydown)

  requestAnimationFrame(() => {
    initGSAP()
  })
})

onUnmounted(() => {
  if (!process.client) return

  window.removeEventListener('resize', handleResize)
  document.removeEventListener('keydown', handleKeydown)

  if (resizeTimeout) clearTimeout(resizeTimeout)
  if (gsapContext) gsapContext.kill()
})

// SEO
useHead({
  bodyAttrs: {
    class: 'dashboard-page'
  }
})
</script>

<style scoped>
/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}

/* Custom Scrollbar */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(107, 114, 128, 0.5);
}

/* Orbs */
.orb {
  transform: translate3d(0, 0, 0);
  backface-visibility: hidden;
}

/* Responsive */
@media (max-width: 767px) {
  .custom-scrollbar {
    min-height: calc(100vh - 140px);
  }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .orb {
    animation: none !important;
  }
  * {
    transition-duration: 0.01ms !important;
  }
}
</style>
