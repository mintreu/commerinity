<script setup lang="ts">
/**
 * Wallet Transactions History Page
 * Full transaction list with pagination and filters
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { transactions, fetchTransactions, historyAvailable } = useWallet()

const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = 20
const includeHistory = ref(false)

// Filter state
const typeFilter = ref('')
const statusFilter = ref('')
const selectedTxn = ref<any | null>(null)
const showTxnModal = ref(false)

// Type filter options
const typeOptions = [
  { label: 'All Types', value: '' },
  { label: 'Credits', value: 'credit' },
  { label: 'Debits', value: 'debit' },
  { label: 'Holds', value: 'hold' },
  { label: 'Refunds', value: 'refund' }
]

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Completed', value: 'completed' },
  { label: 'Pending', value: 'pending' },
  { label: 'Processing', value: 'processing' },
  { label: 'Failed', value: 'failed' }
]

// Load transactions
const loadTransactions = async () => {
  loading.value = true
  const response = await fetchTransactions(currentPage.value, perPage, {
    includeHistory: includeHistory.value
  })
  if (response) {
    totalPages.value = response.meta?.last_page || 1
  }
  loading.value = false
}

onMounted(loadTransactions)

// Watch for filter changes
watch([typeFilter, statusFilter], () => {
  currentPage.value = 1
  loadTransactions()
})

const enableHistory = async () => {
  if (!historyAvailable.value) return
  includeHistory.value = true
  await loadTransactions()
}

// Pagination
const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadTransactions()
  }
}

// Format date
const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return {
    date: date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }),
    time: date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })
  }
}

// Get transaction icon
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

// Get status badge color
const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    completed: 'success',
    pending: 'warning',
    processing: 'primary',
    failed: 'error',
    refunded: 'neutral'
  }
  return colors[status] || 'neutral'
}

// Filtered transactions
const filteredTransactions = computed(() => {
  let result = transactions.value

  if (typeFilter.value) {
    result = result.filter(t => t.type === typeFilter.value)
  }

  if (statusFilter.value) {
    result = result.filter(t => t.status === statusFilter.value)
  }

  return result
})

const openTxn = (txn: any) => {
  selectedTxn.value = txn
  showTxnModal.value = true
}
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <NuxtLink
        to="/wallet"
        class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
      >
        <UIcon
          name="i-lucide-arrow-left"
          class="w-5 h-5 text-slate-600 dark:text-slate-400"
        />
      </NuxtLink>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
          Transaction History
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          View all your wallet transactions
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4">
      <div class="flex flex-wrap gap-4">
        <USelect
          v-model="typeFilter"
          :items="typeOptions"
          placeholder="Filter by type"
          class="w-40"
        />
        <USelect
          v-model="statusFilter"
          :items="statusOptions"
          placeholder="Filter by status"
          class="w-40"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="glass-card divide-y divide-slate-200 dark:divide-slate-700"
    >
      <div
        v-for="i in 10"
        :key="i"
        class="flex items-center gap-4 p-4 animate-pulse"
      >
        <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-xl" />
        <div class="flex-1">
          <div class="h-4 w-40 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
          <div class="h-3 w-56 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
        <div class="text-right">
          <div class="h-4 w-24 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
          <div class="h-5 w-16 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="filteredTransactions.length === 0"
      class="glass-card p-12"
    >
      <CommonEmptyState
        icon="i-lucide-receipt"
        title="No transactions found"
        :description="typeFilter || statusFilter ? 'Try adjusting your filters' : 'Your transaction history will appear here'"
      />
    </div>

    <!-- Transaction List -->
    <div
      v-else
      class="glass-card divide-y divide-slate-200 dark:divide-slate-700"
    >
      <div
        v-for="txn in filteredTransactions"
        :key="txn.uuid"
        class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer"
        @click="openTxn(txn)"
      >
        <!-- Icon -->
        <div
          :class="[
            'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
            txn.is_positive ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'
          ]"
        >
          <UIcon
            :name="getTransactionIcon(txn.type)"
            :class="[
              'w-6 h-6',
              txn.is_positive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
            ]"
          />
        </div>

        <!-- Details -->
        <div class="flex-1 min-w-0">
          <p class="font-medium text-slate-900 dark:text-white truncate">
            {{ txn.description || txn.purpose }}
          </p>
          <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span>{{ formatDate(txn.created_at).date }}</span>
            <span>{{ formatDate(txn.created_at).time }}</span>
            <span class="hidden sm:inline">{{ txn.reference_number }}</span>
          </div>
          <p class="text-xs text-slate-400 dark:text-slate-500 sm:hidden truncate">
            {{ txn.reference_number }}
          </p>
        </div>

        <!-- Amount & Status -->
        <div class="text-right flex-shrink-0">
          <p
            :class="[
              'font-semibold text-lg',
              txn.is_positive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
            ]"
          >
            {{ txn.formatted_amount }}
          </p>
          <UBadge
            :color="getStatusColor(txn.status) as any"
            variant="subtle"
            size="xs"
          >
            {{ txn.status_label }}
          </UBadge>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1 || historyAvailable"
      class="flex flex-col items-center justify-center gap-3"
    >
      <div
        v-if="totalPages > 1"
        class="flex items-center justify-center gap-2"
      >
        <UButton
          variant="outline"
          color="neutral"
          size="sm"
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          <UIcon
            name="i-lucide-chevron-left"
            class="w-4 h-4"
          />
        </UButton>

        <div class="flex items-center gap-1">
          <UButton
            v-for="page in Math.min(5, totalPages)"
            :key="page"
            :variant="currentPage === page ? 'solid' : 'outline'"
            :color="currentPage === page ? 'primary' : 'neutral'"
            size="sm"
            @click="goToPage(page)"
          >
            {{ page }}
          </UButton>
        </div>

        <UButton
          variant="outline"
          color="neutral"
          size="sm"
          :disabled="currentPage === totalPages"
          @click="goToPage(currentPage + 1)"
        >
          <UIcon
            name="i-lucide-chevron-right"
            class="w-4 h-4"
          />
        </UButton>
      </div>

      <div
        v-if="historyAvailable && !includeHistory && currentPage === totalPages"
        class="flex items-center justify-center"
      >
        <UButton
          variant="soft"
          color="primary"
          size="sm"
          class="rounded-full px-5"
          @click="enableHistory"
        >
          Browse older transactions
        </UButton>
      </div>
    </div>
  </div>

  <UModal v-model="showTxnModal">
    <UCard v-if="selectedTxn">
      <template #header>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
          Transaction Details
        </h3>
      </template>
      <div class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
        <div class="flex items-center justify-between">
          <span>UUID</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ selectedTxn.uuid }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Type</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ selectedTxn.type_label || selectedTxn.type }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Status</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ selectedTxn.status_label || selectedTxn.status }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Amount</span>
          <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ selectedTxn.amount_formatted }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Fee</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ selectedTxn.fee_formatted }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Net Amount</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ selectedTxn.net_amount_formatted }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Reference</span>
          <span class="font-semibold text-slate-900 dark:text-white">{{ selectedTxn.reference_number || '—' }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span>Created</span>
          <span class="font-semibold text-slate-900 dark:text-white">
            {{ formatDate(selectedTxn.created_at).date }} {{ formatDate(selectedTxn.created_at).time }}
          </span>
        </div>
        <div
          v-if="selectedTxn.description"
          class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60"
        >
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Description
          </p>
          <p class="text-slate-700 dark:text-slate-300">
            {{ selectedTxn.description }}
          </p>
        </div>
      </div>
      <template #footer>
        <div class="flex items-center justify-end">
          <UButton
            variant="ghost"
            @click="showTxnModal = false"
          >
            Close
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>
