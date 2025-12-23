<script setup lang="ts">
/**
 * Member Dashboard Component
 * MLM-focused dashboard for subscribed members
 * Shows earnings, referrals, network overview, and progress to next level
 */

import type { User } from '~/types/user'

const user = useCurrentUser() as Ref<User | null>
const { formatCurrency } = useBranding()
const { fetchDashboardSummary, fetchWalletComparison } = useTrends()
const { wallet, fetchWallet } = useWallet()
const config = useRuntimeConfig()

// Stats with real data
const stats = ref({
  totalEarnings: 0,
  monthlyEarnings: 0,
  referrals: 0,
  activeReferrals: 0,
  walletBalance: 0,
  pendingCommission: 0,
  ordersThisMonth: 0,
  teamSize: 0
})

const walletTrend = ref({
  credits_change: 0,
  debits_change: 0,
  count_change: 0
})

const levelProgress = ref({
  currentLevel: 'Bronze',
  nextLevel: 'Silver',
  progress: 0,
  requiredReferrals: 10,
  currentReferrals: 0
})

const loading = ref(true)

// Fetch real data from API
onMounted(async () => {
  try {
    await Promise.all([
      fetchWallet(),
      loadDashboardData()
    ])
  } catch (e) {
    console.error('Failed to load dashboard data:', e)
  } finally {
    loading.value = false
  }
})

const loadDashboardData = async () => {
  // Fetch dashboard summary
  const summaryResponse = await fetchDashboardSummary('month')
  if (summaryResponse?.success && summaryResponse.data) {
    const data = summaryResponse.data

    // Update stats from wallet comparison
    if (data.wallet) {
      stats.value.monthlyEarnings = data.wallet.current?.credits || 0
      walletTrend.value = data.wallet.changes || { credits_change: 0, debits_change: 0, count_change: 0 }
    }

    // Update team stats
    if (data.team) {
      stats.value.teamSize = data.team.total_members || 0
      stats.value.referrals = data.team.direct_referrals || 0
      stats.value.activeReferrals = data.team.active_members || 0

      // Update level progress
      levelProgress.value.currentReferrals = stats.value.referrals
      levelProgress.value.progress = Math.min(100, (stats.value.referrals / levelProgress.value.requiredReferrals) * 100)
    }

    // Update commission stats
    if (data.commissions) {
      stats.value.totalEarnings = data.commissions.current?.total || 0
      stats.value.pendingCommission = data.commissions.current?.pending || 0
    }
  }
}

// Watch wallet for balance updates
watch(wallet, (newWallet) => {
  if (newWallet) {
    stats.value.walletBalance = (newWallet.available_balance || 0) / 100 // Convert paisa to rupees
  }
}, { immediate: true })

const recentCommissions = ref([
  {
    id: 1,
    type: 'commission' as const,
    title: 'Direct Referral Commission',
    description: 'From Rahul Sharma\'s purchase',
    amount: 450,
    timestamp: new Date(Date.now() - 3600000)
  },
  {
    id: 2,
    type: 'commission' as const,
    title: 'Level 2 Commission',
    description: 'From network purchase',
    amount: 150,
    timestamp: new Date(Date.now() - 86400000)
  },
  {
    id: 3,
    type: 'referral' as const,
    title: 'New Referral Joined',
    description: 'Priya Patel joined your network',
    timestamp: new Date(Date.now() - 172800000)
  }
])

const quickActions = computed(() => [
  {
    label: 'Share Referral',
    icon: 'i-lucide-share-2',
    onClick: openShareModal,
    color: 'primary' as const,
    description: 'Invite friends'
  },
  {
    label: 'View Network',
    icon: 'i-lucide-users',
    to: '/network',
    color: 'success' as const,
    badge: stats.value.referrals
  },
  {
    label: 'Withdraw',
    icon: 'i-lucide-wallet',
    to: '/wallet/withdraw',
    color: 'purple' as const
  },
  {
    label: 'Shop Now',
    icon: 'i-lucide-shopping-bag',
    to: '/shop',
    color: 'amber' as const
  }
])

const topReferrals = ref([
  { name: 'Rahul Sharma', orders: 8, earnings: 1200, avatar: '' },
  { name: 'Priya Patel', orders: 5, earnings: 750, avatar: '' },
  { name: 'Amit Kumar', orders: 4, earnings: 600, avatar: '' }
])

const showShareModal = ref(false)

const copyReferralCode = () => {
  if (user.value?.referral_code) {
    navigator.clipboard.writeText(user.value.referral_code)
  }
}

const openShareModal = () => {
  showShareModal.value = true
}

const closeShareModal = () => {
  showShareModal.value = false
}
</script>

<template>
  <div class="space-y-6">
    <!-- Dashboard Header -->
    <DashboardDashboardHeader
      :user="user"
      :show-level="true"
      :show-onboarding-progress="true"
    />

    <!-- Dashboard Notices (Promotional messages from admin) -->
    <DashboardDashboardNotices />

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <CommonStatCard
        title="Total Earnings"
        :value="formatCurrency(stats.totalEarnings)"
        icon="i-lucide-trending-up"
        color="success"
        :trend="walletTrend.credits_change !== 0 ? { value: walletTrend.credits_change, label: 'vs last month' } : undefined"
        to="/earnings"
        :loading="loading"
      />
      <CommonStatCard
        title="Wallet Balance"
        :value="formatCurrency(stats.walletBalance)"
        icon="i-lucide-wallet"
        color="primary"
        to="/wallet"
        :loading="loading"
      />
      <CommonStatCard
        title="Active Referrals"
        :value="`${stats.activeReferrals}/${stats.referrals}`"
        icon="i-lucide-users"
        color="purple"
        :trend="stats.referrals > 0 ? { value: stats.referrals, label: 'total referrals' } : undefined"
        to="/network"
        :loading="loading"
      />
      <CommonStatCard
        title="Pending Commission"
        :value="formatCurrency(stats.pendingCommission)"
        icon="i-lucide-clock"
        color="amber"
        :loading="loading"
      />
    </div>

    <!-- Level Progress Card -->
    <div class="glass-card p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="flex items-center gap-4">
          <CommonProgressRing
            :progress="levelProgress.progress"
            size="lg"
            color="primary"
          />
          <div>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Current Level
            </p>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <UIcon
                name="i-lucide-award"
                class="w-5 h-5 text-amber-500"
              />
              {{ levelProgress.currentLevel }}
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
              {{ levelProgress.currentReferrals }}/{{ levelProgress.requiredReferrals }} referrals to reach {{ levelProgress.nextLevel }}
            </p>
          </div>
        </div>

        <div class="flex-1 max-w-md">
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-600 dark:text-slate-400">Progress to {{ levelProgress.nextLevel }}</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ levelProgress.progress }}%</span>
          </div>
          <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-700"
              :style="{ width: `${levelProgress.progress}%` }"
            />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            Refer {{ levelProgress.requiredReferrals - levelProgress.currentReferrals }} more friends to unlock Silver benefits
          </p>
        </div>

        <UButton
          color="primary"
          size="lg"
          @click="openShareModal"
        >
          <UIcon
            name="i-lucide-share-2"
            class="w-4 h-4 mr-2"
          />
          Share & Earn
        </UButton>
      </div>
    </div>

    <!-- Upgrade Prompt -->
    <DashboardUserJourneyCard :user="user" />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Earnings & Network -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Monthly Earnings Chart Placeholder -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Earnings Overview
            </h2>
            <UButtonGroup size="sm">
              <UButton
                variant="soft"
                color="primary"
              >
                Week
              </UButton>
              <UButton variant="ghost">
                Month
              </UButton>
              <UButton variant="ghost">
                Year
              </UButton>
            </UButtonGroup>
          </div>

          <!-- Chart Placeholder -->
          <div class="h-48 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl flex items-center justify-center">
            <div class="text-center">
              <UIcon
                name="i-lucide-bar-chart-3"
                class="w-12 h-12 text-blue-400 mx-auto mb-2"
              />
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Earnings chart coming soon
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                This month: {{ formatCurrency(stats.monthlyEarnings) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Top Referrals -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Top Performing Referrals
            </h2>
            <NuxtLink
              to="/network"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              View all
            </NuxtLink>
          </div>

          <div class="space-y-4">
            <div
              v-for="(referral, index) in topReferrals"
              :key="referral.name"
              class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl"
            >
              <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                  {{ index + 1 }}
                </span>
                <UAvatar
                  :alt="referral.name"
                  size="sm"
                />
                <div>
                  <p class="font-medium text-slate-900 dark:text-white">
                    {{ referral.name }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ referral.orders }} orders
                  </p>
                </div>
              </div>
              <p class="font-semibold text-emerald-600 dark:text-emerald-400">
                +{{ formatCurrency(referral.earnings) }}
              </p>
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
          :activities="recentCommissions"
          title="Recent Commissions"
          view-all-link="/earnings/commissions"
        />

        <!-- Referral Code Card -->
        <div class="glass-card p-6">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">
            Your Referral Code
          </h3>
          <div class="flex items-center gap-2 p-3 bg-slate-100 dark:bg-slate-800 rounded-xl">
            <code class="flex-1 text-lg font-mono font-bold text-blue-600 dark:text-blue-400">
              {{ user?.referral_code || 'Loading...' }}
            </code>
            <UButton
              icon="i-lucide-copy"
              variant="ghost"
              size="sm"
              @click="copyReferralCode"
            />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            Share this code with friends to earn commissions
          </p>
          <UButton
            block
            color="primary"
            variant="soft"
            class="mt-3"
            @click="openShareModal"
          >
            <UIcon
              name="i-lucide-share-2"
              class="w-4 h-4 mr-2"
            />
            Share Now
          </UButton>
        </div>
      </div>
    </div>
    <!-- Share Affiliate Modal -->
    <ShareAffiliateModal
      v-model:open="showShareModal"
      :referral-code="user?.referral_code || ''"
      :user-name="user?.name || 'Friend'"
    />
  </div>
</template>
