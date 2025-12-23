<script setup lang="ts">
/**
 * Change Wallet PIN Page
 * Change PIN with OTP verification
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const router = useRouter()
const toast = useToast()
const { user } = useSanctum()
const { wallet, fetchWallet, requestPinChangeOtp, changePin } = useWallet()

const step = ref(1) // 1: Request OTP, 2: Verify & Change
const loading = ref(false)
const otpSent = ref(false)
const selectedMethod = ref<'mobile' | 'email'>('mobile')
const maskedCredential = ref('')
const resendTimer = ref(0)

// Form data
const formData = ref({
  otp: '',
  new_pin: '',
  confirm_pin: ''
})

// PIN inputs
const otpInputs = ref<HTMLInputElement[]>([])
const pinInputs = ref<HTMLInputElement[]>([])
const confirmPinInputs = ref<HTMLInputElement[]>([])

// Resend timer
let timerInterval: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  await fetchWallet()

  // Redirect if PIN not set
  if (wallet.value?.requires_pin_setup) {
    toast.add({
      title: 'Setup PIN First',
      description: 'Please set up your wallet PIN before changing it',
      color: 'warning'
    })
    router.push('/wallet/setup-pin')
  }
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

// Start resend timer
const startResendTimer = () => {
  resendTimer.value = 60
  timerInterval = setInterval(() => {
    resendTimer.value--
    if (resendTimer.value <= 0 && timerInterval) {
      clearInterval(timerInterval)
    }
  }, 1000)
}

// Request OTP
const handleRequestOtp = async () => {
  loading.value = true
  const result = await requestPinChangeOtp(selectedMethod.value)
  loading.value = false

  if (result.success) {
    otpSent.value = true
    maskedCredential.value = result.data?.credential_masked || ''
    startResendTimer()
    step.value = 2
    toast.add({
      title: 'OTP Sent',
      description: `Verification code sent to your ${selectedMethod.value}`,
      color: 'success'
    })
    nextTick(() => {
      otpInputs.value[0]?.focus()
    })
  } else {
    toast.add({
      title: 'Failed',
      description: result.message,
      color: 'error'
    })
  }
}

// Handle OTP input
const handleOtpInput = (index: number, event: Event) => {
  const target = event.target as HTMLInputElement
  const value = target.value.replace(/\D/g, '')
  target.value = value

  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }

  formData.value.otp = otpInputs.value.map(input => input.value).join('')
}

// Handle PIN input
const handlePinInput = (index: number, event: Event, isConfirm = false) => {
  const target = event.target as HTMLInputElement
  const value = target.value.replace(/\D/g, '')
  target.value = value

  const inputs = isConfirm ? confirmPinInputs.value : pinInputs.value

  if (value && index < 5) {
    inputs[index + 1]?.focus()
  }

  if (isConfirm) {
    formData.value.confirm_pin = confirmPinInputs.value.map(input => input.value).join('')
  } else {
    formData.value.new_pin = pinInputs.value.map(input => input.value).join('')
  }
}

// Handle backspace
const handleKeydown = (index: number, event: KeyboardEvent, inputs: HTMLInputElement[]) => {
  if (event.key === 'Backspace' && !inputs[index].value && index > 0) {
    inputs[index - 1]?.focus()
  }
}

// Validate form
const validateForm = () => {
  if (formData.value.otp.length !== 6) {
    toast.add({
      title: 'Invalid OTP',
      description: 'Please enter the 6-digit OTP',
      color: 'error'
    })
    return false
  }

  if (formData.value.new_pin.length !== 6) {
    toast.add({
      title: 'Invalid PIN',
      description: 'PIN must be 6 digits',
      color: 'error'
    })
    return false
  }

  if (formData.value.new_pin !== formData.value.confirm_pin) {
    toast.add({
      title: 'PIN Mismatch',
      description: 'PINs do not match',
      color: 'error'
    })
    return false
  }

  // Check for weak PINs
  const weakPins = ['123456', '654321', '111111', '000000', '123123']
  if (weakPins.includes(formData.value.new_pin)) {
    toast.add({
      title: 'Weak PIN',
      description: 'Please choose a stronger PIN',
      color: 'error'
    })
    return false
  }

  return true
}

// Submit change
const handleSubmit = async () => {
  if (!validateForm()) return

  loading.value = true
  const result = await changePin({
    otp: formData.value.otp,
    method: selectedMethod.value,
    new_pin: formData.value.new_pin,
    confirm_pin: formData.value.confirm_pin
  })
  loading.value = false

  if (result.success) {
    toast.add({
      title: 'PIN Changed',
      description: 'Your wallet PIN has been updated successfully',
      color: 'success'
    })
    router.push('/wallet')
  } else {
    toast.add({
      title: 'Failed',
      description: result.message,
      color: 'error'
    })

    // Clear OTP on failure
    formData.value.otp = ''
    otpInputs.value.forEach(input => input.value = '')
    otpInputs.value[0]?.focus()
  }
}

// Check if method available
const hasMobile = computed(() => !!user.value?.mobile)
const hasEmail = computed(() => !!user.value?.email)
</script>

<template>
  <div class="max-w-lg mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-amber-600 to-orange-600 p-6 text-white">
        <div class="flex items-center gap-4">
          <NuxtLink
            to="/wallet"
            class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors"
          >
            <UIcon
              name="i-lucide-arrow-left"
              class="w-5 h-5"
            />
          </NuxtLink>
          <div>
            <h1 class="text-xl font-bold">
              Change PIN
            </h1>
            <p class="text-amber-100 text-sm">
              Update your wallet security PIN
            </p>
          </div>
        </div>
      </div>

      <!-- Step 1: Select Method & Request OTP -->
      <div
        v-if="step === 1"
        class="p-6 space-y-6"
      >
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-info"
              class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"
            />
            <p class="text-sm text-blue-700 dark:text-blue-300">
              For security, we'll send a verification code to confirm it's you before changing your PIN.
            </p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            Send verification code via
          </label>
          <div class="space-y-3">
            <div
              v-if="hasMobile"
              :class="[
                'p-4 border-2 rounded-xl cursor-pointer transition-all flex items-center gap-4',
                selectedMethod === 'mobile'
                  ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
              @click="selectedMethod = 'mobile'"
            >
              <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-smartphone"
                  class="w-5 h-5 text-slate-600 dark:text-slate-400"
                />
              </div>
              <div class="flex-1">
                <p class="font-medium text-slate-900 dark:text-white">
                  Mobile SMS
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  +91 {{ user?.mobile?.substring(0, 3) }}****{{ user?.mobile?.substring(7) }}
                </p>
              </div>
              <div
                v-if="selectedMethod === 'mobile'"
                class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center"
              >
                <UIcon
                  name="i-lucide-check"
                  class="w-4 h-4 text-white"
                />
              </div>
            </div>

            <div
              v-if="hasEmail"
              :class="[
                'p-4 border-2 rounded-xl cursor-pointer transition-all flex items-center gap-4',
                selectedMethod === 'email'
                  ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
              @click="selectedMethod = 'email'"
            >
              <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-mail"
                  class="w-5 h-5 text-slate-600 dark:text-slate-400"
                />
              </div>
              <div class="flex-1">
                <p class="font-medium text-slate-900 dark:text-white">
                  Email
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  {{ user?.email?.substring(0, 3) }}***@{{ user?.email?.split('@')[1] }}
                </p>
              </div>
              <div
                v-if="selectedMethod === 'email'"
                class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center"
              >
                <UIcon
                  name="i-lucide-check"
                  class="w-4 h-4 text-white"
                />
              </div>
            </div>
          </div>
        </div>

        <UButton
          color="primary"
          size="lg"
          block
          :loading="loading"
          @click="handleRequestOtp"
        >
          Send Verification Code
        </UButton>
      </div>

      <!-- Step 2: Enter OTP & New PIN -->
      <div
        v-if="step === 2"
        class="p-6 space-y-6"
      >
        <!-- OTP Entry -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Enter OTP sent to {{ maskedCredential }}
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'otp-' + i"
              :ref="el => otpInputs[i-1] = el as HTMLInputElement"
              type="text"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
              @input="handleOtpInput(i-1, $event)"
              @keydown="handleKeydown(i-1, $event, otpInputs)"
            >
          </div>
          <div class="text-center mt-3">
            <button
              v-if="resendTimer > 0"
              class="text-sm text-slate-500 dark:text-slate-400"
              disabled
            >
              Resend in {{ resendTimer }}s
            </button>
            <button
              v-else
              class="text-sm text-amber-600 dark:text-amber-400 hover:underline"
              :disabled="loading"
              @click="handleRequestOtp"
            >
              Resend OTP
            </button>
          </div>
        </div>

        <!-- New PIN -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Enter New 6-digit PIN
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'pin-' + i"
              :ref="el => pinInputs[i-1] = el as HTMLInputElement"
              type="password"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event)"
              @keydown="handleKeydown(i-1, $event, pinInputs)"
            >
          </div>
        </div>

        <!-- Confirm PIN -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Confirm New PIN
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'confirm-' + i"
              :ref="el => confirmPinInputs[i-1] = el as HTMLInputElement"
              type="password"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event, true)"
              @keydown="handleKeydown(i-1, $event, confirmPinInputs)"
            >
          </div>
        </div>

        <div class="flex gap-3">
          <UButton
            variant="outline"
            color="neutral"
            size="lg"
            class="flex-1"
            @click="step = 1"
          >
            Back
          </UButton>
          <UButton
            color="primary"
            size="lg"
            class="flex-1"
            :loading="loading"
            @click="handleSubmit"
          >
            Change PIN
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
