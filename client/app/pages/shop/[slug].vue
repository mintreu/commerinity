<script setup lang="ts">
/**
 * Product Detail Page
 * Displays single product with gallery, variants, and add to cart
 */

definePageMeta({
  layout: 'default'
})

const config = useRuntimeConfig()
const route = useRoute()
const slug = route.params.slug as string

// Fetch product details
const { data: productResponse, status, error } = await useFetch<{
  success: boolean
  data: {
    id: number
    name: string
    slug: string
    sku: string
    description: string | null
    short_description: string | null
    price: number
    price_formatted: string
    category: { id: number; name: string; slug: string } | null
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
      id: number
      name: string
      sku: string
      price: number
      price_formatted: string
      image: string | null
      in_stock: boolean
      filter_options: Array<{ filter: string; value: string }>
    }>
    filter_options: Array<{
      filter_name: string
      options: Array<{ id: number; value: string }>
    }>
  }
}>(`${config.public.apiBase}/api/catalog/products/${slug}`, {
  server: false
})

const product = computed(() => productResponse.value?.data)

// SEO
useSeoMeta({
  title: () => product.value ? `${product.value.name} - Mintreu Shop` : 'Product - Mintreu Shop',
  description: () => product.value?.short_description || product.value?.description?.slice(0, 160) || 'Shop premium products at Mintreu'
})

// Gallery state
const selectedImage = ref(0)
const selectedVariant = ref<number | null>(null)

// Quantity
const quantity = ref(1)
const maxQuantity = computed(() => Math.min(product.value?.stock_quantity || 10, 10))

const incrementQuantity = () => {
  if (quantity.value < maxQuantity.value) {
    quantity.value++
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

// Current display price (may change with variant)
const currentPrice = computed(() => {
  if (selectedVariant.value && product.value?.variants) {
    const variant = product.value.variants.find(v => v.id === selectedVariant.value)
    return variant?.price_formatted || product.value.price_formatted
  }
  return product.value?.price_formatted || '₹0'
})

const currentInStock = computed(() => {
  if (selectedVariant.value && product.value?.variants) {
    const variant = product.value.variants.find(v => v.id === selectedVariant.value)
    return variant?.in_stock ?? product.value?.in_stock
  }
  return product.value?.in_stock
})

// Cart functionality
const addingToCart = ref(false)
const toast = useToast()

const addToCart = async () => {
  if (!product.value || !currentInStock.value) return

  addingToCart.value = true

  try {
    const productId = selectedVariant.value || product.value.id

    const response = await useSanctumFetch<{ success: boolean; message: string }>(
      `${config.public.apiBase}/api/cart`,
      {
        method: 'POST',
        body: {
          product_id: productId,
          quantity: quantity.value
        }
      }
    )

    if (response.success) {
      toast.add({
        title: 'Added to Cart',
        description: `${product.value.name} x${quantity.value} added to your cart`,
        color: 'success',
        icon: 'i-lucide-shopping-cart'
      })
    }
  } catch (err: unknown) {
    const errorMessage = err instanceof Error ? err.message : 'Failed to add to cart'
    toast.add({
      title: 'Error',
      description: errorMessage,
      color: 'error',
      icon: 'i-lucide-alert-circle'
    })
  } finally {
    addingToCart.value = false
  }
}

// Select variant when clicking filter option
const selectVariantByOption = (filterName: string, optionValue: string) => {
  if (!product.value?.variants) return

  // Find variant that has this option
  const variant = product.value.variants.find(v =>
    v.filter_options.some(fo => fo.filter === filterName && fo.value === optionValue)
  )

  if (variant) {
    selectedVariant.value = variant.id
    // Update gallery if variant has image
    if (variant.image) {
      const imageIndex = product.value.gallery.findIndex(g => g.url === variant.image)
      if (imageIndex >= 0) {
        selectedImage.value = imageIndex
      }
    }
  }
}
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
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
            Product Not Found
          </h1>
          <p class="text-slate-500 dark:text-slate-400 mb-6">
            The product you're looking for doesn't exist or has been removed.
          </p>
          <NuxtLink to="/shop">
            <UButton>
              <UIcon name="i-lucide-arrow-left" class="w-4 h-4 mr-2" />
              Back to Shop
            </UButton>
          </NuxtLink>
        </div>
      </UContainer>
    </div>

    <!-- Product Details -->
    <div v-else class="py-8">
      <UContainer>
        <!-- Breadcrumb -->
        <nav class="mb-6">
          <ul class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <li>
              <NuxtLink to="/" class="hover:text-primary-500">
                Home
              </NuxtLink>
            </li>
            <li>
              <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
            </li>
            <li>
              <NuxtLink to="/shop" class="hover:text-primary-500">
                Shop
              </NuxtLink>
            </li>
            <li v-if="product.category">
              <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
            </li>
            <li v-if="product.category">
              <NuxtLink :to="`/shop?category=${product.category.slug}`" class="hover:text-primary-500">
                {{ product.category.name }}
              </NuxtLink>
            </li>
            <li>
              <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
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
            <div class="aspect-square bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
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
                <img
                  :src="image.thumbnail"
                  :alt="`${product.name} - Image ${index + 1}`"
                  class="w-full h-full object-cover"
                >
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="space-y-6">
            <!-- Category -->
            <div v-if="product.category">
              <NuxtLink
                :to="`/shop?category=${product.category.slug}`"
                class="text-sm text-primary-600 dark:text-primary-400 font-medium hover:underline"
              >
                {{ product.category.name }}
              </NuxtLink>
            </div>

            <!-- Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white">
              {{ product.name }}
            </h1>

            <!-- SKU -->
            <p class="text-sm text-slate-500 dark:text-slate-400">
              SKU: {{ product.sku }}
            </p>

            <!-- Price -->
            <div class="flex items-baseline gap-3">
              <span class="text-3xl font-bold text-slate-900 dark:text-white">
                {{ currentPrice }}
              </span>
            </div>

            <!-- MLM Points Badge -->
            <div v-if="product.bv > 0 || product.pv > 0 || product.reward_points > 0" class="flex flex-wrap gap-2">
              <span v-if="product.bv > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                <UIcon name="i-lucide-trending-up" class="w-4 h-4 mr-1" />
                {{ product.bv }} BV
              </span>
              <span v-if="product.pv > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                <UIcon name="i-lucide-star" class="w-4 h-4 mr-1" />
                {{ product.pv }} PV
              </span>
              <span v-if="product.reward_points > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                <UIcon name="i-lucide-gift" class="w-4 h-4 mr-1" />
                {{ product.reward_points }} Reward Points
              </span>
            </div>

            <!-- Short Description -->
            <p v-if="product.short_description" class="text-slate-600 dark:text-slate-300">
              {{ product.short_description }}
            </p>

            <!-- Variants / Options -->
            <div v-if="product.has_variants && product.filter_options.length" class="space-y-4">
              <div v-for="filterGroup in product.filter_options" :key="filterGroup.filter_name">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  {{ filterGroup.filter_name }}
                </label>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="option in filterGroup.options"
                    :key="option.id"
                    :class="[
                      'px-4 py-2 rounded-lg border text-sm font-medium transition-all',
                      product.variants.find(v => v.id === selectedVariant)?.filter_options.some(fo => fo.filter === filterGroup.filter_name && fo.value === option.value)
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                        : 'border-slate-200 dark:border-slate-700 hover:border-primary-300'
                    ]"
                    @click="selectVariantByOption(filterGroup.filter_name, option.value)"
                  >
                    {{ option.value }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Quantity -->
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Quantity
              </label>
              <div class="flex items-center gap-3">
                <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg">
                  <button
                    class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    :disabled="quantity <= 1"
                    @click="decrementQuantity"
                  >
                    <UIcon name="i-lucide-minus" class="w-4 h-4" />
                  </button>
                  <span class="w-12 text-center font-medium">{{ quantity }}</span>
                  <button
                    class="p-3 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    :disabled="quantity >= maxQuantity"
                    @click="incrementQuantity"
                  >
                    <UIcon name="i-lucide-plus" class="w-4 h-4" />
                  </button>
                </div>
                <span v-if="currentInStock" class="text-sm text-emerald-600 dark:text-emerald-400">
                  <UIcon name="i-lucide-check-circle" class="w-4 h-4 inline mr-1" />
                  In Stock
                </span>
                <span v-else class="text-sm text-red-600 dark:text-red-400">
                  <UIcon name="i-lucide-x-circle" class="w-4 h-4 inline mr-1" />
                  Out of Stock
                </span>
              </div>
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
                <UIcon name="i-lucide-shopping-cart" class="w-5 h-5 mr-2" />
                {{ currentInStock ? 'Add to Cart' : 'Out of Stock' }}
              </UButton>
            </div>

            <!-- Return Policy -->
            <div v-if="product.is_returnable" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 p-3 bg-slate-100 dark:bg-slate-800 rounded-lg">
              <UIcon name="i-lucide-undo-2" class="w-5 h-5 text-emerald-500" />
              <span>{{ product.return_days }}-day return policy</span>
            </div>

            <!-- Trust Badges -->
            <div class="flex flex-wrap gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon name="i-lucide-shield-check" class="w-5 h-5 text-emerald-500" />
                <span>Quality Assured</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon name="i-lucide-truck" class="w-5 h-5 text-blue-500" />
                <span>Fast Delivery</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <UIcon name="i-lucide-headphones" class="w-5 h-5 text-violet-500" />
                <span>24/7 Support</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Description -->
        <div v-if="product.description" class="mt-12">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">
              Product Description
            </h2>
            <div class="prose prose-slate dark:prose-invert max-w-none" v-html="product.description" />
          </div>
        </div>
      </UContainer>
    </div>
  </div>
</template>

