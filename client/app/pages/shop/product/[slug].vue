<script setup lang="ts">
import type { Product as CatalogProduct } from '~/types/catalog'
import FilamentContent from '~/components/FilamentContent.vue'
/**
 * Product Detail Page - Flipkart/Amazon Style
 * Displays product with gallery, variants, reviews, wishlist, sales
 */

definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const route = useRoute()
const slug = computed(() => (route.params.slug ? String(route.params.slug) : ''))
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
  category: { name: string, slug: string } | null
  gallery: Array<{ id: number, src: string, srcset: string, thumbnail: string }>
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
    image: { url: string, thumbnail: string } | null
    in_stock: boolean
    filter_options: Array<{ filter: string, value: string }>
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
    author: { name: string, avatar: string | null }
  }>
}

interface RelatedProductsResponse {
  success: boolean
  data: CatalogProduct[]
}

const normalizedSiteUrl = computed(() => String(config.public.siteUrl || '').replace(/\/$/, ''))
const productPublicUrl = computed(() => `${normalizedSiteUrl.value}/shop/product/${slug.value}`)
const ssrProductKey = computed(() => `product-detail:${slug.value}`)

const { data: ssrProductResponse, error: ssrProductError } = await useAsyncData(
  () => ssrProductKey.value,
  () => useSanctumFetch<{ success: boolean, data: ProductData }>(`${config.public.apiBase}/api/catalog/products/${slug.value}`),
  { watch: [slug] }
)

const productResponse = ref<{ success: boolean, data: ProductData } | null>(ssrProductResponse.value ?? null)
const productStatus = ref<'pending' | 'success' | 'error'>(productResponse.value ? 'success' : (ssrProductError.value ? 'error' : 'pending'))
const productError = ref<unknown>(ssrProductError.value ?? null)
const product = computed(() => productResponse.value?.data)

const reviewsResponse = ref<{ success: boolean, data: ReviewData } | null>(null)
const reviews = computed(() => reviewsResponse.value?.data)

const relatedResponse = ref<RelatedProductsResponse | null>(null)
const relatedStatus = ref<'pending' | 'success' | 'error'>('pending')

const buildQueryString = (params: Record<string, any>) => {
  const query = new URLSearchParams()
  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === '') continue
    query.set(key, String(value))
  }
  const queryString = query.toString()
  return queryString ? `?${queryString}` : ''
}

const loadProduct = async () => {
  if (!slug.value) return
  productStatus.value = 'pending'
  productError.value = null
  try {
    productResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/catalog/products/${slug.value}`
    )
    productStatus.value = 'success'
  } catch (err) {
    productError.value = err
    productStatus.value = 'error'
  }
}

const loadReviews = async () => {
  if (!slug.value) return
  try {
    reviewsResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/products/${slug.value}/reviews`
    )
  } catch {
    reviewsResponse.value = null
  }
}

const relatedQuery = computed(() => {
  const params: Record<string, string | number> = {
    per_page: 12,
    sort: 'popularity'
  }
  if (product.value?.category?.slug) {
    params.category = product.value.category.slug
  }
  return params
})

const loadRelated = async () => {
  relatedStatus.value = 'pending'
  try {
    const queryString = buildQueryString(relatedQuery.value)
    relatedResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/catalog/products${queryString}`
    )
    relatedStatus.value = 'success'
  } catch {
    relatedStatus.value = 'error'
  }
}

const relatedProducts = computed(() => {
  const items = relatedResponse.value?.data ?? []
  const currentSlug = product.value?.slug
  const filtered = currentSlug ? items.filter(item => item.slug !== currentSlug) : items
  return filtered.slice(0, 12)
})

const relatedLoading = computed(() => relatedStatus.value === 'pending')

watchEffect(() => {
  if (!product.value) {
    useComprehensiveSeo({
      title: 'Product',
      description: 'Shop premium products online.',
      url: productPublicUrl.value,
      type: 'website'
    })
    return
  }

  const seoDescription = product.value.short_description
    || product.value.description?.slice(0, 160)
    || `Buy ${product.value.name} online at ${config.public.companyName || 'VVIndia'}.`
  const seoImage = product.value.gallery?.[0]?.src || product.value.gallery?.[0]?.thumbnail || undefined

  useComprehensiveSeo({
    title: product.value.name,
    description: seoDescription,
    image: seoImage,
    imageAlt: product.value.name,
    url: productPublicUrl.value,
    type: 'product',
    product: {
      price: product.value.price,
      currency: config.public.currencyCode || 'INR',
      availability: product.value.in_stock ? 'in stock' : 'out of stock',
      brand: config.public.companyName || 'VVIndia',
      condition: 'new'
    }
  })
})

// Structured Data for Rich Snippets
useHead(() => {
  if (!product.value) {
    return {}
  }
  const availability = product.value.in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
  const mainImage = product.value.gallery?.[0]?.src || ''

  return {
    script: [
      {
        type: 'application/ld+json',
        children: JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'Product',
          'name': product.value.name,
          'description': product.value.short_description || product.value.description,
          'image': mainImage,
          'sku': product.value.sku,
          'brand': {
            '@type': 'Brand',
            'name': config.public.companyName || 'VVIndia'
          },
          'offers': {
            '@type': 'Offer',
            'url': productPublicUrl.value,
            'priceCurrency': config.public.currencyCode || 'INR',
            'price': product.value.price,
            availability,
            'itemCondition': 'https://schema.org/NewCondition'
          },
          ...(reviews.value?.stats?.total_reviews && {
            aggregateRating: {
              '@type': 'AggregateRating',
              'ratingValue': reviews.value.stats.average_rating.toFixed(1),
              'reviewCount': reviews.value.stats.total_reviews
            }
          })
        }, null, 2)
      }
    ]
  }
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

const highlights = computed(() => {
  const filters = product.value?.filter_options ?? []
  const items = filters.flatMap(group =>
    group.options.map(option => `${group.filter_name}: ${option.value}`)
  )
  return items.slice(0, 6)
})

const specRows = computed(() => {
  const rows: Array<{ label: string, value: string }> = []
  if (product.value?.sku) rows.push({ label: 'SKU', value: product.value.sku })
  if (product.value?.category?.name) rows.push({ label: 'Category', value: product.value.category.name })
  if (typeof product.value?.view_count === 'number') {
    rows.push({ label: 'Views', value: `${product.value.view_count}` })
  }
  if (typeof product.value?.stock_quantity === 'number') {
    rows.push({
      label: 'Stock',
      value: product.value.stock_quantity > 0 ? `${product.value.stock_quantity} available` : 'Out of stock'
    })
  }
  if (product.value?.is_returnable) {
    rows.push({ label: 'Return Policy', value: `${product.value.return_days}-day returns` })
  }
  const filters = product.value?.filter_options ?? []
  for (const group of filters) {
    const values = group.options.map(option => option.value).join(', ')
    if (values) rows.push({ label: group.filter_name, value: values })
  }
  return rows
})

const totalHelpfulVotes = computed(() => {
  const list = reviews.value?.reviews ?? []
  return list.reduce((sum, review) => sum + (review.helpful_votes || 0), 0)
})

// Wishlist state
const isInWishlist = ref(false)
const wishlistLoading = ref(false)

// Check wishlist status
const checkWishlist = async () => {
  if (!slug.value) return
  try {
    const response = await $fetch<{ success: boolean, data: { in_wishlist: boolean } }>(
      `${config.public.apiBase}/api/wishlist/${slug.value}/check`
    )
    isInWishlist.value = response.data?.in_wishlist || false
  } catch {
    isInWishlist.value = false
  }
}

// Toggle wishlist
const toggleWishlist = async () => {
  if (!slug.value) return
  wishlistLoading.value = true
  try {
    const response = await useSanctumFetch<{ success: boolean, data: { in_wishlist: boolean }, message: string }>(
      `${config.public.apiBase}/api/wishlist/${slug.value}/toggle`,
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
const buyingNow = ref(false)

// Use cart composable for add to cart
const { addToCart: addToCartComposable } = useCart()

const addToCart = async () => {
  if (!product.value || !currentInStock.value) return
  addingToCart.value = true
  try {
    const productSlug = currentVariant.value?.slug || product.value.slug
    await addToCartComposable(productSlug, quantity.value, {
      productName: product.value.name,
      productImage: product.value.gallery[0]?.src
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
      const imageIndex = product.value.gallery.findIndex(g => g.src === variant.image?.src)
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
  return Array.from({ length: 5 }, (_, i) => ({
    id: `${rating}-${i}`,
    filled: i < rating
  }))
}

const buyNow = async () => {
  if (!product.value || !currentInStock.value) return
  buyingNow.value = true
  try {
    const productSlug = currentVariant.value?.slug || product.value.slug
    const ok = await addToCartComposable(productSlug, quantity.value, {
      productName: product.value.name,
      productImage: product.value.gallery[0]?.src,
      skipAnimation: true
    })
    if (ok) {
      await navigateTo('/cart')
    }
  } catch (err: unknown) {
    const errorMessage = err instanceof Error ? err.message : 'Failed to add to cart'
    toast.add({ title: 'Error', description: errorMessage, color: 'error', icon: 'i-lucide-alert-circle' })
  } finally {
    buyingNow.value = false
  }
}

const reviewRating = ref(5)
const reviewText = ref('')
const reviewSubmitting = ref(false)
const helpingReviewId = ref<number | null>(null)

const hoverRating = ref<number | null>(null)

const getStarButtonClass = (star: number) => {
  const active = (hoverRating.value ?? reviewRating.value) >= star
  return [
    'review-star-btn flex items-center justify-center',
    active
      ? 'border-amber-400 bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300'
      : 'border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:border-amber-200 dark:hover:border-amber-500'
  ]
}

const submitReview = async () => {
  if (!product.value) return
  reviewSubmitting.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/api/products/${slug.value}/reviews`, {
      method: 'POST',
      body: {
        rating: reviewRating.value,
        review: reviewText.value || null
      }
    })
    toast.add({
      title: 'Review submitted',
      description: 'Thank you for sharing your feedback.',
      color: 'success'
    })
    reviewText.value = ''
    reviewRating.value = 5
    await loadReviews()
  } catch (err: unknown) {
    const message = (err as any)?.data?.message || 'Unable to submit review'
    toast.add({
      title: 'Review failed',
      description: message,
      color: 'error'
    })
  } finally {
    reviewSubmitting.value = false
  }
}

const markReviewHelpful = async (reviewId: number) => {
  if (!isLoggedIn.value) {
    toast.add({
      title: 'Login required',
      description: 'Please login to mark a review as helpful.',
      color: 'warning'
    })
    return
  }
  helpingReviewId.value = reviewId
  try {
    const response = await useSanctumFetch<{ success: boolean, data: { helpful_votes: number } }>(
      `${config.public.apiBase}/api/reviews/${reviewId}/helpful`,
      { method: 'POST' }
    )
    if (response.success) {
      const review = reviews.value?.reviews?.find(r => r.id === reviewId)
      if (review) {
        review.helpful_votes = response.data.helpful_votes
      }
      toast.add({ title: 'Marked helpful', color: 'success' })
    }
  } catch (err: unknown) {
    const message = (err as any)?.data?.message || 'Unable to mark helpful'
    toast.add({ title: 'Action failed', description: message, color: 'error' })
  } finally {
    helpingReviewId.value = null
  }
}

watch(slug, () => {
  loadProduct()
  loadReviews()
})

watch(relatedQuery, () => {
  loadRelated()
})

// Initialize
onMounted(() => {
  loadProduct()
  loadReviews()
  loadRelated()
  checkWishlist()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-24 lg:pb-0">
    <!-- Loading State -->
    <div
      v-if="productStatus === 'pending'"
      class="py-8"
    >
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
    <div
      v-else-if="productError || !product"
      class="py-16"
    >
      <UContainer>
        <div class="text-center">
          <UIcon
            name="i-lucide-package-x"
            class="w-20 h-20 mx-auto text-slate-400 mb-6"
          />
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
            Product Not Found
          </h1>
          <p class="text-slate-500 dark:text-slate-400 mb-6">
            The product you're looking for doesn't exist or has been removed.
          </p>
          <NuxtLink to="/shop">
            <UButton><UIcon
              name="i-lucide-arrow-left"
              class="w-4 h-4 mr-2"
            />Back to Shop</UButton>
          </NuxtLink>
        </div>
      </UContainer>
    </div>

    <!-- Product Details -->
    <div
      v-else
      class="py-8"
    >
      <UContainer>
        <!-- Breadcrumb -->
        <nav class="mb-6">
          <ul class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
            <li>
              <NuxtLink
                to="/"
                class="hover:text-primary-500"
              >Home</NuxtLink>
            </li>
            <li>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-4 h-4"
              />
            </li>
            <li>
              <NuxtLink
                to="/shop"
                class="hover:text-primary-500"
              >Shop</NuxtLink>
            </li>
            <template v-if="product.category">
              <li>
                <UIcon
                  name="i-lucide-chevron-right"
                  class="w-4 h-4"
                />
              </li>
              <li>
                <NuxtLink
                  :to="`/shop/products?category=${product.category.slug}`"
                  class="hover:text-primary-500"
                >{{ product.category.name }}</NuxtLink>
              </li>
            </template>
            <li>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-4 h-4"
              />
            </li>
            <li class="text-slate-900 dark:text-white font-medium truncate max-w-[200px]">
              {{ product.name }}
            </li>
          </ul>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <!-- Gallery -->
          <div class="space-y-4">
            <!-- Main Image -->
            <div class="relative aspect-square bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
              <!-- Sale Badge -->
              <div
                v-if="currentDiscount"
                class="absolute top-4 left-4 z-10"
              >
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg">
                  <UIcon
                    name="i-lucide-flame"
                    class="w-4 h-4 mr-1"
                  />
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
                :src="product.gallery[selectedImage]?.src"
                :alt="product.name"
                class="w-full h-full object-contain"
              >
              <div
                v-else
                class="w-full h-full flex items-center justify-center"
              >
                <UIcon
                  name="i-lucide-package"
                  class="w-32 h-32 text-slate-300 dark:text-slate-600"
                />
              </div>
            </div>

            <!-- Thumbnails -->
            <div
              v-if="product.gallery.length > 1"
              class="flex gap-2 overflow-x-auto pb-2"
            >
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
                <img
                  :src="image.src"
                  :alt="`${product.name} - Image ${index + 1}`"
                  class="w-full h-full object-cover"
                >
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="space-y-5">
            <!-- Category -->
            <div v-if="product.category">
              <NuxtLink
                :to="`/shop/products?category=${product.category.slug}`"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-100"
              >
                {{ product.category.name }}
              </NuxtLink>
            </div>

            <!-- Title & Rating -->
            <div>
              <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-2">
                {{ product.name }}
              </h1>

              <!-- Rating Summary -->
              <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1">
                  <template
                    v-for="star in getStarArray(Math.round(reviews?.stats.average_rating || 0))"
                    :key="star.id"
                  >
                    <UIcon
                      name="i-lucide-star"
                      :class="['w-4 h-4', star.filled ? 'text-amber-400 fill-amber-400' : 'text-slate-300']"
                    />
                  </template>
                </div>
                <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                  <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                    {{ reviews?.stats.average_rating || 0 }}/5
                  </span>
                  <span>{{ reviews?.stats.total_reviews || 0 }} ratings</span>
                  <span class="text-slate-400">•</span>
                  <span>{{ totalHelpfulVotes }} helpful votes</span>
                </span>
              </div>

              <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                SKU: {{ product.sku }}
              </p>
            </div>

            <!-- Price Section -->
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
              <div class="flex items-baseline gap-3 flex-wrap">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ currentPrice }}</span>
                <span
                  v-if="currentOriginalPrice"
                  class="text-lg text-slate-400 line-through"
                >{{ currentOriginalPrice }}</span>
                <span
                  v-if="currentDiscount"
                  class="px-2 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-bold"
                >
                  Save {{ currentDiscount }}%
                </span>
              </div>
              <p
                v-if="product.sale_name"
                class="text-sm text-red-500 mt-1 flex items-center gap-1"
              >
                <UIcon
                  name="i-lucide-tag"
                  class="w-4 h-4"
                />
                {{ product.sale_name }}
              </p>
            </div>

            <!-- Affiliate Benefits - Only visible to Member/Promoter -->
            <div
              v-if="canSeeAffiliateBenefits && (product.bv > 0 || product.pv > 0 || product.reward_points > 0)"
              class="flex flex-wrap gap-2"
            >
              <span
                v-if="product.bv > 0"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400"
              >
                <UIcon
                  name="i-lucide-trending-up"
                  class="w-4 h-4 mr-1"
                />{{ product.bv }} BV
              </span>
              <span
                v-if="product.pv > 0"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
              >
                <UIcon
                  name="i-lucide-star"
                  class="w-4 h-4 mr-1"
                />{{ product.pv }} PV
              </span>
              <span
                v-if="product.reward_points > 0"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400"
              >
                <UIcon
                  name="i-lucide-coins"
                  class="w-4 h-4 mr-1"
                />{{ product.reward_points }} Coins
              </span>
            </div>

            <!-- Short Description -->
            <p
              v-if="product.short_description"
              class="text-slate-600 dark:text-slate-300"
            >
              {{ product.short_description }}
            </p>

            <!-- Highlights -->
            <div
              v-if="highlights.length"
              class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4"
            >
              <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                <UIcon
                  name="i-lucide-sparkles"
                  class="w-4 h-4 text-amber-500"
                />
                Highlights
              </h3>
              <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-600 dark:text-slate-300">
                <li
                  v-for="item in highlights"
                  :key="item"
                  class="flex items-center gap-2"
                >
                  <UIcon
                    name="i-lucide-check"
                    class="w-4 h-4 text-emerald-500"
                  />
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>

            <!-- Variants / Options (Flipkart Style) -->
            <div
              v-if="product.has_variants && product.filter_options.length"
              class="space-y-4"
            >
              <div
                v-for="filterGroup in product.filter_options"
                :key="filterGroup.filter_name"
              >
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  <UIcon
                    name="i-lucide-palette"
                    class="w-4 h-4 inline mr-1"
                  />
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
            <div
              v-if="product.has_variants && product.variants.length > 1"
              class="space-y-3"
            >
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                <UIcon
                  name="i-lucide-layers"
                  class="w-4 h-4 inline mr-1"
                />
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
                      :src="variant.image.thumbnail || variant.image.src"
                      :alt="variant.name"
                      class="w-full h-full object-cover"
                    >
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center"
                    >
                      <UIcon
                        name="i-lucide-package"
                        class="w-8 h-8 text-slate-300 dark:text-slate-600"
                      />
                    </div>
                  </div>

                  <!-- Variant Name -->
                  <p class="text-xs font-medium text-slate-700 dark:text-slate-300 line-clamp-2 mb-1">
                    {{ variant.name }}
                  </p>

                  <!-- Variant Price -->
                  <div class="flex items-baseline gap-1.5">
                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ variant.price_formatted }}</span>
                    <span
                      v-if="variant.original_price_formatted"
                      class="text-xs text-slate-400 line-through"
                    >{{ variant.original_price_formatted }}</span>
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
                  <button
                    class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    :disabled="quantity <= 1"
                    @click="decrementQuantity"
                  >
                    <UIcon
                      name="i-lucide-minus"
                      class="w-4 h-4"
                    />
                  </button>
                  <span class="w-12 text-center font-medium">{{ quantity }}</span>
                  <button
                    class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    :disabled="quantity >= maxQuantity"
                    @click="incrementQuantity"
                  >
                    <UIcon
                      name="i-lucide-plus"
                      class="w-4 h-4"
                    />
                  </button>
                </div>
              </div>

              <span :class="['text-sm mt-6', currentInStock ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400']">
                <UIcon
                  :name="currentInStock ? 'i-lucide-check-circle' : 'i-lucide-x-circle'"
                  class="w-4 h-4 inline mr-1"
                />
                {{ currentInStock ? `In Stock (${product.stock_quantity} available)` : 'Out of Stock' }}
              </span>
            </div>

            <!-- Add to Cart Button -->
            <div class="flex gap-3">
              <UButton
                size="lg"
                class="flex-1"
                :disabled="!currentInStock || addingToCart"
                :loading="addingToCart"
                @click="addToCart"
              >
                <UIcon
                  name="i-lucide-shopping-cart"
                  class="w-5 h-5 mr-2"
                />
                {{ currentInStock ? 'Add to Cart' : 'Out of Stock' }}
              </UButton>
              <UButton
                size="lg"
                variant="outline"
                :disabled="!currentInStock || buyingNow"
                :loading="buyingNow"
                @click="buyNow"
              >
                <UIcon
                  name="i-lucide-zap"
                  class="w-5 h-5 mr-2"
                />
                Buy Now
              </UButton>
            </div>

            <!-- Return Policy & Trust Badges -->
            <div class="flex flex-wrap gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
              <div
                v-if="product.is_returnable"
                class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400"
              >
                <UIcon
                  name="i-lucide-undo-2"
                  class="w-5 h-5 text-emerald-500"
                />
                <span>{{ product.return_days }}-day returns</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon
                  name="i-lucide-shield-check"
                  class="w-5 h-5 text-emerald-500"
                />
                <span>Quality Assured</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon
                  name="i-lucide-truck"
                  class="w-5 h-5 text-blue-500"
                />
                <span>Fast Delivery</span>
              </div>
            </div>

            <!-- Delivery & Services -->
            <div class="bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4">
              <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                <UIcon
                  name="i-lucide-truck"
                  class="w-4 h-4 text-blue-500"
                />
                Delivery & Services
              </h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600 dark:text-slate-300">
                <div class="flex items-center gap-2">
                  <UIcon
                    name="i-lucide-calendar"
                    class="w-4 h-4 text-slate-500"
                  />
                  <span>Delivery timeline shown at checkout</span>
                </div>
                <div class="flex items-center gap-2">
                  <UIcon
                    name="i-lucide-receipt"
                    class="w-4 h-4 text-slate-500"
                  />
                  <span>Shipping cost calculated at checkout</span>
                </div>
                <div class="flex items-center gap-2">
                  <UIcon
                    name="i-lucide-shield-check"
                    class="w-4 h-4 text-slate-500"
                  />
                  <span>Secure payments & trusted sellers</span>
                </div>
                <div class="flex items-center gap-2">
                  <UIcon
                    name="i-lucide-headset"
                    class="w-4 h-4 text-slate-500"
                  />
                  <span>Support available 10am–8pm</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Description -->
        <div
          v-if="product.description"
          class="mt-12"
        >
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
              Product Description
            </h2>
            <FilamentContent
              class="track-content"
              :content="product.description"
            />
          </div>
        </div>

        <!-- Specifications -->
        <div
          v-if="specRows.length"
          class="mt-10"
        >
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
              Specifications
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div
                v-for="row in specRows"
                :key="row.label"
                class="flex items-start justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-2"
              >
                <span class="text-slate-500 dark:text-slate-400">{{ row.label }}</span>
                <span class="text-slate-900 dark:text-white font-medium text-right">{{ row.value }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-12">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-6 md:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
              <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                  Ratings & Reviews
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Verified buyer feedback and rating distribution
                </p>
              </div>
              <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-sm font-semibold">
                  <UIcon
                    name="i-lucide-star"
                    class="w-4 h-4"
                  />
                  {{ reviews?.stats.average_rating || 0 }}
                </span>
                <span class="text-sm text-slate-500">{{ reviews?.stats.total_reviews || 0 }} ratings</span>
                <span class="text-sm text-slate-500">• {{ totalHelpfulVotes }} helpful votes</span>
              </div>
            </div>

            <!-- Rating Summary -->
            <div
              v-if="reviews?.stats.total_reviews"
              class="flex flex-col md:flex-row gap-8 mb-8 pb-8 border-b border-slate-200 dark:border-slate-700"
            >
              <!-- Average Rating -->
              <div class="text-center">
                <div class="text-5xl font-black text-slate-900 dark:text-white">
                  {{ reviews.stats.average_rating }}
                </div>
                <div class="flex items-center justify-center gap-1 my-2">
                  <template
                    v-for="star in getStarArray(Math.round(reviews.stats.average_rating))"
                    :key="star.id"
                  >
                    <UIcon
                      name="i-lucide-star"
                      :class="['w-5 h-5', star.filled ? 'text-amber-400 fill-amber-400' : 'text-slate-300']"
                    />
                  </template>
                </div>
                <div class="text-sm text-slate-500">
                  {{ reviews.stats.total_reviews }} reviews
                </div>
              </div>

              <!-- Rating Distribution -->
              <div class="flex-1 space-y-2">
                <div
                  v-for="star in [5, 4, 3, 2, 1]"
                  :key="star"
                  class="flex items-center gap-3"
                >
                  <span class="text-sm w-8 flex items-center gap-1 text-slate-600 dark:text-slate-400">
                    {{ star }}
                    <UIcon
                      name="i-lucide-star"
                      class="w-3 h-3 text-amber-400"
                    />
                  </span>
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

            <!-- Review Submission -->
            <div class="mt-10 border-b border-slate-200 dark:border-slate-700 pb-8">
              <div
                v-if="isLoggedIn"
                class="space-y-4"
              >
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  Share your experience with other buyers. Pick a rating and tell us what you liked or what we can improve.
                </p>
                <div class="flex items-center gap-2 text-sm font-semibold">
                  <span class="text-slate-500 dark:text-slate-400">Your Rating:</span>
                  <div class="flex items-center gap-2">
                    <button
                      v-for="star in [1, 2, 3, 4, 5]"
                      :key="star"
                      type="button"
                      :class="getStarButtonClass(star)"
                      @click="reviewRating = star"
                      @mouseenter="hoverRating = star"
                      @mouseleave="hoverRating = null"
                    >
                      <UIcon
                        name="i-lucide-star"
                        class="w-4 h-4"
                      />
                    </button>
                    <span class="text-xs text-slate-400">
                      {{ (hoverRating ?? reviewRating) }} / 5
                    </span>
                  </div>
                </div>
                <UTextarea
                  v-model="reviewText"
                  placeholder="Write your review (max 2,000 characters)"
                  :rows="4"
                  maxlength="2000"
                  size="lg"
                  class="w-full"
                />
                <div class="flex items-center gap-3">
                  <UButton
                    color="primary"
                    size="md"
                    :loading="reviewSubmitting"
                    :disabled="reviewSubmitting"
                    class="rounded-xl px-6 py-3 font-semibold"
                    @click="submitReview"
                  >
                    Submit Review
                  </UButton>
                  <span class="text-xs text-slate-400">
                    Reviews are moderated before publication.
                  </span>
                </div>
              </div>
              <div
                v-else
                class="text-sm text-slate-500 dark:text-slate-400"
              >
                Please
                <NuxtLink
                  to="/auth/login"
                  class="text-primary-600 hover:underline dark:text-primary-400"
                >
                  login
                </NuxtLink>
                to leave a review, or
                <NuxtLink
                  to="/auth/register"
                  class="text-primary-600 hover:underline dark:text-primary-400"
                >
                  register
                </NuxtLink>
                if you don’t have an account yet.
              </div>
            </div>

            <!-- Reviews List -->
            <div
              v-if="reviews?.reviews.length"
              class="space-y-6"
            >
              <div
                v-for="review in reviews.reviews"
                :key="review.id"
                class="pb-6 border-b border-slate-100 dark:border-slate-800 last:border-0"
              >
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-violet-500 flex items-center justify-center text-white font-bold">
                    {{ review.author.name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                      <span class="font-medium text-slate-900 dark:text-white">{{ review.author.name }}</span>
                      <span class="text-xs text-slate-400">{{ formatDate(review.created_at) }}</span>
                      <span
                        v-if="review.helpful_votes >= 5"
                        class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"
                      >
                        Most Helpful
                      </span>
                      <span
                        v-if="review.helpful_votes"
                        class="text-xs text-slate-500"
                      >
                        • {{ review.helpful_votes }} helpful
                      </span>
                    </div>
                    <div class="flex items-center gap-1 mb-2">
                      <template
                        v-for="star in getStarArray(review.rating)"
                        :key="star.id"
                      >
                        <UIcon
                          name="i-lucide-star"
                          :class="['w-4 h-4', star.filled ? 'text-amber-400 fill-amber-400' : 'text-slate-300']"
                        />
                      </template>
                    </div>
                    <p
                      v-if="review.review"
                      class="text-slate-600 dark:text-slate-300"
                    >
                      {{ review.review }}
                    </p>
                    <div
                      v-if="review.helpful_votes !== undefined"
                      class="mt-2 flex items-center gap-3 text-xs text-slate-400"
                    >
                      <div class="flex items-center gap-1">
                        <UIcon
                          name="i-lucide-thumbs-up"
                          class="w-3 h-3"
                        />
                        {{ review.helpful_votes }} found this helpful
                      </div>
                      <UTooltip
                        v-if="isLoggedIn"
                        text="Mark helpful"
                      >
                        <UButton
                          size="xs"
                          variant="ghost"
                          circle
                          :loading="helpingReviewId === review.id"
                          :disabled="helpingReviewId === review.id"
                          class="text-amber-500 border border-slate-200 dark:border-slate-700 hover:bg-amber-100 dark:hover:bg-amber-900/40"
                          @click="markReviewHelpful(review.id)"
                        >
                          <span class="sr-only">Mark review helpful</span>
                          <span aria-hidden="true">
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="20"
                              height="20"
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              class="lucide lucide-thumbs-up"
                            >
                              <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z" />
                              <path d="M7 10v12" />
                            </svg>
                          </span>
                        </UButton>
                      </UTooltip>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- No Reviews -->
            <div
              v-else
              class="text-center py-8"
            >
              <UIcon
                name="i-lucide-message-square"
                class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-4"
              />
              <p class="text-slate-500 dark:text-slate-400">
                No reviews yet. Be the first to review this product!
              </p>
            </div>
          </div>
        </div>

        <!-- Related Products -->
        <section
          v-if="relatedProducts.length"
          class="mt-12"
          data-testid="related-products"
        >
          <StoreProductCarousel
            :products="relatedProducts"
            :title="product.category ? `More from ${product.category.name}` : 'Recommended Products'"
            subtitle="Similar picks you might like"
            :view-all-link="product.category ? `/shop/products?category=${product.category.slug}` : '/shop/products'"
            :loading="relatedLoading"
            badge-text="RELATED"
            badge-icon="i-lucide-sparkles"
            badge-color="violet"
            :autoplay-interval="5000"
          />
        </section>
      </UContainer>
    </div>

    <!-- Sticky Mobile CTA -->
    <div class="fixed bottom-0 inset-x-0 z-50 bg-white/95 dark:bg-slate-900/95 border-t border-slate-200 dark:border-slate-800 backdrop-blur lg:hidden">
      <div class="max-w-6xl mx-auto px-4 py-3 flex items-center gap-3">
        <div class="flex-1">
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Price
          </p>
          <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
            {{ currentPrice }}
          </p>
        </div>
        <UButton
          size="md"
          class="flex-1"
          :disabled="!currentInStock || addingToCart"
          :loading="addingToCart"
          @click="addToCart"
        >
          Add to Cart
        </UButton>
        <UButton
          size="md"
          variant="outline"
          :disabled="!currentInStock"
        >
          Buy Now
        </UButton>
      </div>
    </div>
  </div>
</template>

<style scoped>
.review-star-btn {
  border: none;
  padding: 0.35rem;
  display: inline-flex;
  border-radius: 0.75rem;
  transition: background-color 0.2s ease, transform 0.2s ease;
}
.review-star-btn svg {
  transition: transform 0.2s ease, fill 0.2s ease, color 0.2s ease;
}
.review-star-btn:hover {
  transform: translateY(-2px);
}
.review-star-btn svg {
  color: currentColor;
  fill: currentColor;
}
</style>
