<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 dark:bg-purple-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen relative z-10">
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl">
        <div class="flex flex-col items-center gap-4">
          <div class="w-12 h-12 border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
          <p class="text-slate-600 dark:text-slate-400 font-medium">Loading tickets...</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

      <!-- Header -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm mb-4">
          <NuxtLink to="/dashboard" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
            <Icon name="mdi:view-dashboard" class="w-4 h-4" />
            <span>Dashboard</span>
          </NuxtLink>
          <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
          <span class="text-slate-700 dark:text-slate-300 font-medium">Help Desk</span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <!-- Title Section -->
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Support Center</h1>
            <p class="text-slate-600 dark:text-slate-400 mb-6">Manage your support tickets and get help from our team</p>

            <!-- Stats -->
            <div class="flex flex-wrap gap-6">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                  <Icon name="mdi:clock-outline" class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">Open Tickets</div>
                  <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ openCount }}</div>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                  <Icon name="mdi:check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">Resolved</div>
                  <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ resolvedCount }}</div>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                  <Icon name="mdi:ticket" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">Total Tickets</div>
                  <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ filteredTickets.length }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <div class="flex-shrink-0">
            <NuxtLink to="/dashboard/helpdesk/create" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <Icon name="mdi:plus-circle" class="w-5 h-5" />
              <span>Create Ticket</span>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-4 shadow-xl">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div class="flex items-center gap-2">
            <Icon name="mdi:filter-variant" class="w-5 h-5 text-slate-500" />
            <select v-model="statusFilter" class="bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option value="all">All Status</option>
              <option value="open">Open Only</option>
              <option value="resolved">Resolved Only</option>
              <option value="closed">Closed Only</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <Icon name="mdi:sort" class="w-5 h-5 text-slate-500" />
            <select v-model="priorityFilter" class="bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option value="all">All Priority</option>
              <option value="high">High Priority</option>
              <option value="medium">Medium Priority</option>
              <option value="low">Low Priority</option>
            </select>
          </div>

          <div class="flex items-center gap-2 flex-1 max-w-md">
            <Icon name="mdi:magnify" class="w-5 h-5 text-slate-400" />
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search tickets..."
                class="flex-1 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
            />
          </div>
        </div>
      </div>

      <!-- Content -->
      <div v-if="filteredTickets.length === 0">
        <!-- Empty State -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-12 shadow-xl text-center">
          <div class="w-24 h-24 bg-slate-100 dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <Icon name="mdi:ticket-outline" class="w-12 h-12 text-slate-400" />
          </div>

          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            {{ searchQuery || statusFilter !== 'all' || priorityFilter !== 'all' ? 'No matching tickets found' : 'No Support Tickets Yet' }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 mb-8 max-w-md mx-auto">
            {{ searchQuery || statusFilter !== 'all' || priorityFilter !== 'all'
              ? 'Try adjusting your filters or search terms to find what you\'re looking for.'
              : 'Create your first support ticket to get help from our team. We\'re here to assist you!'
            }}
          </p>

          <div v-if="!searchQuery && statusFilter === 'all' && priorityFilter === 'all'" class="flex items-center justify-center gap-8 mb-8 flex-wrap">
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:lightning-bolt" class="w-5 h-5 text-blue-500" />
              <span class="text-slate-600 dark:text-slate-400">Quick Response</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:account-group" class="w-5 h-5 text-green-500" />
              <span class="text-slate-600 dark:text-slate-400">Expert Support</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:clock-fast" class="w-5 h-5 text-purple-500" />
              <span class="text-slate-600 dark:text-slate-400">24/7 Available</span>
            </div>
          </div>

          <NuxtLink v-if="!searchQuery && statusFilter === 'all' && priorityFilter === 'all'" to="/dashboard/helpdesk/create" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded-xl font-semibold shadow-lg transition-all duration-300 hover:-translate-y-1">
            <Icon name="mdi:plus" class="w-5 h-5" />
            <span>Create Your First Ticket</span>
          </NuxtLink>
        </div>
      </div>

      <div v-else class="space-y-6">
        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-4">
          <div
              v-for="(ticket, index) in paginatedTickets"
              :key="ticket.uuid"
              class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1"
          >
            <!-- Card Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="getStatusBgClass(ticket.status)">
                  <Icon :name="getStatusIcon(ticket.status)" class="w-6 h-6" :class="getStatusTextClass(ticket.status)" />
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">{{ ticket.uuid.slice(0, 8) }}</div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold flex items-center gap-1" :class="getPriorityBadgeClass(ticket.priority)">
                      <Icon :name="getPriorityIcon(ticket.priority)" class="w-3 h-3" />
                      {{ ticket.priority }}
                    </span>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold flex items-center gap-1" :class="getStatusBadgeClass(ticket.status)">
                      <Icon :name="getStatusIcon(ticket.status)" class="w-3 h-3" />
                      {{ ticket.status }}
                    </span>
                  </div>
                </div>
              </div>
              <NuxtLink :to="`/dashboard/helpdesk/${ticket.uuid}`" class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400 transition-colors">
                <Icon name="mdi:arrow-right" class="w-5 h-5" />
              </NuxtLink>
            </div>

            <!-- Card Content -->
            <div class="mb-4">
              <h3 class="font-semibold text-slate-900 dark:text-white mb-2 line-clamp-2">{{ ticket.title }}</h3>
              <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1">
                  <Icon name="mdi:calendar" class="w-4 h-4" />
                  <span>{{ formatDate(ticket.createdAt) }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <Icon name="mdi:clock" class="w-4 h-4" />
                  <span>{{ getTimeAgo(ticket.createdAt) }}</span>
                </div>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center gap-2">
              <NuxtLink :to="`/dashboard/helpdesk/${ticket.uuid}`" class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg transition-colors font-medium">
                <Icon name="mdi:eye" class="w-4 h-4" />
                <span>View Details</span>
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
              <tr class="bg-slate-50/80 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-600">
                <th class="text-left py-4 px-6 font-semibold text-slate-700 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:ticket" class="w-4 h-4" />
                    <span>Ticket</span>
                  </div>
                </th>
                <th class="text-left py-4 px-6 font-semibold text-slate-700 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:text" class="w-4 h-4" />
                    <span>Title</span>
                  </div>
                </th>
                <th class="text-left py-4 px-6 font-semibold text-slate-700 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:flag" class="w-4 h-4" />
                    <span>Priority</span>
                  </div>
                </th>
                <th class="text-left py-4 px-6 font-semibold text-slate-700 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:check-circle" class="w-4 h-4" />
                    <span>Status</span>
                  </div>
                </th>
                <th class="text-left py-4 px-6 font-semibold text-slate-700 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:calendar" class="w-4 h-4" />
                    <span>Created</span>
                  </div>
                </th>
                <th class="text-left py-4 px-6 font-semibold text-slate-700 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <Icon name="mdi:cog" class="w-4 h-4" />
                    <span>Actions</span>
                  </div>
                </th>
              </tr>
              </thead>
              <tbody>
              <tr
                  v-for="(ticket, index) in paginatedTickets"
                  :key="ticket.uuid"
                  class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
              >
                <td class="py-4 px-6">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getStatusBgClass(ticket.status)">
                      <Icon :name="getStatusIcon(ticket.status)" class="w-4 h-4" :class="getStatusTextClass(ticket.status)" />
                    </div>
                    <span class="font-mono font-semibold text-sm text-slate-600 dark:text-slate-400">{{ ticket.uuid.slice(0, 8) }}</span>
                  </div>
                </td>
                <td class="py-4 px-6">
                  <NuxtLink :to="`/dashboard/helpdesk/${ticket.uuid}`" class="font-medium text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2">
                    {{ ticket.title }}
                  </NuxtLink>
                </td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold" :class="getPriorityBadgeClass(ticket.priority)">
                      <Icon :name="getPriorityIcon(ticket.priority)" class="w-3 h-3" />
                      <span>{{ ticket.priority }}</span>
                    </span>
                </td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusBadgeClass(ticket.status)">
                      <Icon :name="getStatusIcon(ticket.status)" class="w-3 h-3" />
                      <span>{{ ticket.status }}</span>
                    </span>
                </td>
                <td class="py-4 px-6">
                  <div class="text-sm">
                    <div class="font-medium text-slate-900 dark:text-white">{{ formatDate(ticket.createdAt) }}</div>
                    <div class="text-slate-500 dark:text-slate-400 text-xs">{{ getTimeAgo(ticket.createdAt) }}</div>
                  </div>
                </td>
                <td class="py-4 px-6">
                  <NuxtLink :to="`/dashboard/helpdesk/${ticket.uuid}`" class="inline-flex items-center gap-1 px-3 py-1 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors text-sm font-medium">
                    <Icon name="mdi:eye-outline" class="w-4 h-4" />
                    <span>View</span>
                  </NuxtLink>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pageCount > 1" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-4 shadow-xl">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-slate-600 dark:text-slate-400">
              Showing {{ ((page - 1) * pageSize) + 1 }} to {{ Math.min(page * pageSize, filteredTickets.length) }} of {{ filteredTickets.length }} tickets
            </div>

            <div class="flex items-center gap-2">
              <button
                  @click="page--"
                  :disabled="page === 1"
                  class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors text-sm font-medium"
              >
                <Icon name="mdi:chevron-left" class="w-4 h-4" />
                <span>Previous</span>
              </button>

              <div class="flex items-center gap-1">
                <button
                    v-for="pageNum in visiblePages"
                    :key="pageNum"
                    @click="page = pageNum"
                    class="w-8 h-8 rounded-lg font-medium text-sm transition-colors"
                    :class="page === pageNum
                    ? 'bg-blue-600 text-white'
                    : 'bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300'"
                >
                  {{ pageNum }}
                </button>
              </div>

              <button
                  @click="page++"
                  :disabled="page >= pageCount"
                  class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors text-sm font-medium"
              >
                <span>Next</span>
                <Icon name="mdi:chevron-right" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRuntimeConfig, useToast, useSanctumFetch } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

definePageMeta({ layout: 'dashboard' })

/* Core */
const config = useRuntimeConfig()
const toast = useToast()
const isLoading = useState('pageLoading', () => true)

/* Types */
type Ticket = {
  uuid: string
  title: string
  status: string
  priority: string
  createdAt: string
}

/* State */
const tickets = ref<Ticket[]>([])
const page = ref(1)
const pageSize = ref(10)
const statusFilter = ref('all')
const priorityFilter = ref('all')
const searchQuery = ref('')

// Refs
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

/* Helper Functions */
function getStatusIcon(status: string) {
  const icons = {
    open: 'mdi:clock-outline',
    resolved: 'mdi:check-circle',
    closed: 'mdi:lock',
    pending: 'mdi:clock-fast'
  }
  return icons[status.toLowerCase() as keyof typeof icons] || 'mdi:help-circle'
}

function getStatusBgClass(status: string) {
  const classes = {
    open: 'bg-yellow-100 dark:bg-yellow-900/30',
    resolved: 'bg-green-100 dark:bg-green-900/30',
    closed: 'bg-slate-100 dark:bg-slate-700',
    pending: 'bg-blue-100 dark:bg-blue-900/30'
  }
  return classes[status.toLowerCase() as keyof typeof classes] || classes.pending
}

function getStatusTextClass(status: string) {
  const classes = {
    open: 'text-yellow-600 dark:text-yellow-400',
    resolved: 'text-green-600 dark:text-green-400',
    closed: 'text-slate-600 dark:text-slate-400',
    pending: 'text-blue-600 dark:text-blue-400'
  }
  return classes[status.toLowerCase() as keyof typeof classes] || classes.pending
}

function getStatusBadgeClass(status: string) {
  const classes = {
    open: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    resolved: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    closed: 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-400',
    pending: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
  }
  return classes[status.toLowerCase() as keyof typeof classes] || classes.pending
}

function getPriorityBadgeClass(priority: string) {
  const classes = {
    high: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    medium: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    low: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    urgent: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  }
  return classes[priority.toLowerCase() as keyof typeof classes] || classes.medium
}

function getPriorityIcon(priority: string) {
  const icons = {
    high: 'mdi:chevron-double-up',
    medium: 'mdi:minus',
    low: 'mdi:chevron-double-down',
    urgent: 'mdi:alert'
  }
  return icons[priority.toLowerCase() as keyof typeof icons] || 'mdi:minus'
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function getTimeAgo(dateString: string) {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor(diff / (1000 * 60))

  if (days > 0) return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  if (minutes > 0) return `${minutes}m ago`
  return 'Just now'
}

/* Computed */
const filteredTickets = computed(() => {
  let filtered = tickets.value

  // Apply status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(ticket => ticket.status.toLowerCase() === statusFilter.value)
  }

  // Apply priority filter
  if (priorityFilter.value !== 'all') {
    filtered = filtered.filter(ticket => ticket.priority.toLowerCase() === priorityFilter.value)
  }

  // Apply search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(ticket =>
        ticket.title.toLowerCase().includes(query) ||
        ticket.uuid.toLowerCase().includes(query) ||
        ticket.status.toLowerCase().includes(query) ||
        ticket.priority.toLowerCase().includes(query)
    )
  }

  return filtered
})

const pageCount = computed(() => Math.ceil(filteredTickets.value.length / pageSize.value))

const paginatedTickets = computed(() => {
  const start = (page.value - 1) * pageSize.value
  return filteredTickets.value.slice(start, start + pageSize.value)
})

const visiblePages = computed(() => {
  const total = pageCount.value
  const current = page.value
  const delta = 2

  let start = Math.max(1, current - delta)
  let end = Math.min(total, current + delta)

  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(total, start + 4)
    } else if (end === total) {
      start = Math.max(1, end - 4)
    }
  }

  const pages = []
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const openCount = computed(() => tickets.value.filter(t => t.status.toLowerCase() === 'open').length)
const resolvedCount = computed(() => tickets.value.filter(t => t.status.toLowerCase() === 'resolved').length)

/* API Functions */
async function fetchUserTickets() {
  try {
    isLoading.value = true
    const res: any = await useSanctumFetch(`${config.public.apiBase}/helpdesk/tickets`)
    const list = res.data ?? []
    tickets.value = list.map((t: any) => ({
      uuid: t.uuid,
      title: t.title,
      status: t.status,
      priority: t.priority,
      createdAt: t.created_at
    }))

    // Reset page when data changes
    page.value = 1
  } catch (e: any) {
    toast.error({
      title: 'Error',
      message: e?.data?.message || 'Failed to load support tickets'
    })
  } finally {
    isLoading.value = false
  }
}

// Watch filters and reset page
watch([statusFilter, priorityFilter, searchQuery], () => {
  page.value = 1
})

// Event handlers
function handleEsc(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    searchQuery.value = ''
  }
}

// Lifecycle
onMounted(async () => {
  document.addEventListener('keydown', handleEsc)

  await fetchUserTickets()

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEsc)
})
</script>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.7);
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
