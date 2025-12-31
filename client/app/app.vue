<template>
  <UApp :toaster="toasterConfig">
    <!-- Global Loader -->
    <AppLoader
      :show="isLoading"
      :message="loadingMessage"
      full-screen
    />

    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const router = useRouter()
const { isLoading, loadingMessage, startLoading, stopLoading } = useLoading()

// Handle route navigation loading
router.beforeEach((to, from, next) => {
  // Only show loader for actual page changes, not just hash changes
  if (to.path !== from.path) {
    startLoading()
  }
  next()
})

router.afterEach(() => {
  // Add a tiny delay for smoother transition
  setTimeout(() => {
    stopLoading()
  }, 300)
})

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
