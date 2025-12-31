<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <!-- Loading State -->
    <div
      v-if="loading"
      class="min-h-screen flex items-center justify-center"
    >
      <div class="text-center">
        <UIcon name="i-lucide-loader-2" class="w-12 h-12 animate-spin text-primary-500 mx-auto mb-4" />
        <p class="text-gray-600 dark:text-gray-400">Loading your profile...</p>
      </div>
    </div>

    <!-- Onboarding Content -->
    <div v-else class="min-h-screen flex flex-col">
      <!-- Header with Logo (Mobile) -->
      <div class="md:hidden px-4 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
              <UIcon name="i-lucide-hexagon" class="w-5 h-5 text-white" />
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
        <div class="w-full max-w-4xl">
          <!-- Desktop Card -->
          <UCard
            class="shadow-xl"
            :ui="{
              root: 'overflow-visible',
              body: 'p-6 md:p-8'
            }"
          >
            <!-- Header -->
            <template #header>
              <div class="hidden md:flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                    <UIcon name="i-lucide-hexagon" class="w-6 h-6 text-white" />
                  </div>
                  <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Profile Setup</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Complete your account setup</p>
                  </div>
                </div>
                <UBadge color="primary" variant="subtle" size="lg">
                  {{ Math.round(progressPercent) }}% Complete
                </UBadge>
              </div>
            </template>

            <!-- Stepper -->
            <UStepper
              ref="stepper"
              v-model="currentStep"
              :items="stepItems"
              :disabled="true"
              :linear="true"
              color="primary"
              :orientation="isMobile ? 'vertical' : 'horizontal'"
              :ui="{
                root: isMobile ? 'hidden' : 'mb-8'
              }"
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
                  @update:data="handleAddressUpdate"
                  @valid="addressValid = $event"
                />
              </template>

              <!-- KYC Step -->
              <template #kyc>
                <OnboardingStepKyc
                  ref="kycStep"
                  @update:data="handleKycUpdate"
                  @valid="kycValid = $event"
                  @skip="handleKycSkip"
                />
              </template>
            </UStepper>

            <!-- Mobile: Show current step content -->
            <div class="md:hidden">
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
                <UIcon name="i-lucide-arrow-left" class="w-4 h-4 mr-2" />
                <span class="hidden sm:inline">Previous</span>
                <span class="sm:hidden">Back</span>
              </UButton>
              <div v-else />

              <div class="flex items-center gap-3">
                <!-- Skip Button (KYC only) -->
                <UButton
                  v-if="currentStep === 4 && !kycData?.skipped"
                  variant="ghost"
                  color="neutral"
                  size="lg"
                  :disabled="submitting"
                  @click="handleKycSkip"
                >
                  Skip KYC
                </UButton>

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
                  <UIcon name="i-lucide-arrow-right" class="w-4 h-4 ml-2" />
                </UButton>
                <UButton
                  v-else
                  color="primary"
                  size="lg"
                  :loading="submitting"
                  :disabled="!canProceed"
                  @click="completeOnboarding"
                >
                  <UIcon name="i-lucide-check" class="w-4 h-4 mr-2" />
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
import OnboardingStepWelcome from '~/components/onboarding/OnboardingStepWelcome.vue'
import OnboardingStepProfile from '~/components/onboarding/OnboardingStepProfile.vue'
import OnboardingStepContact from '~/components/onboarding/OnboardingStepContact.vue'
import OnboardingStepAddress from '~/components/onboarding/OnboardingStepAddress.vue'
import OnboardingStepKyc from '~/components/onboarding/OnboardingStepKyc.vue'

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
const kycStep = ref()

// State
const loading = ref(true)
const submitting = ref(false)
const currentStep = ref(0)

// Step validity states
const profileValid = ref(false)
const contactValid = ref(false)
const addressValid = ref(false)
const kycValid = ref(true) // KYC is optional, default true

// Step data
const profileData = ref<Record<string, unknown>>({})
const contactData = ref<Record<string, unknown>>({})
const addressData = ref<Record<string, unknown>>({})
const kycData = ref<Record<string, unknown>>({})

// Responsive check
const isMobile = ref(false)

onMounted(async () => {
  // Check screen size
  isMobile.value = window.innerWidth < 768
  window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 768
  })

  // Load user data
  try {
    await refreshUser()
  } catch {
    // User not loaded, redirect to login
    router.push('/auth/login')
    return
  }

  loading.value = false
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
    description: 'Delivery address',
    icon: 'i-lucide-map-pin'
  },
  {
    slot: 'kyc',
    title: 'KYC',
    description: 'Optional',
    icon: 'i-lucide-shield-check'
  }
])

// Current step component for mobile view
const currentStepComponent = computed(() => {
  switch (currentStep.value) {
    case 0: return OnboardingStepWelcome
    case 1: return OnboardingStepProfile
    case 2: return OnboardingStepContact
    case 3: return OnboardingStepAddress
    case 4: return OnboardingStepKyc
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
    case 3: return { userName: typedUser.value?.name, userPhone: userMobile.value }
    case 4: return {}
    default: return {}
  }
})

const currentStepRef = computed(() => {
  switch (currentStep.value) {
    case 1: return 'profileStep'
    case 2: return 'contactStep'
    case 3: return 'addressStep'
    case 4: return 'kycStep'
    default: return undefined
  }
})

// Progress calculation
const progressPercent = computed(() => {
  return ((currentStep.value + 1) / stepItems.value.length) * 100
})

// Can proceed to next step
const canProceed = computed(() => {
  switch (currentStep.value) {
    case 0: return true // Welcome - always can proceed
    case 1: return profileValid.value
    case 2: return contactValid.value
    case 3: return addressValid.value
    case 4: return kycValid.value
    default: return false
  }
})

// Can skip entire onboarding
const canSkip = computed(() => currentStep.value === 0)

// Data update handlers
const handleProfileUpdate = (data: Record<string, unknown>) => {
  profileData.value = data
}

const handleContactUpdate = (data: Record<string, unknown>) => {
  contactData.value = data
}

const handleAddressUpdate = (data: Record<string, unknown>) => {
  addressData.value = data
}

const handleKycUpdate = (data: Record<string, unknown>) => {
  kycData.value = data
}

const handleCurrentStepDataUpdate = (data: Record<string, unknown>) => {
  switch (currentStep.value) {
    case 1: handleProfileUpdate(data); break
    case 2: handleContactUpdate(data); break
    case 3: handleAddressUpdate(data); break
    case 4: handleKycUpdate(data); break
  }
}

const handleCurrentStepValidUpdate = (valid: boolean) => {
  switch (currentStep.value) {
    case 1: profileValid.value = valid; break
    case 2: contactValid.value = valid; break
    case 3: addressValid.value = valid; break
    case 4: kycValid.value = valid; break
  }
}

const handleContactVerified = () => {
  // Refresh user to get updated verification status
  refreshUser()
}

const handleKycSkip = () => {
  kycData.value = { skipped: true }
  kycValid.value = true
}

// Navigation
const nextStep = async () => {
  if (!canProceed.value || submitting.value) return

  // Save data for current step before proceeding
  if (currentStep.value === 1) {
    await saveProfile()
  } else if (currentStep.value === 3) {
    await saveAddress()
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
const saveProfile = async () => {
  submitting.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/api/onboarding/profile`, {
      method: 'PUT',
      body: profileData.value
    })
    await refreshUser()
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

// Helper: Format phone to E.164 format
const formatPhoneNumber = (phone: string): string => {
  if (!phone) return ''
  let formatted = phone.replace(/[\s-]/g, '')
  if (formatted && !formatted.startsWith('+')) {
    formatted = '+91' + formatted.replace(/^0+/, '')
  }
  return formatted
}

const saveAddress = async () => {
  submitting.value = true
  try {
    // Transform frontend form data to backend API format
    const data = addressData.value as Record<string, unknown>
    const payload = {
      type: (data.label || data.type || 'home') as string,
      person_name: (data.name || data.person_name || '') as string,
      person_mobile: formatPhoneNumber((data.phone || data.person_mobile || '') as string),
      address_1: (data.address_line_1 || data.address_1 || '') as string,
      address_2: (data.address_line_2 || data.address_2 || '') as string,
      city: (data.city || '') as string,
      postal_code: (data.postal_code || '') as string,
      state_code: (data.state || data.state_code || '') as string,
      country_code: (data.country || data.country_code || 'IN') as string,
      default: true
    }

    await useSanctumFetch(`${config.public.apiBase}/api/addresses`, {
      method: 'POST',
      body: payload
    })
  } catch (error: unknown) {
    const err = error as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Failed to save address',
      color: 'error'
    })
    throw error
  } finally {
    submitting.value = false
  }
}

const completeOnboarding = async () => {
  if (!canProceed.value || submitting.value) return

  submitting.value = true
  try {
    // Submit KYC if not skipped
    if (!kycData.value?.skipped && kycData.value?.document_type) {
      const formData = new FormData()
      formData.append('document_type', kycData.value.document_type as string)
      formData.append('document_number', kycData.value.document_number as string)
      if (kycData.value.front_file) {
        formData.append('front_image', kycData.value.front_file as File)
      }
      if (kycData.value.back_file) {
        formData.append('back_image', kycData.value.back_file as File)
      }

      await useSanctumFetch(`${config.public.apiBase}/api/kyc/submit`, {
        method: 'POST',
        body: formData
      })
    }

    // Complete onboarding
    await useSanctumFetch(`${config.public.apiBase}/api/onboarding/complete`, {
      method: 'POST'
    })

    await refreshUser()

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
    const err = error as { data?: { message?: string; missing?: string[] } }
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
