<script setup lang="ts">
/**
 * Smart Category Breadcrumb
 * Shows navigation path: Home > Parent > Current
 */
import type { Category } from '~/types/catalog'

interface Props {
  category: Category
  ancestors?: Category[]
}

defineProps<Props>()
</script>

<template>
  <nav class="flex items-center gap-2 text-sm mb-6">
    <NuxtLink
      to="/"
      class="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
    >
      <UIcon
        name="i-lucide-home"
        class="w-4 h-4"
      />
    </NuxtLink>

    <UIcon
      name="i-lucide-chevron-right"
      class="w-4 h-4 text-slate-400"
    />

    <template v-if="ancestors && ancestors.length">
      <template
        v-for="ancestor in ancestors"
        :key="ancestor.id"
      >
        <NuxtLink
          :to="`/category/${ancestor.slug}`"
          class="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
          {{ ancestor.name }}
        </NuxtLink>
        <UIcon
          name="i-lucide-chevron-right"
          class="w-4 h-4 text-slate-400"
        />
      </template>
    </template>

    <span class="text-slate-900 dark:text-white font-semibold">
      {{ category.name }}
    </span>
  </nav>
</template>
