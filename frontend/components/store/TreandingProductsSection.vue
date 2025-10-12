<template>
  <section class="w-full py-10 sm:py-16 lg:py-20 px-3 sm:px-6 lg:px-8 bg-gradient-to-b from-white via-purple-50/30 to-white dark:from-gray-900 dark:via-purple-950/20 dark:to-gray-900 relative overflow-hidden">

    <!-- Background Decorations -->
    <div class="absolute inset-0 opacity-5 pointer-events-none">
      <div class="absolute top-20 left-10 w-64 h-64 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full blur-3xl" />
      <div class="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full blur-3xl" />
    </div>

    <div class="w-full max-w-7xl mx-auto relative z-10">

      <!-- Section Header -->
      <div class="text-center mb-8 sm:mb-12 lg:mb-16">
        <div class="section-badge inline-flex items-center px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm mb-3 sm:mb-6">
          <Icon name="mdi:fire" class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-purple-600 dark:text-purple-400 flex-shrink-0 animate-pulse" />
          <span class="text-[10px] sm:text-xs md:text-sm font-medium text-purple-700 dark:text-purple-300">Trending Now</span>
        </div>
        <h2 class="text-xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-3 sm:mb-6 px-2">
          <span class="text-gray-900 dark:text-white">Hot Trending</span><br>
          <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Products</span>
        </h2>
        <p class="text-xs sm:text-base md:text-lg lg:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto px-3">Most viewed and fastest-selling items right now</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
        <div class="flex flex-col items-center gap-4">
          <div class="w-16 h-16 border-4 border-purple-600 border-t-transparent rounded-full animate-spin" />
          <p class="text-gray-600 dark:text-gray-400 font-medium">Loading trending products...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex items-center justify-center min-h-[400px]">
        <div class="text-center">
          <Icon name="mdi:alert-circle" class="w-16 h-16 text-pink-500 mx-auto mb-4" />
          <p class="text-pink-600 dark:text-pink-400 font-medium mb-2">{{ error }}</p>
          <button @click="fetchProducts" class="mt-4 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            Try Again
          </button>
        </div>
      </div>

      <!-- Products Display -->
      <div v-else-if="products.length > 0" class="w-full">

        <!-- MOBILE: Use ProductCard (< sm) -->
        <div class="block sm:hidden w-screen overflow-x-hidden">
          <Swiper
              :modules="swiperModules"
              :slides-per-view="1.2"
              :space-between="8"
              :centered-slides="true"
              :loop="shouldEnableLoop"
              :autoplay="shouldEnableLoop ? { delay: 4000, disableOnInteraction: false } : false"
              :pagination="{ clickable: true, dynamicBullets: true }"
              :breakpoints="{
                400: { slidesPerView: 1.3, spaceBetween: 12 },
                500: { slidesPerView: 1.5, spaceBetween: 16 }
              }"
              class="!pb-12"
          >
            <SwiperSlide v-for="product in products" :key="product.sku">
              <ProductCard
                  :product="product"
                  :show-wishlist="true"
              />
            </SwiperSlide>

            <!-- View All CTA Slide -->
            <SwiperSlide>
              <NuxtLink to="/store" class="block h-full">
                <div class="h-full min-h-[480px] flex items-center justify-center bg-gradient-to-br from-purple-600 via-pink-600 to-purple-600 rounded-2xl p-6 text-center text-white relative overflow-hidden group shadow-2xl border-2 border-white/20">
                  <!-- Shimmer Effect -->
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000" />

                  <div class="relative z-10 flex flex-col items-center space-y-4">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                      <Icon name="mdi:store" class="w-12 h-12" />
                    </div>
                    <h3 class="text-2xl font-black">Explore All</h3>
                    <p class="text-sm opacity-90 max-w-[200px]">Discover thousands of trending products</p>
                    <div class="bg-white text-purple-600 font-bold px-6 py-3 rounded-xl text-base hover:scale-110 hover:shadow-2xl transition-all mt-4 inline-flex items-center gap-2">
                      <span>View Store</span>
                      <Icon name="mdi:arrow-right" class="w-5 h-5" />
                    </div>
                  </div>
                </div>
              </NuxtLink>
            </SwiperSlide>
          </Swiper>
        </div>

        <!-- TABLET/DESKTOP: Coverflow Effect (sm+) -->
        <div class="hidden sm:block products-swiper relative">
          <Swiper
              :modules="swiperModules"
              :slides-per-view="1"
              :space-between="20"
              :centered-slides="true"
              :loop="shouldEnableLoop"
              :autoplay="shouldEnableLoop ? { delay: 4000, disableOnInteraction: false } : false"
              :effect="'coverflow'"
              :coverflow-effect="{ rotate: 10, stretch: 0, depth: 100, modifier: 1, slideShadows: true }"
              :pagination="{ clickable: true, dynamicBullets: true }"
              :breakpoints="{
                640: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 25 }
              }"
              class="pb-12"
          >
            <SwiperSlide v-for="(product, index) in products" :key="product.sku || index">
              <NuxtLink :to="`/product/${product.url}`" class="block h-full">
                <ProductCard
                    :product="product"
                    :show-wishlist="true"
                />
              </NuxtLink>
            </SwiperSlide>

            <!-- View All CTA Slide -->
            <SwiperSlide>
              <NuxtLink to="/store" class="block h-full">
                <div class="h-full min-h-[500px] sm:min-h-[540px] flex items-center justify-center bg-gradient-to-br from-purple-600 via-pink-600 to-purple-600 rounded-2xl p-8 text-center text-white relative overflow-hidden group shadow-2xl border-2 border-white/20">
                  <!-- Animated Background -->
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000" />

                  <div class="relative z-10 flex flex-col items-center space-y-5">
                    <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                      <Icon name="mdi:store" class="w-14 h-14" />
                    </div>
                    <h3 class="text-3xl lg:text-4xl font-black">Explore All Products</h3>
                    <p class="text-base lg:text-lg opacity-90 max-w-xs">Browse our complete collection of trending items</p>
                    <div class="bg-white text-purple-600 font-bold px-8 py-4 rounded-xl text-lg hover:scale-110 hover:shadow-2xl transition-all mt-4 inline-flex items-center gap-2">
                      <Icon name="mdi:fire" class="w-6 h-6 animate-pulse" />
                      <span>Visit Store</span>
                      <Icon name="mdi:arrow-right" class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                    </div>
                  </div>
                </div>
              </NuxtLink>
            </SwiperSlide>
          </Swiper>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex items-center justify-center min-h-[400px]">
        <div class="text-center">
          <Icon name="mdi:package-variant" class="w-20 h-20 text-gray-400 mx-auto mb-4" />
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Trending Products</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Check back soon for hot trending items!</p>
          <NuxtLink to="/store" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors">
            <Icon name="mdi:store" class="w-5 h-5" />
            <span>Browse Store</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectCoverflow, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/effect-coverflow'
import 'swiper/css/pagination'
import ProductCard from '~/components/product/ProductCard.vue'

const SwiperAutoplay = Autoplay
const SwiperEffectCoverflow = EffectCoverflow
const SwiperPagination = Pagination

const swiperModules = [SwiperAutoplay, SwiperEffectCoverflow, SwiperPagination]
const config = useRuntimeConfig()

interface ProductTire {
  min_quantity: number
  max_quantity: number
  wholesale_unit_quantity: number | null
  price: string
  stock: number
  in_stock: boolean
}

interface Category {
  name: string
  url: string
  views: number
  thumbnail: string
}

interface Sale {
  sale_price: string
  discount: string
  ends_till: string
  remaining: string
  action_type: string
}

interface Product {
  name: string
  url: string
  sku: string
  type: string
  views: number
  has_stock: boolean
  rating: string
  review: number
  reward_point: number
  price: string
  sales: Sale[]
  categories: Category[]
  thumbnail: string
  tire: ProductTire | null
}

const products = ref<Product[]>([])
const loading = ref(true)
const error = ref('')

// ✅ Computed property to determine if loop should be enabled
const shouldEnableLoop = computed(() => {
  // Need at least 3 products (+ 1 CTA slide = 4 total) for smooth loop
  // For mobile with slidesPerView: 1.2, need at least 2 slides
  // For desktop with slidesPerView: 3, need at least 4 slides
  return products.value.length >= 3
})

const fetchProducts = async () => {
  if (!process.client) return

  loading.value = true
  error.value = ''

  try {
    const response = await useSanctumFetch(`/api/products/trendingProducts`, {
      method: 'GET',
    })

    if (response?.data && Array.isArray(response.data)) {
      products.value = response.data
      if (products.value.length === 0) {
        error.value = 'No trending products available at the moment'
      }
    } else {
      throw new Error('Invalid response format')
    }
  } catch (err: any) {
    console.error('[✘] Error fetching trending products:', err)
    error.value = err.message || 'Failed to load products. Please try again later.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
})
</script>

<style scoped>
/* Swiper pagination styling */
:deep(.swiper-pagination-bullet) {
  @apply bg-purple-600 dark:bg-purple-400 opacity-50;
}

:deep(.swiper-pagination-bullet-active) {
  @apply bg-purple-600 dark:bg-purple-400 opacity-100 scale-125;
}

:deep(.swiper-pagination) {
  @apply bottom-0;
}

/* Ensure swiper container doesn't overflow */
:deep(.swiper) {
  @apply overflow-hidden;
}

/* Fix for swiper slide height */
:deep(.swiper-slide) {
  @apply h-auto;
}

/* Coverflow shadow fix */
:deep(.swiper-slide-shadow-left),
:deep(.swiper-slide-shadow-right) {
  @apply bg-gradient-to-r from-black/20 to-transparent;
}
</style>
