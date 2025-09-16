<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast, navigateTo } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({ layout: 'dashboard' })

/* Shared state */
const config = useRuntimeConfig()
const toast = useToast()
const isLoading = useState('pageLoading', () => true)

/* Wallet data */
const wallet = ref<any>(null)
/* Default beneficiary from wallet API (when exists) */
const defaultBeneficiary = ref<any>(null)

/* Robust balance parsing */
const balance = computed(() => {
  const raw = wallet.value?.balance_numeric ?? wallet.value?.balance ?? 0
  const str = String(raw)
  const cleaned = str.replace(/[^\d.-]/g, '')
  const num = Number(cleaned)
  return Number.isFinite(num) ? num : 0
})

const walletQr = computed<string | null>(() => wallet.value?.qr || null)

/* Transactions (preview + full) */
const transactions = ref<any[]>([])
const page = ref(1)
const hasMore = ref(true)
const filters = ref({ type: 'all', status: 'all' })
const sort = ref<'desc'|'asc'>('desc')

/* UI: full-page tabs */
type ViewKey = 'home'|'add'|'send'|'transactions'|'pin'
const currentView = ref<ViewKey>('home')

/* Forms + processing */
const processing = ref(false)

/* Add money */
const addForm = ref({ amount: '' })

/* Withdraw (with modal) */
const withdrawForm = ref({ amount: '', pin: '' })
const withdrawOpen = ref(false)
const withdrawErrors = ref<Record<string, string>>({})

/* Send money with pin + purpose */
const sendForm = ref({ amount: '', recipient_uuid: '', pin: '', purpose: '' })
const sendErrors = ref<Record<string, string>>({})

/* Change PIN with reveal toggles */
const pinForm = ref({ pin: '', confirmPin: '' })
const showPin = ref(false)
const showConfirmPin = ref(false)

/* QR modal (wallet QR enlarge) */
const showQrModal = ref(false)

/* Scanner modal (Send) using nuxt-qrcode <QrcodeStream> */
const scannerOpen = ref(false)
const scanning = ref(false)
type DetectedBarcode = { rawValue: string }
function onDetect(detected: DetectedBarcode[]) {
  const first = Array.isArray(detected) && detected.length ? detected : null
  const val = first?.rawValue?.trim() || ''
  if (val) {
    sendForm.value.recipient_uuid = val
    toast.success({ title: 'QR', message: 'Recipient detected from QR.' })
  } else {
    toast.error({ title: 'QR', message: 'Invalid QR content.' })
  }
  scanning.value = false
  scannerOpen.value = false
}
function onScanError(err: Error) {
  console.error('QR scan error', err)
  toast.error({ title: 'QR', message: err?.message || 'Camera or scanning error.' })
  scanning.value = false
}

/* Guards and helpers */
const MIN_WITHDRAW = 100
const hasBeneficiary = computed(() => !!defaultBeneficiary.value)
const canWithdraw = computed(() => balance.value >= MIN_WITHDRAW && hasBeneficiary.value)

/* Normalize GET /wallet for both cases */
function normalizeWalletResponse(res: any) {
  const root = res?.data ?? {}
  const hasNestedWalletField = Object.prototype.hasOwnProperty.call(root, 'wallet')
  const walletObj = hasNestedWalletField ? root.wallet : root
  const finalWallet = walletObj && typeof walletObj === 'object' && (walletObj.uuid || walletObj.qr || walletObj.balance !== undefined)
      ? walletObj
      : null
  const txs = Array.isArray(root.transactions) ? root.transactions : []
  const ben = root.beneficiary ?? null
  return { wallet: finalWallet, transactions: txs, beneficiary: ben }
}

/* Loaders */
async function loadWallet() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet`)
    const { wallet: w, transactions: txs, beneficiary: ben } = normalizeWalletResponse(res)
    wallet.value = w
    defaultBeneficiary.value = ben
    if (Array.isArray(txs) && txs.length > 0) {
      transactions.value = txs
      hasMore.value = false
    }
  } catch {
    wallet.value = null
    defaultBeneficiary.value = null
    console.log('Failed to fetch wallet.')
  }
}

async function loadTransactions(reset = false) {
  try {
    if (reset) { transactions.value = []; page.value = 1; hasMore.value = true }
    if (!hasMore.value) return
    const q = new URLSearchParams({
      page: String(page.value),
      type: filters.value.type,
      status: filters.value.status,
      sort: sort.value
    }).toString()
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet/transactions?${q}`)
    const list = res?.data?.data ?? (Array.isArray(res?.data) ? res?.data : [])
    transactions.value.push(...list)
    hasMore.value = !!res?.data?.next_page_url
    page.value++
  } catch {
    console.log('Failed to fetch transactions.')
  }
}

/* Actions */
async function unlockWallet() {
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/create`, { method: 'POST' })
    await loadWallet()
    toast.success({ title: 'Wallet', message: 'Wallet unlocked successfully!' })
  } catch {
    toast.error({ title: 'Wallet', message: 'Failed to create wallet.' })
  } finally {
    processing.value = false
  }
}

/* Add money: redirect to checkout if backend returns redirect url */
async function addMoney() {
  const amount = Number(addForm.value.amount || 0)
  if (!amount || amount <= 0) {
    toast.error({ title: 'Add Money', message: 'Enter a valid amount.' })
    return
  }
  try {
    processing.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/wallet/add-money`, {
      method: 'POST',
      body: { amount }
    })
    const success = !!(res?.data?.success ?? res?.success)
    const redirectUrl = res?.data?.redirect ?? res?.data?.redirect_url ?? res?.redirect ?? res?.redirect_url
    if (success && redirectUrl && typeof redirectUrl === 'string') {
      if (/^https?:\/\//i.test(redirectUrl)) {
        window.location.assign(redirectUrl)
      } else {
        await navigateTo(redirectUrl)
      }
      return
    }
    toast.success({ title: 'Add Money', message: res?.data?.message || 'Proceed to checkout.' })
    await loadWallet()
    addForm.value = { amount: '' }
    currentView.value = 'home'
  } catch (e: any) {
    toast.error({ title: 'Add Money', message: e?.data?.message || 'Failed to add money.' })
  } finally {
    processing.value = false
  }
}

/* Withdraw: open modal and submit with pin */
function openWithdraw() {
  // if (!canWithdraw.value) {
  //   // Button is disabled in UI, but keep guard
  //   return
  // }
  withdrawErrors.value = {}
  withdrawForm.value = { amount: '', pin: '' }
  withdrawOpen.value = true
}
function closeWithdraw() {
  withdrawOpen.value = false
}

async function submitWithdraw() {
  const amount = Number(withdrawForm.value.amount || 0)
  withdrawErrors.value = {}
  if (!hasBeneficiary.value) {
    toast.error({ title: 'Withdraw', message: 'Add a beneficiary to withdraw.' })
    return
  }
  if (!amount || amount < MIN_WITHDRAW) {
    withdrawErrors.value.amount = `Minimum withdrawal is ₹${MIN_WITHDRAW}.`
    return
  }
  if (amount > balance.value) {
    withdrawErrors.value.amount = 'Amount exceeds wallet balance.'
    return
  }
  if (!withdrawForm.value.pin) {
    withdrawErrors.value.pin = 'PIN is required.'
    return
  }
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/withdraw`, {
      method: 'POST',
      body: { amount, pin: withdrawForm.value.pin }
    })
    toast.success({ title: 'Withdraw', message: 'Withdrawal request placed.' })
    await loadWallet()
    closeWithdraw()
    currentView.value = 'home'
  } catch (e: any) {
    const errs = e?.data?.errors ?? e?.errors
    if (errs && typeof errs === 'object') {
      Object.entries(errs).forEach(([k, v]) => {
        withdrawErrors.value[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
      })
    }
    toast.error({ title: 'Withdraw', message: e?.data?.message || 'Failed to withdraw.' })
  } finally {
    processing.value = false
  }
}

/* Change PIN with reveal toggles */
async function changePin() {
  if (!pinForm.value.pin || pinForm.value.pin !== pinForm.value.confirmPin) {
    toast.error({ title: 'PIN', message: 'PINs do not match.' })
    return
  }
  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/change-pin`, {
      method: 'POST', body: { pin: pinForm.value.pin, confirmPin: pinForm.value.confirmPin }
    })
    toast.success({ title: 'PIN', message: 'PIN changed successfully.' })
    pinForm.value = { pin: '', confirmPin: '' }
    currentView.value = 'home'
  } catch (e: any) {
    toast.error({ title: 'PIN', message: e?.data?.message || 'Failed to change PIN.' })
  } finally {
    processing.value = false
  }
}

/* Send: with pin and purpose */
async function sendMoney() {
  sendErrors.value = {}
  const amount = Number(sendForm.value.amount || 0)
  if (!amount || amount <= 0) {
    sendErrors.value.amount = 'Enter a valid amount.'
  }
  if (!sendForm.value.recipient_uuid) {
    sendErrors.value.recipient_uuid = 'Recipient wallet UUID is required.'
  }
  if (!sendForm.value.pin) {
    sendErrors.value.pin = 'PIN is required.'
  }
  if (!sendForm.value.purpose) {
    sendErrors.value.purpose = 'Purpose is required.'
  }
  if (Object.keys(sendErrors.value).length > 0) return

  if (amount > balance.value) {
    sendErrors.value.amount = 'Amount exceeds wallet balance.'
    return
  }

  try {
    processing.value = true
    await useSanctumFetch(`${config.public.apiBase}/wallet/send`, {
      method: 'POST',
      body: {
        amount,
        recipient_uuid: sendForm.value.recipient_uuid,
        pin: sendForm.value.pin,
        purpose: sendForm.value.purpose
      }
    })
    toast.success({ title: 'Send', message: 'Money sent successfully.' })
    await loadWallet()
    sendForm.value = { amount: '', recipient_uuid: '', pin: '', purpose: '' }
    currentView.value = 'home'
  } catch (e: any) {
    const errs = e?.data?.errors ?? e?.errors
    if (errs && typeof errs === 'object') {
      Object.entries(errs).forEach(([k, v]) => {
        sendErrors.value[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
      })
    }
    toast.error({ title: 'Send', message: e?.data?.message || 'Failed to send money.' })
  } finally {
    processing.value = false
  }
}

/* Navigation */
function go(view: ViewKey) {
  currentView.value = view
  if (view === 'transactions') loadTransactions(true)
}
function backHome() {
  currentView.value = 'home'
}

/* Mounted */
onMounted(async () => {
  isLoading.value = true
  await Promise.all([loadWallet(), loadTransactions(true)])
  isLoading.value = false
})

/* Display helpers */
const maskedAccount = computed(() => {
  const n = defaultBeneficiary.value?.account_number || ''
  return n ? `•••• ${String(n).slice(-4)}` : (defaultBeneficiary.value?.upi_handle || 'Not linked')
})
const isUpiBeneficiary = computed(() => {
  const ben = defaultBeneficiary.value
  if (!ben) return false
  const t = (ben.type || '').toString().toLowerCase()
  return !!ben.upi_handle || t.includes('upi')
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 dark:from-gray-900 dark:to-gray-950 text-gray-800 dark:text-gray-100">
    <!-- Global Loader -->
    <GlobalLoader v-if="isLoading" />

    <div v-else class="mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8">
      <!-- Wallet locked -->
      <div v-if="!wallet" class="min-h-[60vh] flex items-center justify-center">
        <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl p-6 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow-xl text-center">
          <h2 class="text-xl font-bold">Wallet Locked</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Unlock to create a new wallet and start transacting securely.</p>
          <button
              class="mt-5 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow hover:shadow-lg disabled:opacity-50"
              :disabled="processing"
              @click="unlockWallet"
          >
            <span class="i-lucide-wallet w-4 h-4" v-if="!processing"></span>
            <span class="i-lucide-loader-2 w-4 h-4 animate-spin" v-else></span>
            <span>{{ processing ? 'Creating...' : 'Unlock Wallet' }}</span>
          </button>
        </div>
      </div>

      <!-- Wallet Home -->
      <div v-else-if="currentView === 'home'" class="space-y-6">
        <div class="bg-white/95 dark:bg-gray-900/90 backdrop-blur rounded-2xl p-5 md:p-6 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow-xl">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">My Wallet</p>
              <h1 class="text-2xl font-semibold">₹ {{ balance.toLocaleString('en-IN', { maximumFractionDigits: 2 }) }}</h1>
              <p class="text-xs text-gray-500">UUID: {{ wallet.uuid }}</p>
              <p class="mt-1 text-xs text-gray-500">
                Beneficiary: <span class="font-medium">{{ maskedAccount }}</span>
                <NuxtLink class="ml-2 text-indigo-600 hover:underline" to="/dashboard/wallet/beneficiary">Manage beneficiaries</NuxtLink>
              </p>
            </div>

            <!-- QR mini card -->
            <div v-if="walletQr" class="shrink-0">
              <div
                  class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl ring-1 ring-gray-200 dark:ring-gray-800 bg-white dark:bg-gray-900 shadow cursor-pointer overflow-hidden grid place-items-center"
                  @click="showQrModal = true"
                  title="Tap to enlarge"
              >
                <img :src="walletQr" alt="Wallet QR" class="w-full h-full object-contain" />
              </div>
              <p class="mt-1 text-[11px] text-gray-500 text-center">Tap to enlarge</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-4 hidden sm:flex items-center gap-2">
            <button class="px-3 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow" @click="go('add')">Add</button>

<!--            <button class="px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow" @click="openWithdraw" :disabled="!canWithdraw">Withdraw</button>-->

            <button
                class="px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow"
                @click="openWithdraw"
            >
              Withdraw
            </button>

            <button class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow" @click="go('send')">Send</button>
            <button class="px-3 py-2 rounded-lg bg-gray-700 text-white hover:bg-gray-800 shadow" @click="go('pin')">Change PIN</button>
          </div>
          <div class="mt-4 sm:hidden grid grid-cols-2 gap-2">
            <button class="px-3 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow" @click="go('add')">Add</button>
            <button class="px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow" @click="openWithdraw" :disabled="!canWithdraw">Withdraw</button>
            <button class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow" @click="go('send')">Send</button>
            <button class="px-3 py-2 rounded-lg bg-gray-700 text-white hover:bg-gray-800 shadow" @click="go('pin')">Change PIN</button>
          </div>
        </div>

        <!-- Stats, Overview, Transactions preview (unchanged) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="grid grid-cols-2 gap-4 lg:col-span-1">
            <div class="rounded-xl p-4 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow">
              <p class="text-xs text-gray-500">Available</p>
              <p class="mt-1 text-xl font-semibold">₹ {{ balance.toLocaleString('en-IN') }}</p>
            </div>
            <div class="rounded-xl p-4 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow">
              <p class="text-xs text-gray-500">Today</p>
              <p class="mt-1 text-xl font-semibold">₹ {{ (transactions?.amount || 0).toLocaleString('en-IN') }}</p>
            </div>
            <div class="rounded-xl p-4 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow col-span-2">
              <p class="text-xs text-gray-500">Last transaction</p>
              <p class="mt-1 text-base font-medium truncate">{{ transactions?.purpose ?? '—' }}</p>
            </div>
          </div>

          <div class="lg:col-span-2 rounded-2xl p-4 md:p-6 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold">Spending Overview</h3>
              <select v-model="filters.type" @change="loadTransactions(true)" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">
                <option value="all">All</option>
                <option value="debit">Debits</option>
                <option value="credit">Credits</option>
              </select>
            </div>
            <div class="mt-4 h-48 sm:h-64 rounded-xl bg-gradient-to-r from-indigo-50 to-indigo-100 dark:from-indigo-950 dark:to-indigo-900 grid place-items-center text-xs text-indigo-700 dark:text-indigo-200">
              Chart placeholder: will be loaded from analytics endpoint later.
            </div>
          </div>
        </div>

        <div class="rounded-2xl p-4 md:p-6 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Recent Transactions</h3>
            <button class="text-indigo-600 hover:underline" @click="go('transactions')">View all</button>
          </div>
          <div class="mt-3 divide-y dark:divide-gray-800">
            <div v-for="tx in transactions.slice(0,5)" :key="tx.uuid" class="py-3 flex items-center justify-between">
              <div>
                <p class="text-sm font-medium capitalize">{{ tx.type }} • {{ tx.purpose ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500">{{ new Date(tx.created_at).toLocaleString() }}</p>
              </div>
              <p class="text-sm font-semibold" :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                {{ tx.type === 'credit' ? '+' : '-' }}₹ {{ Number(tx.amount).toLocaleString('en-IN') }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Money -->
      <div v-else-if="currentView === 'add'" class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-4">
          <button class="text-sm text-gray-600 dark:text-gray-300 hover:underline" @click="backHome">← Wallet</button>
          <h2 class="text-xl font-semibold">Add Money</h2>
        </div>
        <div class="rounded-2xl p-5 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow space-y-4">
          <div>
            <label class="block text-sm mb-1">Amount (₹)</label>
            <input v-model="addForm.amount" type="number" min="1" placeholder="Enter amount"
                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700" />
            <p class="mt-1 text-xs text-gray-500">Will redirect to checkout to complete payment.</p>
          </div>
          <button
              class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow disabled:opacity-50"
              :disabled="processing"
              @click="addMoney"
          >
            <span v-if="processing" class="i-lucide-loader-2 w-4 h-4 animate-spin"></span>
            <span v-else class="i-lucide-wallet w-4 h-4"></span>
            <span>{{ processing ? 'Redirecting...' : 'Proceed to Checkout' }}</span>
          </button>
        </div>
      </div>

      <!-- Send Money -->
      <div v-else-if="currentView === 'send'" class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-4">
          <button class="text-sm text-gray-600 dark:text-gray-300 hover:underline" @click="backHome">← Wallet</button>
          <h2 class="text-xl font-semibold">Send Money</h2>
        </div>
        <div class="rounded-2xl p-5 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow space-y-4">
          <div class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-sm mb-1">Amount (₹)</label>
              <input v-model="sendForm.amount" type="number" min="1" placeholder="Enter amount"
                     class="w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700" />
              <p v-if="sendErrors.amount" class="mt-1 text-xs text-red-600">{{ sendErrors.amount }}</p>
            </div>

            <div>
              <label class="block text-sm mb-1">Recipient Wallet UUID</label>
              <div class="flex gap-2">
                <input v-model="sendForm.recipient_uuid" type="text" placeholder="Ex: 5353cd45-616c-4441-b91b-edf29b995474"
                       class="flex-1 rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700" />
                <button
                    type="button"
                    class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow"
                    @click="() => { scannerOpen = true; scanning = true }"
                >
                  Scan
                </button>
              </div>
              <p v-if="sendErrors.recipient_uuid" class="mt-1 text-xs text-red-600">{{ sendErrors.recipient_uuid }}</p>
            </div>

            <div>
              <label class="block text-sm mb-1">Purpose</label>
              <textarea v-model="sendForm.purpose" rows="3" placeholder="Add a note (required). Backend validates length."
                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700"></textarea>
              <p v-if="sendErrors.purpose" class="mt-1 text-xs text-red-600">{{ sendErrors.purpose }}</p>
            </div>

            <div>
              <label class="block text-sm mb-1">PIN</label>
              <input v-model="sendForm.pin" type="password" inputmode="numeric" minlength="4" maxlength="6" placeholder="Enter PIN"
                     class="w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700" />
              <p v-if="sendErrors.pin" class="mt-1 text-xs text-red-600">{{ sendErrors.pin }}</p>
            </div>
          </div>

          <button
              class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow disabled:opacity-50"
              :disabled="processing"
              @click="sendMoney"
          >
            <span v-if="processing" class="i-lucide-loader-2 w-4 h-4 animate-spin"></span>
            <span v-else class="i-lucide-send w-4 h-4"></span>
            <span>{{ processing ? 'Processing...' : 'Send' }}</span>
          </button>
        </div>
      </div>

      <!-- Change PIN -->
      <div v-else-if="currentView === 'pin'" class="max-w-lg mx-auto">
        <div class="flex items-center gap-3 mb-4">
          <button class="text-sm text-gray-600 dark:text-gray-300 hover:underline" @click="backHome">← Wallet</button>
          <h2 class="text-xl font-semibold">Change PIN</h2>
        </div>
        <div class="rounded-2xl p-5 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow space-y-4">
          <div class="relative">
            <label class="block text-sm mb-1">New PIN</label>
            <input :type="showPin ? 'text' : 'password'" v-model="pinForm.pin" inputmode="numeric" minlength="4" maxlength="6" placeholder="Enter new PIN"
                   class="w-full rounded-lg border px-3 py-2 pr-10 dark:bg-gray-800 dark:border-gray-700" />
            <button type="button" class="absolute right-2 top-8 text-xs text-gray-500 hover:text-gray-300" @click="showPin = !showPin">
              {{ showPin ? 'Hide' : 'Show' }}
            </button>
          </div>
          <div class="relative">
            <label class="block text-sm mb-1">Confirm PIN</label>
            <input :type="showConfirmPin ? 'text' : 'password'" v-model="pinForm.confirmPin" inputmode="numeric" minlength="4" maxlength="6" placeholder="Confirm new PIN"
                   class="w-full rounded-lg border px-3 py-2 pr-10 dark:bg-gray-800 dark:border-gray-700" />
            <button type="button" class="absolute right-2 top-8 text-xs text-gray-500 hover:text-gray-300" @click="showConfirmPin = !showConfirmPin">
              {{ showConfirmPin ? 'Hide' : 'Show' }}
            </button>
          </div>
          <button
              class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gray-800 text-white hover:bg-gray-900 shadow disabled:opacity-50"
              :disabled="processing"
              @click="changePin"
          >
            <span v-if="processing" class="i-lucide-loader-2 w-4 h-4 animate-spin"></span>
            <span v-else class="i-lucide-key-round w-4 h-4"></span>
            <span>{{ processing ? 'Updating...' : 'Update PIN' }}</span>
          </button>
        </div>
      </div>

      <!-- Transactions full page -->
      <div v-else-if="currentView === 'transactions'" class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button class="text-sm text-gray-600 dark:text-gray-300 hover:underline" @click="backHome">← Wallet</button>
            <h2 class="text-xl font-semibold">All Transactions</h2>
          </div>
          <div class="flex flex-wrap gap-2">
            <select v-model="filters.type" @change="loadTransactions(true)" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">
              <option value="all">All</option>
              <option value="credit">Credits</option>
              <option value="debit">Debits</option>
            </select>
            <select v-model="filters.status" @change="loadTransactions(true)" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">
              <option value="all">All Status</option>
              <option value="success">Success</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
            <select v-model="sort" @change="loadTransactions(true)" class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800">
              <option value="desc">Newest</option>
              <option value="asc">Oldest</option>
            </select>
          </div>
        </div>

        <div class="rounded-2xl p-4 md:p-6 bg-white dark:bg-gray-900 ring-1 ring-gray-200/70 dark:ring-gray-800/70 shadow">
          <!-- Desktop table -->
          <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
              <tr class="border-b dark:border-gray-800">
                <th class="text-left py-2 px-3">Type</th>
                <th class="text-left py-2 px-3">Purpose</th>
                <th class="text-left py-2 px-3">Amount</th>
                <th class="text-left py-2 px-3">Status</th>
                <th class="text-left py-2 px-3">Date</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="tx in transactions" :key="tx.uuid" class="border-b dark:border-gray-800">
                <td class="py-2 px-3 capitalize">{{ tx.type }}</td>
                <td class="py-2 px-3">{{ tx.purpose ?? 'N/A' }}</td>
                <td class="py-2 px-3 font-semibold" :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                  {{ tx.type === 'credit' ? '+' : '-' }}₹ {{ Number(tx.amount).toLocaleString('en-IN') }}
                </td>
                <td class="py-2 px-3 capitalize">{{ tx.status }}</td>
                <td class="py-2 px-3">{{ new Date(tx.created_at).toLocaleString() }}</td>
              </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile cards -->
          <div class="sm:hidden space-y-3">
            <div v-for="tx in transactions" :key="tx.uuid" class="p-3 border rounded dark:border-gray-800">
              <div class="flex justify-between items-center">
                <p class="capitalize font-medium">{{ tx.type }}</p>
                <p class="font-semibold" :class="tx.type === 'credit' ? 'text-green-600' : 'text-red-600'">
                  {{ tx.type === 'credit' ? '+' : '-' }}₹ {{ Number(tx.amount).toLocaleString('en-IN') }}
                </p>
              </div>
              <p class="text-sm text-gray-500">{{ tx.purpose ?? 'N/A' }}</p>
              <p class="text-xs text-gray-400">{{ new Date(tx.created_at).toLocaleString() }}</p>
            </div>
          </div>

          <!-- Load more -->
          <div v-if="hasMore" class="mt-4 text-center">
            <button class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-800" @click="loadTransactions()">Load more</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Wallet QR Modal -->
    <transition enter-active-class="duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showQrModal" class="fixed inset-0 z-[21] bg-black/50 backdrop-blur-sm grid place-items-center p-4" @click.self="showQrModal = false">
        <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 p-4 ring-1 ring-gray-200 dark:ring-gray-800 shadow-2xl">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold">My Wallet QR</h3>
            <button class="text-gray-400 hover:text-gray-200" @click="showQrModal = false">✕</button>
          </div>
          <div class="w-full aspect-square grid place-items-center bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">
            <img :src="walletQr as string" alt="Wallet QR" class="w-full h-full object-contain" />
          </div>
          <p class="mt-2 text-xs text-gray-500 break-all">UUID: {{ wallet.uuid }}</p>
        </div>
      </div>
    </transition>

    <!-- Withdraw Modal -->
    <transition enter-active-class="duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="withdrawOpen" class="fixed inset-0 z-[21] bg-black/40 backdrop-blur-sm grid place-items-center p-4" @click.self="closeWithdraw">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 p-5 ring-1 ring-gray-200 dark:ring-gray-800 shadow-2xl">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold">Withdraw</h3>
            <button class="text-gray-400 hover:text-gray-200" @click="closeWithdraw">✕</button>
          </div>

          <div class="space-y-3">
            <div class="rounded-lg p-3 ring-1 ring-gray-200 dark:ring-gray-800 bg-gray-50/60 dark:bg-gray-800/40">
              <p class="text-xs text-gray-500 mb-1">Beneficiary</p>
              <div v-if="isUpiBeneficiary" class="text-sm">
                <p class="font-medium">UPI: {{ defaultBeneficiary?.upi_handle }}</p>
              </div>
              <div v-else class="text-sm">
                <p class="font-medium">{{ defaultBeneficiary?.account_name || '—' }}</p>
                <p class="text-gray-500">
                  {{ defaultBeneficiary?.bank_name || '—' }}
                  <span v-if="defaultBeneficiary?.bank_branch"> • {{ defaultBeneficiary?.bank_branch }}</span>
                </p>
                <p class="font-mono">{{ defaultBeneficiary?.account_number ? `•••• ${String(defaultBeneficiary?.account_number).slice(-4)}` : '—' }}</p>
                <p class="font-mono">{{ defaultBeneficiary?.ifsc || '—' }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm mb-1">Amount (₹)</label>
              <input v-model="withdrawForm.amount" type="number" :min="MIN_WITHDRAW" placeholder="Enter amount"
                     class="w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700" />
              <p v-if="withdrawErrors.amount" class="mt-1 text-xs text-red-600">{{ withdrawErrors.amount }}</p>
              <p class="mt-1 text-xs text-gray-500">Minimum withdrawal: ₹ {{ MIN_WITHDRAW }}</p>
            </div>

            <div>
              <label class="block text-sm mb-1">PIN</label>
              <input v-model="withdrawForm.pin" type="password" inputmode="numeric" minlength="4" maxlength="6" placeholder="Enter PIN"
                     class="w-full rounded-lg border px-3 py-2 dark:bg-gray-800 dark:border-gray-700" />
              <p v-if="withdrawErrors.pin" class="mt-1 text-xs text-red-600">{{ withdrawErrors.pin }}</p>
            </div>

            <div class="pt-1 flex items-center justify-end gap-2">
              <button class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700"
                      :disabled="processing" @click="closeWithdraw">
                Cancel
              </button>
              <button class="px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 disabled:opacity-50"
                      :disabled="processing" @click="submitWithdraw">
                {{ processing ? 'Processing...' : 'Withdraw' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Scanner Modal -->
    <transition enter-active-class="duration-150 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-100 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="scannerOpen" class="fixed inset-0 z-[21] bg-black/60 backdrop-blur-sm grid place-items-center p-4" @click.self="scannerOpen = false">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 p-4 ring-1 ring-gray-200 dark:ring-gray-800 shadow-2xl">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold">Scan Recipient QR</h3>
            <button class="text-gray-400 hover:text-gray-200" @click="scannerOpen = false">✕</button>
          </div>
          <div class="rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-800 bg-black">
            <client-only>
              <QrcodeStream
                  @detect="onDetect"
                  @error="onScanError"
                  @ready="scanning = true"
              />
            </client-only>
          </div>
          <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span v-if="scanning">Scanning...</span>
            <span class="ml-auto">Allow camera permission to scan.</span>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.i-lucide-loader-2,.i-lucide-wallet,.i-lucide-banknote,.i-lucide-send,.i-lucide-key-round{ display:inline-block; }
</style>
