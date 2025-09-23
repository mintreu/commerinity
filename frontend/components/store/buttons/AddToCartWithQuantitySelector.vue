<template>
  <div class="flex items-center gap-3">




    <!-- Quantity Selector -->
    <div class="flex items-center justify-center border border-gray-400 dark:border-gray-600 rounded-full">
      <button
          @click="decreaseQuantity"
          :disabled="selectedQuantity <= (props.minQuantity)"
          class="group py-[14px] px-3 w-full border-r border-gray-400 dark:border-gray-600 rounded-l-full h-full flex items-center justify-center bg-white dark:bg-gray-700 transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <Icon name="mdi:minus" class="w-5 h-5 text-gray-700 dark:text-gray-300"/>
      </button>
      <input
          v-model="selectedQuantity"
          type="number"
          :min="props.minQuantity || 1"
          :max="props.maxQuantity || 10"
          class="font-semibold text-gray-900 dark:text-white text-lg py-3 px-2 w-full min-[400px]:min-w-[75px] h-full bg-transparent text-center outline-0 hover:text-indigo-600"
      />
      <button
          @click="increaseQuantity"
          :disabled="selectedQuantity >= (props.maxQuantity)"
          class="group py-[14px] px-3 w-full border-l border-gray-400 dark:border-gray-600 rounded-r-full h-full flex items-center justify-center bg-white dark:bg-gray-700 transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <Icon name="mdi:plus" class="w-5 h-5 text-gray-700 dark:text-gray-300"/>
      </button>

    </div>



    <!-- Add To Cart Button -->
    <button
        :disabled="loading"
        @click="handleAddToCart"
        class="group py-3 px-5 rounded-full bg-indigo-50 text-indigo-600 font-semibold text-lg w-full flex items-center justify-center gap-2 shadow-sm shadow-transparent transition-all duration-500 hover:shadow-indigo-300 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30"
        :class="loading
        ? 'cursor-not-allowed'
        : 'hover:bg-blue-700'
      "
    >
      <Icon name="mdi:cart-plus" class="w-5 h-5"/>
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


const decreaseQuantity = () => {
  if (selectedQuantity.value > (props.minQuantity)) {
    selectedQuantity.value--
  }
}

const increaseQuantity = () => {
  if (selectedQuantity.value < (props.maxQuantity)) {
    selectedQuantity.value++
  }
}



</script>
