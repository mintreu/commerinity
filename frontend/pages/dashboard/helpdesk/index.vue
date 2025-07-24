<template>
  <div class="p-6 space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">Help Desk</h1>
      <NuxtLink to="/dashboard/helpdesk/create">
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          + New Ticket
        </button>
      </NuxtLink>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow">
        <p class="text-sm text-gray-500 dark:text-gray-400">Open Tickets</p>
        <h2 class="text-2xl font-bold text-yellow-600">{{ openCount }}</h2>
      </div>
      <div class="p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow">
        <p class="text-sm text-gray-500 dark:text-gray-400">Resolved Tickets</p>
        <h2 class="text-2xl font-bold text-green-600">{{ resolvedCount }}</h2>
      </div>
      <div class="p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Tickets</p>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ tickets.length }}</h2>
      </div>
    </div>

    <!-- Ticket Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow">
      <table class="min-w-full text-sm text-left bg-white dark:bg-gray-900">
        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
        <tr>
          <th class="px-4 py-2 font-medium">ID</th>
          <th class="px-4 py-2 font-medium">Title</th>
          <th class="px-4 py-2 font-medium">Status</th>
          <th class="px-4 py-2 font-medium">Priority</th>
          <th class="px-4 py-2 font-medium">Created At</th>
          <th class="px-4 py-2 font-medium">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="ticket in paginatedTickets"
            :key="ticket.url"
            class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
        >
          <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ ticket.id }}</td>
          <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ ticket.title }}</td>
          <td class="px-4 py-2">
            <span
                :class="[
                'inline-block px-2 py-1 text-xs font-medium rounded',
                ticket.status === 'Open'
                  ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                  : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
              ]"
            >
              {{ ticket.status }}
            </span>
          </td>
          <td class="px-4 py-2 text-gray-800 dark:text-gray-100">{{ ticket.priority }}</td>
          <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ ticket.createdAt }}</td>
          <td class="px-4 py-2">
            <NuxtLink
                :to="`/dashboard/helpdesk/${ticket.url}`"
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
          class="px-4 py-2 border rounded bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
          :disabled="page === 1"
          @click="page--"
      >
        Prev
      </button>
      <button
          class="px-4 py-2 border rounded bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
          :disabled="page >= pageCount"
          @click="page++"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})
const page = ref(1)
const pageSize = 5

// Add `url` key to each ticket for dynamic route access
const tickets = ref([
  { id: 1, url: 'login-issue', title: 'Login issue', status: 'Open', priority: 'High', createdAt: '2025-07-21' },
  { id: 2, url: 'dashboard-bug', title: 'Bug in dashboard', status: 'Resolved', priority: 'Medium', createdAt: '2025-07-20' },
  { id: 3, url: 'file-upload-broken', title: 'File upload broken', status: 'Open', priority: 'Low', createdAt: '2025-07-19' },
  { id: 4, url: 'button-misaligned', title: 'Button misaligned', status: 'Open', priority: 'Low', createdAt: '2025-07-18' },
  { id: 5, url: 'password-reset-issue', title: 'Password reset not working', status: 'Open', priority: 'High', createdAt: '2025-07-17' },
  { id: 6, url: 'billing-error', title: 'Billing error', status: 'Resolved', priority: 'Medium', createdAt: '2025-07-16' }
])

const pageCount = computed(() => Math.ceil(tickets.value.length / pageSize))

const paginatedTickets = computed(() => {
  const start = (page.value - 1) * pageSize
  return tickets.value.slice(start, start + pageSize)
})

const openCount = computed(() => tickets.value.filter(t => t.status === 'Open').length)
const resolvedCount = computed(() => tickets.value.filter(t => t.status === 'Resolved').length)
</script>
