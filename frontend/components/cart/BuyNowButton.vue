<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCart } from '~/composables/useCart'

const props = defineProps({
  sku: { type: String, required: true },
  minQuantity: { type: Number, default: 1 }
})

const loading = ref(false)
const { updateCartItem } = useCart()
const router = useRouter()

async function handleBuyNow() {
  if (!props.sku) return
  loading.value = true

  try {
    await updateCartItem(props.sku, props.minQuantity)
    // redirect to /cart after successful add
    router.push('/cart')
  } catch (e) {
    console.error('Add to cart failed:', e)
    alert('Failed to add to cart')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <!-- Buy Now Button -->
  <button
      :disabled="loading"
      @click="handleBuyNow"
      class="text-center w-full px-5 py-4 rounded-full bg-indigo-600 flex items-center justify-center font-semibold text-lg text-white shadow-sm shadow-transparent transition-all duration-500 hover:bg-indigo-700 hover:shadow-indigo-300 mb-8 disabled:opacity-50"
  >
    {{ loading ? 'Processing...' : 'Buy Now' }}
  </button>
</template>

<style scoped>
</style>
