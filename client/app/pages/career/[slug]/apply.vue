<script setup lang="ts">
import type { User } from '~/types/user'

definePageMeta({
  middleware: ['auth-redirect']
})

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const toast = useToast()
const { user } = useSanctum()
const { formatDate } = useBranding()

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

interface Address {
  id: number
  label: string
  address_line_1: string
  address_line_2: string | null
  city: string | Record<string, unknown> | null
  state: string | Record<string, unknown> | null
  pincode: string | number | null
  country: string | Record<string, unknown> | null
  default: boolean
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

// Fetch recruitment details
const { data: recruitment, status, error } = await useAsyncData<{ data: Recruitment }>(
  `career-apply-${slug}`,
  () => useSanctumFetch(`${config.public.apiBase}/api/careers/${slug}`)
)

// Fetch user addresses
const { data: addressesData } = await useAsyncData<{ data: Address[] }>(
  'user-addresses',
  () => useSanctumFetch(`${config.public.apiBase}/api/addresses`)
)

const addresses = computed(() => addressesData.value?.data || [])
const selectedAddress = computed(() => {
  if (!selectedAddressId.value) return null
  return addresses.value.find((a: Address) => a.id === selectedAddressId.value) || null
})

// Typed user for profile display
const typedUser = computed(() => user.value as User | null)

// Form state
const guardianName = ref('')
const selectedAddressId = ref<number | null>(null)
const educations = ref<Education[]>([])
const skills = ref<Skill[]>([])

// Auto-select default address
watch(addresses, (addrs) => {
  if (addrs.length && !selectedAddressId.value) {
    const def = addrs.find((a: Address) => a.default)
    selectedAddressId.value = def?.id || addrs[0]?.id || null
  }
}, { immediate: true })

const addressOptions = computed(() => {
  return addresses.value.map((addr: Address) => ({
    label: `${addr.label} - ${addr.address_line_1}${getAddressPartLabel(addr.city) ? `, ${getAddressPartLabel(addr.city)}` : ''}`,
    value: addr.id
  }))
})

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

const submitting = ref(false)
const validationErrors = ref<Record<string, string[]>>({})
const referenceName = ref('')
const referenceContact = ref('')

async function submitApplication() {
  if (submitting.value) return

  validationErrors.value = {}

  if (!guardianName.value.trim()) {
    validationErrors.value.guardian_name = ['Guardian name is required']
    return
  }

  if (!selectedAddressId.value) {
    validationErrors.value.address_id = ['Please select an address']
    return
  }

  submitting.value = true

  try {
    const payload: Record<string, unknown> = {
      guardian_name: guardianName.value,
      address_id: selectedAddressId.value
    }

    if (referenceName.value.trim()) {
      payload.reference_name = referenceName.value.trim()
    }
    if (referenceContact.value.trim()) {
      payload.reference_contact = referenceContact.value.trim()
    }

    const validEducations = educations.value.filter(
      (e: Education) => e.degree && e.institution && e.year
    )
    if (validEducations.length) {
      payload.educations = validEducations.map((e: Education) => ({
        ...e,
        year: Number(e.year)
      }))
    }

    const validSkills = skills.value.filter((s: Skill) => s.skill)
    if (validSkills.length) {
      payload.skills = validSkills
    }

    const response = await useSanctumFetch<{
      data: {
        requires_payment: boolean
        payment_url: string | null
        application: {
          uuid: string
          status: string
        }
      }
    }>(`${config.public.apiBase}/api/careers/${slug}/apply`, {
      method: 'POST',
      body: payload
    })

    toast.add({
      title: 'Application Submitted',
      description: response.data.requires_payment
        ? 'Please complete payment to finalize your application.'
        : 'Your application has been submitted successfully.',
      color: 'success'
    })

    if (response.data.requires_payment && response.data.payment_url) {
      window.location.href = response.data.payment_url
    } else {
      router.push(`/career/applications/${response.data.application.uuid}`)
    }
  } catch (err: unknown) {
    const error = err as { data?: { message?: string, errors?: Record<string, string[]> } }

    if (error.data?.errors) {
      validationErrors.value = error.data.errors
    }

    toast.add({
      title: 'Error',
      description: error.data?.message || 'Failed to submit application. Please try again.',
      color: 'error'
    })
  } finally {
    submitting.value = false
  }
}

// Format full address for display
function getAddressPartLabel(value: unknown): string {
  if (value === null || value === undefined) return ''
  if (typeof value === 'string') return value
  if (typeof value === 'number') return value.toString()
  if (typeof value === 'object') {
    const v = value as Record<string, unknown>
    const candidates = [v.name, v.label, v.title, v.value]
    const first = candidates.find(c => typeof c === 'string' && c.trim().length > 0) as string | undefined
    return first || ''
  }
  return ''
}

function formatFullAddress(addr: Address | null): string {
  if (!addr) return ''
  const parts: string[] = []
  if (addr.address_line_1) parts.push(addr.address_line_1)
  if (addr.address_line_2) parts.push(addr.address_line_2)
  const city = getAddressPartLabel(addr.city)
  const state = getAddressPartLabel(addr.state)
  const pincode = getAddressPartLabel(addr.pincode)
  const country = getAddressPartLabel(addr.country)
  if (city) parts.push(city)
  if (state) parts.push(state)
  if (pincode) parts.push(pincode)
  if (country) parts.push(country)
  return parts.filter(Boolean).join(', ')
}

function formatGender(gender: string | null | undefined): string {
  if (!gender) return 'Not provided'
  const map: Record<string, string> = { male: 'Male', female: 'Female', other: 'Other' }
  return map[gender] || gender
}

function formatDob(dob: string | null | undefined): string {
  if (!dob) return 'Not provided'
  return formatDate(dob, 'medium')
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 dark:from-gray-900 dark:to-slate-900">
    <UContainer class="py-8 max-w-4xl">
      <!-- Back Button -->
      <div class="mb-6">
        <UButton
          :to="`/career/${slug}`"
          variant="ghost"
          icon="i-lucide-arrow-left"
          size="sm"
        >
          Back to Job Details
        </UButton>
      </div>

      <!-- Loading State -->
      <div
        v-if="status === 'pending'"
        class="flex justify-center py-12"
      >
        <UIcon
          name="i-lucide-loader-2"
          class="w-8 h-8 animate-spin text-primary"
        />
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="text-center py-12"
      >
        <UIcon
          name="i-lucide-alert-circle"
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

      <!-- Application Form -->
      <div
        v-else-if="recruitment?.data"
        class="space-y-6"
      >
        <!-- Job Header Card -->
        <UCard :ui="{ root: 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' }">
          <div class="flex items-start justify-between">
            <div>
              <h1 class="text-2xl font-bold mb-2">
                {{ recruitment.data.title }}
              </h1>
              <div class="flex flex-wrap gap-3 text-sm text-white/80">
                <span class="flex items-center gap-1">
                  <UIcon
                    name="i-lucide-briefcase"
                    class="w-4 h-4"
                  />
                  {{ recruitment.data.role_label }}
                </span>
                <span class="flex items-center gap-1">
                  <UIcon
                    name="i-lucide-clock"
                    class="w-4 h-4"
                  />
                  {{ recruitment.data.employment_type_label }}
                </span>
                <span class="flex items-center gap-1">
                  <UIcon
                    name="i-lucide-map-pin"
                    class="w-4 h-4"
                  />
                  {{ recruitment.data.location }}
                </span>
              </div>
            </div>
            <UBadge
              v-if="recruitment.data.is_payable"
              color="warning"
              variant="solid"
              size="lg"
            >
              Fee: {{ recruitment.data.fees_formatted }}
            </UBadge>
          </div>
        </UCard>

        <!-- Application Fee Alert -->
        <UAlert
          v-if="recruitment.data.is_payable"
          color="warning"
          icon="i-lucide-indian-rupee"
          title="Application Fee Required"
          :description="`This position requires an application fee of ${recruitment.data.fees_formatted}. You will be redirected to payment after submitting.`"
        />

        <!-- Applicant Information Section (Read-Only) -->
        <UCard>
          <template #header>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                <UIcon
                  name="i-lucide-user"
                  class="w-5 h-5 text-blue-600 dark:text-blue-400"
                />
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Applicant Information
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Your profile details will be included in the application
                </p>
              </div>
            </div>
          </template>

          <div class="grid md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</label>
              <p class="text-base font-medium text-gray-900 dark:text-white">
                {{ typedUser?.name || 'Not provided' }}
              </p>
            </div>

            <!-- Email -->
            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</label>
              <p class="text-base font-medium text-gray-900 dark:text-white">
                {{ typedUser?.email || 'Not provided' }}
              </p>
            </div>

            <!-- Phone -->
            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</label>
              <p class="text-base font-medium text-gray-900 dark:text-white">
                {{ typedUser?.mobile || typedUser?.phone || 'Not provided' }}
              </p>
            </div>

            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Gender</label>
              <p class="text-base font-medium text-gray-900 dark:text-white">
                {{ formatGender(typedUser?.gender) }}
              </p>
            </div>

            <div class="space-y-1">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</label>
              <p class="text-base font-medium text-gray-900 dark:text-white">
                {{ formatDob(typedUser?.dob) }}
              </p>
            </div>
          </div>

          <template #footer>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
              <UIcon
                name="i-lucide-info"
                class="w-4 h-4"
              />
              <span>Need to update your profile? <NuxtLink
                to="/profile/edit"
                class="text-blue-600 dark:text-blue-400 hover:underline"
              >Edit Profile</NuxtLink></span>
            </div>
          </template>
        </UCard>

        <!-- Address Section -->
        <UCard>
          <template #header>
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                  <UIcon
                    name="i-lucide-map-pin"
                    class="w-5 h-5 text-green-600 dark:text-green-400"
                  />
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Communication Address
                  </h2>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Select the address for official correspondence
                  </p>
                </div>
              </div>

              <UButton
                to="/addresses"
                variant="soft"
                size="sm"
                icon="i-lucide-plus"
              >
                Add Address
              </UButton>
            </div>
          </template>

          <div
            v-if="addresses.length"
            class="space-y-4"
          >
            <!-- Address Selector -->
            <UFormField
              label="Select Address"
              :error="validationErrors.address_id?.[0]"
              required
              class="w-full"
            >
              <USelect
                v-model="selectedAddressId"
                :items="addressOptions"
                value-key="value"
                placeholder="Choose an address"
                size="lg"
                :disabled="submitting"
                :ui="{ base: 'w-full' }"
              />
            </UFormField>

            <!-- Selected Address Preview -->
            <div
              v-if="selectedAddress"
              class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl"
            >
              <div class="flex items-start gap-3">
                <UIcon
                  name="i-lucide-check-circle"
                  class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5"
                />
                <div>
                  <p class="font-medium text-gray-900 dark:text-white mb-1">
                    {{ selectedAddress.label }}
                  </p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ formatFullAddress(selectedAddress) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- No Address Warning -->
          <UAlert
            v-else
            color="warning"
            icon="i-lucide-alert-triangle"
            title="No Address Found"
            description="You need to add an address before applying for this position."
          >
            <template #actions>
              <UButton
                to="/addresses"
                size="sm"
                color="warning"
              >
                Add Address
              </UButton>
            </template>
          </UAlert>
        </UCard>

        <!-- Application Form -->
        <UCard>
          <template #header>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                <UIcon
                  name="i-lucide-file-text"
                  class="w-5 h-5 text-purple-600 dark:text-purple-400"
                />
              </div>
              <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Application Details
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Fill in the required information
                </p>
              </div>
            </div>
          </template>

          <form
            class="space-y-6"
            @submit.prevent="submitApplication"
          >
            <!-- Guardian Name -->
            <UFormField
              label="Guardian/Parent Name"
              hint="Required for verification purposes"
              :error="validationErrors.guardian_name?.[0]"
              required
              class="w-full"
            >
              <UInput
                v-model="guardianName"
                placeholder="Enter guardian or parent name"
                size="lg"
                :disabled="submitting"
                class="w-full"
                :ui="{ base: 'w-full' }"
              />
            </UFormField>

            <UDivider label="Optional Information" />

            <!-- Education Section -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Education
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Add your educational qualifications (up to 5)
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
                  <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                      Education #{{ idx + 1 }}
                    </span>
                    <UButton
                      type="button"
                      variant="ghost"
                      color="error"
                      size="xs"
                      icon="i-lucide-trash-2"
                      :disabled="submitting"
                      @click="removeEducation(idx)"
                    />
                  </div>
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

              <p
                v-else
                class="text-sm text-gray-500 dark:text-gray-400 italic"
              >
                No education added. Click "Add Education" to include your qualifications.
              </p>
            </div>

            <!-- Skills Section -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Skills
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Highlight your relevant skills (up to 10)
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
                  <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                      Skill #{{ idx + 1 }}
                    </span>
                    <UButton
                      type="button"
                      variant="ghost"
                      color="error"
                      size="xs"
                      icon="i-lucide-trash-2"
                      :disabled="submitting"
                      @click="removeSkill(idx)"
                    />
                  </div>
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
                        placeholder="Brief description of your expertise"
                        :disabled="submitting"
                      />
                    </UFormField>
                  </div>
                </div>
              </div>

              <p
                v-else
                class="text-sm text-gray-500 dark:text-gray-400 italic"
              >
                No skills added. Click "Add Skill" to showcase your abilities.
              </p>
            </div>

            <div>
              <div class="mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                  Reference
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  Add a reference contact (optional)
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <UFormField
                  label="Reference Name"
                  :error="validationErrors.reference_name?.[0]"
                  class="w-full"
                >
                  <UInput
                    v-model="referenceName"
                    placeholder="Enter reference name"
                    :disabled="submitting"
                    class="w-full"
                    :ui="{ base: 'w-full' }"
                  />
                </UFormField>

                <UFormField
                  label="Reference Contact"
                  :error="validationErrors.reference_contact?.[0]"
                  class="w-full"
                >
                  <UInput
                    v-model="referenceContact"
                    placeholder="Enter reference phone"
                    :disabled="submitting"
                    class="w-full"
                    :ui="{ base: 'w-full' }"
                  />
                </UFormField>
              </div>
            </div>

            <UDivider />

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-4">
              <UButton
                type="button"
                variant="outline"
                size="lg"
                :to="`/career/${slug}`"
                :disabled="submitting"
              >
                Cancel
              </UButton>
              <UButton
                type="submit"
                color="primary"
                size="lg"
                :loading="submitting"
                :disabled="!addresses.length"
              >
                <UIcon
                  name="i-lucide-send"
                  class="w-4 h-4 mr-2"
                />
                {{ recruitment.data.is_payable ? 'Submit & Proceed to Payment' : 'Submit Application' }}
              </UButton>
            </div>
          </form>
        </UCard>
      </div>
    </UContainer>
  </div>
</template>
