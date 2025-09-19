<!-- components/charts/OrdersTrendChart.vue -->
<template>
  <div
      class="w-full min-w-[320px] rounded-lg p-4 border transition-colors relative"
      :class="isDark ? 'bg-gray-900 border-gray-700 text-gray-100' : 'bg-white border-gray-200 text-gray-900'"
      aria-busy="false"
  >
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-base md:text-lg font-semibold flex items-center gap-2">
        <Icon name="mdi:chart-line" class="w-4 h-4" />
        {{ title }}
      </h3>

      <div class="flex items-center gap-2">
        <div class="flex items-center gap-1">
          <Icon name="mdi:calendar" class="w-4 h-4 opacity-70" />
          <select v-model="range" :class="selectClass" class="border rounded px-2 py-1 text-sm">
            <option value="today">Today</option>
            <option value="week">Last week</option>
            <option value="month">Last month</option>
            <option value="year">This year</option>
          </select>
        </div>
        <div class="flex items-center gap-1">
          <Icon name="mdi:finance" class="w-4 h-4 opacity-70" />
          <select v-model="metric" :class="selectClass" class="border rounded px-2 py-1 text-sm">
            <option value="count">Orders</option>
            <option value="revenue">Revenue</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loader -->
    <div v-if="loading" class="h-[var(--chart-h)] flex items-center justify-center text-sm opacity-70">
      <Icon name="mdi:loading" class="w-5 h-5 animate-spin mr-2" />
      Loading chart...
    </div>

    <!-- Chart container -->
    <div v-else class="relative w-full" :style="{ height: `${height}px` }">
      <v-chart v-if="hasLabels" :option="option" autoresize class="w-full h-full" />

      <!-- Empty-state overlay (renders even when series is all zeros) -->
      <div
          v-if="showZeroOverlay"
          class="pointer-events-none absolute inset-0 flex items-center justify-center"
      >
        <div
            class="px-3 py-1.5 rounded-full text-xs flex items-center gap-1.5"
            :class="isDark ? 'bg-gray-800/80 text-gray-300' : 'bg-gray-100/90 text-gray-700'"
        >
          <Icon name="mdi:information-outline" class="w-4 h-4" />
          No data for selected filters.
        </div>
      </div>

      <!-- No labels at all -->
      <div
          v-if="!hasLabels"
          class="absolute inset-0 flex items-center justify-center text-sm opacity-70"
      >
        <Icon name="mdi:chart-box-outline" class="w-5 h-5 mr-2" />
        No data for selected filters.
      </div>
    </div>

    <!-- Error -->
    <div v-if="errorMsg" class="mt-2 text-xs text-red-600 flex items-center gap-1">
      <Icon name="mdi:alert-circle-outline" class="w-4 h-4" />
      {{ errorMsg }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { useRuntimeConfig, useSanctumFetch } from '#imports'

interface Dataset { label: string; data: (number | string)[] }
interface ApiData { datasets: Dataset[]; labels: string[]; meta?: Record<string, any> }

const props = defineProps<{
  title: string
  endpoint: string               // e.g., 'orders/insight'
  type?: 'line' | 'bar'
  showMarkers?: boolean
  valueLabelFormatter?: string   // e.g., '₹{value}'
  status?: string[]              // optional
  height?: number                // pixels, default 320
}>()

const config = useRuntimeConfig()

// Filters (match backend API and Filament patterns)
const range = ref<'today' | 'week' | 'month' | 'year'>('year')
const metric = ref<'count' | 'revenue'>('count')

// Data + states
const data = ref<ApiData | null>(null)
const loading = ref(false)
const errorMsg = ref('')

// Sizing
const height = computed(() => props.height ?? 320)

// Dark mode detection (document class)
const isDark = computed(
    () => typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
)
const baseField = computed(
    () => (isDark.value ? 'bg-gray-800 border-gray-700 text-gray-100' : 'bg-white border-gray-300 text-gray-900')
)
const selectClass = computed(() => `${baseField.value}`)

// Helpers for rendering policy
const labels = computed(() => data.value?.labels ?? [])
const rawSeries = computed(() => data.value?.datasets ?? [])
const hasLabels = computed(() => labels.value.length > 0)

// Normalize series to numeric values
const seriesNumeric = computed(() =>
    rawSeries.value.map(ds => ({
      ...ds,
      data: (ds.data ?? []).map(v => (typeof v === 'string' ? Number(v) : (v as number)) || 0),
    }))
)

// Show overlay if all visible values are zero
const showZeroOverlay = computed(() => {
  if (!hasLabels.value || seriesNumeric.value.length === 0) return false
  const flat = seriesNumeric.value.flatMap(s => s.data)
  return flat.length > 0 && flat.every(v => Number(v) === 0)
})

// ECharts option
const option = computed(() => {
  const textColor = isDark.value ? '#E5E7EB' : '#111827'
  const axisColor = isDark.value ? '#9CA3AF' : '#6B7280'
  const splitColor = isDark.value ? '#374151' : '#E5E7EB'
  const primary = isDark.value ? '#60A5FA' : '#2563EB'
  const secondary = isDark.value ? '#34D399' : '#059669'
  const palette = [primary, secondary, '#F59E0B', '#EF4444']

  const series = seriesNumeric.value.map((ds, i) => ({
    name: ds.label,
    type: props.type ?? 'line',
    symbol: props.showMarkers ? 'circle' : 'none',
    data: ds.data,
    smooth: true,
    lineStyle: { width: 2, color: palette[i % palette.length] },
    itemStyle: { color: palette[i % palette.length] },
    areaStyle:
        (props.type ?? 'line') === 'line'
            ? {
              opacity: 0.08,
              color: palette[i % palette.length],
            }
            : undefined,
    barMaxWidth: 32,
  }))

  const formatter = props.valueLabelFormatter ?? '{value}'

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
      axisTick: { lineStyle: { color: axisColor } },
    },
    yAxis: {
      type: 'value',
      axisLabel: { formatter, color: axisColor },
      splitLine: { lineStyle: { color: splitColor } },
      axisLine: { lineStyle: { color: axisColor } },
    },
    series,
  }
})

async function load() {
  loading.value = true
  errorMsg.value = ''
  try {
    const url = new URL(`${config.public.apiBase}/${props.endpoint}`)
    url.searchParams.set('range', range.value)
    url.searchParams.set('metric', metric.value)
    if (props.status?.length) for (const s of props.status) url.searchParams.append('status[]', s)
    const res = await useSanctumFetch(url.toString(), { method: 'GET' })
    data.value = res?.data?.data || { datasets: [], labels: [] }
  } catch (e: any) {
    errorMsg.value = 'Failed to load chart data'
  } finally {
    loading.value = false
  }
}

// Debounce filter reload a bit for UX
let t: any
watch([range, metric], () => {
  clearTimeout(t)
  t = setTimeout(load, 150)
})

onMounted(load)
</script>

<style scoped>
:root { --chart-h: 320px; }
</style>
