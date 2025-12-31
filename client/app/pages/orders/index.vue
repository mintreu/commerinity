<script setup lang="ts">
/**
 * Orders Page
 * Displays user's real order history from API
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const {
  fetchOrders,
  isLoading,
  orders,
  getStatusColor,
  getStatusIcon
} = useOrders()

const activeTab = ref('all')

// Fetch orders on mount and when tab changes
const loadOrders = async () => {
  const status = activeTab.value === 'all' ? undefined : activeTab.value
  await fetchOrders(1, 20) // We can add status filtering to useOrders fetchOrders if needed
}

onMounted(() => {
  loadOrders()
})

const tabs = [
  { label: 'All Orders', value: 'all' },
  { label: 'Pending', value: 'pending' },
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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
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

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="[
          'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all',
          activeTab === tab.value
            ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/25 animate-in fade-in zoom-in duration-300'
            : 'bg-white/50 dark:bg-slate-800/50 backdrop-blur-md text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50'
        ]"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div
      v-if="isLoading && orders.length === 0"
      class="space-y-4"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="glass-card h-48 animate-pulse p-6"
      >
        <div class="flex justify-between mb-6">
          <div class="h-6 w-32 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="h-6 w-24 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
        <div class="flex gap-4">
          <div class="w-16 h-16 bg-slate-200 dark:bg-slate-700 rounded-xl" />
          <div class="flex-1 space-y-2">
            <div class="h-4 w-3/4 bg-slate-200 dark:bg-slate-700 rounded" />
            <div class="h-4 w-1/2 bg-slate-200 dark:bg-slate-700 rounded" />
          </div>
        </div>
      </div>
    </div>

    <!-- Orders List -->
    <div
      v-else-if="filteredOrders.length > 0"
      class="space-y-4"
    >
      <div
        v-for="order in filteredOrders"
        :key="order.uuid"
        class="glass-card overflow-hidden group hover:border-primary-500/50 transition-all duration-300"
      >
        <!-- Order Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-200/50 dark:border-slate-700/50">
          <div class="flex items-center gap-4">
            <div>
              <p class="font-semibold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                #{{ order.order_number }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Placed on {{ formatDate(order.created_at) }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <UBadge
              :color="getStatusColor(order.status)"
              variant="subtle"
              class="capitalize px-3 py-1"
            >
              <template #leading>
                <UIcon
                  :name="getStatusIcon(order.status)"
                  class="w-3.5 h-3.5"
                />
              </template>
              {{ order.status_label }}
            </UBadge>
          </div>
        </div>

        <!-- Order Items -->
        <div class="p-4">
          <div
            v-for="(item, index) in order.items"
            :key="item.id"
            class="flex items-center gap-4 py-3"
            :class="{ 'border-t border-slate-200/50 dark:border-slate-700/50': index > 0 }"
          >
            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800/50 rounded-xl flex items-center justify-center overflow-hidden border border-slate-200/50 dark:border-slate-700/50">
              <img
                v-if="item.image"
                :src="item.image"
                :alt="item.product_name"
                class="w-full h-full object-cover"
              >
              <UIcon
                v-else
                name="i-lucide-package"
                class="w-8 h-8 text-slate-400"
              />
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-slate-900 dark:text-white truncate">
                {{ item.product_name }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400 capitalize">
                Qty: {{ item.quantity }} &bull; {{ item.unit_price_formatted }}
              </p>
            </div>
            <div class="text-right">
              <p class="font-semibold text-slate-900 dark:text-white">
                {{ item.subtotal_formatted }}
              </p>
            </div>
          </div>
        </div>

        <!-- Order Footer -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-200/50 dark:border-slate-700/50 text-slate-900 dark:text-white">
          <div class="flex items-center gap-6">
            <div class="text-sm">
              <span class="text-slate-500 dark:text-slate-400">Payment:</span>
              <span class="font-medium ml-1 flex items-center gap-1">
                <UIcon
                  :name="order.payment_success ? 'i-lucide-check-circle-2' : 'i-lucide-clock-3'"
                  class="w-4 h-4"
                  :class="order.payment_success ? 'text-emerald-500' : 'text-amber-500'"
                />
                {{ order.payment_status }}
              </span>
            </div>
            <div
              v-if="order.tracking_id"
              class="text-sm"
            >
              <span class="text-slate-500 dark:text-slate-400">Tracking:</span>
              <span class="font-medium text-primary-600 dark:text-primary-400 ml-1">{{ order.tracking_id }}</span>
            </div>
          </div>
          <div class="flex items-center gap-6">
            <div class="text-right">
              <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-bold">
                Order Total
              </p>
              <p class="text-xl font-bold bg-gradient-to-r from-primary-600 to-primary-400 bg-clip-text text-transparent">
                {{ order.total_formatted }}
              </p>
            </div>
            <UButton
              :to="`/orders/${order.uuid}`"
              color="primary"
              variant="soft"
              class="transition-transform active:scale-95"
            >
              <template #trailing>
                <UIcon name="i-lucide-arrow-right" />
              </template>
              View Details
            </UButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="glass-card p-16 text-center animate-in fade-in slide-in-from-bottom-4 duration-500"
    >
      <div class="w-20 h-20 mx-auto bg-slate-100 dark:bg-slate-800/50 rounded-full flex items-center justify-center mb-6">
        <UIcon
          name="i-lucide-package-open"
          class="w-10 h-10 text-slate-400"
        />
      </div>
      <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
        No orders found
      </h3>
      <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto">
        {{ activeTab === 'all' ? "You haven't placed any orders yet. Explore our shop to find something you love!" : `It looks like you don't have any orders marked as ${activeTab} at the moment.` }}
      </p>
      <NuxtLink to="/shop">
        <UButton
          color="primary"
          size="lg"
          class="px-8 shadow-lg shadow-primary-500/25"
        >
          <template #leading>
            <UIcon name="i-lucide-shopping-bag" />
          </template>
          Start Shopping
        </UButton>
      </NuxtLink>
    </div>
  </div>
</template>
