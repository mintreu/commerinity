<!-- components/onboarding/PermissionWizard.vue -->
<template>
  <!-- Only render if should show -->
  <Teleport to="body">
    <transition name="wizard-fade">
      <div
          v-if="visible"
          class="fixed inset-0 z-[99999] bg-white dark:bg-gray-950
             md:bg-black/60 md:backdrop-blur-lg md:flex md:items-center md:justify-center"
          @click.self="handleSkip"
      >
        <!-- Desktop Modal Container -->
        <div class="h-full w-full md:h-auto md:w-full md:max-w-lg md:mx-4
                  bg-white dark:bg-gray-950 md:rounded-3xl md:shadow-premium
                  overflow-hidden relative flex flex-col">

          <!-- Progress Indicator -->
          <div class="absolute top-0 left-0 right-0 h-1.5 bg-gray-200 dark:bg-gray-800 z-10">
            <div
                class="h-full bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 transition-all duration-500 ease-out"
                :style="{ width: `${progress}%` }"
            />
          </div>

          <!-- Skip Button (Top Right) -->
          <button
              v-if="currentStep !== steps.length - 1"
              @click="handleSkip"
              class="absolute top-6 right-6 z-20 text-sm font-semibold text-gray-600 dark:text-gray-400
                 hover:text-gray-900 dark:hover:text-white transition-colors duration-200
                 px-4 py-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            Skip
          </button>

          <!-- Content Area -->
          <div class="flex-1 flex flex-col justify-between p-8 pt-20 pb-8 md:pt-16 max-h-screen overflow-y-auto">

            <!-- Step Content -->
            <transition :name="slideDirection" mode="out-in">
              <div :key="currentStep" class="flex-1 flex flex-col items-center justify-center text-center">

                <!-- Company Logo -->
                <div v-if="currentStep === 0" class="mb-8">
                  <NuxtLink to="/" class="inline-block">
                    <img
                        src="/logo.png"
                        alt="VVIndia Logo"
                        class="h-20 w-auto mx-auto"
                    />
                  </NuxtLink>
                </div>

                <!-- Permission Icon -->
                <div class="relative mb-8">
                  <div class="absolute inset-0 rounded-full animate-ping-slow"
                       :class="steps[currentStep].iconBg"
                       :style="{ opacity: 0.2 }" />
                  <div
                      class="relative w-24 h-24 md:w-28 md:h-28 rounded-full flex items-center justify-center shadow-2xl"
                      :class="steps[currentStep].iconBg"
                  >
                    <Icon :name="steps[currentStep].icon" class="w-12 h-12 md:w-14 md:h-14 text-white" />
                  </div>
                </div>

                <!-- Title -->
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mb-4 px-4">
                  {{ steps[currentStep].title }}
                </h2>

                <!-- Description -->
                <p class="text-base md:text-lg text-gray-600 dark:text-gray-400 mb-8 px-6 leading-relaxed max-w-md">
                  {{ steps[currentStep].description }}
                </p>

                <!-- Permission Level Options (if applicable) -->
                <div v-if="steps[currentStep].options" class="w-full max-w-md space-y-3 mb-8">
                  <button
                      v-for="option in steps[currentStep].options"
                      :key="option.value"
                      @click="handleOptionSelect(steps[currentStep].key, option.value)"
                      :class="[
                    'w-full px-6 py-4 rounded-2xl border-2 transition-all duration-300 text-left',
                    permissions[steps[currentStep].key] === option.value
                      ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                      : 'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700'
                  ]"
                  >
                    <div class="flex items-start gap-3">
                      <div class="mt-0.5">
                        <div
                            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                            :class="permissions[steps[currentStep].key] === option.value
                          ? 'border-blue-600 bg-blue-600'
                          : 'border-gray-300 dark:border-gray-600'"
                        >
                          <div v-if="permissions[steps[currentStep].key] === option.value"
                               class="w-2 h-2 bg-white rounded-full" />
                        </div>
                      </div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white mb-1">
                          {{ option.label }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                          {{ option.description }}
                        </div>
                      </div>
                    </div>
                  </button>
                </div>

                <!-- Feature List (for intro screen) -->
                <div v-if="currentStep === 0" class="w-full max-w-md space-y-4">
                  <div v-for="feature in welcomeFeatures" :key="feature.text"
                       class="flex items-center gap-3 text-left">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-500
                              flex items-center justify-center shrink-0">
                      <Icon name="mdi:check" class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">
                    {{ feature.text }}
                  </span>
                  </div>
                </div>

              </div>
            </transition>

            <!-- Action Buttons -->
            <div class="space-y-3 pt-8">

              <!-- Primary Action -->
              <button
                  @click="handleNext"
                  :disabled="isProcessing"
                  class="w-full px-8 py-5 bg-gradient-to-r from-blue-600 to-purple-600
                     hover:from-blue-700 hover:to-purple-700 text-white font-bold text-lg
                     rounded-2xl shadow-2xl hover:shadow-blue-500/30 transition-all duration-300
                     disabled:opacity-50 disabled:cursor-not-allowed active:scale-95
                     relative overflow-hidden group"
              >
              <span class="relative z-10 flex items-center justify-center gap-3">
                <Icon v-if="isProcessing" name="mdi:loading" class="w-6 h-6 animate-spin" />
                <span>{{ steps[currentStep].primaryAction }}</span>
                <Icon v-if="!isProcessing && currentStep !== steps.length - 1"
                      name="mdi:arrow-right"
                      class="w-6 h-6 transition-transform duration-300 group-hover:translate-x-1" />
              </span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0
                          translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700" />
              </button>

              <!-- Secondary Action -->
              <button
                  v-if="steps[currentStep].secondaryAction"
                  @click="handleSecondary"
                  class="w-full px-8 py-4 text-gray-600 dark:text-gray-400 font-semibold text-base
                     rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300"
              >
                {{ steps[currentStep].secondaryAction }}
              </button>

            </div>

          </div>

          <!-- Step Dots Indicator -->
          <div class="flex items-center justify-center gap-2 pb-6">
            <div
                v-for="(step, index) in steps"
                :key="index"
                class="transition-all duration-300 rounded-full"
                :class="[
              index === currentStep
                ? 'w-8 h-2 bg-gradient-to-r from-blue-600 to-purple-600'
                : 'w-2 h-2 bg-gray-300 dark:bg-gray-700'
            ]"
            />
          </div>

        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRuntimeConfig } from '#imports'
import { useRouter } from 'vue-router'
import { useCookie } from '#app'

// Configuration
const config = useRuntimeConfig()
const router = useRouter()
const toast = useToast()

// Props
const props = defineProps<{
  autoShow?: boolean
}>()

// State
const visible = ref(false)
const currentStep = ref(0)
const isProcessing = ref(false)
const slideDirection = ref('slide-left')

// Permissions State
const permissions = ref({
  cookies: 'essential',
  notifications: 'disabled',
  location: 'disabled'
})

// Cookie for storing permissions (1 year expiry)
const permissionsCookie = useCookie('user-permissions', {
  maxAge: 60 * 60 * 24 * 365, // 1 year
  sameSite: 'lax',
  secure: process.env.NODE_ENV === 'production'
})

const wizardCompletedCookie = useCookie('permission-wizard-completed', {
  maxAge: 60 * 60 * 24 * 365, // 1 year
  sameSite: 'lax',
  secure: process.env.NODE_ENV === 'production'
})

// Welcome Features
const welcomeFeatures = [
  { text: 'Secure Shopping Experience' },
  { text: 'Personalized Recommendations' },
  { text: 'Fast & Easy Checkout' },
  { text: 'Real-time Order Updates' }
]

// Wizard Steps Configuration
const steps = ref([
  {
    key: 'welcome',
    icon: 'mdi:hand-wave',
    iconBg: 'bg-gradient-to-br from-blue-600 to-cyan-600',
    title: 'Welcome to VVIndia! 👋',
    description: 'Let\'s personalize your experience in just a few quick steps.',
    primaryAction: 'Get Started',
    secondaryAction: null,
    options: null
  },
  {
    key: 'cookies',
    icon: 'mdi:cookie',
    iconBg: 'bg-gradient-to-br from-amber-600 to-orange-600',
    title: 'Cookie Preferences',
    description: 'Choose how we use cookies to enhance your browsing experience.',
    primaryAction: 'Continue',
    secondaryAction: null,
    options: [
      {
        value: 'all',
        label: 'Allow All Cookies',
        description: 'Get the best experience with personalized content and features'
      },
      {
        value: 'essential',
        label: 'Essential Only (Recommended)',
        description: 'Required cookies for shopping cart, checkout, and security'
      },
      {
        value: 'none',
        label: 'Reject All',
        description: 'Some features may not work properly'
      }
    ]
  },
  {
    key: 'notifications',
    icon: 'mdi:bell-ring',
    iconBg: 'bg-gradient-to-br from-green-600 to-emerald-600',
    title: 'Stay Updated',
    description: 'Get notified about order updates, special offers, and new arrivals.',
    primaryAction: 'Enable Notifications',
    secondaryAction: 'Not Now',
    options: null
  },
  {
    key: 'location',
    icon: 'mdi:map-marker',
    iconBg: 'bg-gradient-to-br from-red-600 to-pink-600',
    title: 'Location Access',
    description: 'Help us show nearby stores, faster delivery, and local offers.',
    primaryAction: 'Continue',
    secondaryAction: null,
    options: [
      {
        value: 'precise',
        label: 'Precise Location',
        description: 'Best delivery estimates and nearby store recommendations'
      },
      {
        value: 'approximate',
        label: 'Approximate Location',
        description: 'City-level location for general recommendations'
      },
      {
        value: 'disabled',
        label: 'Don\'t Allow',
        description: 'Manual entry required for delivery addresses'
      }
    ]
  },
  {
    key: 'complete',
    icon: 'mdi:check-circle',
    iconBg: 'bg-gradient-to-br from-purple-600 to-pink-600',
    title: 'All Set! 🎉',
    description: 'Your preferences have been saved. You can change them anytime in settings.',
    primaryAction: 'Go to Dashboard',
    secondaryAction: null,
    options: null
  }
])

// Computed
const progress = computed(() => ((currentStep.value + 1) / steps.value.length) * 100)

// Methods
const handleNext = async () => {
  const step = steps.value[currentStep.value]

  // Special handling for notification step
  if (step.key === 'notifications') {
    await enableNotifications()
    return
  }

  // Move to next step
  if (currentStep.value < steps.value.length - 1) {
    slideDirection.value = 'slide-left'
    currentStep.value++
  } else {
    // Complete wizard
    await completeWizard()
  }
}

const handleSecondary = () => {
  const step = steps.value[currentStep.value]

  if (step.key === 'notifications') {
    permissions.value.notifications = 'disabled'
    toast.info({
      title: 'Notifications',
      message: 'You can enable notifications later in settings',
      timeout: 2500
    })
    slideDirection.value = 'slide-left'
    currentStep.value++
  }
}

const handleSkip = () => {
  // Save as skipped
  wizardCompletedCookie.value = 'skipped'
  permissionsCookie.value = JSON.stringify(permissions.value)

  toast.warning({
    title: 'Permissions Skipped',
    message: 'You can configure permissions anytime from settings',
    timeout: 3000
  })

  visible.value = false
}

const handleOptionSelect = (key: string, value: string) => {
  permissions.value[key] = value

  // Show feedback
  if (key === 'cookies') {
    const labels = {
      all: 'All cookies enabled',
      essential: 'Essential cookies only',
      none: 'Cookies disabled'
    }
    toast.success({
      title: 'Cookie Preference',
      message: labels[value] || 'Updated',
      timeout: 1500
    })
  }
}

const enableNotifications = async () => {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    toast.error({
      title: 'Notifications',
      message: 'Push notifications not supported in this browser',
      timeout: 3000
    })
    setTimeout(() => {
      slideDirection.value = 'slide-left'
      currentStep.value++
    }, 2000)
    return
  }

  isProcessing.value = true

  try {
    const permission = await Notification.requestPermission()

    if (permission === 'granted') {
      permissions.value.notifications = 'enabled'

      toast.success({
        title: 'Notifications Enabled',
        message: 'You\'ll receive important updates',
        timeout: 2000
      })

      setTimeout(() => {
        slideDirection.value = 'slide-left'
        currentStep.value++
      }, 1500)
    } else {
      permissions.value.notifications = 'disabled'
      toast.warning({
        title: 'Notifications',
        message: 'Permission denied. You can enable later in settings.',
        timeout: 3000
      })

      setTimeout(() => {
        slideDirection.value = 'slide-left'
        currentStep.value++
      }, 2000)
    }
  } catch (error: any) {
    console.error('Notification error:', error)
    permissions.value.notifications = 'disabled'

    toast.error({
      title: 'Error',
      message: 'Failed to enable notifications',
      timeout: 2500
    })

    setTimeout(() => {
      slideDirection.value = 'slide-left'
      currentStep.value++
    }, 2000)
  } finally {
    isProcessing.value = false
  }
}

const completeWizard = async () => {
  isProcessing.value = true

  try {
    // Save to cookies
    permissionsCookie.value = JSON.stringify(permissions.value)
    wizardCompletedCookie.value = 'true'

    // Optionally save to backend
    try {
      await $fetch(`${config.public.apiBase}/user/permissions`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: {
          permissions: permissions.value
        }
      })
    } catch (apiError) {
      console.warn('Failed to sync permissions to backend:', apiError)
      // Continue anyway - permissions saved locally
    }

    toast.success({
      title: 'Setup Complete!',
      message: 'Welcome to VVIndia. Let\'s get started!',
      timeout: 2000
    })

    setTimeout(() => {
      visible.value = false
    }, 1000)
  } catch (error) {
    console.error('Failed to save permissions:', error)
    // Still save locally
    permissionsCookie.value = JSON.stringify(permissions.value)
    wizardCompletedCookie.value = 'true'

    visible.value = false
  } finally {
    isProcessing.value = false
  }
}

const checkShouldShow = (): boolean => {
  if (!process.client) return false

  // Check if wizard already completed
  if (wizardCompletedCookie.value === 'true' || wizardCompletedCookie.value === 'skipped') {
    return false
  }

  // Check if user just registered (from sessionStorage)
  const justRegistered = sessionStorage.getItem('justRegistered')
  if (justRegistered === 'true') {
    return true
  }

  // Check autoShow prop
  if (props.autoShow) {
    return true
  }

  return false
}

const loadSavedPermissions = () => {
  if (permissionsCookie.value) {
    try {
      const saved = JSON.parse(permissionsCookie.value as string)
      permissions.value = { ...permissions.value, ...saved }
    } catch (e) {
      console.warn('Failed to parse saved permissions')
    }
  }
}

// Public methods for parent component
const show = () => {
  visible.value = true
}

const hide = () => {
  visible.value = false
}

const reset = () => {
  currentStep.value = 0
  permissions.value = {
    cookies: 'essential',
    notifications: 'disabled',
    location: 'disabled'
  }
  wizardCompletedCookie.value = null
  permissionsCookie.value = null
}

// Lifecycle
onMounted(() => {
  if (!process.client) return

  loadSavedPermissions()

  // Check if should show wizard
  if (checkShouldShow()) {
    // Clear the registration flag
    sessionStorage.removeItem('justRegistered')

    // Show after brief delay
    setTimeout(() => {
      visible.value = true
    }, 500)
  }
})

// Expose for parent component
defineExpose({
  show,
  hide,
  reset
})
</script>

<style scoped>
/* Transitions */
.wizard-fade-enter-active,
.wizard-fade-leave-active {
  transition: opacity 0.3s ease;
}
.wizard-fade-enter-from,
.wizard-fade-leave-to {
  opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-left-enter-from {
  opacity: 0;
  transform: translateX(50px);
}
.slide-left-leave-to {
  opacity: 0;
  transform: translateX(-50px);
}

/* Custom Animations */
@keyframes ping-slow {
  0%, 100% {
    transform: scale(1);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.3;
  }
}

.animate-ping-slow {
  animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Shadow Utilities */
.shadow-premium {
  box-shadow:
      0 20px 50px -10px rgba(0, 0, 0, 0.25),
      0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .slide-left-enter-from {
    transform: translateY(30px);
  }
  .slide-left-leave-to {
    transform: translateY(-30px);
  }
}
</style>
