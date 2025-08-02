<template>
  <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Fixed Header -->
    <header
        class="hidden md:flex fixed top-0 inset-x-0 z-40 items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-md transition-colors duration-300"
        style="height: 4rem;"
    >
      <!-- Logo -->
      <div class="text-lg font-semibold">
        <NuxtLink to="/" class="gap-2 flex flex-row items-center">
          <img src="/logo.png" class="w-6 h-6">
          Commernity</NuxtLink>
      </div>

      <!-- Nav & Actions -->
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

        <template v-if="isLoggedIn">
          <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 focus:outline-none">
              <img v-if="user" :src="user.avatar" alt="avatar" class="w-8 h-8 rounded-full" />
            </button>
            <div v-if="dropdownOpen" class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-700 shadow-lg rounded-md overflow-hidden z-50">
              <NuxtLink to="/dashboard/auth/profile" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Profile</NuxtLink>
              <NuxtLink to="/dashboard" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Dashboard</NuxtLink>
              <button @click.prevent="logoutUser" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Logout</button>
            </div>
          </div>
        </template>

        <template v-else>
          <NuxtLink to="/auth/login" class="flex items-center gap-1 hover:underline">
            <Icon name="heroicons-outline:login" class="w-5 h-5" />
            Login
          </NuxtLink>
<!--          <NuxtLink to="/auth/register" class="flex items-center gap-1 hover:underline">-->
<!--            <Icon name="heroicons-outline:user-add" class="w-5 h-5" />-->
<!--            Register-->
<!--          </NuxtLink>-->
        </template>

        <!-- Dark Mode Toggle -->
        <button
            @click="toggleDark"
            class="transition hover:text-yellow-500 dark:hover:text-yellow-300"
            aria-label="Toggle Dark Mode"
            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
          <Icon v-if="!isDark" name="bi:moon-fill" width="20" height="20" />
          <Icon v-else name="bi:brightness-high-fill" width="20" height="20" />
        </button>
      </div>
    </header>

    <!-- Mobile Sidebar Toggle -->
    <button
        class="fixed top-4 left-4 z-50 p-2 bg-white dark:bg-gray-800 rounded-full shadow-md md:hidden"
        @click="sidebarOpen = true"
    >
      <Icon name="bi:list" class="w-6 h-6 text-gray-800 dark:text-white" />
    </button>

    <!-- Sidebar -->
    <transition name="slide">
      <aside
          v-show="sidebarOpen"
          class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-800 dark:text-white shadow-lg z-50 pt-4 flex flex-col justify-between"
      >
        <div class="px-6 pb-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <NuxtLink to="/" class="flex items-center gap-2 text-xl font-semibold">
            <Icon name="bi:bootstrap" width="28" height="28" />
            <span>Commernity</span>
          </NuxtLink>
          <div class="relative group">
            <button
                @click="toggleDark"
                class="hover:text-yellow-500 dark:hover:text-yellow-300"
                aria-label="Toggle Dark Mode"
            >
              <Icon v-if="!isDark" name="bi:moon-fill" width="22" height="22" />
              <Icon v-else name="bi:brightness-high-fill" width="22" height="22" />
            </button>
            <div class="absolute right-0 top-full mt-1 px-2 py-1 text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-white text-gray-800 dark:bg-gray-800 dark:text-white border border-gray-200 dark:border-gray-700">
              {{ isDark ? 'Disable Dark Mode' : 'Enable Dark Mode' }}
            </div>
          </div>
        </div>

        <nav class="p-4 space-y-4 flex-1 overflow-auto">
          <NuxtLink to="/" class="flex items-center gap-2 hover:underline" @click="sidebarOpen = false">
            <Icon name="heroicons-outline:home" class="w-5 h-5" /> Home
          </NuxtLink>
          <NuxtLink to="/store" class="flex items-center gap-2 hover:underline" @click="sidebarOpen = false">
            <Icon name="heroicons-outline:shopping-bag" class="w-5 h-5" /> Store
          </NuxtLink>
          <NuxtLink to="/blog" class="flex items-center gap-2 hover:underline" @click="sidebarOpen = false">
            <Icon name="heroicons-outline:document-text" class="w-5 h-5" /> Blog
          </NuxtLink>

          <NuxtLink v-if="isLoggedIn" to="/dashboard" class="flex items-center gap-2 hover:underline" @click="sidebarOpen = false">
            <Icon name="heroicons-outline:chart-bar" class="w-5 h-5" /> Dashboard
          </NuxtLink>
          <NuxtLink v-else to="/auth/login" class="flex items-center gap-2 hover:underline" @click="sidebarOpen = false">
            <Icon name="heroicons-outline:login" class="w-5 h-5" /> Login
          </NuxtLink>
        </nav>

        <!-- User Info Bottom -->
        <div v-if="user" class="border-t border-gray-200 dark:border-gray-700 p-4 flex items-center gap-3">
          <img :src="user.avatar" alt="avatar" class="w-10 h-10 rounded-full" />
          <div class="flex flex-col">
            <span class="font-medium">{{ user.name }}</span>
            <button
                @click.prevent="logoutUser"
                class="text-sm text-red-500 hover:text-red-700 mt-1"
            >
              <Icon name="heroicons-outline:logout" class="w-4 h-4 inline mr-1" />
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

    <!-- Content -->
    <div class="flex flex-col flex-1 pt-16 overflow-auto dark:text-white" style="min-height: calc(100vh - 4rem);">
      <main class="flex-1">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="bg-gray-200 dark:bg-gray-900 text-gray-800 dark:text-gray-200 py-10 px-6 md:px-16 mt-12 transition-colors">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 text-sm">

          <!-- Brand and Social Icons -->
          <div>
            <h3 class="text-lg font-bold mb-2">Commernity</h3>
            <p class="mb-4">Bringing everything you love under one roof.</p>

            <!-- Social Icons Row -->
            <div class="flex items-center space-x-4 mt-4">
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 transition">
                <Icon name="uil:facebook-f" class="w-5 h-5" />
              </a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-pink-500 transition">
                <Icon name="uil:instagram" class="w-5 h-5" />
              </a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-400 transition">
                <Icon name="uil:twitter" class="w-5 h-5" />
              </a>
              <a href="#" class="text-gray-600 dark:text-gray-400 hover:text-blue-700 transition">
                <Icon name="uil:linkedin" class="w-5 h-5" />
              </a>
            </div>
          </div>

          <!-- Categories -->
          <div>
            <h3 class="font-bold mb-2">Categories</h3>
            <ul class="space-y-1">
              <li><a href="#" class="hover:underline">Electronics</a></li>
              <li><a href="#" class="hover:underline">Fashion</a></li>
              <li><a href="#" class="hover:underline">Home</a></li>
              <li><a href="#" class="hover:underline">Beauty</a></li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h3 class="font-bold mb-2">Support</h3>
            <ul class="space-y-1">
              <li><a href="#" class="hover:underline">Help Center</a></li>
              <li><a href="#" class="hover:underline">Returns</a></li>
              <li><a href="#" class="hover:underline">Shipping</a></li>
              <li><NuxtLink to="/contact" class="hover:underline">Contact Us</NuxtLink></li>
            </ul>
          </div>

          <!-- Legal & Info -->
          <div>
            <h3 class="font-bold mb-2">Information</h3>
            <ul class="space-y-1">
              <li><NuxtLink to="/about" class="hover:underline">About Us</NuxtLink></li>
              <li><NuxtLink to="/privacy" class="hover:underline">Privacy Policy</NuxtLink></li>
              <li><NuxtLink to="/terms" class="hover:underline">Terms & Conditions</NuxtLink></li>
              <li><NuxtLink to="/career" class="hover:underline">Careers</NuxtLink></li>

            </ul>
          </div>
        </div>

        <!-- Bottom copyright -->
        <div class="text-center mt-10 text-xs text-gray-500 dark:text-gray-400">
          &copy; 2025 Commernity. All rights reserved.
        </div>
      </footer>




    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSanctum } from '#imports'

const sidebarOpen = ref(false)
const dropdownOpen = ref(false)
const isDark = ref(false)

interface User {
  name: string
  email: string
  avatar: string
}

const { isLoggedIn, user, logout } = useSanctum<User>()

const navLinks = [
  { to: '/', label: 'Home', icon: 'heroicons-outline:home' },
  { to: '/store', label: 'Store', icon: 'heroicons-outline:shopping-bag' },
  { to: '/blog', label: 'Blog', icon: 'heroicons-outline:document-text' }
]

function toggleDark() {
  isDark.value = !isDark.value
  const html = document.documentElement
  html.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

function logoutUser() {
  logout()
  dropdownOpen.value = false
  sidebarOpen.value = false
}

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved === 'dark') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  } else if (saved === 'light') {
    isDark.value = false
    document.documentElement.classList.remove('dark')
  } else {
    // use system preference if no saved value
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    document.documentElement.classList.toggle('dark', isDark.value)
  }
})
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
