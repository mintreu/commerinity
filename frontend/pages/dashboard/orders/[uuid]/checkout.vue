<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useRuntimeConfig, useSanctumFetch } from '#imports'
import { ref, onMounted, computed } from 'vue'

const config = useRuntimeConfig()
const route = useRoute()

const order = ref<any>(null)
const isLoading = ref(true)

async function fetchOrder() {
  try {
    isLoading.value = true
    const res: any = await useSanctumFetch(
        `${config.public.apiBase}/orders/${route.params.uuid}`,
        { method: 'GET' }
    )
    order.value = res?.data
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchOrder)

// --- Computed states ---
const isSuccess = computed(() => {
  if (!order.value) return false
  return order.value.is_paid === 1 || order.value.transaction?.status === 'success'
})

const isFailure = computed(() => {
  if (!order.value) return false
  return order.value.is_paid === 0 && ['failed', 'cancelled'].includes(order.value.transaction?.status)
})
</script>

<template>
  <div class="w-full h-full flex items-center justify-center p-8">
    <div v-if="isLoading" class="text-center">
      <p class="text-gray-600 dark:text-gray-300">Loading order details...</p>
    </div>

    <div v-else-if="order" class="max-w-lg w-full text-center space-y-6">
      <!-- Success Case -->
      <template v-if="isSuccess">
        <div class="flex justify-center">
          <Icon name="heroicons:check-circle" class="w-16 h-16 text-green-600" />
        </div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
          Payment Successful 🎉
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
          Thank you for your order! Your payment has been confirmed.
        </p>
      </template>

      <!-- Failure Case -->
      <template v-else-if="isFailure">
        <div class="flex justify-center">
          <Icon name="heroicons:x-circle" class="w-16 h-16 text-red-600" />
        </div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
          Payment Failed ❌
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
          Unfortunately, your payment was not processed.
        </p>
      </template>

      <!-- Pending/Other Case -->
      <template v-else>
        <div class="flex justify-center">
          <Icon name="heroicons:clock" class="w-16 h-16 text-yellow-600" />
        </div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
          Payment Pending ⏳
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
          Your payment is still pending. Please complete it soon.
        </p>
      </template>

      <!-- Minimal Order Info -->
      <div
          class="p-4 rounded-lg border bg-white dark:bg-gray-900 dark:border-gray-700 text-left space-y-2"
      >
        <p><span class="font-semibold">Order ID:</span> {{ order.uuid }}</p>
        <p><span class="font-semibold">Amount:</span> {{ order.amount }}</p>
        <p><span class="font-semibold">Status:</span> {{ order.status }}</p>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row justify-center gap-3 mt-6">
        <NuxtLink
            :to="`/orders/${order.uuid}`"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
        >
          View Order
        </NuxtLink>

        <NuxtLink
            to="/"
            class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600"
        >
          Continue Shopping
        </NuxtLink>

        <!-- Retry only if failed -->
        <button
            v-if="isFailure && order.transaction?.uuid"
            @click="window.location.href = `${config.public.apiBase}/checkout/${order.transaction.uuid}`"
            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
        >
          Retry Payment
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
