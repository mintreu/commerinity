<script setup lang="ts">
import type { DashboardAppointment, DashboardProgram } from '~/types/dashboard'
import { useDashboardAppointments } from '~/composables/useDashboardAppointments'
import { useDashboardPrograms } from '~/composables/useDashboardPrograms'
import { useNetwork } from '~/composables/useNetwork'

/**
 * Advisor Dashboard Component
 * Professional advisor dashboard with client management
 * Shows clients, appointments, consultations, and advisory income
 */

const { user } = useUserType()
const { formatCurrency, formatDate } = useBranding()
const { fetchCommissionEarnings, fetchTransactionVolume, fetchDashboardSummary } = useTrends()
const { fetchTeam, team } = useNetwork()
const { fetchList: fetchAppointments } = useDashboardAppointments()
const { fetchList: fetchPrograms } = useDashboardPrograms()

// Real data for stats
const stats = ref({
  totalClients: 0,
  activeClients: 0,
  monthlyIncome: 0,
  pendingPayouts: 0,
  appointments: 0,
  consultations: 0,
  avgRating: 5.0
})

const loading = ref(true)
const appointmentsLoading = ref(false)
const programsLoading = ref(false)
const upcomingAppointments = ref<Array<{
  id: string
  client: string
  date: string
  time: string
  type: string
  duration: string
}>>([])
const activePrograms = ref<DashboardProgram[]>([])
const recentActivity = ref<any[]>([])

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

const formatAppointmentCard = (appointment: DashboardAppointment) => {
  const start = new Date(appointment.start_at)
  const end = appointment.end_at ? new Date(appointment.end_at) : null
  const durationMinutes = end ? Math.max(15, Math.round((end.getTime() - start.getTime()) / 60000)) : 30
  return {
    id: appointment.uuid,
    client: appointment.attendee?.name || 'Client',
    date: formatDate(start, 'short'),
    time: start.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }),
    type: appointment.meeting_mode === 'online' ? 'Online consultation' : 'Offline session',
    duration: `${durationMinutes} mins`
  }
}

const loadAppointments = async () => {
  appointmentsLoading.value = true
  try {
    const { items, meta } = await fetchAppointments({ per_page: 6, status: 'pending' })
    upcomingAppointments.value = items.map(formatAppointmentCard)
    stats.value.appointments = meta.total
    stats.value.consultations = meta.total
  } catch (e) {
    console.error('Failed to load appointments:', e)
  } finally {
    appointmentsLoading.value = false
  }
}

const loadPrograms = async () => {
  programsLoading.value = true
  try {
    const { items } = await fetchPrograms({ per_page: 4, status: 'ongoing' })
    activePrograms.value = items
    stats.value.consultations = Math.max(stats.value.consultations, items.length)
  } catch (e) {
    console.error('Failed to load programs:', e)
  } finally {
    programsLoading.value = false
  }
}

const quickActions = computed(() => [
  {
    label: 'Schedule',
    icon: 'i-lucide-calendar-plus',
    to: '/appointments/new',
    color: 'primary' as const,
    description: 'New appointment'
  },
  {
    label: 'Add Team Leader',
    icon: 'i-lucide-briefcase-plus',
    to: '/dashboard/team-leaders/new',
    color: 'success' as const,
    description: 'Grow your team'
  },
  {
    label: 'Clients',
    icon: 'i-lucide-users',
    to: '/clients',
    color: 'purple' as const,
    badge: stats.value.activeClients
  },
  {
    label: 'Reports',
    icon: 'i-lucide-file-text',
    to: '/reports',
    color: 'amber' as const
  }
])

const topClients = ref<any[]>([])

onMounted(async () => {
  loading.value = true
  try {
    const [summaryRes] = await Promise.all([
      fetchDashboardSummary('month'),
      loadRecentActivity(),
      loadAppointments(),
      loadPrograms()
    ])

    if (summaryRes?.success && summaryRes.data) {
      const { wallet, team: teamData, commissions } = summaryRes.data
      stats.value.monthlyIncome = wallet?.current?.credits || 0
      stats.value.pendingPayouts = commissions?.current?.pending || 0
      stats.value.totalClients = teamData?.total_members || 0
      stats.value.activeClients = teamData?.active_members || 0
    }
  } catch (e) {
    console.error('Failed to load advisor stats:', e)
  } finally {
    loading.value = false
  }
})
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
          to="/programs"
          variant="soft"
          size="sm"
        >
          View Programs
        </UButton>
      </div>

      <template v-if="programsLoading">
        <div class="space-y-2">
          <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4 animate-pulse" />
          <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2 animate-pulse" />
        </div>
      </template>

      <template v-else-if="activePrograms.length === 0">
        <CommonEmptyState
          icon="i-lucide-book-plus"
          title="No programs yet"
          description="Draft a program and invite your mentees."
          action-label="Create Program"
          action-to="/programs/new"
        />
      </template>

      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-2 gap-4"
      >
        <div
          v-for="program in activePrograms"
          :key="program.uuid"
          class="p-4 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-medium text-slate-900 dark:text-white">
                {{ program.title }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ formatDate(program.start_date || program.end_date || new Date(), 'short') }}
                · {{ program.status }}
              </p>
            </div>
            <UBadge
              color="success"
              variant="soft"
            >
              {{ program.participants.length }} participants
            </UBadge>
          </div>

          <div class="text-sm text-slate-500 dark:text-slate-400 mb-3">
            {{ program.location?.full_address || 'Virtual Program' }}
          </div>

          <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Ends {{ formatDate(program.end_date || program.start_date || new Date(), 'short') }}</span>
            <NuxtLink
              :to="`/programs/${program.uuid}`"
              class="text-primary hover:underline text-xs font-semibold"
            >
              View
            </NuxtLink>
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

        <!-- Income Overview (Real Data) -->
        <div class="glass-card p-6">
          <CommonChartsTrendChart
            type="line"
            :fetch-method="fetchCommissionEarnings"
            title="Income Overview"
            height="180"
            show-controls
          />
        </div>

        <!-- Order Volume (Real Data) -->
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
