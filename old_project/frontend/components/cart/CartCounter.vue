<template>
  <div class="relative w-full group">
    <NuxtLink
        to="/cart"
        class="absolute top-2 right-2 flex items-center justify-center"
    >
      <div class="relative">
        <!-- Cart Icon -->
        <Icon
            name="ph:shopping-cart-bold"
            size="32"
            class="text-gray-700 dark:text-gray-200 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400"
        />

        <!-- Item count badge -->
        <span
            v-if="itemCount > 0"
            class="absolute -top-1.5 -right-1.5 z-10 flex h-5 w-5 items-center justify-center
                 text-xs font-bold text-white
                 bg-red-600 dark:bg-red-500 rounded-full animate-pulse"
        >
          {{ displayCount }}
        </span>

        <!-- Loading indicator -->
        <div
            v-if="loading"
            class="absolute -top-1 -right-1 w-3 h-3 border border-blue-600 border-t-transparent rounded-full animate-spin"
        ></div>

        <!-- Tooltip on hover -->
        <div
            class="absolute right-full mr-2 top-1/2 -translate-y-1/2 transform
                 opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100
                 transition-all duration-200 bg-gray-800 text-white
                 dark:bg-gray-200 dark:text-gray-900
                 text-xs rounded px-2 py-1 shadow-lg whitespace-nowrap z-20"
        >
          <template v-if="itemCount > 0">
            {{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} in cart
          </template>
          <template v-else>
            Your cart is empty
          </template>
        </div>
      </div>
    </NuxtLink>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useCart } from '~/composables/useCart'

const { itemCount, fetchCart, loading, hasError, errorMessage } = useCart()

// Display count with max limit for UI
const displayCount = computed(() => {
  const count = itemCount.value
  return count > 99 ? '99+' : count.toString()
})

// Watch for errors and handle them
watch(hasError, (hasErr) => {
  if (hasErr && errorMessage.value) {
    console.warn('[CartCounter] Cart error:', errorMessage.value)
  }
})

onMounted(async () => {
  // Fetch cart on mount
  await fetchCart()
})
</script>
