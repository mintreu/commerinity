<template>
  <div class="w-full h-full flex flex-col">
    <GlobalLoader v-if="isLoading" />
    <div v-else class="p-4 sm:p-8 lg:p-12">
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold mb-6 text-gray-900 dark:text-gray-100">
        My Orders
      </h1>

      <!-- Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Status</label>
          <select v-model="filters.status"
                  class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700">
            <option value="">All</option>
            <option v-for="status in orderStatuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">From Date</label>
          <input type="date" v-model="filters.from_date"
                 class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">To Date</label>
          <input type="date" v-model="filters.to_date"
                 class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" />
        </div>

        <div class="flex items-end">
          <button @click="applyFilters"
                  class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Apply
          </button>
        </div>
      </div>

      <!-- Orders List -->
      <div class="space-y-4">
        <!-- Inside your v-for loop of orders -->
        <div v-for="order in orders" :key="order.uuid"
             class="p-4 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700 shadow-sm">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Order ID</p>
              <p class="font-mono text-gray-800 dark:text-gray-100">{{ order.uuid }}</p>
            </div>
            <span :class="['status-badge', statusClass(order.status)]">
      <Icon :name="statusIcon(order.status)" class="w-4 h-4" />
      {{ statusLabel(order.status) }}
    </span>
          </div>

          <div class="mt-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
            <div class="flex gap-6">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Quantity</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ order.quantity }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>

            <!-- View CTA -->
            <NuxtLink :to="`/dashboard/orders/${order.uuid}`"
                      class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">
              <Icon name="heroicons:eye" class="w-4 h-4 mr-1" />
              View
            </NuxtLink>
          </div>
        </div>

      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center mt-6 text-gray-800 dark:text-gray-200">
        <button :disabled="!pagination.prev_page_url" @click="changePage(pagination.current_page - 1)"
                class="px-4 py-2 rounded-lg border dark:border-gray-700 disabled:opacity-50">
          Previous
        </button>

        <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>

        <button :disabled="!pagination.next_page_url" @click="changePage(pagination.current_page + 1)"
                class="px-4 py-2 rounded-lg border dark:border-gray-700 disabled:opacity-50">
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRuntimeConfig, useSanctumFetch } from '#imports'
import { onMounted, ref } from 'vue'
import GlobalLoader from '~/components/GlobalLoader.vue'

const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)

const orders = ref<any[]>([])
const pagination = ref<any>({})
const filters = ref({
  status: '',
  from_date: '',
  to_date: '',
})

const orderStatuses = [
  { value: 'processing', label: 'Processing', icon: 'heroicons:arrow-path', class: 'status-yellow' },
  { value: 'pending', label: 'Pending', icon: 'heroicons:clock', class: 'status-blue' },
  { value: 'payment_failed', label: 'Payment Failed', icon: 'heroicons:x-circle', class: 'status-red' },
  { value: 'confirm', label: 'Confirm', icon: 'heroicons:check-circle', class: 'status-green' },
  { value: 'review', label: 'Review', icon: 'heroicons:eye', class: 'status-purple' },
  { value: 'accepted', label: 'Accepted', icon: 'heroicons:check', class: 'status-orange' },
  { value: 'ready_to_ship', label: 'Ready To Ship', icon: 'heroicons:truck', class: 'status-teal' },
  { value: 'in_transit', label: 'In Transit', icon: 'heroicons:arrow-path', class: 'status-gray' },
  { value: 'completed', label: 'Completed', icon: 'heroicons:badge-check', class: 'status-green' },
  { value: 'cancelled', label: 'Cancelled', icon: 'heroicons:ban', class: 'status-red' },
  { value: 'refunded', label: 'Refunded', icon: 'heroicons:arrow-uturn-left', class: 'status-blue' },
]

function statusLabel(status: string) {
  return orderStatuses.find(s => s.value === status)?.label || status
}
function statusIcon(status: string) {
  return orderStatuses.find(s => s.value === status)?.icon || 'heroicons:question-mark-circle'
}
function statusClass(status: string) {
  return orderStatuses.find(s => s.value === status)?.class || 'status-gray'
}

async function fetchOrders(page = 1) {
  try {
    isLoading.value = true
    let url = `${config.public.apiBase}/orders?page=${page}`

    if (filters.value.status) url += `&status=${filters.value.status}`
    if (filters.value.from_date) url += `&from_date=${filters.value.from_date}`
    if (filters.value.to_date) url += `&to_date=${filters.value.to_date}`

    const res: any = await useSanctumFetch(url, { method: 'GET' })

    if (res?.data) {
      orders.value = res.data
      pagination.value = res.meta
    }
  } finally {
    isLoading.value = false
  }
}

function changePage(page: number) {
  fetchOrders(page)
}

function applyFilters() {
  fetchOrders(1)
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString()
}

onMounted(() => {
  fetchOrders()
})

definePageMeta({
  layout: 'dashboard',
})
</script>

<style scoped>
.status-badge {
  @apply inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium;
}

.status-yellow {
  @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200;
}
.status-blue {
  @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200;
}
.status-red {
  @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200;
}
.status-green {
  @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200;
}
.status-purple {
  @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200;
}
.status-orange {
  @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200;
}
.status-teal {
  @apply bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200;
}
.status-gray {
  @apply bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200;
}
</style>
