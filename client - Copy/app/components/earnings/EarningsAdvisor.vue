<script setup lang="ts">
/**
 * Advisor Earnings View
 * Advisor-specific summary based on originated user sales + advisor earnings.
 */

const { summary, isLoading, fetchSummary } = useAdvisorEarnings()
const toast = useToast()

onMounted(async () => {
  try {
    await fetchSummary()
  } catch {
    toast.add({
      title: 'Error',
      description: 'Failed to load advisor earnings data',
      color: 'error'
    })
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
    <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
          <UIcon
            name="i-lucide-briefcase"
            class="w-6 h-6 sm:w-8 sm:h-8 text-white"
          />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
            Advisor Earnings
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
            Track originated sales volume and advisor earnings
          </p>
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
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <CommonStatCard
          title="Total Sale Volume"
          :value="summary?.total_sale_volume_formatted || '₹0.00'"
          icon="i-lucide-shopping-bag"
          color="primary"
        />
        <CommonStatCard
          title="Total Earnings"
          :value="summary?.total_earnings_formatted || '₹0.00'"
          icon="i-lucide-indian-rupee"
          color="success"
        />
      </div>
    </template>
  </div>
</template>
