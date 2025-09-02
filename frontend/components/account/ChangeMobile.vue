<template>
  <form @submit.prevent="saveMobile" class="bg-white dark:bg-gray-800 shadow rounded-xl max-w-full mx-auto w-full my-4">
    <div class="grid grid-cols-1 md:grid-cols-[250px_1fr] divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-700">

      <!-- Title & Description -->
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Manage Your Mobile</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Update your mobile number and verify it with OTP.
        </p>
      </div>

      <!-- Mobile Input & OTP -->
      <div class="p-4 space-y-6">

        <!-- Mobile Input with +91 prefix -->
        <div>
          <label for="mobile" class="form-label">Your Mobile Number</label>
          <div class="flex items-center mt-1">
            <span class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-r-0 rounded-l-md text-gray-700 dark:text-gray-300">+91</span>
            <input
                v-model="mobile"
                id="mobile"
                type="tel"
                placeholder="9876543210"
                class="w-full flex-1 px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600
                text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 rounded-r-md"
                @blur="onMobileBlur"
            />
          </div>
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
          <div class="flex justify-center gap-1 mt-2 flex-wrap">
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
            {{ saving ? 'Saving...' : 'Save Mobile Number' }}
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

const mobile = ref('')
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
let originalMobile = ''

onMounted(() => {
  if (user.value?.mobile) {
    mobile.value = user.value.mobile
    originalMobile = user.value.mobile
  }
})

async function onMobileBlur() {
  const value = mobile.value.trim()
  if (value === originalMobile) {
    otpSent.value = false
    otpVerified.value = false
    otpError.value = ''
    canSendOtp.value = false
    return
  }

  const regex = /^[6-9]\d{9}$/
  if (!regex.test(value)) {
    fieldError.value = 'Enter a valid 10-digit Indian mobile number'
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
      body: { type: 'mobile', value },
    })
    canSendOtp.value = !res.data.exists
    if (res.data.exists) fieldError.value = 'Mobile number already in use'
  } catch {
    fieldError.value = 'Failed to check mobile'
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
      body: { type: 'mobile', value: mobile.value.trim() },
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
    toast.info({ title: 'OTP Sent', message: 'A 6-digit OTP has been sent to your mobile.' })
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
      body: { type: 'mobile', value: mobile.value.trim(), otp: code },
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

async function saveMobile() {
  if (!otpVerified.value) return
  saving.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/account/contact`, {
      method: 'PUT',
      body: {
        mobile: mobile.value.trim(),
        type: 'mobile',
        otp: otp.value.join(''), // ✅ send OTP along with mobile
      },
    })
    toast.success({ title: 'Success!', message: 'Mobile number updated successfully.' })
    otpVerified.value = false
    otp.value = ['', '', '', '', '', '']
    originalMobile = mobile.value.trim()
    canSendOtp.value = false
    refreshUser()
  } catch (err: any) {
    toast.error({ title: 'Error!', message: err?.data?.message || 'Failed to update mobile' })
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
  @apply w-12 sm:w-10 md:w-16 h-12 sm:h-10 md:h-16 text-center rounded border border-gray-300 dark:border-gray-600
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
