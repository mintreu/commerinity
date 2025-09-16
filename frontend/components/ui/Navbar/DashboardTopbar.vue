<script setup lang="ts">
import DarkModeToggle from "~/components/ui/DarkModeToggle.vue"
import UserDropdown from "~/components/ui/UserDropdown.vue"
const config = useRuntimeConfig()
const companyName = config.public.companyName
defineProps<{ collapsed: boolean }>()
const emit = defineEmits(['toggle-sidebar'])

function toggleSidebar() { emit('toggle-sidebar') }
</script>

<template>
  <header class="h-16 pr-4 pl-3 dark:text-white md:pr-6 flex items-center justify-between border-b bg-white dark:bg-gray-900 dark:border-gray-800">
    <div class="h-16 flex items-center justify-between pr-2 border-b border-gray-200 dark:border-gray-800">
      <NuxtLink
          to="/"
          class="flex items-center gap-3 select-none"
          :class="{ 'justify-center': collapsed }"
          aria-label="Homepage"
      >
        <NuxtImg src="/logo.png" loading="lazy" class="object-contain" :class="collapsed ? 'w-8 h-8' : 'w-14 h-14'" alt="Commernity" />
        <span v-if="!collapsed" class="text-primary-600 dark:text-primary-400 font-extrabold text-xl tracking-wide">
          {{ companyName }}
        </span>
      </NuxtLink>

      <!-- Sidebar Toggle -->
      <button
          @click="toggleSidebar"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          :class="[
          'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors rounded',
          collapsed ? 'p-1 w-8 h-8 flex items-center justify-center' : 'p-2',
        ]"
          type="button"
      >
        <Icon
            :name="collapsed ? 'mdi:chevron-right' : 'mdi:chevron-left'"
            :style="{ fontSize: (collapsed ? 20 : 26) + 'px' }"
            aria-hidden="true"
        />
      </button>
    </div>

    <div class="flex-1"></div>

    <div class="flex items-center gap-4">
      <DarkModeToggle />
      <UserDropdown />
    </div>
  </header>
</template>
