<template>
  <div class="hover:scale-105 transition transform">
    <img :src="product.thumbnail" class="h-40 w-full object-cover rounded mb-4" />
    <h3 class="font-semibold mb-1">{{ product.name }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ formatPrice(product.price) }}</p>
    <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">{{ product.short_description }}</p>
    <button @click.prevent="addToCart" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
      Add to Cart
    </button>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'

const props = defineProps<{ product: any }>()
const emit = defineEmits({
  added: (_product: any) => true, // ✅ no TSTupleType, no type error
})

function formatPrice(p: number | null) {
  return p != null ? `₹${p.toFixed(2)}` : ''
}

async function addToCart() {
  try {
    await fetch('/api/cart/add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sku: props.product.sku, quantity: 1 }),
    })
    emit('added', props.product)
    alert('Added to cart!')
  } catch {
    alert('Failed to add to cart.')
  }
}
</script>
