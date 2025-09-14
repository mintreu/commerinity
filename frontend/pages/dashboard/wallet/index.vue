<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

// state
const wallet = ref<any>(null)
const transactions = ref<any[]>([])
const loading = ref(true)
const creating = ref(false)
const page = ref(1)
const hasMore = ref(true)
const filters = ref({ type: 'all', status: 'all' })
const sort = ref<'desc' | 'asc'>('desc')

// modal state
const showTxModal = ref(false)
const selectedTx = ref<any>(null)

const showPinModal = ref(false)
const pinForm = ref({ pin: '', confirmPin: '' })

const showAddModal = ref(false)
const addForm = ref({ amount: '' })

const showWithdrawModal = ref(false)
const withdrawForm = ref({ amount: '', bank_account: '' })

const showSendModal = ref(false)
const sendForm = ref({ amount: '', recipient_uuid: '' })

const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)
const toast = useToast()

definePageMeta({ layout: 'dashboard' })

/**
 * Load wallet
 */
async function loadWallet() {
  try {
    loading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet`)
    if (!res?.data) return
    wallet.value = res.data ?? null
  } catch (e) {
    toast.error({ title: 'Error!', message: 'Failed to fetch wallet.' })
  } finally {
    loading.value = false
  }
}

/**
 * Load transactions
 */
async function loadTransactions(reset = false) {
  try {
    if (reset) {
      transactions.value = []
      page.value = 1
      hasMore.value = true
    }
    if (!hasMore.value) return

    const res = await useSanctumFetch(
        `${config.public.apiBase}/transactions?page=${page.value}&type=${filters.value.type}&status=${filters.value.status}&sort=${sort.value}`
    )

    if (!res?.data) return

    const newTx = res.data.data ?? []
    transactions.value.push(...newTx)
    hasMore.value = !!res.data.next_page_url
    page.value++
  } catch (e) {
    toast.error({ title: 'Error!', message: 'Failed to fetch transactions.' })
  }
}

/**
 * Unlock wallet
 */
async function unlockWallet() {
  try {
    creating.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/create`, { method: 'POST' })
    await loadWallet()
    toast.success({ title: 'Success!', message: 'Wallet unlocked successfully!' })
  } catch {
    toast.error({ title: 'Error!', message: 'Failed to create wallet.' })
  } finally {
    creating.value = false
  }
}

/**
 * Change PIN
 */
async function changePin() {
  if (pinForm.value.pin !== pinForm.value.confirmPin) {
    toast.error({ title: 'Error!', message: 'PINs do not match.' })
    return
  }
  try {
    await useSanctumFetch(`${config.public.apiBase}/wallet/change-pin`, {
      method: 'POST',
      body: pinForm.value,
    })
    toast.success({ title: 'Success!', message: 'PIN changed successfully.' })
    showPinModal.value = false
    pinForm.value = { pin: '', confirmPin: '' }
  } catch {
    toast.error({ title: 'Error!', message: 'Failed to change PIN.' })
  }
}

/**
 * Add Money
 */
async function addMoney() {
  try {
    await useSanctumFetch(`${config.public.apiBase}/wallet/add-money`, {
      method: 'POST',
      body: addForm.value,
    })
    toast.success({ title: 'Success!', message: 'Money added successfully.' })
    await loadWallet()
    showAddModal.value = false
    addForm.value = { amount: '' }
  } catch {
    toast.error({ title: 'Error!', message: 'Failed to add money.' })
  }
}

/**
 * Withdraw Money
 */
async function withdrawMoney() {
  try {
    await useSanctumFetch(`${config.public.apiBase}/wallet/withdraw`, {
      method: 'POST',
      body: withdrawForm.value,
    })
    toast.success({ title: 'Success!', message: 'Withdrawal successful.' })
    await loadWallet()
    showWithdrawModal.value = false
    withdrawForm.value = { amount: '', bank_account: '' }
  } catch {
    toast.error({ title: 'Error!', message: 'Failed to withdraw money.' })
  }
}

/**
 * Send Money
 */
async function sendMoney() {
  try {
    await useSanctumFetch(`${config.public.apiBase}/wallet/send`, {
      method: 'POST',
      body: sendForm.value,
    })
    toast.success({ title: 'Success!', message: 'Money sent successfully.' })
    await loadWallet()
    showSendModal.value = false
    sendForm.value = { amount: '', recipient_uuid: '' }
  } catch {
    toast.error({ title: 'Error!', message: 'Failed to send money.' })
  }
}

onMounted(async () => {
  isLoading.value = true
  await loadWallet()
  await loadTransactions(true)
  isLoading.value = false
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 p-4 sm:p-6">
    <!-- Loader -->
    <GlobalLoader v-if="isLoading || loading" />

    <!-- Wallet locked -->
    <div v-else-if="!wallet" class="flex items-center justify-center min-h-screen">
      <div class="max-w-sm w-full bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
        <h2 class="text-xl font-bold mb-2">Wallet Locked 🔒</h2>
        <p class="mb-4 text-gray-600 dark:text-gray-400">Unlock to create your wallet.</p>
        <button
            @click="unlockWallet"
            :disabled="creating"
            class="w-full px-4 py-2 bg-indigo-600 text-white rounded font-medium hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ creating ? 'Creating...' : 'Unlock Wallet' }}
        </button>
      </div>
    </div>

    <!-- Wallet dashboard -->
    <div v-else class="space-y-6">
      <!-- Wallet Info -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-lg font-bold">My Wallet</h2>
          <p class="text-sm text-gray-500">UUID: {{ wallet.uuid }}</p>
          <p class="text-3xl font-extrabold mt-2">{{ wallet.balance }}</p>
        </div>
        <!-- Actions -->
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
          <button @click="showAddModal = true" class="flex items-center gap-1 px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            <Icon name="mdi:wallet-plus" /> Add Money
          </button>
          <button @click="showWithdrawModal = true" class="flex items-center gap-1 px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
            <Icon name="mdi:bank-transfer" /> Withdraw
          </button>
          <button @click="showSendModal = true" class="flex items-center gap-1 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            <Icon name="mdi:send" /> Send
          </button>
          <button @click="showPinModal = true" class="flex items-center gap-1 px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            <Icon name="mdi:key-change" /> Change PIN
          </button>
        </div>
      </div>

      <!-- Transactions -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-2">
          <h3 class="text-lg font-semibold">Transactions</h3>
          <div class="flex flex-wrap gap-2">
            <select v-model="filters.type" @change="loadTransactions(true)" class="px-2 py-1 border rounded text-sm bg-white dark:bg-gray-700 dark:text-gray-100">
              <option value="all">All Types</option>
              <option value="credit">Credit</option>
              <option value="debit">Debit</option>
            </select>
            <select v-model="filters.status" @change="loadTransactions(true)" class="px-2 py-1 border rounded text-sm bg-white dark:bg-gray-700 dark:text-gray-100">
              <option value="all">All Status</option>
              <option value="success">Success</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
            <select v-model="sort" @change="loadTransactions(true)" class="px-2 py-1 border rounded text-sm bg-white dark:bg-gray-700 dark:text-gray-100">
              <option value="desc">Newest</option>
              <option value="asc">Oldest</option>
            </select>
          </div>
        </div>

        <!-- Table for desktop -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
            <tr class="border-b dark:border-gray-700">
              <th class="text-left py-2 px-3">Type</th>
              <th class="text-left py-2 px-3">Purpose</th>
              <th class="text-left py-2 px-3">Amount</th>
              <th class="text-left py-2 px-3">Status</th>
              <th class="text-left py-2 px-3">Date</th>
              <th class="py-2 px-3">Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="tx in transactions" :key="tx.uuid" class="border-b dark:border-gray-700">
              <td class="py-2 px-3 capitalize">{{ tx.type }}</td>
              <td class="py-2 px-3">{{ tx.purpose ?? 'N/A' }}</td>
              <td class="py-2 px-3 font-bold" :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                {{ tx.amount }}
              </td>
              <td class="py-2 px-3">{{ tx.status }}</td>
              <td class="py-2 px-3">{{ new Date(tx.created_at).toLocaleString() }}</td>
              <td class="py-2 px-3 text-center">
                <button @click="selectedTx = tx; showTxModal = true" class="text-indigo-600 hover:underline">View</button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile list -->
        <div class="sm:hidden space-y-3">
          <div v-for="tx in transactions" :key="tx.uuid" class="p-3 border rounded dark:border-gray-700">
            <div class="flex justify-between items-center">
              <p class="capitalize font-medium">{{ tx.type }}</p>
              <p class="font-bold" :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-600'">{{ tx.amount }}</p>
            </div>
            <p class="text-sm text-gray-500">{{ tx.purpose ?? 'N/A' }}</p>
            <p class="text-xs text-gray-400">{{ new Date(tx.created_at).toLocaleString() }}</p>
            <button @click="selectedTx = tx; showTxModal = true" class="mt-2 text-xs text-indigo-600">View Details</button>
          </div>
        </div>

        <!-- Load more -->
        <div v-if="hasMore" class="mt-4 text-center">
          <button @click="loadTransactions()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Load More</button>
        </div>
      </div>
    </div>

    <!-- Transaction Modal -->
    <div v-if="showTxModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full relative">
        <button @click="showTxModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">✕</button>
        <h3 class="text-lg font-bold mb-4">Transaction Details</h3>
        <pre class="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded max-h-64 overflow-auto">{{ selectedTx }}</pre>
      </div>
    </div>

    <!-- Change PIN Modal -->
    <div v-if="showPinModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full relative">
        <button @click="showPinModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">✕</button>
        <h3 class="text-lg font-bold mb-4">Change PIN</h3>
        <input v-model="pinForm.pin" type="password" placeholder="Enter new PIN" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <input v-model="pinForm.confirmPin" type="password" placeholder="Confirm new PIN" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <button @click="changePin" class="w-full bg-indigo-600 text-white px-4 py-2 rounded">Update PIN</button>
      </div>
    </div>

    <!-- Add Money Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full relative">
        <button @click="showAddModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">✕</button>
        <h3 class="text-lg font-bold mb-4">Add Money</h3>
        <input v-model="addForm.amount" type="number" placeholder="Enter amount" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <button @click="addMoney" class="w-full bg-green-600 text-white px-4 py-2 rounded">Add</button>
      </div>
    </div>

    <!-- Withdraw Money Modal -->
    <div v-if="showWithdrawModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full relative">
        <button @click="showWithdrawModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">✕</button>
        <h3 class="text-lg font-bold mb-4">Withdraw Money</h3>
        <input v-model="withdrawForm.amount" type="number" placeholder="Enter amount" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <input v-model="withdrawForm.bank_account" type="text" placeholder="Bank account number" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <button @click="withdrawMoney" class="w-full bg-yellow-600 text-white px-4 py-2 rounded">Withdraw</button>
      </div>
    </div>

    <!-- Send Money Modal -->
    <div v-if="showSendModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full relative">
        <button @click="showSendModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">✕</button>
        <h3 class="text-lg font-bold mb-4">Send Money</h3>
        <input v-model="sendForm.amount" type="number" placeholder="Enter amount" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <input v-model="sendForm.recipient_uuid" type="text" placeholder="Recipient Wallet UUID" class="w-full px-3 py-2 border rounded mb-3 bg-white dark:bg-gray-700 dark:text-gray-100" />
        <button @click="sendMoney" class="w-full bg-blue-600 text-white px-4 py-2 rounded">Send</button>
      </div>
    </div>
  </div>
</template>
