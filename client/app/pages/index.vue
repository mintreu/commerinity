<script setup lang="ts">
/**
 * Homepage - Mintreu E-Commerce & Affiliate Platform
 * Premium landing page with:
 * - Hero Section
 * - Real-time Stats
 * - Featured Products (Trending + New Arrivals)
 * - Category Showcase
 * - Benefits & Features
 * - CTA
 */
definePageMeta({
  layout: 'public'
})

useSeoMeta({
  title: 'VVIndia - Affiliate & E-Commerce Platform',
  description: 'Transform your business dreams into reality with VVIndia. The ultimate platform combining  E-Commerce, Business Marketing, and Career Opportunities — all in one powerful ecosystem.',
  ogTitle: 'VVIndia - Affiliate & E-Commerce Platform',
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

    <!-- Featured Products - Trending & New Arrivals -->
    <HomeFeaturedProducts />

    <!-- Category Showcase -->
    <HomeCategoryShowcase />

    <!-- Benefits Section - Why Join -->
    <HomeBenefitsSection />

    <!-- Features Section - Platform Features -->
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
