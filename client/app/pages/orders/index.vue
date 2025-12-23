<script setup lang="ts">
/**
 * Orders Page - Demo Version
 * Displays user's order history
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const { formatCurrency } = useBranding()

const activeTab = ref('all')

// Demo orders
const orders = ref([
  {
    id: 'ORD-2024-001',
    date: '2024-12-18',
    status: 'delivered',
    total: 249900,
    items: [
      { name: 'Premium Health Supplement', quantity: 1, price: 149900 },
      { name: 'Organic Wellness Kit', quantity: 1, price: 100000 }
    ],
    payment: 'Paid',
    deliveredAt: '2024-12-20'
  },
  {
    id: 'ORD-2024-002',
    date: '2024-12-15',
    status: 'shipped',
    total: 99900,
    items: [
      { name: 'Beauty Care Bundle', quantity: 1, price: 99900 }
    ],
    payment: 'Paid',
    trackingNumber: 'TRACK123456789'
  },
  {
    id: 'ORD-2024-003',
    date: '2024-12-12',
    status: 'processing',
    total: 179900,
    items: [
      { name: 'Daily Nutrition Combo', quantity: 1, price: 179900 }
    ],
    payment: 'Paid'
  },
  {
    id: 'ORD-2024-004',
    date: '2024-12-10',
    status: 'cancelled',
    total: 349900,
    items: [
      { name: 'Fitness Pro Pack', quantity: 1, price: 349900 }
    ],
    payment: 'Refunded'
  }
])

const tabs = [
  { label: 'All Orders', value: 'all' },
  { label: 'Processing', value: 'processing' },
  { label: 'Shipped', value: 'shipped' },
  { label: 'Delivered', value: 'delivered' },
  { label: 'Cancelled', value: 'cancelled' }
]

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') {
    return orders.value
  }
  return orders.value.filter(o => o.status === activeTab.value)
})

const formatPrice = (paisa: number) => {
  return formatCurrency(paisa / 100)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusColor = (status: string) => {
  const colors: Record<string, 'success' | 'warning' | 'info' | 'error' | 'neutral'> = {
    delivered: 'success',
    shipped: 'info',
    processing: 'warning',
    cancelled: 'error',
    pending: 'neutral'
  }
  return colors[status] || 'neutral'
}

const getStatusIcon = (status: string) => {
  const icons: Record<string, string> = {
    delivered: 'i-lucide-check-circle',
    shipped: 'i-lucide-truck',
    processing: 'i-lucide-clock',
    cancelled: 'i-lucide-x-circle',
    pending: 'i-lucide-hourglass'
  }
  return icons[status] || 'i-lucide-circle'
}
</script>

<template>
  <div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
        My Orders
      </h1>
      <p class="text-slate-500 dark:text-slate-400">
        Track and manage your orders
      </p>
    </div>

    <!-- Demo Notice -->
    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
      <div class="flex items-center gap-3">
        <UIcon
          name="i-lucide-info"
          class="w-5 h-5 text-amber-600 dark:text-amber-400"
        />
        <p class="text-sm text-amber-700 dark:text-amber-300">
          This is a demo page. Orders will be connected to the backend API.
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="[
          'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all',
          activeTab === tab.value
            ? 'bg-primary-500 text-white'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
        ]"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Orders List -->
    <div
      v-if="filteredOrders.length > 0"
      class="space-y-4"
    >
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        class="glass-card overflow-hidden"
      >
        <!-- Order Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center gap-4">
            <div>
              <p class="font-semibold text-slate-900 dark:text-white">
                {{ order.id }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Placed on {{ formatDate(order.date) }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <UBadge
              :color="getStatusColor(order.status)"
              variant="subtle"
            >
              <UIcon
                :name="getStatusIcon(order.status)"
                class="w-3 h-3 mr-1"
              />
              {{ order.status }}
            </UBadge>
          </div>
        </div>

        <!-- Order Items -->
        <div class="p-4">
          <div
            v-for="(item, index) in order.items"
            :key="index"
            class="flex items-center gap-4 py-2"
            :class="{ 'border-t border-slate-200 dark:border-slate-700': index > 0 }"
          >
            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center">
              <UIcon
                name="i-lucide-package"
                class="w-8 h-8 text-slate-400"
              />
            </div>
            <div class="flex-1">
              <p class="font-medium text-slate-900 dark:text-white">
                {{ item.name }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Qty: {{ item.quantity }} x {{ formatPrice(item.price) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Order Footer -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
          <div class="flex items-center gap-4">
            <div class="text-sm">
              <span class="text-slate-500 dark:text-slate-400">Payment:</span>
              <span class="font-medium text-slate-900 dark:text-white ml-1">{{ order.payment }}</span>
            </div>
            <div
              v-if="order.trackingNumber"
              class="text-sm"
            >
              <span class="text-slate-500 dark:text-slate-400">Tracking:</span>
              <span class="font-medium text-primary-600 dark:text-primary-400 ml-1">{{ order.trackingNumber }}</span>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="text-right">
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Total
              </p>
              <p class="text-xl font-bold text-slate-900 dark:text-white">
                {{ formatPrice(order.total) }}
              </p>
            </div>
            <UButton
              color="primary"
              variant="soft"
            >
              View Details
            </UButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="glass-card p-12 text-center"
    >
      <UIcon
        name="i-lucide-package-open"
        class="w-16 h-16 mx-auto text-slate-400 mb-4"
      />
      <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
        No orders found
      </h3>
      <p class="text-slate-500 dark:text-slate-400 mb-6">
        {{ activeTab === 'all' ? "You haven't placed any orders yet" : `No ${activeTab} orders` }}
      </p>
      <NuxtLink to="/shop">
        <UButton color="primary">
          <UIcon
            name="i-lucide-shopping-bag"
            class="w-4 h-4 mr-2"
          />
          Start Shopping
        </UButton>
      </NuxtLink>
    </div>
  </div>
</template>
