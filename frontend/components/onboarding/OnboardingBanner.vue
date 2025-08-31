<template>
  <div
      v-if="showBanner"
      role="banner"
      class="relative w-full bg-yellow-100 dark:bg-gray-900 rounded-lg shadow-lg p-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
  >
    <!-- Left: Current step + progress -->
    <div class="flex-1 flex flex-col gap-2">
      <div class="flex items-center gap-3">
        <Icon
            :name="currentStep.completed ? 'mdi:check-circle' : currentStep.icon ?? 'mdi:circle-outline'"
            class="w-6 h-6 text-green-500 dark:text-green-400"
        />
        <span class="font-medium text-gray-800 dark:text-yellow-200">
          {{ currentStep.label }}
        </span>
      </div>

      <!-- Progress bar + step count -->
      <div class="flex items-center gap-2 mt-1">
        <div class="flex-1 bg-gray-300 dark:bg-gray-700 rounded h-2 overflow-hidden">
          <div
              class="bg-green-500 h-2 rounded transition-all duration-500"
              :style="{ width: progress + '%' }"
          ></div>
        </div>
        <span class="text-xs text-gray-600 dark:text-gray-400">
          {{ completedSteps }}/{{ totalSteps }} steps completed
        </span>
      </div>
    </div>

    <!-- Right: CTA -->
    <div class="flex flex-col gap-2 items-end md:items-center">
      <NuxtLink
          v-if="nextStep"
          :to="nextStep.path"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-1 transition-transform duration-200 hover:scale-105"
      >
        <Icon name="mdi:arrow-right" class="w-4 h-4" /> Complete Step
      </NuxtLink>

      <NuxtLink
          v-else
          to="/terms"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-1 transition-transform duration-200 hover:scale-105"
      >
        <Icon name="mdi:star" class="w-4 h-4" /> Finish Onboarding & Accept Terms
      </NuxtLink>
    </div>

    <!-- Close permanently -->
    <button
        v-if="!nextStep"
        @click="closeBanner"
        class="absolute top-2 right-2 text-gray-500 dark:text-gray-300 hover:text-gray-800 dark:hover:text-yellow-400 transition-all duration-300"
    >
      <Icon name="mdi:close" class="w-5 h-5" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'
import { Icon } from '#components'

const config = useRuntimeConfig()
const profileData = ref<any>(null)
const bannerClosed = ref(false)

// Add a unique key for each step for proper validation
const tourSteps = ref([
  {
    key: 'profile',
    label: 'Complete your profile details (name, DOB, gender)',
    path: '/dashboard/account/edit',
    completed: false,
    icon: 'mdi-account'
  },
  {
    key: 'address',
    label: 'Add your home address for deliveries',
    path: '/dashboard/account/address',
    completed: false,
    icon: 'mdi-home'
  },
  {
    key: 'kyc',
    label: 'Submit your KYC documents for verification',
    path: '/dashboard/account/kyc',
    completed: false,
    icon: 'mdi-file-document'
  },
  {
    key: 'subscription',
    label: 'Choose a subscription plan to unlock features',
    path: '/dashboard/subscribe',
    completed: false,
    icon: 'mdi-currency-usd'
  },
])

async function loadProfile() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/profile`)
    profileData.value = res.data ?? res

    tourSteps.value = tourSteps.value.map(step => {
      let completed = false
      switch (step.key) {
        case 'profile':
          completed =
              Boolean(profileData.value?.name) &&
              Boolean(profileData.value?.dob) &&
              Boolean(profileData.value?.gender)
          break
        case 'address':
          completed = profileData.value?.address?.type === 'HOME'
          break
        case 'kyc':
          completed = Boolean(profileData.value?.kyc)
          break
        case 'subscription':
          completed =
              Boolean(profileData.value?.hasLevel) || Boolean(profileData.value?.level_id)
          break
      }
      return { ...step, completed }
    })
  } catch (err) {
    console.error('Failed to load profile', err)
  }
}

onMounted(loadProfile)

// Reactive computed properties
const completedSteps = computed(() => tourSteps.value.filter(s => s.completed).length)
const totalSteps = computed(() => tourSteps.value.length)
const nextStep = computed(() => tourSteps.value.find(s => !s.completed))
const currentStep = computed(() => nextStep.value ?? tourSteps.value[tourSteps.value.length-1])
const progress = computed(() => (completedSteps.value / totalSteps.value) * 100)
const showBanner = computed(() => !bannerClosed.value && completedSteps.value < totalSteps.value)

function closeBanner() {
  bannerClosed.value = true
}
</script>

<style scoped>
div[role='banner'] {
  transition: all 0.3s ease;
}
</style>
