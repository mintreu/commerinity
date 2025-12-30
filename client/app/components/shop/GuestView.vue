<script setup lang="ts">
/**
 * Shop Page for Guest Users
 * Premium landing experience with:
 * - Featured categories showcase
 * - Product carousels (Trending, New Arrivals)
 * - Flash deals banner
 * - Sign-up CTAs
 */
import type { Product } from '~/types/catalog'

interface FeaturedCategory {
  name: string
  slug: string
  description: string
  thumbnail: string
  banner: string
  product_count: number
  sample_products: Array<{ name: string; image: string }>
}

interface Props {
  featuredCategories: FeaturedCategory[]
  trendingProducts: Product[]
  newArrivals: Product[]
}

defineProps<Props>()
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
    <!-- Background Orbs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden opacity-20">
      <div class="absolute top-20 -left-20 w-80 h-80 bg-gradient-to-r from-violet-400 to-purple-400 rounded-full blur-3xl animate-pulse" />
      <div class="absolute bottom-20 -right-20 w-80 h-80 bg-gradient-to-r from-fuchsia-400 to-pink-400 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;" />
    </div>

    <div class="relative">
      <!-- HERO SECTION -->
      <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600">
          <div class="absolute inset-0 bg-[url('/patterns/grid.svg')] opacity-10" />
        </div>

        <div class="relative max-w-7xl mx-auto px-4 md:px-6 py-16 md:py-24">
          <div class="text-center text-white space-y-6">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 backdrop-blur-md border border-white/30">
              <UIcon name="i-lucide-shopping-bag" class="w-4 h-4 text-yellow-300" />
              <span class="font-bold text-sm text-yellow-100">Premium Shopping</span>
            </div>

            <!-- Title -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight">
              <span class="block">Discover Amazing</span>
              <span class="block bg-gradient-to-r from-yellow-300 via-orange-300 to-red-300 bg-clip-text text-transparent">
                Products
              </span>
            </h1>

            <!-- Subtitle -->
            <p class="text-base md:text-lg text-white/90 max-w-2xl mx-auto">
              Quality products, fast delivery, and exclusive member rewards
            </p>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center pt-4">
              <UButton
                to="/shop/products"
                size="lg"
                color="white"
                class="font-bold shadow-xl w-full sm:w-auto"
              >
                <UIcon name="i-lucide-shopping-bag" class="w-5 h-5" />
                Browse All Products
              </UButton>
              <UButton
                to="/register"
                size="lg"
                variant="outline"
                color="white"
                class="font-bold border-2 backdrop-blur-md bg-white/10 w-full sm:w-auto"
              >
                <UIcon name="i-lucide-gift" class="w-5 h-5" />
                Join for Rewards
              </UButton>
            </div>
          </div>
        </div>
      </section>

      <!-- FEATURED CATEGORIES - Image-Rich Grid -->
      <section v-if="featuredCategories.length" class="py-12 md:py-16 bg-white dark:bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
          <!-- Section Header -->
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white">
                Shop by Category
              </h2>
              <p class="text-slate-600 dark:text-slate-400 mt-1">
                {{ featuredCategories.length }} categories with products in stock
              </p>
            </div>
            <NuxtLink
              to="/categories"
              class="hidden sm:flex items-center gap-1 text-primary-600 dark:text-primary-400 font-semibold hover:underline"
            >
              All Categories
              <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
            </NuxtLink>
          </div>

          <!-- Categories Grid - Premium Image Showcase -->
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
            <NuxtLink
              v-for="(category, index) in featuredCategories"
              :key="category.slug"
              :to="`/category/${category.slug}`"
              :class="[
                'group relative rounded-2xl overflow-hidden',
                index < 2 ? 'md:col-span-1 md:row-span-2 min-h-[280px] md:min-h-[400px]' : 'min-h-[180px] md:min-h-[200px]'
              ]"
            >
              <!-- Background Image/Gradient -->
              <div class="absolute inset-0">
                <img
                  v-if="category.banner || category.thumbnail"
                  :src="category.banner || category.thumbnail"
                  :alt="category.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                >
                <div v-else-if="category.sample_products?.length" class="w-full h-full grid grid-cols-2 grid-rows-2">
                  <div
                    v-for="(product, pIdx) in category.sample_products.slice(0, 4)"
                    :key="pIdx"
                    class="overflow-hidden"
                  >
                    <img
                      v-if="product.image"
                      :src="product.image"
                      :alt="product.name"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    >
                    <div v-else class="w-full h-full bg-slate-200 dark:bg-slate-700" />
                  </div>
                </div>
                <div v-else class="w-full h-full bg-gradient-to-br from-violet-500 to-purple-600" />

                <!-- Overlay Gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />
              </div>

              <!-- Content -->
              <div class="absolute bottom-0 left-0 right-0 p-4 md:p-6">
                <h3 class="text-white font-bold text-lg md:text-xl mb-1 group-hover:text-yellow-300 transition-colors">
                  {{ category.name }}
                </h3>
                <p class="text-white/80 text-sm">
                  {{ category.product_count }} products
                </p>

                <!-- Hover Arrow -->
                <div class="absolute bottom-4 right-4 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 transition-all">
                  <UIcon name="i-lucide-arrow-right" class="w-5 h-5 text-white" />
                </div>
              </div>
            </NuxtLink>
          </div>

          <!-- Mobile View All -->
          <div class="text-center mt-6 sm:hidden">
            <UButton to="/categories" variant="outline" color="primary" size="sm">
              View All Categories
              <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
            </UButton>
          </div>
        </div>
      </section>

      <!-- TRENDING PRODUCTS - Carousel -->
      <section v-if="trendingProducts.length" class="py-12 md:py-16 bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 dark:from-amber-950/20 dark:via-orange-950/20 dark:to-red-950/20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
          <StoreProductCarousel
            :products="trendingProducts"
            title="Trending Now"
            subtitle="Top picks this week"
            badge-text="HOT"
            badge-icon="i-lucide-flame"
            badge-color="amber"
            view-all-link="/shop/products?sort=popularity"
            :autoplay-interval="5000"
          />
        </div>
      </section>

      <!-- DEAL BANNER -->
      <section class="py-8 md:py-12 bg-gradient-to-r from-red-600 via-pink-600 to-fuchsia-600">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
          <NuxtLink to="/shop/deals" class="block group">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-white">
              <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                  <UIcon name="i-lucide-zap" class="w-8 h-8 text-yellow-300" />
                </div>
                <div>
                  <h3 class="text-xl md:text-2xl font-black">Flash Deals</h3>
                  <p class="text-white/90">Limited time offers - Up to 50% OFF</p>
                </div>
              </div>
              <UButton
                color="white"
                size="lg"
                class="font-bold shadow-xl group-hover:scale-105 transition-transform"
              >
                Shop Deals
                <UIcon name="i-lucide-arrow-right" class="w-5 h-5" />
              </UButton>
            </div>
          </NuxtLink>
        </div>
      </section>

      <!-- NEW ARRIVALS - Carousel -->
      <section v-if="newArrivals.length" class="py-12 md:py-16 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
          <StoreProductCarousel
            :products="newArrivals"
            title="Fresh Arrivals"
            subtitle="Just added to our collection"
            badge-text="NEW"
            badge-icon="i-lucide-sparkles"
            badge-color="emerald"
            view-all-link="/shop/products?sort=latest"
            :autoplay-interval="6000"
          />
        </div>
      </section>

      <!-- TRUST BADGES - Compact -->
      <section class="py-12 md:py-16 bg-slate-50 dark:bg-slate-800/50">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="text-center p-4 md:p-6 rounded-2xl bg-white dark:bg-slate-800 shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                <UIcon name="i-lucide-truck" class="w-6 h-6 text-white" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-sm md:text-base">Fast Delivery</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Free above ₹499</p>
            </div>

            <div class="text-center p-4 md:p-6 rounded-2xl bg-white dark:bg-slate-800 shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                <UIcon name="i-lucide-shield-check" class="w-6 h-6 text-white" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-sm md:text-base">100% Secure</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Safe payments</p>
            </div>

            <div class="text-center p-4 md:p-6 rounded-2xl bg-white dark:bg-slate-800 shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                <UIcon name="i-lucide-refresh-cw" class="w-6 h-6 text-white" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-sm md:text-base">Easy Returns</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">15-day policy</p>
            </div>

            <div class="text-center p-4 md:p-6 rounded-2xl bg-white dark:bg-slate-800 shadow-lg">
              <div class="w-12 h-12 bg-gradient-to-br from-fuchsia-500 to-pink-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                <UIcon name="i-lucide-gift" class="w-6 h-6 text-white" />
              </div>
              <h3 class="font-bold text-slate-900 dark:text-white text-sm md:text-base">Member Rewards</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Earn on purchases</p>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA for Guests -->
      <section class="py-12 md:py-16 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600">
        <div class="max-w-3xl mx-auto px-4 md:px-6 text-center text-white">
          <UIcon name="i-lucide-gift" class="w-12 h-12 mx-auto mb-4 text-yellow-300" />
          <h2 class="text-2xl md:text-3xl font-black mb-3">
            Unlock Exclusive Rewards
          </h2>
          <p class="text-white/90 mb-6">
            Join now to earn BV/PV points and get exclusive member discounts
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <UButton
              to="/register"
              size="lg"
              color="white"
              class="font-bold shadow-xl"
            >
              <UIcon name="i-lucide-user-plus" class="w-5 h-5" />
              Create Free Account
            </UButton>
            <UButton
              to="/login"
              size="lg"
              variant="outline"
              color="white"
              class="font-bold border-2 bg-white/10"
            >
              Sign In
            </UButton>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
