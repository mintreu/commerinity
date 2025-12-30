<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 py-8 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <NuxtLink
          to="/profile"
          class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
          <UIcon name="i-lucide-arrow-left" class="w-4 h-4" />
          <span>Back to Profile</span>
        </NuxtLink>
      </div>

      <!-- Page Title -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold gradient-text-primary mb-2">
          Change Email Address
        </h1>
        <p class="text-slate-600 dark:text-slate-400 text-lg">
          Verify your new email address with OTP for secure access to your account.
        </p>
      </div>

      <!-- OTP Component -->
      <ProfileChangeEmailWithOtp @success="handleSuccess" />
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  title: 'Change Email',
  layout: 'dashboard'
})

const router = useRouter()

const handleSuccess = () => {
  // Navigate back to profile after successful email change
  setTimeout(() => {
    router.push('/profile')
  }, 1000)
}
</script>
