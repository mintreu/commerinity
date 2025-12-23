<script setup lang="ts">
/**
 * Earnings Page
 * Shows commission history, summary, and breakdown
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const { summary, commissions, commissionsMeta, byType, monthly, isLoading, fetchSummary, fetchCommissions, fetchByType, fetchMonthly } = useCommissions()
const toast = useToast()

const activeTab = ref('history')
const filters = ref({
  status: '',
  type: '',
  period: ''
})

onMounted(async () => {
  try {
    await Promise.all([
      fetchSummary(),
      fetchCommissions()
    ])
  }
  catch {
    toast.add({
      title: 'Error',
      description: 'Failed to load earnings data',
      color: 'error'
    })
  }
})

const loadPage = async (page: number) => {
  await fetchCommissions({ page, ...filters.value })
}

const applyFilters = async () => {
  await fetchCommissions({ page: 1, ...filters.value })
}

const loadByType = async () => {
  if (byType.value.length === 0) {
    await fetchByType()
  }
}

const loadMonthly = async () => {
  if (monthly.value.length === 0) {
    await fetchMonthly()
  }
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusColor = (status: string): 'success' | 'warning' | 'error' | 'info' => {
  const colors: Record<string, 'success' | 'warning' | 'error' | 'info'> = {
    paid: 'success',
    approved: 'success',
    pending: 'warning',
    processing: 'info',
    held: 'warning',
    cancelled: 'error',
    reversed: 'error'
  }
  return colors[status] || 'info'
}

const getTypeIcon = (type: string) => {
  const icons: Record<string, string> = {
    sponsor_bonus: 'i-lucide-user-plus',
    level_commission: 'i-lucide-layers',
    level_achievement: 'i-lucide-award',
    referral_bonus: 'i-lucide-share-2',
    task_completion: 'i-lucide-check-circle',
    milestone_bonus: 'i-lucide-trophy',
    performance_bonus: 'i-lucide-trending-up'
  }
  return icons[type] || 'i-lucide-indian-rupee'
}

const tabs = [
  { label: 'History', value: 'history', icon: 'i-lucide-history' },
  { label: 'By Type', value: 'by-type', icon: 'i-lucide-pie-chart' },
  { label: 'Monthly', value: 'monthly', icon: 'i-lucide-bar-chart' }
]

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Paid', value: 'paid' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Processing', value: 'processing' },
  { label: 'Held', value: 'held' }
]
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <!-- Page Header -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Earnings</h1>
      <p class="text-gray-500 dark:text-gray-400">Track your commissions and earnings history</p>
    </div>

    <div v-if="isLoading && !summary" class="flex justify-center py-12">
      <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-primary-500" />
    </div>

    <template v-else>
      <!-- Summary Cards -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="glass-card p-5">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
              <UIcon name="i-lucide-indian-rupee" class="w-5 h-5 text-green-600 dark:text-green-400" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary?.total_earnings_formatted || '₹0.00' }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Total Earned</div>
        </div>

        <div class="glass-card p-5">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <UIcon name="i-lucide-calendar" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <UBadge v-if="summary?.growth_percent" :color="summary.growth_percent >= 0 ? 'success' : 'error'" size="xs">
              {{ summary.growth_percent >= 0 ? '+' : '' }}{{ summary.growth_percent }}%
            </UBadge>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary?.this_month_earnings_formatted || '₹0.00' }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">This Month</div>
        </div>

        <div class="glass-card p-5">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
              <UIcon name="i-lucide-clock" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary?.pending_earnings_formatted || '₹0.00' }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Pending</div>
        </div>

        <div class="glass-card p-5">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <UIcon name="i-lucide-hash" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary?.total_commissions || 0 }}</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Total Commissions</div>
        </div>
      </div>

      <!-- Tabs -->
      <UTabs
        v-model="activeTab"
        :items="tabs"
        class="w-full"
        @update:model-value="(val) => { if (val === 'by-type') loadByType(); if (val === 'monthly') loadMonthly(); }"
      />

      <!-- History Tab -->
      <div v-if="activeTab === 'history'" class="space-y-4">
        <!-- Filters -->
        <div class="glass-card p-4">
          <div class="flex flex-wrap gap-4">
            <USelect
              v-model="filters.status"
              :options="statusOptions"
              placeholder="All Status"
              class="w-40"
              @update:model-value="applyFilters"
            />
            <UInput
              v-model="filters.period"
              placeholder="YYYY-MM"
              class="w-32"
              @blur="applyFilters"
            />
          </div>
        </div>

        <!-- Commission List -->
        <div v-if="isLoading" class="flex justify-center py-8">
          <UIcon name="i-lucide-loader-2" class="w-6 h-6 animate-spin text-primary-500" />
        </div>

        <template v-else-if="commissions.length > 0">
          <div
            v-for="commission in commissions"
            :key="commission.uuid"
            class="glass-card p-4"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <UIcon :name="getTypeIcon(commission.type)" class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ commission.type_label }}</h3>
                  <UBadge :color="getStatusColor(commission.status)" size="xs">{{ commission.status_label }}</UBadge>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span v-if="commission.from_user">From {{ commission.from_user.name }} &bull; </span>
                  {{ formatDate(commission.commission_date) }}
                </p>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ commission.net_amount_formatted }}</div>
                <div v-if="commission.level" class="text-xs text-gray-500 dark:text-gray-400">Level {{ commission.level }}</div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="commissionsMeta && commissionsMeta.last_page > 1" class="flex justify-center pt-4">
            <UPagination
              :model-value="commissionsMeta.current_page"
              :total="commissionsMeta.total"
              :page-count="commissionsMeta.per_page"
              @update:model-value="loadPage"
            />
          </div>
        </template>

        <div v-else class="glass-card p-8">
          <!-- Show empty chart with zero values -->
          <div class="text-center py-8">
            <div class="h-48 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl flex flex-col items-center justify-center mb-4">
              <UIcon name="i-lucide-bar-chart-3" class="w-16 h-16 text-green-300 dark:text-green-700 mb-3" />
              <div class="flex items-end gap-1 h-16">
                <div class="w-8 bg-green-200 dark:bg-green-800 rounded-t" style="height: 20%" />
                <div class="w-8 bg-green-200 dark:bg-green-800 rounded-t" style="height: 15%" />
                <div class="w-8 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
                <div class="w-8 bg-green-200 dark:bg-green-800 rounded-t" style="height: 5%" />
                <div class="w-8 bg-green-200 dark:bg-green-800 rounded-t" style="height: 0%" />
                <div class="w-8 bg-green-200 dark:bg-green-800 rounded-t" style="height: 0%" />
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Commissions Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
              Start building your network and earning commissions. Your earnings chart will appear here.
            </p>
            <NuxtLink to="/network" class="mt-4 inline-block">
              <UButton color="primary" variant="soft">
                <UIcon name="i-lucide-users" class="w-4 h-4 mr-2" />
                Build Your Network
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- By Type Tab -->
      <div v-if="activeTab === 'by-type'" class="space-y-4">
        <div v-if="isLoading" class="flex justify-center py-8">
          <UIcon name="i-lucide-loader-2" class="w-6 h-6 animate-spin text-primary-500" />
        </div>

        <template v-else-if="byType.length > 0">
          <div class="grid gap-4 sm:grid-cols-2">
            <div
              v-for="item in byType"
              :key="item.type"
              class="glass-card p-5"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                  <UIcon :name="getTypeIcon(item.type)" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ item.type_label }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.count }} commissions</p>
                </div>
                <div class="text-right">
                  <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ item.total_formatted }}</div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <div v-else class="glass-card p-8">
          <!-- Show empty pie chart placeholder -->
          <div class="text-center py-8">
            <div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 flex items-center justify-center">
              <div class="w-24 h-24 rounded-full border-8 border-gray-200 dark:border-gray-700 flex items-center justify-center">
                <span class="text-2xl font-bold text-gray-400 dark:text-gray-500">0%</span>
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Commission Breakdown</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
              Your commission types will be shown here once you start earning.
            </p>
          </div>
        </div>
      </div>

      <!-- Monthly Tab -->
      <div v-if="activeTab === 'monthly'" class="space-y-4">
        <div v-if="isLoading" class="flex justify-center py-8">
          <UIcon name="i-lucide-loader-2" class="w-6 h-6 animate-spin text-primary-500" />
        </div>

        <template v-else-if="monthly.length > 0">
          <div class="glass-card p-6">
            <div class="space-y-4">
              <div
                v-for="item in monthly"
                :key="item.period"
                class="flex items-center gap-4"
              >
                <div class="w-20 text-sm font-medium text-gray-600 dark:text-gray-300">{{ item.period }}</div>
                <div class="flex-1">
                  <div class="h-8 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                    <div
                      class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-lg"
                      :style="{ width: `${Math.min((item.total / Math.max(...monthly.map(m => m.total))) * 100, 100)}%` }"
                    />
                  </div>
                </div>
                <div class="w-28 text-right">
                  <div class="font-semibold text-gray-900 dark:text-white">{{ item.total_formatted }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.count }} commissions</div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <div v-else class="glass-card p-8">
          <!-- Show empty monthly chart placeholder -->
          <div class="text-center py-8">
            <div class="h-32 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl flex items-center justify-center mb-4">
              <div class="flex items-end gap-2 h-20">
                <div class="w-10 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
                <div class="w-10 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
                <div class="w-10 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
                <div class="w-10 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
                <div class="w-10 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
                <div class="w-10 bg-green-200 dark:bg-green-800 rounded-t" style="height: 10%" />
              </div>
            </div>
            <div class="flex justify-center gap-4 text-xs text-gray-400 dark:text-gray-500 mb-4">
              <span>Jan</span>
              <span>Feb</span>
              <span>Mar</span>
              <span>Apr</span>
              <span>May</span>
              <span>Jun</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Monthly Data Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
              Your monthly earnings trend will be displayed here as you earn commissions.
            </p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>


