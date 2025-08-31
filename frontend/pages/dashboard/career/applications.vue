<template>
  <div class="w-full h-full flex flex-col ">
    <GlobalLoader v-if="isLoading" />

    <!-- content -->
    <div v-else class="flex-1 flex flex-col p-4 gap-4 ">

      <!-- header + controls -->
      <div class="shrink-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full overflow-hidden">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
          My Job Applications
        </h1>

        <div class="w-full sm:w-auto grid grid-cols-1 sm:grid-cols-[1fr_auto_auto_auto] gap-2">
          <!-- search -->
          <input
              v-model="search"
              type="text"
              placeholder="Search job title…"
              class="px-3 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 w-full"
          />

          <!-- status filter -->
          <select
              v-model="statusFilter"
              class="px-3 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 w-full"
          >
            <option value="">All Status</option>
            <option value="Awaiting Payment">Awaiting Payment</option>
            <option value="Paid">Paid</option>
          </select>

          <!-- sort -->
          <select
              v-model="sortBy"
              class="px-3 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 w-full"
          >
            <option value="submit_on">Submit Date</option>
            <option value="recruitment.name">Job Title</option>
            <option value="status">Status</option>
          </select>

          <!-- toggle sort -->
          <button
              @click="toggleSortOrder"
              class="px-3 py-2 rounded-lg border dark:border-gray-700 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center"
              :aria-label="`Toggle sort (${sortOrder})`"
              type="button"
          >
            <Icon :name="sortOrder === 'asc' ? 'heroicons:arrow-up' : 'heroicons:arrow-down'" class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Table scroller -->
      <div
          class="flex-1 rounded-lg shadow bg-white dark:bg-gray-900 overflow-hidden"
          role="region"
          aria-label="Applications table"
      >
        <div class="h-full w-full overflow-auto">
          <table class="min-w-max w-full border-collapse divide-y divide-gray-200 dark:divide-gray-700">

            <!-- Desktop header -->
            <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0 z-10 hidden md:table-header-group">
            <tr>
              <th class="p-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Job</th>
              <th class="p-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Type</th>
              <th class="p-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Location</th>
              <th class="p-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Submitted</th>
              <th class="p-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Status</th>
              <th class="p-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
                v-for="app in paginatedData"
                :key="app.uuid"
                class="hover:bg-gray-50 dark:hover:bg-gray-800 transition"
            >
              <!-- Desktop cells -->
              <td class="p-3 text-sm font-medium text-gray-900 dark:text-gray-100 hidden md:table-cell">
                {{ app.recruitment?.name }}
              </td>
              <td class="p-3 text-sm text-gray-600 dark:text-gray-300 hidden md:table-cell">
                {{ app.recruitment?.type }}
              </td>
              <td class="p-3 text-sm text-gray-600 dark:text-gray-300 hidden md:table-cell">
                {{ app.recruitment?.location }}
              </td>
              <td class="p-3 text-sm text-gray-600 dark:text-gray-300 hidden md:table-cell">
                {{ formatDate(app.submit_on) }}
              </td>
              <td class="p-3 text-sm hidden md:table-cell">
            <span
                class="px-2 py-1 rounded-full text-xs font-semibold"
                :class="statusClass(app)"
            >
              {{ app.status }}
            </span>
              </td>
              <td class="p-3 text-sm hidden md:table-cell">
                <div class="flex justify-end gap-2">
                  <button
                      class="px-3 py-1.5 rounded-lg border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white text-sm flex items-center gap-1"
                      @click="onView(app)"
                      type="button"
                  >
                    <Icon name="heroicons:eye" class="w-4 h-4" />
                    View
                  </button>
                  <button
                      v-if="!app.isPaid"
                      class="px-3 py-1.5 rounded-lg border border-green-500 text-green-500 hover:bg-green-500 hover:text-white text-sm flex items-center gap-1"
                      @click="onPay(app)"
                      type="button"
                  >
                    <Icon name="heroicons:currency-dollar" class="w-4 h-4" />
                    Pay Fees
                  </button>
                </div>
              </td>

              <!-- Mobile card -->
              <td class="p-3 md:hidden" colspan="6">
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm bg-gray-50 dark:bg-gray-800 flex flex-col gap-3">

                  <!-- Job Title -->
                  <div class="flex items-center gap-2 text-gray-900 dark:text-gray-100 font-semibold">
                    <Icon name="heroicons:briefcase" class="w-5 h-5 text-blue-500" />
                    {{ app.recruitment?.name }}
                  </div>

                  <!-- Type -->
                  <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <Icon name="heroicons:document-text" class="w-4 h-4 text-indigo-500" />
                    {{ app.recruitment?.type }}
                  </div>

                  <!-- Location -->
                  <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <Icon name="heroicons:map-pin" class="w-4 h-4 text-red-500" />
                    {{ app.recruitment?.location }}
                  </div>

                  <!-- Submitted -->
                  <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <Icon name="heroicons:calendar" class="w-4 h-4 text-amber-500" />
                    {{ formatDate(app.submit_on) }}
                  </div>

                  <!-- Status -->
                  <div class="flex items-center gap-2 text-sm">
                    <Icon name="heroicons:check-circle" class="w-4 h-4 text-green-500" />
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-semibold"
                        :class="statusClass(app)"
                    >
                  {{ app.status }}
                </span>
                  </div>

                  <!-- Actions -->
                  <div class="flex gap-2 pt-2">
                    <button
                        class="flex-1 px-2 py-1 rounded-md border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white text-xs flex items-center justify-center gap-1"
                        @click="onView(app)"
                        type="button"
                    >
                      <Icon name="heroicons:eye" class="w-4 h-4" />
                      View
                    </button>
                    <button
                        v-if="!app.isPaid"
                        class="flex-1 px-2 py-1 rounded-md border border-green-500 text-green-500 hover:bg-green-500 hover:text-white text-xs flex items-center justify-center gap-1"
                        @click="onPay(app)"
                        type="button"
                    >
                      <Icon name="heroicons:currency-dollar" class="w-4 h-4" />
                      Pay
                    </button>
                  </div>
                </div>
              </td>
            </tr>

            <!-- Empty -->
            <tr v-if="!paginatedData.length">
              <td colspan="6" class="p-6 text-center text-sm text-gray-600 dark:text-gray-300">
                No applications found.
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>




      <!-- pagination -->
      <div class="shrink-0 flex flex-col sm:flex-row justify-between items-center gap-3 w-full">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Showing {{ startIndex + 1 }} - {{ endIndex }} of {{ filteredData.length }} results
        </p>
        <div class="flex items-center gap-2">
          <button
              @click="prevPage"
              :disabled="page === 1"
              class="px-3 py-1 rounded border dark:border-gray-700 dark:bg-gray-800 disabled:opacity-50"
              type="button"
          >
            Prev
          </button>
          <button
              @click="nextPage"
              :disabled="page === totalPages"
              class="px-3 py-1 rounded border dark:border-gray-700 dark:bg-gray-800 disabled:opacity-50"
              type="button"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>




<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useRouter } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const router = useRouter()
const isLoading = useState('pageLoading', () => false)

const applications = ref<any[]>([])

// filters & sorting
const search = ref('')
const statusFilter = ref('')
const sortBy = ref('submit_on')
const sortOrder = ref<'asc' | 'desc'>('desc')

// pagination
const page = ref(1)
const perPage = 5

onMounted(async () => {
  isLoading.value = true
  try {
    await fetchMyApplications()
  } finally {
    isLoading.value = false
  }
})

async function fetchMyApplications() {
  try {
    const url = `${config.public.apiBase}/recruitment/user-application/all`
    const res: any = await useSanctumFetch(url, { method: 'GET' })
    if (res?.data) applications.value = res.data
  } catch (e) {
    console.error('Failed to fetch applications:', e)
  }
}

// recompute page on filter/search change
watch([search, statusFilter], () => { page.value = 1 })

// filtered + searched + sorted
const filteredData = computed(() => {
  let data = applications.value

  if (search.value.trim()) {
    const s = search.value.toLowerCase()
    data = data.filter(app =>
        app.recruitment?.name?.toLowerCase().includes(s)
    )
  }

  if (statusFilter.value) {
    data = data.filter(app => (app.status || '') === statusFilter.value)
  }

  const getNestedValue = (obj: any, path: string) =>
      path.split('.').reduce((acc, part) => acc?.[part], obj)

  return [...data].sort((a, b) => {
    const aVal = getNestedValue(a, sortBy.value)
    const bVal = getNestedValue(b, sortBy.value)

    // normalize for date/string sorting
    const av = aVal ?? ''
    const bv = bVal ?? ''
    return av < bv ? (sortOrder.value === 'asc' ? -1 : 1)
        : av > bv ? (sortOrder.value === 'asc' ? 1 : -1)
            : 0
  })
})

// pagination slices
const totalPages = computed(() => Math.max(1, Math.ceil(filteredData.value.length / perPage)))
const paginatedData = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredData.value.slice(start, start + perPage)
})
const startIndex = computed(() => (page.value - 1) * perPage)
const endIndex = computed(() => Math.min(startIndex.value + perPage, filteredData.value.length))

function prevPage() { if (page.value > 1) page.value-- }
function nextPage() { if (page.value < totalPages.value) page.value++ }
function toggleSortOrder() { sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc' }

// actions
function onView(app: any) {
  const slug = app?.recruitment?.url || ''
  router.push(`/careers/${slug}`)
}
function onPay(app: any) {
  router.push({ path: '/checkout', query: { app: app?.uuid } })
}

// helpers
function formatDate(dateStr: string) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString()
}
function statusClass(app: any) {
  const s = (app?.status || '').toLowerCase()
  if (s.includes('await')) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
  if (s.includes('paid')) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
  return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
}
</script>



<style scoped>
.th {
  @apply p-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase truncate;
}
.td {
  @apply p-3 text-sm text-gray-700 dark:text-gray-200 align-top truncate;
}
.status {
  @apply px-2 py-1 rounded-full text-xs font-semibold;
}
.btn-primary {
  @apply px-3 py-1.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white flex items-center gap-1;
}
.btn-success {
  @apply px-3 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white flex items-center gap-1;
}
</style>
