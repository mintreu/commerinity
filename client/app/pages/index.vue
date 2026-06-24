<script setup lang="ts">
/**
 * Homepage - VRIDDHI VIKASH E-Commerce & Affiliate Platform
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
const config = useRuntimeConfig()

useSeoMeta({
  title: 'VRIDDHI VIKASH – Your Premier Online Shopping Destination',
  description: 'Experience the best in online shopping with VRIDDHI VIKASH. Discover a wide range of premium products, exclusive deals, and a seamless shopping experience delivered right to your doorstep.',
  ogTitle: 'VRIDDHI VIKASH - Your Premier Online Shopping Destination',
  ogDescription: 'Shop the latest trends and premium quality products at VRIDDHI VIKASH. Quality products, fast delivery, and exceptional customer service.',
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
