<template>
  <div ref="dropdownRef" v-if="user" class="relative">
    <!-- Enhanced User Button -->
    <button
        @click="toggleDropdown"
        class="flex items-center gap-3 p-2 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-300 relative group"
        :class="{ 'bg-slate-100 dark:bg-slate-800': dropdownOpen }"
    >
      <!-- User Avatar with Glow Effect -->
      <div class="relative">
        <!-- Glow Effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>

        <!-- Avatar Container -->
        <div class="relative">
          <img
              :src="user.avatar || '/default-avatar.png'"
              loading="lazy"
              class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-slate-800 shadow-lg transition-all duration-300 group-hover:scale-110"
              :alt="user.name"
          />
          <!-- Online Status Indicator -->
          <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 border-2 border-white dark:border-slate-800 rounded-full animate-pulse"></div>
        </div>
      </div>

      <!-- User Info (Hidden on Mobile) -->
      <div class="hidden sm:block text-left">
        <span class="font-bold text-sm text-slate-900 dark:text-white block leading-tight">{{ user.name }}</span>
        <span class="text-xs text-slate-500 dark:text-slate-400 capitalize">{{ user.role || 'Member' }}</span>
      </div>

      <!-- Dropdown Arrow -->
      <Icon
          name="mdi:chevron-down"
          class="w-5 h-5 text-slate-500 dark:text-slate-400 transition-transform duration-300"
          :class="{ 'rotate-180': dropdownOpen }"
      />
    </button>

    <!-- Dropdown Menu -->
    <Teleport to="body">
      <!-- Overlay -->
      <div
          v-if="dropdownOpen"
          class="fixed inset-0 z-[9998] bg-black/20 backdrop-blur-sm"
          @click="closeDropdown"
      ></div>

      <!-- Menu -->
      <div
          v-if="dropdownOpen"
          ref="dropdownMenu"
          class="fixed z-[9999] w-80 max-h-[90vh] shadow-2xl"
          :style="dropdownPosition"
      >
        <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl rounded-3xl border border-white/20 dark:border-slate-800/50 overflow-hidden relative">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-500/5 pointer-events-none"></div>

          <div class="relative z-10 max-h-[90vh] overflow-y-auto">
            <!-- User Header -->
            <div class="sticky top-0 z-20 p-6 border-b border-slate-200/50 dark:border-slate-700/50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl">
              <div class="flex items-center gap-4">
                <div class="relative">
                  <img :src="user.avatar || '/default-avatar.png'" loading="lazy" class="w-12 h-12 rounded-2xl object-cover shadow-lg" :alt="user.name" />
                  <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-slate-900 rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-black text-lg text-slate-900 dark:text-white truncate">{{ user.name }}</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{{ user.email }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="px-2 py-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold rounded-full">{{ user.role || 'Member' }}</div>
                    <div class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-semibold rounded-full">Online</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="p-4 border-b border-slate-200/50 dark:border-slate-700/50">
              <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl transition-transform duration-200 hover:scale-105">
                  <div class="text-xl font-black text-blue-600 dark:text-blue-400">12</div>
                  <div class="text-xs text-slate-600 dark:text-slate-400">Orders</div>
                </div>
                <div class="text-center p-3 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl transition-transform duration-200 hover:scale-105">
                  <div class="text-xl font-black text-green-600 dark:text-green-400">$230</div>
                  <div class="text-xs text-slate-600 dark:text-slate-400">Wallet</div>
                </div>
                <div class="text-center p-3 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl transition-transform duration-200 hover:scale-105">
                  <div class="text-xl font-black text-purple-600 dark:text-purple-400">8</div>
                  <div class="text-xs text-slate-600 dark:text-slate-400">Rewards</div>
                </div>
              </div>
            </div>

            <!-- Menu Items -->
            <div class="p-3 space-y-2">
              <NuxtLink
                  v-if="isDashboard"
                  to="/"
                  ref="menuItem1"
                  class="flex items-center gap-3 p-3 rounded-2xl hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10 transition-all duration-300 group"
                  @click="closeDropdown"
              >
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:home" class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="font-semibold text-slate-900 dark:text-white block truncate">Back to Home</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">Browse products</span>
                </div>
                <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 transition-colors duration-300 flex-shrink-0" />
              </NuxtLink>

              <NuxtLink
                  v-else
                  to="/dashboard"
                  ref="menuItem1"
                  class="flex items-center gap-3 p-3 rounded-2xl hover:bg-gradient-to-r hover:from-purple-500/10 hover:to-pink-500/10 transition-all duration-300 group"
                  @click="closeDropdown"
              >
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:view-dashboard" class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="font-semibold text-slate-900 dark:text-white block truncate">Dashboard</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">Manage account</span>
                </div>
                <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 transition-colors duration-300 flex-shrink-0" />
              </NuxtLink>

              <NuxtLink to="/dashboard/account" ref="menuItem2" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-gradient-to-r hover:from-green-500/10 hover:to-emerald-500/10 transition-all duration-300 group" @click="closeDropdown">
                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:account" class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="font-semibold text-slate-900 dark:text-white block truncate">Profile Settings</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">Edit information</span>
                </div>
                <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 transition-colors duration-300 flex-shrink-0" />
              </NuxtLink>

              <NuxtLink to="/dashboard/orders" ref="menuItem3" class="flex items-center gap-3 p-3 rounded-2xl hover:bg-gradient-to-r hover:from-orange-500/10 hover:to-red-500/10 transition-all duration-300 group" @click="closeDropdown">
                <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:package-variant-closed" class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                  <span class="font-semibold text-slate-900 dark:text-white block truncate">My Orders</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">Track purchases</span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <div class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[1.5rem] text-center">3</div>
                  <Icon name="mdi:arrow-right" class="w-4 h-4 text-slate-400 transition-colors duration-300" />
                </div>
              </NuxtLink>

              <div class="my-3 border-t border-slate-200 dark:border-slate-700"></div>

              <button ref="logoutBtn" @click="logoutUser" class="w-full flex items-center gap-3 p-3 rounded-2xl hover:bg-gradient-to-r hover:from-red-500/10 hover:to-pink-500/10 text-red-600 dark:text-red-400 transition-all duration-300 group">
                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:logout" class="w-5 h-5" />
                </div>
                <div class="text-left flex-1 min-w-0">
                  <span class="font-semibold block truncate">Logout</span>
                  <span class="text-xs opacity-75">Sign out</span>
                </div>
                <Icon name="mdi:arrow-right" class="w-4 h-4 opacity-50 transition-opacity duration-300 flex-shrink-0" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctum } from '#imports'

interface User {
  name: string
  email: string
  avatar?: string
  role?: string
}

const { user, logout } = useSanctum<User>()
const route = useRoute()

const dropdownOpen = ref(false)
const dropdownPosition = ref({})
const dropdownRef = ref<HTMLElement | null>(null)
const dropdownMenu = ref<HTMLElement | null>(null)
const menuItem1 = ref<HTMLElement | null>(null)
const menuItem2 = ref<HTMLElement | null>(null)
const menuItem3 = ref<HTMLElement | null>(null)
const logoutBtn = ref<HTMLElement | null>(null)

// ✅ OPTIMIZED: Lazy load GSAP only when dropdown opens
let gsap: any = null
let gsapLoaded = false

const loadGsap = async () => {
  if (gsapLoaded) return gsap

  try {
    const module = await import('gsap')
    gsap = module.gsap
    gsapLoaded = true
    return gsap
  } catch (error) {
    console.error('Failed to load GSAP:', error)
    return null
  }
}

const isDashboard = computed(() => route.path.startsWith('/dashboard'))

function calculateDropdownPosition() {
  if (!dropdownRef.value) return

  const rect = dropdownRef.value.getBoundingClientRect()
  const viewportWidth = window.innerWidth
  const viewportHeight = window.innerHeight
  const dropdownWidth = 320
  const dropdownHeight = 600

  let top = rect.bottom + 8
  let left = rect.left

  if (left + dropdownWidth > viewportWidth) {
    left = rect.right - dropdownWidth
  }
  if (left < 8) {
    left = 8
  }

  if (top + dropdownHeight > viewportHeight) {
    top = rect.top - dropdownHeight - 8
  }
  if (top < 8) {
    top = 8
  }

  dropdownPosition.value = {
    top: `${top}px`,
    left: `${left}px`
  }
}

async function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value

  if (dropdownOpen.value) {
    await nextTick()
    calculateDropdownPosition()

    // ✅ OPTIMIZED: Load GSAP only when needed
    const gsapInstance = await loadGsap()

    if (gsapInstance && dropdownMenu.value) {
      // Animate dropdown entrance
      gsapInstance.fromTo(
          dropdownMenu.value,
          { opacity: 0, scale: 0.95, y: -10 },
          { opacity: 1, scale: 1, y: 0, duration: 0.3, ease: 'power2.out' }
      )

      // Stagger menu items
      const items = [menuItem1.value, menuItem2.value, menuItem3.value, logoutBtn.value].filter(Boolean)
      gsapInstance.fromTo(
          items,
          { opacity: 0, x: -20 },
          { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, ease: 'power2.out' }
      )
    }

    if (process.client && 'vibrate' in navigator) {
      navigator.vibrate(30)
    }
  }
}

async function logoutUser() {
  // ✅ OPTIMIZED: Only use GSAP if already loaded
  if (gsapLoaded && gsap && logoutBtn.value) {
    await gsap.to(logoutBtn.value, {
      opacity: 0,
      scale: 0.9,
      duration: 0.2,
      ease: 'power2.in'
    })
  }

  logout()
  dropdownOpen.value = false
}

function closeDropdown() {
  dropdownOpen.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    dropdownOpen.value = false
  }
}

function handleResize() {
  if (dropdownOpen.value) {
    calculateDropdownPosition()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
})
</script>
