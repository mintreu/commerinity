<script setup lang="ts">
import { useDashboardPrograms } from '~/composables/useDashboardPrograms'

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const { fetchList } = useDashboardPrograms()
const toast = useToast()

const programs = ref([] as Array<{
  uuid: string
  title: string
  status: string
  start_date?: string
  end_date?: string
  participants: unknown[]
}>)
const loading = ref(false)
const page = ref(1)
const totalPages = ref(1)

const loadPrograms = async (newPage = 1) => {
  loading.value = true
  try {
    const res = await fetchList({ per_page: 10, page: newPage })
    programs.value = res.items
    totalPages.value = res.meta.last_page
    page.value = res.meta.current_page
  } catch (error) {
    toast.error('Unable to load programs.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadPrograms()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Programs</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          View your programs and invite mentees.
        </p>
      </div>
      <UButton to="/programs/new" color="primary">
        Create Program
      </UButton>
    </div>

    <div class="glass-card p-4">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left">
          <thead class="text-xs uppercase text-slate-500 dark:text-slate-400 border-b">
            <tr>
              <th class="px-3 py-2">Title</th>
              <th class="px-3 py-2">Dates</th>
              <th class="px-3 py-2">Participants</th>
              <th class="px-3 py-2">Status</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="program in programs" :key="program.uuid">
              <td class="px-3 py-3 font-medium text-slate-900 dark:text-white">
                {{ program.title }}
              </td>
              <td class="px-3 py-3 text-slate-600 dark:text-slate-400">
                {{ program.start_date || 'TBD' }} - {{ program.end_date || 'TBD' }}
              </td>
              <td class="px-3 py-3 text-slate-600 dark:text-slate-400">
                {{ program.participants.length }} confirmed
              </td>
              <td class="px-3 py-3 capitalize">
                <UBadge color="primary" variant="soft">
                  {{ program.status }}
                </UBadge>
              </td>
              <td class="px-3 py-3">
                <NuxtLink
                  class="text-primary text-sm hover:underline"
                  :to="`/programs/${program.uuid}`"
                >
                  View
                </NuxtLink>
              </td>
            </tr>
            <tr v-if="!programs.length && !loading">
              <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                You have no programs yet.
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

    <div v-if="totalPages > 1" class="flex items-center justify-between">
      <span class="text-sm text-slate-500 dark:text-slate-400">
        Page {{ page }} of {{ totalPages }}
      </span>
      <div class="flex gap-2">
        <UButton
          size="sm"
          variant="soft"
          color="neutral"
          :disabled="page === 1"
          @click="loadPrograms(page - 1)"
        >
          Previous
        </UButton>
        <UButton
          size="sm"
          variant="soft"
          color="primary"
          :disabled="page === totalPages"
          @click="loadPrograms(page + 1)"
        >
          Next
        </UButton>
      </div>
    </div>
  </div>
</template>
