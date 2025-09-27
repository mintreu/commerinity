<template>
  <div class="auth-layout min-h-screen w-full relative overflow-hidden">
    <!-- Global Background -->
    <div class="fixed inset-0 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-indigo-900 dark:to-purple-900"></div>

    <!-- Global Floating Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <!-- Main Orbs -->
      <div ref="mainOrb1" class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-blue-400/8 to-indigo-400/8 rounded-full blur-3xl opacity-70 animate-pulse"></div>
      <div ref="mainOrb2" class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-purple-400/8 to-pink-400/8 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="mainOrb3" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-gradient-to-br from-emerald-400/6 to-cyan-400/6 rounded-full blur-3xl opacity-50 animate-pulse"></div>

      <!-- Micro Orbs -->
      <div v-for="i in 6" :key="`micro-${i}`" class="absolute animate-float-slow" :style="getMicroOrbStyle(i)">
        <div class="w-3 h-3 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-sm"></div>
      </div>

      <!-- Brand Elements -->
      <div v-for="i in 8" :key="`brand-${i}`" class="absolute animate-float-gentle opacity-8" :style="getBrandIconStyle(i)">
        <Icon :name="getBrandIcon(i)" class="w-5 h-5 text-blue-500/20 dark:text-blue-400/15" />
      </div>
    </div>

    <!-- Header Navigation (Enhanced) -->
    <header class="relative z-50 w-full">
      <nav class="flex items-center justify-between p-6 lg:p-8">
        <!-- Brand Logo (Desktop & Mobile) -->
        <NuxtLink to="/" class="flex items-center gap-3 group">
          <!-- Logo Image -->
          <div class="relative w-12 h-12 rounded-2xl overflow-hidden shadow-lg group-hover:scale-105 transition-transform duration-300 bg-white">
            <img
                src="/logo.png"
                :alt="`${companyName} Logo`"
                class="w-full h-full object-contain p-1"
                @error="handleLogoError"
            />
            <!-- Fallback Icon if logo fails to load -->
            <div v-if="logoError" class="absolute inset-0 w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center">
              <Icon name="mdi:shopping" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="block">
            <h1 class="text-xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
              {{ companyName }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 -mt-1 hidden sm:block">Your Shopping Destination</p>
          </div>
        </NuxtLink>

        <!-- Navigation Links -->
        <div class="flex items-center gap-3">
          <!-- Home CTA Button (Prominent) -->
          <NuxtLink
              to="/"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl font-semibold text-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg group"
          >
            <Icon name="mdi:home" class="w-4 h-4 group-hover:scale-110 transition-transform" />
            <span class="hidden sm:inline">Back to Shop</span>
            <span class="sm:hidden">Home</span>
          </NuxtLink>

          <!-- Help Link -->
          <NuxtLink
              to="/help"
              class="hidden md:inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl hover:bg-white/60 dark:hover:bg-slate-800/60 transition-all duration-300 text-sm font-medium"
          >
            <Icon name="mdi:help-circle" class="w-4 h-4" />
            <span>Help</span>
          </NuxtLink>

          <!-- Theme Toggle -->
          <button
              @click="toggleTheme"
              class="flex items-center justify-center w-10 h-10 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl hover:bg-white/60 dark:hover:bg-slate-800/60 transition-all duration-300"
              aria-label="Toggle theme"
          >
            <Icon :name="isDark ? 'mdi:weather-sunny' : 'mdi:weather-night'" class="w-5 h-5" />
          </button>

          <!-- Language Selector (Optional) -->
          <div class="relative" v-if="showLanguageSelector">
            <button
                @click="toggleLanguageMenu"
                class="hidden lg:flex items-center gap-2 px-3 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-xl hover:bg-white/60 dark:hover:bg-slate-800/60 transition-all duration-300 text-sm font-medium"
                data-language-selector
            >
              <Icon name="mdi:translate" class="w-4 h-4" />
              <span>{{ currentLanguage }}</span>
              <Icon name="mdi:chevron-down" class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showLanguageMenu }" />
            </button>

            <!-- Language Dropdown -->
            <Transition name="dropdown">
              <div v-if="showLanguageMenu" class="absolute top-full right-0 mt-2 w-32 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-xl shadow-2xl py-2 z-50" data-language-selector>
                <button
                    v-for="lang in languages"
                    :key="lang.code"
                    @click="selectLanguage(lang)"
                    class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors"
                    :class="{ 'bg-blue-50 dark:bg-slate-700 text-blue-600 dark:text-blue-400': currentLanguage === lang.name }"
                >
                  {{ lang.name }}
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </nav>

      <!-- Secondary Home CTA Banner (Mobile Focused) -->
      <div class="lg:hidden px-6 pb-4">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 backdrop-blur-xl border border-blue-200/50 dark:border-blue-800/50 rounded-2xl p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <Icon name="mdi:storefront" class="w-4 h-4 text-white" />
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Continue Shopping</p>
                <p class="text-xs text-slate-600 dark:text-slate-400">Browse millions of products</p>
              </div>
            </div>
            <NuxtLink
                to="/"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-medium text-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg group"
            >
              <span>Shop Now</span>
              <Icon name="mdi:arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="relative z-10 flex-1">
      <slot />
    </main>

    <!-- Footer (Enhanced with Home Links) -->
    <footer class="relative z-10 mt-auto">
      <div class="border-t border-white/20 dark:border-slate-700/50 bg-white/30 dark:bg-slate-800/30 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-6 py-8">
          <!-- Home CTA Section (Above main footer) -->
          <div class="mb-8 pb-8 border-b border-white/10 dark:border-slate-700/50">
            <div class="text-center">
              <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Ready to Start Shopping?</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm mb-6">Discover amazing products with exclusive deals and fast delivery</p>

              <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <!-- Primary Home CTA -->
                <NuxtLink
                    to="/"
                    class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-bold text-lg shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
                >
                  <Icon name="mdi:shopping" class="w-6 h-6 group-hover:scale-110 transition-transform" />
                  <span>Browse Products</span>
                  <Icon name="mdi:arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </NuxtLink>

                <!-- Secondary CTA -->
                <NuxtLink
                    to="/categories"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white/80 dark:bg-slate-700/80 hover:bg-white dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold border-2 border-slate-200/60 dark:border-slate-600/60 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 hover:-translate-y-0.5 group"
                >
                  <Icon name="mdi:view-grid" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                  <span>View Categories</span>
                </NuxtLink>
              </div>

              <!-- Quick Access Links -->
              <div class="flex flex-wrap items-center justify-center gap-6 mt-6 text-sm">
                <NuxtLink to="/deals" class="flex items-center gap-2 text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 font-semibold transition-colors group">
                  <Icon name="mdi:fire" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                  <span>Hot Deals</span>
                </NuxtLink>
                <NuxtLink to="/new-arrivals" class="flex items-center gap-2 text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-semibold transition-colors group">
                  <Icon name="mdi:new-box" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                  <span>New Arrivals</span>
                </NuxtLink>
                <NuxtLink to="/bestsellers" class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-semibold transition-colors group">
                  <Icon name="mdi:star" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                  <span>Best Sellers</span>
                </NuxtLink>
              </div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Copyright -->
            <div class="text-center sm:text-left">
              <p class="text-sm text-slate-600 dark:text-slate-400">
                © {{ currentYear }} {{ companyName }}. All rights reserved.
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                Secure shopping platform powered by advanced technology
              </p>
            </div>

            <!-- Footer Links -->
            <div class="flex items-center gap-6 text-sm">
              <NuxtLink
                  to="/privacy"
                  class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                Privacy Policy
              </NuxtLink>
              <NuxtLink
                  to="/terms"
                  class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                Terms of Service
              </NuxtLink>
              <NuxtLink
                  to="/support"
                  class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
              >
                Support
              </NuxtLink>
            </div>
          </div>

          <!-- Trust Indicators -->
          <div class="mt-6 pt-6 border-t border-white/10 dark:border-slate-700/50">
            <div class="flex items-center justify-center gap-8 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <div class="flex items-center gap-2">
                <Icon name="mdi:shield-check" class="w-4 h-4 text-green-500" />
                <span>SSL Secured</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:bank" class="w-4 h-4 text-blue-500" />
                <span>Banking Partners</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:truck-fast" class="w-4 h-4 text-purple-500" />
                <span>Fast Delivery</span>
              </div>
              <div class="flex items-center gap-2">
                <Icon name="mdi:account-heart" class="w-4 h-4 text-pink-500" />
                <span>50L+ Customers</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRuntimeConfig } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Composables
const config = useRuntimeConfig()

// Configuration
const companyName = config.public.companyName || 'MintReu'
const showLanguageSelector = config.public.showLanguageSelector || false

// Theme Management (Manual implementation)
const isDark = ref(false)
const logoError = ref(false)

// Language Management
const currentLanguage = ref('English')
const showLanguageMenu = ref(false)
const languages = [
  { code: 'en', name: 'English' },
  { code: 'hi', name: 'हिंदी' },
  { code: 'bn', name: 'বাংলা' },
  { code: 'te', name: 'తెలুগు' },
  { code: 'mr', name: 'मराठী' },
]

// Refs for animations
const mainOrb1 = ref<HTMLElement>()
const mainOrb2 = ref<HTMLElement>()
const mainOrb3 = ref<HTMLElement>()

// Computed
const currentYear = computed(() => new Date().getFullYear())

// Methods
function toggleTheme() {
  if (process.client) {
    isDark.value = !isDark.value

    // Update document class for dark mode
    if (isDark.value) {
      document.documentElement.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    }
  }
}

function initTheme() {
  if (process.client) {
    // Check for saved theme preference or default to system preference
    const savedTheme = localStorage.getItem('theme')
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches

    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
      isDark.value = true
      document.documentElement.classList.add('dark')
    } else {
      isDark.value = false
      document.documentElement.classList.remove('dark')
    }
  }
}

function handleLogoError() {
  logoError.value = true
}

function toggleLanguageMenu() {
  showLanguageMenu.value = !showLanguageMenu.value
}

function selectLanguage(lang: { code: string; name: string }) {
  currentLanguage.value = lang.name
  showLanguageMenu.value = false

  // Here you would typically emit an event or call a composable to change the app language
  // Example: await $i18n.setLocale(lang.code)
}

function getMicroOrbStyle(index: number) {
  const positions = [
    { left: '15%', top: '20%' },
    { left: '85%', top: '15%' },
    { left: '10%', top: '70%' },
    { left: '90%', top: '80%' },
    { left: '25%', top: '90%' },
    { left: '75%', top: '5%' }
  ]

  const position = positions[index % positions.length]
  const delay = index * 2
  const duration = 8 + (index % 3) * 2

  return {
    left: position.left,
    top: position.top,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function getBrandIconStyle(index: number) {
  const delay = index * 1.8
  const duration = 12 + (index % 4) * 2

  return {
    left: `${8 + (index * 11) % 84}%`,
    top: `${12 + (index * 13) % 76}%`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function getBrandIcon(index: number) {
  const icons = [
    'mdi:cart-outline',
    'mdi:heart-outline',
    'mdi:star-outline',
    'mdi:gift-outline',
    'mdi:tag-outline',
    'mdi:diamond-outline',
    'mdi:crown-outline',
    'mdi:medal-outline'
  ]
  return icons[index % icons.length]
}

// Handle click outside for language menu
function handleClickOutside(event: Event) {
  const target = event.target as HTMLElement
  if (showLanguageMenu.value && !target.closest('[data-language-selector]')) {
    showLanguageMenu.value = false
  }
}

// Lifecycle
onMounted(() => {
  // Initialize theme
  initTheme()

  // Initialize GSAP animations
  if (process.client && gsap) {
    gsap.to([mainOrb1.value, mainOrb2.value, mainOrb3.value], {
      rotation: 360,
      duration: 25,
      repeat: -1,
      ease: 'none'
    })
  }

  // Add click outside listener
  if (process.client) {
    document.addEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  // Remove click outside listener
  if (process.client) {
    document.removeEventListener('click', handleClickOutside)
  }
})
</script>

<style scoped>
/* Custom Animations */
@keyframes float-slow {
  0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
  50% { transform: translateY(-8px) rotate(2deg); opacity: 0.5; }
}

@keyframes float-gentle {
  0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.1; }
  50% { transform: translateY(-6px) rotate(3deg); opacity: 0.2; }
}

.animate-float-slow {
  animation: float-slow 8s ease-in-out infinite;
}

.animate-float-gentle {
  animation: float-gentle 12s ease-in-out infinite;
}

/* Layout Structure */
.auth-layout {
  display: flex;
  flex-direction: column;
}

/* Logo styling */
.auth-layout img {
  transition: opacity 0.3s ease;
}

.auth-layout img[src=""] {
  opacity: 0;
}

/* Dropdown Transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

/* Smooth scrolling for the layout */
.auth-layout {
  scroll-behavior: smooth;
}

/* Enhanced backdrop blur for Webkit browsers */
@supports (backdrop-filter: blur(20px)) {
  .backdrop-blur-xl {
    backdrop-filter: blur(20px);
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .auth-layout header nav {
    padding: 1rem;
  }

  .auth-layout footer {
    padding: 1rem;
  }
}

/* Focus states for accessibility */
.auth-layout button:focus,
.auth-layout a:focus {
  outline: 2px solid theme('colors.blue.500');
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .auth-layout {
    --tw-bg-opacity: 1;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .animate-pulse,
  .animate-float-slow,
  .animate-float-gentle {
    animation: none;
  }

  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
