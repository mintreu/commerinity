<template>
  <div class="relative min-h-screen py-16 px-6 bg-gray-50 dark:bg-gray-900 transition-colors">
    <!-- Hero CTA -->
    <section class="text-center mb-12">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Regular User Insights
      </h1>
      <p class="text-gray-600 dark:text-gray-300 mb-6">
        Track your orders, service usage, and activity across the platform.
      </p>
      <button
          class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg shadow-md hover:from-indigo-500 hover:to-blue-500 transition"
      >
        View Order History
      </button>
    </section>

    <!-- Status Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Products Ordered</h2>
        <p class="text-3xl font-bold text-indigo-600 mt-2">{{ stats.productsOrdered }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month: {{ stats.thisMonth.productsOrdered }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Services Ordered</h2>
        <p class="text-3xl font-bold text-green-500 mt-2">{{ stats.servicesOrdered }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month: {{ stats.thisMonth.servicesOrdered }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Total Spending</h2>
        <p class="text-3xl font-bold text-yellow-500 mt-2">${{ stats.totalSpending }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month: ${{ stats.thisMonth.totalSpending }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Free Features Used</h2>
        <p class="text-3xl font-bold text-pink-500 mt-2">{{ stats.freeFeaturesUsed }}</p>
      </div>
    </section>

    <!-- Chart Section -->
    <section class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 sm:mb-0">
          Activity Over Time
        </h2>
        <!-- Filters -->
        <div class="flex flex-wrap gap-4">
          <input
              type="date"
              v-model="filters.from"
              class="border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
          />
          <input
              type="date"
              v-model="filters.to"
              class="border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
          />
          <label v-for="line in availableLines" :key="line.key" class="flex items-center space-x-2">
            <input type="checkbox" v-model="activeLines" :value="line.key" />
            <span class="text-gray-700 dark:text-gray-300">{{ line.label }}</span>
          </label>
        </div>
      </div>

      <v-chart
          class="chart-wrapper"
          :option="chartOption"
          autoresize
          :theme="isDark ? 'dark' : 'light'"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart } from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  DataZoomComponent,
  LegendComponent,
  DatasetComponent,
} from 'echarts/components'

use([CanvasRenderer, LineChart, TitleComponent, TooltipComponent, GridComponent, DataZoomComponent, LegendComponent, DatasetComponent])

definePageMeta({
  layout: 'dashboard',
})

// ✅ Mock Stats (no affiliate/commission)
const stats = ref({
  productsOrdered: 45,
  servicesOrdered: 18,
  totalSpending: 1200,
  freeFeaturesUsed: 30,
  thisMonth: {
    productsOrdered: 6,
    servicesOrdered: 3,
    totalSpending: 200,
  },
})

// ✅ Mock raw chart data
const rawData = ref([
  ['date', 'Products Ordered', 'Services Ordered', 'Total Spending'],
  ['2025-01-01', 5, 2, 150],
  ['2025-02-01', 7, 1, 120],
  ['2025-03-01', 3, 2, 90],
  ['2025-04-01', 8, 5, 300],
  ['2025-05-01', 6, 3, 220],
  ['2025-06-01', 10, 2, 280],
  ['2025-07-01', 4, 2, 100],
  ['2025-08-01', 9, 1, 240],
])

const filters = ref({ from: '2025-01-01', to: '2025-08-01' })

const availableLines = [
  { key: 'Products Ordered', label: 'Products Ordered', color: '#3b82f6' },
  { key: 'Services Ordered', label: 'Services Ordered', color: '#10b981' },
  { key: 'Total Spending', label: 'Total Spending', color: '#f59e0b' },
]
const activeLines = ref(availableLines.map(l => l.key))

const isDark = ref(false)
onMounted(() => {
  const html = document.documentElement
  isDark.value = html.classList.contains('dark')
  const media = window.matchMedia('(prefers-color-scheme: dark)')
  media.addEventListener('change', e => {
    isDark.value = e.matches || html.classList.contains('dark')
  })
})

const filteredDataset = computed(() => {
  const [header, ...rows] = rawData.value
  const filteredRows = rows.filter(
      r =>
          (!filters.value.from || r[0] >= filters.value.from) &&
          (!filters.value.to || r[0] <= filters.value.to)
  )
  return [header, ...filteredRows]
})

const chartOption = computed(() => {
  const activeIndexes = availableLines
      .map((l, idx) => ({ l, idx }))
      .filter(({ l }) => activeLines.value.includes(l.key))

  return {
    tooltip: { trigger: 'axis' },
    legend: {
      top: 20,
      data: activeIndexes.map(({ l }) => l.label),
      textStyle: { color: isDark.value ? '#ddd' : '#333' },
    },
    grid: { left: '5%', right: '5%', bottom: '12%', containLabel: true },
    dataZoom: [{ type: 'slider', start: 0, end: 100 }, { type: 'inside' }],
    dataset: { source: filteredDataset.value },
    xAxis: {
      type: 'category',
      axisLine: { lineStyle: { color: isDark.value ? '#aaa' : '#333' } },
      axisLabel: { color: isDark.value ? '#aaa' : '#333' },
      splitLine: { show: false },
    },
    yAxis: {
      type: 'value',
      axisLine: { lineStyle: { color: isDark.value ? '#aaa' : '#333' } },
      axisLabel: { color: isDark.value ? '#aaa' : '#333' },
      splitLine: { show: true, lineStyle: { color: isDark.value ? '#444' : '#ccc' } },
    },
    series: activeIndexes.map(({ idx, l }) => ({
      type: 'line',
      smooth: false,
      seriesLayoutBy: 'row',
      lineStyle: { width: 2.5, color: l.color },
      emphasis: { focus: 'series' },
      encode: { x: 0, y: idx + 1 },
    })),
  }
})
</script>

<style scoped>
.chart-wrapper {
  width: 100%;
  height: 420px;
}
</style>
