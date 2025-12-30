<script setup lang="ts">
/**
 * Product Detail Page - Flipkart/Amazon Style
 * Displays product with gallery, variants, reviews, wishlist, sales
 */

definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const route = useRoute()
const slug = route.params.slug as string
const toast = useToast()

// Auth-aware user type check
const { isLoggedIn } = useSanctum()
const { isMember, isPromoter } = useUserType()

// Only member and promoter can see Affiliate benefits (BV/PV)
const canSeeAffiliateBenefits = computed(() => isMember.value || isPromoter.value)

// Types
interface ProductData {
  name: string
  slug: string
  sku: string
  description: string | null
  short_description: string | null
  price: number
  price_formatted: string
  original_price: number | null
  original_price_formatted: string | null
  discount_percent: number | null
  sale_name: string | null
  sale_ends_at: string | null
  category: { name: string; slug: string } | null
  gallery: Array<{ id: number; url: string; thumbnail: string }>
  in_stock: boolean
  stock_quantity: number
  view_count: number
  is_returnable: boolean
  return_days: number
  bv: number
  pv: number
  reward_points: number
  has_variants: boolean
  variants: Array<{
    name: string
    slug: string
    sku: string
    price: number
    price_formatted: string
    original_price: number | null
    original_price_formatted: string | null
    discount_percent: number | null
    image: { url: string; thumbnail: string } | null
    in_stock: boolean
    filter_options: Array<{ filter: string; value: string }>
  }>
  filter_options: Array<{
    filter_name: string
    options: Array<{ value: string }>
  }>
}

interface ReviewData {
  stats: {
    total_reviews: number
    average_rating: number
    distribution: Record<number, number>
  }
  reviews: Array<{
    id: number
    rating: number
    review: string | null
    helpful_votes: number
    created_at: string
    author: { name: string; avatar: string | null }
  }>
}

// Fetch product details
const { data: productResponse, status, error } = await useFetch<{
  success: boolean
  data: ProductData
}>(`${config.public.apiBase}/api/catalog/products/${slug}`, {
  server: false
})

const product = computed(() => productResponse.value?.data)

// Fetch reviews
const { data: reviewsResponse, refresh: refreshReviews } = await useFetch<{
  success: boolean
  data: ReviewData
}>(`${config.public.apiBase}/api/products/${slug}/reviews`, {
  server: false,
  lazy: true
})

const reviews = computed(() => reviewsResponse.value?.data)

// SEO
useSeoMeta({
  title: () => product.value ? `${product.value.name} - Mintreu Shop` : 'Product - Mintreu Shop',
  description: () => product.value?.short_description || product.value?.description?.slice(0, 160) || 'Shop premium products at Mintreu'
})

// Gallery state
const selectedImage = ref(0)
const selectedVariant = ref<string | null>(null)

// Quantity
const quantity = ref(1)
const maxQuantity = computed(() => Math.min(product.value?.stock_quantity || 10, 10))

const incrementQuantity = () => {
  if (quantity.value < maxQuantity.value) quantity.value++
}

const decrementQuantity = () => {
  if (quantity.value > 1) quantity.value--
}

// Current selected variant
const currentVariant = computed(() => {
  if (!selectedVariant.value || !product.value?.variants) return null
  return product.value.variants.find(v => v.slug === selectedVariant.value)
})

// Current display values (may change with variant)
const currentPrice = computed(() => currentVariant.value?.price_formatted || product.value?.price_formatted || '₹0')
const currentOriginalPrice = computed(() => currentVariant.value?.original_price_formatted || product.value?.original_price_formatted)
const currentDiscount = computed(() => currentVariant.value?.discount_percent || product.value?.discount_percent)
const currentInStock = computed(() => currentVariant.value?.in_stock ?? product.value?.in_stock ?? false)

// Wishlist state
const isInWishlist = ref(false)
const wishlistLoading = ref(false)

// Check wishlist status
const checkWishlist = async () => {
  try {
    const response = await $fetch<{ success: boolean; data: { in_wishlist: boolean } }>(
      `${config.public.apiBase}/api/wishlist/${slug}/check`
    )
    isInWishlist.value = response.data?.in_wishlist || false
  } catch {
    isInWishlist.value = false
  }
}

// Toggle wishlist
const toggleWishlist = async () => {
  wishlistLoading.value = true
  try {
    const response = await useSanctumFetch<{ success: boolean; data: { in_wishlist: boolean }; message: string }>(
      `${config.public.apiBase}/api/wishlist/${slug}/toggle`,
      { method: 'POST' }
    )
    isInWishlist.value = response.data?.in_wishlist || false
    toast.add({
      title: isInWishlist.value ? 'Added to Wishlist' : 'Removed from Wishlist',
      color: 'success',
      icon: isInWishlist.value ? 'i-lucide-heart' : 'i-lucide-heart-off'
    })
  } catch {
    toast.add({
      title: 'Login Required',
      description: 'Please login to add items to your wishlist',
      color: 'warning',
      icon: 'i-lucide-log-in'
    })
  } finally {
    wishlistLoading.value = false
  }
}

// Cart functionality
const addingToCart = ref(false)

// Use cart composable for add to cart
const { addToCart: addToCartComposable } = useCart()

const addToCart = async () => {
  if (!product.value || !currentInStock.value) return
  addingToCart.value = true
  try {
    const productSlug = currentVariant.value?.slug || product.value.slug
    await addToCartComposable(productSlug, quantity.value, {
      productName: product.value.name,
      productImage: product.value.gallery[0]?.url
    })
  } catch (err: unknown) {
    const errorMessage = err instanceof Error ? err.message : 'Failed to add to cart'
    toast.add({ title: 'Error', description: errorMessage, color: 'error', icon: 'i-lucide-alert-circle' })
  } finally {
    addingToCart.value = false
  }
}

// Select variant when clicking filter option
const selectVariantByOption = (filterName: string, optionValue: string) => {
  if (!product.value?.variants) return
  const variant = product.value.variants.find(v =>
    v.filter_options.some(fo => fo.filter === filterName && fo.value === optionValue)
  )
  if (variant) {
    selectedVariant.value = variant.slug
    // Update gallery if variant has image
    if (variant.image) {
      const imageIndex = product.value.gallery.findIndex(g => g.url === variant.image?.url)
      if (imageIndex >= 0) selectedImage.value = imageIndex
    }
  }
}

// Format date helper
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-IN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Star rating display
const getStarArray = (rating: number) => {
  return Array.from({ length: 5 }, (_, i) => i < rating ? 'full' : 'empty')
}

// Initialize
onMounted(() => {
  checkWishlist()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Loading State -->
    <div v-if="status === 'pending'" class="py-8">
      <UContainer>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="aspect-square bg-slate-200 dark:bg-slate-800 rounded-2xl animate-pulse" />
          <div class="space-y-4">
            <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded w-3/4 animate-pulse" />
            <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded w-1/4 animate-pulse" />
            <div class="h-24 bg-slate-200 dark:bg-slate-800 rounded animate-pulse" />
            <div class="h-12 bg-slate-200 dark:bg-slate-800 rounded w-1/2 animate-pulse" />
          </div>
        </div>
      </UContainer>
    </div>

    <!-- Error State -->
    <div v-else-if="error || !product" class="py-16">
      <UContainer>
        <div class="text-center">
          <UIcon name="i-lucide-package-x" class="w-20 h-20 mx-auto text-slate-400 mb-6" />
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Product Not Found</h1>
          <p class="text-slate-500 dark:text-slate-400 mb-6">The product you're looking for doesn't exist or has been removed.</p>
          <NuxtLink to="/shop">
            <UButton><UIcon name="i-lucide-arrow-left" class="w-4 h-4 mr-2" />Back to Shop</UButton>
          </NuxtLink>
        </div>
      </UContainer>
    </div>

    <!-- Product Details -->
    <div v-else class="py-8">
      <UContainer>
        <!-- Breadcrumb -->
        <nav class="mb-6">
          <ul class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
            <li><NuxtLink to="/" class="hover:text-primary-500">Home</NuxtLink></li>
            <li><UIcon name="i-lucide-chevron-right" class="w-4 h-4" /></li>
            <li><NuxtLink to="/shop" class="hover:text-primary-500">Shop</NuxtLink></li>
            <template v-if="product.category">
              <li><UIcon name="i-lucide-chevron-right" class="w-4 h-4" /></li>
              <li><NuxtLink :to="`/shop?category=${product.category.slug}`" class="hover:text-primary-500">{{ product.category.name }}</NuxtLink></li>
            </template>
            <li><UIcon name="i-lucide-chevron-right" class="w-4 h-4" /></li>
            <li class="text-slate-900 dark:text-white font-medium truncate max-w-[200px]">{{ product.name }}</li>
          </ul>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <!-- Gallery -->
          <div class="space-y-4">
            <!-- Main Image -->
            <div class="relative aspect-square bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
              <!-- Sale Badge -->
              <div v-if="currentDiscount" class="absolute top-4 left-4 z-10">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg">
                  <UIcon name="i-lucide-flame" class="w-4 h-4 mr-1" />
                  {{ currentDiscount }}% OFF
                </span>
              </div>

              <!-- Wishlist Button -->
              <button
                class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/90 dark:bg-slate-800/90 backdrop-blur flex items-center justify-center shadow-lg transition-all hover:scale-110"
                :disabled="wishlistLoading"
                @click="toggleWishlist"
              >
                <UIcon
                  :name="isInWishlist ? 'i-lucide-heart' : 'i-lucide-heart'"
                  :class="['w-5 h-5 transition-colors', isInWishlist ? 'text-red-500 fill-red-500' : 'text-slate-400']"
                />
              </button>

              <img
                v-if="product.gallery.length > 0"
                :src="product.gallery[selectedImage]?.url"
                :alt="product.name"
                class="w-full h-full object-contain"
              >
              <div v-else class="w-full h-full flex items-center justify-center">
                <UIcon name="i-lucide-package" class="w-32 h-32 text-slate-300 dark:text-slate-600" />
              </div>
            </div>

            <!-- Thumbnails -->
            <div v-if="product.gallery.length > 1" class="flex gap-2 overflow-x-auto pb-2">
              <button
                v-for="(image, index) in product.gallery"
                :key="image.id"
                :class="[
                  'w-20 h-20 rounded-lg overflow-hidden border-2 transition-all shrink-0',
                  selectedImage === index
                    ? 'border-primary-500 ring-2 ring-primary-500/30'
                    : 'border-slate-200 dark:border-slate-700 hover:border-primary-300'
                ]"
                @click="selectedImage = index"
              >
                <img :src="image.thumbnail" :alt="`${product.name} - Image ${index + 1}`" class="w-full h-full object-cover">
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="space-y-5">
            <!-- Category -->
            <div v-if="product.category">
              <NuxtLink :to="`/shop?category=${product.category.slug}`" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-100">
                {{ product.category.name }}
              </NuxtLink>
            </div>

            <!-- Title & Rating -->
            <div>
              <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-2">{{ product.name }}</h1>

              <!-- Rating Summary -->
              <div v-if="reviews?.stats.total_reviews" class="flex items-center gap-3">
                <div class="flex items-center gap-1">
                  <template v-for="star in getStarArray(Math.round(reviews.stats.average_rating))" :key="star">
                    <UIcon name="i-lucide-star" :class="['w-4 h-4', star === 'full' ? 'text-amber-400 fill-amber-400' : 'text-slate-300']" />
                  </template>
                </div>
                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
                  {{ reviews.stats.average_rating }} ({{ reviews.stats.total_reviews }} reviews)
                </span>
              </div>

              <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">SKU: {{ product.sku }}</p>
            </div>

            <!-- Price Section -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
              <div class="flex items-baseline gap-3 flex-wrap">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ currentPrice }}</span>
                <span v-if="currentOriginalPrice" class="text-lg text-slate-400 line-through">{{ currentOriginalPrice }}</span>
                <span v-if="currentDiscount" class="px-2 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-bold">
                  Save {{ currentDiscount }}%
                </span>
              </div>
              <p v-if="product.sale_name" class="text-sm text-red-500 mt-1 flex items-center gap-1">
                <UIcon name="i-lucide-tag" class="w-4 h-4" />
                {{ product.sale_name }}
              </p>
            </div>

            <!-- Affiliate Benefits - Only visible to Member/Promoter -->
            <div v-if="canSeeAffiliateBenefits && (product.bv > 0 || product.pv > 0 || product.reward_points > 0)" class="flex flex-wrap gap-2">
              <span v-if="product.bv > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                <UIcon name="i-lucide-trending-up" class="w-4 h-4 mr-1" />{{ product.bv }} BV
              </span>
              <span v-if="product.pv > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                <UIcon name="i-lucide-star" class="w-4 h-4 mr-1" />{{ product.pv }} PV
              </span>
              <span v-if="product.reward_points > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                <UIcon name="i-lucide-gift" class="w-4 h-4 mr-1" />{{ product.reward_points }} Points
              </span>
            </div>

            <!-- Guest reward teaser -->
            <div v-else-if="!isLoggedIn && product.reward_points > 0" class="flex items-center gap-2 text-sm text-purple-600 dark:text-purple-400 font-medium bg-purple-50 dark:bg-purple-900/20 px-3 py-2 rounded-lg">
              <UIcon name="i-lucide-gift" class="w-4 h-4" />
              Sign in to earn rewards on this purchase
            </div>

            <!-- Short Description -->
            <p v-if="product.short_description" class="text-slate-600 dark:text-slate-300">{{ product.short_description }}</p>

            <!-- Variants / Options (Flipkart Style) -->
            <div v-if="product.has_variants && product.filter_options.length" class="space-y-4">
              <div v-for="filterGroup in product.filter_options" :key="filterGroup.filter_name">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  <UIcon name="i-lucide-palette" class="w-4 h-4 inline mr-1" />
                  {{ filterGroup.filter_name }}
                </label>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="option in filterGroup.options"
                    :key="option.value"
                    :class="[
                      'px-4 py-2 rounded-lg border text-sm font-medium transition-all',
                      currentVariant?.filter_options.some(fo => fo.filter === filterGroup.filter_name && fo.value === option.value)
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-2 ring-primary-500/30'
                        : 'border-slate-200 dark:border-slate-700 hover:border-primary-300 text-slate-700 dark:text-slate-300'
                    ]"
                    @click="selectVariantByOption(filterGroup.filter_name, option.value)"
                  >
                    {{ option.value }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Variant Cards (Flipkart Style - When variants have images) -->
            <div v-if="product.has_variants && product.variants.length > 1" class="space-y-3">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                <UIcon name="i-lucide-layers" class="w-4 h-4 inline mr-1" />
                Available Variants ({{ product.variants.length }})
              </label>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <button
                  v-for="variant in product.variants"
                  :key="variant.sku"
                  :class="[
                    'p-3 rounded-xl border-2 transition-all text-left',
                    selectedVariant === variant.slug
                      ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/20 shadow-lg'
                      : 'border-slate-200 dark:border-slate-700 hover:border-primary-300 hover:shadow-md'
                  ]"
                  @click="selectedVariant = variant.slug"
                >
                  <!-- Variant Image -->
                  <div class="aspect-square w-full mb-2 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800">
                    <img
                      v-if="variant.image"
                      :src="variant.image.thumbnail || variant.image.url"
                      :alt="variant.name"
                      class="w-full h-full object-cover"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <UIcon name="i-lucide-package" class="w-8 h-8 text-slate-300 dark:text-slate-600" />
                    </div>
                  </div>

                  <!-- Variant Name -->
                  <p class="text-xs font-medium text-slate-700 dark:text-slate-300 line-clamp-2 mb-1">
                    {{ variant.name }}
                  </p>

                  <!-- Variant Price -->
                  <div class="flex items-baseline gap-1.5">
                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ variant.price_formatted }}</span>
                    <span v-if="variant.original_price_formatted" class="text-xs text-slate-400 line-through">{{ variant.original_price_formatted }}</span>
                  </div>

                  <!-- Stock Status -->
                  <span :class="['text-xs mt-1 block', variant.in_stock ? 'text-emerald-600' : 'text-red-500']">
                    {{ variant.in_stock ? 'In Stock' : 'Out of Stock' }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Quantity & Actions -->
            <div class="flex items-center gap-4 flex-wrap">
              <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Quantity</label>
                <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg">
                  <button class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" :disabled="quantity <= 1" @click="decrementQuantity">
                    <UIcon name="i-lucide-minus" class="w-4 h-4" />
                  </button>
                  <span class="w-12 text-center font-medium">{{ quantity }}</span>
                  <button class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" :disabled="quantity >= maxQuantity" @click="incrementQuantity">
                    <UIcon name="i-lucide-plus" class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <span :class="['text-sm mt-6', currentInStock ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400']">
                <UIcon :name="currentInStock ? 'i-lucide-check-circle' : 'i-lucide-x-circle'" class="w-4 h-4 inline mr-1" />
                {{ currentInStock ? `In Stock (${product.stock_quantity} available)` : 'Out of Stock' }}
              </span>
            </div>

            <!-- Add to Cart Button -->
            <div class="flex gap-3">
              <UButton size="lg" class="flex-1" :disabled="!currentInStock || addingToCart" :loading="addingToCart" @click="addToCart">
                <UIcon name="i-lucide-shopping-cart" class="w-5 h-5 mr-2" />
                {{ currentInStock ? 'Add to Cart' : 'Out of Stock' }}
              </UButton>
              <UButton size="lg" variant="outline" :disabled="!currentInStock">
                <UIcon name="i-lucide-zap" class="w-5 h-5 mr-2" />
                Buy Now
              </UButton>
            </div>

            <!-- Return Policy & Trust Badges -->
            <div class="flex flex-wrap gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
              <div v-if="product.is_returnable" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon name="i-lucide-undo-2" class="w-5 h-5 text-emerald-500" />
                <span>{{ product.return_days }}-day returns</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon name="i-lucide-shield-check" class="w-5 h-5 text-emerald-500" />
                <span>Quality Assured</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon name="i-lucide-truck" class="w-5 h-5 text-blue-500" />
                <span>Fast Delivery</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Description -->
        <div v-if="product.description" class="mt-12">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Product Description</h2>
            <div class="prose prose-slate dark:prose-invert max-w-none" v-html="product.description" />
          </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-12">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-bold text-slate-900 dark:text-white">Customer Reviews</h2>
              <span v-if="reviews?.stats.total_reviews" class="text-sm text-slate-500">{{ reviews.stats.total_reviews }} reviews</span>
            </div>

            <!-- Rating Summary -->
            <div v-if="reviews?.stats.total_reviews" class="flex flex-col md:flex-row gap-8 mb-8 pb-8 border-b border-slate-200 dark:border-slate-700">
              <!-- Average Rating -->
              <div class="text-center">
                <div class="text-5xl font-black text-slate-900 dark:text-white">{{ reviews.stats.average_rating }}</div>
                <div class="flex items-center justify-center gap-1 my-2">
                  <template v-for="star in getStarArray(Math.round(reviews.stats.average_rating))" :key="star">
                    <UIcon name="i-lucide-star" :class="['w-5 h-5', star === 'full' ? 'text-amber-400 fill-amber-400' : 'text-slate-300']" />
                  </template>
                </div>
                <div class="text-sm text-slate-500">{{ reviews.stats.total_reviews }} reviews</div>
              </div>

              <!-- Rating Distribution -->
              <div class="flex-1 space-y-2">
                <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-3">
                  <span class="text-sm w-8 text-slate-600 dark:text-slate-400">{{ star }} ★</span>
                  <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-amber-400 rounded-full transition-all"
                      :style="{ width: `${(reviews.stats.distribution[star] / reviews.stats.total_reviews) * 100}%` }"
                    />
                  </div>
                  <span class="text-sm w-8 text-slate-500">{{ reviews.stats.distribution[star] }}</span>
                </div>
              </div>
            </div>

            <!-- Reviews List -->
            <div v-if="reviews?.reviews.length" class="space-y-6">
              <div v-for="review in reviews.reviews" :key="review.id" class="pb-6 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-violet-500 flex items-center justify-center text-white font-bold">
                    {{ review.author.name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="font-medium text-slate-900 dark:text-white">{{ review.author.name }}</span>
                      <span class="text-xs text-slate-400">{{ formatDate(review.created_at) }}</span>
                    </div>
                    <div class="flex items-center gap-1 mb-2">
                      <template v-for="star in getStarArray(review.rating)" :key="star">
                        <UIcon name="i-lucide-star" :class="['w-4 h-4', star === 'full' ? 'text-amber-400 fill-amber-400' : 'text-slate-300']" />
                      </template>
                    </div>
                    <p v-if="review.review" class="text-slate-600 dark:text-slate-300">{{ review.review }}</p>
                    <div v-if="review.helpful_votes" class="mt-2 text-xs text-slate-400">
                      <UIcon name="i-lucide-thumbs-up" class="w-3 h-3 inline mr-1" />
                      {{ review.helpful_votes }} found this helpful
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- No Reviews -->
            <div v-else class="text-center py-8">
              <UIcon name="i-lucide-message-square" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4" />
              <p class="text-slate-500 dark:text-slate-400">No reviews yet. Be the first to review this product!</p>
            </div>
          </div>
        </div>
      </UContainer>
    </div>
  </div>
</template>
