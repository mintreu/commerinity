<template>
  <!-- Floating Background Orbs -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden">
    <div ref="orb1" class="absolute top-20 right-16 w-32 h-32 bg-gradient-to-r from-red-400/10 to-pink-400/10 dark:from-red-400/5 dark:to-pink-400/5 rounded-full blur-2xl opacity-60"></div>
    <div ref="orb2" class="absolute bottom-20 left-16 w-24 h-24 bg-gradient-to-r from-orange-400/8 to-red-400/8 dark:from-orange-400/4 dark:to-red-400/4 rounded-full blur-2xl opacity-40"></div>
  </div>

  <!-- Deletion Progress Modal -->
  <Teleport to="body">
    <div v-if="isDeletingAccount" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-lg">
      <div class="w-full max-w-lg mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
        <!-- Header -->
        <div class="p-8 text-center border-b border-red-200/50 dark:border-red-800/50">
          <div class="w-20 h-20 bg-gradient-to-br from-red-600 to-pink-600 rounded-3xl flex items-center justify-center shadow-2xl mx-auto mb-6">
            <Icon :name="deletionProgress === 100 ? 'mdi:check-bold' : 'mdi:loading'"
                  class="w-10 h-10 text-white"
                  :class="{ 'animate-spin': deletionProgress < 100 }" />
          </div>
          <h3 class="text-3xl font-bold mb-2"
              :class="deletionProgress === 100 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ deletionProgress === 100 ? 'Account Deleted Successfully' : 'Deleting Account' }}
          </h3>
          <p class="text-slate-500 dark:text-slate-400 text-lg">{{ deletionStatus }}</p>
        </div>

        <!-- Progress Body -->
        <div class="p-8">
          <!-- Progress Bar -->
          <div class="w-full bg-red-200/50 dark:bg-red-800/50 rounded-full h-6 overflow-hidden mb-6">
            <div
                class="h-full rounded-full transition-all duration-1000 ease-out"
                :class="deletionProgress === 100 ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-red-500 to-pink-500'"
                :style="{ width: deletionProgress + '%' }"
            ></div>
          </div>

          <!-- Progress Percentage -->
          <div class="text-center text-2xl font-bold mb-6"
               :class="deletionProgress === 100 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ deletionProgress }}%
          </div>

          <!-- Warning or Success Message -->
          <div v-if="deletionProgress < 100" class="p-4 bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 rounded-xl">
            <div class="flex items-center gap-2 text-sm text-amber-600 dark:text-amber-400">
              <Icon name="mdi:alert" class="w-5 h-5" />
              <span class="font-medium">This process cannot be stopped once started</span>
            </div>
          </div>

          <div v-else class="space-y-4">
            <!-- Success Message -->
            <div class="p-4 bg-green-50/80 dark:bg-green-900/20 border border-green-200/60 dark:border-green-800/60 rounded-xl">
              <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                <Icon name="mdi:check-circle" class="w-5 h-5" />
                <span class="font-medium">Your account has been permanently deleted</span>
              </div>
            </div>

            <!-- Redirect Notice -->
            <div class="p-4 bg-blue-50/80 dark:bg-blue-900/20 border border-blue-200/60 dark:border-blue-800/60 rounded-xl">
              <div class="flex items-center justify-between text-sm text-blue-600 dark:text-blue-400">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:home" class="w-5 h-5" />
                  <span class="font-medium">Redirecting to homepage in {{ redirectCountdown }}s...</span>
                </div>
                <button
                    @click="redirectNow"
                    class="px-3 py-1 bg-blue-100 dark:bg-blue-800 rounded-lg font-medium hover:bg-blue-200 dark:hover:bg-blue-700 transition-colors"
                >
                  Go Now
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <div class="relative z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
    <!-- Header Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 via-pink-500/5 to-orange-500/5 opacity-70"></div>

    <div class="relative z-10 p-8 space-y-8">
      <div class="flex items-center gap-6">
        <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
          <Icon name="mdi:account-remove" class="w-6 h-6 text-white" />
        </div>
        <div>
          <h2 class="text-2xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent">Delete Account</h2>
          <div class="w-20 h-1 bg-gradient-to-r from-red-600 to-pink-600 rounded-full mt-2"></div>
        </div>
      </div>

      <!-- Warning Section -->
      <div class="bg-red-50/80 dark:bg-red-900/20 border-2 border-red-200/60 dark:border-red-800/60 rounded-2xl p-8 backdrop-blur-sm">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-red-100/80 dark:bg-red-900/50 rounded-2xl flex items-center justify-center">
            <Icon name="mdi:alert-octagon" class="w-6 h-6 text-red-600 dark:text-red-400" />
          </div>
          <div>
            <h3 class="text-xl font-bold text-red-700 dark:text-red-300">⚠️ Critical Warning</h3>
            <p class="text-red-600 dark:text-red-400 mt-1">This action is permanent and cannot be undone</p>
          </div>
        </div>

        <div class="space-y-4 text-sm text-red-600 dark:text-red-400">
          <div class="flex items-start gap-3">
            <Icon name="mdi:delete-forever" class="w-5 h-5 mt-0.5 flex-shrink-0" />
            <span><strong>All your data will be permanently deleted</strong> including profile, transactions, and activity logs</span>
          </div>
          <div class="flex items-start gap-3">
            <Icon name="mdi:account-off" class="w-5 h-5 mt-0.5 flex-shrink-0" />
            <span><strong>Account cannot be reactivated</strong> - you'll need to create a completely new account</span>
          </div>
          <div class="flex items-start gap-3">
            <Icon name="mdi:backup-restore" class="w-5 h-5 mt-0.5 flex-shrink-0" />
            <span><strong>No data recovery possible</strong> - we cannot restore any information after deletion</span>
          </div>
          <div class="flex items-start gap-3">
            <Icon name="mdi:clock-alert" class="w-5 h-5 mt-0.5 flex-shrink-0" />
            <span><strong>Immediate effect</strong> - deletion process starts immediately after confirmation</span>
          </div>
        </div>
      </div>

      <!-- Mobile Verification Required -->
      <div v-if="!user?.mobile" class="bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 rounded-2xl p-6 backdrop-blur-sm">
        <div class="flex items-center gap-3 mb-3">
          <Icon name="mdi:phone-alert" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
          <h4 class="font-bold text-amber-700 dark:text-amber-300">Mobile Number Required</h4>
        </div>
        <p class="text-sm text-amber-600 dark:text-amber-400">
          To delete your account, you must verify your identity with a mobile OTP. Please add and verify your mobile number first.
        </p>
      </div>

      <!-- Step 1: Initial Confirmation -->
      <div v-if="!otpSent && !isDeletingAccount && user?.mobile" class="space-y-6">
        <div class="bg-slate-50/80 dark:bg-slate-700/50 rounded-2xl p-6 border border-slate-200/60 dark:border-slate-600/60 backdrop-blur-sm">
          <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
            <Icon name="mdi:clipboard-check" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            Before You Continue
          </h4>

          <div class="space-y-3">
            <label class="flex items-start gap-3 cursor-pointer">
              <input
                  type="checkbox"
                  v-model="confirmations.dataLoss"
                  class="mt-1 w-5 h-5 text-red-600 rounded border-slate-300 dark:border-slate-600 focus:ring-red-500"
              >
              <span class="text-sm text-slate-600 dark:text-slate-400">
                I understand that all my account data will be permanently deleted and cannot be recovered
              </span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
              <input
                  type="checkbox"
                  v-model="confirmations.noReactivation"
                  class="mt-1 w-5 h-5 text-red-600 rounded border-slate-300 dark:border-slate-600 focus:ring-red-500"
              >
              <span class="text-sm text-slate-600 dark:text-slate-400">
                I understand that my account cannot be reactivated and I'll need to create a new account if I want to return
              </span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer">
              <input
                  type="checkbox"
                  v-model="confirmations.finalDecision"
                  class="mt-1 w-5 h-5 text-red-600 rounded border-slate-300 dark:border-slate-600 focus:ring-red-500"
              >
              <span class="text-sm text-slate-600 dark:text-slate-400">
                This is my final decision and I want to proceed with account deletion
              </span>
            </label>
          </div>
        </div>

        <div class="flex justify-center">
          <button
              @click="sendDeleteOtp"
              :disabled="!allConfirmationsChecked || otpSending"
              class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
          >
            <Icon name="mdi:loading" v-if="otpSending" class="w-6 h-6 animate-spin" />
            <Icon name="mdi:send" v-else class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" />
            <span>{{ otpSending ? 'Sending OTP...' : 'Send Verification OTP' }}</span>
          </button>
        </div>
      </div>

      <!-- Step 2: OTP Verification -->
      <div v-if="otpSent && !isDeletingAccount" class="space-y-6">
        <!-- Demo OTP Notice -->
        <div v-if="demoOtp" class="bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 rounded-2xl p-6 backdrop-blur-sm">
          <div class="flex items-center gap-3 mb-3">
            <Icon name="mdi:information" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            <h4 class="font-bold text-amber-700 dark:text-amber-300">Demo Mode</h4>
          </div>
          <p class="text-sm text-amber-600 dark:text-amber-400 mb-3">
            For demo purposes, use this OTP: <span class="font-bold text-lg">{{ demoOtp }}</span>
          </p>
        </div>

        <div class="text-center">
          <div class="flex items-center justify-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:message-text" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h4 class="font-bold text-slate-900 dark:text-white text-lg">Final Verification</h4>
              <p class="text-sm text-slate-500 dark:text-slate-400">Code sent to +91 {{ user?.mobile }}</p>
            </div>
          </div>

          <div class="flex justify-center gap-4 mb-6">
            <input
                v-for="(digit, index) in otp"
                :key="index"
                maxlength="1"
                type="text"
                inputmode="numeric"
                class="w-14 h-14 text-center rounded-2xl border-2 border-slate-200 dark:border-slate-600 bg-white/80 dark:bg-slate-700/80 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent font-bold text-xl shadow-lg backdrop-blur-sm transition-all duration-300 hover:scale-105 focus:scale-110"
                v-model="otp[index]"
                @input="focusNext(index, $event)"
                @keydown="handleOtpKeydown(index, $event)"
            />
          </div>

          <div v-if="otpError" class="flex items-center gap-2 text-sm text-red-500 justify-center bg-red-50/80 dark:bg-red-900/20 px-6 py-4 rounded-2xl border border-red-200/60 dark:border-red-800/60 backdrop-blur-sm mb-6">
            <Icon name="mdi:alert-circle" class="w-5 h-5" />
            <span class="font-medium">{{ otpError }}</span>
          </div>

          <div class="space-y-4">
            <button
                @click="verifyDeleteOtp"
                :disabled="verifyingOtp || otp.join('').length < 6"
                class="w-full px-8 py-4 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-bold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 group"
            >
              <Icon name="mdi:loading" v-if="verifyingOtp" class="w-6 h-6 inline animate-spin mr-3" />
              <Icon name="mdi:delete-forever" v-else class="w-6 h-6 inline mr-3 group-hover:scale-110 transition-transform duration-300" />
              <span>{{ verifyingOtp ? 'Verifying & Deleting...' : 'Verify & Delete Account' }}</span>
            </button>

            <button
                @click="cancelDeletion"
                :disabled="verifyingOtp"
                class="w-full px-8 py-3 bg-slate-100/80 dark:bg-slate-700/80 hover:bg-slate-200/80 dark:hover:bg-slate-600/80 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel Deletion
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
const { user, logout } = useSanctum()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

// State
const confirmations = ref({
  dataLoss: false,
  noReactivation: false,
  finalDecision: false
})
const otpSent = ref(false)
const otpSending = ref(false)
const verifyingOtp = ref(false)
const isDeletingAccount = ref(false)
const deletionProgress = ref(0)
const deletionStatus = ref('Preparing account deletion...')
const redirectCountdown = ref(5)
const otp = ref(['', '', '', '', '', ''])
const otpError = ref('')
const demoOtp = ref<string | null>(null)

let redirectTimer: NodeJS.Timeout | null = null

// Computed
const allConfirmationsChecked = computed(() => {
  return Object.values(confirmations.value).every(Boolean)
})

// Lifecycle
onMounted(() => {
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

onUnmounted(() => {
  // Cleanup timers
  if (redirectTimer) {
    clearInterval(redirectTimer)
  }
})

// Methods
async function sendDeleteOtp() {
  if (!allConfirmationsChecked.value) return

  otpSending.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: {
        type: 'mobile',
        value: user.value?.mobile,
        purpose: 'account_deletion'
      },
    })

    if (res.status === 'success') {
      otpSent.value = true

      if (res.demo && res.otp) {
        demoOtp.value = res.otp.toString()
      }

      $toast.success({
        title: 'Verification OTP Sent',
        message: res.message || 'Please check your mobile for the deletion verification code.'
      })
    }
  } catch (err: any) {
    $toast.error({
      title: 'Failed to Send OTP',
      message: err?.data?.message || 'Could not send verification OTP. Please try again.'
    })
  } finally {
    otpSending.value = false
  }
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

async function verifyDeleteOtp() {
  const code = otp.value.join('')
  if (code.length !== 6) {
    otpError.value = 'Please enter the complete 6-digit OTP'
    return
  }

  verifyingOtp.value = true
  otpError.value = ''

  try {
    // Start the deletion process with progress
    isDeletingAccount.value = true
    deletionProgress.value = 0

    // Simulate deletion progress for UX
    const deletionSteps = [
      { progress: 15, status: 'Verifying OTP and identity...' },
      { progress: 30, status: 'Backing up account for compliance...' },
      { progress: 45, status: 'Removing personal information...' },
      { progress: 60, status: 'Deleting transaction history...' },
      { progress: 75, status: 'Clearing security settings...' },
      { progress: 90, status: 'Finalizing account deletion...' },
      { progress: 100, status: 'Account deletion completed successfully!' }
    ]

    for (const step of deletionSteps) {
      await new Promise(resolve => setTimeout(resolve, 1200))
      deletionProgress.value = step.progress
      deletionStatus.value = step.status
    }

    // Make actual deletion API call
    const response = await useSanctumFetch(`${config.public.apiBase}/account/delete`, {
      method: 'DELETE',
      body: {
        mobile: user.value?.mobile,
        otp: code
      },
    })

    // Show success message
    if (response.success) {
      $toast.success({
        title: 'Account Deleted',
        message: response.message || 'Your account has been permanently deleted.'
      })

      // Start countdown for redirect
      redirectCountdown.value = 5
      redirectTimer = setInterval(() => {
        redirectCountdown.value--
        if (redirectCountdown.value <= 0) {
          redirectNow()
        }
      }, 1000)
    }

  } catch (err: any) {
    isDeletingAccount.value = false
    deletionProgress.value = 0

    const errorMessage = err?.data?.message || 'Failed to delete account. Please try again.'
    otpError.value = errorMessage

    $toast.error({
      title: 'Deletion Failed',
      message: errorMessage
    })
  } finally {
    verifyingOtp.value = false
  }
}

async function redirectNow() {
  try {
    // Clear the redirect timer
    if (redirectTimer) {
      clearInterval(redirectTimer)
      redirectTimer = null
    }

    // Show final toast
    $toast.info({
      title: 'Goodbye!',
      message: 'Thank you for using our service. You are being redirected...'
    })

    // Logout user (clears all authentication state and cookies)
    await logout()

    // Small delay to ensure logout completes
    await new Promise(resolve => setTimeout(resolve, 500))

    // Navigate to homepage
    await navigateTo('/', { replace: true })

  } catch (error) {
    console.error('Error during logout/redirect:', error)

    // Fallback: force redirect even if logout fails
    if (process.client) {
      window.location.href = '/'
    }
  }
}

function cancelDeletion() {
  // Clear any ongoing timers
  if (redirectTimer) {
    clearInterval(redirectTimer)
    redirectTimer = null
  }

  // Reset all state
  otpSent.value = false
  otp.value = ['', '', '', '', '', '']
  otpError.value = ''
  demoOtp.value = null
  isDeletingAccount.value = false
  deletionProgress.value = 0
  redirectCountdown.value = 5
  confirmations.value = {
    dataLoss: false,
    noReactivation: false,
    finalDecision: false
  }

  $toast.info({
    title: 'Deletion Cancelled',
    message: 'Account deletion has been cancelled.'
  })
}
</script>
