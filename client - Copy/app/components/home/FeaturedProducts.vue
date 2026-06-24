<script setup lang="ts">
/**
 * Featured Products Section for Homepage
 * Uses enhanced ProductCarousel for desktop with autoplay
 * Fetches featured products from /api/catalog/featured
 */
import type { Product } from '~/types/catalog'

interface FeaturedResponse {
  success: boolean
  data: {
    best_sellers: Product[]
    new_arrivals: Product[]
  }
}

const config = useRuntimeConfig()

const featuredResponse = ref<FeaturedResponse | null>(null)
const status = ref<'pending' | 'success' | 'error'>('pending')

const loadFeatured = async () => {
  status.value = 'pending'
  try {
    featuredResponse.value = await useSanctumFetch(`${config.public.apiBase}/api/catalog/featured`)
    status.value = 'success'
  } catch {
    status.value = 'error'
  }
}

const trendingProducts = computed(() => featuredResponse.value?.data?.best_sellers || [])
const newArrivals = computed(() => featuredResponse.value?.data?.new_arrivals || [])
const isLoading = computed(() => status.value === 'pending')

onMounted(() => {
  loadFeatured()
})
</script>

<template>
  <section class="py-16 md:py-24 section-gradient-secondary relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-50">
      <div class="floating-orb floating-orb-primary orb-lg top-20 -right-20" />
      <div class="floating-orb floating-orb-secondary orb-md bottom-20 -left-10" />
    </div>

    <UContainer class="relative z-10">
      <!-- Section Header -->
      <div class="text-center mb-12 md:mb-16">
        <div class="premium-badge mx-auto mb-4">
          <UIcon
            name="i-lucide-trending-up"
            class="w-4 h-4 mr-2"
          />
          <span>Featured Products</span>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-4">
          <span class="text-slate-900 dark:text-white">Shop Our</span>
          <span class="block gradient-text-primary">Top Picks</span>
        </h2>

        <p class="text-slate-600 dark:text-slate-300 text-base md:text-lg max-w-3xl mx-auto">
          Discover our <span class="font-bold text-violet-600 dark:text-violet-400">trending products</span> and
          <span class="font-bold text-pink-600 dark:text-pink-400">newest arrivals</span>
        </p>
      </div>

      <!-- Content -->
      <div class="space-y-16">
        <!-- Trending Products Carousel -->
        <StoreProductCarousel
          :products="trendingProducts"
          :loading="isLoading"
          title="Trending Products"
          subtitle="Most popular items this week"
          view-all-link="/shop/products?sort=popularity"
          badge-text="HOT"
          badge-icon="i-lucide-flame"
          badge-color="amber"
          :autoplay="true"
          :autoplay-interval="6000"
        />

        <!-- New Arrivals Carousel -->
        <StoreProductCarousel
          :products="newArrivals"
          :loading="isLoading"
          title="New Arrivals"
          subtitle="Fresh additions to our collection"
          view-all-link="/shop/products?sort=newest"
          badge-text="NEW"
          badge-icon="i-lucide-sparkles"
          badge-color="emerald"
          :autoplay="true"
          :autoplay-interval="7000"
        />

        <!-- Empty State -->
        <div
          v-if="!isLoading && !trendingProducts.length && !newArrivals.length"
          class="text-center py-12"
        >
          <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
            <UIcon
              name="i-lucide-package"
              class="w-10 h-10 text-slate-400"
            />
          </div>
          <p class="text-slate-500 dark:text-slate-400 mb-4">
            Products coming soon!
          </p>
          <NuxtLink to="/shop">
            <UButton color="primary">
              Browse Shop
            </UButton>
          </NuxtLink>
        </div>
      </div>

      <!-- Shop CTA -->
      <div class="text-center mt-16">
        <NuxtLink to="/shop">
          <UButton
            size="xl"
            color="primary"
            class="px-8 py-4 font-bold shadow-xl shadow-violet-500/25 hover:shadow-2xl hover:shadow-violet-500/40 hover:scale-105 transition-all duration-300"
          >
            <UIcon
              name="i-lucide-shopping-bag"
              class="w-5 h-5 mr-2"
            />
            Explore All Products
            <UIcon
              name="i-lucide-arrow-right"
              class="w-5 h-5 ml-2"
            />
          </UButton>
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
