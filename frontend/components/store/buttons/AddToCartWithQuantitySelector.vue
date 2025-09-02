<template>
  <div class="flex items-center gap-3">
    <!-- Quantity Selector -->
    <select
        v-model.number="selectedQuantity"
        class="px-3 py-2 border rounded bg-white dark:bg-gray-700 dark:text-white"
    >
      <option
          v-for="q in quantityOptions"
          :key="q"
          :value="q"
      >
        {{ q }}
      </option>
    </select>

    <!-- Add To Cart Button -->
    <button
        :disabled="loading"
        @click="handleAddToCart"
        class="px-6 py-3 rounded transition-colors duration-200"
        :class="loading
        ? 'bg-gray-400 text-gray-100 cursor-not-allowed'
        : 'bg-blue-600 text-white hover:bg-blue-700'
      "
    >
      <span v-if="loading">Adding...</span>
      <span v-else>Add to Cart</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useCart } from '~/composables/useCart'

const props = defineProps({
  sku: { type: String, required: true },
  minQuantity: { type: Number, default: 1 },
  maxQuantity: { type: Number, default: 10 }
})

const loading = ref(false)
const { addToCart } = useCart()

// Generate available options between minQuantity and maxQuantity
const quantityOptions = computed(() => {
  const result: number[] = []
  for (let i = props.minQuantity; i <= props.maxQuantity; i++) {
    result.push(i)
  }
  return result
})

const selectedQuantity = ref(props.minQuantity)

async function handleAddToCart() {
  if (!props.sku) return
  loading.value = true

  try {
    await addToCart(props.sku, selectedQuantity.value)
  } catch (e) {
    console.error('Add to cart failed:', e)
    alert('Failed to add to cart')
  } finally {
    loading.value = false
  }
}
</script>
