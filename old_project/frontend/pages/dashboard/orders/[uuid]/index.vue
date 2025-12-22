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
        <NuxtLink to="/dashboard/orders" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
          Orders
        </NuxtLink>
        <Icon name="mdi:chevron-right" class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white font-semibold">Order Details</span>
      </nav>

      <!-- ✅ Enhanced Header -->
      <div class="mb-8 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
          <div class="flex-1">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:package-variant" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-2xl sm:text-3xl font-black mb-1 bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                  Order Details
                </h1>
                <p class="text-gray-600 dark:text-gray-400">Track your order status and details</p>
              </div>
            </div>

            <!-- Order ID & Status -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
              <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <Icon name="mdi:identifier" class="w-4 h-4" />
                <span class="text-sm font-medium">Order ID:</span>
                <span class="font-mono text-gray-900 dark:text-white font-semibold">{{ order?.uuid }}</span>
                <button
                    @click="copyToClipboard(order?.uuid || '')"
                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors duration-200"
                    title="Copy Order ID"
                >
                  <Icon name="mdi:content-copy" class="w-3 h-3" />
                </button>
              </div>

              <span v-if="order?.status" :class="['status-badge', statusClass(order.status)]">
                <Icon :name="statusIcon(order.status)" class="w-4 h-4" />
                {{ statusLabel(order.status) }}
              </span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-3">
            <button
                @click="refreshOrder"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all duration-200 flex items-center gap-2"
            >
              <Icon name="mdi:refresh" class="w-4 h-4" />
              Refresh
            </button>

            <button
                v-if="order?.status?.toLowerCase() === 'pending'"
                @click="proceedToPayment"
                class="px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 flex items-center gap-2"
            >
              <Icon name="mdi:credit-card" class="w-4 h-4" />
              Complete Payment
            </button>
          </div>
        </div>
      </div>

      <!-- ✅ Payment Countdown (if pending) -->
      <div v-if="order?.status?.toLowerCase() === 'pending' && countdown" class="mb-6 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border border-orange-200 dark:border-orange-700 rounded-2xl p-4">
        <div class="flex items-center gap-3">
          <Icon name="mdi:clock-alert" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
          <div>
            <h3 class="font-bold text-orange-800 dark:text-orange-200">Payment Required</h3>
            <p class="text-sm text-orange-600 dark:text-orange-400">
              Please complete payment within <span class="font-mono font-bold">{{ countdown }}</span>
            </p>
          </div>
        </div>
      </div>

      <!-- ✅ FIXED: Order Progress Timeline with Tooltips -->
      <div v-if="order" class="mb-8 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="mdi:timeline-clock" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
          Order Progress
        </h2>

        <div class="relative">
          <!-- Progress Bar Background -->
          <div class="absolute top-4 left-6 right-6 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
          <!-- Progress Bar Fill -->
          <div
              class="absolute top-4 left-6 h-0.5 bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-500"
              :style="{ width: `calc(${getProgressWidth()}% - 24px)` }"
          ></div>

          <!-- Timeline Steps with Tooltips -->
          <div class="relative flex justify-between">
            <div
                v-for="(step, index) in orderSteps"
                :key="step.status"
                class="flex flex-col items-center text-center flex-1 relative group"
                :class="getStepClasses(step.status)"
            >
              <!-- Tooltip -->
              <div class="tooltip opacity-0 group-hover:opacity-100 absolute -top-20 left-1/2 -translate-x-1/2 bg-gray-900 dark:bg-gray-700 text-white text-xs font-semibold px-4 py-2 rounded-lg whitespace-nowrap pointer-events-none transition-opacity duration-200 z-10 shadow-lg">
                {{ getStepTooltip(step.status) }}
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 rotate-45 w-2 h-2 bg-gray-900 dark:bg-gray-700"></div>
              </div>

              <div class="step-circle mb-2">
                <Icon :name="step.icon" class="w-4 h-4" />
              </div>
              <span class="step-label text-xs px-2">{{ step.label }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- ✅ Left Column - Order Info & Items -->
        <div class="lg:col-span-2 space-y-8">

          <!-- Order Summary -->
          <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
              <Icon name="mdi:receipt" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              Order Summary
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <div class="bg-blue-50/50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
                <Icon name="mdi:package" class="w-6 h-6 text-blue-600 dark:text-blue-400 mx-auto mb-2" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Quantity</p>
                <p class="font-bold text-gray-900 dark:text-white">{{ order?.quantity || 'N/A' }}</p>
              </div>

              <div class="bg-green-50/50 dark:bg-green-900/20 rounded-xl p-4 text-center">
                <Icon name="mdi:currency-inr" class="w-6 h-6 text-green-600 dark:text-green-400 mx-auto mb-2" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Amount</p>
                <p class="font-bold text-gray-900 dark:text-white">{{ order?.amount || 'N/A' }}</p>
              </div>

              <div class="bg-purple-50/50 dark:bg-purple-900/20 rounded-xl p-4 text-center">
                <Icon name="mdi:calendar" class="w-6 h-6 text-purple-600 dark:text-purple-400 mx-auto mb-2" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Order Date</p>
                <p class="font-bold text-gray-900 dark:text-white text-xs">{{ formatDate(order?.created_at) }}</p>
              </div>

              <div class="bg-orange-50/50 dark:bg-orange-900/20 rounded-xl p-4 text-center">
                <Icon name="mdi:shield-check" class="w-6 h-6 text-orange-600 dark:text-orange-400 mx-auto mb-2" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Payment</p>
                <p class="font-bold text-gray-900 dark:text-white text-xs">{{ order?.is_paid ? 'Paid' : 'Pending' }}</p>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
              <Icon name="mdi:shopping" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              Order Items
            </h2>

            <!-- Items List -->
            <div class="space-y-4">
              <div
                  v-for="(item, index) in order?.products || []"
                  :key="index"
                  class="flex items-center gap-4 p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl border border-gray-200/50 dark:border-gray-700/50"
              >
                <!-- Product Image -->
                <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0">
                  <img
                      v-if="item.product?.thumbnail"
                      :src="item.product.thumbnail"
                      :alt="item.product.name"
                      class="w-full h-full object-cover"
                      loading="lazy"
                  />
                  <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                    <Icon name="mdi:package-variant" class="w-8 h-8 text-white" />
                  </div>
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0">
                  <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                    {{ item.product?.name || 'Product' }}
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    SKU: <span class="font-mono font-semibold">{{ item.product?.sku || 'N/A' }}</span>
                  </p>
                  <div class="flex items-center gap-4 mt-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">
                      Qty: <span class="font-semibold text-gray-900 dark:text-white">{{ item.quantity || order.quantity || 1 }}</span>
                    </span>
                    <span class="text-gray-600 dark:text-gray-400">
                      Price: <span class="font-semibold text-gray-900 dark:text-white">{{ item.product?.price || 'N/A' }}</span>
                    </span>
                  </div>
                </div>

                <!-- Item Total -->
                <div class="text-right">
                  <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                  <p class="font-bold text-lg text-gray-900 dark:text-white">
                    {{ item.amount ? `₹${item.amount}` : 'N/A' }}
                  </p>
                </div>
              </div>

              <!-- No items fallback -->
              <div v-if="!order?.products || order.products.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <Icon name="mdi:package-variant-closed" class="w-12 h-12 mx-auto mb-3" />
                <p>No items information available</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Right Column - Addresses & Actions -->
        <div class="space-y-8">

          <!-- Billing Address -->
          <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Icon name="mdi:receipt-text" class="w-5 h-5 text-green-600 dark:text-green-400" />
              Billing Address
            </h2>

            <div v-if="order?.address?.billing" class="space-y-3">
              <div class="flex items-center gap-2">
                <Icon name="mdi:account" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <span class="font-semibold text-gray-900 dark:text-white">{{ order.address.billing.person_name }}</span>
              </div>

              <div class="flex items-start gap-2">
                <Icon name="mdi:map-marker" class="w-4 h-4 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0" />
                <div class="text-gray-600 dark:text-gray-400 text-sm">
                  <p>{{ order.address.billing.address_1 }}</p>
                  <p>{{ order.address.billing.city }} - {{ order.address.billing.postal_code }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2" v-if="order.address.billing.person_mobile">
                <Icon name="mdi:phone" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <span class="text-gray-600 dark:text-gray-400 text-sm">{{ order.address.billing.person_mobile }}</span>
              </div>
            </div>

            <div v-else class="text-center py-6 text-gray-500 dark:text-gray-400">
              <Icon name="mdi:map-marker-off" class="w-8 h-8 mx-auto mb-2" />
              <p class="text-sm">No billing address available</p>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Icon name="mdi:truck-delivery" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
              Shipping Address
            </h2>

            <div v-if="order?.address?.shipping" class="space-y-3">
              <div class="flex items-center gap-2">
                <Icon name="mdi:account" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <span class="font-semibold text-gray-900 dark:text-white">{{ order.address.shipping.person_name }}</span>
              </div>

              <div class="flex items-start gap-2">
                <Icon name="mdi:map-marker" class="w-4 h-4 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0" />
                <div class="text-gray-600 dark:text-gray-400 text-sm">
                  <p>{{ order.address.shipping.address_1 }}</p>
                  <p>{{ order.address.shipping.city }} - {{ order.address.shipping.postal_code }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2" v-if="order.address.shipping.person_mobile">
                <Icon name="mdi:phone" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                <span class="text-gray-600 dark:text-gray-400 text-sm">{{ order.address.shipping.person_mobile }}</span>
              </div>
            </div>

            <div v-else class="text-center py-6 text-gray-500 dark:text-gray-400">
              <Icon name="mdi:truck-off" class="w-8 h-8 mx-auto mb-2" />
              <p class="text-sm">No shipping address available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ UPDATED: Quick Actions - Full Width with Flex Row (Desktop) / Flex Col (Mobile) -->
      <div class="mt-8 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 p-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="mdi:lightning-bolt" class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
          Quick Actions
        </h2>

        <div class="flex flex-col md:flex-row gap-4">
          <button
              @click="downloadInvoice"
              class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105 flex items-center justify-center gap-2"
          >
            <Icon name="mdi:download" class="w-5 h-5" />
            Download Invoice
          </button>

          <button
              @click="trackOrder"
              :disabled="!canTrackOrder"
              :class="[
                'flex-1 px-6 py-4 font-semibold rounded-xl shadow-md transition-all duration-200 transform flex items-center justify-center gap-2',
                canTrackOrder
                  ? 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white hover:shadow-lg hover:scale-105'
                  : 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-500 cursor-not-allowed opacity-60'
              ]"
          >
            <Icon name="mdi:map-marker-path" class="w-5 h-5" />
            Track Order
            <span v-if="!canTrackOrder" class="text-xs">(Available when ready to ship)</span>
          </button>

          <button
              @click="contactSupport"
              class="flex-1 px-6 py-4 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105 flex items-center justify-center gap-2"
          >
            <Icon name="mdi:help-circle" class="w-5 h-5" />
            Contact Support
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute, useRouter, useRuntimeConfig, useSanctumFetch, useToast } from '#imports'
import { onMounted, ref, computed, onUnmounted } from 'vue'
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

const toast = useToast()
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => true)

/* Refs for animations */
const pageOrb1 = ref<HTMLElement>()
const pageOrb2 = ref<HTMLElement>()

let gsapContext: any = null
let countdownInterval: NodeJS.Timeout | null = null

// ✅ Order data
const order = ref<any>(null)
const countdown = ref<string>('')

// ✅ Order statuses matching your backend enum (case-insensitive)
const orderStatuses = [
  { value: 'pending', label: 'Pending', icon: 'mdi:clock', class: 'status-blue' },
  { value: 'processing', label: 'Processing', icon: 'mdi:cog', class: 'status-yellow' },
  { value: 'payment_failed', label: 'Payment Failed', icon: 'mdi:alert-circle', class: 'status-red' },
  { value: 'confirm', label: 'Confirmed', icon: 'mdi:check-circle', class: 'status-green' },
  { value: 'review', label: 'Under Review', icon: 'mdi:eye', class: 'status-purple' },
  { value: 'accepted', label: 'Accepted', icon: 'mdi:check', class: 'status-orange' },
  { value: 'ready_to_ship', label: 'Ready To Ship', icon: 'mdi:package-variant', class: 'status-teal' },
  { value: 'in_transit', label: 'In Transit', icon: 'mdi:truck-fast', class: 'status-blue' },
  { value: 'completed', label: 'Delivered', icon: 'mdi:check-all', class: 'status-green' },
  { value: 'cancelled', label: 'Cancelled', icon: 'mdi:cancel', class: 'status-red' },
  { value: 'refunded', label: 'Refunded', icon: 'mdi:arrow-u-left-top', class: 'status-blue' },
  { value: 'return', label: 'Return Initiated', icon: 'mdi:package-down', class: 'status-orange' },
]

// ✅ Order progress steps for timeline
const orderSteps = [
  { status: 'pending', label: 'Placed', icon: 'mdi:receipt' },
  { status: 'confirm', label: 'Confirmed', icon: 'mdi:check-circle' },
  { status: 'ready_to_ship', label: 'Ready', icon: 'mdi:package-variant' },
  { status: 'in_transit', label: 'Shipped', icon: 'mdi:truck-fast' },
  { status: 'completed', label: 'Delivered', icon: 'mdi:check-all' },
]

// ✅ NEW: Check if order can be tracked
const canTrackOrder = computed(() => {
  if (!order.value?.status) return false
  const currentStatus = order.value.status.toLowerCase()
  const trackableStatuses = ['ready_to_ship', 'in_transit', 'completed']
  return trackableStatuses.includes(currentStatus)
})

// ✅ Helper functions
function statusLabel(status: string) {
  const normalized = status.toLowerCase()
  return orderStatuses.find(s => s.value === normalized)?.label || status
}

function statusIcon(status: string) {
  const normalized = status.toLowerCase()
  return orderStatuses.find(s => s.value === normalized)?.icon || 'mdi:help-circle'
}

function statusClass(status: string) {
  const normalized = status.toLowerCase()
  return orderStatuses.find(s => s.value === normalized)?.class || 'status-gray'
}

// ✅ NEW: Get tooltip text for each step
function getStepTooltip(stepStatus: string) {
  const tooltips: Record<string, string> = {
    'pending': 'Order placed and awaiting confirmation',
    'confirm': 'Order confirmed and being prepared',
    'ready_to_ship': 'Package ready for shipment',
    'in_transit': 'Order is on the way to you',
    'completed': 'Order successfully delivered'
  }
  return tooltips[stepStatus] || 'Order status'
}

function formatDate(dateString: string) {
  if (!dateString) return 'N/A'
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(dateString))
}

function copyToClipboard(text: string) {
  navigator.clipboard.writeText(text).then(() => {
    toast.success({ title: 'Copied!', message: 'Order ID copied to clipboard' })
  }).catch(() => {
    toast.error({ title: 'Copy Failed', message: 'Could not copy to clipboard' })
  })
}

// ✅ Progress calculation based on actual order status
function getProgressWidth() {
  if (!order.value?.status) return 0

  const currentStatus = order.value.status.toLowerCase()
  const currentIndex = orderSteps.findIndex(step => step.status === currentStatus)

  if (currentIndex === -1) {
    if (currentStatus === 'processing') return 10
    if (currentStatus === 'review') return 25
    if (currentStatus === 'accepted') return 40
    if (currentStatus === 'cancelled' || currentStatus === 'refunded' || currentStatus === 'return') return 0
    return 0
  }

  return ((currentIndex + 1) / orderSteps.length) * 100
}

// ✅ Step classes based on actual status
function getStepClasses(stepStatus: string) {
  if (!order.value?.status) return 'step-inactive'

  const currentStatus = order.value.status.toLowerCase()
  const currentIndex = orderSteps.findIndex(step => step.status === currentStatus)
  const stepIndex = orderSteps.findIndex(step => step.status === stepStatus)

  if (currentIndex === -1) return 'step-inactive'

  if (stepIndex <= currentIndex) {
    return 'step-active'
  } else {
    return 'step-inactive'
  }
}

// ✅ API Functions
async function fetchOrder() {
  try {
    isLoading.value = true
    const uuid = route.params.uuid as string

    const res: any = await useSanctumFetch(`${config.public.apiBase}/orders/${uuid}`, {
      method: 'GET'
    })

    if (res?.data) {
      order.value = res.data
      startCountdown()
    } else {
      toast.error({ title: 'Order Not Found', message: 'The requested order could not be found' })
      router.push('/dashboard/orders')
    }
  } catch (error) {
    console.error('Fetch order error:', error)
    toast.error({ title: 'Error', message: 'Failed to load order details' })
    router.push('/dashboard/orders')
  } finally {
    isLoading.value = false
  }
}

function startCountdown() {
  if (order.value?.status?.toLowerCase() !== 'pending' || !order.value?.created_at) return

  const createdAt = new Date(order.value.created_at)
  const expiryTime = new Date(createdAt.getTime() + 30 * 60 * 1000)

  const updateCountdown = () => {
    const now = new Date()
    const timeLeft = expiryTime.getTime() - now.getTime()

    if (timeLeft <= 0) {
      countdown.value = ''
      if (countdownInterval) clearInterval(countdownInterval)
      return
    }

    const minutes = Math.floor(timeLeft / (1000 * 60))
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000)
    countdown.value = `${minutes}:${seconds.toString().padStart(2, '0')}`
  }

  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
}

async function refreshOrder() {
  toast.info({ title: 'Refreshing...', message: 'Getting latest order status' })
  await fetchOrder()
}

function proceedToPayment() {
  toast.info({ title: 'Redirecting...', message: 'Taking you to payment page' })
}

async function downloadInvoice() {
  if (!order.value?.uuid) {
    toast.error({ title: 'Error', message: 'Order UUID not found' })
    return
  }

  try {
    toast.info({ title: 'Downloading...', message: 'Preparing your invoice' })

    const response = await useSanctumFetch(
        `${config.public.apiBase}/orders/${order.value.uuid}/invoice`,
        {
          method: 'GET',
          responseType: 'blob'
        }
    )

    const blob = new Blob([response as BlobPart], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `Invoice-${order.value.uuid}.pdf`

    document.body.appendChild(link)
    link.click()

    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    toast.success({ title: 'Downloaded!', message: 'Invoice downloaded successfully' })
  } catch (error: any) {
    console.error('Download invoice error:', error)
    toast.error({ title: 'Download Failed', message: error?.message || 'Could not download invoice' })
  }
}

function trackOrder() {
  if (!canTrackOrder.value) {
    toast.warning({ title: 'Not Available', message: 'Tracking available when order is ready to ship' })
    return
  }
  toast.info({ title: 'Opening Tracker', message: 'Order tracking information' })
}

function contactSupport() {
  toast.info({ title: 'Contacting Support', message: 'Opening support channel' })
}

/* Animations */
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
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
  fetchOrder()
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }
  if (countdownInterval) {
    clearInterval(countdownInterval)
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

/* ✅ Status Badges */
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

.status-teal {
  @apply bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300;
}

.status-purple {
  @apply bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300;
}

.status-orange {
  @apply bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300;
}

.status-gray {
  @apply bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300;
}

/* ✅ Timeline Steps */
.step-circle {
  @apply w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300;
}

.step-label {
  @apply text-xs font-medium transition-colors duration-300;
}

.step-active .step-circle {
  @apply bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg;
}

.step-active .step-label {
  @apply text-gray-900 dark:text-white font-semibold;
}

.step-inactive .step-circle {
  @apply bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500;
}

.step-inactive .step-label {
  @apply text-gray-400 dark:text-gray-500;
}

/* ✅ Tooltip */
.tooltip {
  @apply shadow-xl;
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
