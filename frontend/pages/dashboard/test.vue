<template>
  <div class="min-h-screen flex flex-col items-center justify-center p-4 bg-gradient-to-br from-pink-200 via-purple-200 to-purple-300 dark:from-pink-900 dark:via-purple-800 dark:to-purple-700 transition-colors duration-300">
    <!-- Main heading with accessibility attributes -->
    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white text-center" aria-label="Welcome to the test page">
      Test Page for Lighthouse
    </h1>
    <!-- Brief description for content -->
    <p class="mt-4 text-lg text-gray-700 dark:text-gray-200 text-center max-w-2xl" aria-describedby="page-description">
      A minimal Nuxt page with dark mode and gradient, optimized for Lighthouse scores.
    </p>
  </div>
</template>

<script setup lang="ts">

definePageMeta({ layout: 'dashboard' })

// Use Nuxt's built-in useHead for SEO
useHead({
  title: 'Lighthouse Test Page with Dark Mode',
  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { name: 'description', content: 'A minimal Nuxt page with dark mode and purple-pink gradient, optimized for Lighthouse scores, performance, accessibility, and SEO.' },
    { name: 'robots', content: 'index, follow' },
  ],
  htmlAttrs: {
    lang: 'en',
  },
})
</script>

<style scoped>
/* Minimal scoped styles for performance */
h1, p {
  font-family: system-ui, -apple-system, sans-serif;
}
</style>