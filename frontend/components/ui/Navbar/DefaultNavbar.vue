<template>
  <div>
    <!-- Desktop Navbar -->
    <header
        class="hidden md:flex fixed top-0 inset-x-0 z-50 transition-all duration-500"
        :class="scrolled ? 'h-16' : 'h-20'"
    >
      <!-- Background -->
      <div class="absolute inset-0 backdrop-blur-xl border-b border-white/20 dark:border-gray-800/50">
        <div class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/95 to-white/90 dark:from-gray-900/90 dark:via-gray-900/95 dark:to-gray-900/90" />
        <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent dark:from-blue-400/10" />
      </div>

      <!-- Content -->
      <div class="relative z-10 flex items-center justify-between w-full px-4 md:px-6 lg:px-12 py-3">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-3 select-none group transition-transform duration-300 hover:scale-105">
          <div class="relative">
            <div class="relative w-11 h-11 rounded-2xl flex items-center justify-center shadow-lg">
              <img src="/logo.png" :alt="websiteName">
            </div>
          </div>
          <div>
            <span class="text-xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
              {{ websiteName }}
            </span>
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
              Premium Shopping
            </div>
          </div>
        </NuxtLink>

        <!-- Navigation -->
        <nav class="hidden lg:flex items-center gap-2">
          <NuxtLink
              v-for="link in navigationLinks"
              :key="link.to"
              :to="link.to"
              class="relative flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm transition-all duration-300 group"
              :class="getNavLinkClasses(link.to)"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl" />
            <Icon :name="link.icon" class="relative z-10 w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
            <span class="relative z-10">{{ link.label }}</span>
          </NuxtLink>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <!-- Search -->
          <button
              @click="searchOpen = true"
              class="w-10 h-10 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110"
          >
            <Icon name="mdi:magnify" class="w-5 h-5" />
          </button>

          <!-- Notifications - LAZY LOADED -->
          <ClientOnly>
            <Suspense>
              <NotificationDropdown />
              <template #fallback>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl animate-pulse" />
              </template>
            </Suspense>
          </ClientOnly>

          <!-- Dark Mode - LAZY LOADED -->
          <ClientOnly>
            <Suspense>
              <DarkModeToggle />
              <template #fallback>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-xl animate-pulse" />
              </template>
            </Suspense>
          </ClientOnly>

          <!-- User - LAZY LOADED -->
          <ClientOnly>
            <Suspense v-if="isLoggedIn">
              <UserDropdown />
              <template #fallback>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-full animate-pulse" />
              </template>
            </Suspense>
          </ClientOnly>

          <template v-if="!isLoggedIn">
            <div class="hidden md:flex items-center gap-2">
              <NuxtLink
                  to="/auth/login"
                  class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-semibold transition-all rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <Icon name="mdi:login" class="w-4 h-4" />
                Login
              </NuxtLink>
              <NuxtLink
                  to="/auth/register"
                  class="flex items-center gap-2 px-6 py-2 text-sm bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105"
              >
                <Icon name="mdi:account-plus" class="w-4 h-4" />
                Sign Up
              </NuxtLink>
            </div>
          </template>
        </div>
      </div>
    </header>

    <!-- Mobile Navbar -->
    <header class="md:hidden fixed top-0 inset-x-0 z-50 h-16">
      <div class="absolute inset-0 backdrop-blur-xl bg-white/95 dark:bg-gray-900/95 border-b border-white/30 dark:border-gray-800/50 shadow-lg">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5" />
      </div>

      <div class="relative z-10 flex items-center justify-between h-full px-4">
        <!-- Menu Button -->
        <button
            @click="toggleSidebar"
            class="w-12 h-12 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
        >
          <Icon name="mdi:menu" class="w-6 h-6" />
        </button>

        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-2 select-none">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg">
            <img src="/logo.png" :alt="websiteName">
          </div>
          <span class="text-xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
            {{ websiteName }}
          </span>
        </NuxtLink>

        <!-- Mobile Actions -->
        <div class="flex items-center gap-2">
          <button
              @click="searchOpen = true"
              class="w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all"
          >
            <Icon name="mdi:magnify" class="w-5 h-5" />
          </button>

          <button
              class="relative w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-orange-500 hover:to-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all"
          >
            <Icon name="mdi:bell-outline" class="w-5 h-5" />
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-red-500 to-pink-500 rounded-full animate-pulse" />
          </button>
        </div>
      </div>
    </header>

    <!-- Sidebar Overlay -->
    <Transition name="overlay">
      <div
          v-if="sidebarOpen"
          @click="closeSidebar"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] md:hidden"
      />
    </Transition>

    <!-- Mobile Sidebar -->
    <Transition name="sidebar">
      <aside
          v-if="sidebarOpen"
          class="fixed top-0 left-0 h-screen w-[min(360px,85vw)] z-[70] md:hidden"
      >
        <div class="absolute inset-0 bg-white dark:bg-gray-900 shadow-2xl">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5" />
        </div>

        <div class="relative z-10 flex flex-col h-full">
          <!-- Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200/30 dark:border-gray-700/30">
            <NuxtLink
                to="/"
                class="flex items-center gap-3 select-none group"
                @click="closeSidebar"
            >
              <div class="w-12 h-12 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:store" class="w-8 h-8 text-white" />
              </div>
              <div>
                <span class="text-xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
                  {{ websiteName }}
                </span>
                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Premium Experience</div>
              </div>
            </NuxtLink>

            <button
                @click="closeSidebar"
                class="w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all"
            >
              <Icon name="mdi:close" class="w-5 h-5" />
            </button>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 p-6 overflow-y-auto">
            <div class="space-y-2">
              <div class="mb-8">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                <NuxtLink
                    v-for="link in navigationLinks"
                    :key="link.to"
                    :to="link.to"
                    class="relative flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all duration-300 group"
                    :class="getSidebarLinkClasses(link.to)"
                    @click="closeSidebar"
                >
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-all rounded-2xl" />
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-all" :class="getSidebarIconClasses(link.to)">
                    <Icon :name="link.icon" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <span class="relative z-10 text-lg font-bold">{{ link.label }}</span>
                  </div>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-all" />
                </NuxtLink>
              </div>

              <!-- Auth Section -->
              <div v-if="!isLoggedIn" class="mb-8">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 px-2">Account</h3>
                <NuxtLink
                    to="/auth/login"
                    class="relative flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all group text-gray-700 dark:text-gray-300"
                    @click="closeSidebar"
                >
                  <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 group-hover:from-green-500 group-hover:to-emerald-500 text-gray-600 dark:text-gray-400 group-hover:text-white rounded-xl flex items-center justify-center transition-all">
                    <Icon name="mdi:login" class="w-6 h-6" />
                  </div>
                  <span class="text-lg font-bold">Login</span>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 ml-auto" />
                </NuxtLink>

                <NuxtLink
                    to="/auth/register"
                    class="mt-2 flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all group bg-gradient-to-r from-blue-600 to-purple-600 text-white"
                    @click="closeSidebar"
                >
                  <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <Icon name="mdi:account-plus" class="w-6 h-6" />
                  </div>
                  <span class="text-lg font-bold">Sign Up</span>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 ml-auto" />
                </NuxtLink>
              </div>

              <!-- Settings -->
              <div class="mt-auto">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 px-2">Settings</h3>
                <ClientOnly>
                  <Suspense>
                    <DarkModeToggle class="flex-1" />
                    <template #fallback>
                      <div class="w-full h-10 bg-gray-100 dark:bg-gray-800 rounded-xl animate-pulse" />
                    </template>
                  </Suspense>
                </ClientOnly>
              </div>
            </div>
          </nav>

          <!-- User Footer -->
          <div v-if="isLoggedIn && user" class="border-t border-gray-200/30 dark:border-gray-700/30 p-6">
            <div class="flex items-center gap-4 p-4 bg-gradient-to-br from-gray-50 to-blue-50/50 dark:from-gray-800/80 dark:to-blue-900/20 rounded-2xl">
              <div class="relative">
                <img :src="user.avatar" alt="avatar" class="w-12 h-12 rounded-xl object-cover border-2 border-white dark:border-gray-700" />
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full" />
              </div>
              <div class="flex-1 min-w-0">
                <span class="font-black text-gray-900 dark:text-white block text-sm truncate">{{ user.name }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 truncate block">{{ user.email }}</span>
              </div>
              <button
                  @click="logoutUser"
                  class="w-10 h-10 bg-red-100 dark:bg-red-900/30 hover:bg-red-500 text-red-600 hover:text-white rounded-xl flex items-center justify-center transition-all"
              >
                <Icon name="mdi:logout" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </aside>
    </Transition>

    <!-- Search Modal - LAZY LOADED -->
    <ClientOnly>
      <Suspense v-if="searchOpen">
        <SearchModal @close="searchOpen = false" />
        <template #fallback>
          <div class="fixed inset-0 z-[9999] bg-black/50 flex items-center justify-center">
            <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin" />
          </div>
        </template>
      </Suspense>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctum } from '#imports'

// ✅ CRITICAL: LAZY LOAD ALL HEAVY COMPONENTS
const DarkModeToggle = defineAsyncComponent(() => import('~/components/ui/DarkModeToggle.vue'))
const UserDropdown = defineAsyncComponent(() => import('~/components/ui/UserDropdown.vue'))
const SearchModal = defineAsyncComponent(() => import('~/components/ui/SearchModal.vue'))
const NotificationDropdown = defineAsyncComponent(() => import('~/components/ui/NotificationDropdown.vue'))

// Composables
const route = useRoute()
const config = useRuntimeConfig()
const websiteName = config.public.websiteName || 'Premium Store'

// Interface
interface User {
  name: string
  email: string
  avatar: string
  type?: string
  role?: string
}

// Auth
const { isLoggedIn, user, logout } = useSanctum<User>()

// State
const sidebarOpen = ref(false)
const searchOpen = ref(false)
const scrolled = ref(false)

// ✅ OPTIMIZED: Use plain array instead of computed
const navigationLinks = [
  { to: '/', label: 'Home', icon: 'mdi:home' },
  { to: '/store', label: 'Store', icon: 'mdi:store' },
  { to: '/categories', label: 'Categories', icon: 'mdi:grid' },
  { to: '/career', label: 'Career', icon: 'mdi:briefcase' },
  { to: '/blog', label: 'Blog', icon: 'mdi:post' },
  { to: '/about', label: 'About', icon: 'mdi:information' }
]

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const logoutUser = () => {
  logout()
  closeSidebar()
}

const handleScroll = () => {
  scrolled.value = window.scrollY > 20
}

// ✅ OPTIMIZED: Memoized path checking
let cachedPath = ''
let cachedResults = new Map()

const getNavLinkClasses = (path: string) => {
  if (cachedPath !== route.path) {
    cachedPath = route.path
    cachedResults.clear()
  }

  if (!cachedResults.has(path)) {
    const isActive = route.path === path || (route.path.startsWith(path) && path !== '/')
    cachedResults.set(path, {
      'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20': isActive,
      'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400': !isActive
    })
  }

  return cachedResults.get(path)
}

const getSidebarLinkClasses = (path: string) => {
  const isActive = route.path === path || (route.path.startsWith(path) && path !== '/')
  return {
    'text-blue-600 dark:text-blue-400 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20': isActive,
    'text-gray-700 dark:text-gray-300': !isActive
  }
}

const getSidebarIconClasses = (path: string) => {
  const isActive = route.path === path || (route.path.startsWith(path) && path !== '/')
  return {
    'bg-gradient-to-br from-blue-500 to-purple-500 text-white': isActive,
    'bg-gradient-to-br from-gray-100 to-blue-100 dark:from-gray-800 dark:to-blue-900/30 text-gray-600 dark:text-gray-400 group-hover:from-blue-500 group-hover:to-purple-500 group-hover:text-white': !isActive
  }
}

// Lifecycle
onMounted(() => {
  if (process.client) {
    window.addEventListener('scroll', handleScroll, { passive: true })
  }
})

onUnmounted(() => {
  if (process.client) {
    window.removeEventListener('scroll', handleScroll)
  }
})
</script>

<style scoped>
.sidebar-enter-active,
.sidebar-leave-active {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-enter-from,
.sidebar-leave-to {
  transform: translateX(-100%);
}

.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

@supports (backdrop-filter: blur(20px)) {
  .backdrop-blur-xl {
    backdrop-filter: blur(20px);
  }
}
</style>
