<template>
  <div class="relative w-full group">
    <NuxtLink
        to="/cart"
        class="absolute top-2 right-2 flex items-center justify-center"
    >
      <div class="relative">
        <!-- Icon -->
        <Icon
            name="ph:shopping-cart-bold"
            size="32"
            class="text-gray-700 dark:text-gray-200 transition-colors"
        />

        <!-- Item count badge -->
        <span

            class="absolute -top-1.5 -right-1.5 z-10 flex h-5 w-5 items-center justify-center
                 text-xs font-bold text-white dark:text-white
                 bg-red-600 dark:bg-red-500 rounded-full"
        >
          {{ itemCount }}
        </span>

        <!-- Tooltip on hover -->
        <div
            class="absolute right-full ml-2 top-1/2 -translate-y-1/2 transform
                 opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100
                 transition-all duration-200 bg-gray-800 text-white
                 dark:bg-gray-200 dark:text-gray-900
                 text-xs rounded px-2 py-1 shadow-lg whitespace-nowrap z-20"
        >
          Go to your cart
        </div>
      </div>
    </NuxtLink>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCart } from '~/composables/useCart'

const { itemCount, fetchCart } = useCart()

onMounted(fetchCart)
</script>
