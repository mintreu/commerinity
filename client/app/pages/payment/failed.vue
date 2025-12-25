<template>
  <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-gray-900 dark:to-gray-950 flex items-center justify-center p-4">
    <UCard class="max-w-md w-full">
      <div class="text-center space-y-6 py-8">
        <!-- Failed Icon -->
        <div class="mx-auto w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
          <Icon name="heroicons:x-circle" class="w-12 h-12 text-red-600 dark:text-red-400" />
        </div>

        <!-- Failed Message -->
        <div class="space-y-2">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Failed</h1>
          <p class="text-gray-600 dark:text-gray-400">
            We couldn't process your payment. Please try again.
          </p>
        </div>

        <!-- Reason (if available) -->
        <div v-if="reason" class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
          <p class="text-sm text-red-700 dark:text-red-300">{{ reason }}</p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
          <UButton color="primary" size="lg" block @click="retryPayment">
            <Icon name="heroicons:arrow-path" class="w-5 h-5 mr-2" />
            Try Again
          </UButton>
          <UButton color="gray" variant="ghost" size="lg" block @click="navigateTo('/dashboard')">
            Return to Dashboard
          </UButton>
        </div>

        <!-- Support -->
        <div class="pt-4 border-t dark:border-gray-700">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Need help?</p>
          <UButton color="gray" variant="soft" size="sm" @click="navigateTo('/helpdesk')">
            <Icon name="heroicons:chat-bubble-left-right" class="w-4 h-4 mr-2" />
            Contact Support
          </UButton>
        </div>
      </div>
    </UCard>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'guest'
})

const route = useRoute()
const reason = computed(() => route.query.reason as string || null)

function retryPayment() {
  // Go back to wallet to retry
  navigateTo('/wallet')
}
</script>
