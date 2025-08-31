<template>
  <div class="relative min-h-screen py-16 px-6 bg-gray-50 dark:bg-gray-900 transition-colors">
    <!-- Hero CTA -->
    <section class="text-center mb-12">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
        Employee Progress Insights
      </h1>
      <p class="text-gray-600 dark:text-gray-300 mb-6">
        Track recruitment, sales, and income performance with detailed analytics.
      </p>
      <button
          class="px-6 py-3 bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-lg shadow-md hover:from-teal-500 hover:to-green-500 transition"
      >
        View Payouts
      </button>
    </section>

    <!-- Status Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Members Joined</h2>
        <p class="text-3xl font-bold text-indigo-600 mt-2">{{ stats.membersJoined }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month: {{ stats.thisMonth.membersJoined }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Active Recruits</h2>
        <p class="text-3xl font-bold text-green-500 mt-2">{{ stats.activeRecruits }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month: {{ stats.thisMonth.activeRecruits }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Total Commission</h2>
        <p class="text-3xl font-bold text-yellow-500 mt-2">${{ stats.commission }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">This Month: ${{ stats.thisMonth.commission }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Current Rank</h2>
        <p class="text-3xl font-bold text-pink-500 mt-2">{{ stats.rank }}</p>
      </div>
    </section>

    <!-- Chart Section -->
    <section class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 sm:mb-0">
          Performance Over Time
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

// Register ECharts components
use([
  CanvasRenderer,
  LineChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  DataZoomComponent,
  LegendComponent,
  DatasetComponent,
])

definePageMeta({
  layout: 'dashboard',
})

// Stats cards mock
const stats = ref({
  membersJoined: 120,
  activeRecruits: 80,
  commission: 1500,
  rank: 'Gold',
  thisMonth: {
    membersJoined: 15,
    activeRecruits: 10,
    commission: 200,
  },
})

// Raw chart data with ups and downs (zig-zag)
const rawData = ref([
  ['date', 'Members Joined', 'Sales Volume', 'Payout / Income'],
  ['2025-01-01', 5, 200, 50],
  ['2025-02-01', 9, 380, 110],
  ['2025-03-01', 7, 450, 210],
  ['2025-04-01', 14, 1300, 390],
  ['2025-05-01', 11, 1000, 470],
  ['2025-06-01', 17, 1800, 620],
  ['2025-07-01', 15, 1400, 780],
  ['2025-08-01', 22, 2200, 960],
])


// Filters
const filters = ref({
  from: '2025-01-01',
  to: '2025-08-01',
})

// Line toggles
const availableLines = [
  { key: 'Members Joined', label: 'Members Joined', color: '#3b82f6' },
  { key: 'Sales Volume', label: 'Sales Volume', color: '#10b981' },
  { key: 'Payout / Income', label: 'Payout / Income', color: '#f59e0b' },
]
const activeLines = ref(availableLines.map(l => l.key))

// Dark mode detection (from Tailwind's `dark` class on <html>)
const isDark = ref(false)
onMounted(() => {
  const html = document.documentElement
  isDark.value = html.classList.contains('dark')

  // If system dark mode changes
  const media = window.matchMedia('(prefers-color-scheme: dark)')
  media.addEventListener('change', e => {
    isDark.value = e.matches || html.classList.contains('dark')
  })
})

// Filtered dataset (keep header row)
const filteredDataset = computed(() => {
  const [header, ...rows] = rawData.value
  const filteredRows = rows.filter(
      r =>
          (!filters.value.from || r[0] >= filters.value.from) &&
          (!filters.value.to || r[0] <= filters.value.to)
  )
  return [header, ...filteredRows]
})

// Chart config using dataset + seriesLayoutBy
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
    dataZoom: [
      { type: 'slider', start: 0, end: 100 },
      { type: 'inside' },
    ],
    dataset: { source: filteredDataset.value },
    xAxis: {
      type: 'category',
      axisLine: { lineStyle: { color: isDark.value ? '#aaa' : '#333' } },
      axisLabel: { color: isDark.value ? '#aaa' : '#333' },
      splitLine: { show: false },
    },
    yAxis: [
      {
        type: 'value',
        axisLine: { lineStyle: { color: isDark.value ? '#aaa' : '#333' } },
        axisLabel: { color: isDark.value ? '#aaa' : '#333' },
        splitLine: { show: true, lineStyle: { color: isDark.value ? '#444' : '#ccc' } },
      },
      {
        type: 'value',
        position: 'right',
        axisLine: { lineStyle: { color: isDark.value ? '#aaa' : '#333' } },
        axisLabel: { color: isDark.value ? '#aaa' : '#333' },
        splitLine: { show: false }, // don’t double grid lines
      },
    ],
    series: activeIndexes.map(({ idx, l }) => ({
      type: 'line',
      smooth: false, // zig-zag look
      seriesLayoutBy: 'row',
      lineStyle: { width: 2.5, color: l.color },
      emphasis: { focus: 'series' },
      encode: { x: 0, y: idx + 1 },
      yAxisIndex: 0, // all share left ruler (or map some to right if needed)
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
