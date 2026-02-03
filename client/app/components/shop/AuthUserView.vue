<script setup lang="ts">
/**
 * Shop Page for Authenticated Users (Any logged-in user)
 * Features:
 * - Left sidebar with comprehensive filters (Flipkart/Amazon style)
 * - Product grid with sorting options
 * - Personalized greeting
 * - Infinite scroll / Lazy loading
 * - Mobile filter drawer toggle
 * - Responsive grid layout with minimal white space
 */
import type { Product } from '~/types/catalog'

interface FilterOption {
  id: number
  value: string
  swatch: string | null
  count: number
}

interface FilterGroup {
  name: string
  options: FilterOption[]
}

interface CategoryChild {
  name: string
  slug: string
  product_count: number
  total_products?: number
  children?: CategoryChild[]
}

interface Category {
  name: string
  slug: string
  total_products?: number
  product_count?: number
  thumbnail?: string
  children?: CategoryChild[]
}

interface Props {
  userName: string
}

defineProps<Props>()

const config = useRuntimeConfig()
const { isLoggedIn } = useSanctum()

// Filter state
const activeFilters = ref<Record<string, any>>({})
const sortBy = ref('popularity')

// Lazy loading state
const currentPage = ref(1)
const allProducts = ref<Product[]>([])
const hasMore = ref(true)
const isLoadingMore = ref(false)
const totalProducts = ref(0)

// Sort options
const sortOptions = [
  { label: 'Popularity', value: 'popularity' },
  { label: 'Newest First', value: 'latest' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Name: A to Z', value: 'name_asc' }
]

const buildQueryString = (params: Record<string, any>) => {
  const query = new URLSearchParams()
  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === '') continue
    query.set(key, String(value))
  }
  const queryString = query.toString()
  return queryString ? `?${queryString}` : ''
}

// Build query params from filters
const queryParams = computed(() => {
  const params: Record<string, any> = {
    page: currentPage.value,
    sort: sortBy.value,
    per_page: 16
  }

  if (activeFilters.value.search) params.search = activeFilters.value.search
  if (activeFilters.value.category) params.category = activeFilters.value.category
  if (activeFilters.value.min_price) params.min_price = activeFilters.value.min_price
  if (activeFilters.value.max_price) params.max_price = activeFilters.value.max_price
  if (activeFilters.value.in_stock) params.in_stock = 1
  if (activeFilters.value.on_sale) params.on_sale = 1
  if (activeFilters.value.min_rating) params.min_rating = activeFilters.value.min_rating

  // Dynamic filters (JSON payload expected by API)
  if (activeFilters.value.filters) {
    const payload: Record<string, string> = {}
    for (const [name, ids] of Object.entries(activeFilters.value.filters)) {
      if (Array.isArray(ids) && ids.length > 0) {
        payload[name] = (ids as number[]).join(',')
      }
    }
    if (Object.keys(payload).length > 0) {
      params.filters = JSON.stringify(payload)
    }
  }

  return params
})

const productsResponse = ref<{
  success: boolean
  data: Product[]
  meta?: {
    current_page: number
    last_page: number
    total: number
    per_page: number
  }
} | null>(null)
const productsStatus = ref<'pending' | 'success' | 'error'>('pending')

const filtersResponse = ref<{
  success: boolean
  data: {
    price_range: { min: number, max: number }
    filter_options: FilterGroup[]
    sort_options: Array<{ value: string, label: string }>
  }
} | null>(null)

const categoriesResponse = ref<{
  success: boolean
  data: Category[]
} | null>(null)

const applyProductsResponse = (payload: typeof productsResponse.value, append: boolean) => {
  if (!payload?.data) return
  if (append) {
    allProducts.value = [...allProducts.value, ...payload.data]
  } else {
    allProducts.value = payload.data
  }

  const meta = payload.meta
  if (meta) {
    hasMore.value = meta.current_page < meta.last_page
    totalProducts.value = meta.total
  } else {
    hasMore.value = false
    totalProducts.value = allProducts.value.length
  }
}

const loadProducts = async (append = false) => {
  productsStatus.value = 'pending'
  try {
    const queryString = buildQueryString(queryParams.value)
    productsResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/catalog/products${queryString}`
    )
    applyProductsResponse(productsResponse.value, append)
    productsStatus.value = 'success'
  } catch {
    productsStatus.value = 'error'
  } finally {
    isLoadingMore.value = false
  }
}

const loadFilters = async () => {
  const queryString = buildQueryString(
    activeFilters.value.category ? { category: activeFilters.value.category } : {}
  )
  try {
    filtersResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/catalog/filters${queryString}`
    )
  } catch {
    filtersResponse.value = null
  }
}

const loadCategories = async () => {
  try {
    categoriesResponse.value = await useSanctumFetch(`${config.public.apiBase}/api/catalog/categories`)
  } catch {
    categoriesResponse.value = null
  }
}

// Computed
const priceRange = computed(() => filtersResponse.value?.data?.price_range || { min: 0, max: 10000 })
const filterGroups = computed(() => filtersResponse.value?.data?.filter_options || [])
const categories = computed(() => categoriesResponse.value?.data || [])
const isLoading = computed(() => productsStatus.value === 'pending' && currentPage.value === 1)

// Handle filter updates
const handleFilterUpdate = (filters: Record<string, any>) => {
  activeFilters.value = filters
  currentPage.value = 1
  allProducts.value = []
  loadProducts(false)
  loadFilters()
}

// Handle filter clear
const handleFilterClear = () => {
  activeFilters.value = {}
  currentPage.value = 1
  allProducts.value = []
  loadProducts(false)
  loadFilters()
}

// Handle sort change
const handleSortChange = () => {
  currentPage.value = 1
  allProducts.value = []
  loadProducts(false)
}

// Load more products (infinite scroll)
const loadMore = async () => {
  if (isLoadingMore.value || !hasMore.value) return

  isLoadingMore.value = true
  currentPage.value += 1
  await loadProducts(true)
}

// Intersection observer for infinite scroll
const loadMoreTrigger = ref<HTMLElement | null>(null)

onMounted(() => {
  loadCategories()
  loadFilters()
  loadProducts(false)

  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && hasMore.value && !isLoadingMore.value && !isLoading.value) {
        loadMore()
      }
    },
    { rootMargin: '200px' }
  )

  if (loadMoreTrigger.value) {
    observer.observe(loadMoreTrigger.value)
  }

  onUnmounted(() => {
    observer.disconnect()
  })
})

// Filter sidebar ref for mobile toggle
const filterSidebarRef = ref<{ isMobileFilterOpen: boolean, activeFilterCount: number } | null>(null)

// Active filter count for badge
const activeFilterCount = computed(() => {
  return filterSidebarRef.value?.activeFilterCount || 0
})

// Open mobile filters
const openMobileFilters = () => {
  if (filterSidebarRef.value) {
    filterSidebarRef.value.isMobileFilterOpen = true
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Compact Header -->
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 text-white">
      <div class="max-w-7xl mx-auto px-3 md:px-4 py-4 md:py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <template v-if="isLoggedIn">
              <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 mb-2">
                <UIcon
                  name="i-lucide-sparkles"
                  class="w-3.5 h-3.5 text-yellow-300"
                />
                <span class="font-semibold text-xs text-yellow-100">Member Benefits Active</span>
              </div>
              <h1 class="text-xl md:text-2xl font-bold">
                Welcome, {{ userName }}!
              </h1>
              <p class="text-white/80 text-sm mt-0.5">
                Earn BV/PV points on every purchase
              </p>
            </template>
            <template v-else>
              <h1 class="text-xl md:text-2xl font-bold">
                Shop Products
              </h1>
              <p class="text-white/80 text-sm mt-0.5">
                Discover top picks and filter by what you need
              </p>
            </template>
          </div>

          <!-- Quick Stats -->
          <div
            v-if="isLoggedIn"
            class="flex gap-3"
          >
            <div class="text-center bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
              <div class="text-lg font-bold text-yellow-300">
                BV/PV
              </div>
              <div class="text-[10px] text-white/70">
                Earn Points
              </div>
            </div>
            <div class="text-center bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
              <div class="text-lg font-bold text-yellow-300">
                Rewards
              </div>
              <div class="text-[10px] text-white/70">
                Every Order
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-3 md:px-4 py-4 min-h-[calc(100vh-10rem)]">
      <div class="flex gap-4 min-h-full">
        <div class="hidden lg:block shrink-0">
          <ShopProductFilterSidebar
            :price-range="priceRange"
            :filter-groups="filterGroups"
            :categories="categories"
            :loading="isLoading"
            :filter-config="{
              showSearch: true,
              showCategories: true,
              showPrice: true,
              showAvailability: true,
              showRating: true,
              showDynamicFilters: true
            }"
            @update:filters="handleFilterUpdate"
            @clear="handleFilterClear"
          />
        </div>
        <!-- Products Grid -->
        <main class="flex-1 min-w-0">
          <!-- Toolbar: Results count + Sort + Mobile Filter Toggle -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center gap-3">
              <!-- Mobile Filter Toggle -->
              <button
                class="lg:hidden flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                @click="openMobileFilters"
              >
                <UIcon
                  name="i-lucide-sliders-horizontal"
                  class="w-3.5 h-3.5"
                />
                Filters
                <span
                  v-if="activeFilterCount > 0"
                  class="px-1.5 py-0.5 text-[10px] bg-primary-500 text-white rounded-full"
                >
                  {{ activeFilterCount }}
                </span>
              </button>

              <div class="text-xs text-slate-600 dark:text-slate-400">
                <span class="font-semibold text-slate-900 dark:text-white">{{ totalProducts }}</span>
                products
              </div>
            </div>

            <div class="flex items-center gap-3">
              <!-- Sort Dropdown -->
              <div class="flex items-center gap-2">
                <span class="text-xs text-slate-600 dark:text-slate-400 hidden sm:inline">Sort:</span>
                <USelect
                  v-model="sortBy"
                  :items="sortOptions"
                  value-key="value"
                  label-key="label"
                  size="xs"
                  class="w-36"
                  @change="handleSortChange"
                />
              </div>

              <!-- View Toggle -->
              <div class="hidden md:flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <button class="p-1.5 bg-primary-100 dark:bg-primary-900/30 text-primary-600">
                  <UIcon
                    name="i-lucide-layout-grid"
                    class="w-3.5 h-3.5"
                  />
                </button>
                <button class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800">
                  <UIcon
                    name="i-lucide-list"
                    class="w-3.5 h-3.5 text-slate-400"
                  />
                </button>
              </div>
            </div>
          </div>

          <!-- Loading State (Initial) -->
          <div
            v-if="isLoading"
            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4"
          >
            <div
              v-for="i in 16"
              :key="i"
              class="bg-white dark:bg-slate-900 rounded-xl overflow-hidden animate-pulse shadow-sm"
            >
              <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
              <div class="p-3 space-y-2">
                <div class="h-3.5 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
                <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded w-1/3" />
              </div>
            </div>
          </div>

          <!-- Products Grid -->
          <div
            v-else-if="allProducts.length"
            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4"
          >
            <StoreProductCard
              v-for="product in allProducts"
              :key="product.slug"
              :product="product"
            />
          </div>

          <!-- Empty State -->
          <div
            v-else
            class="text-center py-12 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700"
          >
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
              <UIcon
                name="i-lucide-search-x"
                class="w-8 h-8 text-slate-400"
              />
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">
              No products found
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
              Try adjusting your filters
            </p>
            <UButton
              variant="outline"
              color="primary"
              size="sm"
              @click="handleFilterClear"
            >
              Clear Filters
            </UButton>
          </div>

          <!-- Load More Trigger (Infinite Scroll) -->
          <div
            ref="loadMoreTrigger"
            class="h-4"
          />

          <!-- Loading More Indicator -->
          <div
            v-if="isLoadingMore"
            class="flex justify-center py-6"
          >
            <div class="flex items-center gap-2 text-sm text-slate-500">
              <UIcon
                name="i-lucide-loader-2"
                class="w-5 h-5 animate-spin"
              />
              Loading more...
            </div>
          </div>

          <!-- End of Results -->
          <div
            v-if="!hasMore && allProducts.length > 0"
            class="text-center py-6"
          >
            <p class="text-sm text-slate-500 dark:text-slate-400">
              You've reached the end
            </p>
          </div>
        </main>
      </div>
    </div>

    <div class="lg:hidden">
      <ShopProductFilterSidebar
        ref="filterSidebarRef"
        :price-range="priceRange"
        :filter-groups="filterGroups"
        :categories="categories"
        :loading="isLoading"
        :filter-config="{
          showSearch: true,
          showCategories: true,
          showPrice: true,
          showAvailability: true,
          showRating: true,
          showDynamicFilters: true
        }"
        @update:filters="handleFilterUpdate"
        @clear="handleFilterClear"
      />
    </div>

    <!-- Mobile padding for bottom nav -->
    <div class="h-20 lg:hidden" />
  </div>
</template>
