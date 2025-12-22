<template>
  <div v-for="(stat, index) in visibleStats" :key="`stat-${index}`">
    <!-- Card Mode (Store Hero) -->
    <div v-if="stat.isCard"
         class="stat-card bg-white/10 backdrop-blur-lg rounded-3xl px-6 py-4 text-center text-white border border-white/20 hover:bg-white/20 hover:border-white/40 transition-all duration-300 transform hover:scale-110 hover:shadow-2xl shadow-lg min-w-[120px]">
      <Icon :name="stat.icon" :class="`w-6 h-6 mx-auto mb-2 ${stat.iconColor}`" />
      <div class="text-2xl font-black">{{ displayStats[index] !== undefined ? displayStats[index] : stat.value }}</div>
      <div class="text-xs opacity-80 font-medium">{{ stat.label }}</div>
    </div>

    <!-- Transparent Mode (Homepage) -->
    <div v-else class="stat-simple flex flex-col items-center">
      <Icon :name="stat.icon" :class="`w-6 h-6 mb-2 ${getIconClass(stat.iconColor)}`" />
      <div class="text-2xl sm:text-3xl lg:text-4xl font-black" :class="getTextClass(stat.textColor)">
        {{ displayStats[index] !== undefined ? displayStats[index] : stat.value }}
      </div>
      <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">{{ stat.label }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

interface StatItem {
  label: string
  value: string | number
  icon: string
  textColor: string
  iconColor: string
  type: 'number' | 'decimal' | 'text'
  visibility: boolean
  isCard: boolean
}

interface Props {
  api: string
  animated?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  animated: true
})

const config = useRuntimeConfig()
const stats = ref<StatItem[]>([])
const displayStats = ref<Record<number, any>>({})

const visibleStats = computed(() => stats.value.filter(s => s.visibility))

const colorMap: Record<string, string> = {
  purple: 'text-purple-600 dark:text-purple-400',
  pink: 'text-pink-600 dark:text-pink-400',
  blue: 'text-blue-600 dark:text-blue-400',
  green: 'text-green-600 dark:text-green-400',
  yellow: 'text-yellow-600 dark:text-yellow-400',
  red: 'text-red-600 dark:text-red-400',
  orange: 'text-orange-600 dark:text-orange-400',
  teal: 'text-teal-600 dark:text-teal-400',
  white: 'text-white'
}

const iconColorMap: Record<string, string> = {
  purple: 'text-purple-400',
  pink: 'text-pink-300',
  blue: 'text-blue-300',
  green: 'text-green-300',
  yellow: 'text-yellow-300',
  red: 'text-red-300',
  orange: 'text-orange-300',
  teal: 'text-teal-300',
  white: 'text-white'
}

const getTextClass = (color: string): string => colorMap[color] || colorMap.purple
const getIconClass = (color: string): string => iconColorMap[color] || iconColorMap.purple

if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

const fetchStats = async () => {
  try {
    const res: any = await useSanctumFetch(`${config.public.apiBase}${props.api}`)
    if (res?.data) {
      stats.value = res.data
      res.data.forEach((stat: StatItem, index: number) => {
        displayStats.value[index] = (stat.type === 'number' || stat.type === 'decimal') ? 0 : stat.value
      })
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const animateCounters = () => {
  if (!props.animated) {
    stats.value.forEach((stat, index) => { displayStats.value[index] = stat.value })
    return
  }

  setTimeout(() => {
    stats.value.forEach((stat, index) => {
      if (stat.type === 'number' || stat.type === 'decimal') {
        const target = typeof stat.value === 'number' ? stat.value : parseFloat(stat.value as string)

        gsap.fromTo(
            displayStats.value,
            { [index]: 0 },
            {
              [index]: target,
              duration: 2,
              ease: 'power2.out',
              scrollTrigger: {
                trigger: '.stat-card, .stat-simple',
                start: 'top 80%',
                once: true
              },
              onUpdate: function() {
                if (stat.type === 'decimal') {
                  displayStats.value[index] = parseFloat(displayStats.value[index].toFixed(1))
                } else {
                  displayStats.value[index] = Math.floor(displayStats.value[index])
                }
              }
            }
        )
      }
    })
  }, 300)
}

watch(() => stats.value, (newStats) => {
  if (newStats.length > 0) animateCounters()
}, { deep: true })

onMounted(async () => {
  await fetchStats()
})
</script>

<style scoped>
.stat-card {
  min-width: 120px;
}

.stat-simple {
  transition: all 0.3s ease;
}
</style>
