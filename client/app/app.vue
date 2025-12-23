<template>
  <UApp :toaster="toasterConfig">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()

// Toast configuration - bottom-right position, 5 second duration
const toasterConfig = {
  position: 'bottom-right' as const,
  duration: 5000,
  max: 5
}

useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} | ${config.public.companyName}` : config.public.companyName
  },
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1' }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: 'en'
  }
})
</script>
