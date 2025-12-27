<script setup lang="ts">
definePageMeta({
  middleware: ['$auth']
})

const route = useRoute()
const config = useRuntimeConfig()
const toast = useToast()

const uuid = route.params.uuid as string

interface JobApplication {
  uuid: string
  status: string
  status_label: string
  is_paid: boolean
  amount: number
  amount_formatted: string
  recruitment: {
    title: string
    slug: string
    role_label: string
  }
}

const { data: application, status, error } = await useAsyncData<{ data: JobApplication }>(
  `application-pay-${uuid}`,
  () => useSanctumFetch(`${config.public.apiBase}/api/my-applications/${uuid}`)
)

const selectedPaymentMethod = ref<'cashfree' | 'razorpay'>('cashfree')
const isProcessing = ref(false)

async function initiatePayment() {
  if (isProcessing.value) return

  isProcessing.value = true

  try {
    const response = await useSanctumFetch<{
      success: boolean
      message: string
      data?: {
        checkout_url: string
        transaction_uuid: string
      }
    }>(`${config.public.apiBase}/api/my-applications/${uuid}/pay`, {
      method: 'POST',
      body: {
        payment_method: selectedPaymentMethod.value
      }
    })

    if (response.success && response.data?.checkout_url) {
      toast.add({
        title: 'Redirecting to Payment',
        description: 'Please complete your payment.',
        color: 'info'
      })

      // Redirect to checkout page
      window.location.href = response.data.checkout_url
    } else {
      toast.add({
        title: 'Error',
        description: response.message || 'Failed to initiate payment.',
        color: 'error'
      })
    }
  } catch (err: unknown) {
    const errorObj = err as { data?: { message?: string } }
    toast.add({
      title: 'Error',
      description: errorObj.data?.message || 'Failed to initiate payment. Please try again.',
      color: 'error'
    })
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-slate-900">
    <UContainer class="py-8 max-w-2xl">
      <!-- Back Button -->
      <div class="mb-6">
        <UButton
          :to="`/career/applications/${uuid}`"
          variant="ghost"
          icon="i-lucide-arrow-left"
          size="sm"
        >
          Back to Application
        </UButton>
      </div>

      <!-- Loading State -->
      <div v-if="status === 'pending'" class="flex justify-center py-12">
        <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-primary" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <UIcon name="i-lucide-alert-circle" class="w-16 h-16 mx-auto text-red-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Application not found
        </h3>
        <UButton to="/career/applications" variant="outline">
          View All Applications
        </UButton>
      </div>

      <!-- Already Paid -->
      <div v-else-if="application?.data?.is_paid" class="text-center py-12">
        <UIcon name="i-lucide-check-circle" class="w-16 h-16 mx-auto text-green-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Payment Already Completed
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Your application fee has already been paid.
        </p>
        <UButton :to="`/career/applications/${uuid}`" color="primary">
          View Application
        </UButton>
      </div>

      <!-- Not Awaiting Payment -->
      <div v-else-if="application?.data?.status !== 'awaiting_payment'" class="text-center py-12">
        <UIcon name="i-lucide-info" class="w-16 h-16 mx-auto text-blue-500 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
          Payment Not Required
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          This application does not require payment at this time.
        </p>
        <UButton :to="`/career/applications/${uuid}`" color="primary">
          View Application
        </UButton>
      </div>

      <!-- Payment Form -->
      <div v-else-if="application?.data" class="space-y-6">
        <!-- Application Summary Card -->
        <UCard :ui="{ root: 'bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white' }">
          <div class="text-center">
            <h1 class="text-2xl font-bold mb-2">
              Complete Payment
            </h1>
            <p class="text-white/80 mb-4">
              {{ application.data.recruitment.title }}
            </p>
            <div class="text-4xl font-bold">
              {{ application.data.amount_formatted }}
            </div>
            <p class="text-sm text-white/60 mt-2">
              Application Fee
            </p>
          </div>
        </UCard>

        <!-- Application Details -->
        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Application Details
            </h2>
          </template>

          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Application ID</span>
              <span class="font-mono text-sm text-gray-900 dark:text-white">{{ application.data.uuid }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Position</span>
              <span class="text-gray-900 dark:text-white">{{ application.data.recruitment.role_label }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Status</span>
              <UBadge color="warning">{{ application.data.status_label }}</UBadge>
            </div>
          </div>
        </UCard>

        <!-- Payment Method Selection -->
        <UCard>
          <template #header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Select Payment Method
            </h2>
          </template>

          <div class="space-y-3">
            <label
              class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all"
              :class="selectedPaymentMethod === 'cashfree'
                ? 'border-primary bg-primary/5 dark:bg-primary/10'
                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
            >
              <input
                v-model="selectedPaymentMethod"
                type="radio"
                value="cashfree"
                name="payment_method"
                class="w-5 h-5 text-primary"
              />
              <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">Cashfree</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Pay using UPI, Credit/Debit Cards, Net Banking
                </p>
              </div>
              <UIcon name="i-lucide-credit-card" class="w-6 h-6 text-gray-400" />
            </label>

            <label
              class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all"
              :class="selectedPaymentMethod === 'razorpay'
                ? 'border-primary bg-primary/5 dark:bg-primary/10'
                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'"
            >
              <input
                v-model="selectedPaymentMethod"
                type="radio"
                value="razorpay"
                name="payment_method"
                class="w-5 h-5 text-primary"
              />
              <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white">Razorpay</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Pay using UPI, Cards, Wallets, Net Banking
                </p>
              </div>
              <UIcon name="i-lucide-wallet" class="w-6 h-6 text-gray-400" />
            </label>
          </div>

          <template #footer>
            <UButton
              block
              size="lg"
              color="primary"
              :loading="isProcessing"
              @click="initiatePayment"
            >
              <UIcon name="i-lucide-lock" class="w-4 h-4 mr-2" />
              Pay {{ application.data.amount_formatted }}
            </UButton>
          </template>
        </UCard>

        <!-- Security Notice -->
        <div class="flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <UIcon name="i-lucide-shield-check" class="w-4 h-4 text-green-500" />
          <span>Secured by industry-standard encryption</span>
        </div>
      </div>
    </UContainer>
  </div>
</template>
