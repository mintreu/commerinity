<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const { isMember, isPromoter } = useUserType()
const { formatCurrency } = useBranding()
const { accounts, loading, fetchAccounts } = useAffiliateFunds()

onMounted(async () => {
  if (!isMember.value && !isPromoter.value) {
    await navigateTo('/dashboard')
    return
  }
  await fetchAccounts()
})

const totalBalance = computed(() => accounts.value.reduce((sum, acc) => sum + (acc.balance || 0), 0))
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
    <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
          <UIcon name="i-lucide-vault" class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
            Fund Accounts
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
            Total balance: {{ formatCurrency(totalBalance) }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-10">
      <UIcon name="i-lucide-loader-2" class="w-7 h-7 animate-spin text-primary-500" />
    </div>

    <div v-else-if="accounts.length === 0" class="glass-card p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 text-center">
      <p class="text-sm text-slate-500">No fund accounts yet.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="account in accounts"
        :key="account.id"
        class="glass-card p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10"
      >
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-white capitalize">
              {{ account.fund_type }}
            </h3>
            <p class="text-xs text-slate-500">Balance</p>
          </div>
          <div class="text-right">
            <div class="text-lg font-bold text-slate-900 dark:text-white">
              {{ account.balance_formatted || formatCurrency(account.balance / 100) }}
            </div>
            <NuxtLink
              :to="`/affiliate/funds/${account.fund_type}`"
              class="text-xs text-primary-500 hover:underline"
            >
              View Transactions
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
