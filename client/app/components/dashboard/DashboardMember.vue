<script setup lang="ts">
/**
 * Member Dashboard Component
 * Affiliate-focused dashboard for subscribed members
 * Shows earnings, referrals, network overview, and progress to next level
 * PWA-optimized with mobile-first design
 */

import type { User } from '~/types/user'

const user = useCurrentUser() as Ref<User | null>
const { formatCurrency } = useBranding()
const { fetchDashboardSummary, fetchCommissionEarnings, fetchTransactionVolume } = useTrends()
const { wallet, fetchWallet } = useWallet()
const { fetchTeam, team } = useNetwork()

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
const recentCommissions = ref<any[]>([])

// Fetch real data from API
onMounted(async () => {
  try {
    await Promise.all([
      fetchWallet(),
      fetchTeam(1, 5), // Get top 5 referrals
      loadDashboardData(),
      loadRecentCommissions()
    ])
  } catch (e) {
    console.error('Failed to load dashboard data:', e)
  } finally {
    loading.value = false
  }
})

const loadRecentCommissions = async () => {
  try {
    const response = await useSanctumFetch<any>(`${useRuntimeConfig().public.apiBase}/api/commissions?per_page=5`)
    if (response?.success) {
      recentCommissions.value = response.data.map((c: any) => ({
        id: c.uuid,
        type: 'commission' as const,
        title: c.type_label,
        description: `From ${c.from_user?.name || 'Network'}`,
        amount: c.net_amount / 100, // paisa to rupees
        timestamp: new Date(c.created_at)
      }))
    }
  } catch (e) {
    console.error('Failed to load recent commissions:', e)
  }
}

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

const quickActions = computed(() => [
  {
    label: 'Share',
    icon: 'i-lucide-share-2',
    onClick: openShareModal,
    color: 'primary' as const
  },
  {
    label: 'Network',
    icon: 'i-lucide-users',
    to: '/network',
    color: 'success' as const
  },
  {
    label: 'Withdraw',
    icon: 'i-lucide-wallet',
    to: '/wallet/withdraw',
    color: 'primary' as const
  },
  {
    label: 'Shop',
    icon: 'i-lucide-shopping-bag',
    to: '/shop',
    color: 'warning' as const
  }
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
</script>

<template>
  <div class="space-y-4 md:space-y-6">
    <!-- Dashboard Header -->
    <DashboardDashboardHeader
      :user="user"
      :show-level="true"
      :show-onboarding-progress="true"
    />

    <!-- Dashboard Notices (Promotional messages from admin) -->
    <DashboardDashboardNotices />

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-3 md:gap-4">
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
        color="primary"
        to="/network"
        :loading="loading"
      />
      <CommonStatCard
        title="Pending"
        :value="formatCurrency(stats.pendingCommission)"
        icon="i-lucide-clock"
        color="warning"
        :loading="loading"
      />
    </div>

    <!-- Level Progress Card (Mobile-optimized) -->
    <div class="glass-card p-4 md:p-6">
      <div class="flex flex-col gap-4">
        <!-- Top Row: Level Info + Button -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <CommonProgressRing
              :progress="levelProgress.progress"
              size="md"
              color="primary"
            />
            <div>
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-award"
                  class="w-5 h-5 text-amber-500"
                />
                <span class="font-bold text-slate-900 dark:text-white">{{ levelProgress.currentLevel }}</span>
              </div>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ levelProgress.currentReferrals }}/{{ levelProgress.requiredReferrals }} to {{ levelProgress.nextLevel }}
              </p>
            </div>
          </div>
          <UButton
            color="primary"
            size="sm"
            @click="openShareModal"
          >
            <UIcon
              name="i-lucide-share-2"
              class="w-4 h-4 mr-1"
            />
            <span class="hidden sm:inline">Share</span>
          </UButton>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600 dark:text-slate-400">Progress</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ Math.round(levelProgress.progress) }}%</span>
          </div>
          <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 rounded-full transition-all duration-700"
              :style="{ width: `${levelProgress.progress}%` }"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <DashboardQuickActions
      :actions="quickActions"
      :columns="4"
    />

    <!-- Upgrade Prompt -->
    <DashboardUserJourneyCard :user="user" />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
      <!-- Left: Earnings & Network -->
      <div class="lg:col-span-2 space-y-4 md:space-y-6">
        <!-- Order Volume Chart -->
        <div class="glass-card p-4 md:p-6">
          <CommonChartsTrendChart
            type="line"
            :fetch-method="fetchTransactionVolume"
            title="Order Volume"
            height="180"
            show-controls
          />
        </div>

        <!-- Monthly Earnings Chart (Real Data) -->
        <div class="glass-card p-4 md:p-6">
          <CommonChartsTrendChart
            type="line"
            :fetch-method="fetchCommissionEarnings"
            title="Earnings Overview"
            height="180"
            show-controls
          />
        </div>

        <!-- Top Referrals -->
        <div class="glass-card p-4 md:p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base md:text-lg font-bold text-slate-900 dark:text-white">
              Top Referrals
            </h2>
            <NuxtLink
              to="/network"
              class="text-sm text-violet-600 dark:text-violet-400"
            >
              View all
            </NuxtLink>
          </div>

          <div class="space-y-3">
            <div
              v-for="(member, index) in team"
              :key="member.uuid"
              class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl"
            >
              <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center text-white text-xs font-bold">
                  {{ index + 1 }}
                </span>
                <UAvatar
                  :alt="member.name"
                  size="sm"
                />
                <div>
                  <p class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ member.name }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">
                    {{ member.type }}
                  </p>
                </div>
              </div>
              <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                Joined {{ formatDate(member.created_at, 'short') }}
              </p>
            </div>
            <div
              v-if="team.length === 0 && !loading"
              class="text-center py-4 text-sm text-slate-500"
            >
              No referrals yet. Share your code to grow!
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-4 md:space-y-6">
        <!-- Recent Activity -->
        <DashboardRecentActivity
          :activities="recentCommissions"
          title="Recent Commissions"
          view-all-link="/earnings/commissions"
        />

        <!-- Referral Code Card -->
        <div class="glass-card p-4 md:p-6">
          <h3 class="text-base font-bold text-slate-900 dark:text-white mb-3">
            Your Referral Code
          </h3>
          <div class="flex items-center gap-2 p-3 bg-slate-100 dark:bg-slate-800 rounded-xl">
            <code class="flex-1 text-lg font-mono font-bold text-violet-600 dark:text-violet-400">
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
            Share this code to earn commissions
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
