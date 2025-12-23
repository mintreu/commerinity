<script setup lang="ts">
/**
 * Mentor Dashboard Component
 * Expert mentor dashboard with program management
 * Shows mentees, sessions, programs, and mentorship income
 */

import type { User } from '~/types/user'

const user = useCurrentUser() as Ref<User | null>
const { formatCurrency } = useBranding()

// Mock data - will be replaced with API calls
const stats = ref({
  totalMentees: 86,
  activeMentees: 42,
  monthlyIncome: 125600,
  pendingPayouts: 28500,
  sessions: 12,
  programs: 4,
  avgRating: 4.9,
  completionRate: 92
})

const activePrograms = ref([
  {
    id: 1,
    title: 'Business Mastery',
    enrolled: 24,
    progress: 68,
    revenue: 48000,
    nextSession: 'Tomorrow, 10:00 AM'
  },
  {
    id: 2,
    title: 'Leadership Excellence',
    enrolled: 18,
    progress: 45,
    revenue: 36000,
    nextSession: 'Thursday, 2:00 PM'
  }
])

const upcomingSessions = ref([
  {
    id: 1,
    program: 'Business Mastery',
    topic: 'Week 8: Scaling Strategies',
    time: '10:00 AM',
    date: 'Tomorrow',
    attendees: 22
  },
  {
    id: 2,
    program: 'Leadership Excellence',
    topic: 'Module 5: Team Building',
    time: '2:00 PM',
    date: 'Thursday',
    attendees: 16
  }
])

const recentActivity = ref([
  {
    id: 1,
    type: 'commission' as const,
    title: 'Program Enrollment',
    description: 'Business Mastery - 3 new enrollments',
    amount: 9000,
    timestamp: new Date(Date.now() - 3600000)
  },
  {
    id: 2,
    type: 'level_up' as const,
    title: 'Achievement Unlocked',
    description: 'Master Mentor Status',
    timestamp: new Date(Date.now() - 86400000)
  },
  {
    id: 3,
    type: 'referral' as const,
    title: 'New Mentee',
    description: 'Vikash Gupta joined your program',
    timestamp: new Date(Date.now() - 172800000)
  }
])

const quickActions = computed(() => [
  {
    label: 'New Session',
    icon: 'i-lucide-video',
    to: '/sessions/new',
    color: 'primary' as const,
    description: 'Schedule session'
  },
  {
    label: 'Mentees',
    icon: 'i-lucide-graduation-cap',
    to: '/mentees',
    color: 'success' as const,
    badge: stats.value.activeMentees
  },
  {
    label: 'Programs',
    icon: 'i-lucide-book-open',
    to: '/programs',
    color: 'purple' as const,
    badge: stats.value.programs
  },
  {
    label: 'Analytics',
    icon: 'i-lucide-bar-chart-2',
    to: '/analytics',
    color: 'amber' as const
  }
])

const topMentees = ref([
  { name: 'Vikash Gupta', program: 'Business Mastery', progress: 92, rating: 5 },
  { name: 'Sneha Reddy', program: 'Leadership Excellence', progress: 88, rating: 5 },
  { name: 'Karan Singh', program: 'Business Mastery', progress: 76, rating: 4.8 }
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
        title="Active Mentees"
        :value="`${stats.activeMentees}/${stats.totalMentees}`"
        icon="i-lucide-graduation-cap"
        color="primary"
        :trend="{ value: 12, label: 'new this month' }"
        to="/mentees"
      />
      <CommonStatCard
        title="Monthly Income"
        :value="formatCurrency(stats.monthlyIncome)"
        icon="i-lucide-indian-rupee"
        color="success"
        :trend="{ value: 24, label: 'vs last month' }"
        to="/income"
      />
      <CommonStatCard
        title="Active Programs"
        :value="stats.programs"
        subtitle="92% completion rate"
        icon="i-lucide-book-open"
        color="purple"
        to="/programs"
      />
      <CommonStatCard
        title="Avg Rating"
        :value="stats.avgRating.toFixed(1)"
        subtitle="Master Mentor"
        icon="i-lucide-star"
        color="amber"
        to="/reviews"
      />
    </div>

    <!-- Active Programs -->
    <div class="glass-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <UIcon
            name="i-lucide-book-open"
            class="w-5 h-5 text-purple-500"
          />
          Active Programs
        </h2>
        <UButton
          to="/programs/new"
          variant="soft"
          size="sm"
        >
          Create Program
        </UButton>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="program in activePrograms"
          :key="program.id"
          class="p-4 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-slate-900 dark:text-white">
                {{ program.title }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Next: {{ program.nextSession }}
              </p>
            </div>
            <UBadge
              color="success"
              variant="soft"
            >
              {{ formatCurrency(program.revenue) }}
            </UBadge>
          </div>

          <div class="flex items-center gap-4 mb-3">
            <div class="flex items-center gap-1 text-sm text-slate-600 dark:text-slate-400">
              <UIcon
                name="i-lucide-users"
                class="w-4 h-4"
              />
              {{ program.enrolled }} enrolled
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-slate-600 dark:text-slate-400">Progress</span>
              <span class="font-medium text-slate-900 dark:text-white">{{ program.progress }}%</span>
            </div>
            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full transition-all duration-500"
                :style="{ width: `${program.progress}%` }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Sessions -->
    <div class="glass-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <UIcon
            name="i-lucide-video"
            class="w-5 h-5 text-blue-500"
          />
          Upcoming Sessions
        </h2>
        <NuxtLink
          to="/sessions"
          class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
        >
          View all
        </NuxtLink>
      </div>

      <div class="space-y-4">
        <div
          v-for="session in upcomingSessions"
          :key="session.id"
          class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
              <UIcon
                name="i-lucide-video"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <p class="font-medium text-slate-900 dark:text-white">
                {{ session.topic }}
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ session.program }}
              </p>
            </div>
          </div>

          <div class="text-right">
            <p class="font-medium text-slate-900 dark:text-white">
              {{ session.date }}, {{ session.time }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              {{ session.attendees }} attendees
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Top Mentees -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Top Performing Mentees
            </h2>
            <NuxtLink
              to="/mentees"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              View all
            </NuxtLink>
          </div>

          <div class="space-y-4">
            <div
              v-for="(mentee, index) in topMentees"
              :key="mentee.name"
              class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl"
            >
              <div class="flex items-center gap-3">
                <span
                  class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                  :class="index === 0 ? 'bg-amber-500' : index === 1 ? 'bg-slate-400' : 'bg-amber-700'"
                >
                  {{ index + 1 }}
                </span>
                <UAvatar
                  :alt="mentee.name"
                  size="sm"
                />
                <div>
                  <p class="font-medium text-slate-900 dark:text-white">
                    {{ mentee.name }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ mentee.program }}
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <div class="text-right">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ mentee.progress }}%
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    progress
                  </p>
                </div>
                <div class="flex items-center gap-1">
                  <UIcon
                    name="i-lucide-star"
                    class="w-4 h-4 text-amber-500"
                  />
                  <span class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ mentee.rating }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Revenue Chart Placeholder -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Revenue Overview
            </h2>
            <UButtonGroup size="sm">
              <UButton
                variant="soft"
                color="primary"
              >
                Month
              </UButton>
              <UButton variant="ghost">
                Quarter
              </UButton>
              <UButton variant="ghost">
                Year
              </UButton>
            </UButtonGroup>
          </div>

          <div class="h-48 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl flex items-center justify-center">
            <div class="text-center">
              <UIcon
                name="i-lucide-trending-up"
                class="w-12 h-12 text-purple-400 mx-auto mb-2"
              />
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Revenue chart coming soon
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                This month: {{ formatCurrency(stats.monthlyIncome) }}
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
          :activities="recentActivity"
          title="Recent Activity"
          view-all-link="/activity"
        />

        <!-- Payout Summary -->
        <div class="glass-card p-6">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
            Earnings Summary
          </h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-600 dark:text-slate-400">Available Balance</span>
              <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatCurrency(stats.monthlyIncome - stats.pendingPayouts) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-600 dark:text-slate-400">Pending</span>
              <span class="text-sm font-medium text-amber-600 dark:text-amber-400">
                {{ formatCurrency(stats.pendingPayouts) }}
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
                Request Payout
              </UButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Onboarding Alert -->
    <UAlert
      v-if="user && !user.onboarded"
      color="warning"
      variant="soft"
      title="Complete Your Profile"
      description="Finish setting up your mentor profile to start accepting mentees."
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
