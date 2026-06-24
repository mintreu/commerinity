<script setup lang="ts">
/**
 * Earnings Page
 * Shows commission history, summary, and breakdown
 */

const { user, isMember, isPromoter, isAdvisor } = useUserType()
const { isLoggedIn } = useSanctum()
const { summary, commissions, commissionsMeta, byType, monthly, isLoading, fetchSummary, fetchCommissions, fetchByType, fetchMonthly } = useCommissions()
const toast = useToast()

const activeTab = ref('history')
const filters = ref({
  status: '',
  type: '',
  period: ''
})
const hasLoaded = ref(false)

const loadEarnings = async () => {
  if (isAdvisor.value) return
  try {
    const [summaryResult, commissionsResult] = await Promise.allSettled([
      fetchSummary(),
      fetchCommissions()
    ])
    const summaryResponse = summaryResult.status === 'fulfilled' ? summaryResult.value : null
    const commissionsResponse = commissionsResult.status === 'fulfilled' ? commissionsResult.value : null
    const summaryOk = summaryResponse?.success === true
    const commissionsOk = commissionsResponse?.success === true

    if (!summaryOk || !commissionsOk) {
      const issues: string[] = []
      if (!summaryOk) issues.push('summary')
      if (!commissionsOk) issues.push('commissions')
      toast.add({
        title: 'Error',
        description: `Failed to load earnings ${issues.join(' & ')}`,
        color: 'error'
      })
    }
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to load earnings data',
      color: 'error'
    })
  }
}

const isUserReady = computed(() => !!user.value)

const triggerLoad = async () => {
  if (!isLoggedIn.value || !isUserReady.value) return
  if (!isMember.value && !isPromoter.value && !isAdvisor.value) {
    await navigateTo('/dashboard')
    return
  }
  if ((isMember.value || isPromoter.value) && !hasLoaded.value) {
    hasLoaded.value = true
    await loadEarnings()
  }
}

watch(
  () => [isLoggedIn.value, isUserReady.value, isMember.value, isPromoter.value, isAdvisor.value],
  () => {
    void triggerLoad()
  },
  { immediate: true }
)

onMounted(() => {
  void triggerLoad()
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
  <EarningsAdvisor v-if="isAdvisor" />
  <div
    v-else
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8"
  >
    <!-- Page Header -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 via-emerald-500/20 to-teal-500/20 rounded-2xl sm:rounded-3xl blur-3xl -z-10" />
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
        <div class="flex items-center gap-3 sm:gap-4">
          <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/30 flex-shrink-0">
            <UIcon
              name="i-lucide-trending-up"
              class="w-6 h-6 sm:w-8 sm:h-8 text-white"
            />
          </div>
          <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 dark:from-white dark:via-slate-100 dark:to-slate-300 bg-clip-text text-transparent">
              My Earnings
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
              Track your rewards and earnings history
            </p>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="isLoading && !summary"
      class="flex justify-center py-12"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="w-8 h-8 animate-spin text-primary-500"
      />
    </div>

    <template v-else>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon
                name="i-lucide-indian-rupee"
                class="w-5 h-5 sm:w-6 sm:h-6 text-white"
              />
            </div>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ summary?.total_earnings_formatted || '₹0.00' }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Total Earned
          </div>
        </div>

        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon
                name="i-lucide-calendar"
                class="w-5 h-5 sm:w-6 sm:h-6 text-white"
              />
            </div>
            <UBadge
              v-if="summary?.growth_percent"
              :color="summary.growth_percent >= 0 ? 'success' : 'error'"
              size="xs"
              class="hidden sm:inline-flex"
            >
              {{ summary.growth_percent >= 0 ? '+' : '' }}{{ summary.growth_percent }}%
            </UBadge>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ summary?.this_month_earnings_formatted || '₹0.00' }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2">
            This Month
            <UBadge
              v-if="summary?.growth_percent"
              :color="summary.growth_percent >= 0 ? 'success' : 'error'"
              size="xs"
              class="sm:hidden"
            >
              {{ summary.growth_percent >= 0 ? '+' : '' }}{{ summary.growth_percent }}%
            </UBadge>
          </div>
        </div>

        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon
                name="i-lucide-clock"
                class="w-5 h-5 sm:w-6 sm:h-6 text-white"
              />
            </div>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ summary?.pending_earnings_formatted || '₹0.00' }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Pending
          </div>
        </div>

        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <UIcon
                name="i-lucide-hash"
                class="w-5 h-5 sm:w-6 sm:h-6 text-white"
              />
            </div>
          </div>
          <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
            {{ summary?.total_commissions || 0 }}
          </div>
          <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
            Total Rewards
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="glass-card p-2 sm:p-3 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
        <UTabs
          v-model="activeTab"
          :items="tabs"
          class="w-full"
          @update:model-value="(val) => { if (val === 'by-type') loadByType(); if (val === 'monthly') loadMonthly(); }"
        />
      </div>

      <!-- History Tab -->
      <div
        v-if="activeTab === 'history'"
        class="space-y-4 sm:space-y-6"
      >
        <!-- Filters -->
        <div class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
          <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <USelect
              v-model="filters.status"
              :options="statusOptions"
              placeholder="All Status"
              size="lg"
              class="w-full sm:w-48"
              @update:model-value="applyFilters"
            />
            <UInput
              v-model="filters.period"
              placeholder="YYYY-MM"
              size="lg"
              class="w-full sm:w-40"
              @blur="applyFilters"
            />
          </div>
        </div>

        <!-- Commission List -->
        <div
          v-if="isLoading"
          class="flex justify-center py-8"
        >
          <UIcon
            name="i-lucide-loader-2"
            class="w-6 h-6 animate-spin text-primary-500"
          />
        </div>

        <template v-else-if="commissions.length > 0">
          <div
            v-for="commission in commissions"
            :key="commission.uuid"
            class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300"
          >
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
              <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                <UIcon
                  :name="getTypeIcon(commission.type)"
                  class="w-6 h-6 sm:w-7 sm:h-7 text-white"
                />
              </div>
              <div class="flex-1 min-w-0 w-full sm:w-auto">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                    {{ commission.type_label }}
                  </h3>
                  <UBadge
                    :color="getStatusColor(commission.status)"
                    size="xs"
                  >
                    {{ commission.status_label }}
                  </UBadge>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                  <span v-if="commission.from_user">From {{ commission.from_user.name }} &bull; </span>
                  {{ formatDate(commission.commission_date) }}
                </p>
              </div>
              <div class="text-left sm:text-right w-full sm:w-auto">
                <div class="text-lg sm:text-xl font-bold text-green-600 dark:text-green-400">
                  {{ commission.net_amount_formatted }}
                </div>
                <div
                  v-if="commission.level"
                  class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                >
                  Level {{ commission.level }}
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div
            v-if="commissionsMeta && commissionsMeta.last_page > 1"
            class="flex justify-center pt-4"
          >
            <UPagination
              :model-value="commissionsMeta.current_page"
              :total="commissionsMeta.total"
              :page-count="commissionsMeta.per_page"
              @update:model-value="loadPage"
            />
          </div>
        </template>

        <div
          v-else
          class="glass-card p-6 sm:p-8 lg:p-10 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl"
        >
          <!-- Show empty chart with zero values -->
          <div class="text-center py-6 sm:py-8">
            <div class="h-48 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl flex flex-col items-center justify-center mb-4">
              <UIcon
                name="i-lucide-bar-chart-3"
                class="w-16 h-16 text-green-300 dark:text-green-700 mb-3"
              />
              <div class="flex items-end gap-1 h-16">
                <div
                  class="w-8 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 20%"
                />
                <div
                  class="w-8 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 15%"
                />
                <div
                  class="w-8 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
                <div
                  class="w-8 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 5%"
                />
                <div
                  class="w-8 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 0%"
                />
                <div
                  class="w-8 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 0%"
                />
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              No Rewards Yet
            </h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
              Start building your community and earning rewards. Your earnings chart will appear here.
            </p>
            <NuxtLink
              to="/network"
              class="mt-4 inline-block"
            >
              <UButton
                color="primary"
                variant="soft"
              >
                <UIcon
                  name="i-lucide-users"
                  class="w-4 h-4 mr-2"
                />
                Build Your Community
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- By Type Tab -->
      <div
        v-if="activeTab === 'by-type'"
        class="space-y-4 sm:space-y-6"
      >
        <div
          v-if="isLoading"
          class="flex justify-center py-8"
        >
          <UIcon
            name="i-lucide-loader-2"
            class="w-6 h-6 animate-spin text-primary-500"
          />
        </div>

        <template v-else-if="byType.length > 0">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div
              v-for="item in byType"
              :key="item.type"
              class="glass-card p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl hover:shadow-2xl transition-all duration-300"
            >
              <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-primary-500 to-purple-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                  <UIcon
                    :name="getTypeIcon(item.type)"
                    class="w-6 h-6 sm:w-7 sm:h-7 text-white"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white truncate">
                    {{ item.type_label }}
                  </h3>
                  <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    {{ item.count }} commissions
                  </p>
                </div>
                <div class="text-right flex-shrink-0">
                  <div class="text-lg sm:text-xl font-bold text-green-600 dark:text-green-400">
                    {{ item.total_formatted }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <div
          v-else
          class="glass-card p-6 sm:p-8 lg:p-10 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl"
        >
          <!-- Show empty pie chart placeholder -->
          <div class="text-center py-8">
            <div class="w-32 h-32 mx-auto mb-4 rounded-full bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 flex items-center justify-center">
              <div class="w-24 h-24 rounded-full border-8 border-gray-200 dark:border-gray-700 flex items-center justify-center">
                <span class="text-2xl font-bold text-gray-400 dark:text-gray-500">0%</span>
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              No Commission Breakdown
            </h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
              Your commission types will be shown here once you start earning.
            </p>
          </div>
        </div>
      </div>

      <!-- Monthly Tab -->
      <div
        v-if="activeTab === 'monthly'"
        class="space-y-4 sm:space-y-6"
      >
        <div
          v-if="isLoading"
          class="flex justify-center py-8"
        >
          <UIcon
            name="i-lucide-loader-2"
            class="w-6 h-6 animate-spin text-primary-500"
          />
        </div>

        <template v-else-if="monthly.length > 0">
          <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
            <div class="space-y-4 sm:space-y-6">
              <div
                v-for="item in monthly"
                :key="item.period"
                class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4"
              >
                <div class="w-full sm:w-20 text-sm font-medium text-gray-600 dark:text-gray-300 flex-shrink-0">
                  {{ item.period }}
                </div>
                <div class="flex-1 w-full">
                  <div class="h-8 sm:h-10 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden">
                    <div
                      class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-xl transition-all duration-500"
                      :style="{ width: `${Math.min((item.total / Math.max(...monthly.map(m => m.total))) * 100, 100)}%` }"
                    />
                  </div>
                </div>
                <div class="w-full sm:w-32 text-left sm:text-right flex-shrink-0">
                  <div class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                    {{ item.total_formatted }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ item.count }} commissions
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <div
          v-else
          class="glass-card p-6 sm:p-8 lg:p-10 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl"
        >
          <!-- Show empty monthly chart placeholder -->
          <div class="text-center py-8">
            <div class="h-32 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl flex items-center justify-center mb-4">
              <div class="flex items-end gap-2 h-20">
                <div
                  class="w-10 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
                <div
                  class="w-10 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
                <div
                  class="w-10 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
                <div
                  class="w-10 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
                <div
                  class="w-10 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
                <div
                  class="w-10 bg-green-200 dark:bg-green-800 rounded-t"
                  style="height: 10%"
                />
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
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              No Monthly Data Yet
            </h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
              Your monthly earnings trend will be displayed here as you earn commissions.
            </p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
