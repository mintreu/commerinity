<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-950 flex items-center justify-center p-4">
    <UCard class="max-w-md w-full">
      <div class="text-center space-y-6 py-8">
        <!-- Success Icon -->
        <div class="mx-auto w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
          <Icon
            name="heroicons:check-circle"
            class="w-12 h-12 text-green-600 dark:text-green-400"
          />
        </div>

        <!-- Success Message -->
        <div class="space-y-2">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Payment Successful!
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            Your payment has been processed successfully.
          </p>
        </div>

        <!-- Transaction Info -->
        <div
          v-if="transactionId"
          class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4"
        >
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Transaction ID
          </p>
          <p class="font-mono text-xs mt-1">
            {{ transactionId }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
          <UButton
            color="primary"
            size="lg"
            block
            @click="navigateTo('/wallet')"
          >
            <Icon
              name="heroicons:wallet"
              class="w-5 h-5 mr-2"
            />
            Go to Wallet
          </UButton>
          <UButton
            color="gray"
            variant="ghost"
            size="lg"
            block
            @click="navigateTo('/dashboard')"
          >
            Go to Dashboard
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
const transactionId = computed(() => route.query.transaction as string || null)
</script>
