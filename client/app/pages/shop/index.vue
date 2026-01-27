<script setup lang="ts">
/**
 * Shop Page - Auth-Aware Layout
 * - Guest: Landing page with carousels, featured categories, CTAs
 * - Auth User: Product listing with filters (Flipkart/Amazon style)
 */

import type { Product } from '~/types/catalog'

definePageMeta({
  layout: 'default'
})

interface FeaturedCategory {
  name: string
  slug: string
  description: string
  thumbnail: string
  banner: string
  product_count: number
  sample_products: Array<{ name: string, image: string }>
}

// SEO
useComprehensiveSeo({
  title: 'Shop Premium Products Online',
  description: 'Explore thousands of quality products across all categories. Fast delivery, secure payments, and exclusive member rewards await you.',
  keywords: ['online shopping', 'e-commerce', 'premium products', 'member rewards', 'quality products'],
  type: 'website'
})

// Auth state
const { isLoggedIn } = useSanctum()
const user = useCurrentUser()

// API
const config = useRuntimeConfig()

// Get user's first name for greeting
const userName = computed(() => {
  if (!isLoggedIn.value || !user.value) return ''
  return user.value.name?.split(' ')[0] || user.value.name || 'Member'
})

// Guest data - only fetch for guests
const { data: featuredCategoriesData } = await useFetch<{
  success: boolean
  data: FeaturedCategory[]
}>(`${config.public.apiBase}/api/catalog/categories/featured?limit=6`, {
  lazy: true,
  server: false,
  immediate: computed(() => !isLoggedIn.value).value
})

const { data: featuredData } = await useFetch<{
  success: boolean
  data: {
    best_sellers: Product[]
    new_arrivals: Product[]
  }
}>(`${config.public.apiBase}/api/catalog/featured`, {
  lazy: true,
  server: false,
  immediate: computed(() => !isLoggedIn.value).value
})

// Parse guest data
const featuredCategories = computed(() => featuredCategoriesData.value?.data || [])
const trendingProducts = computed(() => featuredData.value?.data?.best_sellers || [])
const newArrivals = computed(() => featuredData.value?.data?.new_arrivals || [])
</script>

<template>
  <div>
    <!-- Auth User View: Product list with filters (Flipkart-style) -->
    <ShopAuthUserView
      v-if="isLoggedIn"
      :user-name="userName"
    />

    <!-- Guest View: Landing page with carousels -->
    <ShopGuestView
      v-else
      :featured-categories="featuredCategories"
      :trending-products="trendingProducts"
      :new-arrivals="newArrivals"
    />
  </div>
</template>
