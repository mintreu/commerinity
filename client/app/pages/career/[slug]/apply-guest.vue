<script setup lang="ts">
import StepProfile from '~/components/onboarding/StepProfile.vue'
import StepContact from '~/components/onboarding/StepContact.vue'
import StepAddress from '~/components/onboarding/StepAddress.vue'

definePageMeta({
  layout: 'public'
})

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const toast = useToast()

const slug = route.params.slug as string

interface Recruitment {
  id: number
  uuid: string
  slug: string
  title: string
  role_label: string
  employment_type_label: string
  location: string
  is_payable: boolean
  fees: number
  fees_formatted: string
}

interface Education {
  degree: string
  institution: string
  year: number | string
}

interface Skill {
  skill: string
  description: string
}

const { data: recruitment, status, error } = await useAsyncData<{ data: Recruitment }>(
  `career-guest-apply-${slug}`,
  () => useSanctumFetch(`${config.public.apiBase}/api/careers/${slug}`)
)

const profileRef = useTemplateRef('profileRef')
const contactRef = useTemplateRef('contactRef')
const addressRef = useTemplateRef('addressRef')

const profileValid = ref(false)
const contactValid = ref(false)
const addressValid = ref(false)

const profileData = ref<Record<string, unknown>>({})
const contactData = ref<Record<string, unknown>>({})
const addressData = ref<Record<string, unknown>>({})

const guardianName = ref('')
const educations = ref<Education[]>([])
const skills = ref<Skill[]>([])
const referenceName = ref('')
const referenceContact = ref('')

const submitting = ref(false)
const validationErrors = ref<Record<string, string[]>>({})

function addEducation() {
  if (educations.value.length < 5) {
    educations.value.push({ degree: '', institution: '', year: '' })
  }
}

function removeEducation(index: number) {
  educations.value.splice(index, 1)
}

function addSkill() {
  if (skills.value.length < 10) {
    skills.value.push({ skill: '', description: '' })
  }
}

function removeSkill(index: number) {
  skills.value.splice(index, 1)
}

const computeStartingPassword = (): string => {
  const dobRaw = (profileData.value?.dob as string) || ''
  const postal = (addressData.value?.postal_code as string) || ''
  if (dobRaw) {
    const parts = dobRaw.split('-')
    if (parts.length === 3) {
      return `${parts[2]}${parts[1]}${parts[0]}`
    }
  }
  return postal || Math.random().toString(36).slice(2, 10)
}

const setAuthTokenAndRedirect = async (token: string, applicationUuid: string) => {
  const tokenCookie = useCookie<string | null>(config.public?.laravelSanctum?.token?.storageKey || 'commerinity_auth_token')
  tokenCookie.value = token
  try {
    const { refreshUser } = useSanctum()
    await refreshUser()
  } catch {}
  router.push(`/career/applications/${applicationUuid}`)
}

async function submitGuestApplication() {
  if (submitting.value) return
  validationErrors.value = {}

  const okProfile = profileValid.value || profileRef.value?.validate?.()
  const okContact = contactValid.value || true
  const okAddress = addressValid.value || addressRef.value?.validate?.()

  if (!okProfile || !okAddress) {
    toast.add({
      title: 'Missing Information',
      description: 'Please complete profile and address fields.',
      color: 'warning'
    })
    return
  }

  if (!guardianName.value.trim()) {
    validationErrors.value.guardian_name = ['Guardian name is required']
    return
  }

  submitting.value = true

  try {
    const startingPassword = computeStartingPassword()

    const validEducations = educations.value
      .filter(e => e.degree && e.institution && e.year)
      .map(e => ({ ...e, year: Number(e.year) }))

    const validSkills = skills.value.filter(s => s.skill)

    const payload = {
      profile: {
        name: profileData.value?.name,
        dob: profileData.value?.dob,
        gender: profileData.value?.gender,
        bio: profileData.value?.bio
      },
      contact: {
        mobile: contactData.value?.mobile,
        email: contactData.value?.email
      },
      address: {
        label: addressData.value?.label,
        name: addressData.value?.name,
        phone: addressData.value?.phone,
        address_line_1: addressData.value?.address_line_1,
        address_line_2: addressData.value?.address_line_2,
        city: addressData.value?.city,
        state: addressData.value?.state,
        postal_code: addressData.value?.postal_code,
        country: addressData.value?.country
      },
      application: {
        guardian_name: guardianName.value,
        reference_name: referenceName.value || null,
        reference_contact: referenceContact.value || null,
        educations: validEducations,
        skills: validSkills
      },
      password: startingPassword
    }

    const response = await $fetch<{
      success: boolean
      message?: string
      data?: {
        token: string
        application_uuid: string
        redirect_url?: string
      }
    }>(`${config.public.apiBase}/api/careers/${slug}/guest-apply`, {
      method: 'POST',
      body: payload
    })

    if (response.success && response.data?.token && response.data?.application_uuid) {
      toast.add({
        title: 'Application Submitted',
        description: 'We created your account and submitted your application.',
        color: 'success'
      })
      await setAuthTokenAndRedirect(response.data.token, response.data.application_uuid)
      return
    }

    toast.add({
      title: 'Submission Failed',
      description: response.message || 'Unable to submit application.',
      color: 'error'
    })
  } catch (err: any) {
    if (err?.data?.errors) {
      validationErrors.value = err.data.errors
    }
    toast.add({
      title: 'Error',
      description: err?.data?.message || 'Failed to submit application.',
      color: 'error'
    })
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <UContainer class="py-8 max-w-4xl">
      <div class="mb-6">
        <UButton
          :to="`/career/${slug}`"
          variant="ghost"
          icon="i-heroicons-arrow-left"
          size="sm"
        >
          Back to Job Details
        </UButton>
      </div>

      <div
        v-if="status === 'pending'"
        class="flex justify-center py-12"
      >
        <UIcon
          name="i-heroicons-arrow-path"
          class="w-8 h-8 animate-spin text-primary"
        />
      </div>

      <div
        v-else-if="error"
        class="text-center py-12"
      >
        <UIcon
          name="i-heroicons-exclamation-circle"
          class="w-16 h-16 mx-auto text-red-500 mb-4"
        />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Position not found
        </h3>
        <UButton
          to="/career"
          variant="outline"
        >
          Browse Open Positions
        </UButton>
      </div>

      <div
        v-else-if="recruitment?.data"
        class="space-y-6"
      >
        <UCard>
          <template #header>
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ recruitment.data.title }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Apply as Guest · No account required
                </p>
              </div>
              <UBadge
                v-if="recruitment.data.is_payable"
                color="warning"
                size="lg"
              >
                Fee: {{ recruitment.data.fees_formatted }}
              </UBadge>
            </div>
          </template>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
              <span class="text-xs text-gray-500">Role</span>
              <p class="font-medium">
                {{ recruitment.data.role_label }}
              </p>
            </div>
            <div class="space-y-1">
              <span class="text-xs text-gray-500">Type</span>
              <p class="font-medium">
                {{ recruitment.data.employment_type_label }}
              </p>
            </div>
            <div class="space-y-1">
              <span class="text-xs text-gray-500">Location</span>
              <p class="font-medium">
                {{ recruitment.data.location }}
              </p>
            </div>
          </div>
        </UCard>

        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Your Profile
            </h2>
          </template>
          <StepProfile
            ref="profileRef"
            @update:data="profileData = $event"
            @valid="profileValid = $event"
          />
        </UCard>

        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Contact Verification
            </h2>
          </template>
          <StepContact
            ref="contactRef"
            signup-method="mobile"
            @update:data="contactData = $event"
            @valid="contactValid = $event"
          />
        </UCard>

        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Address
            </h2>
          </template>
          <StepAddress
            ref="addressRef"
            :user-name="profileData?.name as string"
            :user-phone="contactData?.mobile as string"
            @update:data="addressData = $event"
            @valid="addressValid = $event"
          />
        </UCard>

        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Application Details
            </h2>
          </template>
          <div class="space-y-6">
            <UFormField
              label="Guardian/Parent Name"
              :error="validationErrors.guardian_name?.[0]"
              required
              class="w-full"
            >
              <UInput
                v-model="guardianName"
                placeholder="Enter guardian or parent name"
                size="lg"
                :disabled="submitting"
              />
            </UFormField>

            <UDivider label="Optional Information" />

            <div>
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Education
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Add up to 5 entries
                  </p>
                </div>
                <UButton
                  type="button"
                  variant="soft"
                  size="sm"
                  icon="i-lucide-plus"
                  :disabled="submitting || educations.length >= 5"
                  @click="addEducation"
                >
                  Add Education
                </UButton>
              </div>
              <div
                v-if="educations.length"
                class="space-y-4"
              >
                <div
                  v-for="(edu, idx) in educations"
                  :key="idx"
                  class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                >
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <UFormField label="Degree/Qualification">
                      <UInput
                        v-model="edu.degree"
                        placeholder="e.g., 12th Pass, B.Com"
                        :disabled="submitting"
                      />
                    </UFormField>
                    <UFormField label="Institution">
                      <UInput
                        v-model="edu.institution"
                        placeholder="School/College name"
                        :disabled="submitting"
                      />
                    </UFormField>
                    <UFormField label="Year">
                      <UInput
                        v-model="edu.year"
                        type="number"
                        placeholder="2024"
                        :disabled="submitting"
                      />
                    </UFormField>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Skills
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Add up to 10 entries
                  </p>
                </div>
                <UButton
                  type="button"
                  variant="soft"
                  size="sm"
                  icon="i-lucide-plus"
                  :disabled="submitting || skills.length >= 10"
                  @click="addSkill"
                >
                  Add Skill
                </UButton>
              </div>
              <div
                v-if="skills.length"
                class="space-y-4"
              >
                <div
                  v-for="(skill, idx) in skills"
                  :key="idx"
                  class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700"
                >
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <UFormField label="Skill Name">
                      <UInput
                        v-model="skill.skill"
                        placeholder="e.g., Communication, Sales"
                        :disabled="submitting"
                      />
                    </UFormField>
                    <UFormField label="Description">
                      <UInput
                        v-model="skill.description"
                        placeholder="Brief description"
                        :disabled="submitting"
                      />
                    </UFormField>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <UFormField label="Reference Name">
                  <UInput
                    v-model="referenceName"
                    placeholder="Enter reference name"
                    :disabled="submitting"
                  />
                </UFormField>
                <UFormField label="Reference Contact">
                  <UInput
                    v-model="referenceContact"
                    placeholder="Enter reference phone"
                    :disabled="submitting"
                  />
                </UFormField>
              </div>
            </div>

            <UDivider />

            <div class="flex flex-col sm:flex-row justify-end gap-4">
              <UButton
                :to="`/career/${slug}`"
                variant="outline"
                size="lg"
                :disabled="submitting"
              >
                Cancel
              </UButton>
              <UButton
                color="primary"
                size="lg"
                :loading="submitting"
                @click="submitGuestApplication"
              >
                <UIcon
                  name="i-lucide-send"
                  class="w-4 h-4 mr-2"
                />
                Submit Application
              </UButton>
            </div>
          </div>
        </UCard>

        <UAlert
          color="info"
          icon="i-lucide-info"
          title="Account Creation"
          description="We will create an account with your details. Your starting password will be your DOB (DDMMYYYY) or postal code. You can change it later from profile settings."
        />
      </div>
    </UContainer>
  </div>
</template>
