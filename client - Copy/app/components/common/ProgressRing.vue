<script setup lang="ts">
/**
 * ProgressRing - Circular progress indicator
 * Used for onboarding progress, goals, and level tracking
 */

interface Props {
  progress: number // 0-100
  size?: 'sm' | 'md' | 'lg' | 'xl'
  strokeWidth?: number
  color?: 'primary' | 'success' | 'warning' | 'danger'
  showPercentage?: boolean
  label?: string
  sublabel?: string
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  strokeWidth: 4,
  color: 'primary',
  showPercentage: true
})

const sizeConfig = computed(() => {
  const sizes = {
    sm: { width: 48, radius: 20, text: 'text-xs', label: 'text-[8px]' },
    md: { width: 64, radius: 26, text: 'text-sm', label: 'text-[10px]' },
    lg: { width: 80, radius: 34, text: 'text-lg', label: 'text-xs' },
    xl: { width: 120, radius: 52, text: 'text-2xl', label: 'text-sm' }
  }
  return sizes[props.size]
})

const colorClasses = computed(() => {
  const colors = {
    primary: 'stroke-blue-600',
    success: 'stroke-emerald-600',
    warning: 'stroke-amber-500',
    danger: 'stroke-red-600'
  }
  return colors[props.color]
})

const circumference = computed(() => 2 * Math.PI * sizeConfig.value.radius)
const dashOffset = computed(() => circumference.value - (props.progress / 100) * circumference.value)
</script>

<template>
  <div
    class="relative inline-flex items-center justify-center"
    :style="{ width: `${sizeConfig.width}px`, height: `${sizeConfig.width}px` }"
  >
    <!-- SVG Ring -->
    <svg
      class="w-full h-full -rotate-90"
      :viewBox="`0 0 ${sizeConfig.width} ${sizeConfig.width}`"
    >
      <!-- Background Circle -->
      <circle
        :cx="sizeConfig.width / 2"
        :cy="sizeConfig.width / 2"
        :r="sizeConfig.radius"
        class="stroke-slate-200 dark:stroke-slate-700"
        :stroke-width="strokeWidth"
        fill="none"
      />

      <!-- Progress Circle -->
      <circle
        :cx="sizeConfig.width / 2"
        :cy="sizeConfig.width / 2"
        :r="sizeConfig.radius"
        :class="colorClasses"
        :stroke-width="strokeWidth"
        fill="none"
        stroke-linecap="round"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="dashOffset"
        class="transition-all duration-700 ease-out"
      />
    </svg>

    <!-- Center Content -->
    <div class="absolute inset-0 flex flex-col items-center justify-center">
      <span
        v-if="showPercentage"
        :class="[sizeConfig.text, 'font-bold text-slate-900 dark:text-white']"
      >
        {{ Math.round(progress) }}%
      </span>
      <span
        v-if="label"
        :class="[sizeConfig.label, 'font-medium text-slate-600 dark:text-slate-400 leading-tight text-center']"
      >
        {{ label }}
      </span>
      <span
        v-if="sublabel"
        :class="[sizeConfig.label, 'text-slate-500 dark:text-slate-500']"
      >
        {{ sublabel }}
      </span>
    </div>
  </div>
</template>
