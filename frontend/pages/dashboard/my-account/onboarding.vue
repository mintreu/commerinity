<template>
  <div class="min-h-screen flex flex-col items-center py-10 px-4">

    <h1 class="text-4xl font-semibold text-center my-4">Onboarding</h1>

    <div class="max-w-6xl w-full grid md:grid-cols-4 gap-6 bg-white dark:bg-gray-800 shadow-md rounded-lg">
      <!-- Wizard Steps Sidebar -->
      <WizardSteps :steps="steps" :currentStep="currentStep" />

      <!-- Wizard Body Step Form Card -->
      <div class="md:col-span-3">
        <div class="bg-white dark:bg-gray-800 shadow-lg  rounded-lg p-8 transition-all ">
          <component
              :is="steps[currentStep].component"
              v-model="formData"
              @next="handleNext"
              @back="handleBack"
          />
        </div>

        <!-- Final Submit Button (only on last step) -->
        <div v-if="currentStep === steps.length - 1" class="text-right mt-6">
          <button
              @click="handleSubmit"
              :disabled="isSubmitting"
              class="px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Submitting...' : 'Finish & Submit' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig, useCookie } from '#imports'
import WizardSteps from '~/components/onboarding/WizardSteps.vue'
import StepProfile from '~/components/onboarding/StepProfile.vue'
import StepAddress from '~/components/onboarding/StepAddress.vue'
import StepKyc from '~/components/onboarding/StepKyc.vue'
import StepFinish from '~/components/onboarding/StepFinish.vue'

definePageMeta({
  layout: 'dashboard'
})

const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()
const { user } = useSanctum()
const currentStep = ref(0)
const isSubmitting = ref(false)

const normalizeGender = (gender: string | null | undefined) =>
    (gender || '').trim().toLowerCase()

const formatDate = (dateStr: string | null | undefined) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return ''
  return date.toISOString().split('T')[0] // YYYY-MM-DD
}


// ✅ Full form data from all steps
const formData = ref({
  // Step 1 - Profile
  name: user.value?.name || '',
  email: user.value?.email || '',
  mobile: user.value?.mobile || '',
  gender: normalizeGender(user.value?.gender),
  dob: formatDate(user.value?.dob),

  // Step 2 - Address
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  country: '',
  pin: '',

  // Step 3 - KYC
  aadhaar: '',
  pan: '',
  aadhaar_file: null,
  pan_file: null
})



// Wizard Steps
const steps = [
  { name: 'Profile', icon: 'mdi:account-outline', component: StepProfile },
  { name: 'Address', icon: 'mdi:map-marker-outline', component: StepAddress },
  { name: 'Kyc', icon: 'mdi:tune', component: StepKyc },
  { name: 'Finish', icon: 'mdi:check-circle-outline', component: StepFinish }
]

// Step navigation
function handleNext() {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  }
}

function handleBack() {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

// ✅ Final Submit Handler

// ✅ Final Submit
async function handleSubmit() {
  isSubmitting.value = true
  try {
    const form = new FormData()
    for (const key in formData.value) {
      const value = formData.value[key]
      if (value !== null && value !== '') {
        form.append(key, value)
      }
    }

    const response = await useSanctumFetch(`${config.public.apiBase}/user/onboarding`, {
      method: 'POST',
      body: form
    })

    if (response?.data?.status) {
      navigateTo('/dashboard')
    } else {
      // Optional: Show toast for failure
      console.warn('Onboarding failed. Please try again.')
    }
  } catch (error) {
    console.error('Onboarding submission failed:', error)
    // Optional: Show toast or modal
  } finally {
    isSubmitting.value = false
  }
}



</script>
