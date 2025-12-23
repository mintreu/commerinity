<script setup lang="ts">
/**
 * StatCard - Reusable statistics card component
 * Used across all dashboard types to show key metrics
 */

interface Props {
  title: string
  value: string | number
  subtitle?: string
  icon?: string
  trend?: {
    value: number
    label?: string
    isPositive?: boolean
  }
  color?: 'primary' | 'success' | 'warning' | 'danger' | 'purple' | 'amber'
  loading?: boolean
  to?: string
}

const props = withDefaults(defineProps<Props>(), {
  color: 'primary',
  loading: false
})

const colorClasses = computed(() => {
  const colors = {
    primary: {
      icon: 'bg-gradient-to-br from-blue-500 to-indigo-600',
      trend: 'text-blue-600 dark:text-blue-400'
    },
    success: {
      icon: 'bg-gradient-to-br from-emerald-500 to-green-600',
      trend: 'text-emerald-600 dark:text-emerald-400'
    },
    warning: {
      icon: 'bg-gradient-to-br from-amber-500 to-orange-600',
      trend: 'text-amber-600 dark:text-amber-400'
    },
    danger: {
      icon: 'bg-gradient-to-br from-red-500 to-rose-600',
      trend: 'text-red-600 dark:text-red-400'
    },
    purple: {
      icon: 'bg-gradient-to-br from-purple-500 to-pink-600',
      trend: 'text-purple-600 dark:text-purple-400'
    },
    amber: {
      icon: 'bg-gradient-to-br from-amber-500 to-yellow-600',
      trend: 'text-amber-600 dark:text-amber-400'
    }
  }
  return colors[props.color]
})

const trendIcon = computed(() => {
  if (!props.trend) return null
  return props.trend.isPositive !== false && props.trend.value >= 0
    ? 'i-lucide-trending-up'
    : 'i-lucide-trending-down'
})

const trendColorClass = computed(() => {
  if (!props.trend) return ''
  return props.trend.isPositive !== false && props.trend.value >= 0
    ? 'text-emerald-600 dark:text-emerald-400'
    : 'text-red-600 dark:text-red-400'
})
</script>

<template>
  <component
    :is="to ? 'NuxtLink' : 'div'"
    :to="to"
    class="stat-card group"
    :class="{ 'cursor-pointer': to }"
  >
    <!-- Loading State -->
    <div
      v-if="loading"
      class="animate-pulse"
    >
      <div class="flex items-start justify-between">
        <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-2xl" />
        <div class="w-16 h-4 bg-slate-200 dark:bg-slate-700 rounded" />
      </div>
      <div class="mt-4 space-y-2">
        <div class="w-24 h-8 bg-slate-200 dark:bg-slate-700 rounded" />
        <div class="w-32 h-4 bg-slate-200 dark:bg-slate-700 rounded" />
      </div>
    </div>

    <!-- Content -->
    <template v-else>
      <div class="flex items-start justify-between">
        <!-- Icon -->
        <div
          v-if="icon"
          :class="[
            'w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0',
            colorClasses.icon
          ]"
        >
          <UIcon
            :name="icon"
            class="w-6 h-6 text-white"
          />
        </div>

        <!-- Trend Badge -->
        <div
          v-if="trend"
          :class="[
            'flex items-center gap-1 text-sm font-medium',
            trendColorClass
          ]"
        >
          <UIcon
            :name="trendIcon!"
            class="w-4 h-4"
          />
          <span>{{ trend.value >= 0 ? '+' : '' }}{{ trend.value }}%</span>
        </div>
      </div>

      <!-- Value & Title -->
      <div class="mt-4">
        <p class="text-3xl font-bold text-slate-900 dark:text-white">
          {{ value }}
        </p>
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mt-1">
          {{ title }}
        </p>
        <p
          v-if="subtitle || trend?.label"
          class="text-xs text-slate-500 dark:text-slate-500 mt-1"
        >
          {{ subtitle || trend?.label }}
        </p>
      </div>

      <!-- Link Indicator -->
      <div
        v-if="to"
        class="mt-3 flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <span>View details</span>
        <UIcon
          name="i-lucide-arrow-right"
          class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"
        />
      </div>
    </template>
  </component>
</template>
