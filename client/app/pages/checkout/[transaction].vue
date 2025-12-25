<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
    <div class="container mx-auto px-4 py-8">

      <!-- Loading State -->
      <div v-if="isLoading" class="max-w-2xl mx-auto">
        <UCard>
          <div class="flex flex-col items-center justify-center py-12 gap-4">
            <Icon name="svg-spinners:ring-resize" class="w-12 h-12 text-primary" />
            <p class="text-gray-600 dark:text-gray-400">Loading checkout...</p>
          </div>
        </UCard>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="max-w-2xl mx-auto">
        <UCard>
          <div class="flex flex-col items-center justify-center py-12 gap-4">
            <Icon name="heroicons:x-circle" class="w-16 h-16 text-red-500" />
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Checkout Failed</h2>
            <p class="text-gray-600 dark:text-gray-400 text-center">{{ errorMessage }}</p>
            <UButton color="gray" variant="soft" @click="navigateTo('/wallet')">
              Return to Wallet
            </UButton>
          </div>
        </UCard>
      </div>

      <!-- Checkout UI -->
      <div v-else-if="checkoutData" class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Transaction Details (Left) -->
        <div class="md:col-span-1">
          <UCard>
            <template #header>
              <h2 class="text-lg font-semibold">Transaction Details</h2>
            </template>

            <div class="space-y-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Transaction ID</span>
                <span class="font-mono text-xs">{{ checkoutData.transaction.uuid }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Purpose</span>
                <span class="font-medium">{{ checkoutData.transaction.purpose }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Amount</span>
                <span class="text-2xl font-bold text-primary">
                  {{ checkoutData.transaction.amount_formatted }}
                </span>
              </div>

              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Status</span>
                <UBadge :color="getStatusColor(checkoutData.transaction.status)">
                  {{ checkoutData.transaction.status }}
                </UBadge>
              </div>

              <div v-if="checkoutData.transaction.expires_at" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Expires in</span>
                <span class="text-orange-600 dark:text-orange-400 font-medium">
                  {{ formatExpiryTime(checkoutData.transaction.expires_at) }}
                </span>
              </div>

              <div class="pt-4 border-t dark:border-gray-700">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <Icon name="heroicons:shield-check" class="w-5 h-5 text-green-500" />
                  <span>Secured by {{ checkoutData.payment.provider }}</span>
                </div>
              </div>
            </div>
          </UCard>
        </div>

        <!-- Payment Section (Right) -->
        <div class="md:col-span-2">
          <UCard>
            <template #header>
              <h2 class="text-xl font-semibold">Complete Payment</h2>
            </template>

            <!-- Cashfree Payment UI Container -->
            <div class="min-h-[400px] flex flex-col items-center justify-center">
              <div v-if="!paymentInitialized" class="text-center space-y-4">
                <Icon name="svg-spinners:ring-resize" class="w-12 h-12 text-primary mx-auto" />
                <p class="text-gray-600 dark:text-gray-400">Initializing payment gateway...</p>
              </div>

              <div v-else class="w-full text-center space-y-4">
                <Icon name="heroicons:credit-card" class="w-16 h-16 text-primary mx-auto" />
                <h3 class="text-lg font-semibold">Pay {{ checkoutData.transaction.amount_formatted }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Click the button below to complete your payment
                </p>
                <UButton
                  id="cashfree-pay-button"
                  size="xl"
                  color="primary"
                  @click="initiateCashfreePayment"
                  :loading="isProcessing"
                >
                  <Icon name="heroicons:lock-closed" class="w-5 h-5 mr-2" />
                  Pay via Cashfree
                </UButton>
              </div>
            </div>
          </UCard>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * Checkout Page - Universal payment checkout
 *
 * Handles payment for:
 * - Wallet topup
 * - Subscription purchase
 * - Order checkout
 * - Recruitment fees
 */

definePageMeta({
  layout: 'guest' // No authentication required for checkout page
})

const route = useRoute()
const config = useRuntimeConfig()
const toast = useToast()

// State
const isLoading = ref(true)
const error = ref(false)
const errorMessage = ref('')
const checkoutData = ref<any>(null)
const paymentInitialized = ref(false)
const isProcessing = ref(false)

// Cashfree instance
let cashfree: any = null

// Fetch checkout data
async function fetchCheckoutData() {
  try {
    isLoading.value = true

    const transactionId = route.params.transaction
    const response = await $fetch(`${config.public.apiBase}/api/checkout/${transactionId}`)

    if (response.success) {
      checkoutData.value = response.data
      await loadCashfreeSDK()
    } else {
      error.value = true
      errorMessage.value = response.message || 'Failed to load checkout data'
    }
  } catch (e: any) {
    console.error('Checkout fetch error:', e)
    error.value = true
    errorMessage.value = e.data?.message || 'Unable to load checkout. Please try again.'
  } finally {
    isLoading.value = false
  }
}

// Load Cashfree SDK
async function loadCashfreeSDK() {
  return new Promise((resolve, reject) => {
    if (window.Cashfree) {
      initializeCashfree()
      resolve(true)
      return
    }

    const script = document.createElement('script')
    script.src = 'https://sdk.cashfree.com/js/v3/cashfree.js'
    script.async = true
    script.onload = () => {
      initializeCashfree()
      resolve(true)
    }
    script.onerror = () => reject(new Error('Failed to load Cashfree SDK'))
    document.head.appendChild(script)
  })
}

// Initialize Cashfree
function initializeCashfree() {
  const mode = checkoutData.value.payment.is_sandbox ? 'sandbox' : 'production'
  cashfree = new window.Cashfree({ mode })
  paymentInitialized.value = true
}

// Initiate Cashfree payment
async function initiateCashfreePayment() {
  if (!cashfree) {
    toast.add({
      title: 'Error',
      description: 'Payment gateway not initialized',
      color: 'error'
    })
    return
  }

  isProcessing.value = true

  try {
    const paymentSessionId = checkoutData.value.payment.payment_session_id
    const returnUrl = checkoutData.value.redirect.success_url

    if (!paymentSessionId) {
      throw new Error('Payment session ID not found')
    }

    const checkoutOptions = {
      paymentSessionId: paymentSessionId,
      returnUrl: returnUrl
    }

    const result = await cashfree.checkout(checkoutOptions)

    if (result.error) {
      console.error('Cashfree error:', result.error)
      toast.add({
        title: 'Payment Failed',
        description: result.error.message || 'Payment failed',
        color: 'error'
      })

      // Redirect to failure URL after delay
      setTimeout(() => {
        window.location.href = checkoutData.value.redirect.failure_url
      }, 2000)
    }

    if (result.redirect) {
      console.log('Payment redirect triggered')
      // Cashfree will handle redirect
    }
  } catch (e: any) {
    console.error('Payment error:', e)
    toast.add({
      title: 'Payment Error',
      description: e.message || 'Failed to process payment',
      color: 'error'
    })
  } finally {
    isProcessing.value = false
  }
}

// Helper functions
function getStatusColor(status: string) {
  const colors: Record<string, string> = {
    pending: 'orange',
    processing: 'blue',
    completed: 'green',
    failed: 'red',
    cancelled: 'gray'
  }
  return colors[status] || 'gray'
}

function formatExpiryTime(expiresAt: string) {
  const now = new Date()
  const expiry = new Date(expiresAt)
  const diff = expiry.getTime() - now.getTime()

  if (diff < 0) return 'Expired'

  const minutes = Math.floor(diff / 60000)
  const seconds = Math.floor((diff % 60000) / 1000)

  return `${minutes}m ${seconds}s`
}

// Lifecycle
onMounted(() => {
  fetchCheckoutData()
})
</script>

<style scoped>
/* Add any custom styles if needed */
</style>
