<template>
  <div>
    <!-- Global Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- Error State -->
    <section v-else-if="error" class="flex flex-col items-center justify-center min-h-[80vh] px-4">
      <div class="max-w-lg mx-auto text-center">
        <div class="error-icon-container relative mb-8">
          <div class="w-24 h-24 mx-auto bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center shadow-2xl">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center animate-pulse">
              <Icon name="mdi:package-variant-closed" class="w-8 h-8 text-white" />
            </div>
          </div>
        </div>

        <div class="error-content bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-3xl p-8 shadow-xl border border-white/50 dark:border-slate-700/50">
          <h2 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white mb-4">
            Product Not Found
          </h2>
          <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
            {{ error }}
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
                @click="refresh"
                class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 group"
            >
              <Icon name="mdi:refresh" class="inline w-5 h-5 mr-3 group-hover:animate-spin" />
              Try Again
            </button>

            <NuxtLink
                to="/store"
                class="px-8 py-4 bg-white/10 backdrop-blur-md text-blue-600 dark:text-blue-400 font-bold rounded-2xl border-2 border-blue-200 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-300 transform hover:scale-105 group"
            >
              <Icon name="mdi:store" class="inline w-5 h-5 mr-3 group-hover:scale-110 transition-transform duration-300" />
              Browse Store
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Product Content -->
    <template v-else-if="product">

      <!-- Breadcrumbs with max-w-9xl -->
      <section class="breadcrumbs-section px-4 lg:px-6 py-4 bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm border-b border-gray-200/30 dark:border-slate-700/30">
        <div class="max-w-9xl mx-auto">
          <nav class="flex items-center space-x-2 text-sm">
            <NuxtLink to="/store" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
              <Icon name="mdi:home" class="inline w-4 h-4 mr-1" />
              Store
            </NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />

            <template v-if="product.categories?.length">
              <NuxtLink
                  :to="`/category/${product.categories[0].url}`"
                  class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium"
              >
                {{ product.categories[0].name }}
              </NuxtLink>
              <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
            </template>

            <span class="text-slate-900 dark:text-white font-semibold truncate">{{ product.name }}</span>
          </nav>
        </div>
      </section>

      <!-- Main Product Section -->
      <section class="product-section px-4 lg:px-6 py-8">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Product Images (5 columns) -->
            <div class="lg:col-span-5" ref="imageContainer">
              <ProductMediaSlider
                  :media="productMedia"
                  thumb-position="left"
              />
            </div>

            <!-- Product Details (7 columns) -->
            <div class="lg:col-span-7 space-y-6" ref="detailsContainer">

              <!-- Category Tags -->
              <div v-if="product.categories?.length" class="category-tags" ref="categorySection">
                <div class="flex flex-wrap gap-2">
                  <NuxtLink
                      v-for="category in product.categories"
                      :key="category.url"
                      :to="`/category/${category.url}`"
                      class="category-tag px-3 py-1 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-full border border-blue-200 dark:border-blue-700 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-800/30 dark:hover:to-indigo-800/30 transition-all duration-200 hover:scale-105 backdrop-blur-sm"
                  >
                    <Icon name="mdi:tag" class="w-3 h-3 inline mr-1" />
                    {{ category.name }}
                  </NuxtLink>
                </div>
              </div>

              <!-- Product Header with Title and Wishlist -->
              <div class="product-header" ref="productHeader">
                <div class="flex items-start justify-between gap-4">
                  <div class="flex-1">
                    <h1 class="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white mb-3 leading-tight">
                      {{ product.name }}
                    </h1>

                    <div class="flex items-center gap-4 mb-4">
                      <!-- Rating -->
                      <div class="flex items-center gap-2">
                        <div class="flex items-center bg-green-600 text-white px-2 py-1 rounded-md">
                          <span class="text-sm font-bold">{{ averageRating.toFixed(1) }}</span>
                          <Icon name="mdi:star" class="w-3 h-3 ml-1" />
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">({{ product.review_count || 0 }} reviews)</span>
                      </div>

                      <!-- Views -->
                      <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                        <Icon name="mdi:eye" class="w-4 h-4" />
                        <span>{{ product.views }} views</span>
                      </div>
                    </div>

                    <p v-if="product.short_description" class="text-gray-700 dark:text-gray-300 leading-relaxed text-lg mb-4">
                      {{ product.short_description }}
                    </p>
                  </div>

                  <!-- Wishlist Button -->
                  <button
                      @click="toggleWishlist"
                      :disabled="wishlistLoading"
                      class="wishlist-btn w-12 h-12 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-all duration-200 border border-gray-200 dark:border-slate-600"
                      :class="{
                        'text-red-500 bg-red-50/90 dark:bg-red-900/30 border-red-200 dark:border-red-700': isWishlisted,
                        'text-gray-600 dark:text-gray-400': !isWishlisted,
                        'cursor-not-allowed opacity-60': wishlistLoading
                      }"
                  >
                    <Icon
                        :name="wishlistLoading ? 'mdi:loading' : (isWishlisted ? 'mdi:heart' : 'mdi:heart-outline')"
                        class="w-6 h-6"
                        :class="wishlistLoading ? 'animate-spin' : ''"
                    />
                  </button>
                </div>
              </div>

              <!-- Enhanced Price Section -->
              <div class="price-section bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50 dark:border-slate-700/50" ref="priceSection">
                <div class="flex items-baseline gap-4 mb-4">
                  <!-- Sale Price -->
                  <span v-if="hasSale" class="text-4xl lg:text-5xl font-black text-green-600 dark:text-green-400">
                    {{ currentSalePrice }}
                  </span>

                  <!-- Regular Price -->
                  <span
                      class="font-bold"
                      :class="hasSale
                        ? 'text-xl text-gray-500 dark:text-gray-400 line-through'
                        : 'text-4xl lg:text-5xl text-green-600 dark:text-green-400'"
                  >
                    {{ currentPrice }}
                  </span>

                  <!-- Discount Percentage -->
                  <span v-if="hasSale && discountPercentage" class="text-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-4 py-2 rounded-full font-bold">
                    {{ discountPercentage }}% OFF
                  </span>
                </div>

                <!-- You Save Amount -->
                <div v-if="hasSale" class="text-xl font-bold text-green-700 dark:text-green-300 mb-4">
                  You Save: {{ savings }} ({{ discountPercentage }}%)
                </div>

                <!-- 🔥 Flipkart-style Collapsible Sales Offers Section -->
                <div v-if="product.sales?.length" class="sales-offers-section" ref="salesSection">
                  <div class="sales-header">
                    <button
                        @click="salesExpanded = !salesExpanded"
                        class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/10 dark:to-pink-900/10 rounded-xl border border-red-200 dark:border-red-800/30 backdrop-blur-sm hover:from-red-100 hover:to-pink-100 dark:hover:from-red-800/20 dark:hover:to-pink-800/20 transition-all duration-200"
                    >
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                          <Icon name="mdi:fire" class="w-4 h-4 text-white" />
                        </div>
                        <div class="text-left">
                          <div class="font-bold text-red-700 dark:text-red-300">
                            Available Offers ({{ product.sales.length }})
                          </div>
                          <div class="text-sm text-red-600 dark:text-red-400">
                            {{ salesExpanded ? 'Click to collapse' : 'Click to view all offers' }}
                          </div>
                        </div>
                      </div>

                      <div class="flex items-center gap-2">
                        <!-- Best Offer Badge -->
                        <div class="bg-red-500 text-white px-2 py-1 rounded-md text-xs font-bold">
                          SAVE UP TO {{ maxDiscount }}%
                        </div>
                        <Icon
                            :name="salesExpanded ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                            class="w-5 h-5 text-red-600 dark:text-red-400 transition-transform duration-200"
                            :class="salesExpanded ? 'rotate-180' : ''"
                        />
                      </div>
                    </button>
                  </div>

                  <!-- Collapsible Sales List -->
                  <Transition
                      name="sales-collapse"
                      enter-active-class="transition-all duration-300 ease-out"
                      enter-from-class="max-h-0 opacity-0 transform scale-95"
                      enter-to-class="max-h-screen opacity-100 transform scale-100"
                      leave-active-class="transition-all duration-300 ease-in"
                      leave-from-class="max-h-screen opacity-100 transform scale-100"
                      leave-to-class="max-h-0 opacity-0 transform scale-95"
                  >
                    <div v-show="salesExpanded" class="sales-list mt-4 space-y-3 overflow-hidden">
                      <div
                          v-for="(sale, index) in product.sales"
                          :key="index"
                          class="sale-offer-card p-4 bg-white/90 dark:bg-slate-700/90 rounded-xl border border-red-200 dark:border-red-800/30 backdrop-blur-sm hover:border-red-300 dark:hover:border-red-700/50 transition-all duration-200 hover:scale-102"
                      >
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center">
                              <Icon name="mdi:tag" class="w-4 h-4 text-white" />
                            </div>
                            <div>
                              <div class="font-bold text-red-700 dark:text-red-300 mb-1">
                                {{ getSaleLabel(sale) }}
                              </div>
                              <div class="text-sm text-red-600 dark:text-red-400">
                                Save {{ getSaleSavings(sale) }} on this purchase
                              </div>
                              <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Final Price: <span class="font-semibold text-green-600 dark:text-green-400">{{ sale.sale_price }}</span>
                              </div>
                            </div>
                          </div>

                          <!-- Live Countdown Timer -->
                          <div v-if="sale.ends_till" class="text-right">
                            <div class="bg-red-100 dark:bg-red-900/30 px-3 py-2 rounded-lg">
                              <div class="text-sm font-bold text-red-700 dark:text-red-300">
                                {{ getTimeRemaining(sale.ends_till) }}
                              </div>
                              <div class="text-xs text-red-600 dark:text-red-400">
                                remaining
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </Transition>
                </div>

                <!-- Reward Points -->
                <div v-if="product.reward_point" class="reward-points mt-4 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10 rounded-xl border border-purple-200 dark:border-purple-800/30 backdrop-blur-sm">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                      <Icon name="mdi:diamond-stone" class="w-4 h-4 text-white" />
                    </div>
                    <div>
                      <div class="font-bold text-purple-700 dark:text-purple-300">
                        Earn {{ Math.round(product.reward_point) }} Reward Points
                      </div>
                      <div class="text-sm text-purple-600 dark:text-purple-400">
                        Worth ₹{{ (product.reward_point * 0.1).toFixed(0) }} on next purchase
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Product Variants -->
              <div v-if="product.variants?.length" class="variant-selection bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50 dark:border-slate-700/50" ref="variantSection">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                  <Icon name="mdi:palette" class="w-5 h-5 text-blue-500" />
                  Available Variants ({{ product.variants.length }})
                </h3>
                <div class="grid grid-cols-1 gap-4">
                  <NuxtLink
                      v-for="variant in product.variants"
                      :key="variant.sku"
                      :to="`/product/${variant.url}`"
                      class="variant-card p-4 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 bg-gray-50/90 dark:bg-slate-700/90 hover:bg-blue-50/90 dark:hover:bg-blue-900/20 transition-all duration-300 hover:scale-102 hover:shadow-md backdrop-blur-sm"
                  >
                    <div class="flex items-center gap-4">
                      <img
                          :src="variant.thumbnail || '/images/placeholder.png'"
                          :alt="variant.name"
                          class="w-20 h-20 object-cover rounded-lg bg-gray-100 dark:bg-gray-600"
                      />
                      <div class="flex-1">
                        <div class="font-bold text-gray-900 dark:text-white mb-1">{{ variant.name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">SKU: {{ variant.sku }}</div>
                        <div class="flex items-center justify-between">
                          <div class="font-bold text-green-600 dark:text-green-400 text-lg">{{ variant.price }}</div>
                          <div class="text-sm text-gray-500">Qty: {{ variant.min_quantity }}-{{ variant.max_quantity }}</div>
                        </div>
                      </div>
                      <Icon name="mdi:chevron-right" class="w-6 h-6 text-gray-400" />
                    </div>
                  </NuxtLink>
                </div>
              </div>

              <!-- Product Specifications -->
              <div v-if="product.filter_option?.length" class="product-features bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50 dark:border-slate-700/50" ref="featuresSection">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                  <Icon name="mdi:format-list-bulleted" class="w-5 h-5 text-green-500" />
                  Specifications
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                      v-for="filter in product.filter_option"
                      :key="`${filter.filter.name}-${filter.value}`"
                      class="spec-item p-4 bg-gray-50/90 dark:bg-slate-700/90 rounded-xl hover:bg-gray-100/90 dark:hover:bg-slate-600/90 transition-colors duration-200 backdrop-blur-sm"
                  >
                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">{{ filter.filter.name }}</div>
                    <div class="text-base font-bold text-gray-900 dark:text-white">{{ filter.value }}</div>
                  </div>
                </div>
              </div>

              <!-- Enhanced Stock & Delivery Info -->
              <div class="stock-delivery-section bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50 dark:border-slate-700/50" ref="stockSection">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Stock Info -->
                  <div class="stock-info">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                      <Icon name="mdi:package-variant" class="w-5 h-5 text-blue-500" />
                      Stock Information
                    </h4>
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <Icon
                            :name="isInStock ? 'mdi:check-circle' : 'mdi:alert-circle'"
                            class="w-5 h-5"
                            :class="isInStock ? 'text-green-500' : 'text-red-500'"
                        />
                        <span class="font-semibold" :class="isInStock ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                          {{ isInStock ? 'In Stock' : 'Out of Stock' }}
                        </span>
                      </div>
                      <div v-if="isInStock" class="text-sm text-gray-600 dark:text-gray-400">
                        {{ currentStock }} units available
                      </div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">
                        Min Order: {{ product.min_quantity }} | Max Order: {{ currentMaxQuantity }}
                      </div>
                    </div>
                  </div>

                  <!-- Delivery & Return Info -->
                  <div class="delivery-info">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                      <Icon name="mdi:truck-delivery" class="w-5 h-5 text-orange-500" />
                      Delivery & Returns
                    </h4>
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <Icon name="mdi:clock-fast" class="w-4 h-4 text-green-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Fast Delivery Available</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <Icon
                            :name="product.returnable ? 'mdi:keyboard-return' : 'mdi:cancel'"
                            class="w-4 h-4"
                            :class="product.returnable ? 'text-blue-500' : 'text-red-500'"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                          {{ product.returnable ? '7 days return policy' : 'No returns available' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="action-buttons space-y-4 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-gray-200/50 dark:border-slate-700/50" ref="actionButtons">
                <!-- Add to Cart with Quantity -->
                <AddToCartWithQuantitySelector
                    :sku="product.sku"
                    :min-quantity="product.min_quantity"
                    :max-quantity="currentMaxQuantity"
                />

                <!-- Buy Now Button -->
                <BuyNowButton
                    :sku="product.sku"
                    :min-quantity="product.min_quantity"
                />

                <!-- Additional Actions -->
                <div class="grid grid-cols-2 gap-4">
                  <button class="action-btn w-full py-3 bg-gray-100/90 dark:bg-slate-700/90 hover:bg-gray-200/90 dark:hover:bg-slate-600/90 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 backdrop-blur-sm">
                    <Icon name="mdi:share-variant" class="w-5 h-5" />
                    Share Product
                  </button>

                  <button class="action-btn w-full py-3 bg-gray-100/90 dark:bg-slate-700/90 hover:bg-gray-200/90 dark:hover:bg-slate-600/90 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 backdrop-blur-sm">
                    <Icon name="mdi:bookmark-outline" class="w-5 h-5" />
                    Save for Later
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Product Description -->
      <section v-if="product.description" class="description-section px-4 lg:px-6 py-8" ref="descriptionSection">
        <div class="max-w-7xl mx-auto">
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200/50 dark:border-slate-700/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 dark:border-slate-700/50">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <Icon name="mdi:file-document-outline" class="w-6 h-6 text-blue-500" />
                Product Description
              </h2>
            </div>
            <div class="p-6">
              <FilamentTipTapContent :text="product.description" />
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section class="reviews-section px-4 lg:px-6 py-8" ref="reviewsSection">
        <div class="max-w-7xl mx-auto">
          <ProductComment :url="product.url" />
        </div>
      </section>

    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctumFetch, useRuntimeConfig } from '#imports'
import { useToast } from '~/composables/useToast'
import { useWishlist } from '~/composables/useWishlist'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Components
import GlobalLoader from '~/components/GlobalLoader.vue'
import AddToCartWithQuantitySelector from '~/components/store/buttons/AddToCartWithQuantitySelector.vue'
import BuyNowButton from '~/components/cart/BuyNowButton.vue'
import ProductMediaSlider from '~/components/sliders/ProductMediaSlider.vue'
import ProductComment from '~/components/product/ProductComment.vue'
import FilamentTipTapContent from '~/components/FilamentTipTapContent.vue'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

// Types (keeping existing interfaces)
interface ProductSale {
  start_from: string | null
  ends_till: string
  sale_price: string
  discount: number
  discount_type: string
}

interface ProductTier {
  min_quantity: number
  max_quantity: number
  wholesale_unit_quantity: number | null
  price: string
  stock: number
  in_stock: boolean
}

interface ProductVariant {
  name: string
  url: string
  sku: string
  short_description: string | null
  price: string
  min_quantity: number
  max_quantity: number
  reward_point: number
  returnable: boolean
  views: number
  thumbnail: string
  formatted: {
    regular: string | null
    sale: string | null
    effective: string | null
  }
}

interface ProductCategory {
  name: string
  url: string
  views: number
  thumbnail: string
  meta: {
    keywords: string[]
  }
}

interface FilterOption {
  value: string
  swatch_value: string | null
  filter: {
    name: string
  }
}

interface Engagement {
  id: number
  review: string
  rating: number
  helpful_votes: number
  author: {
    name: string
    type: string
  }
  product: {
    url: string
    sku: string
  }
  created_at: string
  updated_at: string
}

interface Product {
  name: string
  url: string
  sku: string
  short_description: string
  price: string
  min_quantity: number
  max_quantity: number
  reward_point: number
  returnable: boolean
  views: number
  thumbnail: string
  formatted: {
    regular: string | null
    sale: string | null
    effective: string | null
  }
  banner: string[]
  description: string
  meta: {
    og_title: string
    meta_title: string
    canonical_url: string
    meta_keywords: string
    twitter_title: string
    og_description: string
    meta_description: string
    twitter_description: string
  }
  hasParent: boolean
  filter_option: FilterOption[]
  categories: ProductCategory[]
  tiers: ProductTier[]
  sales: ProductSale[]
  variants: ProductVariant[]
  engagement: Engagement[]
  is_wishlisted?: number
  review_count?: number
  engagements_avg_rating?: number
  engagements_avg_helpful_votes?: number
}

// Composables
const route = useRoute()
const config = useRuntimeConfig()
const toast = useToast()
const { toggleWishlist: apiToggleWishlist, isLoggedIn } = useWishlist()

// Refs for GSAP animations
const imageContainer = ref()
const detailsContainer = ref()
const productHeader = ref()
const categorySection = ref()
const priceSection = ref()
const salesSection = ref()
const variantSection = ref()
const featuresSection = ref()
const stockSection = ref()
const actionButtons = ref()
const descriptionSection = ref()
const reviewsSection = ref()

// State
const productUrl = computed(() => route.params.url as string)
const isLoading = ref(false)
const error = ref<string | null>(null)
const product = ref<Product | null>(null)
const wishlistLoading = ref(false)
const timeUpdateInterval = ref<NodeJS.Timeout>()
const salesExpanded = ref(false) // 🔥 New: Control sales list visibility

definePageMeta({
  layout: 'default',
})

// ✅ CORRECTED Computed Properties with Proper Pricing Logic
const hasSale = computed(() => product.value?.sales?.length > 0)
const currentSale = computed(() => hasSale.value ? product.value!.sales[0] : null)
const currentSalePrice = computed(() => currentSale.value?.sale_price || '')

// Helper: Get minimum price tier when tiers available
const minPriceTier = computed(() => {
  if (!product.value?.tiers?.length) return null

  return product.value.tiers.reduce((min, tier) => {
    const minPrice = parseFloat(min.price.replace(/[^0-9.]/g, ''))
    const tierPrice = parseFloat(tier.price.replace(/[^0-9.]/g, ''))
    return tierPrice < minPrice ? tier : min
  })
})

// ✅ CORRECTED: Use minimum price tier when available, fallback to product price
const currentPrice = computed(() => {
  return minPriceTier.value?.price || product.value?.price || ''
})

const currentStock = computed(() => {
  return minPriceTier.value?.stock || 0
})

const currentMaxQuantity = computed(() => {
  return minPriceTier.value?.max_quantity || product.value?.max_quantity || 1
})

const isInStock = computed(() => {
  return minPriceTier.value?.in_stock ?? true
})

const isWishlisted = computed(() => product.value?.is_wishlisted === 1)

// 🔥 NEW: Calculate maximum discount percentage across all sales
const maxDiscount = computed(() => {
  if (!product.value?.sales?.length) return 0

  return Math.max(...product.value.sales.map(sale => {
    if (sale.discount_type === 'by_percent') {
      return sale.discount
    } else {
      // For fixed discounts, calculate percentage
      const original = parseFloat(currentPrice.value.replace(/[^0-9.]/g, ''))
      return Math.round((sale.discount / original) * 100)
    }
  }))
})

// Media for ProductMediaSlider component
const productMedia = computed(() => {
  const media = []

  // Add main thumbnail
  if (product.value?.thumbnail) {
    media.push(product.value.thumbnail)
  }

  // Add banner images
  if (product.value?.banner?.length) {
    media.push(...product.value.banner)
  }

  // Fallback to placeholder if no media
  if (media.length === 0) {
    media.push('/images/placeholder.png')
  }

  return media
})

const discountPercentage = computed(() => {
  if (!hasSale.value || !currentSale.value) return null
  const original = parseFloat(currentPrice.value.replace(/[^0-9.]/g, ''))
  const sale = parseFloat(currentSalePrice.value.replace(/[^0-9.]/g, ''))
  return Math.round(((original - sale) / original) * 100)
})

const savings = computed(() => {
  if (!hasSale.value || !currentSale.value) return ''
  const original = parseFloat(currentPrice.value.replace(/[^0-9.]/g, ''))
  const sale = parseFloat(currentSalePrice.value.replace(/[^0-9.]/g, ''))
  const saved = original - sale
  return `₹${saved.toFixed(0)}`
})

const averageRating = computed(() => {
  return product.value?.engagements_avg_rating || 0
})

// Methods
const getSaleLabel = (sale: ProductSale) => {
  switch (sale.discount_type) {
    case 'by_percent':
      return `${sale.discount}% OFF`
    case 'by_fixed':
      return `₹${sale.discount} OFF`
    default:
      return 'SPECIAL PRICE'
  }
}

const getSaleSavings = (sale: ProductSale) => {
  const original = parseFloat(currentPrice.value.replace(/[^0-9.]/g, ''))
  const salePrice = parseFloat(sale.sale_price.replace(/[^0-9.]/g, ''))
  const saved = original - salePrice
  return `₹${saved.toFixed(0)}`
}

// Live countdown timer
const getTimeRemaining = (endTime: string) => {
  const now = new Date().getTime()
  const end = new Date(endTime).getTime()
  const diff = end - now

  if (diff <= 0) return 'Expired'

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  if (days > 0) return `${days}d ${hours}h ${minutes}m`
  if (hours > 0) return `${hours}h ${minutes}m ${seconds}s`
  return `${minutes}m ${seconds}s`
}

const toggleWishlist = async () => {
  if (!isLoggedIn.value) {
    toast.error('Please login to add items to your wishlist')
    return
  }

  if (!product.value || wishlistLoading.value) return

  wishlistLoading.value = true
  const originalStatus = isWishlisted.value

  try {
    // Optimistic UI update
    product.value.is_wishlisted = originalStatus ? 0 : 1

    // Make API call
    const response = await apiToggleWishlist(product.value.url, originalStatus)

    if (response?.data?.message) {
      toast.success(response.data.message)
    }

  } catch (error: any) {
    console.error('Wishlist toggle failed:', error)

    // Revert optimistic update
    product.value.is_wishlisted = originalStatus ? 1 : 0

    let errorMessage = 'Failed to update wishlist'
    if (error.message?.includes('login')) {
      errorMessage = 'Please login to manage your wishlist'
    } else if (error.message?.includes('not found')) {
      errorMessage = 'Product not found'
    }

    toast.error(errorMessage)

  } finally {
    wishlistLoading.value = false
  }
}

const fetchProduct = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/products/${productUrl.value}`, {
      credentials: 'include'
    })

    if (response?.data) {
      product.value = response.data

      // Initialize animations after product data loads
      await nextTick()
      initAnimations()

      // Start countdown timer updates
      startTimeUpdates()
    } else {
      error.value = 'Product not found'
    }
  } catch (e: any) {
    console.error('Failed to fetch product:', e)
    if (e.status === 404) {
      error.value = 'Product not found'
    } else {
      error.value = e.message || 'Failed to load product'
    }
  } finally {
    isLoading.value = false
  }
}

const startTimeUpdates = () => {
  // Update countdown timers every second
  if (timeUpdateInterval.value) {
    clearInterval(timeUpdateInterval.value)
  }

  timeUpdateInterval.value = setInterval(() => {
    // Force reactive updates for countdown timers
    // The computed getTimeRemaining will automatically update
  }, 1000)
}

const initAnimations = () => {
  if (!process.client) return

  // Reset any existing animations
  gsap.killTweensOf("*")

  // Image container animation
  gsap.fromTo(imageContainer.value,
      { opacity: 0, scale: 0.9, rotationY: -15 },
      {
        opacity: 1,
        scale: 1,
        rotationY: 0,
        duration: 1.2,
        ease: "power3.out",
        delay: 0.2
      }
  )

  // Details container animation
  gsap.fromTo(detailsContainer.value,
      { opacity: 0, x: 50 },
      {
        opacity: 1,
        x: 0,
        duration: 1,
        ease: "power3.out",
        delay: 0.4
      }
  )

  // Category tags animation
  if (categorySection.value) {
    gsap.fromTo(categorySection.value?.children || [],
        { opacity: 0, y: -20 },
        {
          opacity: 1,
          y: 0,
          duration: 0.6,
          ease: "power2.out",
          stagger: 0.1,
          delay: 0.6
        }
    )
  }

  // Product header animation
  gsap.fromTo(productHeader.value?.children || [],
      { opacity: 0, y: 30 },
      {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: "power2.out",
        stagger: 0.1,
        delay: 0.8
      }
  )

  // Price section special animation
  if (priceSection.value) {
    gsap.fromTo(priceSection.value,
        { opacity: 0, scale: 0.9, y: 20 },
        {
          opacity: 1,
          scale: 1,
          y: 0,
          duration: 0.8,
          ease: "back.out(1.7)",
          delay: 1.0
        }
    )
  }

  // ScrollTrigger animations for sections below the fold
  const sections = [
    salesSection.value,
    variantSection.value,
    featuresSection.value,
    stockSection.value,
    actionButtons.value,
    descriptionSection.value,
    reviewsSection.value
  ].filter(Boolean)

  sections.forEach((section, index) => {
    gsap.fromTo(section,
        { opacity: 0, y: 50 },
        {
          opacity: 1,
          y: 0,
          duration: 0.8,
          ease: "power2.out",
          scrollTrigger: {
            trigger: section,
            start: "top 85%",
            end: "bottom 15%",
            toggleActions: "play none none reverse"
          }
        }
    )
  })

  // Continuous animations
  startContinuousAnimations()
}

const startContinuousAnimations = () => {
  // Price section glow effect
  if (priceSection.value && hasSale.value) {
    gsap.to(priceSection.value, {
      boxShadow: "0 0 30px rgba(34, 197, 94, 0.2)",
      duration: 3,
      ease: "power2.inOut",
      repeat: -1,
      yoyo: true
    })
  }

  // Wishlist button heartbeat
  const wishlistBtn = document.querySelector('.wishlist-btn')
  if (wishlistBtn && isWishlisted.value) {
    gsap.to(wishlistBtn, {
      scale: 1.1,
      duration: 1,
      ease: "power2.inOut",
      repeat: -1,
      yoyo: true
    })
  }
}

const refresh = () => {
  fetchProduct()
}

// Lifecycle
onMounted(() => {
  fetchProduct()
})

onUnmounted(() => {
  if (timeUpdateInterval.value) {
    clearInterval(timeUpdateInterval.value)
  }
})

// SEO
useSeoMeta({
  title: computed(() => product.value?.meta?.meta_title || product.value?.name || 'Product'),
  description: computed(() => product.value?.meta?.meta_description || product.value?.short_description || ''),
  ogTitle: computed(() => product.value?.meta?.og_title || product.value?.name || ''),
  ogDescription: computed(() => product.value?.meta?.og_description || product.value?.short_description || ''),
  twitterTitle: computed(() => product.value?.meta?.twitter_title || product.value?.name || ''),
  twitterDescription: computed(() => product.value?.meta?.twitter_description || product.value?.short_description || ''),
  keywords: computed(() => product.value?.meta?.meta_keywords || ''),
})

// Watch for route changes
watch(() => route.params.url, (newUrl) => {
  if (newUrl && newUrl !== productUrl.value) {
    fetchProduct()
  }
})
</script>

<style scoped>
.category-tag:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.variant-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.spec-item:hover {
  transform: translateY(-1px);
}

.action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* 🔥 Sales collapse animation styles */
.sales-collapse-enter-active,
.sales-collapse-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.sales-collapse-enter-from {
  max-height: 0;
  opacity: 0;
  transform: scaleY(0.8);
  transform-origin: top;
}

.sales-collapse-enter-to {
  max-height: 1000px;
  opacity: 1;
  transform: scaleY(1);
}

.sales-collapse-leave-from {
  max-height: 1000px;
  opacity: 1;
  transform: scaleY(1);
}

.sales-collapse-leave-to {
  max-height: 0;
  opacity: 0;
  transform: scaleY(0.8);
  transform-origin: top;
}

/* Enhanced transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Hover effects */
.hover\:scale-102:hover {
  transform: scale(1.02);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .text-4xl, .text-5xl {
    font-size: 2rem;
  }

  .lg\:col-span-5, .lg\:col-span-7 {
    grid-column: span 1;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  * {
    transition-duration: 0ms !important;
    animation-duration: 0ms !important;
  }
}

/* Heartbeat animation for wishlisted items */
@keyframes heartbeat {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.wishlist-btn.wishlisted {
  animation: heartbeat 1.5s ease-in-out infinite;
}
</style>
