<script setup lang="ts">
/**
 * Reset Wallet PIN Page
 * Reset PIN when user forgets it, using security questions or OTP
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const router = useRouter()
const toast = useToast()
const { user } = useSanctum()
const { wallet, fetchWallet, requestPinChangeOtp, changePin, resetPinWithToken, verifySecurityQuestion, fetchUserSecurityQuestions } = useWallet()

const step = ref(1) // 1: Choose method, 2: Verify, 3: New PIN
const loading = ref(false)
const verificationMethod = ref<'otp' | 'security'>('otp')
const selectedOtpMethod = ref<'mobile' | 'email'>('mobile')
const maskedCredential = ref('')
const resendTimer = ref(0)
const verificationToken = ref('')
const securityQuestions = ref<Array<{ key: string; label: string }>>([])
const selectedQuestion = ref<string | null>(null)

// Form data
const formData = ref({
  otp: '',
  answer: '',
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
      title: 'No PIN Set',
      description: 'You need to set up your wallet PIN first',
      color: 'warning'
    })
    router.push('/wallet/setup-pin')
    return
  }

  // Load security questions
  const response = await fetchUserSecurityQuestions()
  if (response && response.questions && response.questions.length > 0) {
    securityQuestions.value = response.questions
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

// Select verification method and proceed
const selectMethod = async (method: 'otp' | 'security') => {
  verificationMethod.value = method

  if (method === 'otp') {
    step.value = 2
  } else if (method === 'security' && securityQuestions.value.length > 0) {
    selectedQuestion.value = securityQuestions.value[0].key
    step.value = 2
  } else {
    toast.add({
      title: 'No Security Questions',
      description: 'Please use OTP verification instead',
      color: 'warning'
    })
  }
}

// Request OTP
const handleRequestOtp = async () => {
  loading.value = true
  const result = await requestPinChangeOtp(selectedOtpMethod.value)
  loading.value = false

  if (result.success) {
    maskedCredential.value = result.data?.credential_masked || ''
    startResendTimer()
    toast.add({
      title: 'OTP Sent',
      description: `Verification code sent to your ${selectedOtpMethod.value}`,
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

// Verify OTP and proceed to new PIN
const handleVerifyOtp = async () => {
  if (formData.value.otp.length !== 6) {
    toast.add({
      title: 'Invalid OTP',
      description: 'Please enter the 6-digit OTP',
      color: 'error'
    })
    return
  }

  // For OTP flow, we just move to step 3 to set new PIN
  // The actual verification happens when submitting the new PIN
  step.value = 3
  nextTick(() => {
    pinInputs.value[0]?.focus()
  })
}

// Verify security question
const handleVerifySecurity = async () => {
  if (!formData.value.answer.trim()) {
    toast.add({
      title: 'Answer Required',
      description: 'Please enter your security question answer',
      color: 'error'
    })
    return
  }

  loading.value = true
  const result = await verifySecurityQuestion(selectedQuestion.value!, formData.value.answer)
  loading.value = false

  if (result.success) {
    verificationToken.value = result.data?.token || 'verified'
    step.value = 3
    nextTick(() => {
      pinInputs.value[0]?.focus()
    })
  } else {
    toast.add({
      title: 'Verification Failed',
      description: result.message || 'Incorrect answer. Please try again.',
      color: 'error'
    })
  }
}

// Validate PIN form
const validateForm = () => {
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

// Submit new PIN
const handleSubmit = async () => {
  if (!validateForm()) return

  loading.value = true

  let result
  if (verificationMethod.value === 'otp') {
    // For OTP: Use changePin which verifies OTP + sets PIN
    result = await changePin({
      otp: formData.value.otp,
      method: selectedOtpMethod.value,
      new_pin: formData.value.new_pin,
      confirm_pin: formData.value.confirm_pin
    })
  } else {
    // For security question: Use resetPinWithToken
    result = await resetPinWithToken({
      reset_token: verificationToken.value,
      new_pin: formData.value.new_pin,
      confirm_pin: formData.value.confirm_pin
    })
  }

  loading.value = false

  if (result.success) {
    toast.add({
      title: 'PIN Reset Successful',
      description: 'Your wallet PIN has been reset successfully',
      color: 'success'
    })
    router.push('/wallet')
  } else {
    toast.add({
      title: 'Failed',
      description: result.message,
      color: 'error'
    })
  }
}

// Check if method available
const hasMobile = computed(() => !!user.value?.mobile)
const hasEmail = computed(() => !!user.value?.email)
const hasSecurityQuestions = computed(() => securityQuestions.value.length > 0)
</script>

<template>
  <div class="max-w-lg mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-red-600 to-rose-600 p-6 text-white">
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
              Reset PIN
            </h1>
            <p class="text-red-100 text-sm">
              Forgot your wallet PIN? Reset it securely
            </p>
          </div>
        </div>
      </div>

      <!-- Step 1: Choose Verification Method -->
      <div
        v-if="step === 1"
        class="p-6 space-y-6"
      >
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-shield-alert"
              class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0"
            />
            <p class="text-sm text-amber-700 dark:text-amber-300">
              For your security, please verify your identity before resetting your PIN.
            </p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            Choose verification method
          </label>
          <div class="space-y-3">
            <!-- OTP Option -->
            <button
              class="w-full p-4 border-2 rounded-xl flex items-center gap-4 text-left transition-all hover:border-slate-300 dark:hover:border-slate-600 border-slate-200 dark:border-slate-700"
              @click="selectMethod('otp')"
            >
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-smartphone"
                  class="w-6 h-6 text-blue-600 dark:text-blue-400"
                />
              </div>
              <div class="flex-1">
                <p class="font-medium text-slate-900 dark:text-white">
                  OTP Verification
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Receive a code via SMS or Email
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-5 h-5 text-slate-400"
              />
            </button>

            <!-- Security Question Option -->
            <button
              v-if="hasSecurityQuestions"
              class="w-full p-4 border-2 rounded-xl flex items-center gap-4 text-left transition-all hover:border-slate-300 dark:hover:border-slate-600 border-slate-200 dark:border-slate-700"
              @click="selectMethod('security')"
            >
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <UIcon
                  name="i-lucide-help-circle"
                  class="w-6 h-6 text-purple-600 dark:text-purple-400"
                />
              </div>
              <div class="flex-1">
                <p class="font-medium text-slate-900 dark:text-white">
                  Security Question
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Answer your security question
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-5 h-5 text-slate-400"
              />
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: Verify Identity -->
      <div
        v-if="step === 2"
        class="p-6 space-y-6"
      >
        <!-- OTP Verification -->
        <template v-if="verificationMethod === 'otp'">
          <!-- OTP Method Selection -->
          <div v-if="!maskedCredential">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
              Send OTP via
            </label>
            <div class="space-y-3 mb-4">
              <div
                v-if="hasMobile"
                :class="[
                  'p-4 border-2 rounded-xl cursor-pointer transition-all flex items-center gap-4',
                  selectedOtpMethod === 'mobile'
                    ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                    : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                ]"
                @click="selectedOtpMethod = 'mobile'"
              >
                <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center">
                  <UIcon
                    name="i-lucide-smartphone"
                    class="w-5 h-5 text-slate-600 dark:text-slate-400"
                  />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900 dark:text-white">
                    Mobile
                  </p>
                </div>
                <div
                  v-if="selectedOtpMethod === 'mobile'"
                  class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center"
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
                  selectedOtpMethod === 'email'
                    ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                    : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                ]"
                @click="selectedOtpMethod = 'email'"
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
                </div>
                <div
                  v-if="selectedOtpMethod === 'email'"
                  class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center"
                >
                  <UIcon
                    name="i-lucide-check"
                    class="w-4 h-4 text-white"
                  />
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
              Send OTP
            </UButton>
          </div>

          <!-- OTP Entry -->
          <div v-else>
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
                class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 outline-none transition-all"
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
                class="text-sm text-red-600 dark:text-red-400 hover:underline"
                :disabled="loading"
                @click="handleRequestOtp"
              >
                Resend OTP
              </button>
            </div>

            <div class="flex gap-3 mt-6">
              <UButton
                variant="outline"
                color="neutral"
                size="lg"
                class="flex-1"
                @click="maskedCredential = ''"
              >
                Back
              </UButton>
              <UButton
                color="primary"
                size="lg"
                class="flex-1"
                :loading="loading"
                @click="handleVerifyOtp"
              >
                Verify
              </UButton>
            </div>
          </div>
        </template>

        <!-- Security Question Verification -->
        <template v-if="verificationMethod === 'security'">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              Security Question
            </label>
            <USelect
              v-model="selectedQuestion"
              :items="securityQuestions.map(q => ({ value: q.key, label: q.label }))"
              class="mb-4"
            />

            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              Your Answer
            </label>
            <UInput
              v-model="formData.answer"
              type="password"
              placeholder="Enter your answer"
              size="lg"
            />
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
              @click="handleVerifySecurity"
            >
              Verify
            </UButton>
          </div>
        </template>
      </div>

      <!-- Step 3: Enter New PIN -->
      <div
        v-if="step === 3"
        class="p-6 space-y-6"
      >
        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4">
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-check-circle"
              class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0"
            />
            <p class="text-sm text-green-700 dark:text-green-300">
              Identity verified! Now create your new 6-digit PIN.
            </p>
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
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 outline-none transition-all"
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
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event, true)"
              @keydown="handleKeydown(i-1, $event, confirmPinInputs)"
            >
          </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
          <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-2">
            PIN Guidelines
          </h4>
          <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
            <li class="flex items-center gap-2">
              <UIcon
                name="i-lucide-check"
                class="w-3 h-3 text-green-500"
              />
              Must be exactly 6 digits
            </li>
            <li class="flex items-center gap-2">
              <UIcon
                name="i-lucide-x"
                class="w-3 h-3 text-red-500"
              />
              Avoid sequential numbers (123456)
            </li>
            <li class="flex items-center gap-2">
              <UIcon
                name="i-lucide-x"
                class="w-3 h-3 text-red-500"
              />
              Avoid repeated digits (111111)
            </li>
          </ul>
        </div>

        <UButton
          color="primary"
          size="lg"
          block
          :loading="loading"
          @click="handleSubmit"
        >
          Reset PIN
        </UButton>
      </div>
    </div>
  </div>
</template>
