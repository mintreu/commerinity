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
  description: 'Explore thousands of quality products across all categories. Fast delivery, secure payments, and exclusive rewards await you.',
  keywords: ['online shopping', 'e-commerce', 'premium products', 'exclusive rewards', 'quality products'],
  type: 'website'
})

// Auth state
const { isLoggedIn } = useSanctum()
const user = useCurrentUser()

// Get user's first name for greeting
const userName = computed(() => {
  if (!isLoggedIn.value || !user.value) return ''
  return user.value.name?.split(' ')[0] || user.value.name || 'Customer'
})

const featuredCategoriesData = ref<{ success: boolean, data: FeaturedCategory[] } | null>(null)
const featuredData = ref<{
  success: boolean
  data: {
    best_sellers: Product[]
    new_arrivals: Product[]
  }
} | null>(null)

const loadGuestData = async () => {
  if (isLoggedIn.value) return

  try {
    featuredCategoriesData.value = await useSanctumFetch('/api/catalog/categories/featured?limit=6')
  } catch {
    featuredCategoriesData.value = null
  }

  try {
    featuredData.value = await useSanctumFetch('/api/catalog/featured')
  } catch {
    featuredData.value = null
  }
}

onMounted(() => {
  loadGuestData()
})

watch(isLoggedIn, (loggedIn) => {
  if (!loggedIn) {
    loadGuestData()
  }
})

// Parse guest data
const featuredCategories = computed(() => featuredCategoriesData.value?.data || [])
const trendingProducts = computed(() => featuredData.value?.data?.best_sellers || [])
const newArrivals = computed(() => featuredData.value?.data?.new_arrivals || [])
</script>

<template>
  <div>
    <!-- Guest View: Landing page with carousels -->
    <ShopGuestView
      v-if="!isLoggedIn"
      :featured-categories="featuredCategories"
      :trending-products="trendingProducts"
      :new-arrivals="newArrivals"
    />

    <!-- Product list with filters (Flipkart-style) for all users -->
    <ShopAuthUserView
      v-else
      :user-name="userName"
    />
  </div>
</template>
