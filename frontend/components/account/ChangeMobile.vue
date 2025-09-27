<template>
  <!-- Floating Background Orbs -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div ref="orb1" class="absolute top-20 right-16 w-32 h-32 bg-gradient-to-r from-green-400/10 to-emerald-400/10 dark:from-green-400/5 dark:to-emerald-400/5 rounded-full blur-2xl opacity-60"></div>
    <div ref="orb2" class="absolute bottom-20 left-16 w-24 h-24 bg-gradient-to-r from-blue-400/8 to-teal-400/8 dark:from-blue-400/4 dark:to-teal-400/4 rounded-full blur-2xl opacity-40"></div>
  </div>

  <!-- Success Modal -->
  <Teleport to="body">
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeSuccessModal">
      <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
        <!-- Success Header -->
        <div class="flex items-center gap-6 p-8 border-b border-green-200/50 dark:border-green-800/50">
          <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-3xl flex items-center justify-center shadow-2xl">
            <Icon name="mdi:check-bold" class="w-8 h-8 text-white" />
          </div>
          <div>
            <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">Success!</h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Mobile number updated</p>
          </div>
        </div>

        <!-- Success Body -->
        <div class="p-8">
          <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
            Your mobile number has been successfully updated to <span class="font-bold text-slate-900 dark:text-white">+91 {{ updatedMobile }}</span>
          </p>

          <button
              @click="closeSuccessModal"
              class="w-full px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <form @submit.prevent="saveMobile" class="relative z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
    <!-- Header Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 via-emerald-500/5 to-teal-500/5 opacity-70"></div>

    <div class="relative z-10 grid grid-cols-1 md:grid-cols-[280px_1fr] divide-y md:divide-y-0 md:divide-x divide-slate-200/50 dark:divide-slate-700/50">

      <!-- Title & Description -->
      <div class="p-8 bg-gradient-to-br from-green-50/50 to-emerald-50/50 dark:from-green-900/20 dark:to-emerald-900/20 backdrop-blur-sm">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
            <Icon name="mdi:phone" class="w-6 h-6 text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Mobile Number</h2>
            <div class="w-16 h-1 bg-gradient-to-r from-green-600 to-emerald-600 rounded-full mt-2"></div>
          </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
          Update your mobile number and verify it with OTP for enhanced security.
        </p>

        <!-- Current Mobile Display -->
        <div class="mt-6 p-4 bg-white/60 dark:bg-slate-700/60 rounded-2xl border border-slate-200/50 dark:border-slate-600/50 backdrop-blur-sm">
          <div class="flex items-center gap-3">
            <Icon name="mdi:phone-check" class="w-5 h-5 text-green-600 dark:text-green-400" />
            <div>
              <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Current</p>
              <p class="font-bold text-slate-900 dark:text-white">
                {{ user?.mobile ? '+91 ' + user.mobile : 'Not set' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Input & OTP -->
      <div class="p-8 space-y-8">

        <!-- Mobile Input with +91 prefix -->
        <div class="space-y-3">
          <label for="mobile" class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <Icon name="mdi:phone" class="w-4 h-4" />
            <span>New Mobile Number</span>
            <span class="text-red-500">*</span>
          </label>

          <div class="flex items-center group focus-within:scale-[1.02] transition-transform duration-300">
            <div class="px-4 py-3 bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-600 dark:to-slate-700 border-2 border-r-0 border-slate-200 dark:border-slate-600 rounded-l-xl text-slate-700 dark:text-slate-300 font-bold flex items-center gap-2 shadow-inner">
              <Icon name="mdi:flag" class="w-4 h-4 text-green-600" />
              <span>+91</span>
            </div>
            <input
                v-model="mobile"
                id="mobile"
                type="tel"
                placeholder="9876543210"
                class="w-full flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-r-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 font-semibold"
                :class="{
                  'border-red-500 bg-red-50 dark:bg-red-900/20': fieldError,
                  'border-green-300 bg-green-50 dark:bg-green-900/10': canSendOtp && !fieldError
                }"
                @blur="onMobileBlur"
            />
          </div>

          <div v-if="fieldError" class="flex items-center gap-2 text-sm text-red-500 bg-red-50/80 dark:bg-red-900/20 px-4 py-3 rounded-xl border border-red-200/60 dark:border-red-800/60 backdrop-blur-sm">
            <Icon name="mdi:alert-circle" class="w-5 h-5" />
            <span class="font-medium">{{ fieldError }}</span>
          </div>

          <div v-else-if="canSendOtp" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400 bg-green-50/80 dark:bg-green-900/20 px-4 py-3 rounded-xl border border-green-200/60 dark:border-green-800/60 backdrop-blur-sm">
            <Icon name="mdi:check-circle" class="w-5 h-5" />
            <span class="font-medium">Mobile number is available</span>
          </div>
        </div>

        <!-- Send OTP Button -->
        <div class="flex justify-end" v-if="canSendOtp && !otpVerified">
          <button
              type="button"
              @click="sendOtp"
              :disabled="otpSending || countdown > 0"
              class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
          >
            <Icon name="mdi:loading" v-if="otpSending" class="w-5 h-5 animate-spin" />
            <Icon name="mdi:send" v-else class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" />
            <span>{{ countdown > 0 ? `Resend OTP (${countdown}s)` : 'Send OTP' }}</span>
          </button>
        </div>

        <!-- Demo OTP Notice -->
        <div v-if="otpSent && demoOtp" class="bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 rounded-2xl p-6 backdrop-blur-sm">
          <div class="flex items-center gap-3 mb-3">
            <Icon name="mdi:information" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            <h4 class="font-bold text-amber-700 dark:text-amber-300">Demo Mode</h4>
          </div>
          <p class="text-sm text-amber-600 dark:text-amber-400 mb-3">
            For demo purposes, use this OTP: <span class="font-bold text-lg">{{ demoOtp }}</span>
          </p>
          <p class="text-xs text-amber-500 dark:text-amber-500">
            OTP expires in 5 minutes
          </p>
        </div>

        <!-- OTP Fields -->
        <div v-if="otpSent && !otpVerified" class="space-y-6">
          <div class="text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:message-text" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h4 class="font-bold text-slate-900 dark:text-white text-lg">Enter Verification Code</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400">Code sent to +91 {{ mobile }}</p>
              </div>
            </div>
          </div>

          <div class="flex justify-center gap-4">
            <input
                v-for="(digit, index) in otp"
                :key="index"
                maxlength="1"
                type="text"
                inputmode="numeric"
                class="w-14 h-14 text-center rounded-2xl border-2 border-slate-200 dark:border-slate-600 bg-white/80 dark:bg-slate-700/80 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-bold text-xl shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-105 focus:scale-110"
                v-model="otp[index]"
                @input="focusNext(index, $event)"
                @keydown="handleOtpKeydown(index, $event)"
            />
          </div>

          <div v-if="otpError" class="flex items-center gap-2 text-sm text-red-500 justify-center bg-red-50/80 dark:bg-red-900/20 px-6 py-4 rounded-2xl border border-red-200/60 dark:border-red-800/60 backdrop-blur-sm">
            <Icon name="mdi:alert-circle" class="w-5 h-5" />
            <span class="font-medium">{{ otpError }}</span>
          </div>

          <button
              type="button"
              @click="verifyOtp"
              class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
              :disabled="verifyingOtp || otp.join('').length < 6"
          >
            <Icon name="mdi:loading" v-if="verifyingOtp" class="w-6 h-6 inline animate-spin mr-3" />
            <Icon name="mdi:shield-check" v-else class="w-6 h-6 inline mr-3 group-hover:scale-110 transition-transform duration-300" />
            <span>{{ verifyingOtp ? 'Verifying...' : 'Verify OTP' }}</span>
          </button>
        </div>

        <!-- OTP Verified -->
        <div v-if="otpVerified" class="text-center space-y-6">
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 p-8 rounded-3xl border-2 border-green-200/60 dark:border-green-800/60 backdrop-blur-sm">
            <div class="flex items-center justify-center gap-4 mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-full flex items-center justify-center shadow-2xl">
                <Icon name="mdi:check-bold" class="w-8 h-8 text-white" />
              </div>
              <div>
                <h4 class="text-xl font-bold text-green-600 dark:text-green-400">OTP Verified!</h4>
                <p class="text-green-600/80 dark:text-green-400/80">Your mobile number is verified</p>
              </div>
            </div>
          </div>

          <button
              type="submit"
              class="w-full px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
              :disabled="saving"
          >
            <Icon name="mdi:loading" v-if="saving" class="w-6 h-6 inline animate-spin mr-3" />
            <Icon name="mdi:content-save" v-else class="w-6 h-6 inline mr-3 group-hover:scale-110 transition-transform duration-300" />
            <span>{{ saving ? 'Saving Mobile Number...' : 'Save Mobile Number' }}</span>
          </button>
        </div>

      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Composables
const { $toast } = useNuxtApp()
const config = useRuntimeConfig()
const { user, refreshUser } = useSanctum()

// Emits
const emit = defineEmits<{
  success: []
}>()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

// State
const mobile = ref('')
const otp = ref(['', '', '', '', '', ''])
const otpSent = ref(false)
const otpVerified = ref(false)
const otpSending = ref(false)
const verifyingOtp = ref(false)
const saving = ref(false)
const countdown = ref(0)
const showSuccessModal = ref(false)
const updatedMobile = ref('')
const demoOtp = ref<string | null>(null)
let countdownInterval: any = null
const fieldError = ref('')
const otpError = ref('')
const canSendOtp = ref(false)
let originalMobile = ''

// Lifecycle
onMounted(() => {
  if (user.value?.mobile) {
    mobile.value = user.value.mobile
    originalMobile = user.value.mobile
  }

  // Initialize GSAP animations
  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 25,
      repeat: -1,
      ease: 'none'
    })
  }
})

// Methods
async function onMobileBlur() {
  const value = mobile.value.trim()
  if (value === originalMobile) {
    resetOtpState()
    return
  }

  const regex = /^[6-9]\d{9}$/
  if (!regex.test(value)) {
    fieldError.value = 'Enter a valid 10-digit Indian mobile number'
    canSendOtp.value = false
    return
  }

  fieldError.value = ''
  resetOtpState()

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/has_contact`, {
      method: 'POST',
      body: { type: 'mobile', value },
    })
    canSendOtp.value = !res.data.exists
    if (res.data.exists) fieldError.value = 'Mobile number already in use'
  } catch {
    fieldError.value = 'Failed to check mobile availability'
    canSendOtp.value = false
  }
}

function resetOtpState() {
  otpSent.value = false
  otpVerified.value = false
  otpError.value = ''
  canSendOtp.value = false
  demoOtp.value = null
  otp.value = ['', '', '', '', '', '']

  if (countdownInterval) {
    clearInterval(countdownInterval)
    countdownInterval = null
  }
  countdown.value = 0
}

function focusNext(index: number, event: any) {
  if (event.inputType !== 'deleteContentBackward') {
    const next = event.target.nextElementSibling
    if (next) next.focus()
  }
}

function handleOtpKeydown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace' && otp.value[index] === '') {
    const prev = event.target?.previousElementSibling as HTMLInputElement
    if (prev) {
      prev.focus()
      prev.select()
    }
  }
}

async function sendOtp() {
  otpError.value = ''
  otpVerified.value = false
  otpSending.value = true

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { type: 'mobile', value: mobile.value.trim() },
    })

    // Handle API response structure
    if (res.status === 'success') {
      otpSent.value = true
      canSendOtp.value = false
      countdown.value = 60

      // Store demo OTP if available
      if (res.demo && res.otp) {
        demoOtp.value = res.otp.toString()
      }

      // Start countdown
      countdownInterval = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
          clearInterval(countdownInterval)
          canSendOtp.value = true
          otpSending.value = false
        }
      }, 1000)

      $toast.success({
        title: 'OTP Sent Successfully',
        message: res.message || 'A 6-digit verification code has been sent to your mobile.'
      })
    }
  } catch (err: any) {
    otpError.value = err?.data?.message || 'Failed to send OTP'
    otpSending.value = false
    $toast.error({
      title: 'Error Sending OTP',
      message: otpError.value
    })
  } finally {
    otpSending.value = false
  }
}

async function verifyOtp() {
  const code = otp.value.join('')
  if (code.length !== 6) {
    otpError.value = 'Please enter the complete 6-digit OTP'
    return
  }

  verifyingOtp.value = true
  otpError.value = ''

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { type: 'mobile', value: mobile.value.trim(), otp: code },
    })

    // Handle verification response
    otpVerified.value = res.data?.valid === true
    if (!otpVerified.value) {
      otpError.value = 'Invalid verification code. Please try again.'
    }
    if (otpVerified.value) {
      otpSent.value = false
      $toast.success({
        title: 'OTP Verified',
        message: 'Your mobile number has been verified successfully!'
      })
    }
  } catch (err: any) {
    otpError.value = err?.data?.message || 'OTP verification failed'
    $toast.error({
      title: 'Verification Failed',
      message: otpError.value
    })
  } finally {
    verifyingOtp.value = false
  }
}

async function saveMobile() {
  if (!otpVerified.value) return

  saving.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/contact`, {
      method: 'PUT',
      body: {
        mobile: mobile.value.trim(),
        type: 'mobile',
        otp: otp.value.join(''),
      },
    })

    // Handle success response
    if (res.success) {
      updatedMobile.value = mobile.value.trim()
      showSuccessModal.value = true

      // Reset form state
      resetForm()

      // Refresh user data
      await refreshUser()

      $toast.success({
        title: 'Mobile Updated Successfully!',
        message: res.message || 'Your mobile number has been updated in your profile.'
      })
    }
  } catch (err: any) {
    $toast.error({
      title: 'Update Failed!',
      message: err?.data?.message || 'Failed to update mobile number'
    })
  } finally {
    saving.value = false
  }
}

function resetForm() {
  otpVerified.value = false
  otp.value = ['', '', '', '', '', '']
  originalMobile = mobile.value.trim()
  canSendOtp.value = false
  resetOtpState()
}

function closeSuccessModal() {
  showSuccessModal.value = false
  emit('success')
}
</script>
