<template>
  <div class="step-welcome">
    <!-- Desktop View -->
    <div class="hidden md:block">
      <div class="text-center max-w-2xl mx-auto py-8">
        <!-- Animated Welcome Icon -->
        <div class="relative inline-block mb-8">
          <div class="w-24 h-24 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl flex items-center justify-center shadow-2xl animate-pulse">
            <UIcon name="i-lucide-sparkles" class="w-12 h-12 text-white" />
          </div>
          <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center shadow-lg">
            <UIcon name="i-lucide-check" class="w-4 h-4 text-white" />
          </div>
        </div>

        <!-- Welcome Message -->
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Welcome, {{ userName }}! 
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
          You're just a few steps away from unlocking the full potential of your account.
          Let's set up your profile to personalize your experience.
        </p>

        <!-- What We'll Set Up -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-6 mb-8">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Here's what we'll set up together:
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="step in onboardingSteps"
              :key="step.title"
              class="flex items-start gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl"
            >
              <div :class="[
                'flex items-center justify-center w-10 h-10 rounded-lg shrink-0',
                step.bgColor
              ]">
                <UIcon :name="step.icon" :class="['w-5 h-5', step.iconColor]" />
              </div>
              <div class="text-left">
                <h3 class="font-medium text-gray-900 dark:text-white">{{ step.title }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ step.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Estimated Time -->
        <div class="flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <UIcon name="i-lucide-clock" class="w-4 h-4" />
          <span>This usually takes about 2-3 minutes</span>
        </div>
      </div>
    </div>

    <!-- Mobile View - Minimal & Clean -->
    <div class="md:hidden">
      <div class="text-center py-6 px-2">
        <!-- Simple Icon -->
        <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
          <UIcon name="i-lucide-sparkles" class="w-10 h-10 text-white" />
        </div>

        <!-- Welcome Message - Shorter for mobile -->
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
          Welcome, {{ userName }}!
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Let's complete your profile setup in a few quick steps.
        </p>

        <!-- Compact Steps List -->
        <div class="space-y-3 text-left">
          <div
            v-for="(step, index) in onboardingSteps"
            :key="step.title"
            class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl"
          >
            <div class="flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full font-semibold text-sm">
              {{ index + 1 }}
            </div>
            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ step.title }}</span>
          </div>
        </div>

        <!-- Time Estimate -->
        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center gap-2">
          <UIcon name="i-lucide-clock" class="w-4 h-4" />
          About 2-3 minutes
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  userName?: string
}

const props = withDefaults(defineProps<Props>(), {
  userName: 'there'
})

const onboardingSteps = [
  {
    title: 'Your Profile',
    description: 'Basic info like name and date of birth',
    icon: 'i-lucide-user',
    bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'text-blue-600 dark:text-blue-400'
  },
  {
    title: 'Contact Details',
    description: 'Verify your email or phone number',
    icon: 'i-lucide-mail',
    bgColor: 'bg-green-100 dark:bg-green-900/30',
    iconColor: 'text-green-600 dark:text-green-400'
  },
  {
    title: 'Your Address',
    description: 'Add your primary delivery address',
    icon: 'i-lucide-map-pin',
    bgColor: 'bg-orange-100 dark:bg-orange-900/30',
    iconColor: 'text-orange-600 dark:text-orange-400'
  },
  {
    title: 'KYC Verification',
    description: 'Optional identity verification',
    icon: 'i-lucide-shield-check',
    bgColor: 'bg-purple-100 dark:bg-purple-900/30',
    iconColor: 'text-purple-600 dark:text-purple-400'
  }
]
</script>
