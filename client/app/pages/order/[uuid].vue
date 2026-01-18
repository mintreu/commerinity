<script setup lang="ts">
definePageMeta({
  middleware: '$auth',
  layout: 'default'
})

const route = useRoute()
const config = useRuntimeConfig()
const toast = useToast()

const { fetchOrder, currentOrder, isLoading, getStatusColor, getStatusIcon } = useOrders()

onMounted(async () => {
  const uuid = route.params.uuid as string
  const res = await fetchOrder(uuid)
  if (!res || !res.success) {
    toast.add({
      title: 'Order Not Found',
      description: 'Unable to load this order. It may not exist or you may not have access.',
      color: 'error'
    })
    navigateTo('/orders')
  }
})

const formatDate = (dateString?: string) => {
  if (!dateString) return '—'
  try {
    return new Date(dateString).toLocaleString('en-IN', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
        Order Details
      </h1>
      <p class="text-sm text-slate-600 dark:text-slate-400">
        View your order summary, items, and payment status
      </p>
    </div>

    <div v-if="isLoading" class="space-y-4">
      <USkeleton class="h-10 w-64" />
      <USkeleton class="h-24 w-full" />
      <USkeleton class="h-24 w-full" />
    </div>

    <div v-else-if="currentOrder" class="space-y-6">
      <!-- Order Header -->
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">Order #</p>
              <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ currentOrder.order_number }}
              </h2>
            </div>
            <UBadge :color="getStatusColor(currentOrder.status)">
              <UIcon :name="getStatusIcon(currentOrder.status)" class="w-4 h-4 mr-1" />
              {{ currentOrder.status_label }}
            </UBadge>
          </div>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Placed On</p>
            <p class="font-medium text-slate-900 dark:text-white">
              {{ formatDate(currentOrder.created_at) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Payment</p>
            <p class="font-medium text-slate-900 dark:text-white flex items-center gap-1">
              <UIcon
                :name="currentOrder.payment_success ? 'i-lucide-check-circle-2' : 'i-lucide-clock-3'"
                class="w-4 h-4"
                :class="currentOrder.payment_success ? 'text-emerald-500' : 'text-amber-500'"
              />
              {{ currentOrder.payment_status }}
            </p>
          </div>
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Tracking ID</p>
            <p class="font-medium text-primary-600 dark:text-primary-400">
              {{ currentOrder.tracking_id || '—' }}
            </p>
          </div>
        </div>
      </UCard>

      <!-- Items -->
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            Order Items ({{ currentOrder.quantity }})
          </h3>
        </template>

        <div class="divide-y divide-slate-200 dark:divide-slate-700">
          <div
            v-for="item in currentOrder.items"
            :key="item.id"
            class="py-4 grid grid-cols-1 sm:grid-cols-12 gap-4"
          >
            <div class="sm:col-span-1 flex items-center">
              <img
                v-if="item.image"
                :src="typeof item.image === 'string' ? item.image : (item.image.src || item.image.url)"
                :alt="item.product_name"
                class="w-16 h-16 object-cover rounded-lg"
              />
              <div v-else class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center">
                <UIcon name="i-lucide-package" class="w-6 h-6 text-slate-400" />
              </div>
            </div>
            <div class="sm:col-span-6">
              <NuxtLink :to="`/shop/${item.product_slug}`" class="font-medium text-slate-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                {{ item.product_name }}
              </NuxtLink>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Qty: {{ item.quantity }}
              </p>
            </div>
            <div class="sm:col-span-2 text-sm text-slate-500 dark:text-slate-400">
              {{ item.unit_price_formatted }}
            </div>
            <div class="sm:col-span-3 text-right">
              <p class="font-semibold text-slate-900 dark:text-white">
                {{ item.subtotal_formatted }}
              </p>
            </div>
          </div>
        </div>
      </UCard>

      <!-- Summary -->
      <UCard>
        <template #header>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
            Order Summary
          </h3>
        </template>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-slate-400">Subtotal</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ currentOrder.subtotal_formatted }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-slate-400">Shipping</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ currentOrder.shipping_cost_formatted }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-slate-400">Tax</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ currentOrder.tax_formatted }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-600 dark:text-slate-400">Discount</span>
            <span class="font-medium text-slate-900 dark:text-white">{{ currentOrder.discount_formatted }}</span>
          </div>
          <div class="border-t border-slate-200 dark:border-slate-700 my-2" />
          <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-slate-900 dark:text-white">Total</span>
            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
              {{ currentOrder.total_formatted }}
            </span>
          </div>
        </div>
      </UCard>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <UButton to="/orders" variant="soft">
          Back to Orders
        </UButton>
        <UButton v-if="!currentOrder.payment_success" :to="`/checkout/${currentOrder.uuid}`" color="primary">
          Complete Payment
        </UButton>
      </div>
    </div>

    <UAlert
      v-else
      color="error"
      icon="i-lucide-alert-circle"
      title="Order Not Found"
      description="We couldn't find the order you are looking for."
    >
      <template #actions>
        <UButton to="/orders" size="sm">Go to Orders</UButton>
      </template>
    </UAlert>
  </div>
</template>

