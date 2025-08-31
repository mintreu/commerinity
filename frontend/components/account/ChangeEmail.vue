<template>
  <form @submit.prevent="saveEmail" class="bg-white dark:bg-gray-800 shadow rounded-xl max-w-full mx-auto w-full my-4">
    <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">

      <!-- Title & Description -->
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Change Email</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Update your email address and verify it with OTP.
        </p>
      </div>

      <!-- Email Input & OTP -->
      <div class="p-4 space-y-6">

        <!-- Email Input -->
        <div>
          <label for="email" class="form-label">New Email Address</label>
          <input
              v-model="email"
              id="email"
              type="email"
              placeholder="you@example.com"
              class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600
              text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 rounded-md mt-1"
              @blur="onEmailBlur"
          />
          <span v-if="fieldError" class="form-error">{{ fieldError }}</span>
        </div>

        <!-- Send OTP Button -->
        <div class="flex justify-end" v-if="canSendOtp && !otpVerified">
          <button
              type="button"
              @click="sendOtp"
              :disabled="otpSending"
              class="btn-green text-sm px-4 py-2"
          >
            {{ otpSent ? `Resend OTP (${countdown}s)` : 'Send OTP' }}
          </button>
        </div>

        <!-- OTP Fields -->
        <div v-if="otpSent && !otpVerified" class="flex flex-col items-center w-full">
          <label class="form-label mt-4 text-center">Enter OTP</label>
          <div class="flex justify-center gap-2 mt-2 flex-wrap">
            <input
                v-for="(digit, index) in otp"
                :key="index"
                maxlength="1"
                type="text"
                inputmode="numeric"
                class="otp-input"
                v-model="otp[index]"
                @input="focusNext(index, $event)"
            />
          </div>
          <span v-if="otpError" class="form-error mt-2">{{ otpError }}</span>
          <button
              type="button"
              @click="verifyOtp"
              class="btn-blue mt-4 w-full sm:w-1/2 md:w-1/3"
              :disabled="verifyingOtp || otp.join('').length < 6"
          >
            {{ verifyingOtp ? 'Verifying...' : 'Verify OTP' }}
          </button>
        </div>

        <!-- OTP Verified -->
        <div v-if="otpVerified" class="text-center mt-4">
          <p class="text-green-600 font-semibold text-sm">✅ OTP Verified Successfully</p>
          <button
              type="submit"
              class="btn-blue mt-2 w-full sm:w-1/2 md:w-1/3"
              :disabled="saving"
          >
            {{ saving ? 'Saving...' : 'Save Email' }}
          </button>
        </div>

      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'
import { useToast } from '#imports'

const toast = useToast()
const config = useRuntimeConfig()
const { user, refreshUser } = useSanctum()

const email = ref('')
const otp = ref(['', '', '', '', '', ''])
const otpSent = ref(false)
const otpVerified = ref(false)
const otpSending = ref(false)
const verifyingOtp = ref(false)
const saving = ref(false)
const countdown = ref(60)
let countdownInterval: any = null
const fieldError = ref('')
const otpError = ref('')
const canSendOtp = ref(false)
let originalEmail = ''

onMounted(() => {
  if (user.value?.email) {
    email.value = user.value.email
    originalEmail = user.value.email
  }
})

async function onEmailBlur() {
  const value = email.value.trim()
  if (value === originalEmail) {
    otpSent.value = false
    otpVerified.value = false
    otpError.value = ''
    canSendOtp.value = false
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(value)) {
    fieldError.value = 'Enter a valid email address'
    canSendOtp.value = false
    return
  }

  fieldError.value = ''
  otpSent.value = false
  otpVerified.value = false
  otpError.value = ''

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/has_contact`, {
      method: 'POST',
      body: { type: 'email', value },
    })
    canSendOtp.value = !res.data.exists
    if (res.data.exists) fieldError.value = 'Email already in use'
  } catch {
    fieldError.value = 'Failed to check email'
    canSendOtp.value = false
  }
}

function focusNext(index: number, event: any) {
  if (event.inputType !== 'deleteContentBackward') {
    const next = event.target.nextElementSibling
    if (next) next.focus()
  }
}

async function sendOtp() {
  otpError.value = ''
  otpVerified.value = false
  otpSending.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { type: 'email', value: email.value.trim() },
    })
    otpSent.value = true
    canSendOtp.value = false
    countdown.value = 60
    countdownInterval = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(countdownInterval)
        canSendOtp.value = true
        otpSending.value = false
      }
    }, 1000)
    toast.info({ title: 'OTP Sent', message: 'A 6-digit OTP has been sent to your email.' })
  } catch (err: any) {
    otpError.value = err?.data?.message || 'Failed to send OTP'
    otpSending.value = false
    toast.error({ title: 'Error', message: otpError.value })
  }
}

async function verifyOtp() {
  const code = otp.value.join('')
  if (code.length !== 6) {
    otpError.value = 'Enter 6-digit OTP'
    return
  }

  verifyingOtp.value = true
  otpError.value = ''
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { type: 'email', value: email.value.trim(), otp: code },
    })
    otpVerified.value = res.data?.valid === true
    if (!otpVerified.value) otpError.value = 'Invalid OTP, please try again'
    if (otpVerified.value) otpSent.value = false
  } catch (err: any) {
    otpError.value = err?.data?.message || 'OTP verification failed'
  } finally {
    verifyingOtp.value = false
  }
}

async function saveEmail() {
  if (!otpVerified.value) return
  saving.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/account/contact`, {
      method: 'PUT',
      body: {
        email: email.value.trim(),
        type: 'email',
        otp: otp.value.join(''), // ✅ send OTP along with mobile
      },
    })
    toast.success({ title: 'Success!', message: 'Email updated successfully.' })
    otpVerified.value = false
    otpSent.value = false
    otp.value = ['', '', '', '', '', '']
    originalEmail = email.value.trim()
    canSendOtp.value = false
    refreshUser()
  } catch (err: any) {
    toast.error({ title: 'Error!', message: err?.data?.message || 'Failed to update email' })
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.input-field {
  @apply w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600
  text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 rounded-md;
}
.otp-input {
  @apply w-12 sm:w-14 md:w-16 h-12 sm:h-14 md:h-16 text-center rounded border border-gray-300 dark:border-gray-600
  bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500;
}
.form-label {
  @apply block text-sm font-medium text-gray-700 dark:text-gray-300;
}
.btn-blue {
  @apply bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition;
}
.btn-green {
  @apply bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition;
}
.form-error {
  @apply text-sm text-red-600 dark:text-red-400 mt-1 block text-center;
}
</style>
