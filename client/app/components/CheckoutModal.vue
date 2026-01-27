<script setup lang="ts">
/**
 * Centralized Checkout Modal
 * Used for: Job Applications, Subscriptions, Membership payments
 */

interface Props {
  open: boolean
  title: string
  amount: number
  amountFormatted: string
  description?: string
  checkoutEndpoint: string
  checkoutPayload?: Record<string, any>
  onSuccess?: () => void
}

const props = defineProps<Props>()
const emit = defineEmits(['update:open', 'success'])

const config = useRuntimeConfig()
const toast = useToast()
const { wallet, fetchWallet } = useWallet()

const paymentMethod = ref<'wallet' | 'online'>('online')
const walletPin = ref('')
const isProcessing = ref(false)

onMounted(async () => {
  await fetchWallet()
})

const insufficientBalance = computed(() => {
  return paymentMethod.value === 'wallet' && wallet.value && wallet.value.available_balance < props.amount
})

async function processPayment() {
  if (isProcessing.value || insufficientBalance.value) return

  if (paymentMethod.value === 'wallet' && !walletPin.value) {
    toast.add({
      title: 'PIN Required',
      description: 'Please enter your 6-digit wallet PIN to continue.',
      color: 'warning'
    })
    return
  }

  isProcessing.value = true

  try {
    const response = await useSanctumFetch<{
      success: boolean
      message: string
      data?: {
        checkout_url?: string
        transaction_uuid?: string
      }
    }>(`${config.public.apiBase}${props.checkoutEndpoint}`, {
      method: 'POST',
      body: {
        payment_method: paymentMethod.value,
        pin: walletPin.value,
        ...props.checkoutPayload
      }
    })

    if (response.success) {
      if (paymentMethod.value === 'wallet') {
        // Wallet payment completed
        toast.add({
          title: 'Payment Successful',
          description: 'Payment completed successfully via wallet.',
          color: 'success'
        })
        emit('update:open', false)
        emit('success')
        await fetchWallet()
        if (props.onSuccess) props.onSuccess()
      } else if (response.data?.checkout_url) {
        // Redirect to online payment
        toast.add({
          title: 'Redirecting to Payment',
          description: 'Please complete your payment.',
          color: 'info'
        })
        window.location.href = response.data.checkout_url
      }
    } else {
      toast.add({
        title: 'Payment Failed',
        description: response.message || 'Unable to process payment.',
        color: 'error'
      })
    }
  } catch (err: any) {
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Failed to process payment. Please try again.',
      color: 'error'
    })
  } finally {
    isProcessing.value = false
  }
}

function close() {
  emit('update:open', false)
}
</script>

<template>
  <UModal
    :model-value="open"
    :ui="{ width: 'sm:max-w-md' }"
    @update:model-value="emit('update:open', $event)"
  >
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ title }}
          </h3>
          <UButton
            color="gray"
            variant="ghost"
            icon="i-lucide-x"
            @click="close"
          />
        </div>
      </template>

      <div class="space-y-6">
        <!-- Amount Display -->
        <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-xl p-6 text-center text-white">
          <p class="text-sm opacity-80 mb-2">
            Amount
          </p>
          <p class="text-3xl font-bold">
            {{ amountFormatted }}
          </p>
          <p
            v-if="description"
            class="text-sm opacity-80 mt-2"
          >
            {{ description }}
          </p>
        </div>

        <!-- Wallet Balance -->
        <div
          v-if="wallet"
          class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600 dark:text-slate-400">Wallet Balance</span>
            <span class="font-semibold text-slate-900 dark:text-white">
              {{ wallet.available_balance_formatted }}
            </span>
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Select Payment Method
          </label>

          <!-- Pay via Wallet -->
          <label
            class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
            :class="paymentMethod === 'wallet'
              ? 'border-primary bg-primary/5 dark:bg-primary/10'
              : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'"
          >
            <input
              v-model="paymentMethod"
              type="radio"
              value="wallet"
              class="w-5 h-5 text-primary"
            >
            <div class="flex-1">
              <p class="font-medium text-slate-900 dark:text-white">
                Pay via Wallet
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Instant payment from your wallet balance
              </p>
            </div>
            <UIcon
              name="i-lucide-wallet"
              class="w-6 h-6 text-slate-400"
            />
          </label>

          <!-- Wallet PIN Input (Only when wallet selected) -->
          <div
            v-if="paymentMethod === 'wallet'"
            class="px-2 animate-in fade-in slide-in-from-top-2 duration-300"
          >
            <UFormGroup
              label="Wallet PIN"
              help="Enter your 6-digit transaction PIN"
            >
              <UInput
                v-model="walletPin"
                type="password"
                placeholder="••••••"
                maxlength="6"
                icon="i-lucide-lock"
                class="font-mono text-center tracking-widest"
              />
            </UFormGroup>
          </div>

          <!-- Pay Online -->
          <label
            class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
            :class="paymentMethod === 'online'
              ? 'border-primary bg-primary/5 dark:bg-primary/10'
              : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'"
          >
            <input
              v-model="paymentMethod"
              type="radio"
              value="online"
              class="w-5 h-5 text-primary"
            >
            <div class="flex-1">
              <p class="font-medium text-slate-900 dark:text-white">
                Pay Online
              </p>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                UPI, Cards, Net Banking & more
              </p>
            </div>
            <UIcon
              name="i-lucide-credit-card"
              class="w-6 h-6 text-slate-400"
            />
          </label>
        </div>

        <!-- Insufficient Balance Warning -->
        <UAlert
          v-if="insufficientBalance"
          color="warning"
          icon="i-lucide-alert-triangle"
          title="Insufficient Balance"
          description="Your wallet balance is insufficient. Please add funds or choose online payment."
        />
      </div>

      <template #footer>
        <div class="flex gap-3">
          <UButton
            color="gray"
            variant="outline"
            block
            @click="close"
          >
            Cancel
          </UButton>
          <UButton
            color="primary"
            block
            :loading="isProcessing"
            :disabled="insufficientBalance"
            @click="processPayment"
          >
            <UIcon
              name="i-lucide-lock"
              class="w-4 h-4 mr-2"
            />
            {{ paymentMethod === 'wallet' ? 'Pay from Wallet' : 'Pay Online' }}
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>
</template>
