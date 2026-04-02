<script setup lang="ts">
/**
 * Shop Page - Product Catalog
 * Displays products from the API with filters and categories
 */

import { getContextualApiError, getEmptyStateMessage } from '~/utils/api-error'

definePageMeta({
  layout: 'public'
})

useSeoMeta({
  title: 'Shop - VVIN',
  description: 'Explore our premium products. Quality products at great prices with Affiliate rewards.'
})

interface FilterOption {
  id: number
  value: string
  swatch?: string | null
  count?: number
}

interface FilterGroup {
  name: string
  options: FilterOption[]
}

interface CatalogProductCategory {
  id?: number | null
  uuid?: string | null
  name: string
  slug: string
}

interface CatalogProductImage {
  src: string
  srcset?: string | null
}

interface CatalogProduct {
  id: number
  name: string
  slug: string
  sku: string
  price: number
  price_formatted: string
  original_price?: number | null
  original_price_formatted?: string | null
  discount_percent?: number | null
  sale_name?: string | null
  category: CatalogProductCategory | null
  image: CatalogProductImage | null
  in_stock: boolean
  stock_quantity: number
  view_count: number
  bv: number
  pv: number
  reward_points: number
}

interface CatalogPaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from?: number | null
  to?: number | null
  path?: string
}

interface CatalogProductsResponse {
  success: boolean
  data: CatalogProduct[]
  meta?: CatalogPaginationMeta
  links?: Record<string, string | null>
}

const config = useRuntimeConfig()
const route = useRoute()

const parseNumberFromQuery = (value?: string | string[]): number | null => {
  if (!value) return null
  const normalized = Array.isArray(value) ? value[0] : value
  const parsed = Number(normalized)
  return Number.isFinite(parsed) ? parsed : null
}

const parseBooleanFromQuery = (value?: string | string[]): boolean => {
  if (!value) return false
  const normalized = String(Array.isArray(value) ? value[0] : value).toLowerCase()
  return normalized === '1' || normalized === 'true' || normalized === 'yes'
}

const parseRatingFromQuery = (value?: string | string[]): number | null => {
  const parsed = parseNumberFromQuery(value)
  if (parsed === null) return null
  const rounded = Math.floor(parsed)
  if (rounded < 1 || rounded > 5) return null
  return rounded
}

const parseFilterOptionsFromQuery = (): Record<string, number[]> => {
  const rawFilters = route.query.filters
  if (!rawFilters) return {}

  try {
    const parsed
      = typeof rawFilters === 'string'
        ? JSON.parse(rawFilters)
        : (rawFilters as Record<string, string | number[]>)

    return Object.fromEntries(
      Object.entries(parsed).map(([filterName, value]) => {
        if (Array.isArray(value)) {
          const ids = value.map(Number).filter(id => Number.isFinite(id))
          return [filterName, ids]
        }
        if (typeof value === 'string') {
          const ids = value
            .split(',')
            .map(id => Number(id.trim()))
            .filter(id => Number.isFinite(id))
          return [filterName, ids]
        }
        return [filterName, []]
      })
    )
  } catch (error) {
    console.warn('Failed to parse filters from query', error)
    return {}
  }
}

const cloneFilterMap = (filters: Record<string, number[]>) => {
  return Object.fromEntries(
    Object.entries(filters).map(([key, ids]) => [key, Array.isArray(ids) ? [...ids] : []])
  )
}

const buildQueryString = (params: Record<string, any>) => {
  const query = new URLSearchParams()
  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === '') continue
    query.set(key, String(value))
  }
  const queryString = query.toString()
  return queryString ? `?${queryString}` : ''
}

const normalizeQueryString = (value?: string | string[]) => {
  if (!value) return ''
  const normalized = Array.isArray(value) ? value[0] : value
  if (normalized === 'undefined' || normalized === 'null') return ''
  return normalized
}

// Filter state
const selectedCategory = ref(normalizeQueryString(route.query.category))
const selectedSort = ref(normalizeQueryString(route.query.sort) || 'popularity')
const searchQuery = ref(normalizeQueryString(route.query.search))
const currentPage = ref(Math.max(1, parseNumberFromQuery(route.query.page) || 1))
const priceMin = ref<number | null>(parseNumberFromQuery(route.query.min_price) ?? parseNumberFromQuery(route.query.price_min))
const priceMax = ref<number | null>(parseNumberFromQuery(route.query.max_price) ?? parseNumberFromQuery(route.query.price_max))
const minRating = ref<number | null>(parseRatingFromQuery(route.query.min_rating))
const hasBvOnly = ref(parseBooleanFromQuery(route.query.has_bv))
const hasPvOnly = ref(parseBooleanFromQuery(route.query.has_pv))
const selectedFilterOptions = ref<Record<string, number[]>>(parseFilterOptionsFromQuery())

// Mobile filter drawer
const showMobileFilters = ref(false)

// Auth / user type
const { isLoggedIn } = useSanctum()
const { isMember, isPromoter } = useUserType()
const canSeeAffiliateBenefits = computed(() => isMember.value || isPromoter.value)

// Sort options - Nuxt UI v4 format
const fallbackSortOptions = [
  { label: 'Popularity', id: 'popularity' },
  { label: 'Newest First', id: 'latest' },
  { label: 'Price: Low to High', id: 'price_asc' },
  { label: 'Price: High to Low', id: 'price_desc' },
  { label: 'Name: A to Z', id: 'name_asc' }
]

// API query params
const toPaisa = (value: number | null) => {
  if (value === null) return null
  return Math.round(value * 100)
}

const buildFilterPayload = (filters: Record<string, number[]>) => {
  const payload: Record<string, string> = {}
  for (const [filterName, optionIds] of Object.entries(filters)) {
    if (Array.isArray(optionIds) && optionIds.length > 0) {
      payload[filterName] = optionIds.join(',')
    }
  }
  return payload
}

const filtersQuery = computed(() => {
  const params: Record<string, any> = {}
  if (selectedCategory.value) params.category = selectedCategory.value
  if (searchQuery.value) params.search = searchQuery.value
  const minPrice = toPaisa(priceMin.value)
  const maxPrice = toPaisa(priceMax.value)
  if (minPrice !== null) params.min_price = minPrice
  if (maxPrice !== null) params.max_price = maxPrice
  if (minRating.value !== null) params.min_rating = minRating.value
  if (canSeeAffiliateBenefits.value && hasBvOnly.value) params.has_bv = 1
  if (canSeeAffiliateBenefits.value && hasPvOnly.value) params.has_pv = 1
  const payload = buildFilterPayload(selectedFilterOptions.value)
  if (Object.keys(payload).length > 0) {
    params.filters = JSON.stringify(payload)
  }
  return params
})

const filtersResponse = ref<{
  success: boolean
  data: {
    price_range: { min: number, max: number }
    sort_options: Array<{ value: string, label: string }>
    filter_options?: FilterGroup[]
  }
} | null>(null)
const filtersPending = ref(false)

const loadFilters = async () => {
  filtersPending.value = true
  try {
    const queryString = buildQueryString(filtersQuery.value)
    filtersResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/catalog/filters${queryString}`
    )
  } finally {
    filtersPending.value = false
  }
}

const availableFilters = computed(() => filtersResponse.value?.data)
const isFiltersLoading = computed(() => filtersPending.value)

const sortSelectOptions = computed(() => {
  const apiOptions = availableFilters.value?.sort_options?.map(option => ({
    label: option.label,
    id: option.value
  }))

  if (apiOptions && apiOptions.length > 0) {
    return apiOptions
  }

  return fallbackSortOptions
})

const queryParams = computed(() => {
  const params: Record<string, any> = {
    page: currentPage.value,
    sort: selectedSort.value
  }
  if (selectedCategory.value) params.category = selectedCategory.value
  if (searchQuery.value) params.search = searchQuery.value
  const minPrice = toPaisa(priceMin.value)
  const maxPrice = toPaisa(priceMax.value)
  if (minPrice !== null) params.min_price = minPrice
  if (maxPrice !== null) params.max_price = maxPrice
  if (minRating.value !== null) params.min_rating = minRating.value
  if (canSeeAffiliateBenefits.value && hasBvOnly.value) params.has_bv = 1
  if (canSeeAffiliateBenefits.value && hasPvOnly.value) params.has_pv = 1

  const filters = buildFilterPayload(selectedFilterOptions.value)
  if (Object.keys(filters).length > 0) {
    params.filters = JSON.stringify(filters)
  }

  return params
})

const productsResponse = ref<CatalogProductsResponse | null>(null)
const productsStatus = ref<'pending' | 'success' | 'error'>('pending')
const productsError = ref<string | null>(null)

const loadProducts = async () => {
  productsStatus.value = 'pending'
  productsError.value = null
  try {
    const queryString = buildQueryString(queryParams.value)
    productsResponse.value = await useSanctumFetch(
      `${config.public.apiBase}/api/catalog/products${queryString}`
    )
    productsStatus.value = 'success'
  } catch (err: unknown) {
    productsStatus.value = 'error'
    productsError.value = getContextualApiError(err, 'products').message
  }
}

const categoriesResponse = ref<{
  success: boolean
  data: Array<{
    id: number
    name: string
    slug: string
    product_count: number
    thumbnail: string | null
    children: Array<{ id: number, name: string, slug: string, product_count: number }>
  }>
} | null>(null)
const categoriesStatus = ref<'pending' | 'success' | 'error'>('pending')
const categoriesError = ref<string | null>(null)

const loadCategories = async () => {
  categoriesStatus.value = 'pending'
  categoriesError.value = null
  try {
    categoriesResponse.value = await useSanctumFetch(`${config.public.apiBase}/api/catalog/categories`)
    categoriesStatus.value = 'success'
  } catch (err: unknown) {
    categoriesResponse.value = null
    categoriesStatus.value = 'error'
    categoriesError.value = getContextualApiError(err, 'categories').message
  }
}

const products = computed<CatalogProduct[]>(() => productsResponse.value?.data ?? [])
const pagination = computed(() => {
  const meta = productsResponse.value?.meta
  if (!meta) {
    return null
  }

  return {
    current_page: meta.current_page,
    last_page: meta.last_page,
    per_page: meta.per_page,
    total: meta.total
  }
})
const categories = computed(() => {
  const cats = categoriesResponse.value?.data || []
  return [{ id: 0, name: 'All Products', slug: '', product_count: 0 }, ...cats]
})
const categoriesEmpty = computed(() =>
  categoriesStatus.value === 'success' && (categoriesResponse.value?.data?.length ?? 0) === 0
)

const activeFilterCount = computed(() => {
  let count = 0
  if (selectedCategory.value) count++
  if (selectedSort.value !== 'popularity') count++
  if (searchQuery.value) count++
  if (priceMin.value !== null || priceMax.value !== null) count++
  if (minRating.value !== null) count++
  if (canSeeAffiliateBenefits.value && hasBvOnly.value) count++
  if (canSeeAffiliateBenefits.value && hasPvOnly.value) count++
  count += Object.values(selectedFilterOptions.value).reduce((total, ids) => total + ids.length, 0)
  return count
})

const rupeeFormatter = new Intl.NumberFormat('en-IN', {
  style: 'currency',
  currency: 'INR',
  maximumFractionDigits: 2
})

const formatRupee = (value: number | null, fallback = '₹0') =>
  value === null ? fallback : rupeeFormatter.format(value)

const buildQueryObject = () => {
  const query: Record<string, string> = {}
  if (selectedCategory.value) query.category = selectedCategory.value
  if (selectedSort.value !== 'popularity') query.sort = selectedSort.value
  if (searchQuery.value) query.search = searchQuery.value
  if (priceMin.value !== null) query.min_price = String(toPaisa(priceMin.value) ?? 0)
  if (priceMax.value !== null) query.max_price = String(toPaisa(priceMax.value) ?? 0)
  if (minRating.value !== null) query.min_rating = String(minRating.value)
  if (canSeeAffiliateBenefits.value && hasBvOnly.value) query.has_bv = '1'
  if (canSeeAffiliateBenefits.value && hasPvOnly.value) query.has_pv = '1'
  if (currentPage.value > 1) query.page = String(currentPage.value)
  if (Object.keys(selectedFilterOptions.value).length > 0) {
    query.filters = JSON.stringify(selectedFilterOptions.value)
  }
  return query
}

const normalizeRouteQuery = () => {
  const normalized: Record<string, string> = {}
  for (const [key, value] of Object.entries(route.query)) {
    if (value === undefined) continue
    const normalizedValue = Array.isArray(value) ? value[0] : value
    if (typeof normalizedValue === 'string') {
      normalized[key] = normalizedValue
    }
  }
  return normalized
}

const updateRouteQuery = () => {
  const nextQuery = buildQueryObject()
  const currentQuery = normalizeRouteQuery()
  if (JSON.stringify(nextQuery) === JSON.stringify(currentQuery)) {
    return
  }
  navigateTo({ path: route.path, query: nextQuery }, { replace: true })
}

watch([selectedCategory, selectedSort, searchQuery, priceMin, priceMax, minRating, hasBvOnly, hasPvOnly], () => {
  currentPage.value = 1
  updateRouteQuery()
  loadProducts()
  loadFilters()
})

watch(selectedFilterOptions, () => {
  currentPage.value = 1
  updateRouteQuery()
  loadProducts()
  loadFilters()
}, { deep: true })

watch(currentPage, () => {
  updateRouteQuery()
  loadProducts()
})

onMounted(() => {
  loadCategories()
  loadFilters()
  loadProducts()
})

const handleCategoryChange = (slug: string) => {
  selectedCategory.value = slug
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const updateSelectedSort = (value: string) => {
  selectedSort.value = value
}

const updatePriceMin = (value: number | null) => {
  priceMin.value = value
}

const updatePriceMax = (value: number | null) => {
  priceMax.value = value
}

const updateMinRating = (value: number | null) => {
  minRating.value = value
}

const updateHasBvOnly = (value: boolean) => {
  hasBvOnly.value = value
}

const updateHasPvOnly = (value: boolean) => {
  hasPvOnly.value = value
}

const updateSelectedFilters = (value: Record<string, number[]>) => {
  selectedFilterOptions.value = cloneFilterMap(value)
}

const handleFiltersApplied = () => {
  showMobileFilters.value = false
}

// Clear all filters
const clearAllFilters = () => {
  selectedCategory.value = ''
  selectedSort.value = 'popularity'
  searchQuery.value = ''
  priceMin.value = null
  priceMax.value = null
  minRating.value = null
  hasBvOnly.value = false
  hasPvOnly.value = false
  selectedFilterOptions.value = {}
  currentPage.value = 1
  showMobileFilters.value = false
}

const removeFilterGroup = (filterName: string) => {
  if (!selectedFilterOptions.value[filterName]) return
  const updated = cloneFilterMap(selectedFilterOptions.value)
  delete updated[filterName]
  selectedFilterOptions.value = updated
}

// Cart functionality
const addingToCart = ref<string | null>(null)
const toast = useToast()

const addToCart = async (product: typeof products.value[0]) => {
  if (!product.in_stock) return

  if (!isLoggedIn.value) {
    navigateTo({
      path: '/auth/login',
      query: { redirect: route.fullPath }
    })
    return
  }

  addingToCart.value = product.slug

  try {
    const response = await useSanctumFetch<{ success: boolean, message: string }>(
      `${config.public.apiBase}/api/cart`,
      {
        method: 'POST',
        body: {
          product_slug: product.slug,
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
    const errorMessage = getContextualApiError(error, 'cart').message
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
      <AdsSlot
        placement="shop_top_banner"
        position-type="top_banner"
        variant="default"
        class="mb-6"
      />

      <!-- Filters Row -->
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Filters Sidebar (Desktop) -->
        <aside class="hidden lg:block w-72 shrink-0 space-y-4">
          <AdsSlot
            placement="shop_sidebar"
            position-type="sidebar"
            mode="stack"
            :limit="2"
            variant="compact"
          />

          <!-- Categories -->
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg p-4 sticky top-24">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
              <UIcon
                name="i-lucide-folder"
                class="w-5 h-5 text-primary-500"
              />
              Categories
            </h3>
            <p
              v-if="categoriesError"
              class="text-xs text-red-600 dark:text-red-400 mb-2"
            >
              {{ categoriesError }}
            </p>
            <p
              v-else-if="categoriesEmpty"
              class="text-xs text-slate-500 dark:text-slate-400 mb-2"
            >
              {{ getEmptyStateMessage('categories') }}
            </p>
            <ul class="space-y-1 max-h-64 overflow-y-auto">
              <li
                v-for="cat in categories"
                :key="cat.id"
              >
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
                  <span
                    v-if="cat.product_count"
                    class="text-xs opacity-70 ml-1"
                  >
                    ({{ cat.product_count }})
                  </span>
                </button>
              </li>
            </ul>
          </div>

          <!-- Price, Sort & Filter Options -->
          <StoreProductFilters
            :filters="availableFilters"
            :categories="categories"
            :selected-category="selectedCategory"
            :selected-sort="selectedSort"
            :price-min="priceMin"
            :price-max="priceMax"
            :min-rating="minRating"
            :has-bv-only="hasBvOnly"
            :has-pv-only="hasPvOnly"
            :can-see-affiliate-filters="canSeeAffiliateBenefits"
            :selected-filter-options="selectedFilterOptions"
            :show-sort="false"
            :loading="isFiltersLoading"
            @update:selected-sort="updateSelectedSort"
            @update:price-min="updatePriceMin"
            @update:price-max="updatePriceMax"
            @update:min-rating="updateMinRating"
            @update:has-bv-only="updateHasBvOnly"
            @update:has-pv-only="updateHasPvOnly"
            @update:selected-filter-options="updateSelectedFilters"
            @apply-filters="handleFiltersApplied"
            @clear-filters="clearAllFilters"
          />
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

            <!-- Filter & Sort Buttons -->
            <div class="flex items-center gap-2">
              <!-- Mobile Filter Button -->
              <UButton
                variant="outline"
                color="neutral"
                class="lg:hidden"
                @click="showMobileFilters = true"
              >
                <UIcon
                  name="i-lucide-sliders-horizontal"
                  class="w-4 h-4 mr-1"
                />
                Filters
                <span
                  v-if="activeFilterCount"
                  class="ml-2 px-1.5 py-0.5 text-xs rounded-full bg-primary-600 text-white font-semibold"
                >
                  {{ activeFilterCount }}
                </span>
              </UButton>

              <!-- Sort -->
              <USelect
                v-model="selectedSort"
                :items="sortSelectOptions"
                value-key="id"
                class="w-48"
              />
            </div>
          </div>

          <!-- Active Filters Display -->
          <div
            v-if="priceMin || priceMax || selectedCategory || minRating || hasBvOnly || hasPvOnly || Object.keys(selectedFilterOptions).length"
            class="flex flex-wrap gap-2 mb-4"
          >
            <span
              v-if="selectedCategory"
              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-medium"
            >
              {{ categories.find(c => c.slug === selectedCategory)?.name }}
              <button
                class="hover:text-primary-500"
                @click="selectedCategory = ''"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-4 h-4"
                />
              </button>
            </span>
            <span
              v-if="priceMin || priceMax"
              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-medium"
            >
              Price: {{ priceMin ? formatRupee(priceMin) : '₹0' }} - {{ priceMax ? formatRupee(priceMax) : 'Max' }}
              <button
                class="hover:text-emerald-500"
                @click="priceMin = null; priceMax = null"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-4 h-4"
                />
              </button>
            </span>
            <span
              v-if="minRating"
              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-sm font-medium"
            >
              Rating: {{ minRating }}+ stars
              <button
                class="hover:text-amber-500"
                @click="minRating = null"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-4 h-4"
                />
              </button>
            </span>
            <span
              v-if="canSeeAffiliateBenefits && hasBvOnly"
              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 text-sm font-medium"
            >
              BV only
              <button
                class="hover:text-cyan-500"
                @click="hasBvOnly = false"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-4 h-4"
                />
              </button>
            </span>
            <span
              v-if="canSeeAffiliateBenefits && hasPvOnly"
              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 text-sm font-medium"
            >
              PV only
              <button
                class="hover:text-sky-500"
                @click="hasPvOnly = false"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-4 h-4"
                />
              </button>
            </span>
            <!-- Display selected filter options -->
            <template
              v-for="(optionIds, filterName) in selectedFilterOptions"
              :key="filterName"
            >
              <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm font-medium">
                {{ filterName }}: {{ optionIds.length }} selected
                <button
                  class="hover:text-violet-500"
                  @click="removeFilterGroup(filterName)"
                >
                  <UIcon
                    name="i-lucide-x"
                    class="w-4 h-4"
                  />
                </button>
              </span>
            </template>
            <button
              class="text-sm text-red-500 hover:text-red-600 font-medium"
              @click="clearAllFilters"
            >
              Clear All
            </button>
          </div>

          <!-- Loading State -->
          <div
            v-if="productsStatus === 'pending'"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6"
          >
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

          <!-- Error State -->
          <div
            v-else-if="productsStatus === 'error'"
            class="text-center py-12 text-red-600 dark:text-red-400"
          >
            {{ productsError || getContextualApiError(null, 'products').message }}
          </div>

          <!-- Empty State -->
          <div
            v-else-if="productsStatus === 'success' && products.length === 0"
            class="text-center py-12 text-slate-600 dark:text-slate-300"
          >
            {{ getEmptyStateMessage('products') }}
          </div>

          <!-- Products Grid -->
          <div
            v-else-if="products.length"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6"
            data-testid="product-grid"
          >
            <NuxtLink
              v-for="product in products"
              :key="product.slug || (product as any).url"
              :to="`/shop/product/${product.slug || (product as any).url || ''}`"
              class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden group cursor-pointer"
              data-testid="product-card"
            >
              <!-- Image -->
              <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <img
                  v-if="product.image?.src"
                  :src="product.image.src"
                  :alt="product.name"
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

                <!-- Discount Badge (like Amazon/Flipkart) -->
                <div
                  v-if="product.discount_percent"
                  class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                >
                  {{ product.discount_percent }}% OFF
                </div>

                <!-- BV/PV Badge -->
                <div
                  v-if="canSeeAffiliateBenefits && product.bv > 0"
                  class="absolute top-2 right-2 bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded"
                >
                  {{ product.bv }} BV
                </div>
                <div
                  v-else-if="!isLoggedIn && product.reward_points > 0"
                  class="absolute top-2 right-2 bg-purple-600 text-white text-xs font-bold px-2 py-1 rounded flex items-center gap-1"
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

                <!-- Price (Amazon/Flipkart style) -->
                <div class="flex flex-wrap items-baseline gap-2 mb-2">
                  <span
                    class="text-lg font-bold text-slate-900 dark:text-white"
                    data-testid="product-price"
                  >
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

                <!-- Coins -->
                <div
                  v-if="canSeeAffiliateBenefits && product.reward_points > 0"
                  class="text-xs text-emerald-600 dark:text-emerald-400 mb-3"
                >
                  Earn {{ product.reward_points }} coins
                </div>
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

                <!-- Add to Cart -->
                <div class="flex flex-col gap-2">
                  <UButton
                    v-if="isLoggedIn"
                    block
                    :disabled="!product.in_stock || addingToCart === product.slug"
                    :loading="addingToCart === product.slug"
                    :color="product.in_stock ? 'primary' : 'neutral'"
                    @click.prevent="addToCart(product)"
                  >
                    <UIcon
                      name="i-lucide-shopping-cart"
                      class="w-4 h-4 mr-2"
                    />
                    {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                  </UButton>
                  <UButton
                    v-else
                    block
                    variant="outline"
                    color="primary"
                    @click.prevent="navigateTo(`/shop/product/${product.slug || (product as any).url || ''}`)"
                  >
                    View Product
                  </UButton>
                </div>
              </div>
            </NuxtLink>
          </div>

          <AdsSlot
            placement="shop_top_banner"
            position-type="grid_slot"
            mode="stack"
            :limit="2"
            variant="compact"
            class="mt-6"
          />

          <!-- Empty State -->
          <div
            v-if="productsStatus === 'success' && products.length === 0"
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

  <USlideover
    v-model:open="showMobileFilters"
    side="right"
    class="lg:hidden"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h3 class="text-base font-semibold">
          Filters
          <span
            v-if="activeFilterCount"
            class="ml-2 text-xs text-slate-500"
          >
            ({{ activeFilterCount }})
          </span>
        </h3>
        <UButton
          variant="ghost"
          size="sm"
          icon="i-lucide-x"
          @click="showMobileFilters = false"
        />
      </div>
    </template>

    <template #body>
      <div class="flex-1 overflow-y-auto p-4 space-y-6">
        <div>
          <h4 class="font-semibold text-slate-800 dark:text-slate-200 mb-3 text-sm uppercase tracking-wide">
            Categories
          </h4>
          <div class="flex flex-wrap gap-2 max-h-64 overflow-y-auto">
            <button
              v-for="cat in categories"
              :key="cat.id"
              :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-all',
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

        <StoreProductFilters
          :filters="availableFilters"
          :categories="categories"
          :selected-category="selectedCategory"
          :selected-sort="selectedSort"
          :price-min="priceMin"
          :price-max="priceMax"
          :min-rating="minRating"
          :has-bv-only="hasBvOnly"
          :has-pv-only="hasPvOnly"
          :can-see-affiliate-filters="canSeeAffiliateBenefits"
          :selected-filter-options="selectedFilterOptions"
          :show-sort="false"
          :loading="isFiltersLoading"
          @update:selected-sort="updateSelectedSort"
          @update:price-min="updatePriceMin"
          @update:price-max="updatePriceMax"
          @update:min-rating="updateMinRating"
          @update:has-bv-only="updateHasBvOnly"
          @update:has-pv-only="updateHasPvOnly"
          @update:selected-filter-options="updateSelectedFilters"
          @apply-filters="handleFiltersApplied"
          @clear-filters="clearAllFilters"
        />
      </div>
    </template>
  </USlideover>
</template>
