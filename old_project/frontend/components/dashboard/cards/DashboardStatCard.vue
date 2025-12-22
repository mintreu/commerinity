<!-- components/dashboard/cards/DashboardStatCard.vue -->
<template>
  <div ref="statCard" class="group relative bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">

    <!-- Top Gradient Border (Dynamic Colors) -->
    <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl bg-gradient-to-r" :class="topBorderGradient"></div>

    <!-- Card Content -->
    <div class="relative z-10 flex items-center gap-4">

      <!-- Icon Container -->
      <div class="flex-shrink-0">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" :class="iconClasses">
          <Icon :name="icon" class="w-6 h-6 text-white" />
        </div>
      </div>

      <!-- Stats Details -->
      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-1 leading-tight">
          {{ label }}
        </h3>

        <div class="text-2xl font-bold leading-none mb-2 tabular-nums" :class="valueClasses">
          <span ref="animatedValue">{{ displayValue }}</span>
        </div>

        <div v-if="change" class="flex items-center gap-1 text-xs font-semibold" :class="changeClasses">
          <Icon :name="changeIcon" class="w-3 h-3" />
          <span>{{ change }}</span>
        </div>
      </div>
    </div>

    <!-- Dots -->
    <div class="absolute top-4 right-4 flex gap-1 opacity-30">
      <div class="w-1 h-1 rounded-full animate-pulse" :class="dotColor" style="animation-delay:0s"></div>
      <div class="w-1 h-1 rounded-full animate-pulse" :class="dotColor" style="animation-delay:.5s"></div>
      <div class="w-1 h-1 rounded-full animate-pulse" :class="dotColor" style="animation-delay:1s"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'

// GSAP import (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props Interface
interface Props {
  icon: string
  label: string
  value: string | number
  change?: string
  trend?: 'up' | 'down' | 'neutral'
  color?: 'blue' | 'green' | 'purple' | 'orange' | 'red' | 'indigo' | 'pink' | 'teal' | 'emerald' | 'cyan'
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  trend: 'neutral',
  color: 'blue',
  loading: false
})

// Refs
const statCard = ref<HTMLElement>()
const animatedValue = ref<HTMLElement>()

// Color System - Comprehensive Tailwind Color Mapping
const colorSystem = computed(() => {
  const colors = {
    blue: {
      icon: 'bg-gradient-to-br from-blue-500 to-blue-600',
      value: 'text-blue-600 dark:text-blue-400',
      border: 'from-blue-500 to-indigo-500',
      dot: 'bg-blue-500'
    },
    green: {
      icon: 'bg-gradient-to-br from-green-500 to-emerald-600',
      value: 'text-green-600 dark:text-green-400',
      border: 'from-green-500 to-emerald-500',
      dot: 'bg-green-500'
    },
    purple: {
      icon: 'bg-gradient-to-br from-purple-500 to-pink-600',
      value: 'text-purple-600 dark:text-purple-400',
      border: 'from-purple-500 to-pink-500',
      dot: 'bg-purple-500'
    },
    orange: {
      icon: 'bg-gradient-to-br from-orange-500 to-red-600',
      value: 'text-orange-600 dark:text-orange-400',
      border: 'from-orange-500 to-red-500',
      dot: 'bg-orange-500'
    },
    red: {
      icon: 'bg-gradient-to-br from-red-500 to-pink-600',
      value: 'text-red-600 dark:text-red-400',
      border: 'from-red-500 to-pink-500',
      dot: 'bg-red-500'
    },
    indigo: {
      icon: 'bg-gradient-to-br from-indigo-500 to-purple-600',
      value: 'text-indigo-600 dark:text-indigo-400',
      border: 'from-indigo-500 to-purple-500',
      dot: 'bg-indigo-500'
    },
    pink: {
      icon: 'bg-gradient-to-br from-pink-500 to-rose-600',
      value: 'text-pink-600 dark:text-pink-400',
      border: 'from-pink-500 to-rose-500',
      dot: 'bg-pink-500'
    },
    teal: {
      icon: 'bg-gradient-to-br from-teal-500 to-cyan-600',
      value: 'text-teal-600 dark:text-teal-400',
      border: 'from-teal-500 to-cyan-500',
      dot: 'bg-teal-500'
    },
    emerald: {
      icon: 'bg-gradient-to-br from-emerald-500 to-green-600',
      value: 'text-emerald-600 dark:text-emerald-400',
      border: 'from-emerald-500 to-green-500',
      dot: 'bg-emerald-500'
    },
    cyan: {
      icon: 'bg-gradient-to-br from-cyan-500 to-blue-600',
      value: 'text-cyan-600 dark:text-cyan-400',
      border: 'from-cyan-500 to-blue-500',
      dot: 'bg-cyan-500'
    }
  }
  return colors[props.color]
})

// Computed Classes
const iconClasses = computed(() => colorSystem.value.icon)
const valueClasses = computed(() => colorSystem.value.value)
const topBorderGradient = computed(() => colorSystem.value.border)
const dotColor = computed(() => colorSystem.value.dot)

// Trend-based Change Classes
const changeClasses = computed(() => {
  const classes = {
    up: 'text-green-600 dark:text-green-400',
    down: 'text-red-600 dark:text-red-400',
    neutral: 'text-slate-600 dark:text-slate-400'
  }
  return classes[props.trend]
})

const changeIcon = computed(() => {
  const icons = {
    up: 'mdi:trending-up',
    down: 'mdi:trending-down',
    neutral: 'mdi:minus'
  }
  return icons[props.trend]
})

// Display value as-is (NO FORMATTING)
const displayValue = computed(() => {
  if (props.loading) return '---'
  return props.value.toString()
})

// Animation Methods
function animateValue() {
  if (!process.client || !gsap || !animatedValue.value || props.loading) return

  const element = animatedValue.value

  gsap.set(element, { scale: 0.8, opacity: 0 })

  gsap.to(element, {
    scale: 1,
    opacity: 1,
    duration: 0.5,
    ease: 'back.out(1.7)'
  })
}

function animateCard() {
  if (!process.client || !gsap || !statCard.value) return

  gsap.set(statCard.value, { y: 20, opacity: 0 })

  gsap.to(statCard.value, {
    y: 0,
    opacity: 1,
    duration: 0.6,
    ease: 'power3.out',
    delay: Math.random() * 0.3
  })
}

// Loading Animation
function showLoadingState() {
  if (!process.client || !gsap || !animatedValue.value) return

  gsap.to(animatedValue.value, {
    opacity: 0.5,
    scale: 0.95,
    duration: 0.3,
    yoyo: true,
    repeat: -1,
    ease: 'power2.inOut'
  })
}

function hideLoadingState() {
  if (!process.client || !gsap || !animatedValue.value) return

  gsap.killTweensOf(animatedValue.value)
  gsap.to(animatedValue.value, {
    opacity: 1,
    scale: 1,
    duration: 0.3,
    ease: 'power2.out'
  })
}

// Watchers
watch(() => props.loading, (isLoading) => {
  if (isLoading) {
    showLoadingState()
  } else {
    hideLoadingState()
    nextTick(() => {
      setTimeout(animateValue, 100)
    })
  }
})

watch(() => props.value, () => {
  if (!props.loading) {
    nextTick(() => {
      setTimeout(animateValue, 50)
    })
  }
})

// Lifecycle
onMounted(() => {
  if (process.client) {
    animateCard()

    if (!props.loading) {
      setTimeout(animateValue, 600)
    } else {
      showLoadingState()
    }
  }
})
</script>

<style scoped>
/* Ensure all color gradients work properly */
.from-blue-500 { --tw-gradient-from: rgb(59 130 246) var(--tw-gradient-from-position); }
.to-indigo-500 { --tw-gradient-to: rgb(99 102 241) var(--tw-gradient-to-position); }

.from-green-500 { --tw-gradient-from: rgb(34 197 94) var(--tw-gradient-from-position); }
.to-emerald-500 { --tw-gradient-to: rgb(16 185 129) var(--tw-gradient-to-position); }

.from-purple-500 { --tw-gradient-from: rgb(168 85 247) var(--tw-gradient-from-position); }
.to-pink-500 { --tw-gradient-to: rgb(236 72 153) var(--tw-gradient-to-position); }

.from-orange-500 { --tw-gradient-from: rgb(249 115 22) var(--tw-gradient-from-position); }
.to-red-500 { --tw-gradient-to: rgb(239 68 68) var(--tw-gradient-to-position); }

.from-red-500 { --tw-gradient-from: rgb(239 68 68) var(--tw-gradient-from-position); }
.to-pink-500 { --tw-gradient-to: rgb(236 72 153) var(--tw-gradient-to-position); }

.from-indigo-500 { --tw-gradient-from: rgb(99 102 241) var(--tw-gradient-from-position); }
.to-purple-500 { --tw-gradient-to: rgb(168 85 247) var(--tw-gradient-to-position); }

.from-pink-500 { --tw-gradient-from: rgb(236 72 153) var(--tw-gradient-from-position); }
.to-rose-500 { --tw-gradient-to: rgb(244 63 94) var(--tw-gradient-to-position); }

.from-teal-500 { --tw-gradient-from: rgb(20 184 166) var(--tw-gradient-from-position); }
.to-cyan-500 { --tw-gradient-to: rgb(6 182 212) var(--tw-gradient-to-position); }

.from-emerald-500 { --tw-gradient-from: rgb(16 185 129) var(--tw-gradient-from-position); }
.to-green-500 { --tw-gradient-to: rgb(34 197 94) var(--tw-gradient-to-position); }

.from-cyan-500 { --tw-gradient-from: rgb(6 182 212) var(--tw-gradient-from-position); }
.to-blue-500 { --tw-gradient-to: rgb(59 130 246) var(--tw-gradient-to-position); }
</style>
