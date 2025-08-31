<template>
  <!-- Mobile Bottom Navigation -->
  <nav v-if="isLoggedIn" class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex justify-around items-center h-16 md:hidden z-50 shadow-lg">
    <button
        v-for="tab in tabs"
        :key="tab.name"
        @click="navigate(tab.to)"
        class="flex flex-col items-center justify-center text-center flex-1 transition-colors"
        :class="activeTab === tab.to ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
    >
      <div class="relative">
        <Icon :name="tab.icon" class="w-6 h-6" />
        <span
            v-if="tab.badge"
            class="absolute -top-1 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center"
        >
          {{ tab.badge }}
        </span>
      </div>
      <span class="text-xs mt-1">{{ tab.label }}</span>
    </button>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useSanctum } from '#imports'

const { user } = useSanctum()
const router = useRouter()
const route = useRoute()
const { isLoggedIn } = useSanctum()

const activeTab = computed(() => {
  const path = route.path
  if (path.startsWith('/dashboard/orders') || path.startsWith('/cart')) return '/dashboard/orders'
  if (path.startsWith('/category')) return '/category'
  if (path.startsWith('/dashboard/account')) return '/dashboard/account'
  return '/dashboard'
})

const tabs = computed(() => {
  const t = [
    { name: 'home', to: '/dashboard', label: 'Home', icon: 'mdi:view-dashboard-outline' },
    { name: 'shop', to: '/category', label: 'Shop', icon: 'mdi:cart-outline' },
    { name: 'orders', to: '/dashboard/orders', label: 'Orders', icon: 'mdi:clipboard-list-outline' },
    { name: 'account', to: '/dashboard/account', label: 'Account', icon: 'mdi:account-outline' },
  ]

  if (user.value?.status?.toLowerCase() === 'draft') {
    t.push({ name: 'subscribe', to: '/dashboard/subscribe-now', label: 'Subscribe', icon: 'mdi:star-outline', badge: 'New' })
  }

  return t
})

function navigate(to: string) {
  router.push(to).catch((err) => {
    if (err.name !== 'NavigationDuplicated') console.error(err)
  })
}
</script>
