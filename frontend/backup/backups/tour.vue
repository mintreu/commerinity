<template>
  <div class="p-6 max-w-3xl mx-auto mt-14">
    <h1 class="text-2xl font-bold mb-6">Complete Your Onboarding</h1>

    <div class="space-y-4">
      <div
          v-for="(step, index) in tourSteps"
          :key="index"
          :class="[
          'p-4 border rounded-lg flex justify-between items-center transition',
          step.completed ? 'bg-gray-100 cursor-not-allowed opacity-70' : '',
          isNextStep(index) ? 'ring-2 ring-blue-400' : ''
        ]"
      >
        <div>
          <h3 class="font-semibold text-lg flex items-center gap-2">
            {{ step.label }}
            <span v-if="step.completed" class="text-green-600"><Icon name="solar:verified-check-broken" /></span>
          </h3>
          <p class="text-gray-600">{{ step.description }}</p>
          <p class="text-sm text-gray-400">Step {{ index + 1 }} of {{ tourSteps.length }}</p>
        </div>

        <div class="flex items-center gap-2">
          <NuxtLink
              v-if="!step.completed"
              :to="step.to"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
              :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': !isNextStep(index) }"
          >
            Go
          </NuxtLink>

          <span v-else class="text-gray-500 text-sm italic">Completed</span>
        </div>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="mt-6">
      <div class="w-full bg-gray-200 rounded h-2">
        <div
            class="bg-green-600 h-2 rounded"
            :style="{ width: ((completedSteps / tourSteps.length) * 100) + '%' }"
        ></div>
      </div>
      <p class="text-sm text-gray-500 mt-1">
        {{ completedSteps }} of {{ tourSteps.length }} steps completed
      </p>
    </div>

    <!-- Submit button -->
    <div v-if="completedSteps === tourSteps.length" class="mt-6 text-center">
      <button
          @click="submitOnboarding"
          class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 transition"
      >
        Complete Onboarding
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig, useToast } from '#imports'

const { user } = useSanctum()
const toast = useToast()
const config = useRuntimeConfig()
const profileData = ref<any>(null)
definePageMeta({ layout: 'dashboard' })
async function loadProfile() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/profile`)
    profileData.value = res.data
  } catch (err: any) {
    toast.error({ title: 'Error', message: 'Failed to load profile' })
  }
}

loadProfile()

const tourLinks = [
  { to: '/dashboard/account', label: 'Profile', description: 'Update your personal info' },
  { to: '/dashboard/account/address', label: 'Address', description: 'Update your address' },
  { to: '/dashboard/account/kyc', label: 'KYC', description: 'Upload Aadhaar & PAN' },
  { to: '/dashboard/subscribe-now', label: 'Subscription', description: 'Choose your plan' },
]

// Step completion functions
function isProfileComplete(profile: any): boolean {
  return Boolean(profile?.name?.trim()) &&
      Boolean(profile?.dob?.trim()) &&
      Boolean(profile?.gender?.trim())
}

function isAddressComplete(profile: any): boolean {
  return profile?.address?.type === 'HOME'
}

function isKycComplete(profile: any): boolean {
  return Boolean(profile?.kyc)
}

function isSubscriptionComplete(profile: any): boolean {
  return Boolean(profile?.hasLevel) || Boolean(profile?.level_id)
}

// Compute tour steps dynamically and reactively
const tourSteps = computed(() => {
  if (!profileData.value) return tourLinks.map(step => ({ ...step, completed: false }))

  const profile = profileData.value

  return tourLinks.map(step => {
    let completed = false
    switch (step.label) {
      case 'Profile': completed = isProfileComplete(profile); break
      case 'Address': completed = isAddressComplete(profile); break
      case 'KYC': completed = isKycComplete(profile); break
      case 'Subscription': completed = isSubscriptionComplete(profile); break
    }
    return { ...step, completed }
  })
})

// Next incomplete step for highlight & enabling Go
function isNextStep(index: number): boolean {
  const firstIncompleteIndex = tourSteps.value.findIndex(s => !s.completed)
  return index === firstIncompleteIndex
}

// Count completed steps
const completedSteps = computed(() => tourSteps.value.filter(s => s.completed).length)


// Submit onboarding
async function submitOnboarding() {
  try {
    await useSanctumFetch(`${config.public.apiBase}/account/onboarding`, { method: 'POST' })
    toast.success({ title: 'Success!', message: 'Onboarding completed successfully!' })
    await loadProfile()
  } catch (err: any) {
    console.error(err)
    toast.error({ title: 'Error', message: 'Failed to submit onboarding.' })
  }
}
</script>

<style scoped>
/* Hover & highlight ring for next step */
.ring-blue-400 {
  box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.5);
}
</style>
