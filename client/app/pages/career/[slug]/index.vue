<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const route = useRoute()
const config = useRuntimeConfig()
const { isLoggedIn } = useSanctum()

const slug = route.params.slug as string

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
  open_date: string
  close_date: string
  open_date_formatted: string
  close_date_formatted: string
  is_payable: boolean
  fees: number
  fees_formatted: string
  fees_in_rupees: number
  requirements: string[]
  benefits: string[]
  eligibility: {
    min_age?: number
    max_age?: number
  }
  status: string
  status_label: string
  is_open: boolean
  display_image: string | null
  info_pdf: string | null
}

interface ApplicationCheck {
  has_applied: boolean
  application: {
    uuid: string
    status: string
    status_label: string
  } | null
}

const { data: recruitment, status, error } = await useAsyncData<{ data: Recruitment }>(
  `career-${slug}`,
  () => useSanctumFetch(`${config.public.apiBase}/api/careers/${slug}`)
)

const { data: applicationCheck, refresh: refreshApplicationCheck } = await useAsyncData<{ data: ApplicationCheck }>(
  `career-check-${slug}`,
  () => useSanctumFetch(`${config.public.apiBase}/api/careers/${slug}/check-application`),
  { immediate: isLoggedIn.value }
)

watch(isLoggedIn, (val) => {
  if (val) refreshApplicationCheck()
})

const job = computed(() => recruitment.value?.data)
const hasApplied = computed(() => applicationCheck.value?.data?.has_applied ?? false)
const existingApplication = computed(() => applicationCheck.value?.data?.application)

import GuestApplyForm from '~/components/career/GuestApplyForm.vue'

const showGuestApplyForm = ref(false)

const handleGuestApplication = async (formData: any) => {
  console.log('Guest application submitted:', formData)
  // Here I will call the backend API endpoint
}


</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <UContainer class="py-8">
      <div class="mb-6">
        <UButton
          to="/career"
          variant="ghost"
          icon="i-heroicons-arrow-left"
          size="sm"
        >
          Back to Careers
        </UButton>
      </div>

      <div v-if="status === 'pending'" class="flex justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin text-primary" />
      </div>

      <div v-else-if="error" class="text-center py-12">
        <UIcon name="i-heroicons-exclamation-circle" class="w-16 h-16 mx-auto text-red-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Position not found
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          The position you're looking for doesn't exist or is no longer available.
        </p>
        <UButton to="/career" variant="outline">
          Browse Open Positions
        </UButton>
      </div>

      <div v-else-if="job" class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
          <UCard>
            <template #header>
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ job.title }}
                  </h1>
                  <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                      <UIcon name="i-heroicons-briefcase" class="w-4 h-4" />
                      {{ job.role_label }}
                    </span>
                    <span class="flex items-center gap-1">
                      <UIcon name="i-heroicons-clock" class="w-4 h-4" />
                      {{ job.employment_type_label }}
                    </span>
                    <span class="flex items-center gap-1">
                      <UIcon name="i-heroicons-map-pin" class="w-4 h-4" />
                      {{ job.location }}
                    </span>
                  </div>
                </div>
                <div class="flex gap-2">
                  <UBadge v-if="job.is_open" color="success" variant="subtle">Open</UBadge>
                  <UBadge v-else color="error" variant="subtle">Closed</UBadge>
                  <UBadge v-if="job.is_payable" color="warning" variant="subtle" icon="i-heroicons-banknotes">
                    Fee Required
                  </UBadge>
                </div>
              </div>
            </template>

            <UAlert
              v-if="job.is_payable"
              color="warning"
              variant="subtle"
              icon="i-heroicons-information-circle"
              class="mb-6"
              title="Application Fee Required"
              :description="`Applying for this position requires a non-refundable fee of ${job.fees_formatted}.`"
            />

            <RichContent :content="job.description" class="max-w-none" />
          </UCard>

          <UCard v-if="job.requirements?.length">
            <template #header>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Requirements
              </h2>
            </template>
            <ul class="space-y-2">
              <li
                v-for="(req, idx) in job.requirements"
                :key="idx"
                class="flex items-start gap-2"
              >
                <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                <span class="text-gray-700 dark:text-gray-300">{{ req }}</span>
              </li>
            </ul>
          </UCard>

          <UCard v-if="job.benefits?.length">
            <template #header>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Benefits
              </h2>
            </template>
            <ul class="space-y-2">
              <li
                v-for="(benefit, idx) in job.benefits"
                :key="idx"
                class="flex items-start gap-2"
              >
                <UIcon name="i-heroicons-star" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                <span class="text-gray-700 dark:text-gray-300">{{ benefit }}</span>
              </li>
            </ul>
          </UCard>

          <UCard v-if="job.eligibility">
            <template #header>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Eligibility
              </h2>
            </template>
            <div class="grid grid-cols-2 gap-4">
              <div v-if="job.eligibility.min_age">
                <span class="text-sm text-gray-500 dark:text-gray-400">Minimum Age</span>
                <p class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ job.eligibility.min_age }} years
                </p>
              </div>
              <div v-if="job.eligibility.max_age">
                <span class="text-sm text-gray-500 dark:text-gray-400">Maximum Age</span>
                <p class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ job.eligibility.max_age }} years
                </p>
              </div>
            </div>
          </UCard>
        </div>

        <div class="space-y-6">
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Apply Now
              </h2>
            </template>

            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">Vacancies</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ job.vacancy }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">Opens</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ job.open_date_formatted }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">Closes</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ job.close_date_formatted }}</span>
              </div>
              <div v-if="job.is_payable" class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-gray-400">Application Fee</span>
                <span class="font-medium text-amber-600 dark:text-amber-400">{{ job.fees_formatted }}</span>
              </div>

              <UDivider />

              <div v-if="hasApplied && existingApplication">
                <UAlert
                  color="info"
                  icon="i-heroicons-information-circle"
                  title="Already Applied"
                >
                  <template #description>
                    <p>You have already applied for this position.</p>
                    <p class="mt-1 text-sm">
                      Status: <strong>{{ existingApplication.status_label }}</strong>
                    </p>
                  </template>
                </UAlert>
                <UButton
                  :to="`/career/applications/${existingApplication.uuid}`"
                  color="primary"
                  variant="outline"
                  block
                  class="mt-4"
                >
                  View Application
                </UButton>
              </div>

              <div v-else-if="!job.is_open">
                <UAlert
                  color="warning"
                  icon="i-heroicons-exclamation-triangle"
                  title="Applications Closed"
                  description="This position is no longer accepting applications."
                />
              </div>

              <div v-else-if="!isLoggedIn">
                <UAlert
                  color="info"
                  icon="i-heroicons-information-circle"
                  title="Login Required"
                  description="Please login to apply for this position."
                />
                <UButton
                  to="/auth/login"
                  color="primary"
                  block
                  class="mt-4"
                >
                  Login to Apply
                </UButton>
              </div>

              <div v-else>
                <UButton
                  :to="`/career/${job.slug}/apply`"
                  color="primary"
                  block
                  size="lg"
                >
                  Apply Now
                </UButton>
              </div>
            </div>

            <template #footer v-if="job.info_pdf">
              <UButton
                :href="job.info_pdf"
                target="_blank"
                variant="outline"
                icon="i-heroicons-document-arrow-down"
                block
              >
                Download Info PDF
              </UButton>
            </template>
          </UCard>
        </div>
      </div>
    </UContainer>
  </div>
</template>
