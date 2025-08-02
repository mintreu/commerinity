<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100">
    <!-- Sidebar -->
    <aside
        ref="sidebarEl"
        :class="[
        'bg-white dark:bg-gray-900 shadow-md flex flex-col transition-all duration-300',
        collapsed ? 'w-20' : 'w-64'
      ]"
    >
      <!-- Toggle -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-800">
        <span v-if="!collapsed" class="text-xl font-bold text-primary-600 dark:text-primary-400">MyAdmin</span>
        <button @click="toggleSidebar" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
          <Icon :name="collapsed ? 'mdi:chevron-right' : 'mdi:chevron-left'" />
        </button>
      </div>

      <!-- Nav Links -->
      <nav class="flex-1 p-4 space-y-2 text-sm">
        <NuxtLink
            to="/dashboard"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
            :class="{ 'bg-gray-200 dark:bg-gray-800': $route.path === '/dashboard' }"
        >
          <Icon name="mdi:view-dashboard" />
          <span v-if="!collapsed">Dashboard</span>
        </NuxtLink>
        <NuxtLink to="/dashboard/users" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <Icon name="mdi:account-multiple-outline" />
          <span v-if="!collapsed">Users</span>
        </NuxtLink>
        <NuxtLink to="/dashboard/orders" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <Icon name="mdi:cart-outline" />
          <span v-if="!collapsed">Orders</span>
        </NuxtLink>
        <NuxtLink to="/dashboard/settings" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <Icon name="mdi:cog-outline" />
          <span v-if="!collapsed">Settings</span>
        </NuxtLink>

        <NuxtLink to="/dashboard/helpdesk" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <Icon name="mdi:cog-outline" />
          <span v-if="!collapsed">HelpDesk</span>
        </NuxtLink>
        <NuxtLink to="/dashboard/faq" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
          <Icon name="mdi:cog-outline" />
          <span v-if="!collapsed">Faq</span>
        </NuxtLink>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
      <!-- Topbar -->
      <header class="h-16 px-6 flex items-center justify-end gap-4 border-b bg-white dark:bg-gray-900 dark:border-gray-800 relative">
        <!-- Dark mode toggle -->
        <button
            @click="toggleDark"
            class="transition hover:text-yellow-500 dark:hover:text-yellow-300"
            aria-label="Toggle Dark Mode"
        >
          <Icon v-if="!isDark" name="bi:moon-fill" width="20" height="20" />
          <Icon v-else name="bi:brightness-high-fill" width="20" height="20" />
        </button>

        <!-- User dropdown -->
        <div v-if="user" class="relative">
          <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 focus:outline-none">
            <img :src="user.avatar" alt="avatar" class="w-8 h-8 rounded-full" />
            <span class="hidden sm:block font-medium">{{ user.name }}</span>
            <Icon name="mdi:chevron-down" class="w-4 h-4" />
          </button>
          <div
              v-if="dropdownOpen"
              class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50"
          >
            <NuxtLink
                to="/dashboard/auth/profile"
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              Profile
            </NuxtLink>
            <button
                @click="logoutUser"
                class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              Logout
            </button>
          </div>
        </div>
      </header>

      <!-- Page content slot -->
      <main class="p-6 flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import gsap from 'gsap'
import { useSanctum } from '#imports'

interface User {
  name: string
  email: string
  avatar: string
}

const { user, logout } = useSanctum<User>()
const dropdownOpen = ref(false)
const isDark = ref(false)

const collapsed = ref(false)
const sidebarEl = ref<HTMLElement | null>(null)

function toggleSidebar() {
  collapsed.value = !collapsed.value
  localStorage.setItem('sidebar-collapsed', collapsed.value.toString())
}

function toggleDark() {
  isDark.value = !isDark.value
  const html = document.documentElement
  html.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

function logoutUser() {
  logout()
  dropdownOpen.value = false
}

onMounted(() => {
  // Sidebar memory
  collapsed.value = localStorage.getItem('sidebar-collapsed') === 'true'

  // Dark mode memory
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else if (savedTheme === 'light') {
    isDark.value = false
    document.documentElement.classList.remove('dark')
  } else {
    // Use system preference if none saved
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    document.documentElement.classList.toggle('dark', isDark.value)
  }
})

watch(collapsed, (val) => {
  if (sidebarEl.value) {
    gsap.to(sidebarEl.value, {
      width: val ? '5rem' : '16rem',
      duration: 0.3,
      ease: 'power2.out'
    })
  }
})
</script>
