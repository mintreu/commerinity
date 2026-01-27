<template>
  <!-- Success Modal -->
  <Teleport to="body">
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="closeSuccessModal"
    >
      <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
        <!-- Success Header -->
        <div class="flex items-center gap-6 p-8 border-b border-blue-200/50 dark:border-blue-800/50">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl">
            <UIcon
              name="i-lucide-check-circle"
              class="w-8 h-8 text-white"
            />
          </div>
          <div>
            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400">
              Success!
            </h3>
            <p class="text-slate-500 dark:text-slate-400 mt-1">
              Email address updated
            </p>
          </div>
        </div>

        <!-- Success Body -->
        <div class="p-8">
          <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
            Your email address has been successfully updated to <span class="font-bold text-slate-900 dark:text-white">{{ updatedEmail }}</span>
          </p>

          <button
            class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            @click="closeSuccessModal"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <form
    class="relative z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 overflow-hidden"
    @submit.prevent="saveEmail"
  >
    <!-- Header Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-indigo-500/5 to-purple-500/5 opacity-70" />

    <div class="relative z-10 grid grid-cols-1 md:grid-cols-[280px_1fr] divide-y md:divide-y-0 md:divide-x divide-slate-200/50 dark:divide-slate-700/50">
      <!-- Title & Description -->
      <div class="p-8 bg-gradient-to-br from-blue-50/50 to-indigo-50/50 dark:from-blue-900/20 dark:to-indigo-900/20 backdrop-blur-sm">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
            <UIcon
              name="i-lucide-mail"
              class="w-6 h-6 text-white"
            />
          </div>
          <div>
            <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              Email Address
            </h2>
            <div class="w-16 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full mt-2" />
          </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
          Update your email address and verify it with OTP for secure access to your account.
        </p>

        <!-- Current Email Display -->
        <div class="mt-6 p-4 bg-white/60 dark:bg-slate-700/60 rounded-2xl border border-slate-200/50 dark:border-slate-600/50 backdrop-blur-sm">
          <div class="flex items-center gap-3">
            <UIcon
              name="i-lucide-mail-check"
              class="w-5 h-5 text-blue-600 dark:text-blue-400"
            />
            <div>
              <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                Current
              </p>
              <p class="font-bold text-slate-900 dark:text-white">
                {{ currentUser?.email || 'Not set' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Email Input & OTP -->
      <div class="p-8 space-y-8">
        <!-- Email Input -->
        <div class="space-y-3">
          <label
            for="email"
            class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300"
          >
            <UIcon
              name="i-lucide-mail"
              class="w-4 h-4"
            />
            <span>New Email Address</span>
            <span class="text-red-500">*</span>
          </label>

          <div class="relative group focus-within:scale-[1.02] transition-transform duration-300">
            <UIcon
              name="i-lucide-mail"
              class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-blue-500 transition-colors duration-300"
            />
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="you@example.com"
              class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 font-semibold"
              :class="{
                'border-red-500 bg-red-50 dark:bg-red-900/20': fieldError,
                'border-blue-300 bg-blue-50 dark:bg-blue-900/10': canSendOtp && !fieldError
              }"
              @blur="onEmailBlur"
            >
          </div>

          <div
            v-if="fieldError"
            class="flex items-center gap-2 text-sm text-red-500 bg-red-50/80 dark:bg-red-900/20 px-4 py-3 rounded-xl border border-red-200/60 dark:border-red-800/60 backdrop-blur-sm"
          >
            <UIcon
              name="i-lucide-alert-circle"
              class="w-5 h-5"
            />
            <span class="font-medium">{{ fieldError }}</span>
          </div>

          <div
            v-else-if="canSendOtp"
            class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 bg-blue-50/80 dark:bg-blue-900/20 px-4 py-3 rounded-xl border border-blue-200/60 dark:border-blue-800/60 backdrop-blur-sm"
          >
            <UIcon
              name="i-lucide-check-circle"
              class="w-5 h-5"
            />
            <span class="font-medium">Email address is available</span>
          </div>
        </div>

        <!-- Send OTP Button -->
        <div
          v-if="canSendOtp && !otpVerified"
          class="flex justify-end"
        >
          <button
            type="button"
            :disabled="otpSending || countdown > 0"
            class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
            @click="sendOtp"
          >
            <UIcon
              :name="otpSending ? 'i-lucide-loader-2' : 'i-lucide-send'"
              :class="{ 'w-5 h-5 animate-spin': otpSending, 'w-5 h-5 group-hover:rotate-12 transition-transform duration-300': !otpSending }"
            />
            <span>{{ countdown > 0 ? `Resend OTP (${countdown}s)` : 'Send OTP' }}</span>
          </button>
        </div>

        <!-- Demo OTP Notice -->
        <div
          v-if="otpSent && demoOtp"
          class="bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 rounded-2xl p-6 backdrop-blur-sm"
        >
          <div class="flex items-center gap-3 mb-3">
            <UIcon
              name="i-lucide-info"
              class="w-6 h-6 text-amber-600 dark:text-amber-400"
            />
            <h4 class="font-bold text-amber-700 dark:text-amber-300">
              Demo Mode
            </h4>
          </div>
          <p class="text-sm text-amber-600 dark:text-amber-400 mb-3">
            For demo purposes, use this OTP: <span class="font-bold text-lg">{{ demoOtp }}</span>
          </p>
          <p class="text-xs text-amber-500 dark:text-amber-500">
            OTP expires in 5 minutes
          </p>
        </div>

        <!-- OTP Fields -->
        <div
          v-if="otpSent && !otpVerified"
          class="space-y-6"
        >
          <div class="text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                <UIcon
                  name="i-lucide-mail-open"
                  class="w-6 h-6 text-white"
                />
              </div>
              <div>
                <h4 class="font-bold text-slate-900 dark:text-white text-lg">
                  Check Your Email
                </h4>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Code sent to {{ email }}
                </p>
              </div>
            </div>
          </div>

          <div class="flex justify-center gap-4">
            <input
              v-for="(digit, index) in otp"
              :key="index"
              v-model="otp[index]"
              maxlength="1"
              type="text"
              inputmode="numeric"
              class="w-14 h-14 text-center rounded-2xl border-2 border-slate-200 dark:border-slate-600 bg-white/80 dark:bg-slate-700/80 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-bold text-xl shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-105 focus:scale-110"
              @input="focusNext(index, $event)"
              @keydown="handleOtpKeydown(index, $event)"
            >
          </div>

          <div
            v-if="otpError"
            class="flex items-center gap-2 text-sm text-red-500 justify-center bg-red-50/80 dark:bg-red-900/20 px-6 py-4 rounded-2xl border border-red-200/60 dark:border-red-800/60 backdrop-blur-sm"
          >
            <UIcon
              name="i-lucide-alert-circle"
              class="w-5 h-5"
            />
            <span class="font-medium">{{ otpError }}</span>
          </div>

          <button
            type="button"
            class="w-full px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
            :disabled="verifyingOtp || otp.join('').length < 6"
            @click="verifyOtp"
          >
            <UIcon
              :name="verifyingOtp ? 'i-lucide-loader-2' : 'i-lucide-shield-check'"
              :class="{ 'w-6 h-6 inline animate-spin mr-3': verifyingOtp, 'w-6 h-6 inline mr-3 group-hover:scale-110 transition-transform duration-300': !verifyingOtp }"
            />
            <span>{{ verifyingOtp ? 'Verifying...' : 'Verify OTP' }}</span>
          </button>
        </div>

        <!-- OTP Verified -->
        <div
          v-if="otpVerified"
          class="text-center space-y-6"
        >
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 p-8 rounded-3xl border-2 border-blue-200/60 dark:border-blue-800/60 backdrop-blur-sm">
            <div class="flex items-center justify-center gap-4 mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-2xl">
                <UIcon
                  name="i-lucide-check-circle"
                  class="w-8 h-8 text-white"
                />
              </div>
              <div>
                <h4 class="text-xl font-bold text-blue-600 dark:text-blue-400">
                  Email Verified!
                </h4>
                <p class="text-blue-600/80 dark:text-blue-400/80">
                  Your email address is verified
                </p>
              </div>
            </div>
          </div>

          <button
            type="submit"
            class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
            :disabled="saving"
          >
            <UIcon
              :name="saving ? 'i-lucide-loader-2' : 'i-lucide-save'"
              :class="{ 'w-6 h-6 inline animate-spin mr-3': saving, 'w-6 h-6 inline mr-3 group-hover:scale-110 transition-transform duration-300': !saving }"
            />
            <span>{{ saving ? 'Saving Email Address...' : 'Save Email Address' }}</span>
          </button>
        </div>
      </div>
    </div>
  </form>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  title: 'Change Email'
})

const toast = useToast()
const config = useRuntimeConfig()
const { currentUser, refreshUser } = useSanctum()

// Emits
const emit = defineEmits<{
  success: []
}>()

// State
const email = ref('')
const otp = ref(['', '', '', '', ''])
const otpSent = ref(false)
const otpVerified = ref(false)
const otpSending = ref(false)
const verifyingOtp = ref(false)
const saving = ref(false)
const countdown = ref(0)
const showSuccessModal = ref(false)
const updatedEmail = ref('')
const demoOtp = ref<string | null>(null)
let countdownInterval: NodeJS.Timeout | null = null
const fieldError = ref('')
const otpError = ref('')
const canSendOtp = ref(false)
let originalEmail = ''

// Lifecycle
onMounted(() => {
  if (currentUser.value?.email) {
    originalEmail = currentUser.value.email
  }
})

// Methods
async function onEmailBlur() {
  const value = email.value.trim()
  if (value === originalEmail) {
    resetOtpState()
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(value)) {
    fieldError.value = 'Please enter a valid email address'
    canSendOtp.value = false
    return
  }

  fieldError.value = ''
  resetOtpState()
}

function resetOtpState() {
  otpSent.value = false
  otpVerified.value = false
  otpError.value = ''
  canSendOtp.value = false
  demoOtp.value = null
  otp.value = ['', '', '', '', '']

  if (countdownInterval) {
    clearInterval(countdownInterval)
    countdownInterval = null
  }
  countdown.value = 0
}

function focusNext(index: number, event: Event) {
  if ((event.target as HTMLInputElement).value && index < otp.value.length - 1) {
    const next = (event.target as HTMLInputElement).nextElementSibling as HTMLInputElement
    if (next) next.focus()
  }
}

function handleOtpKeydown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace' && otp.value[index] === '') {
    const prev = (event.target as HTMLInputElement).previousElementSibling as HTMLInputElement
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
    const res = await useSanctumFetch<{
      success: boolean
      message: string
      demo?: string
      otp?: number
    }>(`${config.public.apiBase}/api/auth/send-otp`, {
      method: 'POST',
      body: {
        type: 'email',
        value: email.value.trim()
      }
    })

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

    toast.add({
      title: 'OTP Sent Successfully',
      description: 'A 6-digit verification code has been sent to your email.',
      color: 'success'
    })
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string } }
    otpError.value = fetchError.data?.message || 'Failed to send OTP'
    otpSending.value = false
    toast.add({
      title: 'Error Sending OTP',
      description: otpError.value,
      color: 'error'
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
    const res = await useSanctumFetch<{
      success: boolean
      message: string
      valid: boolean
    }>(`${config.public.apiBase}/api/auth/verify-otp`, {
      method: 'POST',
      body: {
        type: 'email',
        value: email.value.trim(),
        otp: code
      }
    })

    otpVerified.value = res.valid === true
    if (!otpVerified.value) {
      otpError.value = 'Invalid verification code. Please try again.'
    }
    if (otpVerified.value) {
      otpSent.value = false
      toast.add({
        title: 'Email Verified',
        description: 'Your email address has been verified successfully!',
        color: 'success'
      })
    }
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string } }
    otpError.value = fetchError.data?.message || 'OTP verification failed'
    toast.add({
      title: 'Verification Failed',
      description: otpError.value,
      color: 'error'
    })
  } finally {
    verifyingOtp.value = false
  }
}

async function saveEmail() {
  if (!otpVerified.value) return

  saving.value = true
  try {
    const res = await useSanctumFetch<{
      message: string
      data: { user: Record<string, unknown> }
    }>(`${config.public.apiBase}/api/user/profile`, {
      method: 'PUT',
      body: {
        email: email.value.trim()
      }
    })

    updatedEmail.value = email.value.trim()
    showSuccessModal.value = true

    // Reset form state
    resetForm()

    // Refresh user data
    await refreshUser()

    toast.add({
      title: 'Email Updated Successfully!',
      description: res.message || 'Your email address has been updated in your profile.',
      color: 'success'
    })
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string } }
    toast.add({
      title: 'Update Failed!',
      description: fetchError.data?.message || 'Failed to update email address',
      color: 'error'
    })
  } finally {
    saving.value = false
  }
}

function resetForm() {
  otpVerified.value = false
  otp.value = ['', '', '', '', '']
  originalEmail = email.value.trim()
  canSendOtp.value = false
  resetOtpState()
}

function closeSuccessModal() {
  showSuccessModal.value = false
  emit('success')
}
</script>
