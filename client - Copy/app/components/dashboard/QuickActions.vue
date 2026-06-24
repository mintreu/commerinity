<script setup lang="ts">
/**
 * QuickActions - Action buttons grid for common tasks
 * Dynamically shows actions based on user type and permissions
 * PWA-optimized with touch-friendly design
 */
import { NuxtLink } from '#components'

interface QuickAction {
  label: string
  icon: string
  to?: string
  onClick?: () => void
  color: 'primary' | 'success' | 'warning' | 'purple' | 'amber'
  description?: string
  badge?: string | number
}

interface Props {
  actions: QuickAction[]
  title?: string
  columns?: 2 | 3 | 4
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Quick Actions',
  columns: 4
})

const gridCols = computed(() => {
  const cols = {
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4'
  }
  return cols[props.columns]
})

const colorClasses = (color: QuickAction['color']) => {
  const colors = {
    primary: {
      bg: 'bg-blue-50 dark:bg-blue-900/20',
      border: 'border-blue-100 dark:border-blue-800/50',
      icon: 'bg-gradient-to-br from-blue-500 to-indigo-600',
      iconText: 'text-white',
      hover: 'hover:bg-blue-100/80 dark:hover:bg-blue-900/40'
    },
    success: {
      bg: 'bg-emerald-50 dark:bg-emerald-900/20',
      border: 'border-emerald-100 dark:border-emerald-800/50',
      icon: 'bg-gradient-to-br from-emerald-500 to-green-600',
      iconText: 'text-white',
      hover: 'hover:bg-emerald-100/80 dark:hover:bg-emerald-900/40'
    },
    warning: {
      bg: 'bg-amber-50 dark:bg-amber-900/20',
      border: 'border-amber-100 dark:border-amber-800/50',
      icon: 'bg-gradient-to-br from-amber-500 to-orange-600',
      iconText: 'text-white',
      hover: 'hover:bg-amber-100/80 dark:hover:bg-amber-900/40'
    },
    purple: {
      bg: 'bg-purple-50 dark:bg-purple-900/20',
      border: 'border-purple-100 dark:border-purple-800/50',
      icon: 'bg-gradient-to-br from-purple-500 to-pink-600',
      iconText: 'text-white',
      hover: 'hover:bg-purple-100/80 dark:hover:bg-purple-900/40'
    },
    amber: {
      bg: 'bg-orange-50 dark:bg-orange-900/20',
      border: 'border-orange-100 dark:border-orange-800/50',
      icon: 'bg-gradient-to-br from-orange-500 to-amber-600',
      iconText: 'text-white',
      hover: 'hover:bg-orange-100/80 dark:hover:bg-orange-900/40'
    }
  }
  return colors[color]
}
</script>

<template>
  <div class="glass-card p-4 md:p-6">
    <!-- Section Title -->
    <h2
      v-if="title"
      class="text-base font-bold text-slate-900 dark:text-white mb-4"
    >
      {{ title }}
    </h2>

    <!-- Actions Grid -->
    <div :class="['grid gap-3', gridCols]">
      <component
        :is="action.to ? NuxtLink : 'button'"
        v-for="action in actions"
        :key="action.label"
        :to="action.to"
        :class="[
          'quick-action relative flex flex-col items-center justify-center p-3 md:p-4 rounded-2xl border transition-all duration-200 active:scale-95',
          colorClasses(action.color).bg,
          colorClasses(action.color).border,
          colorClasses(action.color).hover
        ]"
        @click="action.onClick ? action.onClick() : null"
      >
        <!-- Badge -->
        <span
          v-if="action.badge"
          class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 px-1.5 text-xs font-bold bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg"
        >
          {{ action.badge }}
        </span>

        <!-- Icon -->
        <div
          :class="[
            'w-10 h-10 md:w-11 md:h-11 rounded-xl flex items-center justify-center shadow-md mb-2',
            colorClasses(action.color).icon
          ]"
        >
          <UIcon
            :name="action.icon"
            :class="['w-5 h-5 md:w-6 md:h-6', colorClasses(action.color).iconText]"
          />
        </div>

        <!-- Label -->
        <span class="text-xs md:text-sm font-semibold text-slate-700 dark:text-slate-200 text-center leading-tight">
          {{ action.label }}
        </span>

        <!-- Description -->
        <span
          v-if="action.description"
          class="hidden md:block text-xs text-slate-500 dark:text-slate-400 text-center mt-0.5"
        >
          {{ action.description }}
        </span>
      </component>
    </div>
  </div>
</template>

<style scoped>
.quick-action {
  min-height: 88px;
}

@media (min-width: 768px) {
  .quick-action {
    min-height: 100px;
  }
}
</style>
