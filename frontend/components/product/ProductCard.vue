<template>
  <div class="product-card group relative h-full flex flex-col">

    <!-- Main Card Container -->
    <div class="relative bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-200 dark:border-slate-700 h-full flex flex-col">

      <!-- Enhanced Border Effect - Different for Light/Dark Mode -->
      <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
        <!-- Light mode: Subtle blue glow -->
        <div class="absolute inset-0 bg-blue-500/15 dark:bg-transparent rounded-xl blur-xl"></div>
        <!-- Dark mode: Colorful gradient -->
        <div class="absolute inset-0 bg-transparent dark:bg-gradient-to-r dark:from-blue-500/30 dark:via-purple-500/30 dark:to-pink-500/30 rounded-xl blur-xl"></div>
      </div>

      <NuxtLink :to="`/product/${product.url}`" class="block flex-1 flex flex-col relative z-10">

        <!-- Enhanced Image Container -->
        <div class="product-image relative aspect-square bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 overflow-hidden">
          <img
              :src="product.thumbnail || '/images/placeholder.png'"
              :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
          />

          <!-- Enhanced Sale Badge -->
          <div v-if="hasSale" class="absolute top-2 left-2 z-20">
            <div class="sale-badge relative">
              <div class="absolute inset-0 bg-red-500 rounded-md blur-sm opacity-40 scale-110"></div>
              <div class="relative bg-gradient-to-r from-red-500 to-pink-500 text-white px-2 py-1 rounded-md text-xs font-bold shadow-md flex items-center gap-1">
                <Icon name="mdi:fire" class="w-2.5 h-2.5" />
                <span>{{ discountLabel }}</span>
              </div>
            </div>
          </div>

          <!-- Enhanced Stock Status Badge -->
          <div v-if="!isInStock" class="absolute top-2" :class="hasSale ? 'right-2' : 'left-2'" style="z-index: 20;">
            <div class="stock-badge bg-gray-900/90 backdrop-blur-sm text-white px-2 py-1 rounded-md text-xs font-bold">
              Out of Stock
            </div>
          </div>
          <div v-else-if="isLowStock" class="absolute top-2" :class="hasSale ? 'right-2' : 'left-2'" style="z-index: 20;">
            <div class="stock-badge bg-orange-500 text-white px-2 py-1 rounded-md text-xs font-bold shadow-md">
              <Icon name="mdi:alert" class="w-2.5 h-2.5 inline mr-1" />
              Low Stock
            </div>
          </div>

          <!-- Enhanced Wishlist Button -->
          <button
              v-if="showWishlist"
              @click.prevent="handleWishlistToggle"
              :disabled="wishlistLoading"
              class="absolute top-2 right-2 w-8 h-8 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-all duration-200 z-30 border border-gray-200/50 dark:border-slate-600/50"
              :class="{
                'text-red-500 bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800': isWishlisted,
                'text-gray-600 dark:text-gray-400': !isWishlisted,
                'cursor-not-allowed opacity-60': wishlistLoading
              }"
          >
            <Icon
                :name="wishlistLoading ? 'mdi:loading' : (isWishlisted ? 'mdi:heart' : 'mdi:heart-outline')"
                class="w-4 h-4"
                :class="wishlistLoading ? 'animate-spin' : ''"
            />
          </button>

          <!-- Enhanced Quick View Overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
            <div class="quick-actions transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
              <button
                  @click.prevent="quickView"
                  class="w-10 h-10 bg-white/95 dark:bg-slate-800/95 text-gray-900 dark:text-white rounded-full flex items-center justify-center hover:scale-110 transition-all duration-200 shadow-lg backdrop-blur-sm border border-gray-200/50 dark:border-slate-600/50"
              >
                <Icon name="mdi:eye" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Enhanced Product Info - Compact -->
        <div class="product-info p-3 flex-1 flex flex-col">

          <!-- Enhanced Categories Tags - Compact -->
          <div v-if="product.categories?.length" class="flex flex-wrap gap-1 mb-2">
            <span
                v-for="category in product.categories.slice(0, 2)"
                :key="category.url"
                class="px-2 py-0.5 bg-gradient-to-r from-blue-500/10 to-purple-500/10 dark:from-blue-500/20 dark:to-purple-500/20 text-blue-700 dark:text-blue-300 text-xs rounded-full font-medium"
            >
              {{ category.name }}
            </span>
          </div>

          <!-- Enhanced Product Name - Compact -->
          <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300 min-h-[2rem]">
            {{ product.name }}
          </h3>

          <!-- Enhanced Rating & Reviews - Compact -->
          <div class="flex items-center gap-2 mb-2">
            <div class="flex items-center gap-0.5">
              <div class="flex items-center">
                <Icon v-for="i in 5" :key="i"
                      :name="i <= Math.floor(parseFloat(product.rating || '0')) ? 'mdi:star' : 'mdi:star-outline'"
                      class="w-3 h-3"
                      :class="i <= Math.floor(parseFloat(product.rating || '0')) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'" />
              </div>
              <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 ml-1">{{ rating }}</span>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              ({{ product.review || 0 }})
            </span>
          </div>

          <!-- Enhanced Pricing Section - More Compact -->
          <div class="mt-auto">
            <div class="pricing-container p-2 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200/30 dark:border-green-800/30 mb-2">
              <div class="flex items-baseline gap-2">
                <!-- Sale Price -->
                <span v-if="hasSale" class="text-lg font-black text-green-600 dark:text-green-400">
                  {{ currentSalePrice }}
                </span>

                <!-- Regular Price -->
                <span
                    class="font-bold"
                    :class="hasSale
                      ? 'text-xs text-gray-500 dark:text-gray-400 line-through'
                      : 'text-lg text-green-600 dark:text-green-400'"
                >
                  {{ displayPrice }}
                </span>

                <!-- You Save Badge -->
                <span v-if="hasSale" class="text-xs bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-1.5 py-0.5 rounded font-bold">
                  -{{ savings }}
                </span>
              </div>

              <!-- Sale Timer - Compact -->
              <div v-if="hasSale && saleEndsLabel" class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400 font-medium mt-0.5">
                <Icon name="mdi:clock-fast" class="w-3 h-3" />
                {{ saleEndsLabel }}
              </div>
            </div>

            <!-- Enhanced Features Row - Compact -->
            <div class="features-row flex items-center justify-between text-xs mb-2">
              <!-- Reward Points -->
              <div v-if="product.reward_point" class="flex items-center gap-1 text-purple-600 dark:text-purple-400 font-medium">
                <Icon name="mdi:diamond-stone" class="w-3 h-3" />
                <span>{{ Math.round(product.reward_point) }}pts</span>
              </div>

              <!-- Stock Status -->
              <div class="flex items-center gap-1 font-medium">
                <span v-if="isInStock" class="text-green-600 dark:text-green-400 flex items-center gap-1">
                  <Icon name="mdi:check-circle" class="w-3 h-3" />
                  {{ stockCount }} left
                </span>
                <span v-else class="text-red-600 dark:text-red-400 flex items-center gap-1">
                  <Icon name="mdi:close-circle" class="w-3 h-3" />
                  No Stock
                </span>
              </div>
            </div>
          </div>
        </div>
      </NuxtLink>

      <!-- Compact Add to Cart Section -->
      <div class="product-actions p-3 pt-0 relative z-10">
        <AddToCartButton
            :sku="product.sku"
            :quantity="minQuantity"
            :disabled="!isInStock"
            class="w-full rounded-lg font-semibold text-sm py-2.5"
            @success="handleAddToCartSuccess"
            @error="handleAddToCartError"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useWishlist } from '~/composables/useWishlist'
import AddToCartButton from '~/components/cart/AddToCartButton.vue'

interface ProductSale {
  sale_price: string
  discount: string
  ends_till: string
  remaining: string
  action_type: 'by_percent' | 'by_fixed' | 'to_percent' | 'to_fixed'
}

interface ProductTire {
  min_quantity: number
  max_quantity: number
  wholesale_unit_quantity: number | null
  price: string
  stock: number
  in_stock: boolean
}

interface ProductCategory {
  name: string
  url: string
  views: number
  thumbnail: string
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
  review_avg_helpful_votes: string
  reward_point: number
  is_wishlisted: number | null
  price: string
  sales: ProductSale[]
  categories: ProductCategory[]
  thumbnail: string
  tire: ProductTire | null
}

const props = defineProps<{
  product: Product
  showWishlist?: boolean
}>()

const emit = defineEmits<{
  wishlistToggle: [product: Product, newStatus: boolean]
  quickView: [product: Product]
  addToCartSuccess: [product: Product]
  addToCartError: [error: any]
}>()

// Composables
const { toggleWishlist, isLoggedIn } = useWishlist()
const wishlistLoading = ref(false)

// Local reactive wishlist state (for immediate UI updates)
const localWishlistStatus = ref(props.product.is_wishlisted === 1)

// Watch for prop changes to sync local state
watch(() => props.product.is_wishlisted, (newValue) => {
  localWishlistStatus.value = newValue === 1
})

// Computed: Sale Information
const hasSale = computed(() => props.product.sales?.length > 0)
const currentSale = computed(() => hasSale.value ? props.product.sales[0] : null)
const currentSalePrice = computed(() => currentSale.value?.sale_price || '')

const discountLabel = computed(() => {
  if (!currentSale.value) return ''

  const discount = currentSale.value.discount
  const actionType = currentSale.value.action_type

  switch (actionType) {
    case 'by_percent':
    case 'to_percent':
      return `${parseFloat(discount.replace(/[^0-9.]/g, '')) * 100}% OFF`
    case 'by_fixed':
      return `${discount} OFF`
    case 'to_fixed':
      return 'SPECIAL PRICE'
    default:
      return 'SALE'
  }
})

const savings = computed(() => {
  if (!hasSale.value) return ''
  const original = parseFloat(displayPrice.value.replace(/[^0-9.]/g, ''))
  const sale = parseFloat(currentSalePrice.value.replace(/[^0-9.]/g, ''))
  const saved = original - sale
  return `₹${saved.toFixed(0)}`
})

const saleEndsLabel = computed(() => {
  if (!currentSale.value?.remaining) return ''
  return `Ends ${currentSale.value.remaining}`
})

// Computed: Pricing Logic
const displayPrice = computed(() => {
  return props.product.tire?.price || props.product.price
})

// Computed: Stock Information
const isInStock = computed(() => props.product.tire?.in_stock ?? props.product.has_stock)
const stockCount = computed(() => props.product.tire?.stock || 0)
const isLowStock = computed(() => isInStock.value && stockCount.value > 0 && stockCount.value <= 10)
const minQuantity = computed(() => props.product.tire?.min_quantity || 1)

// Computed: Rating
const rating = computed(() => {
  const r = parseFloat(props.product.rating || '0')
  return r > 0 ? r.toFixed(1) : 'No rating'
})

// Computed: Wishlist - Use local state for immediate feedback
const isWishlisted = computed(() => localWishlistStatus.value)

// Methods
const handleWishlistToggle = async () => {
  if (!isLoggedIn.value) {
    // Show login prompt or redirect to login
    alert('Please login to add items to your wishlist')
    return
  }

  if (wishlistLoading.value) return

  wishlistLoading.value = true
  const originalStatus = localWishlistStatus.value

  try {
    // Optimistic UI update
    localWishlistStatus.value = !originalStatus

    // Make API call
    const response = await toggleWishlist(props.product.url, originalStatus)

    // Emit event for parent components to handle
    emit('wishlistToggle', props.product, localWishlistStatus.value)

    // Show success message (optional)
    if (response?.data?.message) {
      console.log('✅', response.data.message)
      // You can replace this with a toast notification system
    }

  } catch (error: any) {
    console.error('Wishlist toggle failed:', error)

    // Revert optimistic update on failure
    localWishlistStatus.value = originalStatus

    // Show error message
    let errorMessage = 'Failed to update wishlist'

    if (error.message?.includes('login')) {
      errorMessage = 'Please login to manage your wishlist'
    } else if (error.message?.includes('not found')) {
      errorMessage = 'Product not found'
    } else if (error.message?.includes('Server error')) {
      errorMessage = 'Server error. Please try again.'
    }

    alert(errorMessage) // Replace with toast notification

  } finally {
    wishlistLoading.value = false
  }
}

const quickView = () => {
  emit('quickView', props.product)
}

const handleAddToCartSuccess = () => {
  emit('addToCartSuccess', props.product)
}

const handleAddToCartError = (error: any) => {
  emit('addToCartError', error)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-card {
  will-change: transform;
}

/* Enhanced sale badge animation */
.sale-badge {
  animation: gentle-pulse 3s ease-in-out infinite;
}

@keyframes gentle-pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
}

/* Enhanced wishlist button states */
.product-card button:has([name="mdi:heart"]) {
  animation: heart-beat 0.3s ease-in-out;
}

@keyframes heart-beat {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Hover enhancements */
.product-card:hover .pricing-container {
  background: linear-gradient(135deg, rgb(240 253 244), rgb(236 253 245));
}

.dark .product-card:hover .pricing-container {
  background: linear-gradient(135deg, rgb(20 83 45 / 0.3), rgb(6 78 59 / 0.3));
}

/* Enhanced focus states */
.product-card:focus-within {
  outline: 2px solid rgb(59 130 246);
  outline-offset: 2px;
}

/* Smooth transitions for all interactive elements */
.product-card * {
  transition-property: color, background-color, border-color, transform, shadow, opacity;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .sale-badge {
    animation: none;
  }

  .product-card button:has([name="mdi:heart"]) {
    animation: none;
  }

  .product-card * {
    transition-duration: 0ms !important;
  }
}

/* Enhanced mobile responsiveness */
@media (max-width: 640px) {
  .product-info {
    padding: 0.5rem;
  }

  .product-actions {
    padding: 0.5rem;
    padding-top: 0;
  }
}
</style>
