<script setup lang="ts">
definePageMeta({
  middleware: ['$auth']
})

const config = useRuntimeConfig()

interface JobApplication {
  uuid: string
  status: string
  status_label: string
  status_color: string
  is_paid: boolean
  amount: number
  amount_formatted: string
  submitted_at: string | null
  submitted_at_formatted: string | null
  created_at: string
  recruitment: {
    title: string
    slug: string
    role_label: string
    employment_type_label: string
  }
}

const { data: applications, status, error, refresh } = await useAsyncData<{ data: JobApplication[] }>(
  'my-applications',
  () => useSanctumFetch(`${config.public.apiBase}/api/my-applications`)
)

const statusColorMap: Record<string, 'neutral' | 'warning' | 'info' | 'primary' | 'success' | 'error'> = {
  draft: 'neutral',
  awaiting_payment: 'warning',
  submitted: 'info',
  under_review: 'primary',
  accepted: 'success',
  rejected: 'error',
  withdrawn: 'neutral'
}

function getStatusColor(applicationStatus: string): 'neutral' | 'warning' | 'info' | 'primary' | 'success' | 'error' {
  return statusColorMap[applicationStatus] || 'neutral'
}

function formatDate(dateString: string | null): string {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

async function handleRefresh() {
  await refresh()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <UContainer class="py-8">
      <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
          <UButton
            to="/career"
            variant="ghost"
            icon="i-heroicons-arrow-left"
            size="sm"
          >
            Back to Careers
          </UButton>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          My Applications
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Track the status of your job applications
        </p>
      </div>

      <div v-if="status === 'pending'" class="flex justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-primary" />
      </div>

      <div v-else-if="error" class="text-center py-12">
        <UIcon name="i-heroicons-exclamation-circle" class="w-16 h-16 mx-auto text-red-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Failed to load applications
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          {{ error.message }}
        </p>
        <UButton @click="handleRefresh" variant="outline">
          Try Again
        </UButton>
      </div>

      <div v-else-if="!applications?.data?.length" class="text-center py-12">
        <UIcon name="i-heroicons-document-text" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          No applications yet
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          You haven't applied to any positions yet.
        </p>
        <UButton to="/career" color="primary">
          Browse Open Positions
        </UButton>
      </div>

      <div v-else class="space-y-4">
        <UCard
          v-for="application in applications.data"
          :key="application.uuid"
          class="hover:shadow-md transition-shadow"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ application.recruitment.title }}
                </h3>
                <UBadge :color="getStatusColor(application.status)" size="sm">
                  {{ application.status_label }}
                </UBadge>
              </div>
              
              <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                <span class="flex items-center gap-1">
                  <UIcon name="i-heroicons-briefcase" class="w-4 h-4" />
                  {{ application.recruitment.role_label }}
                </span>
                <span class="flex items-center gap-1">
                  <UIcon name="i-heroicons-clock" class="w-4 h-4" />
                  {{ application.recruitment.employment_type_label }}
                </span>
                <span class="flex items-center gap-1">
                  <UIcon name="i-heroicons-calendar" class="w-4 h-4" />
                  Applied: {{ formatDate(application.created_at) }}
                </span>
              </div>

              <div class="mt-2 text-sm">
                <span class="text-gray-500 dark:text-gray-400">Application ID:</span>
                <span class="ml-1 font-mono text-gray-700 dark:text-gray-300">
                  {{ application.uuid }}
                </span>
              </div>

              <div v-if="application.status === 'awaiting_payment'" class="mt-3">
                <UAlert
                  color="warning"
                  icon="i-heroicons-exclamation-triangle"
                  title="Payment Required"
                  :description="`Complete payment of ${application.amount_formatted} to submit your application.`"
                />
              </div>
            </div>

            <div class="flex flex-col gap-2 sm:items-end">
              <UButton
                :to="`/career/applications/${application.uuid}`"
                variant="outline"
                size="sm"
              >
                View Details
              </UButton>
              
              <UButton
                v-if="application.status === 'awaiting_payment'"
                :to="`/career/applications/${application.uuid}/pay`"
                color="primary"
                size="sm"
              >
                Complete Payment
              </UButton>
            </div>
          </div>
        </UCard>
      </div>
    </UContainer>
  </div>
</template>
