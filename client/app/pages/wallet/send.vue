<script setup lang="ts">
/**
 * Send Money Page
 * P2P transfer with PIN verification
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const router = useRouter()
const toast = useToast()
const { wallet, fetchWallet, sendMoney } = useWallet()

const step = ref(1) // 1: Details, 2: PIN, 3: Success
const loading = ref(false)
const formErrors = ref<Record<string, string>>({})
const successData = ref<any>(null)

// Form data
const formData = ref({
  recipient_mobile: '',
  amount: '',
  note: '',
  pin: ''
})

// PIN inputs
const pinInputs = ref<HTMLInputElement[]>([])

onMounted(async () => {
  await fetchWallet()

  // Check if PIN setup required
  if (wallet.value?.requires_pin_setup) {
    toast.add({
      title: 'PIN Required',
      description: 'Please set up your wallet PIN first',
      color: 'warning'
    })
    router.push('/wallet/setup-pin')
  }
})

// Handle PIN input
const handlePinInput = (index: number, event: Event) => {
  const target = event.target as HTMLInputElement
  const value = target.value.replace(/\D/g, '')
  target.value = value

  if (value && index < 5) {
    pinInputs.value[index + 1]?.focus()
  }

  formData.value.pin = pinInputs.value.map(input => input.value).join('')
}

const handlePinKeydown = (index: number, event: KeyboardEvent) => {
  if (event.key === 'Backspace' && !pinInputs.value[index].value && index > 0) {
    pinInputs.value[index - 1]?.focus()
  }
}

// Validate step 1
const validateStep1 = () => {
  formErrors.value = {}

  const mobile = formData.value.recipient_mobile.replace(/\D/g, '')
  if (!mobile || mobile.length !== 10) {
    formErrors.value.recipient_mobile = 'Enter valid 10-digit mobile number'
    return false
  }

  const amount = parseFloat(formData.value.amount)
  if (!amount || amount < 1) {
    formErrors.value.amount = 'Minimum amount is 1'
    return false
  }

  if (amount > 100000) {
    formErrors.value.amount = 'Maximum amount is 1,00,000'
    return false
  }

  const amountInPaisa = amount * 100
  if (wallet.value && amountInPaisa > wallet.value.available_balance) {
    formErrors.value.amount = 'Insufficient balance'
    return false
  }

  return true
}

// Go to PIN step
const goToPinStep = () => {
  if (validateStep1()) {
    step.value = 2
    nextTick(() => {
      pinInputs.value[0]?.focus()
    })
  }
}

// Submit transfer
const handleSubmit = async () => {
  if (formData.value.pin.length !== 6) {
    toast.add({
      title: 'Invalid PIN',
      description: 'Please enter your 6-digit PIN',
      color: 'error'
    })
    return
  }

  loading.value = true
  const result = await sendMoney({
    pin: formData.value.pin,
    recipient_mobile: formData.value.recipient_mobile.replace(/\D/g, ''),
    amount: parseFloat(formData.value.amount),
    note: formData.value.note || undefined
  })
  loading.value = false

  if (result.success) {
    successData.value = result.data
    step.value = 3
  } else {
    if (result.requiresPinSetup) {
      router.push('/wallet/setup-pin')
      return
    }

    toast.add({
      title: 'Transfer Failed',
      description: result.message,
      color: 'error'
    })

    if (result.attemptsRemaining !== undefined) {
      toast.add({
        title: 'Warning',
        description: `${result.attemptsRemaining} attempts remaining before lockout`,
        color: 'warning'
      })
    }

    // Clear PIN
    formData.value.pin = ''
    pinInputs.value.forEach(input => input.value = '')
    pinInputs.value[0]?.focus()
  }
}

// Format amount display
const formattedAmount = computed(() => {
  const amount = parseFloat(formData.value.amount) || 0
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0
  }).format(amount)
})
</script>

<template>
  <div class="max-w-lg mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6 text-white">
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
              Send Money
            </h1>
            <p class="text-blue-100 text-sm">
              Transfer to any registered user
            </p>
          </div>
        </div>
      </div>

      <!-- Step 1: Enter Details -->
      <div
        v-if="step === 1"
        class="p-6 space-y-6"
      >
        <!-- Available Balance -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center justify-between">
          <span class="text-sm text-slate-600 dark:text-slate-400">Available Balance</span>
          <span class="font-semibold text-slate-900 dark:text-white">
            {{ wallet?.available_balance_formatted || '0.00' }}
          </span>
        </div>

        <UFormField
          label="Recipient Mobile Number"
          :error="formErrors.recipient_mobile"
        >
          <UInput
            v-model="formData.recipient_mobile"
            placeholder="Enter 10-digit mobile"
            inputmode="numeric"
            maxlength="10"
          >
            <template #leading />
          </UInput>
        </UFormField>

        <UFormField
          label="Amount"
          :error="formErrors.amount"
        >
          <UInput
            v-model="formData.amount"
            placeholder="0"
            inputmode="decimal"
            type="number"
            min="1"
            max="100000"
          >
            <template #leading>
              <span class="text-slate-400 font-medium">&#8377;</span>
            </template>
          </UInput>
        </UFormField>

        <UFormField label="Note">
          <UInput
            v-model="formData.note"
            placeholder="Add a note for recipient"
            maxlength="200"
          />
        </UFormField>

        <UButton
          color="primary"
          size="lg"
          block
          @click="goToPinStep"
        >
          Continue
        </UButton>
      </div>

      <!-- Step 2: Enter PIN -->
      <div
        v-if="step === 2"
        class="p-6 space-y-6"
      >
        <!-- Transfer Summary -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 space-y-3">
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">
              Sending to
            </span>
            <span class="font-medium text-slate-900 dark:text-white">
              {{ formData.recipient_mobile }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">
              Amount
            </span>
            <span class="font-semibold text-lg text-blue-600 dark:text-blue-400">
              {{ formattedAmount }}
            </span>
          </div>
          <div
            v-if="formData.note"
            class="flex justify-between"
          >
            <span class="text-slate-600 dark:text-slate-400">
              Note
            </span>
            <span class="text-slate-900 dark:text-white truncate max-w-[200px]">
              {{ formData.note }}
            </span>
          </div>
        </div>

        <!-- PIN Entry -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3 text-center">
            Enter your 6-digit PIN to confirm
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
            :disabled="formData.pin.length !== 6"
            @click="handleSubmit"
          >
            Confirm &amp; Send
          </UButton>
        </div>
      </div>

      <!-- Step 3: Success -->
      <div
        v-if="step === 3"
        class="p-6 text-center space-y-6"
      >
        <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto">
          <UIcon
            name="i-lucide-check"
            class="w-10 h-10 text-green-600 dark:text-green-400"
          />
        </div>

        <div>
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
            Money Sent!
          </h2>
          <p class="text-slate-600 dark:text-slate-400">
            {{ successData?.amount_formatted }} sent to {{ successData?.recipient_name }}
          </p>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">
              Reference
            </span>
            <span class="font-mono text-slate-900 dark:text-white">
              {{ successData?.transaction?.reference_number }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">
              New Balance
            </span>
            <span class="font-medium text-slate-900 dark:text-white">
              {{ successData?.new_balance_formatted }}
            </span>
          </div>
        </div>

        <div class="flex gap-3">
          <UButton
            variant="outline"
            color="neutral"
            class="flex-1"
            to="/wallet/transactions"
          >
            View History
          </UButton>
          <UButton
            color="primary"
            class="flex-1"
            to="/wallet"
          >
            Done
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
