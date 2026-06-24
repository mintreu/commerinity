<script setup lang="ts">
/**
 * RecentActivity - Activity feed for dashboard
 * Shows recent orders, transactions, or team activity
 */

interface ActivityItem {
  id: string | number
  type: 'order' | 'reward' | 'referral' | 'withdrawal' | 'level_up' | 'kyc'
  title: string
  description: string
  amount?: number
  status?: 'success' | 'pending' | 'failed'
  timestamp: string | Date
  icon?: string
}

interface Props {
  activities: ActivityItem[]
  title?: string
  emptyMessage?: string
  loading?: boolean
  viewAllLink?: string
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Recent Activity',
  emptyMessage: 'No recent activity',
  loading: false
})

const { formatCurrency, formatRelativeTime } = useBranding()

const typeConfig = {
  order: {
    icon: 'i-lucide-package',
    color: 'text-blue-600 dark:text-blue-400',
    bg: 'bg-blue-100 dark:bg-blue-900/30'
  },
  reward: {
    icon: 'i-lucide-sparkles',
    color: 'text-emerald-600 dark:text-emerald-400',
    bg: 'bg-emerald-100 dark:bg-emerald-900/30'
  },
  referral: {
    icon: 'i-lucide-user-plus',
    color: 'text-purple-600 dark:text-purple-400',
    bg: 'bg-purple-100 dark:bg-purple-900/30'
  },
  withdrawal: {
    icon: 'i-lucide-wallet',
    color: 'text-amber-600 dark:text-amber-400',
    bg: 'bg-amber-100 dark:bg-amber-900/30'
  },
  level_up: {
    icon: 'i-lucide-trophy',
    color: 'text-yellow-600 dark:text-yellow-400',
    bg: 'bg-yellow-100 dark:bg-yellow-900/30'
  },
  kyc: {
    icon: 'i-lucide-shield-check',
    color: 'text-green-600 dark:text-green-400',
    bg: 'bg-green-100 dark:bg-green-900/30'
  }
}

const statusColor = (status?: ActivityItem['status']) => {
  switch (status) {
    case 'success':
      return 'text-emerald-600 dark:text-emerald-400'
    case 'pending':
      return 'text-amber-600 dark:text-amber-400'
    case 'failed':
      return 'text-red-600 dark:text-red-400'
    default:
      return 'text-slate-600 dark:text-slate-400'
  }
}

const getConfig = (type: ActivityItem['type']) => typeConfig[type] || typeConfig.order
</script>

<template>
  <div class="glass-card p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
        {{ title }}
      </h2>
      <NuxtLink
        v-if="viewAllLink"
        :to="viewAllLink"
        class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
      >
        View all
      </NuxtLink>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="space-y-4"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="flex items-start gap-3 animate-pulse"
      >
        <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700" />
        <div class="flex-1 space-y-2">
          <div class="h-4 w-3/4 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="h-3 w-1/2 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <CommonEmptyState
      v-else-if="activities.length === 0"
      :icon="'i-lucide-activity'"
      :title="emptyMessage"
      description="Your activity will appear here"
      size="sm"
    />

    <!-- Activity List -->
    <div
      v-else
      class="space-y-4"
    >
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="flex items-start gap-3 group"
      >
        <!-- Icon -->
        <div
          :class="[
            'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0',
            getConfig(activity.type).bg
          ]"
        >
          <UIcon
            :name="activity.icon || getConfig(activity.type).icon"
            :class="['w-5 h-5', getConfig(activity.type).color]"
          />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                {{ activity.title }}
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ activity.description }}
              </p>
            </div>

            <!-- Amount or Status -->
            <div class="text-right flex-shrink-0">
              <p
                v-if="activity.amount !== undefined"
                :class="[
                  'text-sm font-semibold',
                  activity.amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'
                ]"
              >
                {{ activity.amount >= 0 ? '+' : '' }}{{ formatCurrency(activity.amount) }}
              </p>
              <p
                v-if="activity.status"
                :class="['text-xs font-medium capitalize', statusColor(activity.status)]"
              >
                {{ activity.status }}
              </p>
            </div>
          </div>

          <!-- Timestamp -->
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
            {{ formatRelativeTime(activity.timestamp) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
