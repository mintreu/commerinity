<script setup lang="ts">
/**
 * Smart Category Card
 * Shows category with child count and thumbnail
 */
import type { Category } from '~/types/catalog'

interface Props {
  category: Category
  featured?: boolean
}

defineProps<Props>()
</script>

<template>
  <NuxtLink
    :to="`/category/${category.url}`"
    :class="[
      'group block bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300',
      featured ? 'md:col-span-2' : ''
    ]"
  >
    <div :class="[
      'relative overflow-hidden bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900/30 dark:to-fuchsia-900/30',
      featured ? 'h-48' : 'h-32'
    ]">
      <img
        v-if="category.thumbnail"
        :src="category.thumbnail"
        :alt="category.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
      >
      <div v-else class="w-full h-full flex items-center justify-center">
        <UIcon name="i-lucide-folder-open" class="w-16 h-16 text-primary-400 opacity-50" />
      </div>
      
      <!-- Overlay gradient -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent" />
      
      <!-- Product count badge -->
      <div class="absolute top-3 right-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-slate-900 dark:text-white">
        {{ category.product_count || 0 }} products
      </div>
    </div>

    <div class="p-4">
      <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
        {{ category.name }}
      </h3>
      
      <p v-if="category.description" class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mb-3">
        {{ category.description }}
      </p>
      
    </div>
  </NuxtLink>
</template>
