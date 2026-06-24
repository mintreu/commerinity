<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const { isMember, isPromoter } = useUserType()
const toast = useToast()
const { formatDate } = useBranding()
const { entries, meta, loading, fetchLedger } = useAffiliateLedger()

const statusFilter = ref('')

onMounted(async () => {
  if (!isMember.value && !isPromoter.value) {
    await navigateTo('/dashboard')
    return
  }
  await fetchLedger()
})

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Pending', value: 'pending' },
  { label: 'Confirmed', value: 'confirmed' },
  { label: 'Reversed', value: 'reversed' },
  { label: 'Expired', value: 'expired' }
]

const applyFilter = async () => {
  await fetchLedger({ status: statusFilter.value, page: 1 })
}

const loadPage = async (page: number) => {
  await fetchLedger({ status: statusFilter.value, page })
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
    <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
          <UIcon
            name="i-lucide-network"
            class="w-6 h-6 sm:w-8 sm:h-8 text-white"
          />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
            Volume Ledger
          </h1>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
            Track pending and confirmed community volumes
          </p>
        </div>
      </div>
    </div>

    <div class="glass-card p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
      <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
        <USelect
          v-model="statusFilter"
          :options="statusOptions"
          placeholder="All Status"
          size="lg"
          class="w-full sm:w-48"
          @update:model-value="applyFilter"
        />
      </div>
    </div>

    <div
      v-if="loading"
      class="flex justify-center py-10"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="w-7 h-7 animate-spin text-primary-500"
      />
    </div>

    <div
      v-else-if="entries.length === 0"
      class="glass-card p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 text-center"
    >
      <p class="text-sm text-slate-500">
        No ledger entries yet.
      </p>
    </div>

    <div
      v-else
      class="space-y-4"
    >
      <div
        v-for="entry in entries"
        :key="entry.uuid"
        class="glass-card p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <UBadge
                color="primary"
                size="xs"
              >
                {{ entry.status_label || entry.status }}
              </UBadge>
              <span class="text-xs text-slate-500">{{ formatDate(entry.created_at, 'short') }}</span>
            </div>
            <div class="mt-2 text-sm text-slate-700 dark:text-slate-200">
              Volume <strong>{{ entry.bv }}</strong> • Points <strong>{{ entry.pv }}</strong>
            </div>
          </div>
          <div class="text-xs text-slate-500">
            Tier {{ entry.depth }}
          </div>
        </div>
      </div>

      <div
        v-if="meta && meta.last_page > 1"
        class="flex justify-center pt-2"
      >
        <UPagination
          :model-value="meta.current_page"
          :total="meta.total"
          :page-count="meta.per_page"
          @update:model-value="loadPage"
        />
      </div>
    </div>
  </div>
</template>
