<script setup lang="ts">
/**
 * Support Center - Premium Mintreu Design
 * Displays user's support tickets with stats and quick filters
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { loading, fetchTickets } = useHelpdesk()
const tickets = ref([])
const page = ref(1)
const pageSize = 8
const statusFilter = ref('all')
const priorityFilter = ref('all')
const searchQuery = ref('')

const filteredTickets = computed(() => {
  let filtered = tickets.value
  if (statusFilter.value !== 'all') filtered = filtered.filter(t => t.status === statusFilter.value)
  if (priorityFilter.value !== 'all') filtered = filtered.filter(t => t.priority === priorityFilter.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(t => t.title.toLowerCase().includes(q) || t.uuid.toLowerCase().includes(q))
  }
  return filtered
})

const pageCount = computed(() => Math.ceil(filteredTickets.value.length / pageSize))
const paginatedTickets = computed(() => filteredTickets.value.slice((page.value - 1) * pageSize, page.value * pageSize))
const openCount = computed(() => tickets.value.filter(t => t.status === 'open').length)
const resolvedCount = computed(() => tickets.value.filter(t => t.status === 'resolved').length)

const statsData = computed(() => [
  { label: 'Total Tickets', value: tickets.value.length, color: 'primary', icon: 'i-lucide-ticket' },
  { label: 'Active Issues', value: openCount.value, color: 'warning', icon: 'i-lucide-flame' },
  { label: 'Resolved Tickets', value: resolvedCount.value, color: 'success', icon: 'i-lucide-check-circle' }
])

onMounted(async () => {
  tickets.value = await fetchTickets()
})

const columns = [
  { key: 'uuid', label: 'Ticket ID' },
  { key: 'title', label: 'Subject' },
  { key: 'priority', label: 'Priority' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Created On' },
  { key: 'actions', label: '' }
]

const getPriorityColor = (priority: string) => {
  const map: Record<string, string> = {
    urgent: 'red',
    high: 'orange',
    medium: 'amber',
    low: 'green'
  }
  return map[priority] || 'gray'
}

const getStatusColor = (status: string) => {
  const map: Record<string, string> = {
    open: 'warning',
    resolved: 'success',
    closed: 'gray'
  }
  return map[status] || 'blue'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="relative overflow-hidden glass-card p-8 border-none bg-gradient-to-br from-white/40 to-slate-50/20 dark:from-slate-900/40 dark:to-slate-900/10">
      <div class="absolute -top-12 -right-12 w-64 h-64 bg-primary-500/10 blur-3xl rounded-full" />
      <div class="relative flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-1">
          <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
            Support <span class="text-primary-600 dark:text-primary-400">Center</span>
          </h1>
          <p class="text-slate-500 dark:text-slate-400 font-medium">
            We're here to help you 24/7. Track your active resolutions here.
          </p>
        </div>
        <UButton
          to="/helpdesk/create"
          size="xl"
          color="primary"
          class="rounded-2xl font-black px-8 shadow-xl shadow-primary-500/20 hover:scale-105 transition-all"
        >
          <template #leading>
            <UIcon name="i-lucide-plus-circle" />
          </template>
          Open New Ticket
        </UButton>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="stat in statsData"
        :key="stat.label"
        class="glass-card p-6 border-none flex items-center gap-5 group hover:bg-white dark:hover:bg-slate-800 transition-all duration-300"
      >
        <div :class="`w-14 h-14 rounded-2xl flex items-center justify-center bg-${stat.color}-500/10 text-${stat.color}-500 group-hover:bg-${stat.color}-500/20 transition-colors`">
          <UIcon
            :name="stat.icon"
            class="w-7 h-7"
          />
        </div>
        <div>
          <span class="text-[10px] uppercase font-black tracking-widest text-slate-400">{{ stat.label }}</span>
          <p class="text-2xl font-black text-slate-900 dark:text-white">
            {{ stat.value }}
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="glass-card p-0 border-none overflow-hidden">
      <!-- Toolbar -->
      <div class="p-6 bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-200/50 dark:border-slate-700/50 flex flex-col lg:flex-row gap-4">
        <div class="flex-1">
          <UInput
            v-model="searchQuery"
            placeholder="Search by ID or Subject..."
            icon="i-lucide-search"
            size="xl"
            class="rounded-2xl"
            variant="none"
            :ui="{ base: 'bg-white dark:bg-slate-900 ring-1 ring-slate-200 dark:ring-slate-700' }"
          />
        </div>
        <div class="flex gap-3">
          <USelectMenu
            v-model="statusFilter"
            :options="[{ label: 'All Statuses', value: 'all' }, { label: 'Open', value: 'open' }, { label: 'Resolved', value: 'resolved' }, { label: 'Closed', value: 'closed' }]"
            class="w-40"
            size="xl"
          />
          <USelectMenu
            v-model="priorityFilter"
            :options="[{ label: 'All Priorities', value: 'all' }, { label: 'Low', value: 'low' }, { label: 'Medium', value: 'medium' }, { label: 'High', value: 'high' }, { label: 'Urgent', value: 'urgent' }]"
            class="w-40"
            size="xl"
          />
        </div>
      </div>

      <!-- Tickets List -->
      <div
        v-if="loading && tickets.length === 0"
        class="p-20 flex flex-col items-center justify-center space-y-4"
      >
        <div class="w-12 h-12 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin" />
        <p class="text-xs font-black uppercase tracking-widest text-slate-400">
          Loading your tickets...
        </p>
      </div>

      <div
        v-else-if="filteredTickets.length === 0"
        class="p-20 text-center"
      >
        <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-6 text-slate-400">
          <UIcon
            name="i-lucide-ticket-x"
            class="w-10 h-10"
          />
        </div>
        <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">
          No tickets found
        </h3>
        <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto">
          We couldn't find any tickets matching your search criteria.
        </p>
        <UButton
          color="primary"
          variant="soft"
          @click="searchQuery = ''; statusFilter = 'all'; priorityFilter='all'"
        >
          Clear Filters
        </UButton>
      </div>

      <div
        v-else
        class="overflow-x-auto"
      >
        <UTable
          :rows="paginatedTickets"
          :columns="columns"
          class="w-full"
          :ui="{
            thead: 'bg-slate-50/30 dark:bg-slate-900/30',
            th: { base: 'text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400' },
            td: { base: 'text-sm font-medium text-slate-700 dark:text-slate-300' }
          }"
        >
          <template #uuid-data="{ row }">
            <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-slate-500">
              #{{ row.uuid.slice(0, 8).toUpperCase() }}
            </span>
          </template>

          <template #title-data="{ row }">
            <span class="font-bold text-slate-900 dark:text-white truncate max-w-xs block">
              {{ row.title }}
            </span>
          </template>

          <template #priority-data="{ row }">
            <UBadge
              :color="getPriorityColor(row.priority)"
              variant="soft"
              class="rounded-full px-3 text-[10px] font-black uppercase tracking-widest"
            >
              {{ row.priority }}
            </UBadge>
          </template>

          <template #status-data="{ row }">
            <UBadge
              :color="getStatusColor(row.status)"
              variant="subtle"
              class="rounded-full px-3 text-[10px] font-black uppercase tracking-widest"
            >
              {{ row.status }}
            </UBadge>
          </template>

          <template #created_at-data="{ row }">
            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
              <UIcon
                name="i-lucide-calendar"
                class="w-3.5 h-3.5"
              />
              {{ formatDate(row.created_at) }}
            </div>
          </template>

          <template #actions-data="{ row }">
            <div class="flex justify-end pr-4">
              <UButton
                :to="`/helpdesk/${row.uuid}`"
                color="primary"
                variant="ghost"
                class="hover:bg-primary-500/10 transition-colors"
                icon="i-lucide-arrow-right-circle"
              >
                Manage
              </UButton>
            </div>
          </template>
        </UTable>
      </div>

      <!-- Pagination Footer -->
      <div
        v-if="pageCount > 1"
        class="p-6 bg-slate-50/20 dark:bg-slate-900/20 border-t border-slate-200/50 dark:border-slate-700/50 flex items-center justify-between"
      >
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
          Showing page {{ page }} of {{ pageCount }}
        </p>
        <UPagination
          v-model="page"
          :total="filteredTickets.length"
          :page-count="pageSize"
          class="rounded-xl"
        />
      </div>
    </div>
  </div>
</template>
