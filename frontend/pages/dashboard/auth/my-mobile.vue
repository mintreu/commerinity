<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 px-4 py-8">

    <!-- Card -->
    <div class="w-full max-w-7xl bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden">

      <!-- Header Section -->
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ hasMobile ? 'Verify Your Mobile Number 📱' : 'Add Your Mobile Number 📱' }}
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          <span v-if="!hasMobile">
            Please add your mobile number to secure your account and access all features.
          </span>
          <span v-else>
            Your mobile number is not verified. Please verify it to unlock the full system.
          </span>
        </p>
      </div>

      <!-- Info / Steps Section -->
      <div class="p-6 space-y-4">
        <div class="flex items-start space-x-3">
          <span class="bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-200 rounded-full p-2">
            1
          </span>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            {{ hasMobile ? 'Verify your existing number by entering the OTP.' : 'Enter your new mobile number in the form below.' }}
          </p>
        </div>
        <div class="flex items-start space-x-3">
          <span class="bg-green-100 dark:bg-green-800 text-green-600 dark:text-green-200 rounded-full p-2">
            2
          </span>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            We’ll send you a One-Time Password (OTP) to confirm it’s your number.
          </p>
        </div>
        <div class="flex items-start space-x-3">
          <span class="bg-yellow-100 dark:bg-yellow-800 text-yellow-600 dark:text-yellow-200 rounded-full p-2">
            3
          </span>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            Enter the OTP, verify, and save your mobile number.
          </p>
        </div>
      </div>

      <!-- Change Mobile Form -->
      <div class="p-6 bg-gray-50 dark:bg-gray-900">
        <ChangeMobile />
      </div>

      <!-- Footer / Help -->
      <div class="p-4 text-center border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
        Having trouble? <a href="/support" class="text-blue-600 dark:text-blue-400 hover:underline">Contact Support</a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import ChangeMobile from "~/components/account/ChangeMobile.vue"
import { useSanctum } from '#imports'

const router = useRouter()
const { user } = useSanctum<User>()

// ✅ Determine if user has mobile number or not
const hasMobile = computed(() => !!user.value?.mobile)
const isMobileVerified = computed(() => !!user.value?.mobile_verified)

// 🔹 Redirect to dashboard if mobile is already verified
watch([hasMobile, isMobileVerified], ([has, verified]) => {
  if (has && verified) {
    router.replace('/dashboard')
  }
}, { immediate: true })

definePageMeta({ layout: "dashboard" })
</script>
