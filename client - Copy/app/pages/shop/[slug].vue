<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const route = useRoute()
const slug = computed(() => (route.params.slug ? String(route.params.slug) : ''))

watch(slug, (nextSlug) => {
  if (!nextSlug) return
  navigateTo(`/shop/product/${nextSlug}`, { replace: true })
}, { immediate: true })
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <UContainer class="py-12">
      <div class="h-6 w-48 bg-slate-200 dark:bg-slate-800 rounded animate-pulse" />
    </UContainer>
  </div>
</template>
