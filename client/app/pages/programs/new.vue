<script setup lang="ts">
import { useDashboardPrograms } from '~/composables/useDashboardPrograms'
import { useAppointmentUserSearch } from '~/composables/useAppointmentUserSearch'
import { useUserType } from '~/composables/useUserType'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const toast = useToast()
const router = useRouter()
const { create } = useDashboardPrograms()
const { search } = useAppointmentUserSearch()
const { isAdvisor, isMember, isPromoter } = useUserType()

const statuses = ['draft', 'scheduled', 'ongoing', 'completed', 'cancelled']

const form = reactive({
  title: '',
  description: '',
  status: 'draft',
  start_date: '',
  end_date: '',
  location: {
    person_name: '',
    person_mobile: '',
    address_1: '',
    city: '',
    postal_code: '',
    country_code: 'IN'
  }
})

const participants = ref<any[]>([])
const participantQuery = ref('')
const showParticipantModal = ref(false)
const participantSearchResults = ref<any[]>([])
const participantLoading = ref(false)
const submitting = ref(false)

const searchScope = computed(() => (isAdvisor.value || isMember.value || isPromoter.value ? 'team' : 'all'))

const participantColumns = [
  { key: 'label', label: 'Participant' },
  { key: 'details', label: 'Details' },
  { key: 'actions', label: '' }
]

const handleParticipantSearch = async () => {
  if (!participantQuery.value) return
  try {
    participantLoading.value = true
    const results = await search(participantQuery.value, 'user', searchScope.value)
    participantSearchResults.value = results || []
  } finally {
    participantLoading.value = false
  }
}

const addParticipant = (option: any) => {
  if (!participants.value.some(p => p.uuid === option.uuid)) {
    participants.value = [...participants.value, option]
  }
  participantQuery.value = ''
  participantSearchResults.value = []
}

const removeParticipant = (uuid: string) => {
  participants.value = participants.value.filter(p => p.uuid !== uuid)
}

const submit = async () => {
  if (submitting.value || !form.title) {
    toast.warning('Give your program a title.')
    return
  }

  submitting.value = true
  try {
    const payload: Record<string, unknown> = {
      title: form.title,
      description: form.description || undefined,
      status: form.status,
      start_date: form.start_date || undefined,
      end_date: form.end_date || undefined
    }

    if (participants.value.length) {
      payload.participants = participants.value.map(p => ({ uuid: p.uuid, role: 'participant' }))
    }

    if (form.location.person_name && form.location.address_1 && form.location.city && form.location.postal_code) {
      payload.address = {
        ...form.location,
        type: 'service_point'
      }
    }

    await create(payload)
    toast.success('Program created. Wait for admin approval if drafted.')
    await router.push('/programs')
  } catch (error) {
    console.error('Failed to create program', error)
    toast.error('Unable to create program.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6 sm:space-y-8">
    <!-- Header Section with Gradient -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 via-teal-500/20 to-cyan-500/20 rounded-2xl sm:rounded-3xl blur-3xl -z-10" />
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 flex-shrink-0">
                <UIcon
                  name="i-lucide-graduation-cap"
                  class="w-5 h-5 sm:w-6 sm:h-6 text-white"
                />
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 dark:from-white dark:via-slate-100 dark:to-slate-300 bg-clip-text text-transparent">
                  Create New Program
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                  Draft a mentorship program and invite your mentees & advisors
                </p>
              </div>
            </div>
          </div>
          <UButton
            to="/programs"
            variant="soft"
            color="neutral"
            icon="i-lucide-arrow-left"
            size="lg"
            class="w-full lg:w-auto"
          >
            Back to programs
          </UButton>
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <form
      class="space-y-4 sm:space-y-6"
      @submit.prevent="submit"
    >
      <!-- Basic Information Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center flex-shrink-0">
            <UIcon
              name="i-lucide-file-text"
              class="w-5 h-5 sm:w-6 sm:h-6 text-white"
            />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">
              Basic Information
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
              Program title, status and description
            </p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Program Title *
              </label>
              <UInput
                v-model="form.title"
                size="lg"
                placeholder="e.g., Financial Planning Bootcamp, Leadership Training"
                icon="i-lucide-text"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Status
              </label>
              <USelectMenu
                v-model="form.status"
                :options="statuses.map(s => ({ value: s, label: s.charAt(0).toUpperCase() + s.slice(1) }))"
                value-attribute="value"
                option-attribute="label"
                size="lg"
                class="w-full"
              />
            </div>
          </div>

          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Program Description
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
              placeholder="Describe what mentees will learn, program objectives, and key outcomes..."
            />
          </div>
        </div>
      </div>

      <!-- Schedule Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
            <UIcon
              name="i-lucide-calendar-range"
              class="w-5 h-5 sm:w-6 sm:h-6 text-white"
            />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">
              Program Schedule
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
              Define program duration and timeline
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Start Date
            </label>
            <UInput
              v-model="form.start_date"
              type="date"
              size="lg"
              icon="i-lucide-calendar-check"
              class="w-full"
            />
          </div>
          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              End Date
            </label>
            <UInput
              v-model="form.end_date"
              type="date"
              size="lg"
              icon="i-lucide-calendar-x"
              class="w-full"
            />
          </div>
        </div>
      </div>

      <!-- Location Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center flex-shrink-0">
            <UIcon
              name="i-lucide-map-pin"
              class="w-5 h-5 sm:w-6 sm:h-6 text-white"
            />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">
              Location Details
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
              Venue and contact information
            </p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Contact Person
              </label>
              <UInput
                v-model="form.location.person_name"
                size="lg"
                placeholder="Contact name"
                icon="i-lucide-user"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Mobile Number
              </label>
              <UInput
                v-model="form.location.person_mobile"
                size="lg"
                placeholder="+91 98765 43210"
                icon="i-lucide-phone"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Address
              </label>
              <UInput
                v-model="form.location.address_1"
                size="lg"
                placeholder="Street address"
                icon="i-lucide-map"
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                City
              </label>
              <UInput
                v-model="form.location.city"
                size="lg"
                placeholder="City name"
                icon="i-lucide-building"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Postal Code
              </label>
              <UInput
                v-model="form.location.postal_code"
                size="lg"
                placeholder="Postal/ZIP code"
                icon="i-lucide-hash"
                class="w-full"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Participants Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center flex-shrink-0">
              <UIcon
                name="i-lucide-users"
                class="w-5 h-5 sm:w-6 sm:h-6 text-white"
              />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">
                Program Participants
              </h2>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Add mentees and advisors to this program
              </p>
            </div>
          </div>
          <UButton
            size="lg"
            variant="soft"
            color="primary"
            icon="i-lucide-user-plus"
            class="w-full sm:w-auto"
            @click="showParticipantModal = true"
          >
            Add Participant
          </UButton>
        </div>

        <div
          v-if="participants.length === 0"
          class="rounded-2xl border-2 border-dashed border-slate-300/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 p-8 text-center"
        >
          <div class="flex flex-col items-center gap-3">
            <div class="w-16 h-16 rounded-2xl bg-slate-200/50 dark:bg-slate-700/50 flex items-center justify-center">
              <UIcon
                name="i-lucide-users"
                class="w-8 h-8 text-slate-400"
              />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-400">
                No participants added yet
              </p>
              <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                Click "Add Participant" to invite mentees and advisors
              </p>
            </div>
          </div>
        </div>

        <div
          v-else
          class="rounded-2xl border border-slate-200/60 dark:border-slate-700/60 overflow-hidden backdrop-blur-sm"
        >
          <UTable
            :rows="participants"
            :columns="participantColumns"
            class="w-full"
            :ui="{
              thead: 'bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800',
              th: { base: 'text-[10px] font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400' },
              td: { base: 'text-sm font-medium text-slate-700 dark:text-slate-300' }
            }"
          >
            <template #label-data="{ row }">
              <div class="flex items-center gap-3 py-1">
                <UAvatar
                  size="sm"
                  :alt="row.label"
                />
                <span class="font-semibold text-slate-900 dark:text-white">{{ row.label }}</span>
              </div>
            </template>
            <template #details-data="{ row }">
              <span class="text-xs text-slate-500 dark:text-slate-400">{{ row.details }}</span>
            </template>
            <template #actions-data="{ row }">
              <UButton
                size="sm"
                variant="ghost"
                color="error"
                icon="i-lucide-trash-2"
                @click="removeParticipant(row.uuid)"
              >
                Remove
              </UButton>
            </template>
          </UTable>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="glass-card p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
          <UButton
            type="button"
            to="/programs"
            variant="soft"
            color="neutral"
            size="lg"
            icon="i-lucide-x"
            class="w-full sm:w-auto"
          >
            Cancel
          </UButton>
          <UButton
            type="submit"
            color="primary"
            size="lg"
            icon="i-lucide-check"
            :loading="submitting"
            class="w-full sm:w-auto sm:min-w-[200px]"
          >
            Create Program
          </UButton>
        </div>
      </div>
    </form>

    <!-- Participant Modal -->
    <UModal
      v-model:open="showParticipantModal"
      :ui="{ width: 'sm:max-w-xl' }"
    >
      <template #content>
        <div class="glass-card rounded-3xl border border-white/20 dark:border-white/10 overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-green-500/10 p-6 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                <UIcon
                  name="i-lucide-users"
                  class="w-6 h-6 text-white"
                />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                  Add Participants
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  Search by mobile, email, or referral code
                </p>
              </div>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <div class="flex gap-2">
              <UInput
                v-model="participantQuery"
                placeholder="Type mobile, email, or referral id"
                icon="i-lucide-search"
                size="lg"
                class="flex-1"
              />
              <UButton
                size="lg"
                variant="soft"
                color="primary"
                :loading="participantLoading"
                icon="i-lucide-search"
                @click="handleParticipantSearch"
              >
                Search
              </UButton>
            </div>

            <div
              v-if="participantQuery && participantSearchResults.length"
              class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-lg overflow-hidden max-h-[300px] overflow-y-auto"
            >
              <button
                v-for="option in participantSearchResults"
                :key="`${option.uuid}-modal`"
                type="button"
                class="w-full text-left px-4 py-3 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-200 border-b border-slate-100 dark:border-slate-700 last:border-0"
                @click="() => addParticipant(option)"
              >
                <div class="flex items-center gap-3">
                  <UAvatar
                    size="sm"
                    :alt="option.label"
                  />
                  <div class="flex-1">
                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                      {{ option.label }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                      {{ option.details }}
                    </div>
                  </div>
                  <UIcon
                    name="i-lucide-plus-circle"
                    class="w-5 h-5 text-emerald-500"
                  />
                </div>
              </button>
            </div>

            <div
              v-else
              class="rounded-2xl border-2 border-dashed border-slate-300/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 p-8 text-center"
            >
              <UIcon
                name="i-lucide-search"
                class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3"
              />
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Search results will appear here
              </p>
            </div>
          </div>

          <div class="bg-slate-50/50 dark:bg-slate-900/50 p-6 border-t border-slate-200/50 dark:border-slate-700/50 flex justify-end">
            <UButton
              size="lg"
              variant="soft"
              color="primary"
              icon="i-lucide-check"
              @click="showParticipantModal = false"
            >
              Done
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>
