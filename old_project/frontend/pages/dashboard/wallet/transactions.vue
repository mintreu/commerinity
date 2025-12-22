<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900">
    <div class="max-w-7xl mx-auto p-4 md:p-6 lg:p-8 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <NuxtLink to="/dashboard/wallet" class="w-10 h-10 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-colors">
            <Icon name="mdi:arrow-left" class="w-5 h-5 text-slate-600 dark:text-slate-400" />
          </NuxtLink>
          <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white mb-1">Transactions</h1>
            <p class="text-slate-600 dark:text-slate-400">Complete transaction history</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <!-- Type Filter -->
          <div class="flex-1">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Transaction Type</label>
            <select v-model="filters.type" @change="loadTransactions(true)" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
              <option value="all">All Types</option>
              <option value="credited">Credits</option>
              <option value="debited">Debits</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="flex-1">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
            <select v-model="filters.status" @change="loadTransactions(true)" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="failed">Failed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Sort -->
          <div class="flex-1">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Sort By</label>
            <select v-model="sort" @change="loadTransactions(true)" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
              <option value="desc">Newest First</option>
              <option value="asc">Oldest First</option>
            </select>
          </div>

          <!-- Results Count -->
          <div class="flex-1 flex items-end">
            <div class="w-full px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
              <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase mb-1">Results</div>
              <div class="text-lg font-black text-blue-700 dark:text-blue-300">{{ totalCount }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && transactions.length === 0" class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-12">
        <div class="flex flex-col items-center justify-center">
          <div class="w-16 h-16 border-4 border-slate-200 dark:border-slate-700 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin mb-4"></div>
          <p class="text-slate-600 dark:text-slate-400 font-semibold">Loading transactions...</p>
        </div>
      </div>

      <!-- Transactions List -->
      <div v-else-if="transactions.length > 0" class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full">
            <thead>
            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
              <th class="text-left px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:swap-horizontal" class="w-4 h-4" />
                  <span>Type</span>
                </div>
              </th>
              <th class="text-left px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:message-text" class="w-4 h-4" />
                  <span>Purpose</span>
                </div>
              </th>
              <th class="text-left px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:currency-inr" class="w-4 h-4" />
                  <span>Amount</span>
                </div>
              </th>
              <th class="text-left px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:check-circle" class="w-4 h-4" />
                  <span>Status</span>
                </div>
              </th>
              <th class="text-left px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:calendar" class="w-4 h-4" />
                  <span>Date</span>
                </div>
              </th>
              <th class="text-center px-6 py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                <div class="flex items-center justify-center gap-2">
                  <Icon name="mdi:cog" class="w-4 h-4" />
                  <span>Actions</span>
                </div>
              </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <tr
                v-for="tx in transactions"
                :key="tx.uuid"
                :data-tx="tx.uuid"
                :class="[
                  'hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors',
                  highlightedTx === tx.uuid ? 'bg-blue-50 dark:bg-blue-900/20 highlight-animation' : ''
                ]"
            >
              <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold" :class="getTypeClass(tx.type)">
                    <Icon :name="getTypeIcon(tx.type)" class="w-4 h-4" />
                    <span>{{ tx.type }}</span>
                  </span>
              </td>
              <td class="px-6 py-4">
                <div class="font-semibold text-slate-900 dark:text-white text-sm">{{ tx.purpose || 'N/A' }}</div>
                <div v-if="tx.integration" class="text-xs text-slate-500 dark:text-slate-400 mt-1">via {{ tx.integration }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="font-black text-sm" :class="tx.type === 'Credited' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                  {{ tx.type === 'Credited' ? '+' : '-' }}{{ tx.amount }}
                </div>
              </td>
              <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" :class="getStatusClass(tx.status)">
                    <Icon :name="getStatusIcon(tx.status)" class="w-3.5 h-3.5" />
                    <span>{{ tx.status }}</span>
                  </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-slate-900 dark:text-white font-medium">{{ formatDate(tx.created_at) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ formatTime(tx.created_at) }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="viewTransaction(tx)" class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors" title="View Details">
                    <Icon name="mdi:eye" class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden p-4 space-y-4">
          <div
              v-for="tx in transactions"
              :key="tx.uuid"
              :data-tx="tx.uuid"
              :class="[
              'p-4 rounded-xl border transition-all cursor-pointer',
              highlightedTx === tx.uuid
                ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 highlight-animation'
                : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
            ]"
              @click="viewTransaction(tx)"
          >
            <!-- Header -->
            <div class="flex items-start justify-between mb-3">
              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold" :class="getTypeClass(tx.type)">
                <Icon :name="getTypeIcon(tx.type)" class="w-4 h-4" />
                <span>{{ tx.type }}</span>
              </span>
              <div class="font-black text-base" :class="tx.type === 'Credited' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                {{ tx.type === 'Credited' ? '+' : '-' }}{{ tx.amount }}
              </div>
            </div>

            <!-- Purpose -->
            <div class="mb-3">
              <div class="font-bold text-slate-900 dark:text-white mb-1">{{ tx.purpose || 'Transaction' }}</div>
              <div v-if="tx.integration" class="text-xs text-slate-500 dark:text-slate-400">via {{ tx.integration }}</div>
            </div>

            <!-- Status & Date -->
            <div class="flex items-center justify-between text-sm">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold" :class="getStatusClass(tx.status)">
                <Icon :name="getStatusIcon(tx.status)" class="w-3.5 h-3.5" />
                <span>{{ tx.status }}</span>
              </span>
              <div class="text-xs text-slate-500 dark:text-slate-400 text-right">
                <div class="font-semibold text-slate-900 dark:text-white">{{ formatDate(tx.created_at) }}</div>
                <div>{{ formatTime(tx.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Load More / Pagination -->
        <div v-if="hasMore" class="p-6 text-center border-t border-slate-200 dark:border-slate-700">
          <button @click="loadMore" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors font-semibold">
            <Icon :name="loading ? 'mdi:loading' : 'mdi:refresh'" class="w-5 h-5" :class="{ 'animate-spin': loading }" />
            <span>{{ loading ? 'Loading...' : 'Load More' }}</span>
          </button>
        </div>

        <!-- End of Results -->
        <div v-else-if="transactions.length > 0" class="p-4 text-center border-t border-slate-200 dark:border-slate-700">
          <p class="text-sm text-slate-500 dark:text-slate-400">No more transactions to load</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-12">
        <div class="flex flex-col items-center justify-center text-center">
          <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mb-6">
            <Icon name="mdi:receipt-text-outline" class="w-12 h-12 text-slate-400 dark:text-slate-500" />
          </div>
          <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">No Transactions Found</h3>
          <p class="text-slate-600 dark:text-slate-400 mb-6 max-w-md">
            {{ filters.type !== 'all' || filters.status !== 'all' ? 'Try adjusting your filters to see more results.' : 'Your transaction history will appear here.' }}
          </p>
          <div class="flex gap-3">
            <button v-if="filters.type !== 'all' || filters.status !== 'all'" @click="clearFilters" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg font-semibold transition-colors">
              Clear Filters
            </button>
            <NuxtLink to="/dashboard/wallet" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
              Back to Wallet
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Detail Modal -->
    <Teleport to="body">
      <div v-if="showDetailModal && selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click="showDetailModal = false">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full shadow-2xl p-6 max-h-[90vh] overflow-y-auto" @click.stop>
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:receipt-text" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Transaction Details</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Complete information</p>
              </div>
            </div>
            <button @click="showDetailModal = false" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
              <Icon name="mdi:close" class="w-5 h-5" />
            </button>
          </div>

          <div class="space-y-4">
            <!-- Amount Display -->
            <div class="p-6 rounded-2xl text-center" :class="selectedTransaction.type === 'Credited' ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20'">
              <div class="text-sm font-bold mb-2" :class="selectedTransaction.type === 'Credited' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'">
                {{ selectedTransaction.type === 'Credited' ? 'Money Received' : 'Money Sent' }}
              </div>
              <div class="text-4xl font-black" :class="selectedTransaction.type === 'Credited' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                {{ selectedTransaction.type === 'Credited' ? '+' : '-' }}{{ selectedTransaction.amount }}
              </div>
            </div>

            <!-- Transaction Info -->
            <div class="space-y-3">
              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Transaction ID</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">{{ selectedTransaction.uuid }}</span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Type</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ selectedTransaction.type }}</span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Purpose</span>
                <span class="font-bold text-slate-900 dark:text-white text-right">{{ selectedTransaction.purpose || 'N/A' }}</span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Status</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold" :class="getStatusClass(selectedTransaction.status)">
                  <Icon :name="getStatusIcon(selectedTransaction.status)" class="w-3.5 h-3.5" />
                  <span>{{ selectedTransaction.status }}</span>
                </span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Verified</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold" :class="selectedTransaction.verified ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'">
                  <Icon :name="selectedTransaction.verified ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3.5 h-3.5" />
                  <span>{{ selectedTransaction.verified ? 'Yes' : 'No' }}</span>
                </span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Created</span>
                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ new Date(selectedTransaction.created_at).toLocaleString('en-IN') }}</span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Updated</span>
                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ new Date(selectedTransaction.updated_at).toLocaleString('en-IN') }}</span>
              </div>

              <div v-if="selectedTransaction.integration" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Payment Gateway</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ selectedTransaction.integration }}</span>
              </div>

              <div v-if="selectedTransaction.provider_transaction_id" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Gateway TXN ID</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white text-xs break-all">{{ selectedTransaction.provider_transaction_id }}</span>
              </div>

              <div v-if="selectedTransaction.expire_at" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm text-slate-600 dark:text-slate-400">Expires At</span>
                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ new Date(selectedTransaction.expire_at).toLocaleString('en-IN') }}</span>
              </div>
            </div>

            <!-- Action Button -->
            <button @click="downloadPDF(selectedTransaction)" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-bold transition-all shadow-lg">
              <Icon name="mdi:download" class="w-5 h-5" />
              <span>Download Receipt PDF</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const toast = useToast()
const route = useRoute()

// State
const loading = ref(false)
const transactions = ref<any[]>([])
const page = ref(1)
const hasMore = ref(true)
const totalCount = ref(0)

// Filters
const filters = ref({
  type: 'all',
  status: 'all'
})
const sort = ref('desc')

// Modals
const showDetailModal = ref(false)
const selectedTransaction = ref<any>(null)

// Highlight transaction from URL
const highlightedTx = computed(() => route.query.highlight as string)

// Utils
function getTypeClass(type: string) {
  const t = type.toLowerCase()
  if (t === 'credited') return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
  if (t === 'debited') return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  return 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
}

function getTypeIcon(type: string) {
  const t = type.toLowerCase()
  if (t === 'credited') return 'mdi:arrow-down-circle'
  if (t === 'debited') return 'mdi:arrow-up-circle'
  return 'mdi:swap-horizontal'
}

function getStatusClass(status: string) {
  const s = status.toLowerCase()
  if (s === 'completed') return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
  if (s === 'pending' || s === 'processing') return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'
  if (s === 'failed' || s === 'cancelled') return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  return 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'
}

function getStatusIcon(status: string) {
  const s = status.toLowerCase()
  if (s === 'completed') return 'mdi:check-circle'
  if (s === 'pending') return 'mdi:clock'
  if (s === 'processing') return 'mdi:sync'
  if (s === 'failed' || s === 'cancelled') return 'mdi:close-circle'
  return 'mdi:help-circle'
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

function formatTime(iso: string) {
  return new Date(iso).toLocaleTimeString('en-IN', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

// API
async function loadTransactions(reset = false) {
  if (reset) {
    transactions.value = []
    page.value = 1
    hasMore.value = true
  }

  if (!hasMore.value && !reset) return

  try {
    loading.value = true
    const params = new URLSearchParams({
      page: page.value.toString(),
      type: filters.value.type,
      status: filters.value.status,
      direction: sort.value,
      per_page: '20'
    })

    const res = await useSanctumFetch(`${config.public.apiBase}/transactions?${params}`)
    const newTransactions = res?.data || []

    if (reset) {
      transactions.value = newTransactions
    } else {
      transactions.value.push(...newTransactions)
    }

    hasMore.value = !!res?.links?.next
    totalCount.value = res?.meta?.total || transactions.value.length
    page.value++
  } catch (e: any) {
    console.error('Error loading transactions:', e)
    toast.error({ title: 'Error', message: e?.data?.message || 'Failed to load transactions.' })
  } finally {
    loading.value = false
  }
}

function loadMore() {
  loadTransactions(false)
}

function clearFilters() {
  filters.value = { type: 'all', status: 'all' }
  sort.value = 'desc'
  loadTransactions(true)
}

async function viewTransaction(tx: any) {
  try {
    loading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/transactions/${tx.uuid}`)
    selectedTransaction.value = res?.data || tx
    showDetailModal.value = true
  } catch (e: any) {
    console.error('Error fetching transaction details:', e)
    // Fallback to existing transaction data
    selectedTransaction.value = tx
    showDetailModal.value = true
  } finally {
    loading.value = false
  }
}

function downloadPDF(tx: any) {
  // Open PDF in new tab
  const pdfUrl = `${config.public.apiBase}/transactions/${tx.uuid}/request_pdf`
  window.open(pdfUrl, '_blank')
  toast.success({ title: 'Success', message: 'Opening receipt in new tab...' })
}

// Watchers
watch(() => route.query.highlight, (newVal) => {
  if (newVal) {
    setTimeout(() => {
      const el = document.querySelector(`[data-tx="${newVal}"]`)
      el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }, 500)
  }
})

onMounted(() => {
  loadTransactions(true)
})
</script>

<style scoped>
@keyframes highlight {
  0%, 100% { background-color: transparent; }
  50% { background-color: rgba(59, 130, 246, 0.2); }
}

.highlight-animation {
  animation: highlight 2s ease-in-out 3;
}
</style>
