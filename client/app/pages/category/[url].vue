<script setup lang="ts">
/**
 * Category Page - Premium Storefront Category View
 * Dynamic category page with hero, subcategory slider, and product grid
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

const sortOptions = [
  { label: 'Popularity', value: 'popularity' },
  { label: 'Newest First', value: 'latest' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Name: A to Z', value: 'name_asc' }
]

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
    useComprehensiveSeo({
      title: category.value.name,
      description: category.value.description || `Browse ${category.value.name} products`,
      image: category.value.banner || category.value.thumbnail || undefined,
      type: 'website'
    })
  }
})

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

const clearPriceFilter = () => {
  priceMin.value = undefined
  priceMax.value = undefined
}

// Subcategory slider
const childSlider = ref<HTMLElement | null>(null)
const scrollChildren = (direction: 'left' | 'right') => {
  if (!childSlider.value) return
  const amount = childSlider.value.clientWidth * 0.7
  childSlider.value.scrollBy({ left: direction === 'right' ? amount : -amount, behavior: 'smooth' })
}

// Mobile filter sheet
const showMobileFilters = ref(false)
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Loading State -->
    <div v-if="status === 'pending'">
      <!-- Hero skeleton -->
      <div class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-violet-950/80 to-slate-950">
        <div class="mx-auto max-w-7xl px-4 md:px-6 py-10 md:py-16">
          <div class="animate-pulse">
            <div class="h-3 w-40 bg-white/10 rounded mb-4" />
            <div class="h-10 w-72 bg-white/10 rounded mb-3" />
            <div class="h-4 w-96 bg-white/10 rounded" />
          </div>
        </div>
      </div>
      <UContainer class="py-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="i in 8" :key="i" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden animate-pulse">
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
    <div v-else-if="error" class="py-20">
      <UContainer>
        <div class="text-center max-w-md mx-auto">
          <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
            <UIcon name="i-lucide-alert-circle" class="w-10 h-10 text-red-500" />
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Category Not Found</h3>
          <p class="text-slate-500 dark:text-slate-400 mb-6">The category you're looking for doesn't exist or has been moved.</p>
          <div class="flex gap-3 justify-center">
            <NuxtLink to="/categories">
              <UButton color="primary">Browse Categories</UButton>
            </NuxtLink>
            <NuxtLink to="/shop/products">
              <UButton color="neutral" variant="outline">All Products</UButton>
            </NuxtLink>
          </div>
        </div>
      </UContainer>
    </div>

    <!-- Main Content -->
    <template v-else-if="category">
      <!-- Premium Hero -->
      <section class="relative overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0">
          <img
            v-if="category.banner"
            :src="category.banner"
            :alt="category.name"
            class="w-full h-full object-cover"
          >
          <div v-else class="w-full h-full bg-gradient-to-br from-slate-950 via-violet-950/80 to-slate-950" />
          <!-- Overlay -->
          <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70" />
          <!-- Dot pattern -->
          <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;" />
        </div>

        <UContainer class="relative z-10 py-10 md:py-14 lg:py-18">
          <!-- Breadcrumb -->
          <nav class="flex items-center gap-2 text-xs text-slate-300/70 mb-5 flex-wrap">
            <NuxtLink to="/" class="hover:text-white transition-colors">Home</NuxtLink>
            <UIcon name="i-lucide-chevron-right" class="w-3 h-3" />
            <NuxtLink to="/categories" class="hover:text-white transition-colors">Categories</NuxtLink>
            <template v-if="category.ancestors?.length">
              <template v-for="ancestor in category.ancestors" :key="ancestor.slug">
                <UIcon name="i-lucide-chevron-right" class="w-3 h-3" />
                <NuxtLink :to="`/category/${ancestor.slug}`" class="hover:text-white transition-colors">{{ ancestor.name }}</NuxtLink>
              </template>
            </template>
            <UIcon name="i-lucide-chevron-right" class="w-3 h-3" />
            <span class="text-violet-300 font-medium">{{ category.name }}</span>
          </nav>

          <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <!-- Left -->
            <div>
              <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight text-white">
                {{ category.name }}
              </h1>
              <p v-if="category.description" class="mt-3 text-sm md:text-base text-slate-300 max-w-xl leading-relaxed">
                {{ category.description }}
              </p>
              <p v-else class="mt-3 text-sm md:text-base text-slate-400 max-w-xl">
                Explore our premium {{ category.name.toLowerCase() }} collection
              </p>
            </div>

            <!-- Right: Stats + Cart -->
            <div class="flex items-center gap-3 flex-wrap">
              <div v-if="pagination" class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 backdrop-blur-sm px-4 py-2.5">
                <UIcon name="i-lucide-package" class="w-4 h-4 text-violet-400" />
                <span class="text-sm font-medium text-slate-300">{{ pagination.total }} Products</span>
              </div>
              <div v-if="category.children?.length" class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 backdrop-blur-sm px-4 py-2.5">
                <UIcon name="i-lucide-layers" class="w-4 h-4 text-fuchsia-400" />
                <span class="text-sm font-medium text-slate-300">{{ category.children.length }} Subcategories</span>
              </div>
              <NuxtLink
                to="/cart"
                class="relative flex items-center gap-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 hover:bg-white/20 px-4 py-2.5 text-sm font-medium text-white transition-all"
              >
                <UIcon name="i-lucide-shopping-cart" class="w-4 h-4" />
                <span class="hidden sm:inline">Cart</span>
                <span
                  v-if="cartCount > 0"
                  class="absolute -top-2 -right-2 min-w-5 h-5 px-1 bg-fuchsia-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                >
                  {{ cartCount > 99 ? '99+' : cartCount }}
                </span>
              </NuxtLink>
            </div>
          </div>

          <!-- Search Bar -->
          <div class="mt-6 max-w-xl">
            <div class="relative">
              <UIcon name="i-lucide-search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 z-10" />
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="`Search in ${category.name}...`"
                class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/10 backdrop-blur-sm border border-white/15 text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-400/50 transition-all"
              >
            </div>
          </div>
        </UContainer>

        <!-- Bottom Fade -->
        <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent" />
      </section>

      <!-- Subcategories Slider -->
      <div v-if="category.children && category.children.length" class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <UContainer class="py-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Browse {{ category.name }}</h2>
            <div class="hidden lg:flex items-center gap-2">
              <button
                class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:text-violet-600 hover:border-violet-300 transition-all"
                @click="scrollChildren('left')"
              >
                <UIcon name="i-lucide-chevron-left" class="w-4 h-4" />
              </button>
              <button
                class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:text-violet-600 hover:border-violet-300 transition-all"
                @click="scrollChildren('right')"
              >
                <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div ref="childSlider" class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x snap-mandatory">
            <NuxtLink
              v-for="child in category.children"
              :key="child.slug"
              :to="`/category/${child.slug}`"
              class="group flex-shrink-0 snap-start"
            >
              <div class="w-32 md:w-40 rounded-xl border border-slate-200/80 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 overflow-hidden hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <div class="aspect-[4/3] relative overflow-hidden bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900/20 dark:to-fuchsia-900/20">
                  <img
                    v-if="child.thumbnail"
                    :src="child.thumbnail"
                    :alt="child.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                  >
                  <div v-else class="w-full h-full flex items-center justify-center">
                    <UIcon name="i-lucide-folder-open" class="w-8 h-8 text-violet-400/40" />
                  </div>
                  <div class="absolute top-1.5 right-1.5 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm rounded-full px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300">
                    {{ child.product_count }}
                  </div>
                </div>
                <div class="px-3 py-2.5">
                  <h3 class="text-xs md:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                    {{ child.name }}
                  </h3>
                </div>
              </div>
            </NuxtLink>
          </div>
        </UContainer>
      </div>

      <!-- Products Section -->
      <UContainer class="py-6 md:py-8">
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Desktop Sidebar Filters -->
          <aside class="hidden lg:block w-60 shrink-0">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sticky top-24">
              <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <UIcon name="i-lucide-sliders-horizontal" class="w-4 h-4 text-violet-500" />
                Filters
              </h3>

              <!-- Sort -->
              <div class="mb-5">
                <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sort By</h4>
                <div class="space-y-1">
                  <button
                    v-for="opt in sortOptions"
                    :key="opt.value"
                    class="w-full text-left px-3 py-2 rounded-lg text-sm transition-all"
                    :class="selectedSort === opt.value
                      ? 'bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 font-semibold'
                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'"
                    @click="selectedSort = opt.value"
                  >
                    {{ opt.label }}
                  </button>
                </div>
              </div>

              <!-- Price Range -->
              <div>
                <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Price Range</h4>
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <input
                      v-model.number="priceMin"
                      type="number"
                      placeholder="Min"
                      class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                    >
                    <span class="text-slate-400 text-xs">to</span>
                    <input
                      v-model.number="priceMax"
                      type="number"
                      placeholder="Max"
                      class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/50"
                    >
                  </div>
                  <button
                    v-if="priceMin !== undefined || priceMax !== undefined"
                    class="text-xs text-violet-600 dark:text-violet-400 hover:text-violet-700 font-medium"
                    @click="clearPriceFilter"
                  >
                    Clear price filter
                  </button>
                </div>
              </div>
            </div>
          </aside>

          <!-- Main Products -->
          <div class="flex-1 min-w-0">
            <!-- Top Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
              <div class="flex items-center gap-3">
                <span v-if="pagination" class="text-sm text-slate-600 dark:text-slate-400">
                  Showing <span class="font-semibold text-slate-900 dark:text-white">{{ products.length }}</span> of <span class="font-semibold text-slate-900 dark:text-white">{{ pagination.total }}</span> products
                </span>
                <!-- Active filters -->
                <div v-if="searchQuery || priceMin !== undefined || priceMax !== undefined" class="flex items-center gap-2">
                  <span
                    v-if="searchQuery"
                    class="inline-flex items-center gap-1 text-xs bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 px-2.5 py-1 rounded-full"
                  >
                    "{{ searchQuery }}"
                    <button class="hover:text-red-500 transition-colors" @click="searchQuery = ''">
                      <UIcon name="i-lucide-x" class="w-3 h-3" />
                    </button>
                  </span>
                  <span
                    v-if="priceMin !== undefined || priceMax !== undefined"
                    class="inline-flex items-center gap-1 text-xs bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 px-2.5 py-1 rounded-full"
                  >
                    {{ priceMin || '0' }} - {{ priceMax || '...' }}
                    <button class="hover:text-red-500 transition-colors" @click="clearPriceFilter">
                      <UIcon name="i-lucide-x" class="w-3 h-3" />
                    </button>
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <!-- Mobile filter button -->
                <button
                  class="lg:hidden flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300 hover:border-violet-300 transition-all"
                  @click="showMobileFilters = !showMobileFilters"
                >
                  <UIcon name="i-lucide-sliders-horizontal" class="w-4 h-4" />
                  Filters
                </button>

                <!-- Sort (mobile/tablet) -->
                <USelect
                  v-model="selectedSort"
                  :items="sortOptions"
                  class="w-44 lg:hidden"
                />
              </div>
            </div>

            <!-- Mobile Filters Dropdown -->
            <div
              v-if="showMobileFilters"
              class="lg:hidden mb-5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4"
            >
              <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Price Range</h4>
              <div class="flex items-center gap-2">
                <input
                  v-model.number="priceMin"
                  type="number"
                  placeholder="Min"
                  class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                >
                <span class="text-slate-400 text-xs">to</span>
                <input
                  v-model.number="priceMax"
                  type="number"
                  placeholder="Max"
                  class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                >
              </div>
              <div class="mt-3 flex gap-2">
                <UButton size="sm" color="primary" block @click="showMobileFilters = false">Apply</UButton>
                <UButton size="sm" color="neutral" variant="outline" block @click="clearPriceFilter(); showMobileFilters = false">Clear</UButton>
              </div>
            </div>

            <!-- Products Grid -->
            <div
              v-if="products.length"
              class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4"
            >
              <div
                v-for="product in products"
                :key="product.slug"
                class="group bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col"
              >
                <NuxtLink :to="`/shop/product/${product.slug || (product as any).url || ''}`" class="flex-1 flex flex-col">
                  <!-- Image -->
                  <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
                    <img
                      v-if="product.image?.src"
                      :src="product.image.thumbnail || product.image.src"
                      :alt="product.image.alt || product.name"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                      loading="lazy"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <UIcon name="i-lucide-package" class="w-12 h-12 text-slate-300 dark:text-slate-600" />
                    </div>

                    <!-- Out of Stock -->
                    <div v-if="!product.in_stock" class="absolute inset-0 bg-black/50 flex items-center justify-center backdrop-blur-[1px]">
                      <span class="bg-slate-900/90 text-white px-3 py-1.5 rounded-lg font-medium text-xs">Out of Stock</span>
                    </div>

                    <!-- Badges -->
                    <div class="absolute top-2 left-2 flex flex-col gap-1.5">
                      <div
                        v-if="product.discount_percent"
                        class="bg-gradient-to-r from-red-500 to-pink-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow"
                      >
                        -{{ product.discount_percent }}%
                      </div>
                      <div
                        v-if="product.sale_name"
                        class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow"
                      >
                        {{ product.sale_name }}
                      </div>
                    </div>

                    <!-- Right badges -->
                    <div class="absolute top-2 right-2">
                      <div
                        v-if="canSeeAffiliateBenefits && product.bv > 0"
                        class="bg-emerald-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow"
                      >
                        {{ product.bv }} BV
                      </div>
                      <div
                        v-else-if="!isLoggedIn && product.reward_points > 0"
                        class="bg-gradient-to-r from-violet-600 to-fuchsia-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow flex items-center gap-0.5"
                      >
                        <UIcon name="i-lucide-coins" class="w-3 h-3" />
                        Coins
                      </div>
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="p-3 md:p-4 flex-1 flex flex-col">
                    <p v-if="product.category" class="text-[11px] text-violet-600 dark:text-violet-400 font-medium mb-1">
                      {{ product.category.name }}
                    </p>
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm line-clamp-2 mb-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                      {{ product.name }}
                    </h3>

                    <div class="mt-auto">
                      <!-- Price -->
                      <div class="flex flex-wrap items-baseline gap-1.5 mb-1">
                        <span class="text-base md:text-lg font-bold text-slate-900 dark:text-white">{{ product.price_formatted }}</span>
                        <span v-if="product.original_price_formatted" class="text-xs text-slate-400 line-through">{{ product.original_price_formatted }}</span>
                      </div>

                      <!-- Reward info -->
                      <div
                        v-if="canSeeAffiliateBenefits && product.reward_points > 0"
                        class="text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1"
                      >
                        <UIcon name="i-lucide-coins" class="w-3 h-3" />
                        Earn {{ product.reward_points }} coins
                      </div>
                      <div
                        v-else-if="!isLoggedIn && product.reward_points > 0"
                        class="text-[11px] text-violet-600 dark:text-violet-400 flex items-center gap-1"
                      >
                        <UIcon name="i-lucide-coins" class="w-3 h-3" />
                        Sign in to earn coins
                      </div>
                    </div>
                  </div>
                </NuxtLink>

                <!-- Add to Cart -->
                <div class="px-3 pb-3 md:px-4 md:pb-4">
                  <button
                    :disabled="!product.in_stock || addingToCart === product.slug"
                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300"
                    :class="product.in_stock
                      ? 'bg-violet-600 hover:bg-violet-700 text-white shadow-sm hover:shadow-md'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed'"
                    @click.prevent="addToCart(product)"
                  >
                    <UIcon
                      v-if="addingToCart === product.slug"
                      name="i-lucide-loader-2"
                      class="w-4 h-4 animate-spin"
                    />
                    <UIcon v-else name="i-lucide-shopping-cart" class="w-4 h-4" />
                    {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
              <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                <UIcon name="i-lucide-package-x" class="w-10 h-10 text-slate-400" />
              </div>
              <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No products found</h3>
              <p class="text-slate-500 dark:text-slate-400 mb-6">
                {{ searchQuery ? `No products match "${searchQuery}"` : 'No products available in this category yet' }}
              </p>
              <UButton
                v-if="searchQuery || priceMin !== undefined || priceMax !== undefined"
                color="primary"
                @click="searchQuery = ''; clearPriceFilter()"
              >
                Clear Filters
              </UButton>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="flex justify-center mt-8">
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

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
