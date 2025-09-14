<template>
  <div class="w-full h-full flex flex-col">
    <GlobalLoader v-if="isLoading" />

    <div v-else-if="order" class="p-4 sm:p-8 lg:p-12 space-y-8">
      <!-- Page Title -->
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">
        Order Details
      </h1>

      <!-- Header -->
      <div
          class="p-4 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700 shadow-sm space-y-4"
      >
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Order ID</p>
            <p class="font-mono text-gray-800 dark:text-gray-100">
              {{ order.uuid }}
            </p>
          </div>
          <span :class="['flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium', statusClass(order.status)]">
            <Icon :name="statusIcon(order.status)" class="w-4 h-4" />
            {{ statusLabel(order.status) }}
          </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Quantity</p>
            <p class="font-semibold">{{ order.quantity }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Amount</p>
            <p class="font-semibold">{{ order.amount }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Created At</p>
            <p class="font-semibold">{{ formatDate(order.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Payment Reminder -->
      <div
          v-if="order.status.toLowerCase() === 'pending' && order.transaction"
          class="p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900 border dark:border-yellow-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
      >
        <div>
          <h2 class="font-semibold text-gray-800 dark:text-gray-100">Payment Pending</h2>
          <p class="text-sm text-gray-600 dark:text-gray-300">
            Please complete payment within <span class="font-bold">{{ countdown }}</span>
          </p>
        </div>
        <button
            @click="payNow"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
        >
          Pay Now
        </button>
      </div>

      <!-- Products -->
      <div>
        <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Products</h2>
        <div class="space-y-3">
          <div
              v-for="(item, index) in order.products"
              :key="index"
              class="flex items-center justify-between p-4 border rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700"
          >
            <div class="flex items-center gap-4">
              <img
                  :src="item.product.thumbnail"
                  class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-md border"
                  alt="Product thumbnail"
              />
              <div class="space-y-1">
                <NuxtLink
                    :to="`/product/${item.product.url}`"
                    class="text-blue-600 dark:text-blue-400 hover:underline font-medium line-clamp-1"
                >
                  {{ item.product.name }}
                </NuxtLink>
                <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ item.product.sku }}</p>
                <p class="text-sm">Price: {{ item.product.price }}</p>
                <p class="text-sm">Qty: {{ item.quantity || 1 }}</p>
              </div>
            </div>
            <div class="flex flex-col gap-2">
              <button
                  v-if="canCancel"
                  @click="handleItemAction('cancel', item)"
                  class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700 text-xs"
              >
                Cancel
              </button>
              <button
                  v-if="canReturn"
                  @click="handleItemAction('return', item)"
                  class="px-3 py-1 rounded bg-yellow-500 text-white hover:bg-yellow-600 text-xs"
              >
                Return
              </button>
              <button
                  v-if="canRefund"
                  @click="handleItemAction('refund', item)"
                  class="px-3 py-1 rounded bg-purple-600 text-white hover:bg-purple-700 text-xs"
              >
                Refund
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Actions -->
      <div class="flex flex-wrap gap-3">
        <button
            v-if="canCancel"
            @click="handleAction('cancel')"
            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
        >
          Cancel Order
        </button>
        <button
            v-if="canReturn"
            @click="handleAction('return')"
            class="px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600"
        >
          Return Order
        </button>
        <button
            v-if="canRefund"
            @click="handleAction('refund')"
            class="px-4 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700"
        >
          Request Refund
        </button>
        <button
            @click="downloadInvoice"
            class="px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900"
        >
          Download Invoice
        </button>
      </div>

      <!-- Addresses -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border dark:border-gray-700">
          <h2 class="font-semibold mb-2 text-gray-800 dark:text-gray-100">Billing Address</h2>
          <p>{{ order.address.billing.person_name }}</p>
          <p>{{ order.address.billing.address_1 }}</p>
          <p>{{ order.address.billing.city }} - {{ order.address.billing.postal_code }}</p>
          <p>{{ order.address.billing.person_mobile }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border dark:border-gray-700">
          <h2 class="font-semibold mb-2 text-gray-800 dark:text-gray-100">Shipping Address</h2>
          <p>{{ order.address.shipping.person_name }}</p>
          <p>{{ order.address.shipping.address_1 }}</p>
          <p>{{ order.address.shipping.city }} - {{ order.address.shipping.postal_code }}</p>
          <p>{{ order.address.shipping.person_mobile }}</p>
        </div>
      </div>
    </div>
  </div>
</template>





<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useRuntimeConfig, useSanctumFetch } from '#imports'
import { ref, onMounted, computed } from 'vue'
import GlobalLoader from '~/components/GlobalLoader.vue'

const config = useRuntimeConfig()
const route = useRoute()
const isLoading = useState('pageLoading', () => false)
const order = ref<any>(null)
const countdown = ref<string>('')

// --- Status Helpers ---
const orderStatuses = [
  { value: 'processing', label: 'Processing', icon: 'heroicons:arrow-path', class: 'status-yellow' },
  { value: 'pending', label: 'Pending', icon: 'heroicons:clock', class: 'status-blue' },
  { value: 'payment_failed', label: 'Payment Failed', icon: 'heroicons:x-circle', class: 'status-red' },
  { value: 'confirm', label: 'Confirm', icon: 'heroicons:check-circle', class: 'status-green' },
  { value: 'review', label: 'Review', icon: 'heroicons:eye', class: 'status-purple' },
  { value: 'accepted', label: 'Accepted', icon: 'heroicons:check', class: 'status-orange' },
  { value: 'ready_to_ship', label: 'Ready To Ship', icon: 'heroicons:truck', class: 'status-teal' },
  { value: 'in_transit', label: 'In Transit', icon: 'heroicons:truck', class: 'status-gray' },
  { value: 'completed', label: 'Completed', icon: 'heroicons:badge-check', class: 'status-green' },
  { value: 'cancelled', label: 'Cancelled', icon: 'heroicons:ban', class: 'status-red' },
  { value: 'refunded', label: 'Refunded', icon: 'heroicons:arrow-uturn-left', class: 'status-blue' },
]

function statusLabel(status: string) {
  return orderStatuses.find(s => s.value === status.toLowerCase())?.label || status
}
function statusIcon(status: string) {
  return orderStatuses.find(s => s.value === status.toLowerCase())?.icon || 'heroicons:question-mark-circle'
}
function statusClass(status: string) {
  return orderStatuses.find(s => s.value === status.toLowerCase())?.class || 'status-gray'
}
function formatDate(date: string) {
  return new Date(date).toLocaleDateString()
}

// --- Computed ---
const canCancel = computed(() =>
    ['pending', 'processing', 'confirm'].includes(order.value?.status?.toLowerCase())
)
const canReturn = computed(() =>
    ['completed'].includes(order.value?.status?.toLowerCase())
)
const canRefund = computed(() =>
    ['completed', 'return'].includes(order.value?.status?.toLowerCase())
)

// --- Actions ---
async function handleAction(type: 'cancel' | 'return' | 'refund') {
  try {
    isLoading.value = true
    await useSanctumFetch(`${config.public.apiBase}/orders/${route.params.uuid}/${type}`, {
      method: 'POST',
    })
    await fetchOrder()
  } finally {
    isLoading.value = false
  }
}
async function handleItemAction(type: 'cancel' | 'return' | 'refund', item: any) {
  try {
    isLoading.value = true
    await useSanctumFetch(`${config.public.apiBase}/orders/${route.params.uuid}/${type}`, {
      method: 'POST',
      body: { product_id: item.product.sku },
    })
    await fetchOrder()
  } finally {
    isLoading.value = false
  }
}

function downloadInvoice() {
  window.open(`${config.public.apiBase}/orders/${route.params.uuid}/invoice`, '_blank')
}

function payNow() {
  if (!order.value?.transaction?.uuid) return
  window.location.href = `${config.public.apiBase}/checkout/${order.value.transaction.uuid}`
}

// --- Countdown ---
function startCountdown(expireAt: string) {
  let remaining = 60 * 60 * 24 // fallback
  // if backend gives duration string like "23 hours from now"
  if (expireAt.toLowerCase().includes('hour')) {
    const hours = parseInt(expireAt.split(' ')[0])
    remaining = hours * 3600
  }

  const interval = setInterval(() => {
    if (remaining <= 0) {
      countdown.value = 'Expired'
      clearInterval(interval)
      return
    }
    const h = Math.floor(remaining / 3600)
    const m = Math.floor((remaining % 3600) / 60)
    const s = remaining % 60
    countdown.value = `${h}h ${m}m ${s}s`
    remaining--
  }, 1000)
}

// --- Fetch ---
async function fetchOrder() {
  try {
    isLoading.value = true
    const res: any = await useSanctumFetch(
        `${config.public.apiBase}/orders/${route.params.uuid}`,
        { method: 'GET' }
    )
    order.value = res?.data

    if (order.value?.status.toLowerCase() === 'pending' && order.value.expire_at) {
      startCountdown(order.value.expire_at)
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchOrder()
})
</script>
