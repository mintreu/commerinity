<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 overflow-x-hidden">

    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden will-change-transform">
      <div ref="orb1" class="product-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-10 blur-3xl will-change-transform"></div>
      <div ref="orb2" class="product-orb-2 absolute bottom-20 right-20 w-72 h-72 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-15 blur-3xl will-change-transform"></div>
      <div ref="orb3" class="product-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl will-change-transform"></div>

      <!-- Shopping Particles -->
      <div class="product-particles">
        <div v-for="(particle, index) in particles" :key="`particle-${index}`"
             class="particle absolute rounded-full opacity-60 animate-bounce will-change-transform"
             :class="particle.class"
             :style="particle.style">
        </div>
      </div>
    </div>

    <!-- Global Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- Main Content -->
    <div v-else class="relative z-10">

      <!-- Breadcrumbs & Cart Counter -->
      <div class="breadcrumbs-section px-4 lg:px-20 pt-20 mb-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
          <!-- Breadcrumbs -->
          <nav class="breadcrumbs bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl px-6 py-3 shadow-lg border border-white/50 dark:border-gray-700/50">
            <ol class="flex items-center space-x-2 text-sm">
              <li>
                <NuxtLink to="/store" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
                  <Icon name="mdi:home" class="inline w-4 h-4 mr-1" />
                  Store
                </NuxtLink>
              </li>
              <li class="text-gray-400">/</li>
              <li v-if="product?.categories?.length">
                <NuxtLink
                    :to="`/category/${product.categories[0].url}`"
                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium"
                >
                  {{ product.categories[0].name }}
                </NuxtLink>
              </li>
              <li v-if="product?.categories?.length" class="text-gray-400">/</li>
              <li class="text-gray-900 dark:text-white font-semibold truncate max-w-xs">
                {{ product?.name || 'Product' }}
              </li>
            </ol>
          </nav>

          <!-- Cart Counter -->
          <div class="cart-counter-container bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/50 dark:border-gray-700/50">
            <CartCounter />
          </div>
        </div>
      </div>

      <!-- Product Details Section -->
      <div v-if="product" class="product-section px-4 lg:px-20 mb-16">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16">

            <!-- Left Side - Enhanced Image Slider -->
            <div class="product-media-section" ref="mediaSection">
              <ProductMediaSlider :media="product.banner" />

              <!-- Product Trust Badges -->
              <div class="trust-badges mt-6 grid grid-cols-3 gap-4" ref="trustBadges">
                <div class="trust-badge bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl p-4 text-center shadow-lg border border-white/50 dark:border-gray-700/50 hover:scale-105 transition-all duration-300">
                  <Icon name="mdi:shield-check" class="w-8 h-8 mx-auto mb-2 text-green-600 dark:text-green-400" />
                  <span class="text-sm font-bold text-gray-900 dark:text-white">Verified</span>
                  <span class="text-xs text-gray-600 dark:text-gray-400 block">Product</span>
                </div>
                <div class="trust-badge bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl p-4 text-center shadow-lg border border-white/50 dark:border-gray-700/50 hover:scale-105 transition-all duration-300">
                  <Icon name="mdi:truck-fast" class="w-8 h-8 mx-auto mb-2 text-blue-600 dark:text-blue-400" />
                  <span class="text-sm font-bold text-gray-900 dark:text-white">Free</span>
                  <span class="text-xs text-gray-600 dark:text-gray-400 block">Delivery</span>
                </div>
                <div class="trust-badge bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-2xl p-4 text-center shadow-lg border border-white/50 dark:border-gray-700/50 hover:scale-105 transition-all duration-300">
                  <Icon name="mdi:refresh-circle" class="w-8 h-8 mx-auto mb-2 text-purple-600 dark:text-purple-400" />
                  <span class="text-sm font-bold text-gray-900 dark:text-white">Easy</span>
                  <span class="text-xs text-gray-600 dark:text-gray-400 block">Returns</span>
                </div>
              </div>
            </div>

            <!-- Right Side - Enhanced Product Details -->
            <div class="product-details-section" ref="detailsSection">
              <div class="product-info bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl p-8 shadow-2xl border border-white/50 dark:border-gray-700/50">

                <!-- Product Title & Wishlist -->
                <div class="product-header mb-8">
                  <div class="flex items-start justify-between gap-6">
                    <div class="product-title-section flex-1">
                      <div class="product-badges mb-4">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg">
                          <Icon name="mdi:check-circle" class="w-4 h-4 mr-2" />
                          In Stock
                        </span>
                      </div>

                      <h1 class="product-name text-3xl lg:text-4xl font-black text-gray-900 dark:text-white mb-4 leading-tight">
                        {{ product.name }}
                      </h1>

                      <div class="product-meta flex flex-wrap items-center gap-4 mb-6">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                          <span class="font-semibold">SKU:</span> {{ product.sku }}
                        </span>
                        <div class="h-4 w-px bg-gray-300 dark:bg-gray-600"></div>
                        <div class="flex items-center gap-1">
                          <Icon name="mdi:star" class="w-4 h-4 text-yellow-400" />
                          <span class="text-sm font-bold text-gray-900 dark:text-white">4.8</span>
                          <span class="text-sm text-gray-600 dark:text-gray-400">({{ Math.floor(Math.random() * 500) + 50 }} reviews)</span>
                        </div>
                      </div>

                      <!-- Product Attributes Tags -->
                      <div v-if="product.filter_option && product.filter_option.length > 0" class="product-attributes mb-6">
                        <div class="flex flex-wrap gap-2">
                          <span
                              v-for="filterOption in product.filter_option.slice(0, 5)"
                              :key="filterOption.filter.name"
                              class="attribute-tag px-3 py-1.5 bg-gradient-to-r from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 text-blue-800 dark:text-blue-200 rounded-full text-sm font-semibold border border-blue-200 dark:border-blue-700"
                          >
                            {{ filterOption.filter.name }}: {{ filterOption.swatch_value || filterOption.value }}
                          </span>
                          <span
                              v-if="product.filter_option.length > 5"
                              class="attribute-tag px-3 py-1.5 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 text-gray-700 dark:text-gray-300 rounded-full text-sm font-semibold"
                          >
                            +{{ product.filter_option.length - 5 }} more
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Enhanced Wishlist Button -->
                    <button class="wishlist-btn group p-4 bg-white/90 dark:bg-gray-700/90 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 dark:border-gray-600 hover:scale-110 transition-all duration-300">
                      <Icon name="mdi:heart-outline" class="w-8 h-8 text-gray-600 dark:text-gray-400 group-hover:text-red-500 transition-colors duration-300" />
                    </button>
                  </div>
                </div>

                <!-- Enhanced Price & Rating -->
                <div class="pricing-section mb-8">
                  <div class="price-container bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-800">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                      <div class="price-info">
                        <div class="current-price flex items-center gap-3 mb-2">
                          <h2 class="text-4xl lg:text-5xl font-black text-green-600 dark:text-green-400">
                            {{ product.price }}
                          </h2>
                          <span v-if="product.reward_point" class="reward-points px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl text-sm font-bold shadow-lg">
                            +{{ Math.round(product.reward_point) }} pts
                          </span>
                        </div>
                        <div class="price-details flex items-center gap-3">
                          <span class="original-price text-lg text-gray-500 dark:text-gray-400 line-through">
                            ₹{{ formatPrice(Math.floor(product.price * 1.3)) }}
                          </span>
                          <span class="discount-badge px-3 py-1 bg-red-500 text-white rounded-full text-sm font-bold">
                            {{ Math.floor(((product.price * 1.3 - product.price) / (product.price * 1.3)) * 100) }}% OFF
                          </span>
                        </div>
                      </div>

                      <div class="rating-section">
                        <div class="rating-container bg-gradient-to-r from-yellow-400 to-orange-400 rounded-2xl px-6 py-4 shadow-lg">
                          <div class="flex items-center gap-2">
                            <Icon name="mdi:star" class="w-6 h-6 text-white" />
                            <span class="text-xl font-black text-white">4.8</span>
                            <span class="text-sm text-white/90">Excellent</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Categories -->
                <div v-if="product.categories" class="categories-section mb-8">
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <Icon name="mdi:tag-multiple" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                    Categories
                  </h3>
                  <div class="flex flex-wrap gap-3">
                    <NuxtLink
                        v-for="category in product.categories"
                        :key="category.url"
                        :to="`/category/${category.url}`"
                        class="category-link px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all duration-300"
                    >
                      {{ category.name }}
                    </NuxtLink>
                  </div>
                </div>

                <!-- Enhanced Variants Section -->
                <div v-if="product.parent?.variants || product.variants" class="variants-section mb-8" ref="variantsSection">
                  <div class="variants-header mb-6">
                    <div class="flex justify-between items-center">
                      <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                        <Icon name="mdi:palette" class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" />
                        Similar Products
                        <span class="ml-2 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-sm font-bold">
                          {{ product.parent?.variants?.length || product.variants?.length }}
                        </span>
                      </h3>
                      <span class="text-sm text-gray-600 dark:text-gray-400">
                        Choose your preferred option
                      </span>
                    </div>
                  </div>

                  <div class="variants-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <div
                        v-for="variant in (product.parent?.variants || product.variants)"
                        :key="variant.url"
                        class="variant-card group bg-white dark:bg-gray-700 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border-2 relative transform hover:scale-105"
                        :class="variant.url === product.url ? 'border-indigo-500 ring-4 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300'"
                    >
                      <NuxtLink :to="`/product/${variant.url}`" :title="`View ${variant.name}`">
                        <!-- Variant Image -->
                        <div class="variant-image relative aspect-square overflow-hidden bg-gray-50 dark:bg-gray-600">
                          <img
                              :src="variant.thumbnail"
                              :alt="variant.name"
                              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                              loading="lazy"
                          />

                          <!-- Stock Status -->
                          <div class="stock-badge absolute top-2 left-2">
                            <span class="px-2 py-1 bg-green-500 text-white text-xs font-bold rounded-lg shadow-lg">
                              In Stock
                            </span>
                          </div>

                          <!-- Current Badge -->
                          <div
                              v-if="variant.url === product.url"
                              class="current-badge absolute top-2 right-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-2 py-1 rounded-lg text-xs font-bold shadow-lg animate-pulse"
                          >
                            CURRENT
                          </div>

                          <!-- Quick Action Overlay -->
                          <div class="variant-overlay absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <span class="px-4 py-2 bg-white text-gray-900 rounded-xl font-semibold shadow-lg">
                              View Details
                            </span>
                          </div>
                        </div>
                      </NuxtLink>
                    </div>
                  </div>

                  <!-- Variant Selection Helper -->
                  <div class="variant-info mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                      <Icon name="mdi:information" class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                      <div class="text-sm text-blue-800 dark:text-blue-200">
                        <span class="font-bold">{{ (product.parent?.variants || product.variants)?.length }} variants available</span>
                        - Click any variant to view its details and make your selection
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Enhanced Add to Cart Section -->
                <div class="cart-section mb-8">
                  <div class="add-to-cart-container bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 rounded-2xl p-6 border border-gray-200 dark:border-gray-600">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                      <Icon name="mdi:cart-plus" class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                      Add to Cart
                    </h3>

                    <AddToCartWithQuantitySelector
                        :sku="product.sku"
                        :min-quantity="product.min_quantity"
                        :max-quantity="product.max_quantity"
                        class="mb-4"
                    />
                  </div>
                </div>

                <!-- Enhanced Buy Now Button -->
                <div class="buy-now-section mb-8">
                  <BuyNowButton
                      :sku="product.sku"
                      :min-quantity="product.min_quantity"
                  />
                </div>

                <!-- Enhanced Available Offers -->
                <div v-if="product.sales?.length" class="offers-section mb-8" ref="offersSection">
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <Icon name="mdi:tag-heart" class="w-5 h-5 mr-2 text-red-600 dark:text-red-400" />
                    Special Offers
                  </h3>
                  <div class="offers-list space-y-3">
                    <div
                        v-for="offer in product.sales"
                        :key="offer.sale.uuid"
                        class="offer-card bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-4 border border-green-200 dark:border-green-800 shadow-lg hover:scale-105 transition-all duration-300"
                    >
                      <div class="flex items-start gap-4">
                        <div class="offer-icon w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                          <Icon name="mdi:tag" class="w-6 h-6 text-white" />
                        </div>
                        <div class="offer-content flex-1">
                          <div class="offer-text">
                            <span class="font-bold text-green-800 dark:text-green-200">
                              {{ formatOffer(offer) }}
                            </span>
                            <button class="ml-3 px-3 py-1 bg-green-500 text-white rounded-lg text-xs font-semibold hover:bg-green-600 transition-colors duration-200">
                              Terms & Conditions
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Enhanced Product Overview -->
                <div v-if="product.short_description" class="overview-section">
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                    <Icon name="mdi:information-outline" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                    Product Overview
                  </h3>
                  <div class="overview-content bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
                    <div v-html="product.short_description" class="text-gray-700 dark:text-gray-300 leading-relaxed prose prose-sm max-w-none"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Full Product Description -->
      <div v-if="product?.description" class="description-section px-4 lg:px-20 mb-16" ref="descriptionSection">
        <div class="max-w-7xl mx-auto">
          <div class="description-container bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="description-header bg-gradient-to-r from-blue-600 to-purple-600 text-white p-8">
              <h2 class="text-3xl font-black flex items-center">
                <Icon name="mdi:text-box-multiple" class="w-8 h-8 mr-3" />
                Detailed Description
              </h2>
              <p class="text-blue-100 mt-2">Everything you need to know about this product</p>
            </div>
            <div class="description-content p-8">
              <FilamentTipTapContent :text="product.description" class="prose prose-lg max-w-none dark:prose-invert" />
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Product Comments Section -->
      <div class="reviews-section px-4 lg:px-20 mb-16" ref="reviewsSection">
        <div class="max-w-7xl mx-auto">
          <div class="reviews-container bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 overflow-hidden">
            <div class="reviews-header bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-8">
              <h2 class="text-3xl font-black flex items-center">
                <Icon name="mdi:star-box-multiple" class="w-8 h-8 mr-3" />
                Customer Reviews
              </h2>
              <p class="text-indigo-100 mt-2">See what our customers are saying about this product</p>
            </div>
            <div class="reviews-content">
              <ProductComment :url="product?.url" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue'
import { useRoute, useRuntimeConfig, useSanctumFetch } from '#imports'
import CartCounter from "~/components/cart/CartCounter.vue"
import GlobalLoader from "~/components/GlobalLoader.vue"
import ProductMediaSlider from "~/components/sliders/ProductMediaSlider.vue"
import FilamentTipTapContent from "~/components/FilamentTipTapContent.vue"
import AddToCartWithQuantitySelector from "~/components/store/buttons/AddToCartWithQuantitySelector.vue"
import BuyNowButton from "~/components/cart/BuyNowButton.vue"
import ProductComment from "~/components/product/ProductComment.vue"

// GSAP imports (client-side only)
let gsap: any = null
let ScrollTrigger: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
    import('gsap/ScrollTrigger').then(({ ScrollTrigger: ScrollTriggerModule }) => {
      ScrollTrigger = ScrollTriggerModule
      gsap.registerPlugin(ScrollTrigger)
    })
  })
}

// Composables
const route = useRoute()
const config = useRuntimeConfig()

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()
const mediaSection = ref<HTMLElement>()
const detailsSection = ref<HTMLElement>()
const trustBadges = ref<HTMLElement>()
const variantsSection = ref<HTMLElement>()
const offersSection = ref<HTMLElement>()
const descriptionSection = ref<HTMLElement>()
const reviewsSection = ref<HTMLElement>()

// State
const isLoading = useState('pageLoading', () => false)
const product = ref<any>(null)
let gsapContext: any = null

// Animated particles
const particles = [
  { class: 'w-4 h-4 bg-blue-400', style: 'top: 15%; left: 10%; animation-delay: 0s;' },
  { class: 'w-3 h-3 bg-purple-400', style: 'top: 25%; right: 15%; animation-delay: 1.5s;' },
  { class: 'w-5 h-5 bg-emerald-400', style: 'bottom: 20%; left: 20%; animation-delay: 3s;' },
  { class: 'w-3 h-3 bg-pink-400', style: 'bottom: 30%; right: 25%; animation-delay: 4.5s;' }
]

// Utility functions
const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-IN').format(price)
}

const formatOffer = (offer: any): string => {
  if (!offer) return ""
  let discountAmount = offer.discount ?? ""
  if (offer.discount_type === "by_percent" || offer.discount_type === "to_percent") {
    discountAmount += "%"
  }
  return `${offer.sale.name} Offer ${discountAmount} off - ${offer.sale.description}`
}

// API calls
const fetchProductDetail = async () => {
  try {
    isLoading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/products/${route.params.url}`, {
      method: 'GET'
    })
    product.value = res?.data ?? null
  } catch (error) {
    console.error('[✘] Failed to load product', error)
    product.value = null
  } finally {
    isLoading.value = false
  }
}

// GSAP animations
const initializeAnimations = () => {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (orb1.value) {
      gsap.to(orb1.value, {
        y: -40,
        rotation: 15,
        duration: 8,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb2.value) {
      gsap.to(orb2.value, {
        y: 30,
        rotation: -12,
        duration: 10,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    if (orb3.value) {
      gsap.to(orb3.value, {
        y: -25,
        rotation: 20,
        duration: 6,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: -1
      })
    }

    // Product sections entrance animations
    if (ScrollTrigger) {
      // Media section animation
      if (mediaSection.value) {
        gsap.fromTo(mediaSection.value,
            { x: -50, opacity: 0 },
            {
              x: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              scrollTrigger: {
                trigger: mediaSection.value,
                start: 'top 80%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }

      // Details section animation
      if (detailsSection.value) {
        gsap.fromTo(detailsSection.value,
            { x: 50, opacity: 0 },
            {
              x: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              scrollTrigger: {
                trigger: detailsSection.value,
                start: 'top 80%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }

      // Trust badges animation
      if (trustBadges.value && trustBadges.value.children) {
        gsap.fromTo(trustBadges.value.children,
            { y: 30, opacity: 0, scale: 0.9 },
            {
              y: 0,
              opacity: 1,
              scale: 1,
              duration: 0.6,
              ease: 'back.out(1.7)',
              stagger: 0.2,
              scrollTrigger: {
                trigger: trustBadges.value,
                start: 'top 80%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }

      // Variant cards animation
      if (variantsSection.value) {
        const variantCards = variantsSection.value.querySelectorAll('.variant-card')
        if (variantCards.length) {
          gsap.fromTo(variantCards,
              { y: 20, opacity: 0, scale: 0.95 },
              {
                y: 0,
                opacity: 1,
                scale: 1,
                duration: 0.5,
                ease: 'back.out(1.7)',
                stagger: 0.1,
                scrollTrigger: {
                  trigger: variantsSection.value,
                  start: 'top 80%',
                  toggleActions: 'play none none reverse'
                }
              }
          )
        }
      }

      // Offer cards animation
      if (offersSection.value) {
        const offerCards = offersSection.value.querySelectorAll('.offer-card')
        if (offerCards.length) {
          gsap.fromTo(offerCards,
              { x: -30, opacity: 0 },
              {
                x: 0,
                opacity: 1,
                duration: 0.6,
                ease: 'back.out(1.7)',
                stagger: 0.1,
                scrollTrigger: {
                  trigger: offersSection.value,
                  start: 'top 80%',
                  toggleActions: 'play none none reverse'
                }
              }
          )
        }
      }

      // Description section animation
      if (descriptionSection.value) {
        gsap.fromTo(descriptionSection.value,
            { y: 50, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              scrollTrigger: {
                trigger: descriptionSection.value,
                start: 'top 80%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }

      // Reviews section animation
      if (reviewsSection.value) {
        gsap.fromTo(reviewsSection.value,
            { y: 40, opacity: 0 },
            {
              y: 0,
              opacity: 1,
              duration: 1,
              ease: 'back.out(1.7)',
              scrollTrigger: {
                trigger: reviewsSection.value,
                start: 'top 80%',
                toggleActions: 'play none none reverse'
              }
            }
        )
      }
    }
  })
}

// Watchers
watch(() => route.params.url, async (newUrl) => {
  if (newUrl) {
    await fetchProductDetail()
  }
})

// Lifecycle
onMounted(async () => {
  try {
    await fetchProductDetail()

    // Initialize animations after content loads
    await nextTick()
    setTimeout(() => {
      initializeAnimations()
    }, 100)
  } catch (e) {
    console.error('Failed to load product:', e)
  }
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }

  if (process.client && ScrollTrigger) {
    ScrollTrigger.getAll().forEach((trigger: any) => trigger.kill())
  }
})

// SEO
useSeoMeta({
  title: computed(() => `${product.value?.name || 'Product'} - Premium Quality`),
  description: computed(() => product.value?.short_description || 'Discover this amazing product with great deals and fast delivery'),
})
</script>

<style scoped>
/* Line clamp utilities */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Enhanced card effects */
.variant-card {
  position: relative;
}

.variant-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(99, 102, 241, 0.3), transparent);
  border-radius: 1rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.variant-card:hover::before {
  opacity: 1;
}

/* Trust badge hover effects */
.trust-badge {
  position: relative;
  transition: all 0.3s ease;
}

.trust-badge:hover {
  transform: translateY(-5px);
}

/* Category link effects */
.category-link {
  position: relative;
  overflow: hidden;
}

.category-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.6s ease;
}

.category-link:hover::before {
  left: 100%;
}

/* Particle animations */
.particle {
  animation: float 6s ease-in-out infinite;
  will-change: transform;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* Wishlist button effect */
.wishlist-btn:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Responsive design */
@media (max-width: 1024px) {
  .product-orb-2 {
    display: none;
  }
}

@media (max-width: 640px) {
  .product-name {
    font-size: 2rem;
    line-height: 1.2;
  }

  .current-price h2 {
    font-size: 2.5rem;
  }

  .variants-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .trust-badges {
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
  }
}

@media (max-width: 480px) {
  .product-orb-1, .product-orb-3 {
    width: 4rem;
    height: 4rem;
  }

  .particles {
    display: none;
  }
}

/* Focus states for accessibility */
.variant-card:focus-visible,
.wishlist-btn:focus-visible,
.category-link:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Prose styling for description */
.prose {
  max-w: none;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
  color: inherit;
  margin-top: 1.5em;
  margin-bottom: 0.75em;
}

.prose p {
  margin-top: 0.75em;
  margin-bottom: 0.75em;
}

.prose img {
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
