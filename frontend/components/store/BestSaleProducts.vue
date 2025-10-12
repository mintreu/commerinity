<template>
  <section class="w-full py-8 sm:py-12 md:py-16 lg:py-20 bg-gradient-to-b from-white via-purple-50/30 to-white dark:from-gray-900 dark:via-purple-950/20 dark:to-gray-900 overflow-x-hidden md:overflow-hidden">

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12">

      <!-- Section Header -->
      <div class="flex flex-col items-center text-center mb-6 sm:mb-8 md:mb-10 lg:mb-12 space-y-2 sm:space-y-3 md:space-y-4">
        <div class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm">
          <Icon name="mdi:trophy" class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2 text-purple-600 dark:text-purple-400 flex-shrink-0" />
          <span class="text-xs sm:text-sm font-medium text-purple-700 dark:text-purple-300 whitespace-nowrap">Best Sellers</span>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black leading-tight px-4">
          <span class="text-gray-900 dark:text-white">Top Selling </span>
          <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 bg-clip-text text-transparent">Products</span>
        </h2>

        <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-2xl px-4">
          Our most popular items with proven sales performance
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center min-h-[300px] sm:min-h-[400px]">
        <div class="flex flex-col items-center gap-3">
          <div class="w-12 h-12 sm:w-16 sm:h-16 border-4 border-purple-600 border-t-transparent rounded-full animate-spin" />
          <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 font-medium animate-pulse">Loading products...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex items-center justify-center min-h-[300px] sm:min-h-[400px]">
        <div class="flex flex-col items-center text-center space-y-3">
          <Icon name="mdi:alert-circle" class="w-12 h-12 sm:w-14 sm:h-14 text-red-500" />
          <p class="text-sm sm:text-base text-red-600 dark:text-red-400 font-medium px-4">{{ error }}</p>
          <button @click="fetchProducts" class="px-4 sm:px-5 py-2 bg-purple-600 text-white text-xs sm:text-sm rounded-lg hover:bg-purple-700 transition-all">
            Try Again
          </button>
        </div>
      </div>

      <!-- Products Display -->
      <div v-else-if="products.length > 0" class="w-full">

        <!-- MOBILE: Horizontal Slider -->
        <div class="block md:hidden w-screen overflow-x-hidden">
          <Swiper
              :modules="[SwiperAutoplay, SwiperPagination]"
              :slides-per-view="1.2"
              :space-between="12"
              :centered-slides="true"
              :loop="products.length >= 3"
              :autoplay="products.length >= 3 ? { delay: 4000, disableOnInteraction: false } : false"
              :pagination="{ clickable: true, dynamicBullets: true }"
              :breakpoints="{
                400: { slidesPerView: 1.3, spaceBetween: 12 },
                500: { slidesPerView: 1.5, spaceBetween: 16 },
                640: { slidesPerView: 2.1, spaceBetween: 16 }
              }"
              class="!pb-12"
          >
            <SwiperSlide v-for="product in products" :key="product.sku">
              <ProductCard :product="product" :show-wishlist="true" />
            </SwiperSlide>
          </Swiper>
        </div>

        <!-- DESKTOP: Premium Split View -->
        <div class="hidden md:flex flex-row gap-4 md:gap-5 lg:gap-6 w-full ">

          <!-- LEFT: PREMIUM Featured Product Preview -->
          <div class="w-full overflow-y-hidden md:w-3/5 lg:w-7/12 flex-shrink-0 rounded-2xl">
            <div
                ref="featuredCard"
                class="featured-product-card m-3 group relative h-[520px] lg:h-[580px] bg-gradient-to-br from-white via-purple-50/20 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-2xl overflow-hidden border-2 border-purple-200 dark:border-purple-700 transition-all duration-500 hover:scale-[1.01]"
            >

              <!-- Animated Gradient Border -->
              <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/30 via-purple-500/30 to-pink-500/30 rounded-2xl blur-xl animate-pulse-glow" />
              </div>

              <!-- Sale Badge (if applicable) -->
              <div v-if="activeSale" class="absolute top-4 left-4 z-30">
                <div class="sale-badge-featured relative">
                  <div class="absolute inset-0 bg-red-500 rounded-lg blur-sm opacity-40 scale-110" />
                  <div class="relative bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1.5 rounded-lg text-sm font-bold shadow-xl flex items-center gap-1.5">
                    <Icon name="mdi:fire" class="w-4 h-4 animate-bounce" />
                    <span>{{ activeDiscountLabel }}</span>
                  </div>
                </div>
              </div>

              <!-- Stock Badge -->
              <div v-if="!activeIsInStock" class="absolute top-4 right-4 z-30 bg-gray-900/90 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-sm font-bold">
                Out of Stock
              </div>
              <div v-else-if="activeIsLowStock" class="absolute top-4 right-4 z-30 bg-orange-500 text-white px-3 py-1.5 rounded-lg text-sm font-bold shadow-xl flex items-center gap-1">
                <Icon name="mdi:alert" class="w-4 h-4" />
                Low Stock
              </div>

              <NuxtLink :to="`/product/${activeProduct.url}`" class="block h-full flex flex-col relative z-10">

                <!-- Image Section -->
                <div class="h-[280px] lg:h-[320px] w-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 overflow-hidden flex items-center justify-center p-6 flex-shrink-0 relative">
                  <img
                      ref="featuredImage"
                      :src="activeProduct.thumbnail"
                      :alt="activeProduct.name"
                      class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-700"
                      @error="handleImageError"
                  />

                  <!-- Image Overlay Effects -->
                  <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300" />
                </div>

                <!-- Info Section -->
                <div class="flex-1 p-5 lg:p-6 flex flex-col bg-white dark:bg-gray-800 space-y-3 lg:space-y-4">

                  <!-- Categories -->
                  <div v-if="activeProduct.categories?.length" class="flex flex-wrap gap-2">
                    <span
                        v-for="category in activeProduct.categories.slice(0, 3)"
                        :key="category.url"
                        class="px-2.5 py-1 bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-700 dark:text-blue-300 text-xs rounded-full font-medium"
                    >
                      {{ category.name }}
                    </span>
                  </div>

                  <div class="flex flex-row gap-4 items-center">
                    <!-- Product Name -->
                    <h3 ref="featuredTitle" class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white line-clamp-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors leading-tight">
                      {{ activeProduct.name }}
                    </h3>

                    <!-- Rating & Reviews -->
                    <div class="flex items-center gap-3">
                      <div class="flex items-center gap-1">
                        <div class="flex items-center">
                          <Icon
                              v-for="i in 5"
                              :key="i"
                              :name="i <= Math.floor(parseFloat(activeProduct.rating || '0')) ? 'mdi:star' : 'mdi:star-outline'"
                              class="w-4 h-4"
                              :class="i <= Math.floor(parseFloat(activeProduct.rating || '0')) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                          />
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">{{ activeRating }}</span>
                      </div>
                      <span class="text-sm text-gray-500 dark:text-gray-400">
                        ({{ activeProduct.review || 0 }} reviews)
                      </span>
                    </div>
                  </div>

                  <!-- Pricing Section -->
                  <div class="pricing-section px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border-2 border-green-200/50 dark:border-green-800/30">
                    <div class="flex items-baseline gap-3 flex-wrap">
                      <!-- Sale Price -->
                      <span v-if="activeSale" class="text-3xl lg:text-4xl font-black text-green-600 dark:text-green-400">
                        {{ activeSalePrice }}
                      </span>

                      <!-- Regular Price -->
                      <span
                          class="font-bold"
                          :class="activeSale ? 'text-base text-gray-500 dark:text-gray-400 line-through' : 'text-3xl lg:text-4xl text-green-600 dark:text-green-400'"
                      >
                        {{ activeDisplayPrice }}
                      </span>

                      <!-- Savings Badge -->
                      <span v-if="activeSale" class="text-sm bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2.5 py-1 rounded-lg font-bold">
                        Save {{ activeSavings }}
                      </span>
                    </div>

                    <!-- Sale Timer -->
                    <div v-if="activeSale && activeSaleEnds" class="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400 font-medium mt-2">
                      <Icon name="mdi:clock-fast" class="w-4 h-4 animate-pulse" />
                      {{ activeSaleEnds }}
                    </div>
                  </div>

                  <!-- CTA Button -->
                  <div class="mt-auto">
                    <div class="w-full bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 bg-[length:200%_100%] hover:bg-right text-white font-bold py-3.5 lg:py-3 px-4 rounded-xl text-center text-base flex items-center justify-center gap-2 hover:scale-105 transition-all shadow-xl hover:shadow-purple-500/50">
                      <Icon name="mdi:cart-plus" class="w-5 h-5 flex-shrink-0" />
                      <span>Add to Cart</span>
                      <Icon name="mdi:arrow-right" class="w-5 h-5 flex-shrink-0 group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              </NuxtLink>

            </div>
          </div>

          <!-- RIGHT: Data-Rich Vertical Slider (Navigation) -->
          <div class="w-full md:w-2/5 lg:w-5/12 flex-shrink-0">
            <div class="h-[520px] lg:h-[580px] overflow-hidden rounded-2xl border-2 border-purple-200 dark:border-purple-700 bg-gradient-to-b from-white/80 to-purple-50/30 dark:from-gray-800/80 dark:to-purple-900/20 backdrop-blur-sm flex flex-col shadow-xl">
              <Swiper
                  :modules="[SwiperAutoplay, SwiperNavigation]"
                  direction="vertical"
                  :slides-per-view="5"
                  :space-between="10"
                  :autoplay="{ delay: 4000, disableOnInteraction: false }"
                  :navigation="{
                    nextEl: '.swiper-nav-next',
                    prevEl: '.swiper-nav-prev',
                  }"
                  @slideChange="onSlideChange"
                  class="h-full w-full"
              >
                <SwiperSlide v-for="(product, index) in products" :key="product.sku">
                  <div
                      @click="setActiveProduct(index)"
                      class="group cursor-pointer bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all border-2 h-full flex p-2.5 gap-2.5 relative"
                      :class="activeIndex === index ? 'border-purple-600 ring-2 ring-purple-400/50 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-purple-300'"
                  >

                    <!-- Active Indicator Bar -->
                    <div
                        v-if="activeIndex === index"
                        class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-purple-600 to-pink-600 rounded-r-full"
                    />

                    <!-- Image Section with Overlay Badges -->
                    <div class="relative w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center p-1.5">
                      <img
                          :src="product.thumbnail"
                          :alt="product.name"
                          class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                          @error="handleImageError"
                      />

                      <!-- Sale Badge -->
                      <div v-if="product.sales?.length > 0" class="absolute top-0.5 right-0.5 bg-gradient-to-r from-red-500 to-pink-500 text-white px-1.5 py-0.5 rounded-md text-[8px] font-bold shadow-md">
                        -{{ Math.round(parseFloat(product.sales[0].discount.replace(/[^0-9.]/g, '')) * 100) }}%
                      </div>

                      <!-- Stock Status Badge -->
                      <div v-if="!(product.tire?.in_stock ?? product.has_stock)" class="absolute bottom-0 left-0 right-0 bg-gray-900/90 backdrop-blur-sm text-white text-[7px] font-bold text-center py-0.5">
                        OUT
                      </div>
                      <div v-else-if="(product.tire?.in_stock ?? product.has_stock) && (product.tire?.stock || 0) > 0 && (product.tire?.stock || 0) <= 10" class="absolute bottom-0 left-0 right-0 bg-orange-500 text-white text-[7px] font-bold text-center py-0.5 flex items-center justify-center gap-0.5">
                        <Icon name="mdi:alert" class="w-2 h-2" />
                        LOW
                      </div>
                    </div>

                    <!-- Info Section -->
                    <div class="flex-1 min-w-0 flex flex-col justify-between space-y-1">

                      <!-- Top: Category & Name -->
                      <div class="space-y-1">
                        <!-- Category Tag -->
                        <div v-if="product.categories?.length" class="flex">
                          <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-[8px] rounded-full font-medium truncate max-w-full">
                            <Icon name="mdi:tag" class="w-2 h-2 flex-shrink-0" />
                            <span class="truncate">{{ product.categories[0].name }}</span>
                          </span>
                        </div>

                        <!-- Product Name -->
                        <h4 class="font-bold text-[11px] text-gray-900 dark:text-white line-clamp-2 leading-tight group-hover:text-purple-600 transition-colors">
                          {{ product.name }}
                        </h4>
                      </div>

                      <!-- Middle: Pricing & Rating -->
                      <div class="space-y-1">
                        <!-- Pricing -->
                        <div class="flex items-baseline gap-1.5 flex-wrap">
                          <span v-if="product.sales?.length > 0" class="text-sm font-black text-green-600 dark:text-green-400">
                            {{ product.sales[0].sale_price }}
                          </span>
                          <span
                              class="font-bold"
                              :class="product.sales?.length > 0 ? 'text-[9px] text-gray-500 dark:text-gray-400 line-through' : 'text-sm text-purple-600 dark:text-purple-400'"
                          >
                            {{ product.tire?.price || product.price }}
                          </span>
                        </div>

                        <!-- Rating Stars -->
                        <div class="flex items-center gap-1">
                          <div class="flex items-center">
                            <Icon
                                v-for="i in 5"
                                :key="i"
                                :name="i <= Math.floor(parseFloat(product.rating || '0')) ? 'mdi:star' : 'mdi:star-outline'"
                                class="w-2.5 h-2.5"
                                :class="i <= Math.floor(parseFloat(product.rating || '0')) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                            />
                          </div>
                          <span class="text-[8px] text-gray-500 dark:text-gray-400">
                            ({{ product.review || 0 }})
                          </span>
                        </div>
                      </div>

                      <!-- Bottom: Stats Row -->
                      <div class="flex items-center justify-between text-[8px] pt-1 border-t border-gray-200/50 dark:border-gray-700/50">
                        <!-- Views -->
                        <span class="flex items-center gap-0.5 text-blue-600 dark:text-blue-400">
                          <Icon name="mdi:eye" class="w-2.5 h-2.5 flex-shrink-0" />
                          {{ formatViews(product.views) }}
                        </span>

                        <!-- Reward Points -->
                        <span v-if="product.reward_point" class="flex items-center gap-0.5 text-purple-600 dark:text-purple-400 font-bold">
                          <Icon name="mdi:diamond-stone" class="w-2.5 h-2.5 flex-shrink-0" />
                          {{ Math.round(product.reward_point) }}
                        </span>

                        <!-- Stock Count or Arrow -->
                        <span v-if="(product.tire?.in_stock ?? product.has_stock) && (product.tire?.stock || 0) > 0" class="flex items-center gap-0.5 text-green-600 dark:text-green-400 font-bold">
                          <Icon name="mdi:package-variant" class="w-2.5 h-2.5 flex-shrink-0" />
                          {{ product.tire?.stock || 0 }}
                        </span>

                        <!-- View Arrow -->
                        <span class="text-purple-600 dark:text-purple-400 font-bold flex items-center ml-auto">
                          <Icon name="mdi:chevron-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                        </span>
                      </div>
                    </div>

                    <!-- Active Product Glow -->
                    <div
                        v-if="activeIndex === index"
                        class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-xl pointer-events-none"
                    />
                  </div>
                </SwiperSlide>

                <!-- CTA Slide -->
                <SwiperSlide>
                  <div class="h-full flex items-center justify-center bg-gradient-to-br from-purple-600 via-pink-600 to-purple-600 rounded-xl p-4 text-center text-white relative overflow-hidden group shadow-xl">
                    <!-- Shimmer Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000" />

                    <div class="relative z-10 flex flex-col items-center space-y-2">
                      <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-1">
                        <Icon name="mdi:package-variant" class="w-7 h-7 flex-shrink-0" />
                      </div>
                      <h3 class="text-sm font-black">Explore More</h3>
                      <p class="text-[10px] opacity-90">Discover all products</p>
                      <NuxtLink
                          to="/store"
                          class="bg-white text-purple-600 font-bold px-4 py-1.5 rounded-lg text-xs hover:scale-110 hover:shadow-xl transition-all mt-2"
                      >
                        View All →
                      </NuxtLink>
                    </div>
                  </div>
                </SwiperSlide>
              </Swiper>

              <!-- Navigation Buttons -->
              <div class="flex justify-center gap-3 py-3 bg-gradient-to-r from-white/90 to-purple-50/50 dark:from-gray-800/90 dark:to-purple-900/30 backdrop-blur-sm border-t border-purple-200/30 dark:border-purple-800/30">
                <button class="swiper-nav-prev w-9 h-9 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white hover:scale-110 hover:shadow-lg hover:shadow-purple-500/50 transition-all active:scale-95">
                  <Icon name="mdi:chevron-up" class="w-5 h-5" />
                </button>
                <button class="swiper-nav-next w-9 h-9 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full flex items-center justify-center text-white hover:scale-110 hover:shadow-lg hover:shadow-purple-500/50 transition-all active:scale-95">
                  <Icon name="mdi:chevron-down" class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'

// ✅ CRITICAL: Only import core CSS, not all modules upfront
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

import ProductCard from "~/components/product/ProductCard.vue"

const SwiperAutoplay = Autoplay
const SwiperNavigation = Navigation
const SwiperPagination = Pagination

const config = useRuntimeConfig()

// ... (all your interfaces remain the same)

interface ProductTire {
  min_quantity: number
  max_quantity: number
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
  start_from: string | null
  ends_till: string
  sale_price: string
  discount: string
  remaining: string
  action_type: 'by_percent' | 'by_fixed' | 'to_percent' | 'to_fixed'
}

interface Product {
  name: string
  url: string
  sku: string
  price: string
  reward_point: number
  views: number
  rating: string
  review: number
  has_stock: boolean
  thumbnail: string
  tire: ProductTire | null
  categories: Category[]
  sales?: Sale[]
}

const products = ref<Product[]>([])
const activeIndex = ref(0)
const loading = ref(true)
const error = ref('')

// Refs for GSAP animations
const featuredCard = ref<HTMLElement | null>(null)
const featuredImage = ref<HTMLImageElement | null>(null)
const featuredTitle = ref<HTMLElement | null>(null)

// ✅ OPTIMIZED: Lazy load GSAP (ALREADY PERFECT!)
let gsap: any = null
let gsapLoaded = false

const loadGsap = async () => {
  if (gsapLoaded) return gsap

  try {
    const module = await import('gsap')
    gsap = module.default || module.gsap
    gsapLoaded = true
    console.log('✅ GSAP loaded lazily')
    return gsap
  } catch (error) {
    console.error('❌ Failed to load GSAP:', error)
    return null
  }
}

const activeProduct = computed(() => products.value[activeIndex.value] || {} as Product)

// ... (all your computed properties remain the same)
const activeSale = computed(() => activeProduct.value.sales?.length > 0 ? activeProduct.value.sales[0] : null)
const activeSalePrice = computed(() => activeSale.value?.sale_price || '')
const activeDisplayPrice = computed(() => activeProduct.value.tire?.price || activeProduct.value.price)

const activeDiscountLabel = computed(() => {
  if (!activeSale.value) return ''
  const discount = activeSale.value.discount
  const actionType = activeSale.value.action_type

  switch (actionType) {
    case 'by_percent':
    case 'to_percent':
      return `${parseFloat(discount.replace(/[^0-9.]/g, '')) * 100}% OFF`
    case 'by_fixed':
      return `${discount} OFF`
    default:
      return 'SALE'
  }
})

const activeSavings = computed(() => {
  if (!activeSale.value) return ''
  const original = parseFloat(activeDisplayPrice.value.replace(/[^0-9.]/g, ''))
  const sale = parseFloat(activeSalePrice.value.replace(/[^0-9.]/g, ''))
  return `₹${(original - sale).toFixed(0)}`
})

const activeSaleEnds = computed(() => activeSale.value?.remaining ? `Ends ${activeSale.value.remaining}` : '')

const activeIsInStock = computed(() => activeProduct.value.tire?.in_stock ?? activeProduct.value.has_stock)
const activeStockCount = computed(() => activeProduct.value.tire?.stock || 0)
const activeIsLowStock = computed(() => activeIsInStock.value && activeStockCount.value > 0 && activeStockCount.value <= 10)

const activeRating = computed(() => {
  const r = parseFloat(activeProduct.value.rating || '0')
  return r > 0 ? r.toFixed(1) : 'No rating'
})

const fetchProducts = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/products/bestSaleProducts`, {
      method: 'GET',
    })

    if (response.data && Array.isArray(response.data)) {
      products.value = response.data
      if (products.value.length === 0) {
        error.value = 'No products found'
      }
    } else {
      throw new Error('Invalid data format')
    }
  } catch (err: any) {
    console.error('Error fetching products:', err)
    error.value = err.message || 'Failed to load products. Please try again.'
  } finally {
    loading.value = false
  }
}

const setActiveProduct = (index: number) => {
  activeIndex.value = index
}

const onSlideChange = (swiper: any) => {
  if (swiper.activeIndex < products.value.length) {
    activeIndex.value = swiper.activeIndex
  }
}

// ✅ OPTIMIZED: GSAP Animation - only loads when product changes
watch(activeIndex, async () => {
  await nextTick()

  // ✅ FIX 1: Only run on desktop where featured card exists
  if (!featuredCard.value || !featuredImage.value || !featuredTitle.value) return

  const gsapInstance = await loadGsap()

  if (gsapInstance) {
    const tl = gsapInstance.timeline()

    tl.to([featuredImage.value, featuredTitle.value], {
      opacity: 0,
      scale: 0.95,
      duration: 0.3,
      ease: 'power2.in'
    })
        .to([featuredImage.value, featuredTitle.value], {
          opacity: 1,
          scale: 1,
          duration: 0.5,
          ease: 'back.out(1.7)',
          stagger: 0.1
        })
  }
})

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = 'https://placehold.co/400x600?text=Product+Image'
}

const formatViews = (views: number): string => {
  if (views >= 1000) return `${(views / 1000).toFixed(1)}K`
  return views.toString()
}

onMounted(async () => {
  await fetchProducts()

  // ✅ FIX 2: Only animate on desktop
  if (process.client && window.innerWidth >= 768 && featuredCard.value && products.value.length > 0) {
    const gsapInstance = await loadGsap()

    if (gsapInstance) {
      gsapInstance.from(featuredCard.value, {
        opacity: 0,
        y: 50,
        duration: 1,
        ease: 'back.out(1.7)'
      })
    }
  }
})
</script>


<style scoped>
:deep(.swiper-pagination-bullet) {
  @apply bg-purple-600 dark:bg-purple-400;
}

:deep(.swiper-pagination-bullet-active) {
  @apply bg-purple-600 dark:bg-purple-400 scale-125;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Premium animations */
@keyframes pulse-glow {
  0%, 100% {
    opacity: 0.5;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.animate-pulse-glow {
  animation: pulse-glow 3s ease-in-out infinite;
}

.sale-badge-featured {
  animation: badge-float 2s ease-in-out infinite;
}

@keyframes badge-float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.pricing-section {
  animation: price-highlight 0.5s ease-out;
}

@keyframes price-highlight {
  0% {
    transform: scale(0.95);
    opacity: 0;
  }
  50% {
    transform: scale(1.02);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

/* Mobile Product Slider Fixes */
@media (max-width: 767px) {
  /* Disable all animations on mobile */
  .animate-pulse-glow,
  .sale-badge-featured,
  .pricing-section {
    animation: none !important;
  }

  /* Container stability */
  .mobile-product-swiper {
    max-width: 100% !important;
    overflow: hidden !important;
  }

  .mobile-slide {
    height: auto !important;
    max-width: 100% !important;
  }

  /* Disable hover transforms on ProductCard */
  .mobile-product-card :deep(.product-card) {
    transform: none !important;
  }

  .mobile-product-card :deep(.product-card:hover) {
    transform: none !important;
  }

  /* Disable hover translate effect */
  .mobile-product-card :deep(.hover\:-translate-y-1) {
    transform: none !important;
  }

  .mobile-product-card :deep(.hover\:-translate-y-1:hover) {
    transform: none !important;
  }

  /* Disable scale effects */
  .mobile-product-card :deep(.group-hover\:scale-105),
  .mobile-product-card :deep(.hover\:scale-105),
  .mobile-product-card :deep(.hover\:scale-110) {
    transform: none !important;
  }

  /* Prevent viewport changes */
  .mobile-product-card :deep(*) {
    will-change: auto !important;
  }

  /* Swiper must work */
  .mobile-product-swiper :deep(.swiper-wrapper) {
    transform: translate3d(0, 0, 0) !important;
  }
}
</style>
