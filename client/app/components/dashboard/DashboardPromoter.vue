<script setup lang="ts">
/**
 * Promoter Dashboard Component
 * Team leader dashboard with advanced Affiliate metrics
 * Shows team performance, leaderboards, challenges, and recruitment goals
 */

import type { User } from '~/types/user'

const { user } = useUserType()
const { formatCurrency, formatCompactNumber, formatDate } = useBranding()
const { fetchTeamGrowth, fetchTransactionVolume, fetchDashboardSummary } = useTrends()
const { fetchTeam, team } = useNetwork()

// Real data - will be replaced with API calls
const stats = ref({
  totalEarnings: 0,
  monthlyEarnings: 0,
  teamSize: 0,
  activeTeam: 0,
  targetProgress: 78,
  walletBalance: 0,
  pendingPayout: 0,
  leaderboardRank: 12
})

const loading = ref(true)
const recentActivity = ref<any[]>([])

// Fetch real data
onMounted(async () => {
  try {
    const [summaryRes, teamRes, activityRes] = await Promise.all([
      fetchDashboardSummary('month'),
      fetchTeam(1, 5),
      loadRecentActivity()
    ])

    if (summaryRes?.success && summaryRes.data) {
      const { wallet, team: teamData, commissions } = summaryRes.data
      stats.value.monthlyEarnings = wallet?.current?.credits || 0
      stats.value.totalEarnings = commissions?.current?.total || 0
      stats.value.pendingPayout = commissions?.current?.pending || 0
      stats.value.teamSize = teamData?.total_members || 0
      stats.value.activeTeam = teamData?.active_members || 0
    }
  } catch (e) {
    console.error('Failed to load promoter stats:', e)
  } finally {
    loading.value = false
  }
})

const loadRecentActivity = async () => {
  try {
    const response = await useSanctumFetch<any>(`${useRuntimeConfig().public.apiBase}/api/commissions?per_page=5`)
    if (response?.success) {
      recentActivity.value = response.data.map((c: any) => ({
        id: c.uuid,
        type: 'commission' as const,
        title: c.type_label,
        description: `From ${c.from_user?.name || 'Network'}`,
        amount: c.net_amount / 100,
        timestamp: new Date(c.created_at)
      }))
    }
  } catch (e) {
    console.error('Failed to load activity:', e)
  }
}

const challenges = ref<any[]>([])

const teamPerformance = ref([
  { name: 'Vikash Gupta', role: 'Member', sales: 28500, recruits: 5 },
  { name: 'Sneha Reddy', role: 'Member', sales: 24200, recruits: 3 },
  { name: 'Karan Singh', role: 'Member', sales: 19800, recruits: 4 },
  { name: 'Pooja Sharma', role: 'Member', sales: 16500, recruits: 2 }
])

const quickActions = computed(() => [
  {
    label: 'Recruit',
    icon: 'i-lucide-user-plus',
    to: '/recruitment',
    color: 'primary' as const,
    description: 'Add to team'
  },
  {
    label: 'Team Stats',
    icon: 'i-lucide-bar-chart-3',
    to: '/team',
    color: 'success' as const,
    badge: stats.value.activeTeam
  },
  {
    label: 'Leaderboard',
    icon: 'i-lucide-trophy',
    to: '/leaderboard',
    color: 'amber' as const,
    badge: `#${stats.value.leaderboardRank}`
  },
  {
    label: 'Marketing',
    icon: 'i-lucide-megaphone',
    to: '/marketing',
    color: 'purple' as const
  }
])
</script>

<template>
  <div class="space-y-6">
    <!-- Dashboard Header -->
    <DashboardDashboardHeader
      :user="user"
      :show-level="true"
      :show-onboarding-progress="false"
    />

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <CommonStatCard
        title="Total Earnings"
        :value="formatCurrency(stats.totalEarnings)"
        icon="i-lucide-trending-up"
        color="success"
        :trend="{ value: 32, label: 'vs last month' }"
        to="/earnings"
      />
      <CommonStatCard
        title="Team Size"
        :value="stats.teamSize"
        subtitle="42 active this month"
        icon="i-lucide-users"
        color="primary"
        :trend="{ value: 12, label: 'new this month' }"
        to="/team"
      />
      <CommonStatCard
        title="Target Progress"
        :value="`${stats.targetProgress}%`"
        icon="i-lucide-target"
        color="purple"
        to="/goals"
      />
      <CommonStatCard
        title="Leaderboard Rank"
        :value="`#${stats.leaderboardRank}`"
        subtitle="Top 5% this month"
        icon="i-lucide-trophy"
        color="amber"
        to="/leaderboard"
      />
    </div>

    <!-- Monthly Challenges -->
    <div class="glass-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <UIcon
            name="i-lucide-flame"
            class="w-5 h-5 text-orange-500"
          />
          Monthly Challenges
        </h2>
        <UBadge
          color="warning"
          variant="soft"
        >
          2 Active
        </UBadge>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="challenge in challenges"
          :key="challenge.id"
          class="p-4 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-medium text-slate-900 dark:text-white">
                {{ challenge.title }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Ends {{ challenge.deadline }}
              </p>
            </div>
            <UBadge
              color="success"
              variant="soft"
            >
              +{{ formatCurrency(challenge.reward) }}
            </UBadge>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Progress</span>
              <span class="font-medium text-slate-900 dark:text-white">
                {{ typeof challenge.progress === 'number' && challenge.progress > 100
                  ? formatCurrency(challenge.progress) + ' / ' + formatCurrency(challenge.target)
                  : challenge.progress + ' / ' + challenge.target
                }}
              </span>
            </div>
            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-emerald-500 to-green-500 rounded-full transition-all duration-500"
                :style="{ width: `${Math.min((challenge.progress / challenge.target) * 100, 100)}%` }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upgrade Prompt (for promoters who can become advisors) -->
    <DashboardUserJourneyCard :user="user" />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Team Performance -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Team Performance Table -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Top Team Performers
            </h2>
            <NuxtLink
              to="/team"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              View all team
            </NuxtLink>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700">
                  <th class="text-left py-3 px-2 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Member
                  </th>
                  <th class="text-right py-3 px-2 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Joined
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(member, index) in team"
                  :key="member.uuid"
                  class="border-b border-slate-100 dark:border-slate-800 last:border-0"
                >
                  <td class="py-3 px-2">
                    <div class="flex items-center gap-3">
                      <span
                        class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                        :class="index === 0 ? 'bg-amber-500' : index === 1 ? 'bg-slate-400' : index === 2 ? 'bg-amber-700' : 'bg-slate-300'"
                      >
                        {{ index + 1 }}
                      </span>
                      <div>
                        <p class="font-medium text-slate-900 dark:text-white">
                          {{ member.name }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">
                          {{ member.type }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 px-2 text-right text-xs text-slate-500 dark:text-slate-400">
                    {{ formatDate(member.created_at, 'short') }}
                  </td>
                </tr>
                <tr v-if="team.length === 0 && !loading">
                  <td
                    colspan="2"
                    class="py-6 text-center text-sm text-slate-500"
                  >
                    No team members found. Start recruiting!
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Team Growth Chart (Real Data) -->
        <div class="glass-card p-6">
          <CommonChartsTrendChart
            type="bar"
            :fetch-method="fetchTeamGrowth"
            title="Team Growth"
            height="180"
            show-controls
          />
        </div>

        <!-- Order Volume Chart (Real Data) -->
        <div class="glass-card p-6">
          <CommonChartsTrendChart
            type="line"
            :fetch-method="fetchTransactionVolume"
            title="Order Volume"
            height="180"
            show-controls
          />
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
          :activities="recentActivity"
          title="Team Activity"
          view-all-link="/activity"
        />

        <!-- Wallet Summary -->
        <div class="glass-card p-6">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
            Wallet Summary
          </h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-600 dark:text-slate-400">Available Balance</span>
              <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatCurrency(stats.walletBalance) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-600 dark:text-slate-400">Pending Payout</span>
              <span class="text-sm font-medium text-amber-600 dark:text-amber-400">
                {{ formatCurrency(stats.pendingPayout) }}
              </span>
            </div>
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
              <UButton
                to="/wallet/withdraw"
                block
                color="primary"
              >
                <UIcon
                  name="i-lucide-wallet"
                  class="w-4 h-4 mr-2"
                />
                Withdraw Funds
              </UButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
