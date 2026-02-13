<script setup lang="ts">
/**
 * Category Page - Flipkart-style Category Browsing
 * Dynamic category page with banner hero, hierarchy, child categories, and products
 */
import type { Product, Pagination } from '~/types/catalog'

definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const route = useRoute()
const toast = useToast()

// Auth composables
const { isLoggedIn } = useSanctum()
const user = useCurrentUser()
const { isMember, isPromoter } = useUserType()
const canSeeAffiliateBenefits = computed(() => isMember.value || isPromoter.value)

// Cart composable
const { addToCart: addToCartComposable, cartCount } = useCart()

// Filter state
const selectedSort = ref(route.query.sort as string || 'popularity')
const searchQuery = ref(route.query.search as string || '')
const priceMin = ref(route.query.price_min ? Number(route.query.price_min) : undefined)
const priceMax = ref(route.query.price_max ? Number(route.query.price_max) : undefined)
const currentPage = ref(1)

// Sort options
const sortOptions = [
  { label: 'Popularity', value: 'popularity' },
  { label: 'Newest First', value: 'latest' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Name: A to Z', value: 'name_asc' }
]

// Category interface
interface CategoryData {
  name: string
  slug: string
  description: string | null
  thumbnail: string | null
  banner: string | null
  seo_meta: {
    title?: string
    description?: string
    keywords?: string
  } | null
  children: Array<{
    name: string
    slug: string
    thumbnail: string | null
    product_count: number
  }>
  ancestors: Array<{
    name: string
    slug: string
  }>
}

// API query params for products
const queryParams = computed(() => {
  const params: Record<string, string | number> = {
    page: currentPage.value,
    sort: selectedSort.value
  }
  if (searchQuery.value) params.search = searchQuery.value
  if (priceMin.value !== undefined) params.price_min = priceMin.value
  if (priceMax.value !== undefined) params.price_max = priceMax.value
  return params
})

// Fetch category with products using the combined API
const status = ref<'idle' | 'pending' | 'success' | 'error'>('idle')
const error = ref<Error | null>(null)
const categoryResponse = ref<{
  category: CategoryData
  items: Product[]
  pagination: Pagination
} | null>(null)

const category = computed(() => categoryResponse.value?.category)
const products = computed(() => categoryResponse.value?.items || [])
const pagination = computed(() => categoryResponse.value?.pagination)

const buildQueryString = (params: Record<string, string | number>) => {
  const searchParams = new URLSearchParams()
  Object.entries(params).forEach(([key, value]) => {
    searchParams.append(key, String(value))
  })
  return searchParams.toString() ? `?${searchParams.toString()}` : ''
}

const loadCategory = async () => {
  if (!route.params.url) return

  status.value = 'pending'
  error.value = null

  const queryString = buildQueryString(queryParams.value)
  const url = `${config.public.apiBase}/api/catalog/category/${route.params.url}${queryString}`

  try {
    categoryResponse.value = await useSanctumFetch(url)
    status.value = 'success'
  } catch (fetchError) {
    categoryResponse.value = null
    status.value = 'error'
    error.value = fetchError instanceof Error ? fetchError : new Error('Failed to load category')
  }
}

watch(
  () => [route.params.url, queryParams.value],
  loadCategory,
  { immediate: true, deep: true }
)

// Dynamic SEO using category.seo_meta
watchEffect(() => {
  if (category.value?.seo_meta) {
    useComprehensiveSeo({
      title: category.value.seo_meta.title || category.value.name,
      description: category.value.seo_meta.description || category.value.description || `Browse ${category.value.name} products`,
      keywords: category.value.seo_meta.keywords,
      image: category.value.banner || category.value.thumbnail || undefined,
      type: 'website'
    })
  } else if (category.value) {
    // Fallback SEO
    useComprehensiveSeo({
      title: category.value.name,
      description: category.value.description || `Browse ${category.value.name} products`,
      image: category.value.banner || category.value.thumbnail || undefined,
      type: 'website'
    })
  }
})

// Update URL when filters change
watch([selectedSort, searchQuery, priceMin, priceMax], () => {
  currentPage.value = 1
  const query: Record<string, string> = {}
  if (selectedSort.value !== 'popularity') query.sort = selectedSort.value
  if (searchQuery.value) query.search = searchQuery.value
  if (priceMin.value !== undefined) query.price_min = String(priceMin.value)
  if (priceMax.value !== undefined) query.price_max = String(priceMax.value)
  navigateTo({ query })
})

const handlePageChange = (page: number) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Cart functionality
const addingToCart = ref<string | null>(null)

const addToCart = async (product: Product) => {
  if (!product.in_stock) return

  addingToCart.value = product.slug

  try {
    await addToCartComposable(product.slug, 1, {
      productName: product.name,
      productImage: product.image?.src
    })
  } catch (err: unknown) {
    const errorMessage = err instanceof Error ? err.message : 'Failed to add to cart'
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

// Price filter helpers
const applyPriceFilter = () => {
  // Trigger filter update
  currentPage.value = 1
}

const clearPriceFilter = () => {
  priceMin.value = undefined
  priceMax.value = undefined
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Loading State -->
    <div
      v-if="status === 'pending'"
      class="py-8"
    >
      <UContainer>
        <!-- Hero skeleton -->
        <div class="relative h-64 lg:h-80 bg-slate-200 dark:bg-slate-800 rounded-2xl animate-pulse mb-8" />
        <!-- Products skeleton -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div
            v-for="i in 8"
            :key="i"
            class="bg-white/80 dark:bg-slate-900/80 rounded-2xl animate-pulse"
          >
            <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
            <div class="p-4 space-y-3">
              <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
              <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
              <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3" />
            </div>
          </div>
        </div>
      </UContainer>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="py-16"
    >
      <UContainer>
        <div class="text-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg max-w-md mx-auto p-8">
          <UIcon
            name="i-lucide-alert-circle"
            class="w-20 h-20 mx-auto text-red-500 mb-6"
          />
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            Category Not Found
          </h3>
          <p class="text-slate-500 dark:text-slate-400 mb-6">
            The category you're looking for doesn't exist.
          </p>
          <UButton to="/shop">
            Browse All Products
          </UButton>
        </div>
      </UContainer>
    </div>

    <!-- Main Content -->
    <template v-else-if="category">
      <!-- Hero Banner with Category Image -->
      <div class="relative overflow-hidden">
        <!-- Background Image or Gradient -->
        <div class="absolute inset-0">
          <img
            v-if="category.banner"
            :src="category.banner"
            :alt="category.name"
            class="w-full h-full object-cover"
          >
          <div
            v-else
            class="w-full h-full bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600"
          />
          <!-- Overlay -->
          <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" />
        </div>

        <!-- Background decorations -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
          <div class="absolute top-0 left-1/4 w-64 h-64 bg-white rounded-full blur-3xl" />
          <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-pink-300 rounded-full blur-3xl" />
        </div>

        <UContainer class="relative z-10 py-12 md:py-16">
          <div class="text-center text-white max-w-4xl mx-auto">
            <!-- Personalized greeting for logged-in users -->
            <p
              v-if="isLoggedIn && user"
              class="text-purple-100 text-sm mb-2"
            >
              Welcome back, {{ user.name }}!
            </p>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-black mb-4 drop-shadow-lg">
              {{ category.name }}
            </h1>

            <p
              v-if="category.description"
              class="text-purple-100 text-lg max-w-2xl mx-auto mb-4"
            >
              {{ category.description }}
            </p>
            <p
              v-else
              class="text-purple-100 text-lg max-w-2xl mx-auto mb-4"
            >
              Explore our premium {{ category.name }} collection
            </p>

            <!-- Category Stats -->
            <div class="flex flex-wrap justify-center gap-4 mt-6">
              <div class="bg-white/10 backdrop-blur-lg rounded-2xl px-4 py-2 border border-white/20">
                <div class="text-xl font-black">
                  {{ pagination?.total || 0 }}
                </div>
                <div class="text-xs opacity-80">
                  Products
                </div>
              </div>
              <div
                v-if="category.children?.length"
                class="bg-white/10 backdrop-blur-lg rounded-2xl px-4 py-2 border border-white/20"
              >
                <div class="text-xl font-black">
                  {{ category.children.length }}
                </div>
                <div class="text-xs opacity-80">
                  Subcategories
                </div>
              </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-6 max-w-xl mx-auto">
              <UInput
                v-model="searchQuery"
                placeholder="Search in this category..."
                size="lg"
                icon="i-lucide-search"
                class="bg-white/10 backdrop-blur-sm border-white/20"
              />
            </div>
          </div>
        </UContainer>
      </div>

      <UContainer class="py-8">
        <!-- Breadcrumb with Cart Icon -->
        <div class="flex items-center justify-between mb-6">
          <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
            <NuxtLink
              to="/"
              class="hover:text-primary-500"
            >Home</NuxtLink>
            <UIcon
              name="i-lucide-chevron-right"
              class="w-4 h-4"
            />
            <NuxtLink
              to="/shop"
              class="hover:text-primary-500"
            >Shop</NuxtLink>
            <template v-if="category.ancestors?.length">
              <template
                v-for="ancestor in category.ancestors"
                :key="ancestor.slug"
              >
                <UIcon
                  name="i-lucide-chevron-right"
                  class="w-4 h-4"
                />
                <NuxtLink
                  :to="`/category/${ancestor.slug}`"
                  class="hover:text-primary-500"
                >{{ ancestor.name }}</NuxtLink>
              </template>
            </template>
            <UIcon
              name="i-lucide-chevron-right"
              class="w-4 h-4"
            />
            <span class="text-slate-900 dark:text-white font-medium">{{ category.name }}</span>
          </nav>

          <!-- Cart Button -->
          <NuxtLink
            to="/cart"
            class="relative flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl font-semibold text-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
          >
            <UIcon
              name="i-lucide-shopping-cart"
              class="w-4 h-4"
            />
            <span class="hidden sm:inline">Cart</span>
            <span
              v-if="cartCount > 0"
              class="absolute -top-2 -right-2 min-w-5 h-5 px-1 bg-fuchsia-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
            >
              {{ cartCount > 99 ? '99+' : cartCount }}
            </span>
          </NuxtLink>
        </div>

        <!-- Child Categories (Flipkart-style) -->
        <div
          v-if="category.children && category.children.length"
          class="mb-12"
        >
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
            Browse {{ category.name }}
          </h2>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <NuxtLink
              v-for="child in category.children"
              :key="child.slug"
              :to="`/category/${child.slug}`"
              class="group block bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300"
            >
              <div class="relative h-32 overflow-hidden bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900/30 dark:to-fuchsia-900/30">
                <img
                  v-if="child.thumbnail"
                  :src="child.thumbnail"
                  :alt="child.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                >
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center"
                >
                  <UIcon
                    name="i-lucide-folder-open"
                    class="w-12 h-12 text-primary-400 opacity-50"
                  />
                </div>
                <!-- Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent" />
                <!-- Product count badge -->
                <div class="absolute top-2 right-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm px-2 py-1 rounded-full text-xs font-bold text-slate-900 dark:text-white">
                  {{ child.product_count }} items
                </div>
              </div>
              <div class="p-3">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                  {{ child.name }}
                </h3>
              </div>
            </NuxtLink>
          </div>
        </div>

        <!-- Products Section -->
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Sidebar Filters (Desktop) -->
          <aside class="hidden lg:block w-64 shrink-0">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-4 sticky top-24">
              <h3 class="font-bold text-slate-900 dark:text-white mb-4">
                Filters
              </h3>

              <!-- Price Range -->
              <div class="mb-6">
                <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                  Price Range
                </h4>
                <div class="space-y-3">
                  <UInput
                    v-model.number="priceMin"
                    type="number"
                    placeholder="Min"
                    size="sm"
                  />
                  <UInput
                    v-model.number="priceMax"
                    type="number"
                    placeholder="Max"
                    size="sm"
                  />
                  <div class="flex gap-2">
                    <UButton
                      size="xs"
                      block
                      @click="applyPriceFilter"
                    >
                      Apply
                    </UButton>
                    <UButton
                      size="xs"
                      variant="ghost"
                      block
                      @click="clearPriceFilter"
                    >
                      Clear
                    </UButton>
                  </div>
                </div>
              </div>
            </div>
          </aside>

          <!-- Main Products Area -->
          <div class="flex-1">
            <!-- Filters Row -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
              <div class="text-sm text-slate-600 dark:text-slate-400">
                <span
                  v-if="pagination"
                  class="font-medium"
                >
                  {{ pagination.total }} products
                </span>
              </div>

              <!-- Sort -->
              <USelect
                v-model="selectedSort"
                :items="sortOptions"
                class="w-48"
              />
            </div>

            <!-- Products Grid -->
            <div
              v-if="products.length"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6"
            >
              <div
                v-for="product in products"
                :key="product.slug"
                class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden group cursor-pointer hover:shadow-2xl transition-all duration-300"
              >
                <NuxtLink :to="`/shop/product/${product.slug || (product as any).url || ''}`">
                  <!-- Image -->
                  <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
                    <img
                      v-if="product.image?.src"
                      :src="product.image.thumbnail || product.image.src"
                      :alt="product.image.alt || product.name"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      loading="lazy"
                    >
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center"
                    >
                      <UIcon
                        name="i-lucide-package"
                        class="w-16 h-16 text-slate-300 dark:text-slate-600"
                      />
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

                    <!-- Discount Badge -->
                    <div
                      v-if="product.discount_percent"
                      class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                    >
                      {{ product.discount_percent }}% OFF
                    </div>

                    <!-- BV Badge - Only for Member/Promoter -->
                    <div
                      v-if="canSeeAffiliateBenefits && product.bv > 0"
                      class="absolute top-2 right-2 bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded"
                    >
                      {{ product.bv }} BV
                    </div>

                    <!-- Guest Coins Badge -->
                    <div
                      v-else-if="!isLoggedIn && product.reward_points > 0"
                      class="absolute top-2 right-2 bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white text-xs font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1"
                    >
                      <UIcon
                        name="i-lucide-coins"
                        class="w-3.5 h-3.5"
                      />
                      <span>Coins</span>
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
                    <div class="flex flex-wrap items-baseline gap-2 mb-2">
                      <span class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ product.price_formatted }}
                      </span>
                      <span
                        v-if="product.original_price_formatted"
                        class="text-sm text-slate-400 line-through"
                      >
                        {{ product.original_price_formatted }}
                      </span>
                      <span
                        v-if="product.discount_percent"
                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                      >
                        {{ product.discount_percent }}% off
                      </span>
                    </div>

                    <!-- Sale Name Badge -->
                    <div
                      v-if="product.sale_name"
                      class="text-xs text-red-600 dark:text-red-400 font-medium mb-2"
                    >
                      {{ product.sale_name }}
                    </div>

                    <!-- Affiliate Benefits - Only for Member/Promoter -->
                    <div
                      v-if="canSeeAffiliateBenefits && product.reward_points > 0"
                      class="text-xs text-emerald-600 dark:text-emerald-400 mb-3"
                    >
                      Earn {{ product.reward_points }} coins
                    </div>

                    <!-- Guest reward teaser -->
                    <div
                      v-else-if="!isLoggedIn && product.reward_points > 0"
                      class="text-xs text-purple-600 dark:text-purple-400 mb-3 flex items-center gap-1"
                    >
                      <UIcon
                        name="i-lucide-coins"
                        class="w-3.5 h-3.5"
                      />
                      <span>Sign in to earn coins</span>
                    </div>
                  </div>
                </NuxtLink>

                <!-- Add to Cart -->
                <div class="px-4 pb-4">
                  <UButton
                    block
                    :disabled="!product.in_stock || addingToCart === product.slug"
                    :loading="addingToCart === product.slug"
                    :color="product.in_stock ? 'primary' : 'neutral'"
                    @click="addToCart(product)"
                  >
                    <UIcon
                      name="i-lucide-shopping-cart"
                      class="w-4 h-4 mr-2"
                    />
                    {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                  </UButton>
                </div>
              </div>
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
                  v-if="searchQuery || priceMin || priceMax"
                  @click="searchQuery = ''; clearPriceFilter()"
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
    </template>
  </div>
</template>
