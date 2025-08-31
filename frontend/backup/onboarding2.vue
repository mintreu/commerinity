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

definePageMeta({
  layout: 'dashboard'
})

const { user } = useSanctum()
const config = useRuntimeConfig()
const currentStep = ref(0)
const stepComponentRef = shallowRef(null)
const isSubmitting = ref(false)

const normalizeGender = (gender: string | null | undefined) =>
    (gender || '').trim().toLowerCase()

const formatDate = (dateStr: string | null | undefined) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return ''
  return date.toISOString().split('T')[0] // YYYY-MM-DD
}

// Full form data
const formData = ref({
  // Step 1 - Profile
  name: user.value?.name || '',
  email: user.value?.email || '',
  mobile: user.value?.mobile || '',
  gender: normalizeGender(user.value?.gender),
  dob: formatDate(user.value?.dob),

  // Step 2 - Address
  postal_code: '', // 6-digit pin code
  address_1: '', // street address
  landmark: '',
  village: '',
  city: '', // district / city (select)
  state_code: '', // state (select)
  block_id: '', // block / municipality (select)
  country: {}, // country info (auto-filled, but mostly readonly)

  // Step 3 - KYC
  aadhaar: '',
  pan: '',
  aadhaar_file: null,
  pan_file: null,

  tnc:null,
  subscription_type:'',


})

// Wizard Steps
const steps = [
  { name: 'Profile', icon: 'mdi:account-outline', component: StepProfile },
  { name: 'Address', icon: 'mdi:map-marker-outline', component: StepAddress },
  { name: 'Kyc', icon: 'mdi:tune', component: StepKyc },
  { name: 'Finish', icon: 'mdi:check-circle-outline', component: StepFinish }
]

// Computed ref for current step form data slice and sync back
const formDataStep = computed({
  get() {
    switch (currentStep.value) {
      case 0:
        return {
          name: formData.value.name,
          email: formData.value.email,
          mobile: formData.value.mobile,
          gender: formData.value.gender,
          dob: formData.value.dob
        }
      case 1:
        return {
          postal_code: formData.value.postal_code,
          address_1: formData.value.address_1,
          landmark: formData.value.landmark,
          village: formData.value.village,
          city: formData.value.city,
          state_code: formData.value.state_code,
          block_id: formData.value.block_id,
          country: formData.value.country
        }
      case 2:
        return {
          aadhaar: formData.value.aadhaar,
          pan: formData.value.pan,
          aadhaar_file: formData.value.aadhaar_file,
          pan_file: formData.value.pan_file
        }
      case 3: // Add this block for Finish step
        return {
          subscription_type: formData.value.subscription_type,
          tnc: formData.value.tnc
        }
      default:
        return {}
    }
  },
  set(val) {
    switch (currentStep.value) {
      case 0:
        formData.value.name = val.name
        formData.value.email = val.email
        formData.value.mobile = val.mobile
        formData.value.gender = val.gender
        formData.value.dob = val.dob
        break
      case 1:
        formData.value.postal_code = val.postal_code
        formData.value.address_1 = val.address_1
        formData.value.landmark = val.landmark
        formData.value.village = val.village
        formData.value.city = val.city
        formData.value.state_code = val.state_code
        formData.value.block_id = val.block_id
        formData.value.country = val.country
        break
      case 2:
        formData.value.aadhaar = val.aadhaar
        formData.value.pan = val.pan
        formData.value.aadhaar_file = val.aadhaar_file
        formData.value.pan_file = val.pan_file
        break
      case 3: // Add this for Finish step
        formData.value.subscription_type = val.subscription_type
        formData.value.tnc = val.tnc
        break
    }
  }
})


// Step navigation handlers
function handleNext() {
  if (!stepComponentRef.value) return

  if (typeof stepComponentRef.value.validate === 'function') {
    const valid = stepComponentRef.value.validate()
    if (!valid) return
  }

  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  }
}

function handleBack() {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

// Final submit handler
// Final submit handler
async function handleSubmit() {
  isSubmitting.value = true
  try {
    const form = new FormData()
    for (const key in formData.value) {
      const value = formData.value[key]
      if (value !== null && value !== '') {
        if (key === 'aadhaar_file' || key === 'pan_file') {
          if (value instanceof File) {
            form.append(key, value)
          }
        } else if (typeof value === 'object' && !(value instanceof File)) {
          form.append(key, JSON.stringify(value))
        } else {
          form.append(key, value)
        }
      }
    }

    const response = await useSanctumFetch(`${config.public.apiBase}/user/onboarding`, {
      method: 'POST',
      body: form
    })

    if (response?.data?.status) {
      // Success notification (replace alert with your toast)
      alert('Onboarding successful!')

      // Redirect based on backend instruction
      if (response.data.redirect) {
        await navigateTo(response.data.redirect_url)
      } else {
        await navigateTo('/dashboard')
      }
    } else {
      // Failure notification
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
    const response = await useSanctumFetch(`${config.public.apiBase}/user/my-profile`, {
      method: 'GET'
    })

    if (response?.data?.data) {
      const user = response.data.data

      // Update formData values from API response safely
      formData.value.name = user.name ?? ''
      formData.value.email = user.email ?? ''
      formData.value.mobile = user.mobile ?? ''
      formData.value.gender = normalizeGender(user.gender ?? '')
      formData.value.dob = formatDate(user.dob ?? '')

      if (user.address) {
        formData.value.postal_code = user.address.postal_code ?? ''
        formData.value.address_1 = user.address.address_1 ?? ''
        formData.value.landmark = user.address.landmark ?? ''
        formData.value.village = user.address.village ?? ''
        formData.value.city = user.address.city ?? ''
        formData.value.state_code = user.address.state_code ?? ''
        formData.value.block_id = user.address.block_id ?? ''
        formData.value.country = user.address.country ?? {}
      }

      if (user.kyc) {
        formData.value.aadhaar = user.kyc.aadhaar ?? ''
        formData.value.pan = user.kyc.pan ?? ''
        // Files can’t be prefilled
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
