<template>
  <div class="p-4 sm:p-6 space-y-6 sm:space-y-8">
    <GlobalLoader v-if="isLoading" />
    <div v-else class="w-full h-full">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">Help Desk</h1>
        <NuxtLink to="/dashboard/helpdesk/create" class="self-start sm:self-auto">
          <button
              class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <span class="text-lg leading-none">＋</span>
            <span class="text-sm font-medium">New Ticket</span>
          </button>
        </NuxtLink>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="p-4 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
          <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Open Tickets</p>
          <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-yellow-600">{{ openCount }}</h2>
        </div>
        <div class="p-4 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
          <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Resolved Tickets</p>
          <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-green-600">{{ resolvedCount }}</h2>
        </div>
        <div class="p-4 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
          <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Tickets</p>
          <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-gray-100">{{ tickets.length }}</h2>
        </div>
      </div>

      <!-- Cards (mobile-first) -->
      <div class="md:hidden space-y-3" role="list" aria-label="Tickets">
        <NuxtLink
            v-for="ticket in paginatedTickets"
            :key="ticket.uuid"
            :to="`/dashboard/helpdesk/${ticket.uuid}`"
            class="block rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm p-4 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition"
            role="listitem"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-[11px] text-gray-500 dark:text-gray-400 font-mono truncate">{{ ticket.uuid }}</p>
              <h3 class="mt-0.5 text-base font-semibold text-gray-900 dark:text-gray-100 truncate">{{ ticket.title }}</h3>
            </div>
            <span
                :class="[
                'shrink-0 inline-block px-2 py-1 text-[10px] font-medium rounded-full',
                ticket.status === 'Open'
                  ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                  : ticket.status === 'Resolved'
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
              ]"
            >
              {{ ticket.status }}
            </span>
          </div>
          <div class="mt-2 flex items-center justify-between text-[11px] sm:text-xs">
            <span class="text-gray-700 dark:text-gray-300">Priority: <b>{{ ticket.priority }}</b></span>
            <span class="text-gray-500 dark:text-gray-400">{{ ticket.createdAt }}</span>
          </div>
        </NuxtLink>
      </div>

      <!-- Table (md+) -->
      <div class="hidden md:block overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
        <table class="min-w-full text-sm text-left bg-white dark:bg-gray-950 border-separate border-spacing-y-0">
          <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 font-medium">Ticket</th>
            <th class="px-4 py-3 font-medium">Title</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Priority</th>
            <th class="px-4 py-3 font-medium">Created At</th>
            <th class="px-4 py-3 font-medium sr-only">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="ticket in paginatedTickets"
              :key="ticket.uuid"
              class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition"
          >
            <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-mono">
              <NuxtLink :to="`/dashboard/helpdesk/${ticket.uuid}`" class="hover:underline">{{ ticket.uuid }}</NuxtLink>
            </td>
            <td class="px-4 py-3 text-gray-900 dark:text-gray-100 truncate max-w-xs">
              <NuxtLink :to="`/dashboard/helpdesk/${ticket.uuid}`" class="hover:underline">{{ ticket.title }}</NuxtLink>
            </td>
            <td class="px-4 py-3">
                <span
                    :class="[
                    'inline-block px-2 py-1 text-xs font-medium rounded-full',
                    ticket.status === 'Open'
                      ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                      : ticket.status === 'Resolved'
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                  ]"
                >
                  {{ ticket.status }}
                </span>
            </td>
            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ ticket.priority }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ ticket.createdAt }}</td>
            <td class="px-4 py-3 text-right">
              <NuxtLink
                  :to="`/dashboard/helpdesk/${ticket.uuid}`"
                  class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
              >
                View
              </NuxtLink>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center mt-6 gap-2">
        <button
            class="px-4 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50"
            :disabled="page === 1"
            @click="page--"
        >
          Prev
        </button>
        <button
            class="px-4 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50"
            :disabled="page >= pageCount"
            @click="page++"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { useRuntimeConfig, useSanctumFetch, useToast } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)
const toast = useToast()

const page = ref(1)
const pageSize = 5
const tickets = ref<Array<{ uuid: string; title: string; status: string; priority: string; createdAt: string }>>([])

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
      createdAt: new Date(t.created_at).toLocaleString()
    }))
  } catch (e) {
    toast.error({ title: 'Error', message: '❌ Failed to load tickets', timeout: 3000 })
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchUserTickets)

const pageCount = computed(() => Math.ceil(tickets.value.length / pageSize))
const paginatedTickets = computed(() => {
  const start = (page.value - 1) * pageSize
  return tickets.value.slice(start, start + pageSize)
})

const openCount = computed(() => tickets.value.filter(t => t.status === 'Open').length)
const resolvedCount = computed(() => tickets.value.filter(t => t.status === 'Resolved').length)
</script>
