<script setup lang="ts">
/**
 * Deals Page - Products on Sale (Flash Deals Style)
 * Shows all products currently on sale with countdown timer and filters
 */

import type { Product } from '~/types/catalog'

definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const sanctumFetch = useSanctumFetch()
const toast = useToast()

// SEO
useComprehensiveSeo({
  title: 'Hot Deals - Limited Time Offers',
  description: 'Grab amazing deals with discounts on quality products. Limited time offers with fast delivery.',
  keywords: ['deals', 'discounts', 'offers', 'sale', 'limited time'],
  type: 'website'
})

// Cart composable
const { addToCart: addToCartComposable, cartCount } = useCart()

// Types
interface SaleStats {
  total_deals: number
  avg_discount: number
  ends_at: string | null
}

interface SaleProduct extends Product {
  sale_ends_at: string | null
}

// Filter state
const selectedCategory = ref('')
const selectedDiscount = ref('')
const sortBy = ref('discount_desc')
const currentPage = ref(1)

// Fetch deals
const dealsResponse = ref<{ success: boolean; data: { stats: SaleStats; items: SaleProduct[]; pagination: { current_page: number; last_page: number; per_page: number; total: number; has_more: boolean } } } | null>(null)
const dealsStatus = ref<'pending' | 'success' | 'error'>('pending')
const loadDeals = async () => {
  dealsStatus.value = 'pending'
  try {
    const queryParams = new URLSearchParams()
    queryParams.set('page', String(currentPage.value))
    if (selectedCategory.value) {
      queryParams.set('category', selectedCategory.value)
    }
    const url = `/api/catalog/on-deal?${queryParams.toString()}`
    dealsResponse.value = await sanctumFetch(url)
    dealsStatus.value = 'success'
  } catch (err) {
    dealsStatus.value = 'error'
    toast.add({
      title: 'Deals Load Failed',
      description: err instanceof Error ? err.message : 'Unable to load deals at the moment.',
      color: 'error',
      icon: 'i-lucide-alert-circle'
    })
  }
}

const categoriesData = ref<{ success: boolean; data: Array<{ name: string; slug: string; product_count: number }> } | null>(null)
const loadCategories = async () => {
  try {
    categoriesData.value = await sanctumFetch('/api/catalog/categories')
  } catch (err) {
    toast.add({
      title: 'Categories Load Failed',
      description: err instanceof Error ? err.message : 'Unable to load categories',
      color: 'warning',
      icon: 'i-lucide-alert-circle'
    })
  }
}

const deals = computed(() => dealsResponse.value?.data.items || [])
const stats = computed(() => dealsResponse.value?.data.stats || { total_deals: 0, avg_discount: 0, ends_at: null })
const pagination = computed(() => dealsResponse.value?.data.pagination)
const categories = computed(() => categoriesData.value?.data || [])

watch([currentPage, selectedCategory], () => {
  loadDeals()
}, { immediate: true })

onMounted(() => {
  loadCategories()
})

// Filter deals by discount
const filteredDeals = computed(() => {
  let filtered = [...deals.value]

  if (selectedDiscount.value) {
    const minDiscount = parseInt(selectedDiscount.value)
    filtered = filtered.filter(d => (d.discount_percent || 0) >= minDiscount)
  }

  // Sort
  switch (sortBy.value) {
    case 'discount_desc':
      filtered.sort((a, b) => (b.discount_percent || 0) - (a.discount_percent || 0))
      break
    case 'price_asc':
      filtered.sort((a, b) => a.price - b.price)
      break
    case 'price_desc':
      filtered.sort((a, b) => b.price - a.price)
      break
    case 'ending_soon':
      filtered.sort((a, b) => {
        if (!a.sale_ends_at) return 1
        if (!b.sale_ends_at) return -1
        return new Date(a.sale_ends_at).getTime() - new Date(b.sale_ends_at).getTime()
      })
      break
  }

  return filtered
})

// Countdown timer
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
let countdownInterval: ReturnType<typeof setInterval> | null = null

const updateCountdown = () => {
  if (!stats.value.ends_at) {
    countdown.value = { days: 0, hours: 23, minutes: 59, seconds: 59 }
    return
  }

  const now = new Date().getTime()
  const end = new Date(stats.value.ends_at).getTime()
  const diff = end - now

  if (diff <= 0) {
    countdown.value = { days: 0, hours: 0, minutes: 0, seconds: 0 }
    return
  }

  countdown.value = {
    days: Math.floor(diff / (1000 * 60 * 60 * 24)),
    hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
    minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
    seconds: Math.floor((diff % (1000 * 60)) / 1000)
  }
}

onMounted(() => {
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
})

// Format time left
const formatTimeLeft = (endsAt: string | null): string => {
  if (!endsAt) return 'Limited Time'

  const now = new Date().getTime()
  const end = new Date(endsAt).getTime()
  const diff = end - now

  if (diff <= 0) return 'Expired'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

  if (hours > 24) return `${Math.floor(hours / 24)}d ${hours % 24}h`
  if (hours > 0) return `${hours}h ${minutes}m`
  return `${minutes}m left`
}

// Add to cart
const addingToCart = ref<string | null>(null)

const addToCart = async (product: SaleProduct) => {
  if (!product.in_stock) return
  addingToCart.value = product.slug
  try {
    await addToCartComposable(product.slug, 1, {
      productName: product.name,
      productImage: product.image?.url
    })
  } catch (err: unknown) {
    const errorMessage = err instanceof Error ? err.message : 'Failed to add to cart'
    toast.add({ title: 'Error', description: errorMessage, color: 'error', icon: 'i-lucide-alert-circle' })
  } finally {
    addingToCart.value = null
  }
}

// Clear filters
const clearFilters = () => {
  selectedCategory.value = ''
  selectedDiscount.value = ''
  sortBy.value = 'discount_desc'
}

// Sort options
const sortOptions = [
  { label: 'Highest Discount', id: 'discount_desc' },
  { label: 'Price: Low to High', id: 'price_asc' },
  { label: 'Price: High to Low', id: 'price_desc' },
  { label: 'Ending Soon', id: 'ending_soon' }
]

// Discount options
const discountOptions = [
  { label: 'All Discounts', value: '' },
  { label: '20%+ Off', value: '20' },
  { label: '30%+ Off', value: '30' },
  { label: '50%+ Off', value: '50' },
  { label: '70%+ Off', value: '70' }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Hero Section with Countdown -->
    <section class="relative bg-gradient-to-r from-red-500 via-pink-600 to-orange-500 text-white py-16 md:py-20 overflow-hidden">
      <!-- Background Animation -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full animate-pulse" />
        <div class="absolute bottom-20 right-20 w-24 h-24 bg-yellow-400/20 rounded-full animate-bounce" />
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white/5 rounded-full animate-ping" />
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 bg-yellow-400 text-black px-4 py-2 rounded-full font-bold text-sm mb-6 animate-pulse">
          <UIcon
            name="i-lucide-zap"
            class="w-5 h-5"
          />
          LIMITED TIME SALE
        </div>

        <!-- Title -->
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 drop-shadow-2xl">
          <span class="bg-gradient-to-r from-yellow-300 to-white bg-clip-text text-transparent">
            HOT DEALS
          </span>
        </h1>

        <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
          Amazing discounts up to <span class="font-bold text-yellow-300">{{ stats.avg_discount }}% OFF</span> on premium products!
        </p>

        <!-- Countdown Timer -->
        <div
          v-if="countdown.days || countdown.hours || countdown.minutes || countdown.seconds"
          class="flex justify-center items-center gap-2 md:gap-4 mb-8"
        >
          <div
            v-if="countdown.days"
            class="bg-black/30 backdrop-blur-md rounded-2xl px-3 md:px-6 py-4 md:py-6 text-center min-w-[60px] md:min-w-[80px]"
          >
            <div class="text-2xl md:text-4xl font-black">
              {{ countdown.days }}
            </div>
            <div class="text-xs opacity-80">
              DAYS
            </div>
          </div>
          <div
            v-if="countdown.days"
            class="text-2xl md:text-4xl font-black animate-pulse"
          >
            :
          </div>
          <div class="bg-black/30 backdrop-blur-md rounded-2xl px-3 md:px-6 py-4 md:py-6 text-center min-w-[60px] md:min-w-[80px]">
            <div class="text-2xl md:text-4xl font-black">
              {{ String(countdown.hours).padStart(2, '0') }}
            </div>
            <div class="text-xs opacity-80">
              HOURS
            </div>
          </div>
          <div class="text-2xl md:text-4xl font-black animate-pulse">
            :
          </div>
          <div class="bg-black/30 backdrop-blur-md rounded-2xl px-3 md:px-6 py-4 md:py-6 text-center min-w-[60px] md:min-w-[80px]">
            <div class="text-2xl md:text-4xl font-black">
              {{ String(countdown.minutes).padStart(2, '0') }}
            </div>
            <div class="text-xs opacity-80">
              MINS
            </div>
          </div>
          <div class="text-2xl md:text-4xl font-black animate-pulse">
            :
          </div>
          <div class="bg-black/30 backdrop-blur-md rounded-2xl px-3 md:px-6 py-4 md:py-6 text-center min-w-[60px] md:min-w-[80px]">
            <div class="text-2xl md:text-4xl font-black">
              {{ String(countdown.seconds).padStart(2, '0') }}
            </div>
            <div class="text-xs opacity-80">
              SECS
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="flex justify-center gap-8 md:gap-12">
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold">
              {{ stats.total_deals }}+
            </div>
            <div class="text-sm opacity-80">
              Hot Deals
            </div>
          </div>
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold">
              {{ stats.avg_discount }}%
            </div>
            <div class="text-sm opacity-80">
              Avg Discount
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filter Bar -->
    <section class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <!-- Results Count -->
          <div class="flex items-center gap-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
              {{ filteredDeals.length }} Deals Available
            </h2>
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
              <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" />
              Live deals
            </div>
          </div>

          <!-- Filters -->
          <div class="flex flex-wrap items-center gap-3">
            <!-- Category -->
            <USelect
              v-model="selectedCategory"
              :items="[{ label: 'All Categories', id: '' }, ...categories.map(c => ({ label: c.name, id: c.slug }))]"
              value-key="id"
              placeholder="Category"
              class="w-40"
            />

            <!-- Discount -->
            <select
              v-model="selectedDiscount"
              class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
            >
              <option
                v-for="opt in discountOptions"
                :key="opt.value"
                :value="opt.value"
              >
                {{ opt.label }}
              </option>
            </select>

            <!-- Sort -->
            <USelect
              v-model="sortBy"
              :items="sortOptions"
              value-key="id"
              class="w-44"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Deals Grid -->
    <section class="py-8 md:py-12">
      <div class="max-w-7xl mx-auto px-4">
        <!-- Loading -->
        <div
          v-if="dealsStatus === 'pending'"
          class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6"
        >
          <div
            v-for="i in 10"
            :key="i"
            class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm animate-pulse"
          >
            <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
            <div class="p-4 space-y-3">
              <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
              <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
            </div>
          </div>
        </div>

        <!-- Deals -->
        <div
          v-else-if="filteredDeals.length"
          class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6"
        >
          <div
            v-for="deal in filteredDeals"
            :key="deal.slug"
            class="group bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-[1.02] border border-slate-100 dark:border-slate-700 relative"
          >
            <!-- Timer Badge -->
            <div class="absolute top-2 left-2 z-10 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-bold flex items-center gap-1">
              <UIcon
                name="i-lucide-timer"
                class="w-3 h-3"
              />
              {{ formatTimeLeft(deal.sale_ends_at) }}
            </div>

            <!-- Discount Badge -->
            <div
              v-if="deal.discount_percent"
              class="absolute top-2 right-2 z-10 bg-gradient-to-r from-orange-500 to-red-600 text-white px-2 py-1 rounded-md text-sm font-black"
            >
              -{{ deal.discount_percent }}%
            </div>

            <!-- Product Image -->
            <NuxtLink
              :to="`/shop/${deal.slug}`"
              class="block"
            >
              <div class="relative aspect-square bg-slate-100 dark:bg-slate-700 overflow-hidden">
                <img
                  v-if="deal.image"
                  :src="deal.image.url || deal.image"
                  :alt="deal.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
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

                <!-- Flash Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 translate-x-full group-hover:translate-x-[-200%] transition-transform duration-1000 pointer-events-none" />
              </div>
            </NuxtLink>

            <!-- Product Info -->
            <div class="p-4">
              <NuxtLink :to="`/shop/${deal.slug}`">
                <h3 class="font-semibold text-sm text-slate-900 dark:text-white mb-2 line-clamp-2 leading-tight hover:text-primary-500 transition-colors">
                  {{ deal.name }}
                </h3>
              </NuxtLink>

              <!-- Pricing -->
              <div class="flex items-center gap-2 mb-3">
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                  {{ deal.price_formatted }}
                </span>
                <span
                  v-if="deal.original_price_formatted"
                  class="text-sm text-slate-400 line-through"
                >
                  {{ deal.original_price_formatted }}
                </span>
              </div>

              <!-- Sale Name -->
              <div
                v-if="deal.sale_name"
                class="text-xs text-red-500 font-medium mb-3 flex items-center gap-1"
              >
                <UIcon
                  name="i-lucide-tag"
                  class="w-3 h-3"
                />
                {{ deal.sale_name }}
              </div>

              <!-- Add to Cart -->
              <UButton
                block
                :disabled="!deal.in_stock || addingToCart === deal.slug"
                :loading="addingToCart === deal.slug"
                :color="deal.in_stock ? 'primary' : 'neutral'"
                class="bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 border-0"
                @click="addToCart(deal)"
              >
                <UIcon
                  :name="deal.in_stock ? 'i-lucide-shopping-cart' : 'i-lucide-x'"
                  class="w-4 h-4"
                />
                {{ deal.in_stock ? 'Add to Cart' : 'Sold Out' }}
              </UButton>
            </div>
          </div>
        </div>

        <!-- No Deals -->
        <div
          v-else
          class="text-center py-16"
        >
          <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <UIcon
              name="i-lucide-zap-off"
              class="w-12 h-12 text-slate-400"
            />
          </div>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
            No Deals Found
          </h3>
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            {{ selectedCategory || selectedDiscount ? 'Try adjusting your filters' : 'Check back later for new deals' }}
          </p>
          <UButton
            v-if="selectedCategory || selectedDiscount"
            @click="clearFilters"
          >
            Clear Filters
          </UButton>
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
            @update:model-value="currentPage = $event"
          />
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-gradient-to-r from-violet-600 to-purple-600">
      <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <UIcon
          name="i-lucide-shopping-bag"
          class="w-12 h-12 mx-auto mb-4 text-yellow-300"
        />
        <h2 class="text-3xl md:text-4xl font-black mb-4">
          Don't Miss These Deals!
        </h2>
        <p class="text-lg text-white/90 mb-6">
          Shop now before these offers expire. New deals added daily!
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <UButton
            to="/shop"
            size="lg"
            color="white"
            class="font-bold"
          >
            <UIcon
              name="i-lucide-store"
              class="w-5 h-5"
            />
            Browse All Products
          </UButton>
          <UButton
            to="/categories"
            size="lg"
            variant="outline"
            color="white"
            class="font-bold border-2 bg-white/10 hover:bg-white/20"
          >
            <UIcon
              name="i-lucide-grid-3x3"
              class="w-5 h-5"
            />
            Shop by Category
          </UButton>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
