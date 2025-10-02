<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-gray-50 via-blue-50/30 to-purple-50/30 dark:from-gray-950 dark:via-blue-950/30 dark:to-purple-950/30">
    <GlobalLoader v-if="isLoading" />

    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="pageOrb1" class="absolute -top-20 -right-20 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 rounded-full blur-3xl opacity-60 animate-float"></div>
      <div ref="pageOrb2" class="absolute -bottom-20 -left-20 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl opacity-70 animate-float-reverse"></div>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-10">

      <!-- ✅ Breadcrumb -->
      <nav class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-6 animate-slideInDown" aria-label="Breadcrumb">
        <NuxtLink to="/dashboard" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
          <Icon name="mdi:view-dashboard" class="w-4 h-4 mr-1" />
          Dashboard
        </NuxtLink>
        <Icon name="mdi:chevron-right" class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white font-semibold">Orders</span>
      </nav>

      <!-- ✅ Enhanced Header with Stats -->
      <div class="mb-8 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6 md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-3xl sm:text-4xl font-black mb-2 bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
              My Orders
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Track and manage your order history</p>
          </div>

          <div class="flex items-center gap-4">
            <!-- Order Stats -->
            <div class="flex items-center gap-6 text-sm">
              <div class="text-center">
                <div class="font-bold text-lg text-gray-900 dark:text-white">{{ orders.length }}</div>
                <div class="text-gray-500 dark:text-gray-400">Total</div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg text-green-600 dark:text-green-400">{{ completedCount }}</div>
                <div class="text-gray-500 dark:text-gray-400">Completed</div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg text-blue-600 dark:text-blue-400">{{ pendingCount }}</div>
                <div class="text-gray-500 dark:text-gray-400">Pending</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Enhanced Filters Card -->
      <div class="mb-8 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
        <div class="flex items-center gap-3 mb-4">
          <Icon name="mdi:filter" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Filters</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          <!-- Status Filter -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Status</label>
            <select
                v-model="filters.status"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
            >
              <option value="">All Status</option>
              <option v-for="status in orderStatuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <!-- From Date -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">From Date</label>
            <input
                type="date"
                v-model="filters.from_date"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
            />
          </div>

          <!-- To Date -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">To Date</label>
            <input
                type="date"
                v-model="filters.to_date"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
            />
          </div>

          <!-- Search -->
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Search</label>
            <div class="relative">
              <input
                  type="text"
                  v-model="searchTerm"
                  placeholder="Order ID..."
                  class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200"
              />
              <Icon name="mdi:magnify" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            </div>
          </div>

          <!-- Apply Button -->
          <div class="flex items-end">
            <button
                @click="applyFilters"
                :disabled="isLoading"
                class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
            >
              <Icon v-if="isLoading" name="mdi:loading" class="w-4 h-4 animate-spin" />
              <Icon v-else name="mdi:filter-check" class="w-4 h-4" />
              {{ isLoading ? 'Loading...' : 'Apply' }}
            </button>
          </div>
        </div>
      </div>

      <!-- ✅ Enhanced Orders List (keeping original data structure) -->
      <div v-if="!isLoading" class="space-y-4">
        <div
            v-for="order in orders"
            :key="order.uuid"
            class="group bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:ring-blue-200 dark:hover:ring-blue-700"
        >
          <!-- Order Header -->
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Order Details</h3>
                <span :class="['status-badge', statusClass(order.status)]">
                  <Icon :name="statusIcon(order.status)" class="w-4 h-4" />
                  {{ statusLabel(order.status) }}
                </span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <Icon name="mdi:identifier" class="w-4 h-4" />
                <span class="font-mono">{{ order.uuid }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <NuxtLink
                  :to="`/dashboard/orders/${order.uuid}`"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105"
              >
                <Icon name="mdi:eye" class="w-4 h-4" />
                View Details
              </NuxtLink>
            </div>
          </div>

          <!-- Order Info Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl">
            <div class="text-center">
              <Icon name="mdi:package-variant" class="w-5 h-5 text-blue-600 dark:text-blue-400 mx-auto mb-1" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Quantity</p>
              <p class="font-bold text-gray-900 dark:text-white">{{ order.quantity || 'N/A' }}</p>
            </div>

            <div class="text-center">
              <Icon name="mdi:calendar" class="w-5 h-5 text-green-600 dark:text-green-400 mx-auto mb-1" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Order Date</p>
              <p class="font-bold text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</p>
            </div>

            <div class="text-center" v-if="order.amount">
              <Icon name="mdi:currency-usd" class="w-5 h-5 text-purple-600 dark:text-purple-400 mx-auto mb-1" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Amount</p>
              <p class="font-bold text-gray-900 dark:text-white">₹{{ order.amount }}</p>
            </div>

            <div class="text-center">
              <Icon name="mdi:update" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 mx-auto mb-1" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Last Updated</p>
              <p class="font-bold text-gray-900 dark:text-white">{{ formatDate(order.updated_at) }}</p>
            </div>
          </div>

          <!-- Progress Bar for Status -->
          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Order Progress</span>
              <span class="text-xs font-bold text-gray-900 dark:text-white">{{ getStatusProgress(order.status) }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
              <div
                  class="h-full rounded-full transition-all duration-500 ease-out"
                  :class="getStatusProgressColor(order.status)"
                  :style="{ width: `${getStatusProgress(order.status)}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Empty State -->
      <div v-else-if="!isLoading && orders.length === 0" class="text-center py-16">
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-12">
          <Icon name="mdi:package-variant-closed" class="w-24 h-24 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Orders Found</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">You haven't placed any orders yet.</p>
          <NuxtLink
              to="/dashboard/products"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
          >
            Browse Products
          </NuxtLink>
        </div>
      </div>

      <!-- ✅ Enhanced Pagination (keeping original logic) -->
      <div v-if="orders.length > 0 && pagination.last_page > 1" class="mt-8 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <!-- Page Info -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </div>

          <!-- Pagination Controls -->
          <div class="flex items-center gap-2">
            <button
                :disabled="!pagination.prev_page_url || isLoading"
                @click="changePage(pagination.current_page - 1)"
                class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
            >
              Previous
            </button>

            <span class="px-4 py-2 text-gray-900 dark:text-white font-medium">
              {{ pagination.current_page }}
            </span>

            <button
                :disabled="!pagination.next_page_url || isLoading"
                @click="changePage(pagination.current_page + 1)"
                class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRuntimeConfig, useSanctumFetch } from '#imports'
import { onMounted, ref, computed } from 'vue'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({
  layout: 'dashboard',
})

/* GSAP imports (client-side only) */
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)

/* Refs for animations */
const pageOrb1 = ref<HTMLElement>()
const pageOrb2 = ref<HTMLElement>()

let gsapContext: any = null

// ✅ Keep original working data structure
const orders = ref<any[]>([])
const pagination = ref<any>({})
const filters = ref({
  status: '',
  from_date: '',
  to_date: '',
})
const searchTerm = ref('')

const orderStatuses = [
  { value: 'processing', label: 'Processing', icon: 'mdi:cog', class: 'status-yellow' },
  { value: 'pending', label: 'Pending', icon: 'mdi:clock', class: 'status-blue' },
  { value: 'payment_failed', label: 'Payment Failed', icon: 'mdi:close-circle', class: 'status-red' },
  { value: 'confirm', label: 'Confirmed', icon: 'mdi:check-circle', class: 'status-green' },
  { value: 'review', label: 'Under Review', icon: 'mdi:eye', class: 'status-purple' },
  { value: 'accepted', label: 'Accepted', icon: 'mdi:check', class: 'status-orange' },
  { value: 'ready_to_ship', label: 'Ready To Ship', icon: 'mdi:truck', class: 'status-teal' },
  { value: 'in_transit', label: 'In Transit', icon: 'mdi:truck-fast', class: 'status-blue' },
  { value: 'completed', label: 'Completed', icon: 'mdi:check-all', class: 'status-green' },
  { value: 'cancelled', label: 'Cancelled', icon: 'mdi:cancel', class: 'status-red' },
  { value: 'refunded', label: 'Refunded', icon: 'mdi:undo', class: 'status-blue' },
]

// ✅ Computed properties for stats
const completedCount = computed(() =>
    orders.value.filter(o => ['completed', 'confirm'].includes(o.status)).length
)

const pendingCount = computed(() =>
    orders.value.filter(o => ['pending', 'processing', 'review'].includes(o.status)).length
)

// ✅ Keep original helper functions
function statusLabel(status: string) {
  return orderStatuses.find(s => s.value === status)?.label || status
}

function statusIcon(status: string) {
  return orderStatuses.find(s => s.value === status)?.icon || 'mdi:help-circle'
}

function statusClass(status: string) {
  return orderStatuses.find(s => s.value === status)?.class || 'status-gray'
}

function getStatusProgress(status: string): number {
  const progressMap: Record<string, number> = {
    'pending': 10,
    'processing': 25,
    'confirm': 40,
    'review': 50,
    'accepted': 60,
    'ready_to_ship': 75,
    'in_transit': 85,
    'completed': 100,
    'cancelled': 0,
    'refunded': 0,
    'payment_failed': 0,
  }
  return progressMap[status] || 0
}

function getStatusProgressColor(status: string): string {
  const progress = getStatusProgress(status)
  if (progress === 0) return 'bg-red-500'
  if (progress === 100) return 'bg-green-500'
  if (progress >= 50) return 'bg-blue-500'
  return 'bg-yellow-500'
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString()
}

// ✅ Keep original API functions exactly as they were
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

/* Animations */
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (pageOrb1.value && pageOrb2.value) {
      gsap.to(pageOrb1.value, {
        x: -100,
        y: 100,
        rotation: 360,
        duration: 25,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })

      gsap.to(pageOrb2.value, {
        x: 100,
        y: -80,
        rotation: -360,
        duration: 30,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })
    }
  })
}

onMounted(() => {
  fetchOrders()

  // Initialize animations
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }
})
</script>

<style scoped>
/* ✅ Animations */
@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-10px) rotate(5deg);
  }
}

@keyframes float-reverse {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(10px) rotate(-5deg);
  }
}

.animate-slideInDown {
  animation: slideInDown 0.6s ease-out;
}

.animate-float {
  animation: float 20s infinite ease-in-out;
}

.animate-float-reverse {
  animation: float-reverse 25s infinite ease-in-out;
}

/* ✅ Status Badges (keeping original styles) */
.status-badge {
  @apply inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider;
}

.status-yellow {
  @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300;
}

.status-blue {
  @apply bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300;
}

.status-red {
  @apply bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300;
}

.status-green {
  @apply bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300;
}

.status-purple {
  @apply bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300;
}

.status-orange {
  @apply bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300;
}

.status-teal {
  @apply bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300;
}

.status-gray {
  @apply bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300;
}

/* ✅ Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800 rounded-lg;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500;
}
</style>
