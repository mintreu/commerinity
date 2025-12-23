<script setup lang="ts">
/**
 * QuickActions - Action buttons grid for common tasks
 * Dynamically shows actions based on user type and permissions
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
    3: 'grid-cols-2 md:grid-cols-3',
    4: 'grid-cols-2 md:grid-cols-4'
  }
  return cols[props.columns]
})

const colorClasses = (color: QuickAction['color']) => {
  const colors = {
    primary: {
      bg: 'bg-blue-50 dark:bg-blue-900/20',
      border: 'border-blue-100 dark:border-blue-800',
      icon: 'text-blue-600 dark:text-blue-400',
      hover: 'hover:bg-blue-100 dark:hover:bg-blue-900/30'
    },
    success: {
      bg: 'bg-emerald-50 dark:bg-emerald-900/20',
      border: 'border-emerald-100 dark:border-emerald-800',
      icon: 'text-emerald-600 dark:text-emerald-400',
      hover: 'hover:bg-emerald-100 dark:hover:bg-emerald-900/30'
    },
    warning: {
      bg: 'bg-amber-50 dark:bg-amber-900/20',
      border: 'border-amber-100 dark:border-amber-800',
      icon: 'text-amber-600 dark:text-amber-400',
      hover: 'hover:bg-amber-100 dark:hover:bg-amber-900/30'
    },
    purple: {
      bg: 'bg-purple-50 dark:bg-purple-900/20',
      border: 'border-purple-100 dark:border-purple-800',
      icon: 'text-purple-600 dark:text-purple-400',
      hover: 'hover:bg-purple-100 dark:hover:bg-purple-900/30'
    },
    amber: {
      bg: 'bg-orange-50 dark:bg-orange-900/20',
      border: 'border-orange-100 dark:border-orange-800',
      icon: 'text-orange-600 dark:text-orange-400',
      hover: 'hover:bg-orange-100 dark:hover:bg-orange-900/30'
    }
  }
  return colors[color]
}
</script>

<template>
  <div>
    <!-- Section Title -->
    <h2
      v-if="title"
      class="text-lg font-semibold text-slate-900 dark:text-white mb-4"
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
          'relative flex flex-col items-center justify-center p-4 rounded-2xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg cursor-pointer',
          colorClasses(action.color).bg,
          colorClasses(action.color).border,
          colorClasses(action.color).hover
        ]"
        @click="action.onClick ? action.onClick() : null"
      >
        <!-- Badge -->
        <span
          v-if="action.badge"
          class="absolute -top-1 -right-1 px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full"
        >
          {{ action.badge }}
        </span>

        <!-- Icon -->
        <div
          :class="[
            'w-10 h-10 rounded-xl flex items-center justify-center mb-2',
            colorClasses(action.color).bg
          ]"
        >
          <UIcon
            :name="action.icon"
            :class="['w-5 h-5', colorClasses(action.color).icon]"
          />
        </div>

        <!-- Label -->
        <span class="text-sm font-medium text-slate-700 dark:text-slate-200 text-center">
          {{ action.label }}
        </span>

        <!-- Description -->
        <span
          v-if="action.description"
          class="text-xs text-slate-500 dark:text-slate-400 text-center mt-0.5"
        >
          {{ action.description }}
        </span>
      </component>
    </div>
  </div>
</template>
