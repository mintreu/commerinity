<template>
  <div>
    <!-- Enhanced Desktop Navbar -->
    <header
        class="navbar-desktop hidden md:flex fixed top-0 inset-x-0 z-50 transition-all duration-500"
        :class="scrolled ? 'navbar-scrolled' : 'navbar-top'"
        ref="desktopNavbar"
    >
      <!-- Navbar Background -->
      <div class="navbar-background absolute inset-0 backdrop-blur-xl border-b border-white/20 dark:border-gray-800/50">
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/95 to-white/90 dark:from-gray-900/90 dark:via-gray-900/95 dark:to-gray-900/90"></div>

        <!-- Glow Effect -->
        <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent dark:from-blue-400/10"></div>
      </div>

      <!-- Navbar Content -->
      <div class="navbar-content relative z-10 flex items-center justify-between w-full px-6 lg:px-12 py-4">

        <!-- Logo Section -->
        <NuxtLink
            to="/"
            class="logo-container flex items-center gap-4 select-none group transition-all duration-300 hover:scale-105"
            aria-label="Homepage"
        >
          <!-- Logo Image -->
          <div class="logo-wrapper relative">
            <div class="logo-glow absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
            <div class="logo-bg relative w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
              <img src="/logo.png" loading="lazy" class="object-contain w-8 h-8 filter brightness-0 invert" alt="Logo" />
            </div>
          </div>

          <!-- Brand Name -->
          <div class="brand-text">
            <span class="brand-name text-2xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent tracking-tight">
              {{ websiteName }}
            </span>
            <div class="brand-tagline text-xs text-gray-500 dark:text-gray-400 font-medium tracking-wide opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              Premium Shopping Experience
            </div>
          </div>
        </NuxtLink>

        <!-- Navigation Links -->
        <nav class="nav-links-container flex items-center gap-8" ref="navLinksContainer">
          <template v-for="(link, index) in navigationLinks" :key="link.to">
            <NuxtLink
                :to="link.to"
                class="nav-link flex items-center gap-2 px-4 py-2 rounded-xl font-semibold transition-all duration-300 relative overflow-hidden group"
                :class="getNavLinkClasses(link.to)"
                :ref="el => { if (el) navLinkRefs[index] = el }"
                @click="handleNavClick"
                @mouseenter="handleNavHover"
                @mouseleave="handleNavLeave"
            >
              <!-- Background Effect -->
              <div class="nav-link-bg absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl"></div>

              <!-- Icon -->
              <Icon :name="link.icon" class="relative z-10 w-5 h-5 transition-transform duration-300 group-hover:scale-110" />

              <!-- Label -->
              <span class="relative z-10">{{ link.label }}</span>

              <!-- Hover Indicator -->
              <div class="nav-indicator absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-purple-500 group-hover:w-full transition-all duration-300"></div>
            </NuxtLink>
          </template>
        </nav>

        <!-- Right Section -->
        <div class="navbar-actions flex items-center gap-4">
          <!-- Search Button -->
          <button
              @click.stop="handleSearchClick"
              @mouseenter="handleSearchHover"
              @mouseleave="handleSearchLeave"
              class="search-btn w-10 h-10 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-xl flex items-center justify-center transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :class="searchButtonClasses"
              aria-label="Search"
              ref="searchButton"
          >
            <Icon name="mdi:magnify" class="w-5 h-5 transition-transform duration-300" :class="{ 'scale-110': isSearchHovered }" />
          </button>

          <!-- Notifications -->
<!--          <button-->
<!--              class="notification-btn relative w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-orange-500 hover:to-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"-->
<!--              aria-label="Notifications"-->
<!--          >-->
<!--            <Icon name="mdi:bell-outline" class="w-5 h-5" />-->
<!--            &lt;!&ndash; Notification Badge &ndash;&gt;-->
<!--            <div class="notification-badge absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-red-500 to-pink-500 rounded-full border-2 border-white dark:border-gray-900 animate-pulse"></div>-->
<!--          </button>-->

          <NotificationDropdown />

          <!-- Dark Mode Toggle -->
          <DarkModeToggle />

          <!-- User Section -->
          <UserDropdown v-if="isLoggedIn" />
          <template v-else>
            <div class="auth-buttons flex items-center gap-3">
              <NuxtLink
                  to="/auth/login"
                  class="login-btn flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-semibold transition-all duration-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800"
              >
                <Icon name="mdi:login" class="w-4 h-4" />
                Login
              </NuxtLink>
              <NuxtLink
                  to="/auth/register"
                  class="register-btn flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
              >
                <Icon name="mdi:account-plus" class="w-4 h-4" />
                Sign Up
              </NuxtLink>
            </div>
          </template>
        </div>
      </div>
    </header>

    <!-- Enhanced Mobile Navbar -->
    <header class="navbar-mobile md:hidden fixed top-0 inset-x-0 z-50" ref="mobileNavbar">
      <!-- Mobile Background -->
      <div class="mobile-background backdrop-blur-xl bg-white/95 dark:bg-gray-900/95 border-b border-white/30 dark:border-gray-800/50 shadow-lg">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5"></div>
      </div>

      <!-- Mobile Content -->
      <div class="mobile-content relative z-10 flex items-center justify-between px-4 py-3">

        <!-- Menu Button -->
        <button
            @click="toggleSidebar"
            class="menu-btn w-12 h-12 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110"
            aria-label="Open Menu"
        >
          <Icon name="mdi:menu" class="w-6 h-6" />
        </button>

        <!-- Mobile Logo -->
        <NuxtLink
            to="/"
            class="mobile-logo flex items-center gap-3 select-none"
            aria-label="Homepage"
        >
          <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
            <img src="/logo.png" loading="lazy" class="object-contain w-6 h-6 filter brightness-0 invert" alt="Logo" />
          </div>
          <span class="text-xl font-black bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-200 bg-clip-text text-transparent">
            {{ websiteName }}
          </span>
        </NuxtLink>

        <!-- Mobile Actions -->
        <div class="mobile-actions flex items-center gap-2">
          <!-- Search -->
          <button
              @click.stop="handleSearchClick"
              class="mobile-search-btn w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
              aria-label="Search"
          >
            <Icon name="mdi:magnify" class="w-5 h-5" />
          </button>

          <!-- Notifications -->
          <button
              class="relative w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-orange-500 hover:to-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300"
              aria-label="Notifications"
          >
            <Icon name="mdi:bell-outline" class="w-5 h-5" />
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-red-500 to-pink-500 rounded-full animate-pulse"></div>
          </button>
        </div>
      </div>
    </header>

    <!-- Mobile Sidebar Overlay -->
    <transition name="overlay">
      <div
          v-if="sidebarOpen"
          @click="closeSidebar"
          class="sidebar-overlay fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] md:hidden"
      ></div>
    </transition>

    <!-- Enhanced Mobile Sidebar -->
    <transition name="sidebar">
      <aside
          v-if="sidebarOpen"
          class="mobile-sidebar fixed top-0 left-0 h-screen w-80 max-w-[85vw] z-[70] md:hidden overflow-hidden"
          ref="mobileSidebar"
      >
        <!-- Sidebar Background -->
        <div class="sidebar-background absolute inset-0 bg-white dark:bg-gray-900 shadow-2xl">
          <!-- Glassmorphism Effect -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5 backdrop-blur-xl"></div>
          <!-- Border -->
          <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-blue-500/20 via-purple-500/20 to-pink-500/20"></div>
        </div>

        <!-- Sidebar Content -->
        <div class="sidebar-content relative z-10 flex flex-col h-full">

          <!-- Sidebar Header -->
          <div class="sidebar-header flex items-center justify-between p-6 border-b border-gray-200/30 dark:border-gray-700/30">
            <NuxtLink
                to="/"
                class="flex items-center gap-3 select-none group"
                aria-label="Homepage"
                @click="closeSidebar"
            >
              <div class="w-12 h-12 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                <img src="/logo.png" loading="lazy" class="object-contain w-8 h-8 filter brightness-0 invert" alt="Logo" />
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
                class="w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:rotate-90"
                aria-label="Close Menu"
            >
              <Icon name="mdi:close" class="w-5 h-5" />
            </button>
          </div>

          <!-- Sidebar Navigation -->
          <nav class="sidebar-nav flex-1 p-6 overflow-y-auto">
            <div class="space-y-2">

              <!-- Main Navigation Links -->
              <div class="mb-8">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Navigation</h3>
                <template v-for="(link, index) in navigationLinks" :key="`nav-${link.to}`">
                  <NuxtLink
                      :to="link.to"
                      class="sidebar-link flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all duration-300 group relative overflow-hidden hover:scale-105"
                      :class="getSidebarLinkClasses(link.to)"
                      @click="closeSidebar"
                      :ref="el => { if (el) sidebarLinkRefs[index] = el }"
                  >
                    <!-- Background Effect -->
                    <div class="sidebar-link-bg absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-all duration-300 rounded-2xl"></div>

                    <!-- Icon -->
                    <div class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110" :class="getSidebarIconClasses(link.to)">
                      <Icon :name="link.icon" class="w-6 h-6" />
                    </div>

                    <!-- Label -->
                    <div class="flex-1">
                      <span class="relative z-10 text-lg font-bold">{{ link.label }}</span>
                      <div class="text-xs text-gray-500 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        Navigate to {{ link.label }}
                      </div>
                    </div>

                    <!-- Arrow -->
                    <Icon name="mdi:chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-300 group-hover:translate-x-1" />
                  </NuxtLink>
                </template>
              </div>

              <!-- User Actions -->
              <div v-if="isLoggedIn" class="mb-8">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Account</h3>

                <!-- Dashboard Link -->
                <NuxtLink
                    to="/dashboard"
                    class="sidebar-link flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all duration-300 group relative overflow-hidden text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:scale-105"
                    @click="closeSidebar"
                >
                  <div class="sidebar-link-bg absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-all duration-300 rounded-2xl"></div>
                  <div class="sidebar-icon w-12 h-12 bg-gradient-to-br from-blue-100 to-purple-100 dark:bg-gradient-to-br dark:from-blue-900/30 dark:to-purple-900/30 group-hover:from-blue-500 group-hover:to-purple-500 text-gray-600 dark:text-gray-400 group-hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                    <Icon name="mdi:view-dashboard" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <span class="relative z-10 text-lg font-bold">Dashboard</span>
                    <div class="text-xs text-gray-500 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                      Manage your account
                    </div>
                  </div>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors duration-300 group-hover:translate-x-1" />
                </NuxtLink>
              </div>

              <!-- Auth Links for guests -->
              <div v-if="!isLoggedIn" class="mb-8">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Account</h3>

                <!-- Login Link -->
                <NuxtLink
                    to="/auth/login"
                    class="sidebar-link flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all duration-300 group relative overflow-hidden text-gray-700 dark:text-gray-300 hover:text-green-600 dark:hover:text-green-400 hover:scale-105"
                    @click="closeSidebar"
                >
                  <div class="sidebar-link-bg absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-all duration-300 rounded-2xl"></div>
                  <div class="sidebar-icon w-12 h-12 bg-gradient-to-br from-green-100 to-emerald-100 dark:bg-gradient-to-br dark:from-green-900/30 dark:to-emerald-900/30 group-hover:from-green-500 group-hover:to-emerald-500 text-gray-600 dark:text-gray-400 group-hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110">
                    <Icon name="mdi:login" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <span class="relative z-10 text-lg font-bold">Login</span>
                    <div class="text-xs text-gray-500 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                      Access your account
                    </div>
                  </div>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-green-500 transition-colors duration-300 group-hover:translate-x-1" />
                </NuxtLink>

                <!-- Register Link -->
                <NuxtLink
                    to="/auth/register"
                    class="sidebar-link mt-2 flex items-center gap-4 p-4 rounded-2xl font-semibold transition-all duration-300 group relative overflow-hidden bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 shadow-lg hover:scale-105"
                    @click="closeSidebar"
                >
                  <div class="sidebar-icon w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <Icon name="mdi:account-plus" class="w-6 h-6" />
                  </div>
                  <div class="flex-1">
                    <span class="text-lg font-bold">Sign Up</span>
                    <div class="text-xs text-white/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                      Create new account
                    </div>
                  </div>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 text-white/80 group-hover:text-white transition-colors duration-300 group-hover:translate-x-1" />
                </NuxtLink>
              </div>

              <!-- Quick Actions -->
              <div class="mt-auto">
                <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Settings</h3>
                <div class="flex gap-2">
                  <DarkModeToggle class="flex-1" />
                  <button
                      class="flex-1 p-4 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 hover:from-purple-500 hover:to-pink-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-xl transition-all duration-300 flex items-center justify-center hover:scale-105"
                      aria-label="Settings"
                  >
                    <Icon name="mdi:cog" class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
          </nav>

          <!-- User Footer -->
          <div
              v-if="isLoggedIn && user"
              class="sidebar-footer border-t border-gray-200/30 dark:border-gray-700/30 p-6"
          >
            <div class="user-profile flex items-center gap-4 p-4 bg-gradient-to-br from-gray-50 to-blue-50/50 dark:from-gray-800/80 dark:to-blue-900/20 rounded-2xl border border-gray-200/30 dark:border-gray-700/30 hover:scale-105 transition-transform duration-300">
              <div class="user-avatar relative">
                <img :src="user.avatar" loading="lazy" alt="avatar" class="w-12 h-12 rounded-xl object-cover border-2 border-white dark:border-gray-700 shadow-lg" />
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full animate-pulse"></div>
              </div>
              <div class="user-info flex-1">
                <span class="user-name font-black text-gray-900 dark:text-white block text-sm">{{ user.name }}</span>
                <span class="user-email text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</span>
              </div>
              <button
                  @click="logoutUser"
                  class="logout-btn w-10 h-10 bg-red-100 dark:bg-red-900/30 hover:bg-red-500 text-red-600 hover:text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 hover:rotate-12"
                  aria-label="Logout"
              >
                <Icon name="mdi:logout" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </aside>
    </transition>

    <!-- Search Modal -->
    <SearchModal v-if="searchOpen" @close="closeSearch" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctum } from '#imports'
import DarkModeToggle from '~/components/ui/DarkModeToggle.vue'
import UserDropdown from '~/components/ui/UserDropdown.vue'
import SearchModal from '~/components/ui/SearchModal.vue'
import NotificationDropdown from "~/components/ui/NotificationDropdown.vue";

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

// Interface
interface User {
  name: string
  email: string
  avatar: string
  type: string
  role: string
}

// Composables
const { isLoggedIn, user, logout } = useSanctum<User>()

// State
const sidebarOpen = ref(false)
const searchOpen = ref(false)
const scrolled = ref(false)
const isSearchHovered = ref(false)
const searchClickDebounce = ref(false)

// Refs
const desktopNavbar = ref<HTMLElement>()
const mobileNavbar = ref<HTMLElement>()
const mobileSidebar = ref<HTMLElement>()
const navLinksContainer = ref<HTMLElement>()
const searchButton = ref<HTMLElement>()
const navLinkRefs = ref<(HTMLElement | null)[]>([])
const sidebarLinkRefs = ref<(HTMLElement | null)[]>([])

let gsapContext: any = null
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null

// Navigation links
const baseLinks = [
  { to: '/', label: 'Home', icon: 'mdi:home', color: 'blue' },
  { to: '/store', label: 'Store', icon: 'mdi:store', color: 'purple' },
  { to: '/categories', label: 'Categories', icon: 'mdi:grid', color: 'green' },
  { to: '/career', label: 'Career', icon: 'mdi:briefcase', color: 'teal' },
  { to: '/blog', label: 'Blog', icon: 'mdi:post', color: 'orange' },
  { to: '/about', label: 'About', icon: 'mdi:information', color: 'indigo' }
]

const navigationLinks = computed(() => {
  const links = [...baseLinks]

  if (isLoggedIn.value && user.value) {
    if (user.value.type === 'applicant') {
      links.push({ to: '/career', label: 'My Career', icon: 'mdi:briefcase', color: 'pink' })
    }
    if (user.value.role === 'admin') {
      links.push({ to: '/admin', label: 'Admin Panel', icon: 'mdi:shield-check', color: 'red' })
    }
    if (user.value.role === 'recruiter') {
      links.push({ to: '/recruiter/jobs', label: 'My Listings', icon: 'mdi:clipboard-list', color: 'teal' })
    }
  }

  return links
})

// Computed for search button classes
const searchButtonClasses = computed(() => ({
  'hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-500 hover:text-white hover:scale-110 hover:shadow-lg': !searchClickDebounce.value,
  'scale-105 bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg': isSearchHovered.value && !searchClickDebounce.value
}))

// Enhanced Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value

  // Add haptic feedback
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(50)
  }
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const handleSearchClick = (event: MouseEvent) => {
  // Prevent multiple rapid clicks
  if (searchClickDebounce.value) return

  event.preventDefault()
  event.stopPropagation()

  searchClickDebounce.value = true

  // Clear any existing timer
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
  }

  // Reset debounce after a short delay
  searchDebounceTimer = setTimeout(() => {
    searchClickDebounce.value = false
  }, 300)

  // Toggle search
  searchOpen.value = !searchOpen.value

  // Add haptic feedback
  if (process.client && 'vibrate' in navigator) {
    navigator.vibrate(30)
  }
}

const closeSearch = () => {
  searchOpen.value = false
}

const handleSearchHover = () => {
  if (searchClickDebounce.value) return
  isSearchHovered.value = true
}

const handleSearchLeave = () => {
  isSearchHovered.value = false
}

const handleNavClick = (event: MouseEvent) => {
  // Ensure search interactions don't interfere
  if (searchOpen.value) {
    closeSearch()
  }
}

const handleNavHover = () => {
  // Close search hover state when hovering nav links
  if (isSearchHovered.value) {
    isSearchHovered.value = false
  }
}

const handleNavLeave = () => {
  // Optional: could add nav-specific leave logic here
}

const logoutUser = () => {
  logout()
  closeSidebar()
}

const handleScroll = () => {
  scrolled.value = window.scrollY > 20
}

// Styling methods
const getNavLinkClasses = (path: string) => {
  const isActive = route.path === path || (route.path.startsWith(path) && path !== '/')
  return {
    'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20': isActive,
    'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400': !isActive
  }
}

const getSidebarLinkClasses = (path: string) => {
  const isActive = route.path === path || (route.path.startsWith(path) && path !== '/')
  return {
    'text-blue-600 dark:text-blue-400 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 shadow-lg': isActive,
    'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400': !isActive
  }
}

const getSidebarIconClasses = (path: string) => {
  const isActive = route.path === path || (route.path.startsWith(path) && path !== '/')
  return {
    'bg-gradient-to-br from-blue-500 to-purple-500 text-white shadow-lg': isActive,
    'bg-gradient-to-br from-gray-100 to-blue-100 dark:from-gray-800 dark:to-blue-900/30 text-gray-600 dark:text-gray-400 group-hover:from-blue-500 group-hover:to-purple-500 group-hover:text-white': !isActive
  }
}

// GSAP animations
const initializeAnimations = () => {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Desktop navbar entrance
    if (desktopNavbar.value) {
      gsap.fromTo(desktopNavbar.value,
          { y: -100, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 1,
            ease: 'back.out(1.7)'
          }
      )
    }

    // Mobile navbar entrance
    if (mobileNavbar.value) {
      gsap.fromTo(mobileNavbar.value,
          { y: -80, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.8,
            ease: 'back.out(1.7)'
          }
      )
    }

    // Navigation links stagger animation
    if (navLinkRefs.value.length > 0) {
      gsap.fromTo(navLinkRefs.value.filter(Boolean),
          { y: 20, opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.6,
            stagger: 0.1,
            ease: 'back.out(1.7)',
            delay: 0.3
          }
      )
    }
  })
}

// Lifecycle
onMounted(() => {
  // Add scroll listener
  window.addEventListener('scroll', handleScroll, { passive: true })

  // Initialize animations
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (process.client) {
    window.removeEventListener('scroll', handleScroll)
  }

  if (gsapContext) {
    gsapContext.kill()
  }

  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
  }
})

// Watch for route changes to close search
watch(() => route.path, () => {
  if (searchOpen.value) {
    closeSearch()
  }
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Desktop Navbar */
.navbar-desktop {
  height: 80px;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.navbar-top {
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.navbar-scrolled {
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
  height: 70px;
}

.navbar-background {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Logo animations */
.logo-wrapper:hover .logo-bg {
  transform: scale(1.05);
}

/* Navigation links */
.nav-link {
  position: relative;
  backdrop-filter: blur(10px);
  isolation: isolate; /* Prevent stacking context issues */
}

.nav-link:hover {
  transform: translateY(-2px);
}

/* Search Button - Enhanced specificity and isolation */
.search-btn {
  position: relative;
  isolation: isolate;
  z-index: 10;
  pointer-events: auto;
  user-select: none;
}

.search-btn:hover {
  transform: scale(1.1);
}

.search-btn:active {
  transform: scale(0.95);
}

.mobile-search-btn {
  position: relative;
  isolation: isolate;
  z-index: 10;
  pointer-events: auto;
}

/* Prevent button blinking/flickering */
.search-btn,
.mobile-search-btn {
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform-style: preserve-3d;
}

/* Mobile Navbar */
.navbar-mobile {
  height: 64px;
}

.mobile-background {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Mobile Sidebar - Full height and proper design */
.mobile-sidebar {
  height: 100vh;
  height: 100dvh; /* For better mobile support */
  min-height: 100vh;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.sidebar-background {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.sidebar-content {
  height: 100%;
  min-height: 100vh;
}

.sidebar-nav {
  max-height: calc(100vh - 200px);
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background-color: rgba(107, 114, 128, 0.7);
}

.sidebar-link {
  position: relative;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.sidebar-link:hover {
  transform: translateX(5px) scale(1.02);
}

/* User profile */
.user-profile:hover {
  transform: scale(1.05);
}

/* Transitions */
.sidebar-enter-active,
.sidebar-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-enter-from,
.sidebar-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.overlay-enter-active,
.overlay-leave-active {
  transition: all 0.3s ease;
}

.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

/* Button hover effects - Prevent conflicts */
.notification-btn::before,
.menu-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
  border-radius: 0.75rem;
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

.notification-btn:hover::before,
.menu-btn:hover::before {
  opacity: 1;
}

/* Enhanced Mobile Sidebar Design */
.sidebar-header {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Responsive design - Ensures proper mobile/desktop separation */
@media (max-width: 767px) {
  .navbar-desktop {
    display: none !important;
  }

  .mobile-sidebar {
    width: min(360px, 85vw);
  }
}

@media (min-width: 768px) {
  .navbar-mobile {
    display: none !important;
  }

  .mobile-sidebar {
    display: none !important;
  }

  .sidebar-overlay {
    display: none !important;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .navbar-top {
    background: rgba(17, 24, 39, 0.9);
  }

  .navbar-scrolled {
    background: rgba(17, 24, 39, 0.95);
  }
}

/* Focus states */
.nav-link:focus-visible,
.sidebar-link:focus-visible,
.search-btn:focus-visible,
.mobile-search-btn:focus-visible,
.notification-btn:focus-visible,
.menu-btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Notification badge animation */
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

/* Prevent unwanted interactions during animations */
.navbar-actions {
  isolation: isolate;
}

.navbar-actions > * {
  position: relative;
  z-index: 1;
}

/* Additional stability for search button */
.search-btn {
  will-change: transform;
  transform: translateZ(0);
}

.search-btn:not(:hover):not(:focus):not(:active) {
  transform: translateZ(0) scale(1);
}
</style>
