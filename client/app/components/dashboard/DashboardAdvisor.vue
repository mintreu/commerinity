<script setup lang="ts">
/**
 * Advisor Dashboard Component
 * Professional advisor dashboard with client management
 * Shows clients, appointments, consultations, and advisory income
 */

import type { User } from '~/types/user'

const user = useCurrentUser() as Ref<User | null>
const { formatCurrency } = useBranding()

// Mock data - will be replaced with API calls
const stats = ref({
  totalClients: 48,
  activeClients: 32,
  monthlyIncome: 45600,
  pendingPayouts: 12500,
  appointments: 8,
  consultations: 156,
  avgRating: 4.8
})

const upcomingAppointments = ref([
  {
    id: 1,
    client: 'Rahul Sharma',
    type: 'Financial Planning',
    time: '10:00 AM',
    date: 'Today',
    duration: '45 min'
  },
  {
    id: 2,
    client: 'Priya Patel',
    type: 'Investment Review',
    time: '2:30 PM',
    date: 'Today',
    duration: '30 min'
  },
  {
    id: 3,
    client: 'Amit Kumar',
    type: 'Portfolio Analysis',
    time: '11:00 AM',
    date: 'Tomorrow',
    duration: '60 min'
  }
])

const recentActivity = ref([
  {
    id: 1,
    type: 'commission' as const,
    title: 'Advisory Fee',
    description: 'From Rahul Sharma consultation',
    amount: 2500,
    timestamp: new Date(Date.now() - 3600000)
  },
  {
    id: 2,
    type: 'kyc' as const,
    title: 'Client Onboarded',
    description: 'Priya Patel completed KYC',
    timestamp: new Date(Date.now() - 86400000)
  },
  {
    id: 3,
    type: 'commission' as const,
    title: 'Referral Bonus',
    description: 'New client from network',
    amount: 1500,
    timestamp: new Date(Date.now() - 172800000)
  }
])

const quickActions = computed(() => [
  {
    label: 'Schedule',
    icon: 'i-lucide-calendar-plus',
    to: '/appointments/new',
    color: 'primary' as const,
    description: 'New appointment'
  },
  {
    label: 'Clients',
    icon: 'i-lucide-users',
    to: '/clients',
    color: 'success' as const,
    badge: stats.value.activeClients
  },
  {
    label: 'Reports',
    icon: 'i-lucide-file-text',
    to: '/reports',
    color: 'purple' as const
  },
  {
    label: 'My Team',
    icon: 'i-lucide-users-round',
    to: '/team',
    color: 'amber' as const,
    description: 'Recruited users'
  }
])

const topClients = ref([
  { name: 'Rahul Sharma', consultations: 12, revenue: 28500 },
  { name: 'Priya Patel', consultations: 8, revenue: 19200 },
  { name: 'Amit Kumar', consultations: 6, revenue: 15600 }
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
        title="Active Clients"
        :value="`${stats.activeClients}/${stats.totalClients}`"
        icon="i-lucide-users"
        color="primary"
        :trend="{ value: 8, label: 'new this month' }"
        to="/clients"
      />
      <CommonStatCard
        title="Monthly Income"
        :value="formatCurrency(stats.monthlyIncome)"
        icon="i-lucide-indian-rupee"
        color="success"
        :trend="{ value: 18, label: 'vs last month' }"
        to="/income"
      />
      <CommonStatCard
        title="Appointments"
        :value="stats.appointments"
        subtitle="This week"
        icon="i-lucide-calendar"
        color="purple"
        to="/appointments"
      />
      <CommonStatCard
        title="Avg Rating"
        :value="stats.avgRating.toFixed(1)"
        subtitle="From 156 reviews"
        icon="i-lucide-star"
        color="amber"
        to="/reviews"
      />
    </div>

    <!-- Today's Schedule -->
    <div class="glass-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
          <UIcon
            name="i-lucide-calendar-days"
            class="w-5 h-5 text-blue-500"
          />
          Upcoming Appointments
        </h2>
        <UButton
          to="/appointments"
          variant="soft"
          size="sm"
        >
          View Calendar
        </UButton>
      </div>

      <div
        v-if="upcomingAppointments.length === 0"
        class="text-center py-8"
      >
        <CommonEmptyState
          icon="i-lucide-calendar"
          title="No upcoming appointments"
          description="Your schedule is clear"
          action-label="Schedule Consultation"
          action-to="/appointments/new"
        />
      </div>

      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-3 gap-4"
      >
        <div
          v-for="appointment in upcomingAppointments"
          :key="appointment.id"
          class="p-4 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700"
        >
          <div class="flex items-start justify-between mb-3">
            <UBadge
              :color="appointment.date === 'Today' ? 'primary' : 'neutral'"
              variant="soft"
              size="xs"
            >
              {{ appointment.date }}
            </UBadge>
            <span class="text-sm font-medium text-slate-900 dark:text-white">
              {{ appointment.time }}
            </span>
          </div>
          <h3 class="font-medium text-slate-900 dark:text-white mb-1">
            {{ appointment.client }}
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
            {{ appointment.type }}
          </p>
          <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <UIcon
              name="i-lucide-clock"
              class="w-3 h-3"
            />
            {{ appointment.duration }}
          </div>
        </div>
      </div>
    </div>

    <!-- Upgrade Prompt -->
    <DashboardUserJourneyCard :user="user" />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Top Clients -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Top Clients
            </h2>
            <NuxtLink
              to="/clients"
              class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
            >
              View all
            </NuxtLink>
          </div>

          <div class="space-y-4">
            <div
              v-for="(client, index) in topClients"
              :key="client.name"
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
                  :alt="client.name"
                  size="sm"
                />
                <div>
                  <p class="font-medium text-slate-900 dark:text-white">
                    {{ client.name }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ client.consultations }} consultations
                  </p>
                </div>
              </div>
              <p class="font-semibold text-emerald-600 dark:text-emerald-400">
                {{ formatCurrency(client.revenue) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Performance Chart Placeholder -->
        <div class="glass-card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              Income Overview
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

          <div class="h-48 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl flex items-center justify-center">
            <div class="text-center">
              <UIcon
                name="i-lucide-bar-chart-3"
                class="w-12 h-12 text-blue-400 mx-auto mb-2"
              />
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Income chart coming soon
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
            Payout Summary
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
      description="Finish setting up your advisor profile to start accepting clients."
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
