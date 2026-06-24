<template>
  <div class="min-h-screen w-full flex relative overflow-hidden">
    <!-- Left Side: Platform Features (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative z-10 flex-col justify-center p-12">
      <!-- Background gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600/6 via-indigo-600/4 to-purple-600/6 backdrop-blur-sm" />

      <div class="relative z-10 max-w-lg">
        <!-- Brand Logo & Name -->
        <NuxtLink
          to="/"
          class="flex items-center gap-4 mb-12"
        >
          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl transform transition-transform hover:scale-105 duration-300">
            <UIcon
              name="i-lucide-hexagon"
              class="w-8 h-8 text-white"
            />
          </div>
          <div>
            <h1 class="gradient-text-primary text-3xl font-bold">
              {{ config.public.appName }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 font-medium">Your Shopping Destination</p>
          </div>
        </NuxtLink>

        <!-- Main Heading -->
        <h2 class="text-5xl xl:text-6xl font-bold mb-8 leading-tight">
          <span class="gradient-text-primary">
            Reset Your
          </span>
          <br>
          <span class="gradient-text-accent">
            Password
          </span>
        </h2>

        <!-- Description -->
        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 leading-relaxed">
          Don't worry! It happens to the best of us. Enter your email address and we'll send you a link to reset your password.
        </p>

        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-shield-check"
              class="w-4 h-4"
            />
            <span>Secure Process</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-mail"
              class="w-4 h-4"
            />
            <span>Email Verification</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-clock"
              class="w-4 h-4"
            />
            <span>Quick Reset</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Forgot Password Form -->
    <div class="w-full lg:w-1/2 xl:w-2/5 relative z-10 flex items-center justify-center p-4 lg:p-8">
      <div class="w-full max-w-md">
        <!-- Forgot Password Card -->
        <div class="glass-card p-8">
          <!-- Mobile: Branding (shown only on mobile) -->
          <div class="lg:hidden text-center mb-8">
            <div class="flex items-center justify-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl">
                <UIcon
                  name="i-lucide-key-round"
                  class="w-8 h-8 text-white"
                />
              </div>
            </div>
            <h1 class="gradient-text-primary text-3xl font-bold mb-3">
              Forgot Password
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              We'll help you recover your account
            </p>
          </div>

          <!-- Desktop: Simple Header -->
          <div class="hidden lg:block text-center mb-8">
            <h2 class="gradient-text-primary text-2xl font-bold mb-2">
              Forgot Password
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm">
              Enter your email to reset your password
            </p>
          </div>

          <!-- Success Message -->
          <div
            v-if="success"
            class="mb-6"
          >
            <div class="flex items-start gap-3 px-4 py-4 bg-green-50 dark:bg-green-900/20 border border-green-200/60 dark:border-green-800/60 rounded-xl text-green-700 dark:text-green-400">
              <UIcon
                name="i-lucide-check-circle"
                class="w-5 h-5 flex-shrink-0 mt-0.5"
              />
              <div class="flex-1">
                <p class="font-semibold mb-1">
                  Reset Link Sent!
                </p>
                <p class="text-sm">
                  Check your email for the password reset link. It will expire in 60 minutes.
                </p>
              </div>
            </div>
            <div class="mt-6 text-center">
              <NuxtLink to="/auth/login">
                <UButton
                  color="primary"
                  variant="soft"
                  size="lg"
                >
                  <UIcon
                    name="i-lucide-arrow-left"
                    class="w-4 h-4"
                  />
                  Back to Login
                </UButton>
              </NuxtLink>
            </div>
          </div>

          <!-- Forgot Password Form -->
          <form
            v-if="!success"
            class="space-y-6"
            @submit.prevent="handleForgotPassword"
          >
            <!-- Email Address -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <UIcon
                  name="i-lucide-mail"
                  class="w-4 h-4"
                />
                <span>Email Address <span class="text-red-500">*</span></span>
              </label>
              <div class="relative">
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="you@example.com"
                  class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                >
                <UIcon
                  name="i-lucide-at-sign"
                  class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
                />
              </div>
              <p
                v-if="fieldErrors.email"
                class="text-xs text-red-500 mt-1"
              >
                {{ fieldErrors.email }}
              </p>
            </div>

            <!-- Error Alert -->
            <div
              v-if="error"
              class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200/60 dark:border-red-800/60 rounded-xl text-red-600 dark:text-red-400 text-sm"
            >
              <UIcon
                name="i-lucide-alert-circle"
                class="w-5 h-5 flex-shrink-0"
              />
              <p>{{ error }}</p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
              <UIcon
                :name="loading ? 'i-lucide-loader-2' : 'i-lucide-send'"
                :class="{ 'animate-spin': loading }"
                class="w-5 h-5"
              />
              <span>{{ loading ? 'Sending...' : 'Send Reset Link' }}</span>
            </button>
          </form>

          <!-- Back to Login Link -->
          <div
            v-if="!success"
            class="mt-8 text-center"
          >
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Remember your password?
              <NuxtLink
                to="/auth/login"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors"
              >
                Sign In
              </NuxtLink>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'guest',
  middleware: '$guest'
})

const config = useRuntimeConfig()

const loading = ref(false)
const success = ref(false)
const error = ref<string | null>(null)
const fieldErrors = reactive<Record<string, string>>({})

const form = reactive({
  email: ''
})

const handleForgotPassword = async () => {
  loading.value = true
  error.value = null
  Object.keys(fieldErrors).forEach((key) => { delete fieldErrors[key] })

  try {
    await $fetch(`${config.public.apiBase}/api/auth/forgot-password`, {
      method: 'POST',
      body: {
        email: form.email
      }
    })

    success.value = true
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string, errors?: Record<string, string[]> } }
    const apiErrors = fetchError.data?.errors || {}

    if (Object.keys(apiErrors).length > 0) {
      for (const [key, messages] of Object.entries(apiErrors)) {
        fieldErrors[key] = messages?.[0] || 'Invalid value'
      }
      error.value = Object.values(apiErrors).flat()[0] as string
    } else {
      error.value = fetchError.data?.message || 'Failed to send reset link. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
