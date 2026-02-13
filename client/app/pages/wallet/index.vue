<script setup lang="ts">
/**
 * Wallet Dashboard Page
 * Shows balance, quick actions, and recent transactions
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { wallet, stats, transactions, loading, fetchWallet, fetchStats, fetchTransactions } = useWallet()
const toast = useToast()

const transactionsLoading = ref(false)

// Fetch wallet data on mount
onMounted(async () => {
  await Promise.all([
    fetchWallet(),
    fetchStats(),
    loadTransactions()
  ])
})

const loadTransactions = async () => {
  transactionsLoading.value = true
  await fetchTransactions(1, 10)
  transactionsLoading.value = false
}

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Get transaction type icon
const getTransactionIcon = (type: string) => {
  const icons: Record<string, string> = {
    credit: 'i-lucide-arrow-down-left',
    debit: 'i-lucide-arrow-up-right',
    hold: 'i-lucide-clock',
    release: 'i-lucide-unlock',
    refund: 'i-lucide-rotate-ccw',
    adjustment: 'i-lucide-settings-2'
  }
  return icons[type] || 'i-lucide-circle'
}

// Get transaction color
const getTransactionColor = (type: string, isPositive: boolean) => {
  if (isPositive) return 'text-green-600 dark:text-green-400'
  return 'text-red-600 dark:text-red-400'
}

// Quick action cards
const quickActions = [
  { label: 'Send Money', icon: 'i-lucide-send', to: '/wallet/send', color: 'blue' },
  { label: 'Withdraw', icon: 'i-lucide-building-2', to: '/wallet/withdraw', color: 'purple' },
  { label: 'Add Money', icon: 'i-lucide-plus-circle', to: '/wallet/add', color: 'green' },
  { label: 'Transactions', icon: 'i-lucide-history', to: '/wallet/transactions', color: 'amber' }
]

// Security actions
const securityActions = computed(() => [
  { label: 'Change PIN', icon: 'i-lucide-key', to: '/wallet/change-pin', description: 'Update your wallet PIN' },
  { label: 'Reset PIN', icon: 'i-lucide-refresh-cw', to: '/wallet/reset-pin', description: 'Forgot PIN? Reset with OTP' },
  { label: 'Bank Accounts', icon: 'i-lucide-building-2', to: '/wallet/bank-accounts', description: 'Manage withdrawal accounts' }
])
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <!-- PIN Setup Banner -->
    <div
      v-if="wallet?.requires_pin_setup"
      class="glass-card p-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-l-4 border-amber-500"
    >
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
          <UIcon
            name="i-lucide-shield-alert"
            class="w-6 h-6 text-amber-600 dark:text-amber-400"
          />
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-slate-900 dark:text-white">
            Secure Your Wallet
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            Set up your 6-digit PIN and security questions to protect your wallet
          </p>
        </div>
        <UButton
          to="/wallet/setup-pin"
          color="warning"
        >
          Setup PIN
        </UButton>
      </div>
    </div>

    <!-- Balance Card -->
    <div class="glass-card overflow-hidden">
      <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 p-6 text-white">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-blue-100 text-sm mb-1">
              Available Balance
            </p>
            <div
              v-if="loading"
              class="animate-pulse"
            >
              <div class="h-10 w-48 bg-white/20 rounded" />
            </div>
            <p
              v-else
              class="text-4xl font-bold"
            >
              {{ wallet?.available_balance_formatted || '0.00' }}
            </p>
          </div>
          <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center">
            <UIcon
              name="i-lucide-wallet"
              class="w-8 h-8"
            />
          </div>
        </div>

        <!-- Balance Details -->
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white/10 rounded-xl p-3">
            <p class="text-blue-100 text-xs">
              Total Balance
            </p>
            <p class="font-semibold">
              {{ wallet?.balance_formatted || '0.00' }}
            </p>
          </div>
          <div class="bg-white/10 rounded-xl p-3">
            <p class="text-blue-100 text-xs">
              On Hold
            </p>
            <p class="font-semibold">
              {{ wallet?.hold_balance_formatted || '0.00' }}
            </p>
          </div>
          <div class="bg-white/10 rounded-xl p-3">
            <p class="text-blue-100 text-xs">
              Coins
            </p>
            <p class="font-semibold flex items-center gap-1">
              <UIcon
                name="i-lucide-coins"
                class="w-4 h-4"
              />
              {{ stats?.points || 0 }}
            </p>
          </div>
          <div class="bg-white/10 rounded-xl p-3">
            <p class="text-blue-100 text-xs">
              Status
            </p>
            <p class="font-semibold capitalize">
              {{ wallet?.status || 'Active' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <NuxtLink
        v-for="action in quickActions"
        :key="action.label"
        :to="action.to"
        class="glass-card p-4 text-center hover:shadow-lg transition-all hover:-translate-y-0.5 group"
      >
        <div
          :class="[
            'w-12 h-12 mx-auto mb-3 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110',
            action.color === 'blue' ? 'bg-blue-100 dark:bg-blue-900/30' : '',
            action.color === 'purple' ? 'bg-purple-100 dark:bg-purple-900/30' : '',
            action.color === 'green' ? 'bg-green-100 dark:bg-green-900/30' : '',
            action.color === 'amber' ? 'bg-amber-100 dark:bg-amber-900/30' : ''
          ]"
        >
          <UIcon
            :name="action.icon"
            :class="[
              'w-6 h-6',
              action.color === 'blue' ? 'text-blue-600 dark:text-blue-400' : '',
              action.color === 'purple' ? 'text-purple-600 dark:text-purple-400' : '',
              action.color === 'green' ? 'text-green-600 dark:text-green-400' : '',
              action.color === 'amber' ? 'text-amber-600 dark:text-amber-400' : ''
            ]"
          />
        </div>
        <p class="text-sm font-medium text-slate-900 dark:text-white">
          {{ action.label }}
        </p>
      </NuxtLink>
    </div>

    <!-- Security & Settings -->
    <div
      v-if="!wallet?.requires_pin_setup"
      class="glass-card p-4"
    >
      <h3 class="font-semibold text-slate-900 dark:text-white mb-4">
        Security & Settings
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <NuxtLink
          v-for="action in securityActions"
          :key="action.label"
          :to="action.to"
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        >
          <div class="w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-xl flex items-center justify-center">
            <UIcon
              :name="action.icon"
              class="w-5 h-5 text-slate-600 dark:text-slate-400"
            />
          </div>
          <div>
            <p class="font-medium text-slate-900 dark:text-white text-sm">
              {{ action.label }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ action.description }}
            </p>
          </div>
        </NuxtLink>
      </div>
    </div>

    <!-- Monthly Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="glass-card p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
            <UIcon
              name="i-lucide-trending-up"
              class="w-5 h-5 text-green-600 dark:text-green-400"
            />
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              This Month In
            </p>
            <p class="font-semibold text-slate-900 dark:text-white">
              {{ stats?.monthly_credits_formatted || '0.00' }}
            </p>
          </div>
        </div>
      </div>
      <div class="glass-card p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
            <UIcon
              name="i-lucide-trending-down"
              class="w-5 h-5 text-red-600 dark:text-red-400"
            />
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              This Month Out
            </p>
            <p class="font-semibold text-slate-900 dark:text-white">
              {{ stats?.monthly_debits_formatted || '0.00' }}
            </p>
          </div>
        </div>
      </div>
      <div class="glass-card p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
            <UIcon
              name="i-lucide-clock"
              class="w-5 h-5 text-amber-600 dark:text-amber-400"
            />
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Pending
            </p>
            <p class="font-semibold text-slate-900 dark:text-white">
              {{ stats?.pending_amount_formatted || '0.00' }}
            </p>
          </div>
        </div>
      </div>
      <div class="glass-card p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
            <UIcon
              name="i-lucide-activity"
              class="w-5 h-5 text-blue-600 dark:text-blue-400"
            />
          </div>
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Recent Activity
            </p>
            <p class="font-semibold text-slate-900 dark:text-white">
              {{ stats?.recent_transaction_count || 0 }} txns
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="glass-card">
      <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
        <h2 class="font-semibold text-slate-900 dark:text-white">
          Recent Transactions
        </h2>
        <UButton
          to="/wallet/transactions"
          variant="ghost"
          size="sm"
          trailing-icon="i-lucide-arrow-right"
        >
          View All
        </UButton>
      </div>

      <div
        v-if="transactionsLoading"
        class="p-4 space-y-3"
      >
        <div
          v-for="i in 5"
          :key="i"
          class="flex items-center gap-4 animate-pulse"
        >
          <div class="w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-xl" />
          <div class="flex-1">
            <div class="h-4 w-32 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
            <div class="h-3 w-48 bg-slate-200 dark:bg-slate-700 rounded" />
          </div>
          <div class="h-4 w-20 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
      </div>

      <div
        v-else-if="transactions.length === 0"
        class="p-8 text-center"
      >
        <UIcon
          name="i-lucide-receipt"
          class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3"
        />
        <p class="text-slate-500 dark:text-slate-400">
          No transactions yet
        </p>
      </div>

      <div
        v-else
        class="divide-y divide-slate-200 dark:divide-slate-700"
      >
        <div
          v-for="txn in transactions"
          :key="txn.uuid"
          class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
        >
          <div
            :class="[
              'w-10 h-10 rounded-xl flex items-center justify-center',
              txn.is_positive ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'
            ]"
          >
            <UIcon
              :name="getTransactionIcon(txn.type)"
              :class="[
                'w-5 h-5',
                txn.is_positive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
              ]"
            />
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-slate-900 dark:text-white truncate">
              {{ txn.description || txn.purpose }}
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ formatDate(txn.created_at) }} · {{ txn.reference_number }}
            </p>
          </div>
          <div class="text-right">
            <p
              :class="[
                'font-semibold',
                getTransactionColor(txn.type, txn.is_positive)
              ]"
            >
              {{ txn.formatted_amount }}
            </p>
            <UBadge
              :color="txn.status === 'completed' ? 'success' : txn.status === 'pending' ? 'warning' : 'neutral'"
              variant="subtle"
              size="xs"
            >
              {{ txn.status_label }}
            </UBadge>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
