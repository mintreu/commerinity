<template>
  <div class="relative h-screen w-full bg-gray-100 dark:bg-gray-900 overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/bg-login.jpeg');"></div>
    <div class="absolute inset-0 bg-black bg-opacity-50 dark:bg-opacity-60 backdrop-blur-sm"></div>

    <!-- Card -->
    <div class="relative z-10 flex items-center justify-center h-full px-4 max-w-full">
      <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8">
        <!-- Branding -->
        <div class="flex flex-col items-center mb-6">
          <img src="/logo.png" alt="Logo" class="h-12 w-auto mb-2" />
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Reset Your Password</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Recover access using mobile or email</p>
        </div>

        <!-- Tab Toggle -->
        <div class="flex justify-center gap-6 text-sm mb-6">
          <button :class="contactType === 'mobile' ? activeTab : inactiveTab" @click="switchMode('mobile')">Mobile</button>
          <button :class="contactType === 'email' ? activeTab : inactiveTab" @click="switchMode('email')">Email</button>
        </div>

        <!-- Step 1: Contact Input -->
        <form v-if="step === 1" @submit.prevent="sendOtp" class="space-y-5">
          <div>
            <label :for="contactType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ contactType === 'mobile' ? 'Mobile Number' : 'Email Address' }}
            </label>
            <input
                v-model="form[contactType]"
                :type="contactType === 'mobile' ? 'tel' : 'email'"
                :id="contactType"
                required
                :placeholder="contactType === 'mobile' ? '+8801XXXXXXXXX' : 'you@example.com'"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow">
            Send OTP
          </button>
        </form>

        <!-- Step 2: OTP Verification -->
        <form v-else-if="step === 2" @submit.prevent="verifyOtp" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Enter 6-digit OTP</label>
            <div class="flex gap-2 justify-between">
              <input
                  v-for="(digit, i) in 6"
                  :key="i"
                  v-model="otp[i]"
                  maxlength="1"
                  :ref="el => otpRefs[i] = el"
                  @input="onOtpInput(i)"
                  @keydown.backspace="onOtpBackspace(i)"
                  class="w-10 h-10 text-center border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              />

            </div>
          </div>
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow">
            Verify OTP
          </button>
        </form>

        <!-- Step 3: Reset Password -->
        <form v-else-if="step === 3" @submit.prevent="resetPassword" class="space-y-5">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
            <div class="relative">
              <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white pr-10 focus:ring-2 focus:ring-blue-500"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2 text-gray-500 dark:text-gray-400">
                <span v-if="showPassword">🙈</span>
                <span v-else>👁️</span>
              </button>
            </div>
          </div>

          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
            <div class="relative">
              <input
                  v-model="form.confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  id="confirmPassword"
                  class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white pr-10 focus:ring-2 focus:ring-blue-500"
              />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2 text-gray-500 dark:text-gray-400">
                <span v-if="showConfirmPassword">🙈</span>
                <span v-else>👁️</span>
              </button>
            </div>
          </div>

          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow">
            Reset Password
          </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Remembered your password?
          <NuxtLink to="/auth/login" class="text-blue-600 hover:underline dark:text-blue-400">Login</NuxtLink>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <AlertModal v-model="showModal" :title="modalTitle" :message="modalMessage" />
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue'
import { useRouter, useRuntimeConfig, useSanctumFetch } from '#imports'
import AlertModal from '~/components/AlertModal.vue'

// State
const contactType = ref<'mobile' | 'email'>('mobile')
const step = ref(1)
const otp = ref(['', '', '', '', '', ''])
const form = ref({
  mobile: '',
  email: '',
  password: '',
  confirmPassword: ''
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Modal State
const showModal = ref(false)
const modalTitle = ref('')
const modalMessage = ref('')

// Router + Config
const router = useRouter()
const config = useRuntimeConfig()

// OTP Refs (optional if you use `ref="otpRefs[i]"`)
const otpRefs = ref<HTMLInputElement[]>([])

// Switch mode between email/mobile
function switchMode(mode: 'mobile' | 'email') {
  contactType.value = mode
  otp.value = ['', '', '', '', '', '']
  form.value.password = ''
  form.value.confirmPassword = ''
  step.value = 1
}

// Modal helper
function openModal(title: string, message: string) {
  modalTitle.value = title
  modalMessage.value = message
  showModal.value = true
}

// Send OTP
async function sendOtp() {
  const value = form.value[contactType.value].trim()
  if (!value) {
    openModal('Invalid Input', 'Please enter a valid contact.')
    return
  }

  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: {
        type: contactType.value,
        value
      }
    })
    step.value = 2
    nextTick(() => otpRefs.value[0]?.focus())
  } catch {
    openModal('Error', 'Failed to send OTP.')
  }
}

// OTP navigation
function onOtpInput(i: number) {
  if (otp.value[i].length === 1 && i < 5) nextTick(() => otpRefs.value[i + 1]?.focus())
}
function onOtpBackspace(i: number) {
  if (otp.value[i] === '' && i > 0) nextTick(() => otpRefs.value[i - 1]?.focus())
}

// Verify OTP
async function verifyOtp() {
  const fullOtp = otp.value.join('')
  if (fullOtp.length !== 6) {
    openModal('Invalid OTP', 'Please enter the 6-digit OTP.')
    return
  }

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: {
        type: contactType.value,
        value: form.value[contactType.value].trim(),
        otp: fullOtp
      }
    })

    if (res.data?.valid) {
      step.value = 3
    } else {
      openModal('Verification Failed', 'Invalid OTP, please try again.')
    }
  } catch {
    openModal('Verification Failed', 'OTP verification failed.')
  }
}

// Reset password
async function resetPassword() {
  const password = form.value.password.trim()
  const confirmPassword = form.value.confirmPassword.trim()
  const value = form.value[contactType.value].trim()
  const fullOtp = otp.value.join('')

  if (password.length < 6) {
    openModal('Weak Password', 'Password must be at least 6 characters.')
    return
  }
  if (password !== confirmPassword) {
    openModal('Mismatch', 'Passwords do not match.')
    return
  }

  try {
    await useSanctumFetch(`${config.public.apiBase}/reset_password`, {
      method: 'POST',
      body: {
        type: contactType.value,
        value,
        otp: fullOtp,
        password,
        password_confirmation: confirmPassword
      }
    })

    openModal('Success', 'Password reset successfully. Redirecting to login...')
    setTimeout(() => router.push('/auth/login'), 1500)
  } catch {
    openModal('Error', 'Password reset failed.')
  }
}
</script>

<style scoped>
/* Optional styles for tab active/inactive */
.activeTab {
  @apply border-b-2 border-blue-600 font-semibold text-blue-600;
}
.inactiveTab {
  @apply text-gray-500 hover:text-blue-500;
}
.input-field {
  @apply mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:outline-none;
}
</style>
