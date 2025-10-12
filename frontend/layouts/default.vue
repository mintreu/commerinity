<template>
  <div class="layout-container min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-blue-950 dark:to-purple-950 transition-colors duration-500">

    <!-- ✅ Background Effects - Load AFTER initial render -->
    <div v-if="mounted && showBackgroundEffects" class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-20 -left-20 w-80 h-80 bg-gradient-to-r from-blue-400/20 to-purple-400/20 rounded-full blur-3xl opacity-60 will-change-transform"/>
      <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-gradient-to-r from-purple-400/20 to-pink-400/20 rounded-full blur-3xl opacity-70 will-change-transform"/>
    </div>

    <!-- Main Layout Container -->
    <div class="layout-main flex min-h-screen flex-col">

      <!-- ✅ Navbar - Defer 100ms for faster FCP -->
      <ClientOnly>
        <component :is="navbarComponent" v-if="mounted && showNavbar" />
      </ClientOnly>

      <!-- Main Content -->
      <div class="content-area flex flex-1">
        <main
            id="main-content"
            aria-label="Main content"
            :class="['main-content flex-1 relative z-10', showNavbar ? 'pt-16 md:pt-20' : '']"
        >
          <slot />
        </main>
      </div>

      <!-- ✅ Footer Component - Load AFTER scroll or 3 seconds -->
      <ClientOnly>
        <component :is="footerComponent" v-if="showFooter && footerVisible" />
      </ClientOnly>
    </div>

    <!-- ✅ Mobile Bottom Nav - Defer until needed -->
    <ClientOnly>
      <component :is="bottomNavComponent" v-if="mounted && isMobile && showBottomNav" />
    </ClientOnly>

    <!-- ✅ Scroll to Top - Only show when needed -->
    <Transition name="fade">
      <button
          v-show="showScrollTop"
          @click="scrollToTop"
          class="fixed z-30 w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-xl transition-all duration-300 hover:scale-110 active:scale-95"
          style="bottom: 5rem; right: 1.5rem;"
          aria-label="Scroll to top"
      >
        <Icon name="mdi:arrow-up" class="w-5 h-5 mx-auto" />
      </button>
    </Transition>

    <!-- ✅ Newsletter - Load AFTER 5 seconds (ALWAYS) -->
    <ClientOnly>
      <component :is="newsletterComponent" v-if="mounted && showNewsletter" />
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, defineAsyncComponent } from 'vue'

// ✅ CRITICAL: Lazy load components
const navbarComponent = defineAsyncComponent(() =>
    import('~/components/ui/Navbar/DefaultNavbar.vue')
)
const bottomNavComponent = defineAsyncComponent(() =>
    import('~/components/ui/BottomNavBar.vue')
)
const footerComponent = defineAsyncComponent(() =>
    import('~/components/ui/Footer/DefaultFooter.vue')
)
const newsletterComponent = defineAsyncComponent(() =>
    import('~/components/ui/NewsletterSplash.vue')
)

// ✅ State Management
const mounted = ref(false)
const isMobile = ref(false)
const footerVisible = ref(false)
const showScrollTop = ref(false)

// Static visibility controls
const showNavbar = ref(true)
const showFooter = ref(true)
const showNewsletter = ref(true) // ✅ Always true - component handles its own visibility
const showBottomNav = ref(true)
const showBackgroundEffects = ref(true)

// ✅ Optimized scroll handler
const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

let ticking = false
const handleScroll = () => {
  if (!ticking) {
    requestAnimationFrame(() => {
      const scrollY = window.scrollY
      showScrollTop.value = scrollY > 400

      // Show footer when user scrolls 50% of page
      if (!footerVisible.value && scrollY > window.innerHeight * 0.5) {
        footerVisible.value = true
      }

      ticking = false
    })
    ticking = true
  }
}

// ✅ Handle resize
const handleResize = () => {
  isMobile.value = window.innerWidth <= 768
}

// ✅ Lifecycle - Optimized mounting
onMounted(() => {
  if (process.client) {
    // Defer component loading to next frame
    requestAnimationFrame(() => {
      mounted.value = true
      isMobile.value = window.innerWidth <= 768
    })

    // ✅ Show footer after 3 seconds if not scrolled (OUTSIDE requestAnimationFrame)
    setTimeout(() => {
      if (!footerVisible.value) {
        footerVisible.value = true
      }
    }, 3000)

    // Add scroll listener with passive for performance
    window.addEventListener('scroll', handleScroll, { passive: true })
    window.addEventListener('resize', handleResize, { passive: true })
  }
})

onUnmounted(() => {
  if (process.client) {
    window.removeEventListener('scroll', handleScroll)
    window.removeEventListener('resize', handleResize)
  }
})
</script>

<style scoped>
/* ✅ Smooth transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* ✅ GPU acceleration for smooth scrolling */
.layout-container {
  will-change: scroll-position;
}
</style>
