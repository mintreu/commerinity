<script setup lang="ts">
import type { DashboardAppointment } from '~/types/dashboard'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

import { useDashboardAppointments } from '~/composables/useDashboardAppointments'

const { formatDate } = useBranding()
const { fetchList } = useDashboardAppointments()
const toast = useToast()

const appointments = ref<DashboardAppointment[]>([])
const loading = ref(false)
const page = ref(1)
const totalPages = ref(1)

const loadAppointments = async (newPage = 1) => {
  loading.value = true
  try {
    const result = await fetchList({ per_page: 12, page: newPage })
    appointments.value = result.items
    totalPages.value = result.meta.last_page
    page.value = result.meta.current_page
  } catch (error) {
    toast.error('Unable to load appointments')
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAppointments()
})

</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Appointments</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Manage your sessions and client consultations.</p>
      </div>
      <UButton
        to="/appointments/new"
        color="primary"
      >
        Schedule Appointment
      </UButton>
    </div>

    <div class="glass-card p-4">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left">
          <thead class="text-xs uppercase text-slate-500 dark:text-slate-400 border-b">
            <tr>
              <th class="px-3 py-2">Client</th>
              <th class="px-3 py-2">Date & Time</th>
              <th class="px-3 py-2">Mode</th>
              <th class="px-3 py-2">Status</th>
              <th class="px-3 py-2">Advisor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="appointment in appointments" :key="appointment.uuid">
              <td class="px-3 py-3 font-medium text-slate-900 dark:text-white">
                {{ appointment.attendee?.name || 'Client' }}
              </td>
              <td class="px-3 py-3 text-slate-600 dark:text-slate-400">
                {{ formatDate(appointment.start_at, 'short') }} · {{ new Date(appointment.start_at).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }) }}
              </td>
              <td class="px-3 py-3 capitalize">
                {{ appointment.meeting_mode }}
              </td>
              <td class="px-3 py-3">
                <UBadge color="primary" variant="soft">{{ appointment.status }}</UBadge>
              </td>
              <td class="px-3 py-3">
                {{ appointment.advisor?.name || 'Self' }}
              </td>
            </tr>
            <tr v-if="!appointments.length && !loading">
              <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                No appointments scheduled. Use the button above to create one.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="5" class="px-3 py-6 text-center">
                <UIcon name="i-lucide-loader-circle" class="animate-spin w-5 h-5 text-primary mx-auto" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      v-if="totalPages > 1"
      class="flex items-center justify-between"
    >
      <span class="text-sm text-slate-500 dark:text-slate-400">
        Page {{ page }} of {{ totalPages }}
      </span>
      <div class="flex gap-2">
        <UButton
          :disabled="page === 1"
          size="sm"
          variant="soft"
          color="neutral"
          @click="loadAppointments(page - 1)"
        >
          Previous
        </UButton>
        <UButton
          :disabled="page === totalPages"
          size="sm"
          variant="soft"
          color="primary"
          @click="loadAppointments(page + 1)"
        >
          Next
        </UButton>
      </div>
    </div>
  </div>
</template>
