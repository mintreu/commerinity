<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <!-- Loading State -->
    <div
      v-if="loading"
      class="min-h-screen flex items-center justify-center"
    >
      <div class="text-center">
        <UIcon
          name="i-lucide-loader-2"
          class="w-12 h-12 animate-spin text-primary-500 mx-auto mb-4"
        />
        <p class="text-gray-600 dark:text-gray-400">
          Loading your profile...
        </p>
      </div>
    </div>

    <!-- Onboarding Content -->
    <div
      v-else
      class="min-h-screen flex flex-col"
    >
      <!-- Header with Logo (Mobile) -->
      <div class="md:hidden px-4 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
              <UIcon
                name="i-lucide-hexagon"
                class="w-5 h-5 text-white"
              />
            </div>
            <span class="font-bold text-gray-900 dark:text-white">Commerinity Pro</span>
          </div>
          <!-- Skip Button (Mobile) -->
          <UButton
            v-if="canSkip"
            variant="ghost"
            color="neutral"
            size="sm"
            @click="handleSkip"
          >
            Skip
          </UButton>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 flex items-center justify-center p-4 md:p-8">
        <div class="w-full max-w-6xl">
          <!-- Desktop Card -->
          <UCard
            class="shadow-xl"
            :ui="{
              root: 'overflow-visible',
              body: 'p-6 md:p-10'
            }"
          >
            <!-- Header -->
            <template #header>
              <div class="hidden md:flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                    <UIcon
                      name="i-lucide-hexagon"
                      class="w-6 h-6 text-white"
                    />
                  </div>
                  <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                      Profile Setup
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Complete your account setup
                    </p>
                  </div>
                </div>
                <UBadge
                  color="primary"
                  variant="subtle"
                  size="lg"
                >
                  {{ Math.round(progressPercent) }}% Complete
                </UBadge>
              </div>
            </template>

            <!-- Stepper (Desktop only) -->
            <UStepper
              v-if="!isMobile"
              ref="stepper"
              v-model="currentStep"
              :items="stepItems"
              :disabled="true"
              :linear="true"
              color="primary"
              :orientation="'horizontal'"
              :ui="{ root: 'mb-8' }"
            >
              <!-- Welcome Step -->
              <template #welcome>
                <OnboardingStepWelcome :user-name="userName" />
              </template>

              <!-- Profile Step -->
              <template #profile>
                <OnboardingStepProfile
                  ref="profileStep"
                  :initial-data="profileInitialData"
                  @update:data="handleProfileUpdate"
                  @valid="profileValid = $event"
                />
              </template>

              <!-- Contact Step -->
              <template #contact>
                <OnboardingStepContact
                  ref="contactStep"
                  :signup-method="signupMethod"
                  :user-email="userEmail"
                  :user-mobile="userMobile"
                  :email-verified-at="emailVerifiedAt"
                  :mobile-verified-at="mobileVerifiedAt"
                  @update:data="handleContactUpdate"
                  @valid="contactValid = $event"
                  @verified="handleContactVerified"
                />
              </template>

              <!-- Address Step -->
              <template #address>
                <OnboardingStepAddress
                  ref="addressStep"
                  :user-name="userName"
                  :user-phone="userMobile"
                  @update:data="addressData = $event"
                  @valid="addressValid = $event"
                />
              </template>
            </UStepper>

            <!-- Mobile: Show current step content -->
            <div v-else>
              <component
                :is="currentStepComponent"
                v-bind="currentStepProps"
                :ref="currentStepRef"
                @update:data="handleCurrentStepDataUpdate"
                @valid="handleCurrentStepValidUpdate"
              />
            </div>

            <!-- Navigation Controls -->
            <div class="flex items-center justify-between gap-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
              <!-- Back Button -->
              <UButton
                v-if="currentStep > 0"
                variant="outline"
                color="neutral"
                size="lg"
                :disabled="submitting"
                @click="prevStep"
              >
                <UIcon
                  name="i-lucide-arrow-left"
                  class="w-4 h-4 mr-2"
                />
                <span class="hidden sm:inline">Previous</span>
                <span class="sm:hidden">Back</span>
              </UButton>
              <div v-else />

              <div class="flex items-center gap-3">
                <!-- Next / Complete Button -->
                <UButton
                  v-if="currentStep < stepItems.length - 1"
                  color="primary"
                  size="lg"
                  :loading="submitting"
                  :disabled="!canProceed"
                  @click="nextStep"
                >
                  <span class="hidden sm:inline">{{ currentStep === 0 ? 'Get Started' : 'Continue' }}</span>
                  <span class="sm:hidden">{{ currentStep === 0 ? 'Start' : 'Next' }}</span>
                  <UIcon
                    name="i-lucide-arrow-right"
                    class="w-4 h-4 ml-2"
                  />
                </UButton>
                <UButton
                  v-else
                  color="primary"
                  size="lg"
                  :loading="submitting"
                  :disabled="!canProceed"
                  @click="completeOnboarding"
                >
                  <UIcon
                    name="i-lucide-check"
                    class="w-4 h-4 mr-2"
                  />
                  Complete Setup
                </UButton>
              </div>
            </div>
          </UCard>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { StepperItem } from '@nuxt/ui'
import type { User } from '~/types/user'
import OnboardingStepWelcome from '~/components/onboarding/StepWelcome.vue'
import OnboardingStepProfile from '~/components/onboarding/StepProfile.vue'
import OnboardingStepContact from '~/components/onboarding/StepContact.vue'
import OnboardingStepAddress from '~/components/onboarding/StepAddress.vue'

definePageMeta({
  layout: 'guest',
  middleware: '$auth'
})

const config = useRuntimeConfig()
const router = useRouter()
const toast = useToast()
const { user, refreshUser } = useSanctum()

// Refs for step components
const stepper = useTemplateRef('stepper')
const profileStep = ref()
const contactStep = ref()
const addressStep = ref()

// State
const loading = ref(true)
const submitting = ref(false)
const currentStep = ref(0)
const onboardingStatus = ref<any>(null)

// Step validity states
const profileValid = ref(false)
const contactValid = ref(false)
const addressValid = ref(false)
// Step data
const profileData = ref<Record<string, unknown>>({})
const contactData = ref<Record<string, unknown>>({})
const addressData = ref<Record<string, unknown>>({})
// Responsive check
const isMobile = ref(false)

const withTimeout = <T>(promise: Promise<T>, ms: number, label: string): Promise<T> => {
  let timeoutId: ReturnType<typeof setTimeout>
  const timeout = new Promise<T>((_, reject) => {
    timeoutId = setTimeout(() => reject(new Error(`${label} timeout`)), ms)
  })

  return Promise.race([promise, timeout]).finally(() => clearTimeout(timeoutId))
}

onMounted(async () => {
  // Check screen size
  isMobile.value = window.innerWidth < 768
  window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 768
  })

  // Load user data
  try {
    await withTimeout(refreshUser(), 15000, 'refreshUser')
    if ((typedUser.value as User | null)?.onboarded) {
      router.push('/dashboard')
      return
    }
  await withTimeout(fetchOnboardingStatus(), 15000, 'fetchOnboardingStatus')
  } catch {
    // User not loaded, redirect to login
    router.push('/auth/login')
    return
  } finally {
    loading.value = false
  }
})

// User computed properties
const typedUser = computed(() => user.value as User | null)
const userName = computed(() => typedUser.value?.name?.split(' ')[0] || 'there')
const userEmail = computed(() => typedUser.value?.email || null)
const userMobile = computed(() => typedUser.value?.mobile || null)
const emailVerifiedAt = computed(() => typedUser.value?.email_verified_at || null)
const mobileVerifiedAt = computed(() => typedUser.value?.mobile_verified_at || null)

// Determine signup method based on what's verified
const signupMethod = computed<'mobile' | 'email'>(() => {
  if (mobileVerifiedAt.value) return 'mobile'
  if (emailVerifiedAt.value) return 'email'
  return config.public.signupMode as 'mobile' | 'email' || 'mobile'
})

// Initial data for profile step
const profileInitialData = computed(() => ({
  name: typedUser.value?.name || '',
  dob: typedUser.value?.dob || '',
  gender: typedUser.value?.gender || '',
  bio: typedUser.value?.bio || ''
}))

// Stepper items configuration
const stepItems = computed<StepperItem[]>(() => [
  {
    slot: 'welcome',
    title: 'Welcome',
    description: 'Get started',
    icon: 'i-lucide-sparkles'
  },
  {
    slot: 'profile',
    title: 'Profile',
    description: 'Your details',
    icon: 'i-lucide-user'
  },
  {
    slot: 'contact',
    title: 'Contact',
    description: signupMethod.value === 'mobile' ? 'Add email' : 'Add mobile',
    icon: 'i-lucide-mail'
  },
  {
    slot: 'address',
    title: 'Address',
    description: 'Delivery location',
    icon: 'i-lucide-map-pin'
  }
])

// Current step component for mobile view
const currentStepComponent = computed(() => {
  switch (currentStep.value) {
    case 0: return OnboardingStepWelcome
    case 1: return OnboardingStepProfile
    case 2: return OnboardingStepContact
    case 3: return OnboardingStepAddress
    default: return null
  }
})

const currentStepProps = computed(() => {
  switch (currentStep.value) {
    case 0: return { userName: userName.value }
    case 1: return { initialData: profileInitialData.value }
    case 2: return {
      signupMethod: signupMethod.value,
      userEmail: userEmail.value,
      userMobile: userMobile.value,
      emailVerifiedAt: emailVerifiedAt.value,
      mobileVerifiedAt: mobileVerifiedAt.value
    }
    case 3: return {
      userName: userName.value,
      userPhone: userMobile.value,
      initialData: addressData.value
    }
    default: return {}
  }
})

const currentStepRef = computed(() => {
  switch (currentStep.value) {
    case 1: return 'profileStep'
    case 2: return 'contactStep'
    case 3: return 'addressStep'
    default: return undefined
  }
})

// Progress calculation
const progressPercent = computed(() => {
  if (onboardingStatus.value?.progress !== undefined) {
    return onboardingStatus.value.progress
  }
  return ((currentStep.value + 1) / stepItems.value.length) * 100
})

// Can proceed to next step
const canProceed = computed(() => {
  switch (currentStep.value) {
    case 0: return true // Welcome - always can proceed
    case 1: return profileValid.value
    case 2: return contactValid.value
    case 3: return addressValid.value
    default: return false
  }
})

// Can skip entire onboarding
const canSkip = computed(() => false)

// Data update handlers
const handleProfileUpdate = (data: Record<string, unknown>) => {
  profileData.value = data
}

const handleContactUpdate = (data: Record<string, unknown>) => {
  contactData.value = data
}

const handleCurrentStepDataUpdate = (data: Record<string, unknown>) => {
  switch (currentStep.value) {
    case 1: handleProfileUpdate(data); break
    case 2: handleContactUpdate(data); break
    case 3: addressData.value = data; break
  }
}

const handleCurrentStepValidUpdate = (valid: boolean) => {
  switch (currentStep.value) {
    case 1: profileValid.value = valid; break
    case 2: contactValid.value = valid; break
    case 3: addressValid.value = valid; break
  }
}

const handleContactVerified = async () => {
  // Refresh user to get updated verification status
  await refreshUser()
  await fetchOnboardingStatus()
}

// Navigation
const nextStep = async () => {
  if (!canProceed.value || submitting.value) return

  // Save data for current step before proceeding
  if (currentStep.value === 1) {
    await saveProfile()
  }

  if (currentStep.value < stepItems.value.length - 1) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const handleSkip = () => {
  // Skip to dashboard without completing onboarding
  router.push('/dashboard')
}

// API calls
const fetchOnboardingStatus = async () => {
  const response = await useSanctumFetch(`${config.public.apiBase}/api/onboarding/status`)
  onboardingStatus.value = response

  if (response?.steps) {
    profileValid.value = response.steps.profile?.complete ?? false
    contactValid.value = (response.steps.mobile?.complete ?? false) || (response.steps.email?.complete ?? false)
  }

  const nextStep = response?.next_step as string | null
  if (nextStep) {
    currentStep.value = mapStepToIndex(nextStep)
  }
}

const mapStepToIndex = (step: string): number => {
  switch (step) {
    case 'profile': return 1
    case 'address': return 3
    case 'mobile':
    case 'email':
      return 2
    case 'kyc':
    case 'avatar':
      return 2
    default: return 0
  }
}

const saveProfile = async () => {
  submitting.value = true
  try {
    const payload = { ...profileData.value }
    const avatarFile = (payload as { avatar?: File | null }).avatar || null
    delete (payload as { avatar?: File | null }).avatar

    await useSanctumFetch(`${config.public.apiBase}/api/onboarding/profile`, {
      method: 'PUT',
      body: payload
    })

    if (avatarFile) {
      const formData = new FormData()
      formData.append('avatar', avatarFile)

      await useSanctumFetch(`${config.public.apiBase}/api/user/avatar`, {
        method: 'POST',
        body: formData
      })
    }

    await refreshUser()
    await fetchOnboardingStatus()
  } catch (error: unknown) {
    const err = error as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Failed to save profile',
      color: 'error'
    })
    throw error
  } finally {
    submitting.value = false
  }
}

// Helper: Normalize phone to E.164 for India if needed
const normalizeMobile = (phone: string, countryCode = 'IN'): string => {
  if (!phone) return ''
  if (phone.startsWith('+')) return phone
  const digits = phone.replace(/\D/g, '')
  if (countryCode === 'IN' && digits.length === 10) {
    return `+91${digits}`
  }
  return digits ? `+${digits}` : ''
}

const fetchAddresses = async () => {
  try {
    const response = await useSanctumFetch<{ data: any[] }>(`${config.public.apiBase}/api/addresses`)
    return response.data || []
  } catch {
    return []
  }
}

const saveAddress = async () => {
  const payload = { ...addressData.value } as Record<string, unknown>
  const countryCode = (payload.country_code as string) || 'IN'
  payload.person_mobile = normalizeMobile(String(payload.person_mobile || ''), countryCode)

  await useSanctumFetch(`${config.public.apiBase}/api/addresses`, {
    method: 'POST',
    body: payload
  })
}

const completeOnboarding = async () => {
  if (!canProceed.value || submitting.value) return

  submitting.value = true
  try {
    const existingAddresses = await fetchAddresses()
    if (existingAddresses.length === 0) {
      await saveAddress()
    }

    await useSanctumFetch(`${config.public.apiBase}/api/onboarding/complete`, {
      method: 'POST'
    })

    await refreshUser()
    await fetchOnboardingStatus()

    toast.add({
      title: 'Welcome!',
      description: 'Your profile setup is complete. Enjoy your experience!',
      color: 'success'
    })

    // Redirect to dashboard
    setTimeout(() => {
      router.push('/dashboard')
    }, 1000)
  } catch (error: unknown) {
    const err = error as { data?: { message?: string, missing?: string[] } }
    if (err.data?.missing) {
      toast.add({
        title: 'Incomplete Setup',
        description: `Please complete: ${err.data.missing.join(', ')}`,
        color: 'warning'
      })
    } else {
      toast.add({
        title: 'Error',
        description: err.data?.message || 'Failed to complete onboarding',
        color: 'error'
      })
    }
  } finally {
    submitting.value = false
  }
}
</script>
