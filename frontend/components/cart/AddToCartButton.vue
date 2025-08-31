<template>
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
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useCart } from '~/composables/useCart'

const props = defineProps({
  sku: { type: String, required: true },
  quantity: { type: Number, default: 1 }
})

const loading = ref(false)
const { addToCart } = useCart()

async function handleAddToCart() {
  if (!props.sku) return
  loading.value = true

  try {
    await addToCart(props.sku, props.quantity)

    // ✅ cartData updates automatically via composable,
    // so CartCounter & MyCart will react instantly
  } catch (e) {
    console.error('Add to cart failed:', e)
    alert('Failed to add to cart')
  } finally {
    loading.value = false
  }
}
</script>
