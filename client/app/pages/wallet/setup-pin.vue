<script setup lang="ts">
/**
 * Wallet PIN Setup Page
 * First-time PIN and security questions setup
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const router = useRouter()
const toast = useToast()
const { wallet, securityQuestions, fetchWallet, fetchSecurityQuestions, setupPin } = useWallet()

const step = ref(1) // 1: PIN, 2: Security Questions
const loading = ref(false)
const formErrors = ref<Record<string, string>>({})

// Form data
const formData = ref({
  pin: '',
  confirm_pin: '',
  security_question_1: '',
  security_answer_1: '',
  security_question_2: '',
  security_answer_2: ''
})

// PIN input refs
const pinInputs = ref<HTMLInputElement[]>([])
const confirmPinInputs = ref<HTMLInputElement[]>([])

// Fetch data on mount
onMounted(async () => {
  await Promise.all([
    fetchWallet(),
    fetchSecurityQuestions()
  ])

  // Redirect if PIN already set
  if (wallet.value && !wallet.value.requires_pin_setup) {
    toast.add({
      title: 'PIN Already Set',
      description: 'Your wallet PIN is already configured',
      color: 'primary'
    })
    router.push('/wallet')
  }
})

// Handle PIN input
const handlePinInput = (index: number, event: Event, isConfirm = false) => {
  const target = event.target as HTMLInputElement
  const value = target.value.replace(/\D/g, '')
  target.value = value

  const inputs = isConfirm ? confirmPinInputs.value : pinInputs.value

  if (value && index < 5) {
    inputs[index + 1]?.focus()
  }

  // Update form data
  const allInputs = isConfirm ? confirmPinInputs.value : pinInputs.value
  const pin = allInputs.map(input => input.value).join('')

  if (isConfirm) {
    formData.value.confirm_pin = pin
  } else {
    formData.value.pin = pin
  }
}

// Handle backspace
const handlePinKeydown = (index: number, event: KeyboardEvent, isConfirm = false) => {
  const inputs = isConfirm ? confirmPinInputs.value : pinInputs.value

  if (event.key === 'Backspace' && !inputs[index].value && index > 0) {
    inputs[index - 1]?.focus()
  }
}

// Validate PIN step
const validatePinStep = () => {
  formErrors.value = {}

  if (formData.value.pin.length !== 6) {
    formErrors.value.pin = 'PIN must be 6 digits'
    return false
  }

  if (formData.value.pin !== formData.value.confirm_pin) {
    formErrors.value.confirm_pin = 'PINs do not match'
    return false
  }

  // Check for weak PINs
  const weakPins = ['123456', '654321', '111111', '000000', '123123']
  if (weakPins.includes(formData.value.pin)) {
    formErrors.value.pin = 'Please choose a stronger PIN'
    return false
  }

  return true
}

// Go to next step
const goToNextStep = () => {
  if (step.value === 1 && validatePinStep()) {
    step.value = 2
  }
}

// Go back
const goBack = () => {
  if (step.value > 1) {
    step.value--
  } else {
    router.push('/wallet')
  }
}

// Validate security questions
const validateSecurityQuestions = () => {
  formErrors.value = {}

  if (!formData.value.security_question_1) {
    formErrors.value.security_question_1 = 'Please select a question'
    return false
  }

  if (!formData.value.security_answer_1 || formData.value.security_answer_1.length < 2) {
    formErrors.value.security_answer_1 = 'Answer must be at least 2 characters'
    return false
  }

  if (!formData.value.security_question_2) {
    formErrors.value.security_question_2 = 'Please select a question'
    return false
  }

  if (formData.value.security_question_1 === formData.value.security_question_2) {
    formErrors.value.security_question_2 = 'Please select a different question'
    return false
  }

  if (!formData.value.security_answer_2 || formData.value.security_answer_2.length < 2) {
    formErrors.value.security_answer_2 = 'Answer must be at least 2 characters'
    return false
  }

  return true
}

// Submit form
const handleSubmit = async () => {
  if (!validateSecurityQuestions()) return

  loading.value = true
  const result = await setupPin(formData.value)
  loading.value = false

  if (result.success) {
    toast.add({
      title: 'PIN Setup Complete',
      description: 'Your wallet is now secured with a PIN',
      color: 'success'
    })
    router.push('/wallet')
  } else {
    toast.add({
      title: 'Setup Failed',
      description: result.message,
      color: 'error'
    })
    if (result.errors) {
      formErrors.value = result.errors
    }
  }
}

// Filtered questions for second dropdown
const filteredQuestionsForSecond = computed(() => {
  return securityQuestions.value.filter(q => q.key !== formData.value.security_question_1)
})
</script>

<template>
  <div class="max-w-lg mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white text-center">
        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <UIcon
            name="i-lucide-shield-check"
            class="w-8 h-8"
          />
        </div>
        <h1 class="text-xl font-bold">
          Secure Your Wallet
        </h1>
        <p class="text-blue-100 text-sm mt-1">
          Set up your PIN and security questions
        </p>
      </div>

      <!-- Progress Steps -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-center gap-4">
          <div
            :class="[
              'flex items-center gap-2',
              step >= 1 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'
            ]"
          >
            <div
              :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold',
                step >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700'
              ]"
            >
              1
            </div>
            <span class="text-sm font-medium hidden sm:inline">
              Create PIN
            </span>
          </div>
          <div class="w-12 h-0.5 bg-slate-200 dark:bg-slate-700">
            <div
              :class="[
                'h-full bg-blue-600 transition-all',
                step >= 2 ? 'w-full' : 'w-0'
              ]"
            />
          </div>
          <div
            :class="[
              'flex items-center gap-2',
              step >= 2 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'
            ]"
          >
            <div
              :class="[
                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold',
                step >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700'
              ]"
            >
              2
            </div>
            <span class="text-sm font-medium hidden sm:inline">
              Security Questions
            </span>
          </div>
        </div>
      </div>

      <!-- Step 1: PIN Entry -->
      <div
        v-if="step === 1"
        class="p-6 space-y-6"
      >
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Enter 6-digit PIN
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'pin-' + i"
              :ref="el => pinInputs[i-1] = el as HTMLInputElement"
              type="password"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event)"
              @keydown="handlePinKeydown(i-1, $event)"
            >
          </div>
          <p
            v-if="formErrors.pin"
            class="text-red-500 text-sm text-center mt-2"
          >
            {{ formErrors.pin }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Confirm PIN
          </label>
          <div class="flex justify-center gap-2">
            <input
              v-for="i in 6"
              :key="'confirm-' + i"
              :ref="el => confirmPinInputs[i-1] = el as HTMLInputElement"
              type="password"
              maxlength="1"
              inputmode="numeric"
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
              @input="handlePinInput(i-1, $event, true)"
              @keydown="handlePinKeydown(i-1, $event, true)"
            >
          </div>
          <p
            v-if="formErrors.confirm_pin"
            class="text-red-500 text-sm text-center mt-2"
          >
            {{ formErrors.confirm_pin }}
          </p>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-info"
              class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"
            />
            <div class="text-sm text-slate-600 dark:text-slate-400">
              <p class="font-medium text-slate-700 dark:text-slate-300 mb-1">
                PIN Guidelines
              </p>
              <ul class="list-disc list-inside space-y-1">
                <li>Must be exactly 6 digits</li>
                <li>Avoid simple patterns (123456, 111111)</li>
                <li>Don't use your birth year or phone number</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Security Questions -->
      <div
        v-if="step === 2"
        class="p-6 space-y-6"
      >
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-6">
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-shield"
              class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"
            />
            <p class="text-sm text-blue-700 dark:text-blue-300">
              Security questions help you recover your PIN if you forget it.
              Choose questions only you know the answers to.
            </p>
          </div>
        </div>

        <!-- Question 1 -->
        <div class="space-y-3">
          <UFormField
            label="Security Question 1"
            :error="formErrors.security_question_1"
          >
            <USelect
              v-model="formData.security_question_1"
              :items="securityQuestions.map(q => ({ label: q.label, value: q.key }))"
              placeholder="Select a question"
            />
          </UFormField>
          <UFormField
            label="Your Answer"
            :error="formErrors.security_answer_1"
          >
            <UInput
              v-model="formData.security_answer_1"
              placeholder="Enter your answer"
            />
          </UFormField>
        </div>

        <!-- Question 2 -->
        <div class="space-y-3">
          <UFormField
            label="Security Question 2"
            :error="formErrors.security_question_2"
          >
            <USelect
              v-model="formData.security_question_2"
              :items="filteredQuestionsForSecond.map(q => ({ label: q.label, value: q.key }))"
              placeholder="Select a different question"
            />
          </UFormField>
          <UFormField
            label="Your Answer"
            :error="formErrors.security_answer_2"
          >
            <UInput
              v-model="formData.security_answer_2"
              placeholder="Enter your answer"
            />
          </UFormField>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
        <UButton
          variant="ghost"
          color="neutral"
          @click="goBack"
        >
          <UIcon
            name="i-lucide-arrow-left"
            class="w-4 h-4 mr-1"
          />
          Back
        </UButton>

        <UButton
          v-if="step === 1"
          color="primary"
          @click="goToNextStep"
        >
          Continue
          <UIcon
            name="i-lucide-arrow-right"
            class="w-4 h-4 ml-1"
          />
        </UButton>

        <UButton
          v-else
          color="primary"
          :loading="loading"
          @click="handleSubmit"
        >
          Complete Setup
        </UButton>
      </div>
    </div>
  </div>
</template>
