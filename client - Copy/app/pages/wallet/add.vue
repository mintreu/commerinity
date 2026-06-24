<script setup lang="ts">
/**
 * Add Money to Wallet Page
 * Shows payment options for adding funds to wallet
 */

definePageMeta({
  middleware: '$auth',
  layout: 'dashboard'
})

const router = useRouter()
const toast = useToast()
const { wallet, fetchWallet, topup } = useWallet()

const loading = ref(false)
const selectedAmount = ref<number | null>(null)
const customAmount = ref('')

// Quick amount options
const quickAmounts = [100, 500, 1000, 2000, 5000, 10000]

onMounted(async () => {
  await fetchWallet()
})

// Select quick amount
const selectAmount = (amount: number) => {
  selectedAmount.value = amount
  customAmount.value = ''
}

// Handle custom amount
const handleCustomAmount = () => {
  selectedAmount.value = null
}

// Get final amount
const finalAmount = computed(() => {
  if (selectedAmount.value) return selectedAmount.value
  const parsed = parseFloat(customAmount.value)
  return isNaN(parsed) ? 0 : parsed
})

// Format amount
const formattedAmount = computed(() => {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0
  }).format(finalAmount.value)
})

// Validate and proceed
const proceedToPayment = async () => {
  if (finalAmount.value < 10) {
    toast.add({
      title: 'Invalid Amount',
      description: 'Minimum amount is ₹10',
      color: 'error'
    })
    return
  }

  if (finalAmount.value > 100000) {
    toast.add({
      title: 'Invalid Amount',
      description: 'Maximum amount is ₹1,00,000',
      color: 'error'
    })
    return
  }

  loading.value = true
  const result = await topup(finalAmount.value)
  loading.value = false
  if (!result.success) {
    toast.add({
      title: 'Failed',
      description: result.message || 'Unable to start payment',
      color: 'error'
    })
  }
}
</script>

<template>
  <div class="max-w-lg mx-auto">
    <div class="glass-card overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 text-white">
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
              Add Money
            </h1>
            <p class="text-green-100 text-sm">
              Top up your wallet balance
            </p>
          </div>
        </div>
      </div>

      <div class="p-6 space-y-6">
        <!-- Current Balance -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center justify-between">
          <span class="text-sm text-slate-600 dark:text-slate-400">Current Balance</span>
          <span class="font-semibold text-slate-900 dark:text-white">
            {{ wallet?.balance_formatted || '₹0.00' }}
          </span>
        </div>

        <!-- Quick Amount Selection -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            Select Amount
          </label>
          <div class="grid grid-cols-3 gap-3">
            <button
              v-for="amount in quickAmounts"
              :key="amount"
              :class="[
                'p-4 rounded-xl border-2 font-semibold transition-all',
                selectedAmount === amount
                  ? 'border-green-500 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-700 dark:text-slate-300'
              ]"
              @click="selectAmount(amount)"
            >
              ₹{{ amount.toLocaleString('en-IN') }}
            </button>
          </div>
        </div>

        <!-- Custom Amount -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Or enter custom amount
          </label>
          <UInput
            v-model="customAmount"
            placeholder="Enter amount"
            inputmode="numeric"
            type="number"
            min="10"
            max="100000"
            size="lg"
            @focus="handleCustomAmount"
          >
            <template #leading>
              <span class="text-slate-400 font-medium">₹</span>
            </template>
          </UInput>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Min: ₹10 | Max: ₹1,00,000
          </p>
        </div>

        <!-- Amount Summary -->
        <div
          v-if="finalAmount > 0"
          class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4"
        >
          <div class="flex items-center justify-between">
            <span class="text-green-700 dark:text-green-300">Amount to add</span>
            <span class="text-2xl font-bold text-green-600 dark:text-green-400">
              {{ formattedAmount }}
            </span>
          </div>
        </div>

        <!-- Payment Methods Info -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
            Payment Options
          </p>
          <div class="space-y-2">
            <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
              <UIcon
                name="i-lucide-credit-card"
                class="w-4 h-4"
              />
              <span>Credit / Debit Card</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
              <UIcon
                name="i-lucide-smartphone"
                class="w-4 h-4"
              />
              <span>UPI (GPay, PhonePe, Paytm)</span>
            </div>
            <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
              <UIcon
                name="i-lucide-building-2"
                class="w-4 h-4"
              />
              <span>Net Banking</span>
            </div>
          </div>
        </div>

        <UButton
          color="primary"
          size="lg"
          block
          :disabled="finalAmount < 10"
          :loading="loading"
          @click="proceedToPayment"
        >
          <UIcon
            name="i-lucide-plus"
            class="w-4 h-4 mr-2"
          />
          Add {{ finalAmount > 0 ? formattedAmount : 'Money' }}
        </UButton>

        <!-- Security Note -->
        <div class="flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400">
          <UIcon
            name="i-lucide-shield-check"
            class="w-4 h-4 flex-shrink-0 mt-0.5"
          />
          <p>
            Your payment is secured with 256-bit SSL encryption. We do not store your card details.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
