<script setup lang="ts">
/**
 * Analytics Dashboard
 * Comprehensive analytics with user growth, sales, team stats, conversions, and traffic
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const activeTab = ref('overview')
const dateRange = ref('7d')

// Mock data - Replace with actual API calls
const analytics = ref({
  userGrowth: {
    totalUsers: 12458,
    activeUsers: 8234,
    newUsersToday: 127,
    growth: 15.3,
    chartData: [
      { date: 'Jan', users: 8500 },
      { date: 'Feb', users: 9200 },
      { date: 'Mar', users: 10100 },
      { date: 'Apr', users: 11200 },
      { date: 'May', users: 11800 },
      { date: 'Jun', users: 12458 }
    ]
  },
  sales: {
    totalRevenue: 2458900,
    todayRevenue: 45600,
    orders: 1247,
    avgOrderValue: 1970,
    growth: 23.5,
    topProducts: [
      { name: 'Premium Plan', sales: 456, revenue: 912000 },
      { name: 'Professional Plan', sales: 328, revenue: 656000 },
      { name: 'Starter Plan', sales: 463, revenue: 463000 }
    ]
  },
  team: {
    totalMembers: 456,
    activeMembers: 389,
    topPerformers: 23,
    avgPerformance: 78.5,
    levels: [
      { level: 'Level 1', count: 156, active: 142 },
      { level: 'Level 2', count: 98, active: 87 },
      { level: 'Level 3', count: 67, active: 58 },
      { level: 'Level 4', count: 45, active: 38 },
      { level: 'Level 5+', count: 90, active: 64 }
    ]
  },
  conversions: {
    signupRate: 68.5,
    activationRate: 54.2,
    purchaseRate: 32.8,
    retentionRate: 78.9,
    funnel: [
      { stage: 'Visitors', count: 45600, percent: 100 },
      { stage: 'Sign ups', count: 31236, percent: 68.5 },
      { stage: 'Activated', count: 16930, percent: 54.2 },
      { stage: 'Purchased', count: 5551, percent: 32.8 }
    ]
  },
  traffic: {
    totalVisits: 45678,
    pageViews: 123456,
    avgDuration: '4m 32s',
    bounceRate: 34.5,
    sources: [
      { source: 'Direct', visits: 15234, percent: 33.4 },
      { source: 'Organic Search', visits: 12890, percent: 28.2 },
      { source: 'Social Media', visits: 9876, percent: 21.6 },
      { source: 'Referral', visits: 5432, percent: 11.9 },
      { source: 'Email', visits: 2246, percent: 4.9 }
    ],
    topPages: [
      { page: '/shop', views: 28934, duration: '5m 12s' },
      { page: '/dashboard', views: 19283, duration: '8m 45s' },
      { page: '/products', views: 15672, duration: '3m 28s' }
    ]
  }
})

const tabs = [
  { label: 'Overview', value: 'overview', icon: 'i-lucide-layout-dashboard' },
  { label: 'User Growth', value: 'users', icon: 'i-lucide-users' },
  { label: 'Sales', value: 'sales', icon: 'i-lucide-trending-up' },
  { label: 'Team Stats', value: 'team', icon: 'i-lucide-users-round' },
  { label: 'Conversions', value: 'conversions', icon: 'i-lucide-target' },
  { label: 'Traffic', value: 'traffic', icon: 'i-lucide-globe' }
]

const dateRanges = [
  { label: 'Last 7 Days', value: '7d' },
  { label: 'Last 30 Days', value: '30d' },
  { label: 'Last 90 Days', value: '90d' },
  { label: 'This Year', value: 'year' }
]

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(amount)
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-IN').format(num)
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
    <!-- Page Header -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20 rounded-2xl sm:rounded-3xl blur-3xl -z-10" />
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 flex-shrink-0">
              <UIcon name="i-lucide-bar-chart-3" class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 dark:from-white dark:via-slate-100 dark:to-slate-300 bg-clip-text text-transparent">
                Analytics Dashboard
              </h1>
              <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                Track your performance metrics and insights
              </p>
            </div>
          </div>
          <USelectMenu
            v-model="dateRange"
            :options="dateRanges"
            value-attribute="value"
            option-attribute="label"
            size="lg"
            class="w-full lg:w-48"
          />
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="glass-card p-2 sm:p-3 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <UTabs
        v-model="activeTab"
        :items="tabs"
        class="w-full"
      />
    </div>

    <!-- Overview Tab -->
    <div v-if="activeTab === 'overview'" class="space-y-6">
      <!-- Quick Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon name="i-lucide-users" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
            <UBadge color="success" size="xs" class="hidden sm:inline-flex">+{{ analytics.userGrowth.growth }}%</UBadge>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.userGrowth.totalUsers) }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Total Users
          </div>
        </div>

        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon name="i-lucide-indian-rupee" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
            <UBadge color="success" size="xs" class="hidden sm:inline-flex">+{{ analytics.sales.growth }}%</UBadge>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ formatCurrency(analytics.sales.totalRevenue) }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Total Revenue
          </div>
        </div>

        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon name="i-lucide-users-round" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.team.totalMembers) }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Team Members
          </div>
        </div>

        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon name="i-lucide-target" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ analytics.conversions.signupRate }}%
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Signup Rate
          </div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- User Growth Chart -->
        <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Growth Trend</h3>
          <div class="space-y-3">
            <div v-for="item in analytics.userGrowth.chartData" :key="item.date" class="flex items-center gap-3">
              <div class="w-12 text-xs font-medium text-gray-600 dark:text-gray-400">{{ item.date }}</div>
              <div class="flex-1">
                <div class="h-8 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                  <div
                    class="h-full bg-gradient-to-r from-blue-400 to-cyan-500 rounded-lg transition-all duration-500"
                    :style="{ width: `${(item.users / 13000) * 100}%` }"
                  />
                </div>
              </div>
              <div class="w-16 text-right text-sm font-semibold text-gray-900 dark:text-white">
                {{ formatNumber(item.users) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Traffic Sources -->
        <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Traffic Sources</h3>
          <div class="space-y-4">
            <div v-for="source in analytics.traffic.sources" :key="source.source" class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-gray-900 dark:text-white">{{ source.source }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ source.percent }}%</span>
              </div>
              <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div
                  class="h-full bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full transition-all duration-500"
                  :style="{ width: `${source.percent}%` }"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Growth Tab -->
    <div v-if="activeTab === 'users'" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-users" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.userGrowth.totalUsers) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Users</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-user-check" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.userGrowth.activeUsers) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active Users</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-user-plus" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.userGrowth.newUsersToday) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">New Today</div>
        </div>
      </div>

      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Monthly Growth Trend</h3>
        <div class="space-y-4">
          <div v-for="item in analytics.userGrowth.chartData" :key="item.date" class="flex items-center gap-4">
            <div class="w-16 text-sm font-medium text-gray-600 dark:text-gray-400">{{ item.date }}</div>
            <div class="flex-1">
              <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden">
                <div
                  class="h-full bg-gradient-to-r from-blue-400 to-cyan-500 rounded-xl transition-all duration-500"
                  :style="{ width: `${(item.users / 13000) * 100}%` }"
                />
              </div>
            </div>
            <div class="w-20 text-right text-base font-semibold text-gray-900 dark:text-white">
              {{ formatNumber(item.users) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sales Tab -->
    <div v-if="activeTab === 'sales'" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-indian-rupee" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatCurrency(analytics.sales.totalRevenue) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Revenue</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-calendar" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatCurrency(analytics.sales.todayRevenue) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Today's Revenue</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-shopping-cart" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.sales.orders) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Orders</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-trending-up" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatCurrency(analytics.sales.avgOrderValue) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Avg Order Value</div>
        </div>
      </div>

      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Top Products</h3>
        <div class="space-y-4">
          <div v-for="product in analytics.sales.topProducts" :key="product.name" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon name="i-lucide-package" class="w-5 h-5 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-gray-900 dark:text-white">{{ product.name }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ product.sales }} sales</p>
            </div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">
              {{ formatCurrency(product.revenue) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Team Stats Tab -->
    <div v-if="activeTab === 'team'" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-users-round" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.team.totalMembers) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Members</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-user-check" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.team.activeMembers) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active Members</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-award" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.team.topPerformers) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Top Performers</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-trending-up" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.team.avgPerformance }}%
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Avg Performance</div>
        </div>
      </div>

      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Team Distribution by Level</h3>
        <div class="space-y-4">
          <div v-for="level in analytics.team.levels" :key="level.level" class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="font-medium text-gray-900 dark:text-white">{{ level.level }}</span>
              <span class="text-gray-500 dark:text-gray-400">{{ level.active }}/{{ level.count }} active</span>
            </div>
            <div class="h-8 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg transition-all duration-500"
                :style="{ width: `${(level.count / 200) * 100}%` }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Conversions Tab -->
    <div v-if="activeTab === 'conversions'" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-user-plus" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.conversions.signupRate }}%
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Signup Rate</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-zap" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.conversions.activationRate }}%
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Activation Rate</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-shopping-bag" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.conversions.purchaseRate }}%
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Purchase Rate</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-repeat" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.conversions.retentionRate }}%
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Retention Rate</div>
        </div>
      </div>

      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Conversion Funnel</h3>
        <div class="space-y-4">
          <div v-for="stage in analytics.conversions.funnel" :key="stage.stage" class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="font-medium text-gray-900 dark:text-white">{{ stage.stage }}</span>
              <span class="text-gray-500 dark:text-gray-400">{{ formatNumber(stage.count) }} ({{ stage.percent }}%)</span>
            </div>
            <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-indigo-400 to-purple-500 rounded-xl transition-all duration-500 flex items-center justify-center text-white text-sm font-semibold"
                :style="{ width: `${stage.percent}%` }"
              >
                {{ stage.percent }}%
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Traffic Tab -->
    <div v-if="activeTab === 'traffic'" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-eye" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.traffic.totalVisits) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Visits</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-file-text" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ formatNumber(analytics.traffic.pageViews) }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Page Views</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-clock" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.traffic.avgDuration }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Avg Duration</div>
        </div>

        <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
              <UIcon name="i-lucide-arrow-right-left" class="w-6 h-6 text-white" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ analytics.traffic.bounceRate }}%
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Bounce Rate</div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Traffic Sources</h3>
          <div class="space-y-4">
            <div v-for="source in analytics.traffic.sources" :key="source.source" class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-gray-900 dark:text-white">{{ source.source }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ formatNumber(source.visits) }}</span>
              </div>
              <div class="h-8 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                <div
                  class="h-full bg-gradient-to-r from-blue-400 to-cyan-500 rounded-lg transition-all duration-500"
                  :style="{ width: `${source.percent * 3}%` }"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Top Pages</h3>
          <div class="space-y-4">
            <div v-for="page in analytics.traffic.topPages" :key="page.page" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
                <UIcon name="i-lucide-file" class="w-5 h-5 text-white" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-gray-900 dark:text-white truncate">{{ page.page }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ page.duration }} avg duration</p>
              </div>
              <div class="text-base font-bold text-gray-900 dark:text-white">
                {{ formatNumber(page.views) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

