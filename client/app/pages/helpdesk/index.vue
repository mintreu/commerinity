<template>
  <UContainer class="py-8">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold">Support Center</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your support tickets</p>
          </div>
          <UButton to="/helpdesk/create" color="primary" icon="i-heroicons-plus">Create Ticket</UButton>
        </div>
        <div class="flex gap-4 mt-6">
          <UBadge color="yellow" size="lg">{{ openCount }} Open</UBadge>
          <UBadge color="green" size="lg">{{ resolvedCount }} Resolved</UBadge>
          <UBadge color="blue" size="lg">{{ tickets.length }} Total</UBadge>
        </div>
      </template>

      <div class="space-y-4">
        <div class="flex gap-4">
          <USelect v-model="statusFilter" :options="[{label:'All',value:'all'},{label:'Open',value:'open'},{label:'Resolved',value:'resolved'},{label:'Closed',value:'closed'}]" />
          <USelect v-model="priorityFilter" :options="[{label:'All',value:'all'},{label:'Low',value:'low'},{label:'Medium',value:'medium'},{label:'High',value:'high'},{label:'Urgent',value:'urgent'}]" />
          <UInput v-model="searchQuery" placeholder="Search tickets..." icon="i-heroicons-magnifying-glass" class="flex-1" />
        </div>

        <div v-if="loading" class="text-center py-12">
          <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" />
        </div>

        <div v-else-if="filteredTickets.length === 0" class="text-center py-12">
          <UIcon name="i-heroicons-ticket" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
          <h3 class="text-xl font-semibold mb-2">No tickets found</h3>
          <UButton to="/helpdesk/create" color="primary" class="mt-4">Create Your First Ticket</UButton>
        </div>

        <UTable v-else :rows="paginatedTickets" :columns="[
          {key:'uuid',label:'ID'},{key:'title',label:'Title'},{key:'priority',label:'Priority'},{key:'status',label:'Status'},{key:'created_at',label:'Created'},{key:'actions',label:''}
        ]">
          <template #uuid-data="{ row }">
            <span class="font-mono text-xs">{{ row.uuid.slice(0,8) }}</span>
          </template>
          <template #priority-data="{ row }">
            <UBadge :color="row.priority === 'urgent' || row.priority === 'high' ? 'red' : row.priority === 'medium' ? 'yellow' : 'green'">{{ row.priority }}</UBadge>
          </template>
          <template #status-data="{ row }">
            <UBadge :color="row.status === 'open' ? 'yellow' : row.status === 'resolved' ? 'green' : 'gray'">{{ row.status }}</UBadge>
          </template>
          <template #created_at-data="{ row }">
            {{ new Date(row.created_at).toLocaleDateString() }}
          </template>
          <template #actions-data="{ row }">
            <UButton :to="`/helpdesk/${row.uuid}`" size="xs" variant="ghost">View</UButton>
          </template>
        </UTable>

        <div v-if="pageCount > 1" class="flex justify-center gap-2 mt-4">
          <UButton @click="page--" :disabled="page===1" size="xs">Prev</UButton>
          <span class="px-4 py-2">Page {{ page }} of {{ pageCount }}</span>
          <UButton @click="page++" :disabled="page>=pageCount" size="xs">Next</UButton>
        </div>
      </div>
    </UCard>
  </UContainer>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const { loading, fetchTickets } = useHelpdesk()
const tickets = ref([])
const page = ref(1)
const pageSize = 10
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
const paginatedTickets = computed(() => filteredTickets.value.slice((page.value-1)*pageSize, page.value*pageSize))
const openCount = computed(() => tickets.value.filter(t => t.status === 'open').length)
const resolvedCount = computed(() => tickets.value.filter(t => t.status === 'resolved').length)

onMounted(async () => {
  tickets.value = await fetchTickets()
})
</script>
