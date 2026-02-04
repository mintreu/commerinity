<script setup lang="ts">
import { useDashboardAppointments } from '~/composables/useDashboardAppointments'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

import { useAppointmentUserSearch } from '~/composables/useAppointmentUserSearch'
import { useUserType } from '~/composables/useUserType'
const { create } = useDashboardAppointments()
const toast = useToast()
const router = useRouter()
const { search, fetchAttendeeTypes } = useAppointmentUserSearch()
const { isAdvisor, isMember, isPromoter } = useUserType()
const attendeeTypes = ref<Array<{ value: string; label: string }>>([])
const selectedAttendeeType = ref('user')
const attendees = ref([] as any[])
const attendeeQuery = ref('')
const selectedAttendee = ref<{ uuid: string; label: string } | null>(null)
const participants = ref<any[]>([])
const participantQuery = ref('')
const showParticipantModal = ref(false)
const participantSearchResults = ref<any[]>([])
const attendeeLoading = ref(false)
const participantLoading = ref(false)

const searchScope = computed(() => (isAdvisor.value || isMember.value || isPromoter.value ? 'team' : 'all'))

const form = reactive({
  title: '',
  agenda: '',
  meeting_mode: 'online' as 'online' | 'offline',
  meeting_link: '',
  start_at: '',
  end_at: '',
  advisor_uuid: '',
  mentor_uuid: ''
})

const submitting = ref(false)

const loadAttendees = async (query = '') => {
  try {
    attendeeLoading.value = true
    attendees.value = await search(query, selectedAttendeeType.value, searchScope.value)
  } catch (error) {
    console.error('Failed user search', error)
  } finally {
    attendeeLoading.value = false
  }
}

const selectAttendee = (option: any) => {
  selectedAttendee.value = option
  attendeeQuery.value = option.label
  attendees.value = []
}

const addParticipant = (option: any) => {
  if (!participants.value.some(p => p.uuid === option.uuid)) {
    participants.value = [...participants.value, option]
  }
  participantQuery.value = ''
  participantSearchResults.value = []
}

const handleAttendeeSearch = () => {
  loadAttendees(attendeeQuery.value)
}

watch(attendeeQuery, (value) => {
  if (!value) {
    attendees.value = []
    return
  }
})

watch(selectedAttendeeType, () => {
  attendeeQuery.value = ''
  selectedAttendee.value = null
})

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

const removeParticipant = (uuid: string) => {
  participants.value = participants.value.filter(p => p.uuid !== uuid)
}

const participantColumns = [
  { key: 'label', label: 'Participant' },
  { key: 'details', label: 'Details' },
  { key: 'actions', label: '' }
]

const submit = async () => {
  if (submitting.value || !selectedAttendee.value || !form.title || !form.start_at) {
    toast.warning('Please fill the required fields.')
    return
  }

  submitting.value = true
  try {
    const payload: Record<string, unknown> = {
      title: form.title,
      agenda: form.agenda || undefined,
      meeting_mode: form.meeting_mode,
      meeting_link: form.meeting_mode === 'online' ? form.meeting_link || undefined : undefined,
      start_at: form.start_at,
      end_at: form.end_at || undefined,
      attendee_type: selectedAttendeeType.value,
      attendee_uuid: selectedAttendee.value?.uuid || undefined,
      attendee_contact: attendeeQuery.value,
      advisor_uuid: form.advisor_uuid || undefined,
      mentor_uuid: form.mentor_uuid || undefined,
      participants: participants.value.map(p => p.uuid)
    }

    await create(payload)
    toast.success('Appointment scheduled successfully.')
    await router.push('/appointments')
  } catch (error) {
    console.error('Failed to create appointment', error)
    toast.error('Unable to schedule appointment.')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchAttendeeTypes().then((data) => {
    attendeeTypes.value = data?.length ? data : [
      { value: 'admin', label: 'Company' },
      { value: 'user', label: 'Users' }
    ]
    if (!selectedAttendeeType.value) {
      selectedAttendeeType.value = attendeeTypes.value[0]?.value || 'user'
    }
  })
})
</script>

<template>
  <div class="space-y-6 sm:space-y-8">
    <!-- Header Section with Gradient -->
    <div class="relative">
      <div class="absolute inset-0 bg-gradient-to-r from-primary-500/20 via-purple-500/20 to-pink-500/20 rounded-2xl sm:rounded-3xl blur-3xl -z-10" />
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center shadow-lg shadow-primary-500/30 flex-shrink-0">
                <UIcon name="i-lucide-calendar-plus" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-slate-900 via-slate-800 to-slate-700 dark:from-white dark:via-slate-100 dark:to-slate-300 bg-clip-text text-transparent">
                  Schedule Appointment
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                  Create a new advisor/mentor meeting with your clients
                </p>
              </div>
            </div>
          </div>
          <UButton
            variant="soft"
            color="neutral"
            icon="i-lucide-arrow-left"
            to="/appointments"
            size="lg"
            class="w-full lg:w-auto"
          >
            Back to appointments
          </UButton>
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
      <!-- Attendee Selection Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-user-search" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">Attendee Details</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Select who will attend this meeting</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Attendee Type *
            </label>
            <USelectMenu
              v-model="selectedAttendeeType"
              :options="attendeeTypes"
              value-attribute="value"
              option-attribute="label"
              size="lg"
              class="w-full"
            />
          </div>

          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Search Attendee *
            </label>
            <div class="space-y-3">
              <div class="flex flex-col sm:flex-row gap-2">
                <UInput
                  v-model="attendeeQuery"
                  placeholder="Search by name, mobile, referral code"
                  icon="i-lucide-search"
                  size="lg"
                  class="flex-1 w-full"
                  required
                  @blur="handleAttendeeSearch"
                />
                <UButton
                  size="lg"
                  variant="soft"
                  color="primary"
                  :loading="attendeeLoading"
                  icon="i-lucide-search"
                  @click="handleAttendeeSearch"
                  class="w-full sm:w-auto"
                >
                  Search
                </UButton>
              </div>

              <div v-if="selectedAttendee" class="rounded-2xl border-2 border-primary-500/30 bg-gradient-to-br from-primary-500/10 to-purple-500/10 p-4 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <UAvatar size="md" :alt="selectedAttendee.label" />
                    <div>
                      <div class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ selectedAttendee.label }}
                      </div>
                      <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                        <UIcon name="i-lucide-check-circle" class="w-3 h-3 text-primary-500" />
                        Selected attendee
                      </div>
                    </div>
                  </div>
                  <UButton
                    size="sm"
                    variant="ghost"
                    color="neutral"
                    icon="i-lucide-x"
                    @click="selectedAttendee = null; attendeeQuery = ''"
                  >
                    Clear
                  </UButton>
                </div>
              </div>

              <div v-if="attendees.length && attendeeQuery" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md shadow-xl overflow-hidden">
                <button
                  v-for="option in attendees"
                  :key="option.uuid"
                  type="button"
                  class="w-full text-left px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 border-b border-slate-100 dark:border-slate-700 last:border-0"
                  @click="() => selectAttendee(option)"
                >
                  <div class="flex items-center gap-3">
                    <UAvatar size="sm" :alt="option.label" />
                    <div class="flex-1">
                      <div class="text-sm font-medium text-slate-900 dark:text-white">{{ option.label }}</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">{{ option.details }}</div>
                    </div>
                    <UIcon name="i-lucide-chevron-right" class="w-4 h-4 text-slate-400" />
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Meeting Details Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex items-center gap-3 mb-4 sm:mb-6">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-clipboard-list" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">Meeting Information</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Configure meeting details and schedule</p>
          </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Meeting Title *
            </label>
            <UInput
              v-model="form.title"
              size="lg"
              placeholder="e.g., Client Consultation, Financial Planning Session"
              icon="i-lucide-text"
              required
              class="w-full"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Meeting Mode
              </label>
              <USelectMenu
                v-model="form.meeting_mode"
                :options="[
                  { value: 'online', label: 'Online Meeting' },
                  { value: 'offline', label: 'In-Person Meeting' }
                ]"
                value-attribute="value"
                option-attribute="label"
                size="lg"
                class="w-full"
              />
            </div>
            <div v-if="form.meeting_mode === 'online'" class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Meeting Link
              </label>
              <UInput
                v-model="form.meeting_link"
                type="url"
                size="lg"
                placeholder="https://meet.google.com/xxx-xxxx-xxx"
                icon="i-lucide-video"
                class="w-full"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Start Date & Time *
              </label>
              <UInput
                v-model="form.start_at"
                type="datetime-local"
                size="lg"
                icon="i-lucide-calendar-clock"
                required
                class="w-full"
              />
            </div>
            <div class="space-y-2 w-full">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                End Date & Time
              </label>
              <UInput
                v-model="form.end_at"
                type="datetime-local"
                size="lg"
                icon="i-lucide-calendar-clock"
                class="w-full"
              />
            </div>
          </div>

          <div class="space-y-2 w-full">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
              Meeting Agenda
            </label>
            <textarea
              v-model="form.agenda"
              rows="4"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200"
              placeholder="Describe the meeting agenda and topics to be discussed..."
            />
          </div>
        </div>
      </div>

      <!-- Participants Card -->
      <div class="glass-card p-4 sm:p-6 lg:p-8 rounded-2xl sm:rounded-3xl border border-white/20 dark:border-white/10 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-6">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center flex-shrink-0">
              <UIcon name="i-lucide-users" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white">Participants</h2>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Add team members to this appointment</p>
            </div>
          </div>
          <UButton
            size="lg"
            variant="soft"
            color="primary"
            icon="i-lucide-user-plus"
            @click="showParticipantModal = true"
            class="w-full sm:w-auto"
          >
            Add Participant
          </UButton>
        </div>

        <div v-if="participants.length === 0" class="rounded-2xl border-2 border-dashed border-slate-300/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 p-8 text-center">
          <div class="flex flex-col items-center gap-3">
            <div class="w-16 h-16 rounded-2xl bg-slate-200/50 dark:bg-slate-700/50 flex items-center justify-center">
              <UIcon name="i-lucide-users" class="w-8 h-8 text-slate-400" />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-400">No participants added yet</p>
              <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Click "Add Participant" to invite team members</p>
            </div>
          </div>
        </div>

        <div v-else class="rounded-2xl border border-slate-200/60 dark:border-slate-700/60 overflow-hidden backdrop-blur-sm">
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
                <UAvatar size="sm" :alt="row.label" />
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
            variant="soft"
            color="neutral"
            size="lg"
            icon="i-lucide-x"
            to="/appointments"
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
            Schedule Appointment
          </UButton>
        </div>
      </div>
    </form>

    <!-- Participant Modal -->
    <UModal v-model:open="showParticipantModal" :ui="{ width: 'sm:max-w-xl' }">
      <template #content>
        <div class="glass-card rounded-3xl border border-white/20 dark:border-white/10 overflow-hidden">
          <div class="bg-gradient-to-r from-primary-500/10 via-purple-500/10 to-pink-500/10 p-6 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center shadow-lg">
                <UIcon name="i-lucide-users" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Add Participants</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Search by mobile, email, or referral code</p>
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

            <div v-if="participantQuery && participantSearchResults.length" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm shadow-lg overflow-hidden max-h-[300px] overflow-y-auto">
              <button
                v-for="option in participantSearchResults"
                :key="`${option.uuid}-modal`"
                type="button"
                class="w-full text-left px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200 border-b border-slate-100 dark:border-slate-700 last:border-0"
                @click="() => addParticipant(option)"
              >
                <div class="flex items-center gap-3">
                  <UAvatar size="sm" :alt="option.label" />
                  <div class="flex-1">
                    <div class="text-sm font-medium text-slate-900 dark:text-white">{{ option.label }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ option.details }}</div>
                  </div>
                  <UIcon name="i-lucide-plus-circle" class="w-5 h-5 text-primary-500" />
                </div>
              </button>
            </div>

            <div v-else class="rounded-2xl border-2 border-dashed border-slate-300/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/50 p-8 text-center">
              <UIcon name="i-lucide-search" class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
              <p class="text-sm text-slate-500 dark:text-slate-400">Search results will appear here</p>
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
