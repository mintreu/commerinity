<script setup lang="ts">
import { useDashboardPrograms } from '~/composables/useDashboardPrograms'
import type { DashboardProgram } from '~/types/dashboard'
import { useRoute } from '#app'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { show } = useDashboardPrograms()
const route = useRoute()
const toast = useToast()

const program = ref<DashboardProgram | null>(null)
const loading = ref(true)

const loadProgram = async () => {
  loading.value = true
  try {
    const response = await show(route.params.uuid as string)
    program.value = response.data
  } catch (error) {
    toast.error('Unable to load program.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(loadProgram)
</script>

<template>
  <div class="space-y-6">
    <div
      v-if="loading"
      class="glass-card p-6"
    >
      <UIcon
        name="i-lucide-loader-circle"
        class="animate-spin w-6 h-6 text-primary"
      />
    </div>

    <div
      v-else-if="program"
      class="space-y-4"
    >
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">
            {{ program.title }}
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ program.start_date || 'TBD' }} - {{ program.end_date || 'TBD' }}
          </p>
        </div>
        <UBadge
          color="primary"
          variant="soft"
        >
          {{ program.status }}
        </UBadge>
      </div>

      <div class="glass-card p-6 space-y-4">
        <p class="text-slate-600 dark:text-slate-400">
          {{ program.description || 'No description yet.' }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <h5 class="text-xs uppercase text-slate-500">
              Participants
            </h5>
            <p class="font-semibold">
              {{ program.participants.length }}
            </p>
          </div>
          <div>
            <h5 class="text-xs uppercase text-slate-500">
              Location
            </h5>
            <p class="font-semibold">
              {{ program.location?.full_address || 'Virtual' }}
            </p>
          </div>
          <div>
            <h5 class="text-xs uppercase text-slate-500">
              Creator
            </h5>
            <p class="font-semibold">
              {{ program.creator?.name || 'You' }}
            </p>
          </div>
        </div>
      </div>

      <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
          Participants
        </h2>
        <ul class="space-y-3 mt-4">
          <li
            v-for="participant in program.participants"
            :key="participant.uuid"
            class="border border-slate-200 dark:border-slate-700 rounded p-3"
          >
            <div class="flex items-center justify-between">
              <p class="font-medium text-slate-900 dark:text-white">
                {{ participant.user?.name || 'Participant' }}
              </p>
              <span class="text-xs text-slate-500 dark:text-slate-400">{{ participant.role }}</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Status: {{ participant.status }} · Invited by {{ participant.inviter?.name || '---' }}
            </p>
          </li>
        </ul>
      </div>
    </div>

    <div
      v-else
      class="glass-card p-6"
    >
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Program not found.
      </p>
    </div>
  </div>
</template>
