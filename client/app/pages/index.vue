<script setup lang="ts">
definePageMeta({
  layout: 'guest'
})

useSeoMeta({
  title: 'Mintreu - Your All-in-One Business Platform',
  description: 'Transform your business dreams into reality with Mintreu. The ultimate platform combining Network Marketing, E-Commerce, and Career Opportunities — all in one powerful ecosystem.',
  ogTitle: 'Mintreu - Your All-in-One Business Platform',
  ogDescription: 'Join thousands of successful entrepreneurs. Network Marketing, E-Commerce & Career Opportunities all in one place.',
  ogImage: '/logo.png'
})
</script>

<template>
  <div class="homepage bg-gradient-to-br from-gray-50 via-purple-50/30 to-pink-50/20 dark:from-slate-950 dark:via-purple-950/20 dark:to-slate-950 overflow-x-hidden">
    <!-- Hero Section -->
    <HomeHeroSection />

    <!-- Stats Section - Real data from API -->
    <HomeStatsSection />

    <!-- Benefits Section - Static marketing content -->
    <HomeBenefitsSection />

    <!-- Features Section - Static feature descriptions -->
    <HomeFeaturesSection />

    <!-- CTA Section -->
    <HomeCtaSection />
  </div>
</template>

<style scoped>
.homepage {
  min-height: 100vh;
}
</style>
