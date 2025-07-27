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
            v-if="itemCount > 0"
            class="absolute -top-2 -right-2 flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-600 dark:bg-red-500 rounded-full"
        >
          {{ itemCount }}
        </span>

        <!-- Tooltip on hover (left) -->
        <div
            class="absolute left-full ml-2 top-1/2 -translate-y-1/2 transform opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all duration-200 bg-gray-800 text-white dark:bg-gray-200 dark:text-gray-900 text-xs rounded px-2 py-1 shadow-lg whitespace-nowrap z-10"
        >
          Go to your cart
        </div>
      </div>
    </NuxtLink>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFetch } from '#app'

const itemCount = ref(0)

const fetchCartCount = async () => {
  try {
    const {data} = await useFetch('/api/cart/index', {
      credentials: 'include'
    })

    if (Array.isArray(data.value)) {
      itemCount.value = data.value.reduce((acc, item) => acc + (item.quantity || 1), 0)
    } else {
      itemCount.value = data.value?.reduce((acc, item) => acc + item.quantity, 0) || 0
    }
  } catch (e) {
    console.error('Cart fetch error:', e)
    itemCount.value = 0
  }
}

onMounted(fetchCartCount)
</script>
