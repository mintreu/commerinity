<script setup lang="ts">
/**
 * Wallet Withdraw Page
 * Withdraw to bank account with PIN verification
 */

definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const router = useRouter()
const toast = useToast()
const config = useRuntimeConfig()
const { wallet, fetchWallet, withdraw } = useWallet()

const step = ref(1) // 1: Select Amount & Account, 2: PIN, 3: Success
const loading = ref(false)
const beneficiaries = ref<any[]>([])
const beneficiariesLoading = ref(true)
const successData = ref<any>(null)

// Form data
const formData = ref({
  amount: '',
  beneficiary_uuid: null as string | null,
  pin: ''
})

// PIN inputs
const pinInputs = ref<HTMLInputElement[]>([])

onMounted(async () => {
  await Promise.all([
    fetchWallet(),
    fetchBeneficiaries()
  ])

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

// Fetch beneficiary accounts (only verified ones for withdrawals)
const fetchBeneficiaries = async () => {
  beneficiariesLoading.value = true
  try {
    const response = await useSanctumFetch<any>(`${config.public.apiBase}/api/wallet/beneficiaries`)
    // Filter only verified accounts that can receive payouts
    beneficiaries.value = (response.data || []).filter((b: any) => b.can_receive_payout)
  } catch (e) {
    console.error('Failed to fetch beneficiaries:', e)
    beneficiaries.value = []
  } finally {
    beneficiariesLoading.value = false
  }
}

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
  const amount = parseFloat(formData.value.amount)

  if (!amount || amount < 100) {
    toast.add({
      title: 'Invalid Amount',
      description: 'Minimum withdrawal amount is 100',
      color: 'error'
    })
    return false
  }

  if (amount > 200000) {
    toast.add({
      title: 'Invalid Amount',
      description: 'Maximum withdrawal amount is 2,00,000',
      color: 'error'
    })
    return false
  }

  const amountInPaisa = amount * 100
  if (wallet.value && amountInPaisa > wallet.value.available_balance) {
    toast.add({
      title: 'Insufficient Balance',
      description: 'You do not have enough balance for this withdrawal',
      color: 'error'
    })
    return false
  }

  if (!formData.value.beneficiary_uuid) {
    toast.add({
      title: 'Select Account',
      description: 'Please select a bank account for withdrawal',
      color: 'error'
    })
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

// Submit withdrawal
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
  const result = await withdraw({
    pin: formData.value.pin,
    amount: parseFloat(formData.value.amount),
    beneficiary_uuid: formData.value.beneficiary_uuid!
  })
  loading.value = false

  if (result.success) {
    successData.value = result.data
    step.value = 3
  } else {
    toast.add({
      title: 'Withdrawal Failed',
      description: result.message,
      color: 'error'
    })

    // Clear PIN
    formData.value.pin = ''
    pinInputs.value.forEach(input => input.value = '')
    pinInputs.value[0]?.focus()
  }
}

// Selected beneficiary
const selectedBeneficiary = computed(() => {
  return beneficiaries.value.find(b => b.uuid === formData.value.beneficiary_uuid)
})

// Format amount
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
      <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 text-white">
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
              Withdraw Money
            </h1>
            <p class="text-purple-100 text-sm">
              Transfer to your bank account
            </p>
          </div>
        </div>
      </div>

      <!-- Step 1: Amount & Account -->
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

        <!-- Amount -->
        <UFormField label="Withdrawal Amount">
          <UInput
            v-model="formData.amount"
            placeholder="Minimum 100"
            inputmode="numeric"
            type="number"
            min="100"
            max="200000"
          >
            <template #leading>
              <span class="text-slate-400 font-medium">&#8377;</span>
            </template>
          </UInput>
          <template #hint>
            <span class="text-xs text-slate-500">
              Min: &#8377;100 | Max: &#8377;2,00,000
            </span>
          </template>
        </UFormField>

        <!-- Bank Account Selection -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            Select Bank Account
          </label>

          <div
            v-if="beneficiariesLoading"
            class="space-y-3"
          >
            <div
              v-for="i in 2"
              :key="i"
              class="p-4 border border-slate-200 dark:border-slate-700 rounded-xl animate-pulse"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-lg" />
                <div class="flex-1">
                  <div class="h-4 w-32 bg-slate-200 dark:bg-slate-700 rounded mb-2" />
                  <div class="h-3 w-48 bg-slate-200 dark:bg-slate-700 rounded" />
                </div>
              </div>
            </div>
          </div>

          <div
            v-else-if="beneficiaries.length === 0"
            class="text-center py-8 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl"
          >
            <UIcon
              name="i-lucide-building-2"
              class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3"
            />
            <p class="text-slate-600 dark:text-slate-400 mb-4">
              No bank accounts added yet
            </p>
            <UButton
              to="/wallet/bank-accounts"
              variant="outline"
              size="sm"
            >
              Add Bank Account
            </UButton>
          </div>

          <div
            v-else
            class="space-y-3"
          >
            <div
              v-for="beneficiary in beneficiaries"
              :key="beneficiary.uuid"
              :class="[
                'p-4 border-2 rounded-xl cursor-pointer transition-all',
                formData.beneficiary_uuid === beneficiary.uuid
                  ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
              @click="formData.beneficiary_uuid = beneficiary.uuid"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center">
                  <UIcon
                    name="i-lucide-building-2"
                    class="w-5 h-5 text-slate-600 dark:text-slate-400"
                  />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900 dark:text-white">
                    {{ beneficiary.bank_name }}
                  </p>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ beneficiary.account_number_masked }}
                  </p>
                </div>
                <div
                  v-if="formData.beneficiary_uuid === beneficiary.uuid"
                  class="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center"
                >
                  <UIcon
                    name="i-lucide-check"
                    class="w-4 h-4 text-white"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <UButton
          color="primary"
          size="lg"
          block
          :disabled="beneficiaries.length === 0"
          @click="goToPinStep"
        >
          Continue
        </UButton>
      </div>

      <!-- Step 2: PIN Entry -->
      <div
        v-if="step === 2"
        class="p-6 space-y-6"
      >
        <!-- Summary -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 space-y-3">
          <div class="flex justify-between">
            <span class="text-slate-600 dark:text-slate-400">
              Withdrawal Amount
            </span>
            <span class="font-semibold text-lg text-purple-600 dark:text-purple-400">
              {{ formattedAmount }}
            </span>
          </div>
          <div
            v-if="selectedBeneficiary"
            class="flex justify-between"
          >
            <span class="text-slate-600 dark:text-slate-400">
              Bank Account
            </span>
            <span class="text-slate-900 dark:text-white">
              {{ selectedBeneficiary.account_number_masked }}
            </span>
          </div>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4">
          <div class="flex gap-3">
            <UIcon
              name="i-lucide-info"
              class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0"
            />
            <p class="text-sm text-amber-700 dark:text-amber-300">
              Withdrawals are processed within 24-48 hours. The amount will be transferred to your bank account.
            </p>
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
              class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all"
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
            Confirm Withdrawal
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
            Withdrawal Initiated!
          </h2>
          <p class="text-slate-600 dark:text-slate-400">
            {{ successData?.amount_formatted }} will be transferred within 24-48 hours
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
              Available Balance
            </span>
            <span class="font-medium text-slate-900 dark:text-white">
              {{ successData?.new_available_balance_formatted }}
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
