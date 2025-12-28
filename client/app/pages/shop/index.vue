<script setup lang="ts">
/**
 * Shop Page - Product Catalog
 * Displays products from the API with filters and categories
 */

definePageMeta({
  layout: 'default'
})

useSeoMeta({
  title: 'Shop - Mintreu',
  description: 'Explore our premium products. Quality products at great prices with MLM rewards.'
})

const config = useRuntimeConfig()
const route = useRoute()

// Filter state
const selectedCategory = ref(route.query.category as string || '')
const selectedSort = ref(route.query.sort as string || 'popularity')
const searchQuery = ref(route.query.search as string || '')
const currentPage = ref(1)

// Sort options
const sortOptions = [
  { label: 'Popularity', value: 'popularity' },
  { label: 'Newest First', value: 'latest' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Name: A to Z', value: 'name_asc' }
]

// API query params
const queryParams = computed(() => {
  const params: Record<string, string | number> = {
    page: currentPage.value,
    sort: selectedSort.value
  }
  if (selectedCategory.value) params.category = selectedCategory.value
  if (searchQuery.value) params.search = searchQuery.value
  return params
})

// Fetch products
const { data: productsResponse, status: productsStatus, refresh: refreshProducts } = await useFetch<{
  success: boolean
  data: {
    items: Array<{
      id: number
      name: string
      slug: string
      sku: string
      price: number
      price_formatted: string
      category: { id: number; name: string; slug: string } | null
      image: string | null
      in_stock: boolean
      stock_quantity: number
      view_count: number
      bv: number
      pv: number
      reward_points: number
    }>
    pagination: {
      current_page: number
      last_page: number
      per_page: number
      total: number
      has_more: boolean
    }
  }
}>(`${config.public.apiBase}/api/catalog/products`, {
  query: queryParams,
  watch: [queryParams],
  lazy: true,
  server: false
})

// Fetch categories
const { data: categoriesResponse } = await useFetch<{
  success: boolean
  data: Array<{
    id: number
    name: string
    slug: string
    product_count: number
    thumbnail: string | null
    children: Array<{ id: number; name: string; slug: string; product_count: number }>
  }>
}>(`${config.public.apiBase}/api/catalog/categories`, {
  lazy: true,
  server: false
})

const products = computed(() => productsResponse.value?.data?.items || [])
const pagination = computed(() => productsResponse.value?.data?.pagination)
const categories = computed(() => {
  const cats = categoriesResponse.value?.data || []
  return [{ id: 0, name: 'All Products', slug: '', product_count: 0 }, ...cats]
})

// Update URL when filters change
watch([selectedCategory, selectedSort, searchQuery], () => {
  currentPage.value = 1
  const query: Record<string, string> = {}
  if (selectedCategory.value) query.category = selectedCategory.value
  if (selectedSort.value !== 'popularity') query.sort = selectedSort.value
  if (searchQuery.value) query.search = searchQuery.value
  navigateTo({ query })
})

const handleCategoryChange = (slug: string) => {
  selectedCategory.value = slug
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Cart functionality
const addingToCart = ref<number | null>(null)
const toast = useToast()

const addToCart = async (product: typeof products.value[0]) => {
  if (!product.in_stock) return

  addingToCart.value = product.id

  try {
    const response = await useSanctumFetch<{ success: boolean; message: string }>(
      `${config.public.apiBase}/api/cart`,
      {
        method: 'POST',
        body: {
          product_id: product.id,
          quantity: 1
        }
      }
    )

    if (response.success) {
      toast.add({
        title: 'Added to Cart',
        description: `${product.name} has been added to your cart`,
        color: 'success',
        icon: 'i-lucide-shopping-cart'
      })
    }
  } catch (error: unknown) {
    const errorMessage = error instanceof Error ? error.message : 'Failed to add to cart'
    toast.add({
      title: 'Error',
      description: errorMessage,
      color: 'error',
      icon: 'i-lucide-alert-circle'
    })
  } finally {
    addingToCart.value = null
  }
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Banner -->
    <div class="relative bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 py-12 md:py-16 overflow-hidden">
      <!-- Background decorations -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-white rounded-full blur-3xl" />
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-pink-300 rounded-full blur-3xl" />
      </div>

      <UContainer class="relative z-10">
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-black mb-3">
            Shop Our Products
          </h1>
          <p class="text-purple-100 text-lg max-w-2xl mx-auto">
            Premium quality products with exclusive member rewards
          </p>

          <!-- Search Bar -->
          <div class="mt-6 max-w-xl mx-auto">
            <UInput
              v-model="searchQuery"
              placeholder="Search products..."
              size="lg"
              icon="i-lucide-search"
              class="bg-white/10 backdrop-blur-sm border-white/20"
            />
          </div>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-8">
      <!-- Filters Row -->
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Categories Sidebar (Desktop) -->
        <aside class="hidden lg:block w-64 shrink-0">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-4 sticky top-24">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">
              Categories
            </h3>
            <ul class="space-y-1">
              <li v-for="cat in categories" :key="cat.id">
                <button
                  :class="[
                    'w-full text-left px-3 py-2 rounded-lg text-sm transition-all',
                    selectedCategory === cat.slug
                      ? 'bg-primary-500 text-white font-medium'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                  ]"
                  @click="handleCategoryChange(cat.slug)"
                >
                  {{ cat.name }}
                  <span v-if="cat.product_count" class="text-xs opacity-70 ml-1">
                    ({{ cat.product_count }})
                  </span>
                </button>
              </li>
            </ul>
          </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
          <!-- Mobile Categories & Filters -->
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <!-- Mobile Categories -->
            <div class="w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
              <div class="flex gap-2 min-w-max">
                <button
                  v-for="cat in categories.slice(0, 6)"
                  :key="cat.id"
                  :class="[
                    'px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap',
                    selectedCategory === cat.slug
                      ? 'bg-primary-500 text-white'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                  ]"
                  @click="handleCategoryChange(cat.slug)"
                >
                  {{ cat.name }}
                </button>
              </div>
            </div>

            <!-- Sort -->
            <USelect
              v-model="selectedSort"
              :items="sortOptions"
              class="w-48"
            />
          </div>

          <!-- Loading State -->
          <div v-if="productsStatus === 'pending'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <div
              v-for="i in 8"
              :key="i"
              class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden animate-pulse"
            >
              <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
              <div class="p-4 space-y-3">
                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
                <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3" />
                <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded" />
              </div>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-else-if="products.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <NuxtLink
              v-for="product in products"
              :key="product.id"
              :to="`/shop/${product.slug}`"
              class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden group cursor-pointer"
            >
              <!-- Image -->
              <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <img
                  v-if="product.image"
                  :src="product.image"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy"
                >
                <div v-else class="w-full h-full flex items-center justify-center">
                  <UIcon name="i-lucide-package" class="w-16 h-16 text-slate-300 dark:text-slate-600" />
                </div>

                <!-- Out of Stock Overlay -->
                <div
                  v-if="!product.in_stock"
                  class="absolute inset-0 bg-black/50 flex items-center justify-center"
                >
                  <span class="bg-slate-900 text-white px-4 py-2 rounded-lg font-medium text-sm">
                    Out of Stock
                  </span>
                </div>

                <!-- BV/PV Badge -->
                <div
                  v-if="product.bv > 0"
                  class="absolute top-2 right-2 bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded"
                >
                  {{ product.bv }} BV
                </div>
              </div>

              <!-- Content -->
              <div class="p-4">
                <p
                  v-if="product.category"
                  class="text-xs text-primary-600 dark:text-primary-400 font-medium mb-1"
                >
                  {{ product.category.name }}
                </p>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-2 line-clamp-2 text-sm md:text-base">
                  {{ product.name }}
                </h3>

                <!-- Price -->
                <div class="flex items-center gap-2 mb-3">
                  <span class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ product.price_formatted }}
                  </span>
                </div>

                <!-- Reward Points -->
                <div
                  v-if="product.reward_points > 0"
                  class="text-xs text-emerald-600 dark:text-emerald-400 mb-3"
                >
                  Earn {{ product.reward_points }} reward points
                </div>

                <!-- Add to Cart -->
                <UButton
                  block
                  :disabled="!product.in_stock || addingToCart === product.id"
                  :loading="addingToCart === product.id"
                  :color="product.in_stock ? 'primary' : 'neutral'"
                  @click.prevent="addToCart(product)"
                >
                  <UIcon
                    name="i-lucide-shopping-cart"
                    class="w-4 h-4 mr-2"
                  />
                  {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                </UButton>
              </div>
            </NuxtLink>
          </div>

          <!-- Empty State -->
          <div
            v-else
            class="text-center py-16"
          >
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg max-w-md mx-auto p-8">
              <UIcon
                name="i-lucide-package-x"
                class="w-20 h-20 mx-auto text-slate-400 mb-6"
              />
              <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
                No products found
              </h3>
              <p class="text-slate-500 dark:text-slate-400 mb-6">
                {{ searchQuery ? `No products match "${searchQuery}"` : 'No products available in this category' }}
              </p>
              <UButton
                v-if="selectedCategory || searchQuery"
                @click="selectedCategory = ''; searchQuery = ''"
              >
                Clear Filters
              </UButton>
            </div>
          </div>

          <!-- Pagination -->
          <div
            v-if="pagination && pagination.last_page > 1"
            class="flex justify-center mt-8"
          >
            <UPagination
              :model-value="pagination.current_page"
              :total="pagination.total"
              :items-per-page="pagination.per_page"
              @update:model-value="handlePageChange"
            />
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>

