<template>
  <div ref="dropdownRef" v-if="user" class="relative">
    <!-- User button -->
    <button
        @click="toggleDropdown"
        class="flex items-center gap-2 px-2 py-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition"
    >

      <img :src="user.avatar" loading="lazy" class="w-8 h-8 rounded-full" :alt="user.name" />
      <span class="hidden sm:block font-medium text-sm text-gray-800 dark:text-white">
        {{ user.name }}
      </span>
      <Icon name="mdi:chevron-down" class="w-4 h-4 text-gray-600 dark:text-gray-300" />
    </button>

    <!-- Dropdown Menu -->
    <div
        v-if="dropdownOpen"
        class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-black/5 z-50 overflow-hidden"
    >
      <!-- Back to Home / Dashboard -->
      <NuxtLink
          v-if="isDashboard"
          to="/"
          class="dropdown-item"
      >
        <Icon name="mdi:home-outline" class="w-4 h-4" />
        <span>Back to Home</span>
      </NuxtLink>
      <NuxtLink
          v-else
          to="/dashboard"
          class="dropdown-item"
      >
        <Icon name="mdi:view-dashboard-outline" class="w-4 h-4" />
        <span>Back to Dashboard</span>
      </NuxtLink>

      <!-- Profile -->
      <NuxtLink
          to="/dashboard/account"
          class="dropdown-item"
      >
        <Icon name="mdi:account-outline" class="w-4 h-4" />
        <span>Profile</span>
      </NuxtLink>

      <!-- Divider -->
      <div class="border-t border-gray-200 dark:border-gray-700 my-1" />

      <!-- Logout -->
      <button
          @click="logoutUser"
          class="dropdown-item text-red-600 hover:bg-red-50 dark:hover:bg-red-900/40"
      >
        <Icon name="mdi:logout" class="w-4 h-4" />
        <span>Logout</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctum } from '#imports'

interface User {
  name: string
  email: string
  avatar: string
}

const { user, logout } = useSanctum<User>()
const route = useRoute()

const dropdownOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value
}

function logoutUser() {
  logout()
  dropdownOpen.value = false
}

// ✅ Detect click outside to close dropdown
function handleClickOutside(event: MouseEvent) {
  if (
      dropdownRef.value &&
      !dropdownRef.value.contains(event.target as Node)
  ) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const isDashboard = computed(() => route.path.startsWith('/dashboard'))
</script>

<style scoped>
.dropdown-item {
  @apply flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors w-full text-left;
}
</style>
