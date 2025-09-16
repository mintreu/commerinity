<template>
  <div>
    <!-- Topbar (Desktop Only) -->
    <header
        class="hidden md:flex fixed top-0 inset-x-0 z-40 items-center justify-between px-6 py-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-md transition-colors duration-300"
        style="height: 4rem;"
    >
      <!-- Logo -->
      <NuxtLink
          to="/"
          class="flex items-center gap-3 select-none"
          aria-label="Homepage"
      >
        <NuxtImg src="/logo.png" loading="lazy" class="object-contain w-8 h-8" alt="Commernity" />
        <span class="text-primary-600 dark:text-primary-400 font-extrabold text-xl tracking-wide">
          {{ websiteName }}
        </span>
      </NuxtLink>

      <!-- Navigation Links -->
      <div class="flex items-center gap-6 ml-auto">
        <template v-for="link in navLinks" :key="link.to">
          <NuxtLink
              :to="link.to"
              class="flex items-center gap-2 hover:text-blue-600 dark:hover:text-blue-400 transition"
          >
            <Icon :name="link.icon" class="w-5 h-5" />
            {{ link.label }}
          </NuxtLink>
        </template>

        <DarkModeToggle />
        <UserDropdown v-if="isLoggedIn" />
        <template v-else>
          <NuxtLink to="/auth/login" class="flex items-center gap-1 hover:underline">
            <Icon name="heroicons-outline:login" class="w-5 h-5" />
            Login
          </NuxtLink>
        </template>
      </div>
    </header>

    <!-- Mobile Topbar -->
    <div class="w-full md:hidden fixed top-0 z-50 bg-white dark:bg-gray-900 py-2 flex items-center gap-2 px-4 shadow-md dark:text-white">
      <button
          class="px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition"
          @click="sidebarOpen = true"
          aria-label="Open Menu"
      >
        <Icon name="bi:list" class="w-6 h-6" />
      </button>

      <!-- Logo -->
      <NuxtLink
          to="/"
          class="flex items-center gap-3 select-none ml-2"
          aria-label="Homepage"
      >
        <NuxtImg src="/logo.png" loading="lazy" class="object-contain w-8 h-8" alt="Commernity" />
        <span class="text-primary-600 dark:text-primary-400 font-extrabold text-xl tracking-wide ">
          Commernity
        </span>
      </NuxtLink>
    </div>

    <!-- Mobile Sidebar -->
    <transition name="slide">
      <aside
          v-show="sidebarOpen"
          class="fixed top-0 left-0 h-full w-4/5 max-w-xs bg-white dark:bg-gray-800 dark:text-white shadow-lg z-50 pt-4 flex flex-col justify-between transform transition-transform duration-300 ease-in-out"
      >
        <!-- Sidebar Header -->
        <div class="px-6 pb-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <NuxtLink
              to="/"
              class="flex items-center gap-3 select-none"
              aria-label="Homepage"
          >
            <NuxtImg src="/logo.png" loading="lazy" class="object-contain w-8 h-8" alt="Commernity" />
            <span class="text-primary-600 dark:text-primary-400 font-extrabold text-xl tracking-wide">
              Commernity
            </span>
          </NuxtLink>
          <DarkModeToggle />
        </div>

        <!-- Sidebar Links -->
        <nav class="px-6 py-4 space-y-4 flex-1 overflow-auto">
          <template v-for="link in navLinks" :key="link.to">
            <NuxtLink
                :to="link.to"
                class="flex items-center gap-2 hover:underline"
                @click="sidebarOpen = false"
            >
              <Icon :name="link.icon" class="w-5 h-5" />
              {{ link.label }}
            </NuxtLink>
          </template>

          <NuxtLink
              v-if="isLoggedIn"
              to="/dashboard"
              class="flex items-center gap-2 hover:underline"
              @click="sidebarOpen = false"
          >
            <Icon name="heroicons-outline:chart-bar" class="w-5 h-5" />
            Dashboard
          </NuxtLink>

          <NuxtLink
              v-else
              to="/auth/login"
              class="flex items-center gap-2 hover:underline"
              @click="sidebarOpen = false"
          >
            <Icon name="heroicons-outline:login" class="w-5 h-5" />
            Login
          </NuxtLink>
        </nav>

        <!-- User Info (Bottom) -->
        <div
            v-if="user"
            class="border-t border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3"
        >
          <NuxtImg :src="user.avatar" loading="lazy" alt="avatar" class="w-10 h-10 rounded-full" />
          <div class="flex flex-col">
            <span class="font-medium">{{ user.name }}</span>
            <button
                @click.prevent="logoutUser"
                class="text-sm text-red-500 hover:text-red-700 mt-1 flex items-center gap-1"
            >
              <Icon name="heroicons-outline:logout" class="w-4 h-4 inline" />
              Logout
            </button>
          </div>
        </div>
      </aside>
    </transition>

    <!-- Overlay -->
    <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-40 md:hidden z-40"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useSanctum } from '#imports'
import DarkModeToggle from '~/components/ui/DarkModeToggle.vue'
import UserDropdown from '~/components/ui/UserDropdown.vue'
const config = useRuntimeConfig()
const websiteName = config.public.websiteName
interface User {
  name: string
  email: string
  avatar: string
  type: string
  role: string
}

const { isLoggedIn, user, logout } = useSanctum<User>()
const sidebarOpen = ref(false)

const baseLinks = [
  { to: '/', label: 'Home', icon: 'heroicons-outline:home' },
  { to: '/store', label: 'Store', icon: 'heroicons-outline:shopping-bag' },
  { to: '/blog', label: 'Blog', icon: 'heroicons-outline:document-text' }
]

const navLinks = computed(() => {
  const links = [...baseLinks]

  if (isLoggedIn.value && user.value) {
    if (user.value.type === 'applicant') {
      links.push({ to: '/career', label: 'My Career', icon: 'heroicons-outline:briefcase' })
    }
    if (user.value.role === 'admin') {
      links.push({ to: '/admin', label: 'Admin Panel', icon: 'heroicons-outline:shield-check' })
    }
    if (user.value.role === 'recruiter') {
      links.push({ to: '/recruiter/jobs', label: 'My Listings', icon: 'heroicons-outline:clipboard-list' })
    }
  }

  return links
})

function logoutUser() {
  logout()
  sidebarOpen.value = false
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>
