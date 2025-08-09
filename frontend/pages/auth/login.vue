<template>
  <div class="h-screen w-full bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
    <div class="w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg">
      <!-- Branding -->
      <div class="text-center mb-6">
        <img src="/logo.png" alt="Logo" class="mx-auto h-12 mb-2" />
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Welcome to Commernity</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Simple. Secure. Seamless Login</p>

        <NuxtLink
            to="/auth/register"
            class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors underline"
        >
          Don’t have an account? Register here
        </NuxtLink>
      </div>

      <!-- Mode Toggle -->
      <div class="flex justify-center mb-6 space-x-6 text-sm">
        <button :class="loginMode === 'mobile' ? activeTab : inactiveTab" @click="switchMode('mobile')">Mobile Login</button>
        <button :class="loginMode === 'email' ? activeTab : inactiveTab" @click="switchMode('email')">Email Login</button>
      </div>

      <!-- Mobile Login Form -->
      <form v-if="loginMode === 'mobile'" @submit.prevent="handleMobileSubmit" class="space-y-5">
        <!-- Mobile Number -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile Number</label>
          <input v-model="form.mobile" type="tel" required placeholder="+91 9XXXXXXXXX"
                 class="mt-1 w-full px-4 py-2 border rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          <p v-if="errors.mobile" class="text-red-500 text-sm mt-1">{{ errors.mobile }}</p>
        </div>

        <!-- OTP or Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ useOtp ? 'OTP' : 'Password' }}</label>
          <div v-if="useOtp">
            <div v-if="!otpSent">
              <button @click="sendOtp" type="button"
                      class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition"
                      :disabled="sendingOtp">
                {{ sendingOtp ? 'Sending OTP...' : 'Send OTP' }}
              </button>
            </div>
            <div v-else>
              <!-- OTP Input Fields and Verify Button -->
              <template v-if="!otpVerified">
                <div class="flex gap-2 mt-2">
                  <input v-for="(_, i) in 6" :key="i" v-model="otp[i]" :ref="el => otpRefs[i] = el"
                         maxlength="1" inputmode="numeric" type="text"
                         class="w-10 h-10 text-center border rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                         @input="onOtpInput(i)" @keydown.backspace.prevent="onOtpBackspace(i)" />
                </div>
                <button type="button" @click="verifyOtp"
                        class="w-full mt-3 bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition"
                        :disabled="verifyingOtp">
                  {{ verifyingOtp ? 'Verifying...' : 'Verify OTP' }}
                </button>

                <!-- Countdown + Resend -->
                <div class="text-xs text-gray-500 text-center mt-2">
                  <span v-if="otpCountdown > 0">Resend OTP in {{ otpCountdown }}s</span>
                  <button v-else @click="resendOtp" class="text-blue-600 hover:underline" type="button">Resend OTP</button>
                </div>

                <p v-if="errors.validated_otp" class="text-red-500 text-sm mt-1">{{ errors.validated_otp }}</p>
              </template>

              <!-- Success Message -->
              <p v-else class="text-green-600 text-sm mt-3 text-center">✅ OTP Verified Successfully!</p>
            </div>
          </div>

          <div v-else class="relative">
            <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="••••••••"
                class="mt-1 w-full px-4 py-2 border rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-10"
            />
            <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
                tabindex="-1"
            >
              <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-5 h-5" />
            </button>
          </div>
          <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>

        </div>

        <!-- Options -->
        <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
          <label class="inline-flex items-center">
            <input type="checkbox" v-model="form.remember" class="mr-2" />
            Remember me
          </label>
          <label class="inline-flex items-center">
            <input type="checkbox" v-model="useOtp" class="mr-2" />
            Login with OTP
          </label>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition"
                :disabled="useOtp && !otpVerified">
          Sign In
        </button>
        <p v-if="errors.auth" class="text-red-500 text-sm text-center mt-1">{{ errors.auth }}</p>
      </form>

      <!-- Email Login Form -->
      <form v-else @submit.prevent="handleEmailLogin" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
          <input v-model="form.email" type="email" required placeholder="you@example.com"
                 class="mt-1 w-full px-4 py-2 border rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
        </div>
        <div class="relative">
          <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="••••••••"
              class="mt-1 w-full px-4 py-2 border rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-10"
          />
          <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white"
              tabindex="-1"
          >
            <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-5 h-5" />
          </button>
        </div>

        <div class="flex text-sm text-gray-700 dark:text-gray-300">
          <label><input type="checkbox" v-model="form.remember" class="mr-2" />Remember me</label>
        </div>
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
          Sign In
        </button>
        <p v-if="errors.auth" class="text-red-500 text-sm text-center mt-1">{{ errors.auth }}</p>
      </form>

      <!-- Social Login -->
      <div class="mt-6">
        <button @click="loginWithGoogle" class="w-full flex items-center justify-center gap-3 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm font-medium text-gray-700 dark:text-gray-300">
          <Icon name="logos:google-icon" class="w-5 h-5" />
          Continue with Google
        </button>
      </div>


      <!-- Forgot password -->
      <p class="text-center text-sm mt-4">
        <NuxtLink to="/auth/forgot-password" class="text-blue-600 hover:underline">Forgot your password?</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRuntimeConfig } from '#imports'
import { useSanctumFetch, useSanctum } from '#imports'

const router = useRouter()
const config = useRuntimeConfig()
const { login } = useSanctum()
const { isLoggedIn } = useSanctum()
const showPassword = ref(false)
const loginMode = ref<'mobile' | 'email'>('mobile')
const useOtp = ref(false)
const otpSent = ref(false)
const otpVerified = ref(false)
const sendingOtp = ref(false)
const verifyingOtp = ref(false)
const otpCountdown = ref(0)
const countdownInterval = ref<NodeJS.Timeout | null>(null)
const otp = ref(['', '', '', '', '', ''])
const otpRefs = ref<HTMLInputElement[]>([])
const errors = ref<Record<string, string>>({})
const form = ref({ mobile: '', email: '', password: '', remember: false })

const activeTab = 'px-4 py-2 text-blue-600 border-b-2 border-blue-600 font-semibold'
const inactiveTab = 'px-4 py-2 text-gray-500 hover:text-blue-600'


onMounted(() => {
  if (isLoggedIn.value) {
    router.push('/dashboard')
  }
})

function switchMode(mode: 'mobile' | 'email') {
  loginMode.value = mode
  useOtp.value = otpSent.value = otpVerified.value = false
  otp.value = ['', '', '', '', '', '']
  form.value = { mobile: '', email: '', password: '', remember: false }
  errors.value = {}
  clearOtpCountdown()
}

function validateIndianMobile(mobile: string): string | null {
  const cleaned = mobile.trim()
  if (!/^[6-9]\d{9}$/.test(cleaned)) return 'Enter a valid 10-digit Indian mobile number'
  return null
}

function startOtpCountdown() {
  otpCountdown.value = 60
  countdownInterval.value = setInterval(() => {
    if (otpCountdown.value > 0) otpCountdown.value--
    else clearOtpCountdown()
  }, 1000)
}

function clearOtpCountdown() {
  if (countdownInterval.value) clearInterval(countdownInterval.value)
  countdownInterval.value = null
}

async function sendOtp() {
  errors.value.mobile = validateIndianMobile(form.value.mobile) || ''
  if (errors.value.mobile) return

  sendingOtp.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { type: 'mobile', value: form.value.mobile.trim() }
    })
    otpSent.value = true
    startOtpCountdown()
    nextTick(() => otpRefs.value[0]?.focus())
  } catch {
    errors.value.validated_otp = 'Failed to send OTP'
  } finally {
    sendingOtp.value = false
  }
}

function resendOtp() {
  otp.value = ['', '', '', '', '', '']
  otpVerified.value = false
  sendOtp()
}

async function verifyOtp() {
  errors.value.validated_otp = ''
  const fullOtp = otp.value.join('')
  if (fullOtp.length !== 6) {
    errors.value.validated_otp = 'Enter 6-digit OTP'
    return
  }
  verifyingOtp.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { type: 'mobile', value: form.value.mobile.trim(), otp: fullOtp }
    })
    if (res.data?.valid) {
      otpVerified.value = true
      clearOtpCountdown()
    } else {
      errors.value.validated_otp = 'Invalid OTP'
    }
  } catch {
    errors.value.validated_otp = 'OTP verification failed'
  } finally {
    verifyingOtp.value = false
  }
}

async function handleMobileSubmit() {
  errors.value = {}
  const mobileError = validateIndianMobile(form.value.mobile)
  if (mobileError) {
    errors.value.mobile = mobileError
    return
  }

  const payload: any = {
    mobile: form.value.mobile.trim(),
    remember: form.value.remember,
    validated_otp: otpVerified.value
  }
  if (!useOtp.value) payload.password = form.value.password
  try {
    await login(payload)
    router.push('/dashboard')
  } catch (e: any) {
    const err = e?.data?.errors || {}
    errors.value.auth = err.mobile?.[0] || err.password?.[0] || e?.data?.message || 'Login failed'
  }
}

async function handleEmailLogin() {
  errors.value = {}
  try {
    await login({
      email: form.value.email.trim(),
      password: form.value.password,
      remember: form.value.remember
    })
    router.push('/dashboard')
  } catch (e: any) {
    const err = e?.data?.errors || {}
    errors.value.auth = err.email?.[0] || err.password?.[0] || e?.data?.message || 'Login failed'
  }
}

function onOtpInput(i: number) {
  if (otp.value[i].length === 1 && i < 5) nextTick(() => otpRefs.value[i + 1]?.focus())
}
function onOtpBackspace(i: number) {
  if (otp.value[i] === '' && i > 0) nextTick(() => otpRefs.value[i - 1]?.focus())
}

onBeforeUnmount(clearOtpCountdown)
</script>
