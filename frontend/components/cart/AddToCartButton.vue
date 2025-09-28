<template>
  <button
      :disabled="loading || processing"
      @click="handleAddToCart"
      class="px-6 py-3 rounded transition-all duration-200 flex items-center justify-center gap-2 min-w-[140px]"
      :class="loading || processing
        ? 'bg-gray-400 text-gray-100 cursor-not-allowed'
        : 'bg-blue-600 text-white hover:bg-blue-700 active:scale-95'
      "
  >
    <div v-if="loading || processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
    <Icon v-else name="mdi:cart-plus" class="w-4 h-4" />
    <span>{{ loading || processing ? 'Adding...' : 'Add to Cart' }}</span>
  </button>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useCart } from '~/composables/useCart'

const props = defineProps({
  sku: { type: String, required: true },
  quantity: { type: Number, default: 1 }
})

const emit = defineEmits<{
  success: [sku: string, quantity: number]
  error: [error: any]
}>()

const loading = ref(false)
const { addToCart, loading: globalLoading, hasError, errorMessage } = useCart()

const processing = computed(() => globalLoading.value)

async function handleAddToCart() {
  if (!props.sku || loading.value || processing.value) return

  loading.value = true

  try {
    await addToCart(props.sku, props.quantity)

    // Success feedback
    emit('success', props.sku, props.quantity)
    console.log(`✅ Added ${props.quantity}x ${props.sku} to cart`)

  } catch (e: any) {
    console.error('Add to cart failed:', e)
    emit('error', e)

    // Enhanced error messages based on backend errors
    let errorMessage = 'Failed to add to cart'

    if (e.status === 419) {
      errorMessage = 'Session expired. Please refresh the page.'
    } else if (e.status === 422) {
      errorMessage = 'Invalid product or quantity selected'
    } else if (e.status === 404) {
      errorMessage = 'Product not available'
    } else if (e.status >= 500) {
      errorMessage = 'Server error. Please try again in a moment.'
    } else if (e.message?.includes('credential')) {
      errorMessage = 'Unable to establish cart session. Please refresh the page.'
    }

    // You can integrate with a toast system here instead of alert
    alert(errorMessage)
  } finally {
    loading.value = false
  }
}
</script>
