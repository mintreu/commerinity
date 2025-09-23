<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">

    <!-- Flash Deals Hero -->
    <section class="relative bg-gradient-to-r from-red-500 via-pink-600 to-orange-600 text-white py-16 md:py-20 overflow-hidden">
      <!-- Background Animation -->
      <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full animate-pulse"></div>
        <div class="absolute bottom-20 right-20 w-24 h-24 bg-yellow-400/20 rounded-full animate-bounce"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white/5 rounded-full animate-ping"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-2 bg-yellow-400 text-black px-4 py-2 rounded-full font-bold text-sm mb-6 animate-pulse">
          <Icon name="mdi:flash" class="w-5 h-5" />
          LIMITED TIME FLASH SALE
        </div>

        <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 drop-shadow-2xl">
          <span class="bg-gradient-to-r from-yellow-300 to-white bg-clip-text text-transparent">
            MEGA FLASH DEALS
          </span>
        </h1>

        <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
          Unbelievable discounts up to <span class="font-bold text-yellow-300">{{ dealStats.avg_discount }}% OFF</span> on thousands of products!
        </p>

        <!-- Countdown Timer -->
        <div class="flex justify-center items-center gap-4 md:gap-8 mb-8">
          <div class="bg-black/30 backdrop-blur-md rounded-2xl px-4 py-6 text-center min-w-[80px]">
            <div class="text-3xl md:text-4xl font-black">{{ countdown.hours }}</div>
            <div class="text-xs md:text-sm opacity-80">HOURS</div>
          </div>
          <div class="text-4xl font-black animate-pulse">:</div>
          <div class="bg-black/30 backdrop-blur-md rounded-2xl px-4 py-6 text-center min-w-[80px]">
            <div class="text-3xl md:text-4xl font-black">{{ countdown.minutes }}</div>
            <div class="text-xs md:text-sm opacity-80">MINS</div>
          </div>
          <div class="text-4xl font-black animate-pulse">:</div>
          <div class="bg-black/30 backdrop-blur-md rounded-2xl px-4 py-6 text-center min-w-[80px]">
            <div class="text-3xl md:text-4xl font-black">{{ countdown.seconds }}</div>
            <div class="text-xs md:text-sm opacity-80">SECS</div>
          </div>
        </div>

        <p class="text-lg font-semibold mb-8">
          Sale ends in: <span class="text-yellow-300">{{ formatEndTime }}</span>
        </p>

        <!-- Stats -->
        <div class="flex justify-center gap-8 md:gap-12">
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold">{{ dealStats.total_deals }}+</div>
            <div class="text-sm opacity-80">Hot Deals</div>
          </div>
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold">{{ dealStats.avg_discount }}%</div>
            <div class="text-sm opacity-80">Avg Discount</div>
          </div>
          <div class="text-center">
            <div class="text-2xl md:text-3xl font-bold">{{ Math.floor(dealStats.customers_saved / 1000) }}K</div>
            <div class="text-sm opacity-80">Happy Customers</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filter & Sort Bar -->
    <section class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">

          <!-- Left: Results Count -->
          <div class="flex items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ totalDeals }} Flash Deals Available
            </h2>
            <div class="hidden md:flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
              Live deals updating every minute
            </div>
          </div>

          <!-- Right: Filters & Sort -->
          <div class="flex items-center gap-4">
            <!-- Category Filter -->
            <select
                v-model="selectedCategory"
                @change="applyFilters"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="">All Categories</option>
              <option v-for="cat in availableCategories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>

            <!-- Discount Filter -->
            <select
                v-model="selectedDiscount"
                @change="applyFilters"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="">All Discounts</option>
              <option value="50">50%+ Off</option>
              <option value="60">60%+ Off</option>
              <option value="70">70%+ Off</option>
              <option value="80">80%+ Off</option>
            </select>

            <!-- Sort -->
            <select
                v-model="sortBy"
                @change="applyFilters"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="discount_desc">Highest Discount</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="ending_soon">Ending Soon</option>
            </select>
          </div>
        </div>
      </div>
    </section>

    <!-- Flash Deals Grid -->
    <section class="py-8 md:py-12">
      <div class="max-w-7xl mx-auto px-4">

        <!-- Loading State -->
        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
          <div v-for="i in 15" :key="i" class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm animate-pulse">
            <div class="aspect-square bg-gray-200 dark:bg-gray-700"></div>
            <div class="p-4 space-y-3">
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <!-- Flash Deals Products -->
        <div v-else-if="flashDeals.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
          <div
              v-for="deal in flashDeals"
              :key="deal.product.url"
              class="group bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:scale-105 border border-gray-100 dark:border-gray-700 relative"
          >
            <!-- Deal Timer -->
            <div class="absolute top-2 left-2 z-10 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-bold flex items-center gap-1">
              <Icon name="mdi:timer" class="w-3 h-3" />
              {{ formatTimeLeft(deal.ends_at) }}
            </div>

            <!-- Discount Badge -->
            <div class="absolute top-2 right-2 z-10 bg-gradient-to-r from-orange-500 to-red-600 text-white px-2 py-1 rounded-md text-sm font-black">
              -{{ deal.discount_percentage }}%
            </div>

            <!-- Product Image -->
            <div class="relative aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
              <img
                  :src="deal.product.image || 'https://via.placeholder.com/300x300?text=No+Image'"
                  :alt="deal.product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                  loading="lazy"
                  @error="$event.target.src = 'https://via.placeholder.com/300x300?text=No+Image'"
              />

              <!-- Flash Effect -->
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 translate-x-full group-hover:translate-x-[-200%] transition-transform duration-1000 pointer-events-none"></div>

              <!-- Quick Actions -->
              <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2">
                <NuxtLink
                    :to="`/product/${deal.product.url}`"
                    class="bg-white text-gray-900 px-3 py-2 rounded-lg text-sm font-medium shadow-lg hover:bg-gray-100 transition-colors"
                >
                  View Details
                </NuxtLink>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
              <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2 leading-tight">
                {{ deal.product.name }}
              </h3>

              <!-- Pricing -->
              <div class="flex items-center gap-2 mb-2">
                <span class="text-lg font-bold text-green-600 dark:text-green-400">
                  ₹{{ formatPrice(deal.sale_price) }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400 line-through">
                  ₹{{ formatPrice(deal.original_price) }}
                </span>
              </div>

              <!-- Deal Progress Bar -->
              <div class="mb-3" v-if="deal.stock_left !== undefined">
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                  <span>{{ deal.sold || 0 }} sold</span>
                  <span>{{ deal.stock_left }} left</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div
                      class="bg-gradient-to-r from-red-500 to-orange-500 h-2 rounded-full transition-all duration-500"
                      :style="{ width: `${deal.progress_percentage || 0}%` }"
                  ></div>
                </div>
              </div>

              <!-- Add to Cart -->
              <button
                  @click="addToCart(deal.product.url)"
                  :disabled="deal.stock_left === 0"
                  class="w-full bg-gradient-to-r from-red-500 to-pink-600 text-white py-2 rounded-lg font-semibold text-sm hover:from-red-600 hover:to-pink-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <Icon v-if="deal.stock_left === 0" name="mdi:close" class="w-4 h-4" />
                <Icon v-else name="mdi:cart-plus" class="w-4 h-4" />
                {{ deal.stock_left === 0 ? 'Sold Out' : 'Add to Cart' }}
              </button>
            </div>
          </div>
        </div>

        <!-- No Deals Found -->
        <div v-else class="text-center py-16">
          <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
            <Icon name="mdi:flash-off" class="w-12 h-12 text-gray-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
            No Flash Deals Found
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">
            {{ error ? 'Failed to load deals. Please try again later.' : 'Try adjusting your filters or check back later for new deals' }}
          </p>
          <button
              @click="error ? loadFlashDeals() : clearFilters()"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
          >
            {{ error ? 'Retry' : 'Clear All Filters' }}
          </button>
        </div>

      </div>
    </section>

    <!-- Flash Deal Categories -->
    <section v-if="dealCategories.length" class="py-12 bg-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">
          Flash Deals by Category
        </h2>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <button
              v-for="category in dealCategories"
              :key="category.name"
              @click="filterByCategory(category.name)"
              :class="{
                'ring-2 ring-red-500': selectedCategory === category.name
              }"
              class="group p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl hover:shadow-lg transition-all duration-300 text-center"
          >
            <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
              <Icon :name="category.icon || 'mdi:tag'" class="w-6 h-6 text-white" />
            </div>
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-1">
              {{ category.name }}
            </h3>
            <p class="text-xs text-red-600 dark:text-red-400 font-medium">
              {{ category.deal_count }} deals
            </p>
          </button>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

// Composables
const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()

// Reactive data
const flashDeals = ref([])
const allDeals = ref([])
const dealCategories = ref([])
const loading = ref(true)
const error = ref(null)

// Filters
const selectedCategory = ref('')
const selectedDiscount = ref('')
const sortBy = ref('discount_desc')

// Timer state
const countdown = ref({
  hours: 23,
  minutes: 45,
  seconds: 12
})

// Deal stats
const dealStats = ref({
  total_deals: 0,
  avg_discount: 0,
  customers_saved: 0
})

// Computed properties
const availableCategories = computed(() => {
  return [...new Set(allDeals.value.map(deal => deal.category))].filter(Boolean).sort()
})

const totalDeals = computed(() => {
  return flashDeals.value.length
})

const formatEndTime = computed(() => {
  const now = new Date()
  const end = new Date(now.getTime() + (countdown.value.hours * 60 * 60 * 1000) +
      (countdown.value.minutes * 60 * 1000) +
      (countdown.value.seconds * 1000))
  return end.toLocaleDateString() + ' at ' + end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
})

// Methods
const loadFlashDeals = async () => {
  try {
    loading.value = true
    error.value = null

    // Build query params
    const params = new URLSearchParams()
    if (selectedCategory.value) params.append('category', selectedCategory.value)
    if (selectedDiscount.value) params.append('min_discount', selectedDiscount.value)
    if (sortBy.value) params.append('sort_by', sortBy.value)

    const queryString = params.toString()
    const url = `${config.public.apiBase}/flash-deals${queryString ? '?' + queryString : ''}`

    const { data: dealsData, error: dealsError } = await $fetch(url)

    if (dealsError) throw new Error(dealsError)

    if (dealsData?.data) {
      flashDeals.value = dealsData.data
      if (!selectedCategory.value && !selectedDiscount.value) {
        allDeals.value = dealsData.data // Store original data
      }
    }

  } catch (err) {
    console.error('Failed to load flash deals:', err)
    error.value = err.message || 'Failed to load deals'
    flashDeals.value = []
  } finally {
    loading.value = false
  }
}

const loadDealStats = async () => {
  try {
    const { data: statsData } = await $fetch(`${config.public.apiBase}/flash-deals/stats`)
    if (statsData) {
      dealStats.value = {
        total_deals: statsData.total_deals || 0,
        avg_discount: statsData.avg_discount || 0,
        customers_saved: statsData.customers_saved || 0
      }
    }
  } catch (err) {
    console.error('Failed to load deal stats:', err)
  }
}

const loadDealCategories = async () => {
  try {
    const categoriesData = await $fetch(`${config.public.apiBase}/flash-deals/categories`)
    if (Array.isArray(categoriesData)) {
      // Add default icons for categories
      const iconMap = {
        'Electronics': 'mdi:laptop',
        'Fashion': 'mdi:tshirt-crew',
        'Home & Living': 'mdi:home',
        'Home': 'mdi:home',
        'Beauty': 'mdi:face-woman',
        'Sports': 'mdi:dumbbell',
        'Books': 'mdi:book',
        'Grocery': 'mdi:cart',
        'Health': 'mdi:medical-bag',
        'Toys': 'mdi:toys'
      }

      dealCategories.value = categoriesData.map(cat => ({
        ...cat,
        icon: iconMap[cat.name] || 'mdi:tag'
      }))
    }
  } catch (err) {
    console.error('Failed to load deal categories:', err)
    dealCategories.value = []
  }
}

const applyFilters = async () => {
  // Update URL with current filters
  const query = {}
  if (selectedCategory.value) query.category = selectedCategory.value
  if (selectedDiscount.value) query.discount = selectedDiscount.value
  if (sortBy.value && sortBy.value !== 'discount_desc') query.sort = sortBy.value

  await router.push({ query })
  await loadFlashDeals()
}

const filterByCategory = async (categoryName) => {
  selectedCategory.value = categoryName
  await applyFilters()

  // Scroll to products section
  nextTick(() => {
    const productsSection = document.querySelector('.py-8.md\\:py-12')
    productsSection?.scrollIntoView({ behavior: 'smooth' })
  })
}

const clearFilters = async () => {
  selectedCategory.value = ''
  selectedDiscount.value = ''
  sortBy.value = 'discount_desc'

  await router.push({ query: {} })
  await loadFlashDeals()
}

const formatTimeLeft = (endsAt) => {
  if (!endsAt) return 'Expired'

  const now = new Date()
  const end = new Date(endsAt)
  const diff = end - now

  if (diff <= 0) return 'Expired'

  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

  if (hours > 0) return `${hours}h ${minutes}m`
  return `${minutes}m left`
}

const formatPrice = (price) => {
  if (typeof price === 'number') return price.toLocaleString()
  return price || 0
}

const addToCart = async (productUrl) => {
  try {
    // Implement add to cart logic with product URL
    console.log('Adding to cart:', productUrl)

    // You can make an API call here:
    // await $fetch(`${config.public.apiBase}/cart/add`, {
    //   method: 'POST',
    //   body: { product_url: productUrl, quantity: 1 }
    // })

    // Show success message
    // showNotification('Product added to cart!', 'success')
  } catch (err) {
    console.error('Failed to add to cart:', err)
    // showNotification('Failed to add product to cart', 'error')
  }
}

// Initialize filters from URL
const initFiltersFromURL = () => {
  const { category, discount, sort } = route.query

  if (category) selectedCategory.value = category
  if (discount) selectedDiscount.value = discount
  if (sort) sortBy.value = sort
}

// Countdown timer
let countdownInterval
const startCountdown = () => {
  countdownInterval = setInterval(() => {
    if (countdown.value.seconds > 0) {
      countdown.value.seconds--
    } else if (countdown.value.minutes > 0) {
      countdown.value.minutes--
      countdown.value.seconds = 59
    } else if (countdown.value.hours > 0) {
      countdown.value.hours--
      countdown.value.minutes = 59
      countdown.value.seconds = 59
    } else {
      // Timer ended, reload deals
      loadFlashDeals()
      countdown.value = { hours: 23, minutes: 59, seconds: 59 } // Reset timer
    }
  }, 1000)
}

// Lifecycle hooks
onMounted(async () => {
  // Initialize filters from URL
  initFiltersFromURL()

  // Load all data
  await Promise.all([
    loadFlashDeals(),
    loadDealStats(),
    loadDealCategories()
  ])

  // Start countdown timer
  startCountdown()
})

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})

// SEO
useSeoMeta({
  title: 'Flash Deals - Up to 80% Off | Limited Time Offers',
  description: 'Grab amazing flash deals with discounts up to 80% off. Limited time offers on electronics, fashion, home decor and more.',
  keywords: 'flash deals, discounts, offers, sale, limited time'
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
