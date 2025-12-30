<script setup lang="ts">
/**
 * StatCard - Reusable statistics card component
 * Used across all dashboard types to show key metrics
 * Optimized for PWA/mobile with compact design
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
  compact?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  color: 'primary',
  loading: false,
  compact: false
})

const colorClasses = computed(() => {
  const colors = {
    primary: {
      icon: 'bg-gradient-to-br from-blue-500 to-indigo-600',
      iconSoft: 'bg-blue-100 dark:bg-blue-900/30',
      trend: 'text-blue-600 dark:text-blue-400',
      accent: 'text-blue-600 dark:text-blue-400'
    },
    success: {
      icon: 'bg-gradient-to-br from-emerald-500 to-green-600',
      iconSoft: 'bg-emerald-100 dark:bg-emerald-900/30',
      trend: 'text-emerald-600 dark:text-emerald-400',
      accent: 'text-emerald-600 dark:text-emerald-400'
    },
    warning: {
      icon: 'bg-gradient-to-br from-amber-500 to-orange-600',
      iconSoft: 'bg-amber-100 dark:bg-amber-900/30',
      trend: 'text-amber-600 dark:text-amber-400',
      accent: 'text-amber-600 dark:text-amber-400'
    },
    danger: {
      icon: 'bg-gradient-to-br from-red-500 to-rose-600',
      iconSoft: 'bg-red-100 dark:bg-red-900/30',
      trend: 'text-red-600 dark:text-red-400',
      accent: 'text-red-600 dark:text-red-400'
    },
    purple: {
      icon: 'bg-gradient-to-br from-purple-500 to-pink-600',
      iconSoft: 'bg-purple-100 dark:bg-purple-900/30',
      trend: 'text-purple-600 dark:text-purple-400',
      accent: 'text-purple-600 dark:text-purple-400'
    },
    amber: {
      icon: 'bg-gradient-to-br from-amber-500 to-yellow-600',
      iconSoft: 'bg-amber-100 dark:bg-amber-900/30',
      trend: 'text-amber-600 dark:text-amber-400',
      accent: 'text-amber-600 dark:text-amber-400'
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
    :class="[
      'stat-card group relative overflow-hidden',
      to ? 'cursor-pointer active:scale-[0.98] transition-transform' : ''
    ]"
  >
    <!-- Loading State -->
    <div v-if="loading" class="animate-pulse">
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 md:w-12 md:h-12 bg-slate-200 dark:bg-slate-700 rounded-xl" />
        <div class="flex-1 space-y-2">
          <div class="w-16 h-3 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="w-24 h-6 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Compact Layout (Mobile-first) -->
      <div class="flex items-center gap-3">
        <!-- Icon -->
        <div
          v-if="icon"
          :class="[
            'w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center shadow-lg shrink-0',
            colorClasses.icon
          ]"
        >
          <UIcon :name="icon" class="w-5 h-5 md:w-6 md:h-6 text-white" />
        </div>

        <!-- Value & Title -->
        <div class="flex-1 min-w-0">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate">
            {{ title }}
          </p>
          <p class="text-lg md:text-2xl font-bold text-slate-900 dark:text-white truncate">
            {{ value }}
          </p>
        </div>

        <!-- Trend Badge (Desktop) -->
        <div
          v-if="trend"
          :class="[
            'hidden md:flex items-center gap-1 text-sm font-medium px-2 py-1 rounded-lg',
            trendColorClass,
            trend.isPositive !== false && trend.value >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'bg-red-50 dark:bg-red-900/30'
          ]"
        >
          <UIcon :name="trendIcon!" class="w-3.5 h-3.5" />
          <span class="text-xs">{{ trend.value >= 0 ? '+' : '' }}{{ trend.value }}%</span>
        </div>

        <!-- Link Indicator -->
        <UIcon
          v-if="to"
          name="i-lucide-chevron-right"
          class="w-4 h-4 text-slate-400 shrink-0 group-hover:translate-x-0.5 transition-transform"
        />
      </div>

      <!-- Subtitle / Trend Label (Mobile) -->
      <div v-if="subtitle || trend?.label" class="mt-2 flex items-center justify-between">
        <p class="text-xs text-slate-500 dark:text-slate-500 truncate">
          {{ subtitle || trend?.label }}
        </p>
        <!-- Mobile Trend -->
        <div
          v-if="trend"
          :class="[
            'md:hidden flex items-center gap-1 text-xs font-medium',
            trendColorClass
          ]"
        >
          <UIcon :name="trendIcon!" class="w-3 h-3" />
          <span>{{ trend.value >= 0 ? '+' : '' }}{{ trend.value }}%</span>
        </div>
      </div>
    </template>
  </component>
</template>

<style scoped>
.stat-card {
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(226, 232, 240, 0.6);
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.dark .stat-card {
  background-color: rgba(30, 41, 59, 0.95);
  border-color: rgba(51, 65, 85, 0.6);
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

@media (min-width: 768px) {
  .stat-card {
    padding: 1.25rem;
  }
}
</style>
