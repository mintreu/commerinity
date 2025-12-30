<script setup lang="ts">
import { Line, Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement,
  BarElement,
  ArcElement,
  Filler,
  type ChartOptions,
  type ChartData as ChartJSData
} from 'chart.js'
import type { Period, Interval, TrendParams } from '~/composables/useTrends'

// Register Chart.js components
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement,
  BarElement,
  ArcElement,
  Filler
)

const props = defineProps<{
  type: 'line' | 'bar' | 'doughnut' | 'pie'
  fetchMethod: (params: TrendParams) => Promise<any>
  title?: string
  height?: string | number
  period?: Period
  interval?: Interval
  color?: 'primary' | 'success' | 'warning' | 'danger' | 'purple' | 'amber'
  showControls?: boolean
}>()

const { toChartJsData, getLineChartOptions, getBarChartOptions, getDoughnutChartOptions } = useTrends()

const currentPeriod = ref<Period>(props.period || 'month')
const chartData = ref<ChartJSData<any>>({
  labels: [],
  datasets: []
})
const loading = ref(true)
const error = ref<string | null>(null)

const periods = [
  { label: 'Today', value: 'today' as Period },
  { label: 'Week', value: 'week' as Period },
  { label: 'Month', value: 'month' as Period },
  { label: 'Year', value: 'year' as Period }
]

const loadData = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await props.fetchMethod({
      period: currentPeriod.value,
      interval: props.interval
    })

    if (response?.success) {
      chartData.value = toChartJsData(response)
    } else {
      error.value = 'No data available'
    }
  } catch (e: any) {
    error.value = e.message || 'Failed to load chart'
  } finally {
    loading.value = false
  }
}

const chartOptions = computed(() => {
  let options: any
  switch (props.type) {
    case 'bar':
      options = getBarChartOptions(props.title)
      break
    case 'doughnut':
    case 'pie':
      options = getDoughnutChartOptions(props.title)
      break
    default:
      options = getLineChartOptions(props.title)
  }

  // Ensure responsive behavior
  return {
    ...options,
    maintainAspectRatio: false,
    plugins: {
      ...options.plugins,
      legend: {
        ...options.plugins?.legend,
        labels: {
          usePointStyle: true,
          padding: 20,
          font: {
            family: "'Plus Jakarta Sans', sans-serif",
            size: 12
          }
        }
      }
    }
  } as ChartOptions<any>
})

watch(() => currentPeriod.value, loadData)

onMounted(loadData)

defineExpose({ refresh: loadData })
</script>

<template>
  <div class="flex flex-col h-full">
    <div v-if="showControls" class="flex items-center justify-between mb-4">
      <h3 v-if="title" class="text-sm font-semibold text-slate-900 dark:text-white">
        {{ title }}
      </h3>
      <div class="flex p-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg overflow-hidden">
        <button
          v-for="p in periods"
          :key="p.value"
          class="px-3 py-1 text-xs font-medium transition-colors"
          :class="[
            currentPeriod === p.value
              ? 'bg-white dark:bg-slate-700 text-primary-600 dark:text-primary-400 shadow-sm rounded-md'
              : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'
          ]"
          @click="currentPeriod = p.value"
        >
          {{ p.label }}
        </button>
      </div>
    </div>

    <div class="relative flex-1 min-h-[160px]" :style="{ height: height ? (typeof height === 'number' ? `${height}px` : height) : '100%' }">
      <!-- Loading State -->
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-slate-900/50 z-10 rounded-xl overflow-hidden backdrop-blur-sm">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 text-primary-500 animate-spin" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="absolute inset-0 flex flex-col items-center justify-center text-center p-4">
        <UIcon name="i-lucide-alert-circle" class="w-8 h-8 text-red-400 mb-2" />
        <p class="text-sm text-slate-600 dark:text-slate-400">{{ error }}</p>
        <UButton size="xs" variant="soft" color="primary" class="mt-2" @click="loadData">
          Retry
        </UButton>
      </div>

      <!-- Empty State -->
      <div v-else-if="!chartData.labels.length" class="absolute inset-0 flex flex-col items-center justify-center text-center p-4">
        <UIcon name="i-lucide-bar-chart-3" class="w-10 h-10 text-slate-300 mb-2" />
        <p class="text-sm text-slate-500">No data available for this period</p>
      </div>

      <!-- Chart -->
      <div v-else class="h-full w-full">
        <Line v-if="type === 'line'" :data="chartData" :options="chartOptions" />
        <Bar v-else-if="type === 'bar'" :data="chartData" :options="chartOptions" />
        <Doughnut v-else-if="type === 'doughnut' || type === 'pie'" :data="chartData" :options="chartOptions" />
      </div>
    </div>
  </div>
</template>
