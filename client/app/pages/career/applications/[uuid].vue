<script setup lang="ts">
definePageMeta({
  middleware: ['$auth']
})

const route = useRoute()
const config = useRuntimeConfig()
const toast = useToast()

const uuid = route.params.uuid as string

interface Address {
  full_address: string
  city: string
  state: string
  pincode: string
}

interface Education {
  degree: string
  institution: string
  year: number
}

interface Skill {
  skill: string
  description: string
}

interface JobApplication {
  uuid: string
  status: string
  status_label: string
  status_color: string
  status_feedback: string | null
  guardian_name: string
  is_paid: boolean
  amount: number
  amount_formatted: string
  educations: Education[] | null
  skills: Skill[] | null
  experiences: unknown[] | null
  reference_name: string | null
  reference_contact: string | null
  submitted_at: string | null
  submitted_at_formatted: string | null
  created_at: string
  can_withdraw: boolean
  address: Address | null
  recruitment: {
    id: number
    uuid: string
    slug: string
    title: string
    role_label: string
    employment_type_label: string
    location: string
    is_payable: boolean
    fees_formatted: string
    status: string
  }
}

const { data: application, status, error, refresh } = await useAsyncData<{ data: JobApplication }>(
  `application-${uuid}`,
  () => useSanctumFetch(`${config.public.apiBase}/api/my-applications/${uuid}`)
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
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <UContainer class="py-8">
      <div class="mb-6">
        <UButton
          to="/career/applications"
          variant="ghost"
          icon="i-heroicons-arrow-left"
          size="sm"
        >
          Back to My Applications
        </UButton>
      </div>

      <div v-if="status === 'pending'" class="flex justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-primary" />
      </div>

      <div v-else-if="error" class="text-center py-12">
        <UIcon name="i-heroicons-exclamation-circle" class="w-16 h-16 mx-auto text-red-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Application not found
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          The application you're looking for doesn't exist or you don't have access to it.
        </p>
        <UButton to="/career/applications" variant="outline">
          View All Applications
        </UButton>
      </div>

      <div v-else-if="application?.data" class="space-y-6">
        <UCard>
          <template #header>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                      {{ application.data.recruitment.title }}
                    </h1>
                    <UButton
                      :to="`/career/${application.data.recruitment.slug}`"
                      variant="ghost"
                      color="primary"
                      size="xs"
                      icon="i-heroicons-arrow-top-right-on-square"
                    >
                      View Position
                    </UButton>
                  </div>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Application ID: <span class="font-mono">{{ application.data.uuid }}</span>
                  </p>
                </div>
              <UBadge :color="getStatusColor(application.data.status)" size="lg">
                {{ application.data.status_label }}
              </UBadge>
            </div>
          </template>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Position</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.recruitment.role_label }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Employment Type</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.recruitment.employment_type_label }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Location</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.recruitment.location }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Applied On</h3>
              <p class="text-gray-900 dark:text-white">{{ formatDate(application.data.created_at) }}</p>
            </div>
            <div v-if="application.data.submitted_at">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Submitted On</h3>
              <p class="text-gray-900 dark:text-white">{{ formatDate(application.data.submitted_at) }}</p>
            </div>
          </div>

          <div v-if="application.data.status_feedback" class="mt-6">
            <UAlert
              :color="application.data.status === 'accepted' ? 'success' : application.data.status === 'rejected' ? 'error' : 'info'"
              :icon="application.data.status === 'accepted' ? 'i-heroicons-check-circle' : application.data.status === 'rejected' ? 'i-heroicons-x-circle' : 'i-heroicons-information-circle'"
              title="Feedback"
              :description="application.data.status_feedback"
            />
          </div>

          <div v-if="application.data.recruitment.is_payable && application.data.status === 'awaiting_payment'" class="mt-6">
            <UAlert
              color="warning"
              icon="i-heroicons-exclamation-triangle"
              title="Payment Pending"
            >
              <template #description>
                <p>Complete payment of <strong>{{ application.data.amount_formatted }}</strong> to submit your application.</p>
              </template>
            </UAlert>
            <div class="mt-4">
<!--              Api End Point is "`/api/my-applications/${application.data.uuid}/pay`" -->
              <CheckoutButton
                label="Complete Payment"
                icon="i-lucide-credit-card"
                color="primary"
                size="lg"
                modal-title="Pay Application Fee"
                :amount="application.data.amount"
                :amount-formatted="application.data.amount_formatted"
                :description="application.data.recruitment.title"
                :checkout-endpoint="`/api/my-applications/${application.data.uuid}/pay`"
                :checkout-payload="{ application_uuid: application.data.uuid }"
                @success="refresh"
              />
            </div>
          </div>
        </UCard>

        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Personal Information
            </h2>
          </template>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Guardian Name</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.guardian_name }}</p>
            </div>
            <div v-if="application.data.address">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Address</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.address.full_address }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ application.data.address.city }}, {{ application.data.address.state }} - {{ application.data.address.pincode }}
              </p>
            </div>
            <div v-if="application.data.reference_name">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Reference</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.reference_name }}</p>
              <p v-if="application.data.reference_contact" class="text-sm text-gray-600 dark:text-gray-400">
                {{ application.data.reference_contact }}
              </p>
            </div>
          </div>
        </UCard>

        <UCard v-if="application.data.educations?.length">
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Education
            </h2>
          </template>

          <div class="space-y-4">
            <div
              v-for="(edu, index) in application.data.educations"
              :key="index"
              class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0"
            >
              <h3 class="font-medium text-gray-900 dark:text-white">{{ edu.degree }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ edu.institution }} ({{ edu.year }})
              </p>
            </div>
          </div>
        </UCard>

        <UCard v-if="application.data.skills?.length">
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Skills
            </h2>
          </template>

          <div class="space-y-4">
            <div
              v-for="(skill, index) in application.data.skills"
              :key="index"
              class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0"
            >
              <h3 class="font-medium text-gray-900 dark:text-white">{{ skill.skill }}</h3>
              <p v-if="skill.description" class="text-sm text-gray-600 dark:text-gray-400">
                {{ skill.description }}
              </p>
            </div>
          </div>
        </UCard>

        <UCard v-if="application.data.recruitment.is_payable">
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Payment Information
            </h2>
          </template>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Application Fee</h3>
              <p class="text-gray-900 dark:text-white">{{ application.data.amount_formatted }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Payment Status</h3>
              <UBadge :color="application.data.is_paid ? 'success' : 'warning'">
                {{ application.data.is_paid ? 'Paid' : 'Pending' }}
              </UBadge>
            </div>
          </div>
        </UCard>
      </div>
    </UContainer>
  </div>
</template>
