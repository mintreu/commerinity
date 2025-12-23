<script setup lang="ts">
/**
 * Regular User Dashboard Component
 * E-commerce focused dashboard for regular customers
 * Shows orders, points, and membership upgrade prompts
 */

import type { User } from '~/types/user'

const user = useCurrentUser() as Ref<User | null>
const { formatCurrency } = useBranding()

// Mock data - will be replaced with API calls
const stats = ref({
  orders: 12,
  points: 2450,
  rewards: 3,
  wishlist: 8
})

const recentOrders = ref([
  {
    id: 'ORD-12345',
    product: 'Premium Health Supplement',
    status: 'Processing',
    statusColor: 'warning' as const,
    amount: 2499,
    date: new Date(Date.now() - 86400000)
  },
  {
    id: 'ORD-12344',
    product: 'Organic Face Cream',
    status: 'Delivered',
    statusColor: 'success' as const,
    amount: 1299,
    date: new Date(Date.now() - 172800000)
  },
  {
    id: 'ORD-12343',
    product: 'Hair Growth Serum',
    status: 'Shipped',
    statusColor: 'primary' as const,
    amount: 1899,
    date: new Date(Date.now() - 259200000)
  }
])

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
    label: 'Wishlist',
    icon: 'i-lucide-heart',
    to: '/wishlist',
    color: 'purple' as const,
    badge: stats.value.wishlist > 0 ? stats.value.wishlist : undefined
  },
  {
    label: 'Support',
    icon: 'i-lucide-headphones',
    to: '/support',
    color: 'amber' as const
  }
])

const recentActivities = computed(() => [
  {
    id: 1,
    type: 'order' as const,
    title: 'Order Placed',
    description: 'Premium Health Supplement',
    amount: -2499,
    status: 'pending' as const,
    timestamp: new Date(Date.now() - 3600000)
  },
  {
    id: 2,
    type: 'order' as const,
    title: 'Order Delivered',
    description: 'Organic Face Cream',
    status: 'success' as const,
    timestamp: new Date(Date.now() - 172800000)
  }
])
</script>

<template>
  <div class="space-y-6">
    <!-- Dashboard Header -->
    <DashboardDashboardHeader
      :user="user"
      :show-onboarding-progress="true"
    />

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <CommonStatCard
        title="Total Orders"
        :value="stats.orders"
        icon="i-lucide-package"
        color="primary"
        :trend="{ value: 15, label: 'vs last month' }"
        to="/orders"
      />
      <CommonStatCard
        title="Reward Points"
        :value="stats.points.toLocaleString()"
        icon="i-lucide-star"
        color="amber"
        :trend="{ value: 150, label: 'earned this month' }"
      />
      <CommonStatCard
        title="Available Rewards"
        :value="stats.rewards"
        icon="i-lucide-gift"
        color="purple"
      />
      <CommonStatCard
        title="Wishlist Items"
        :value="stats.wishlist"
        icon="i-lucide-heart"
        color="danger"
        to="/wishlist"
      />
    </div>

    <!-- Upgrade Banner -->
    <DashboardUserJourneyCard :user="user" />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Orders -->
      <div class="lg:col-span-2">
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Recent Orders
            </h2>
            <NuxtLink
              to="/orders"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              View all
            </NuxtLink>
          </div>

          <div
            v-if="recentOrders.length === 0"
            class="text-center py-8"
          >
            <CommonEmptyState
              icon="i-lucide-package"
              title="No orders yet"
              description="Start shopping to see your orders here"
              action-label="Browse Products"
              action-to="/shop"
            />
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="order in recentOrders"
              :key="order.id"
              class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-xl flex items-center justify-center">
                  <UIcon
                    name="i-lucide-package"
                    class="w-6 h-6 text-slate-400"
                  />
                </div>
                <div>
                  <p class="font-medium text-slate-900 dark:text-white">
                    {{ order.product }}
                  </p>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ order.id }}
                  </p>
                </div>
              </div>

              <div class="text-right">
                <p class="font-semibold text-slate-900 dark:text-white">
                  {{ formatCurrency(order.amount) }}
                </p>
                <UBadge
                  :color="order.statusColor"
                  variant="soft"
                  size="xs"
                >
                  {{ order.status }}
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
          :activities="recentActivities"
          title="Activity"
          view-all-link="/activity"
        />
      </div>
    </div>

    <!-- Onboarding Alert (if not complete) -->
    <UAlert
      v-if="user && !user.onboarded"
      color="warning"
      variant="soft"
      title="Complete Your Profile"
      description="Finish setting up your profile to unlock all features and get personalized recommendations."
    >
      <template #actions>
        <UButton
          to="/onboarding"
          color="warning"
        >
          Complete Setup
        </UButton>
      </template>
    </UAlert>
  </div>
</template>
