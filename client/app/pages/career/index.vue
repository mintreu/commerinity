<script setup lang="ts">
const config = useRuntimeConfig()

interface Recruitment {
  id: number
  uuid: string
  slug: string
  title: string
  description: string
  role: string
  role_label: string
  location: string
  employment_type: string
  employment_type_label: string
  vacancy: number
  open_date_formatted: string
  close_date_formatted: string
  is_payable: boolean
  fees_formatted: string
  is_open: boolean
}

interface FilterOption {
  value: string
  label: string
}

interface FiltersData {
  roles: FilterOption[]
  types: FilterOption[]
  counts_by_role: Record<string, number>
}

const selectedRole = ref<string | undefined>(undefined)
const selectedType = ref<string | undefined>(undefined)

const queryParams = computed(() => {
  const params = new URLSearchParams()
  if (selectedRole.value) params.append('role', selectedRole.value)
  if (selectedType.value) params.append('type', selectedType.value)
  return params.toString()
})

const apiUrl = computed(() => {
  const base = `${config.public.apiBase}/api/careers`
  return queryParams.value ? `${base}?${queryParams.value}` : base
})

const { data: recruitments, status, refresh } = await useAsyncData<{ data: Recruitment[] }>(
  'careers',
  () => useSanctumFetch(apiUrl.value),
  { watch: [apiUrl] }
)

const { data: filtersData } = await useAsyncData<{ data: FiltersData }>(
  'career-filters',
  () => useSanctumFetch(`${config.public.apiBase}/api/careers/filters`)
)

const roleOptions = computed(() => {
  const data = filtersData.value?.data
  if (!data?.roles) return []
  // Filter out empty values to prevent SelectItem error
  return data.roles
    .filter((r: FilterOption) => r.value && r.value.trim() !== '')
    .map((r: FilterOption) => ({ label: r.label, value: r.value }))
})

const typeOptions = computed(() => {
  const data = filtersData.value?.data
  if (!data?.types) return []
  // Filter out empty values to prevent SelectItem error
  return data.types
    .filter((t: FilterOption) => t.value && t.value.trim() !== '')
    .map((t: FilterOption) => ({ label: t.label, value: t.value }))
})

function clearFilters() {
  selectedRole.value = undefined
  selectedType.value = undefined
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 py-16">
      <UContainer>
        <h1 class="text-4xl font-bold text-white text-center">
          Career Opportunities
        </h1>
        <p class="mt-4 text-xl text-white/80 text-center max-w-2xl mx-auto">
          Join our team and build your career with us
        </p>
      </UContainer>
    </div>

    <UContainer class="py-8">
      <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <USelect
          v-model="selectedRole"
          :items="roleOptions"
          placeholder="Filter by Role"
          class="w-full sm:w-48"
        />
        <USelect
          v-model="selectedType"
          :items="typeOptions"
          placeholder="Filter by Type"
          class="w-full sm:w-48"
        />
        <UButton
          v-if="selectedRole || selectedType"
          variant="ghost"
          icon="i-lucide-x"
          @click="clearFilters"
        >
          Clear
        </UButton>
      </div>

      <div v-if="status === 'pending'" class="flex justify-center py-12">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-violet-600" />
      </div>

      <div v-else-if="!recruitments?.data?.length" class="text-center py-12">
        <UIcon name="i-lucide-briefcase" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          No open positions
        </h3>
        <p class="text-gray-600 dark:text-gray-400">
          Check back later for new opportunities.
        </p>
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <UCard
          v-for="job in recruitments.data"
          :key="job.uuid"
          class="hover:shadow-lg transition-shadow"
        >
          <template #header>
            <div class="flex items-start justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ job.title }}
              </h3>
              <UBadge v-if="job.is_payable" color="warning" size="sm">
                Paid
              </UBadge>
            </div>
          </template>

          <div class="space-y-3">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <UIcon name="i-lucide-briefcase" class="w-4 h-4" />
              <span>{{ job.role_label }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <UIcon name="i-lucide-clock" class="w-4 h-4" />
              <span>{{ job.employment_type_label }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <UIcon name="i-lucide-map-pin" class="w-4 h-4" />
              <span>{{ job.location }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <UIcon name="i-lucide-users" class="w-4 h-4" />
              <span>{{ job.vacancy }} {{ job.vacancy === 1 ? 'vacancy' : 'vacancies' }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <UIcon name="i-lucide-calendar" class="w-4 h-4" />
              <span>Closes: {{ job.close_date_formatted }}</span>
            </div>
            <div v-if="job.is_payable" class="flex items-center gap-2 text-sm font-medium text-amber-600 dark:text-amber-400">
              <UIcon name="i-lucide-indian-rupee" class="w-4 h-4" />
              <span>Application Fee: {{ job.fees_formatted }}</span>
            </div>
          </div>

          <template #footer>
            <UButton
              :to="`/career/${job.slug}`"
              color="primary"
              block
            >
              View Details
            </UButton>
          </template>
        </UCard>
      </div>
    </UContainer>
  </div>
</template>
