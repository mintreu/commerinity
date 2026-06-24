<script setup lang="ts">
const emit = defineEmits<{
  success: [token: string]
}>()

const config = useRuntimeConfig()
const toast = useToast()
const route = useRoute()

const currentStep = ref(1)
const otpSent = ref(false)
const otpVerified = ref(false)
const sendingOtp = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const showPassword = ref(false)

const form = reactive({
  name: '',
  email: '',
  mobile: '',
  otp: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const referralCodeFromUrl = computed(() => {
  const value = route.query.ref || route.query.referral_code || route.query.code
  if (typeof value !== 'string') {
    return ''
  }
  return value.trim().toUpperCase()
})

// Password strength calculation
const passwordStrength = computed(() => {
  const password = form.password
  let strength = 0
  if (password.length >= 8) strength++
  if (/[A-Z]/.test(password)) strength++
  if (/[0-9]/.test(password)) strength++
  if (/[^A-Za-z0-9]/.test(password)) strength++
  return strength
})

const strengthColor = computed(() => {
  if (passwordStrength.value <= 1) return 'bg-red-500'
  if (passwordStrength.value === 2) return 'bg-yellow-500'
  if (passwordStrength.value === 3) return 'bg-blue-400'
  return 'bg-blue-500'
})

const handleSendOtp = async () => {
  if (!form.email) {
    error.value = 'Please enter your email address'
    return
  }

  if (!form.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
    error.value = 'Please enter a valid email address'
    return
  }

  sendingOtp.value = true
  error.value = null

  try {
    const response = await $fetch<{ success: boolean, demo?: boolean, otp?: string }>(`${config.public.apiBase}/api/auth/send-otp`, {
      method: 'POST',
      body: {
        type: 'email',
        value: form.email
      }
    })

    otpSent.value = true

    toast.add({
      title: 'OTP Sent',
      description: 'Check your email for the verification code',
      color: 'success'
    })
  } catch (err: unknown) {
    const fetchError = err as { statusCode?: number, data?: { message?: string } }
    error.value = fetchError.data?.message || 'Failed to send OTP'

    const isRateLimited = fetchError.statusCode === 429 || /too many otp requests/i.test(error.value)

    toast.add({
      title: isRateLimited ? 'Too Many OTP Requests' : 'OTP Send Failed',
      description: error.value,
      color: isRateLimited ? 'warning' : 'error'
    })
  } finally {
    sendingOtp.value = false
  }
}

const handleResendOtp = () => {
  form.otp = ''
  handleSendOtp()
}

const changeEmail = () => {
  otpSent.value = false
  otpVerified.value = false
  form.otp = ''
}

const verifyOtpAndNext = () => {
  if (form.otp.length !== 6) {
    error.value = 'Please enter a valid 6-digit OTP'
    return
  }
  otpVerified.value = true
  currentStep.value = 2
  error.value = null
}

const handleRegister = async () => {
  if (!form.terms) {
    error.value = 'Please accept the terms and conditions'
    return
  }

  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  if (form.password.length < 8) {
    error.value = 'Password must be at least 8 characters'
    return
  }

  loading.value = true
  error.value = null

  try {
    interface RegisterPayload {
      name: string
      email: string
      otp: string
      password: string
      password_confirmation: string
      mobile?: string
      referral_code?: string
    }

    const payload: RegisterPayload = {
      name: form.name,
      email: form.email,
      otp: form.otp,
      password: form.password,
      password_confirmation: form.password_confirmation
    }

    if (form.mobile) {
      payload.mobile = form.mobile
    }

    const referralCode = referralCodeFromUrl.value

    if (referralCode) {
      payload.referral_code = referralCode.toUpperCase()
    }

    const response = await $fetch<{ success: boolean, data: { user: Record<string, unknown>, token: string } }>(`${config.public.apiBase}/api/auth/register-email`, {
      method: 'POST',
      body: payload
    })

    if (response.data?.token) {
      emit('success', response.data.token)
    }
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string, errors?: Record<string, string[]> } }
    if (fetchError.data?.errors) {
      const errors = Object.values(fetchError.data.errors).flat()
      error.value = errors[0] as string
    } else {
      error.value = fetchError.data?.message || 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <!-- Step Indicator -->
    <div class="flex items-center justify-center gap-2 mb-6">
      <div
        :class="currentStep >= 1 ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-600'"
        class="w-8 h-1 rounded-full transition-colors"
      />
      <div
        :class="currentStep >= 2 ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-600'"
        class="w-8 h-1 rounded-full transition-colors"
      />
      <div
        :class="currentStep >= 3 ? 'bg-blue-500' : 'bg-slate-300 dark:bg-slate-600'"
        class="w-8 h-1 rounded-full transition-colors"
      />
    </div>

    <!-- Register Form -->
    <form
      class="space-y-5"
      @submit.prevent="handleRegister"
    >
      <!-- Step 1: Email & OTP -->
      <template v-if="currentStep === 1">
        <div class="space-y-2">
          <label class="required-label flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-mail"
              class="w-4 h-4"
            />
            <span>Email Address</span>
          </label>
          <div class="relative">
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              :disabled="otpSent"
              class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:opacity-60 disabled:cursor-not-allowed"
            >
            <UIcon
              name="i-lucide-at-sign"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
            />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            We'll send a verification code to this email
          </p>
        </div>

        <!-- Send OTP Button -->
        <button
          v-if="!otpSent"
          type="button"
          :disabled="sendingOtp || !form.email"
          class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
          @click="handleSendOtp"
        >
          <UIcon
            :name="sendingOtp ? 'i-lucide-loader-2' : 'i-lucide-send'"
            :class="{ 'animate-spin': sendingOtp }"
            class="w-5 h-5"
          />
          <span>{{ sendingOtp ? 'Sending Code...' : 'Send Verification Code' }}</span>
        </button>

        <!-- OTP Input (after OTP sent) -->
        <template v-if="otpSent">
          <div class="space-y-2">
            <label class="required-label flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
              <UIcon
                name="i-lucide-shield-check"
                class="w-4 h-4"
              />
              <span>Enter Verification Code</span>
            </label>
            <div class="relative">
              <input
                v-model="form.otp"
                type="text"
                maxlength="6"
                required
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="******"
                class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all tracking-widest text-center text-lg font-mono"
              >
              <UIcon
                name="i-lucide-hash"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
              />
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <button
              type="button"
              class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors"
              @click="handleResendOtp"
            >
              Resend Code
            </button>
            <button
              type="button"
              class="text-slate-600 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium transition-colors"
              @click="changeEmail"
            >
              Change Email
            </button>
          </div>

          <button
            type="button"
            :disabled="form.otp.length !== 6"
            class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            @click="verifyOtpAndNext"
          >
            <UIcon
              name="i-lucide-arrow-right"
              class="w-5 h-5"
            />
            <span>Continue</span>
          </button>
        </template>
      </template>

      <!-- Step 2: Personal Details -->
      <template v-if="currentStep === 2">
        <div class="space-y-2">
          <label class="required-label flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-user"
              class="w-4 h-4"
            />
            <span>Full Name</span>
          </label>
          <div class="relative">
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="John Doe"
              class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <UIcon
              name="i-lucide-user"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
            />
          </div>
        </div>

        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-smartphone"
              class="w-4 h-4"
            />
            <span>Mobile Number</span>
          </label>
          <div class="relative">
            <input
              v-model="form.mobile"
              type="tel"
              placeholder="10-digit mobile number"
              class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <UIcon
              name="i-lucide-phone"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
            />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Enter a 10-digit mobile number.
          </p>
        </div>

        <div class="flex gap-3">
          <button
            type="button"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-slate-200 dark:bg-slate-600 hover:bg-slate-300 dark:hover:bg-slate-500 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all"
            @click="currentStep = 1"
          >
            <UIcon
              name="i-lucide-arrow-left"
              class="w-5 h-5"
            />
            <span>Back</span>
          </button>
          <button
            type="button"
            :disabled="!form.name"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            @click="currentStep = 3"
          >
            <span>Continue</span>
            <UIcon
              name="i-lucide-arrow-right"
              class="w-5 h-5"
            />
          </button>
        </div>
      </template>

      <!-- Step 3: Password & Submit -->
      <template v-if="currentStep === 3">
        <div class="space-y-2">
          <label class="required-label flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-lock"
              class="w-4 h-4"
            />
            <span>Password</span>
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="At least 8 characters"
              class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <UIcon
              name="i-lucide-key"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
            />
            <button
              type="button"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300"
              @click="showPassword = !showPassword"
            >
              <UIcon
                :name="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                class="w-4 h-4"
              />
            </button>
          </div>
          <!-- Password Strength Indicator -->
          <div class="flex gap-1">
            <div
              v-for="i in 4"
              :key="i"
              :class="passwordStrength >= i ? strengthColor : 'bg-slate-200 dark:bg-slate-600'"
              class="h-1 flex-1 rounded-full transition-colors"
            />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Use 8+ characters with mix of letters, numbers & symbols
          </p>
        </div>

        <div class="space-y-2">
          <label class="required-label flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <UIcon
              name="i-lucide-lock"
              class="w-4 h-4"
            />
            <span>Confirm Password</span>
          </label>
          <div class="relative">
            <input
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Re-enter your password"
              class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            >
            <UIcon
              name="i-lucide-key"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
            />
            <UIcon
              v-if="form.password_confirmation && form.password === form.password_confirmation"
              name="i-lucide-check-circle"
              class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-500"
            />
          </div>
        </div>

        <!-- Terms & Conditions -->
        <label class="flex items-start gap-3 cursor-pointer">
          <input
            v-model="form.terms"
            type="checkbox"
            required
            class="w-5 h-5 mt-0.5 text-blue-600 rounded border-slate-300 dark:border-slate-600 focus:ring-blue-500"
          >
          <span class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            I agree to the
            <NuxtLink
              to="/terms"
              class="text-blue-600 dark:text-blue-400 hover:underline font-medium"
              target="_blank"
            >
              Terms & Conditions
            </NuxtLink>
            and
            <NuxtLink
              to="/privacy"
              class="text-blue-600 dark:text-blue-400 hover:underline font-medium"
              target="_blank"
            >
              Privacy Policy
            </NuxtLink>
          </span>
        </label>

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

        <div class="flex gap-3">
          <button
            type="button"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-slate-200 dark:bg-slate-600 hover:bg-slate-300 dark:hover:bg-slate-500 text-slate-700 dark:text-slate-200 rounded-xl font-semibold transition-all"
            @click="currentStep = 2"
          >
            <UIcon
              name="i-lucide-arrow-left"
              class="w-5 h-5"
            />
            <span>Back</span>
          </button>
          <button
            type="submit"
            :disabled="loading || !form.terms || !form.password || form.password !== form.password_confirmation"
            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
          >
            <UIcon
              :name="loading ? 'i-lucide-loader-2' : 'i-lucide-user-plus'"
              :class="{ 'animate-spin': loading }"
              class="w-5 h-5"
            />
            <span>{{ loading ? 'Creating...' : 'Create Account' }}</span>
          </button>
        </div>
      </template>
    </form>
  </div>
</template>
