<template>
  <div class="min-h-screen flex flex-col items-center py-10 px-4">

    <h1 class="text-4xl font-semibold text-center my-4">Onboarding</h1>

    <div class="max-w-6xl w-full grid md:grid-cols-4 gap-6 bg-white dark:bg-gray-800 shadow-md rounded-lg">
      <!-- Wizard Steps Sidebar -->
      <WizardSteps :steps="steps" :currentStep="currentStep" />

      <!-- Wizard Body Step Form Card -->
      <div class="md:col-span-3">
        <div class="bg-white dark:bg-gray-800  rounded-lg p-8 transition-all ">
          <component
              :is="steps[currentStep].component"
              v-model="formDataStep"
              @next="handleNext"
              @back="handleBack"
              ref="stepComponentRef"
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
import { ref, shallowRef, computed, onMounted } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'
import WizardSteps from '~/components/onboarding/WizardSteps.vue'
import StepProfile from '~/components/onboarding/StepProfile.vue'
import StepAddress from '~/components/onboarding/StepAddress.vue'
import StepKyc from '~/components/onboarding/StepKyc.vue'
import StepFinish from '~/components/onboarding/StepFinish.vue'

definePageMeta({ layout: 'dashboard' })

const { user } = useSanctum()
const config = useRuntimeConfig()
const currentStep = ref(0)
const stepComponentRef = shallowRef(null)
const isSubmitting = ref(false)
const isSkipableWizard = true


const normalizeGender = (gender: string | null | undefined) =>
    (gender || '').trim().toLowerCase()

const formatDate = (dateStr: string | null | undefined) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return ''
  return date.toISOString().split('T')[0] // YYYY-MM-DD
}

// Nested formData structured by step matching backend
const formData = ref({
  profile: {
    name: user.value?.name || '',
    email: user.value?.email || '',
    mobile: user.value?.mobile || '',
    gender: normalizeGender(user.value?.gender),
    dob: formatDate(user.value?.dob),
  },
  address: {
    postal_code: '',
    address_1: '',
    landmark: '',
    village: '',
    city: '',
    state_code: '',
    block_id: '',
    country: {}
  },
  kyc: {
    aadhaar: '',
    pan: '',
    aadhaar_file: null as File | null,
    pan_file: null as File | null,
    aadhaarImageUrl: '', // for preview if image URL from API
    panImageUrl: ''
  },
  finish: {
    subscription_type: '',
    tnc: null
  }
})

// Wizard Steps
const steps = [
  { name: 'Profile', icon: 'mdi:account-outline', component: StepProfile },
  { name: 'Kyc', icon: 'mdi:tune', component: StepKyc },
  { name: 'Address', icon: 'mdi:map-marker-outline', component: StepAddress },
  { name: 'Finish', icon: 'mdi:check-circle-outline', component: StepFinish }
]

// Computed ref for current step data slice
const formDataStep = computed({
  get() {
    switch (currentStep.value) {
      case 0: return formData.value.profile
      case 1: return formData.value.address
      case 2: return formData.value.kyc
      case 3: return formData.value.finish
      default: return {}
    }
  },
  set(val) {
    switch (currentStep.value) {
      case 0: formData.value.profile = val; break
      case 1: formData.value.address = val; break
      case 2: formData.value.kyc = val; break
      case 3: formData.value.finish = val; break
    }
  }
})

function handleNext() {
  if(!isSkipableWizard)
  {
    if (!stepComponentRef.value) return
    if (typeof stepComponentRef.value.validate === 'function') {
      const valid = stepComponentRef.value.validate()
      if (!valid) return
    }
  }
  if (currentStep.value < steps.length - 1) currentStep.value++
}

function handleBack() {
  if (currentStep.value > 0) currentStep.value--
}

async function handleSubmit() {
  isSubmitting.value = true
  try {
    const form = new FormData()
    // Flatten formData nested structure for submission
    // You can adjust keys to match backend expectations (e.g. profile[name], address[city], etc.)
    for (const stepKey in formData.value) {
      const stepData = formData.value[stepKey]
      for (const key in stepData) {
        const value = stepData[key]
        if (value !== null && value !== '') {
          if ((key === 'aadhaar_file' || key === 'pan_file') && value instanceof File) {
            form.append(key, value)
          } else if (typeof value === 'object' && !(value instanceof File)) {
            form.append(`${stepKey}[${key}]`, JSON.stringify(value))
          } else {
            form.append(`${stepKey}[${key}]`, value)
          }
        }
      }
    }

    const response = await useSanctumFetch(`${config.public.apiBase}/user/onboarding`, {
      method: 'POST',
      body: form
    })

    if (response?.data?.status) {
      alert('Onboarding successful!')
      if (response.data.redirect) {
        await navigateTo(response.data.redirect_url)
      } else {
        await navigateTo('/dashboard')
      }
    } else {
      alert('Onboarding failed: ' + (response.data.error || 'Unknown error. Please try again.'))
    }
  } catch (error) {
    console.error('Onboarding submission failed:', error)
    alert('An unexpected error occurred. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

async function fetchUserProfile() {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/user/my-profile`, { method: 'GET' })
    if (response?.data?.data) {
      const user = response.data.data

      // Profile data
      formData.value.profile.name = user.name ?? ''
      formData.value.profile.email = user.email ?? ''
      formData.value.profile.mobile = user.mobile ?? ''
      formData.value.profile.gender = normalizeGender(user.gender ?? '')
      formData.value.profile.dob = formatDate(user.dob ?? '')

      // Address
      if (user.address) {
        formData.value.address.postal_code = user.address.postal_code ?? ''
        formData.value.address.address_1 = user.address.address_1 ?? ''
        formData.value.address.landmark = user.address.landmark ?? ''
        formData.value.address.village = user.address.village ?? ''
        formData.value.address.city = user.address.city ?? ''
        formData.value.address.state_code = user.address.state_code ?? ''
        formData.value.address.block_id = user.address.block_id ?? ''
        formData.value.address.country = user.address.country ?? {}
      }

      // KYC
      if (user.kyc) {
        formData.value.kyc.aadhaar = user.kyc.aadhaar ?? ''
        formData.value.kyc.pan = user.kyc.pan ?? ''
        // Set preview image URLs for existing images (to show and allow replace)
        formData.value.kyc.aadhaarImageUrl = user.kyc.aadhaarImage ?? ''
        formData.value.kyc.panImageUrl = user.kyc.panImage ?? ''
        // Note: Files can't be prefilled, only preview via URL
      }
    }
  } catch (err) {
    console.error('Failed to fetch user profile', err)
  }
}

onMounted(() => {
  fetchUserProfile()
})
</script>
