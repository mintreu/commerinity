<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const { isMember, isPromoter } = useUserType()
const { formatCurrency, formatDate } = useBranding()
const route = useRoute()
const fundType = computed(() => String(route.params.fundType || ''))

const { transactions, transactionsMeta, loading, fetchTransactions } = useAffiliateFunds()

onMounted(async () => {
  if (!isMember.value && !isPromoter.value) {
    await navigateTo('/dashboard')
    return
  }
  if (fundType.value) {
    await fetchTransactions(fundType.value)
  }
})

const loadPage = async (page: number) => {
  await fetchTransactions(fundType.value, { page })
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
    <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
          <UIcon name="i-lucide-receipt" class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white capitalize">
            {{ fundType }} Transactions
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
            Detailed fund ledger
          </p>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-10">
      <UIcon name="i-lucide-loader-2" class="w-7 h-7 animate-spin text-primary-500" />
    </div>

    <div v-else-if="transactions.length === 0" class="glass-card p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 text-center">
      <p class="text-sm text-slate-500">No transactions yet.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="tx in transactions"
        :key="tx.id"
        class="glass-card p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <UBadge :color="tx.type === 'credit' ? 'success' : 'error'" size="xs">
                {{ tx.type_label || tx.type }}
              </UBadge>
              <span class="text-xs text-slate-500">{{ formatDate(tx.created_at, 'short') }}</span>
            </div>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">
              {{ tx.notes || 'Fund transaction' }}
            </p>
          </div>
          <div class="text-right">
            <div class="text-lg font-bold text-slate-900 dark:text-white">
              {{ tx.amount_formatted || formatCurrency(tx.amount / 100) }}
            </div>
            <div v-if="tx.balance_after_formatted" class="text-xs text-slate-500">
              Balance: {{ tx.balance_after_formatted }}
            </div>
          </div>
        </div>
      </div>

      <div v-if="transactionsMeta && transactionsMeta.last_page > 1" class="flex justify-center pt-2">
        <UPagination
          :model-value="transactionsMeta.current_page"
          :total="transactionsMeta.total"
          :page-count="transactionsMeta.per_page"
          @update:model-value="loadPage"
        />
      </div>
    </div>
  </div>
</template>
