<template>
  <div class="w-full h-full mt-16 px-4 lg:px-20">
    <!-- Loader -->
    <GlobalLoader v-if="isLoading"/>

    <div v-else>
      <div class="mt-14 mr-5">
        <CartCounter/>
      </div>

      <div v-if="product" class="max-w-7xl mx-auto py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16">

          <!-- Left Side - Image Slider -->
          <div class="slider-box w-full h-full max-lg:mx-auto">
            <ProductMediaSlider :media="product.banner"/>
          </div>

          <!-- Right Side - Product Details -->
          <div class="flex justify-center items-start">
            <div class="pro-detail w-full max-lg:max-w-[608px] max-lg:mx-auto max-lg:mt-8">

              <!-- Product Title & Wishlist -->
              <div class="flex items-start justify-between gap-6 mb-6">
                <div class="text flex-1">
                  <h1 class="font-bold text-3xl leading-10 text-gray-900 dark:text-white mb-2">
                    {{ product.name }}
                  </h1>
                  <p class="font-normal text-base text-gray-500 dark:text-gray-400 mb-3">
                    SKU: {{ product.sku }}
                  </p>

                  <!-- Current Product Info as Small Tags -->
                  <div v-if="product.filter_option && product.filter_option.length > 0" class="flex flex-wrap gap-1.5">
                    <span
                        v-for="filterOption in product.filter_option.slice(0, 5)"
                        :key="filterOption.filter.name"
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                    >
                      {{ filterOption.filter.name }}: {{ filterOption.swatch_value || filterOption.value }}
                    </span>
                    <span
                        v-if="product.filter_option.length > 5"
                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300"
                    >
                      +{{ product.filter_option.length - 5 }} more
                    </span>
                  </div>
                </div>
                <button class="group transition-all duration-500 p-0.5 flex-shrink-0">
                  <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="fill-indigo-50 transition-all duration-500 group-hover:fill-indigo-100 dark:fill-gray-700 dark:group-hover:fill-gray-600" cx="30" cy="30" r="30"/>
                    <path class="stroke-indigo-600 transition-all duration-500 group-hover:stroke-indigo-700" d="M21.4709 31.3196L30.0282 39.7501L38.96 30.9506M30.0035 22.0789C32.4787 19.6404 36.5008 19.6404 38.976 22.0789C41.4512 24.5254 41.4512 28.4799 38.9842 30.9265M29.9956 22.0789C27.5205 19.6404 23.4983 19.6404 21.0231 22.0789C18.548 24.5174 18.548 28.4799 21.0231 30.9184M21.0231 30.9184L21.0441 30.939M21.0231 30.9184L21.4628 31.3115" stroke="" stroke-width="1.6" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
              </div>

              <!-- Price & Rating -->
              <div class="flex flex-col min-[400px]:flex-row min-[400px]:items-center mb-8 gap-y-3">
                <div class="flex items-center">
                  <h2 class="font-semibold text-2xl leading-9 text-gray-900 dark:text-white">
                    {{ product.price }}
                  </h2>
                  <span v-if="product.reward_point" class="ml-3 font-semibold text-lg text-indigo-600">
                    +{{ Math.round(product.reward_point) }} pts
                  </span>
                </div>
                <svg class="mx-5 max-[400px]:hidden" xmlns="http://www.w3.org/2000/svg" width="2" height="36" viewBox="0 0 2 36" fill="none">
                  <path d="M1 0V36" stroke="#E5E7EB"/>
                </svg>
                <button class="flex items-center gap-1 rounded-lg bg-amber-400 py-1.5 px-2.5 w-max">
                  <Icon name="mdi:star" class="w-4 h-4 text-white"/>
                  <span class="text-base font-medium text-white">4.8</span>
                </button>
              </div>

              <!-- Categories -->
              <div v-if="product.categories" class="mb-6">
                <div class="flex flex-wrap gap-2 items-center">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Categories:</span>
                  <div v-for="category in product.categories" :key="category.url">
                    <NuxtLink :to="`/category/${category.url}`">
                      <span class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-full text-sm transition-colors">
                        {{ category.name }}
                      </span>
                    </NuxtLink>
                  </div>
                </div>
              </div>

              <!-- Available Variants - Flipkart Style -->
              <div v-if="product.parent?.variants || product.variants" class="mb-8">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="font-medium text-lg text-gray-900 dark:text-white">
                    Similar Products
                    <span v-if="product.parent?.variants.length || product.variants.length">
                      ({{ product.parent?.variants.length || product.variants.length}})
                    </span>
                  </h3>

                  <span class="text-sm text-gray-500 dark:text-gray-400">
                    Choose your preferred option
                  </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                  <div
                      v-for="variant in product.parent?.variants || product.variants"
                      :key="variant.url"
                      class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 group relative"
                      :class="variant.url === product.url ? 'ring-2 ring-indigo-500 ring-offset-2 shadow-lg' : ''"
                  >
                    <NuxtLink :to="`/product/${variant.url}`" :title="`View ${variant.name}`">
                      <!-- Variant Image -->
                      <div class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-gray-700">
                        <img
                            :src="variant.thumbnail"
                            :alt="variant.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            loading="lazy"
                        />
                        <!-- Stock Status -->
                        <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded shadow-sm">
                          In Stock
                        </span>
                        </div>
                        <!-- Current Badge -->
                        <div
                            v-if="variant.url === product.url"
                            class="absolute top-2 right-2 bg-indigo-600 text-white px-2 py-1 rounded-md text-xs font-bold shadow-lg"
                        >
                          CURRENT
                        </div>
                      </div>
                      <!-- Variant Info -->
                    </NuxtLink>
                  </div>
                </div>

                <!-- Variant Selection Helper -->
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                  <div class="flex items-center gap-3">
                    <Icon name="mdi:information" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0"/>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                      <span class="font-medium">{{ product.parent?.variants.length }} variants available</span>
                      - Hover for quick actions or click to view details
                    </div>
                  </div>
                </div>
              </div>

              <!-- Quantity & Add to Cart -->
              <div class="flex items-center flex-col min-[400px]:flex-row gap-3 mb-6">
                <AddToCartWithQuantitySelector
                    :sku="product.sku"
                    :min-quantity="product.min_quantity"
                    :max-quantity="product.max_quantity"
                    class="mt-2 w-96"
                />
              </div>

              <!-- Buy Now Button -->
              <BuyNowButton
                  :sku="product.sku"
                  :min-quantity="product.min_quantity"
              />

              <!-- Available Offers -->
              <div v-if="product.sales?.length" class="mb-8">
                <h3 class="font-medium text-lg text-gray-900 dark:text-white mb-4">Available Offers</h3>
                <div class="space-y-3">
                  <div
                      v-for="offer in product.sales"
                      :key="offer.sale.uuid"
                      class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800"
                  >
                    <Icon name="mdi:tag-outline" class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0"/>
                    <div class="text-sm">
                      <span class="font-medium text-green-800 dark:text-green-200">
                        {{ formatOffer(offer) }}
                      </span>
                      <button class="ml-2 text-blue-600 dark:text-blue-400 hover:underline">
                        T&C
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Product Overview -->
              <div v-if="product.short_description" class="mb-8">
                <h3 class="font-medium text-lg text-gray-900 dark:text-white mb-4">Product Overview</h3>
                <p v-html="product.short_description" class="text-gray-600 dark:text-gray-300 leading-relaxed"></p>
              </div>

            </div>
          </div>
        </div>

        <!-- Full Product Description -->
        <div v-if="product.description" class="mt-16 max-w-7xl mx-auto">
          <h2 class="font-bold text-2xl text-gray-900 dark:text-white mb-6">Detailed Description</h2>
          <FilamentTipTapContent :text="product.description"/>
        </div>


        <!-- Product Comments Section -->
        <div class="mt-16 max-w-7xl mx-auto">
          <ProductComment
              :product-id="product.id"
              :initial-comments="product.comments || []"
              :auto-load="true"
          />
        </div>


      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import CartCounter from "~/components/cart/CartCounter.vue";
import GlobalLoader from "~/components/GlobalLoader.vue"
import { useRoute, useRuntimeConfig, useSanctumFetch } from "#imports";
import ProductMediaSlider from "~/components/sliders/ProductMediaSlider.vue";
import FilamentTipTapContent from "~/components/FilamentTipTapContent.vue";
import AddToCartWithQuantitySelector from "~/components/store/buttons/AddToCartWithQuantitySelector.vue";
import BuyNowButton from "~/components/cart/BuyNowButton.vue";

const route = useRoute()
const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)
const product = ref(null)

// API calls
async function fetchProductDetail() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/${route.params.url}`, {
      method: 'GET'
    })
    product.value = res?.data ?? null
  } catch (error) {
    console.error('[✘] Failed to load product', error)
  }
}

onMounted(async () => {
  try {
    await fetchProductDetail()
  } catch (e) {
    console.error('Api fetch failed!', e)
  } finally {
    isLoading.value = false
  }
})

watch(() => route.params.url, async (newUrl) => {
  if (newUrl) {
    isLoading.value = true
    await fetchProductDetail()
    isLoading.value = false
  }
})

const formatOffer = (offer) => {
  if (!offer) return ""
  let discountAmount = offer.discount ?? ""
  if (offer.discount_type === "by_percent" || offer.discount_type === "to_percent") {
    discountAmount += "%"
  }
  return `${offer.sale.name} Offer ${discountAmount} off ${offer.sale.description}`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
