<script setup lang="ts">
/**
 * Regular User Dashboard Component
 * E-commerce focused dashboard for regular customers
 * Connected to real API data for orders, wallet, and stats
 */

import type { User } from '~/types/user'

const { user } = useUserType()
const { formatCurrency } = useBranding()
const { fetchRecentOrders, getOrderStats } = useOrders()
const { fetchWallet, wallet } = useWallet()
const { fetchDashboardSummary, fetchTransactionVolume } = useTrends()

const stats = ref({
  orders: 0,
  points: 0,
  rewards: 0,
  wishlist: 0
})

const dashboardData = ref<any>(null)
const recentOrdersData = ref<any[]>([])
const loading = ref(true)

// Fetch real data on mount
onMounted(async () => {
  loading.value = true
  try {
    // Parallel fetch for better performance
    const [statsRes, walletRes, summaryRes, recentOrdersRes] = await Promise.all([
      getOrderStats(),
      fetchWallet(),
      fetchDashboardSummary('month'),
      fetchRecentOrders(5)
    ])

    if (statsRes) {
      stats.value.orders = statsRes.total
    }

    if (wallet.value) {
      stats.value.points = wallet.value.points
    }

    if (summaryRes?.success) {
      dashboardData.value = summaryRes.data
      // You can extract more stats from summary if available
    }

    if (recentOrdersRes) {
      recentOrdersData.value = recentOrdersRes
    }

    // Wishlist count - assuming we have a way to get it, or default to 0 for now
    // stats.value.wishlist = ...
  } catch (e) {
    console.error('Failed to load dashboard data:', e)
  } finally {
    loading.value = false
  }
})

const quickActions = computed(() => [
  {
    label: 'Shop Now',
    icon: 'i-lucide-shopping-bag',
    to: '/shop',
    color: 'primary' as const,
    description: 'Browse products'
  },
  {
    label: 'My Orders',
    icon: 'i-lucide-package',
    to: '/orders',
    color: 'success' as const,
    badge: stats.value.orders > 0 ? stats.value.orders : undefined
  },
  {
    label: 'Wallet',
    icon: 'i-lucide-wallet',
    to: '/wallet',
    color: 'primary' as const,
    badge: wallet.value?.available_balance_formatted
  },
  {
    label: 'Support',
    icon: 'i-lucide-headphones',
    to: '/support',
    color: 'warning' as const
  }
])

// Activity Feed - Map real data if available
const activities = computed(() => {
  return recentOrdersData.value.map(order => ({
    id: order.uuid,
    type: 'order' as const,
    title: 'Order Placed',
    description: order.items?.[0]?.product_name || `Order #${order.order_number}`,
    amount: -order.total,
    status: order.payment_success ? 'success' as const : 'pending' as const,
    timestamp: new Date(order.created_at)
  }))
})
</script>

<template>
  <div class="space-y-6">
    <!-- Dashboard Header -->
    <DashboardHeader
      :user="user"
      :show-onboarding-progress="true"
    />

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <CommonStatCard
        title="Total Orders"
        :value="loading ? '...' : stats.orders"
        icon="i-lucide-package"
        color="primary"
        :trend="{ value: 12, label: 'vs last month' }"
        to="/orders"
      />
      <CommonStatCard
        title="Wallet Balance"
        :value="loading ? '...' : wallet?.available_balance_formatted || '₹0'"
        icon="i-lucide-wallet"
        color="success"
        to="/wallet"
      />
      <CommonStatCard
        title="Reward Points"
        :value="loading ? '...' : stats.points.toLocaleString()"
        icon="i-lucide-star"
        color="warning"
        subtitle="Current Balance"
      />
      <CommonStatCard
        title="Wishlist Items"
        :value="stats.wishlist"
        icon="i-lucide-heart"
        color="error"
        to="/wishlist"
      />
    </div>

    <!-- Upgrade Banner -->
    <DashboardUserJourneyCard :user="user" />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Chart & Orders -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Order Volume Chart -->
        <div class="glass-card p-6">
          <CommonChartsTrendChart
            type="line"
            :fetch-method="fetchTransactionVolume"
            title="Order Volume"
            height="220"
            show-controls
          />
        </div>

        <!-- Recent Orders -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
              <UIcon
                name="i-lucide-shopping-cart"
                class="text-primary-500"
              />
              Recent Orders
            </h2>
            <NuxtLink
              to="/orders"
              class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 underline-offset-4 hover:underline"
            >
              View all orders
            </NuxtLink>
          </div>

          <div
            v-if="loading"
            class="space-y-4"
          >
            <div
              v-for="i in 3"
              :key="i"
              class="h-20 bg-slate-100 dark:bg-slate-800/50 animate-pulse rounded-xl"
            />
          </div>

          <div
            v-else-if="recentOrdersData.length === 0"
            class="py-12"
          >
            <CommonEmptyState
              icon="i-lucide-package-open"
              title="No orders yet"
              description="Explore our premium collection and start your wellness journey today."
              action-label="Start Shopping"
              action-to="/shop"
            />
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="order in recentOrdersData"
              :key="order.uuid"
              class="flex items-center justify-between p-4 bg-slate-50/50 dark:bg-slate-800/20 border border-slate-200/50 dark:border-slate-700/50 rounded-2xl hover:bg-white dark:hover:bg-slate-800/50 transition-all duration-300 group shadow-sm hover:shadow-md"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center shadow-sm border border-slate-100 dark:border-slate-700">
                  <UIcon
                    name="i-lucide-package"
                    class="w-6 h-6 text-primary-500"
                  />
                </div>
                <div>
                  <p class="font-semibold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    #{{ order.order_number }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ order.items?.[0]?.product_name || 'Multiple Items' }}
                  </p>
                </div>
              </div>

              <div class="text-right">
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ order.total_formatted }}
                </p>
                <UBadge
                  :color="order.status_color"
                  variant="subtle"
                  size="xs"
                  class="capitalize"
                >
                  {{ order.status_label }}
                </UBadge>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <DashboardQuickActions
          :actions="quickActions"
          :columns="2"
        />

        <!-- Recent Activity -->
        <DashboardRecentActivity
          :activities="activities"
          title="Activity Feed"
          view-all-link="/activity"
        />
      </div>
    </div>

    <!-- Onboarding Alert (if not complete) -->
    <div
      v-if="user && !user.onboarded"
      class="animate-in fade-in slide-in-from-bottom-4 duration-700"
    >
      <div class="p-6 bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-3xl backdrop-blur-md">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <div class="flex items-center gap-4 text-center md:text-left">
            <div class="w-14 h-14 bg-amber-500/20 rounded-2xl flex items-center justify-center text-amber-500">
              <UIcon
                name="i-lucide-user-check"
                class="w-8 h-8"
              />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                Complete Your Profile
              </h3>
              <p class="text-sm text-slate-600 dark:text-slate-400 max-w-md">
                Finish setting up your account to unlock personalized recommendations and exclusive member benefits.
              </p>
            </div>
          </div>
          <UButton
            to="/onboarding"
            color="warning"
            size="lg"
            class="px-8 shadow-lg shadow-amber-500/20"
          >
            Complete Setup
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
