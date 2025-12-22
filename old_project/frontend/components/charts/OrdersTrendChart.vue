<template>
  <div class="chart-card" :class="isDark ? 'dark' : 'light'">

    <!-- Header -->
    <div class="chart-header">
      <h3 class="chart-title">
        <Icon name="mdi:chart-line" class="w-4 h-4" />
        {{ title }}
      </h3>

      <div class="chart-controls">
        <div class="control-group">
          <Icon name="mdi:calendar" class="w-4 h-4 opacity-70" />
          <select v-model="range" class="select">
            <option value="today">Today</option>
            <option value="week">Last week</option>
            <option value="month">Last month</option>
            <option value="year">This year</option>
          </select>
        </div>
        <div class="control-group">
          <Icon name="mdi:finance" class="w-4 h-4 opacity-70" />
          <select v-model="metric" class="select">
            <option value="count">Orders</option>
            <option value="revenue">Revenue</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="chart-loader">
      <Icon name="mdi:loading" class="w-5 h-5 animate-spin" />
      <span>Loading chart...</span>
    </div>

    <!-- Chart container -->
    <div v-else class="chart-container" :style="{ height: `${height}px` }">
      <!-- ✅ Lazy load ECharts -->
      <ClientOnly>
        <component :is="VChart" v-if="hasLabels && chartLoaded" :option="option" autoresize class="chart" />
      </ClientOnly>

      <!-- Empty-state overlay -->
      <div v-if="showZeroOverlay" class="chart-empty">
        <div class="empty-badge">
          <Icon name="mdi:information-outline" class="w-4 h-4" />
          No data for selected filters.
        </div>
      </div>

      <!-- No labels -->
      <div v-if="!hasLabels" class="chart-no-data">
        <Icon name="mdi:chart-box-outline" class="w-5 h-5" />
        <span>No data for selected filters.</span>
      </div>
    </div>

    <!-- Error -->
    <div v-if="errorMsg" class="chart-error">
      <Icon name="mdi:alert-circle-outline" class="w-4 h-4" />
      {{ errorMsg }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted, defineAsyncComponent } from 'vue'
import { useRuntimeConfig, useSanctumFetch } from '#imports'

interface Dataset { label: string; data: (number | string)[] }
interface ApiData { datasets: Dataset[]; labels: string[]; meta?: Record<string, any> }

const props = defineProps<{
  title: string
  endpoint: string
  type?: 'line' | 'bar'
  showMarkers?: boolean
  valueLabelFormatter?: string
  status?: string[]
  height?: number
}>()

const config = useRuntimeConfig()

// ✅ Lazy load ECharts (only when component mounts)
const VChart = defineAsyncComponent(() =>
    import('vue-echarts').then(module => module.default || module)
)

// Filters
const range = ref<'today' | 'week' | 'month' | 'year'>('year')
const metric = ref<'count' | 'revenue'>('count')

// Data + states
const data = ref<ApiData | null>(null)
const loading = ref(false)
const errorMsg = ref('')
const chartLoaded = ref(false)

// Sizing
const height = computed(() => props.height ?? 320)

// Dark mode
const isDark = computed(() =>
    typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
)

// Helpers
const labels = computed(() => data.value?.labels ?? [])
const rawSeries = computed(() => data.value?.datasets ?? [])
const hasLabels = computed(() => labels.value.length > 0)

// Normalize series to numeric values
const seriesNumeric = computed(() =>
    rawSeries.value.map(ds => ({
      ...ds,
      data: (ds.data ?? []).map(v => (typeof v === 'string' ? Number(v) : (v as number)) || 0)
    }))
)

// Show overlay if all visible values are zero
const showZeroOverlay = computed(() => {
  if (!hasLabels.value || seriesNumeric.value.length === 0) return false
  const flat = seriesNumeric.value.flatMap(s => s.data)
  return flat.length > 0 && flat.every(v => Number(v) === 0)
})

// ✅ Optimized ECharts option
const option = computed(() => {
  const textColor = isDark.value ? '#E5E7EB' : '#111827'
  const axisColor = isDark.value ? '#9CA3AF' : '#6B7280'
  const splitColor = isDark.value ? '#374151' : '#E5E7EB'
  const palette = isDark.value
      ? ['#60A5FA', '#34D399', '#F59E0B', '#EF4444']
      : ['#2563EB', '#059669', '#F59E0B', '#EF4444']

  const series = seriesNumeric.value.map((ds, i) => ({
    name: ds.label,
    type: props.type ?? 'line',
    symbol: props.showMarkers ? 'circle' : 'none',
    data: ds.data,
    smooth: true,
    lineStyle: { width: 2, color: palette[i % palette.length] },
    itemStyle: { color: palette[i % palette.length] },
    areaStyle: (props.type ?? 'line') === 'line' ? { opacity: 0.08, color: palette[i % palette.length] } : undefined,
    barMaxWidth: 32
  }))

  return {
    textStyle: { color: textColor },
    tooltip: { trigger: 'axis' },
    legend: { top: 0, textStyle: { color: textColor } },
    grid: { left: 40, right: 20, bottom: 40, top: 40, containLabel: true },
    xAxis: {
      type: 'category',
      data: labels.value,
      axisLabel: { color: axisColor },
      axisLine: { lineStyle: { color: axisColor } },
      axisTick: { lineStyle: { color: axisColor } }
    },
    yAxis: {
      type: 'value',
      axisLabel: { formatter: props.valueLabelFormatter ?? '{value}', color: axisColor },
      splitLine: { lineStyle: { color: splitColor } },
      axisLine: { lineStyle: { color: axisColor } }
    },
    series
  }
})

// ✅ Optimized data loading with abort controller
let abortController: AbortController | null = null

async function load() {
  // Cancel previous request
  if (abortController) abortController.abort()
  abortController = new AbortController()

  loading.value = true
  errorMsg.value = ''

  try {
    const url = new URL(`${config.public.apiBase}/${props.endpoint}`)
    url.searchParams.set('range', range.value)
    url.searchParams.set('metric', metric.value)
    if (props.status?.length) {
      props.status.forEach(s => url.searchParams.append('status[]', s))
    }

    const res = await useSanctumFetch(url.toString(), {
      method: 'GET',
      signal: abortController.signal
    })

    data.value = res?.data?.data || { datasets: [], labels: [] }
  } catch (e: any) {
    if (e.name !== 'AbortError') {
      errorMsg.value = 'Failed to load chart data'
    }
  } finally {
    loading.value = false
  }
}

// ✅ Debounced filter reload
let debounceTimer: NodeJS.Timeout
watch([range, metric], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(load, 200)
})

onMounted(() => {
  // Load chart after a small delay to prioritize critical content
  setTimeout(() => {
    chartLoaded.value = true
    load()
  }, 100)
})
</script>

<style scoped>
/* Card */
.chart-card {
  min-width: 320px;
  border-radius: 0.5rem;
  padding: 1rem;
  border: 1px solid;
  transition: all 0.2s;
  position: relative;
}

.chart-card.light {
  background: white;
  border-color: rgb(229,231,235);
  color: rgb(17,24,39);
}

.chart-card.dark {
  background: rgb(17,24,39);
  border-color: rgb(55,65,81);
  color: rgb(229,231,235);
}

/* Header */
.chart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

@media (min-width: 768px) {
  .chart-title {
    font-size: 1.125rem;
  }
}

.chart-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.select {
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  border: 1px solid;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.light .select {
  background: white;
  border-color: rgb(209,213,219);
  color: rgb(17,24,39);
}

.light .select:hover {
  border-color: rgb(156,163,175);
}

.dark .select {
  background: rgb(31,41,55);
  border-color: rgb(55,65,81);
  color: rgb(229,231,235);
}

.dark .select:hover {
  border-color: rgb(75,85,99);
}

/* Loader */
.chart-loader {
  height: 320px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  opacity: 0.7;
}

/* Chart Container */
.chart-container {
  position: relative;
  width: 100%;
}

.chart {
  width: 100%;
  height: 100%;
}

/* Empty State */
.chart-empty {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.empty-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.light .empty-badge {
  background: rgba(243,244,246,0.9);
  color: rgb(55,65,81);
}

.dark .empty-badge {
  background: rgba(31,41,55,0.8);
  color: rgb(209,213,219);
}

/* No Data */
.chart-no-data {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  opacity: 0.7;
}

/* Error */
.chart-error {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: rgb(220,38,38);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
</style>
