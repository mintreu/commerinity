<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-blue-900 dark:to-indigo-900">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 dark:bg-purple-400/5 rounded-full blur-3xl opacity-50 animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8">

      <!-- Enhanced Search Header with Search Box -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl space-y-4 sm:space-y-6">

        <!-- Top Row: Title, Info, Back Button -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
          <div class="flex items-center gap-3 sm:gap-4 w-full sm:w-auto">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:magnify" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
            <div class="flex-1 min-w-0">
              <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-purple-600 dark:from-white dark:via-blue-400 dark:to-purple-400 bg-clip-text text-transparent">
                Search Results
              </h1>
              <p v-if="searchQuery" class="text-sm sm:text-base text-slate-600 dark:text-slate-400 truncate">
                Results for "<span class="font-semibold">{{ searchQuery }}</span>"
                <span v-if="categoryFilter" class="text-purple-600 dark:text-purple-400"> in {{ categoryFilter }}</span>
                <span v-if="filter === 'deals'" class="text-orange-600 dark:text-orange-400"> (Deals)</span>
                <span v-if="filter === 'blog'" class="text-green-600 dark:text-green-400"> (Blog)</span>
              </p>
            </div>
          </div>

          <button
              @click="goBack"
              class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 text-sm sm:text-base w-full sm:w-auto justify-center"
          >
            <Icon name="mdi:arrow-left" class="w-4 h-4" />
            <span>Back</span>
          </button>
        </div>

        <!-- Search Box & Filters Row -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-3 sm:gap-4">

          <!-- Search Box -->
          <div class="flex-1 w-full">
            <div class="relative">
              <Icon name="mdi:magnify" class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-slate-400" />
              <input
                  v-model="newSearchQuery"
                  type="text"
                  placeholder="Refine your search..."
                  class="w-full pl-10 sm:pl-12 pr-20 sm:pr-24 py-2.5 sm:py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm sm:text-base text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                  @keyup.enter="performNewSearch"
              />
              <button
                  v-if="newSearchQuery"
                  @click="clearNewSearch"
                  class="absolute right-14 sm:right-16 top-1/2 -translate-y-1/2 w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors"
              >
                <Icon name="mdi:close" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
              </button>
              <button
                  @click="performNewSearch"
                  :disabled="!newSearchQuery.trim()"
                  class="absolute right-1.5 sm:right-2 top-1/2 -translate-y-1/2 px-2 sm:px-3 py-1 sm:py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:from-slate-400 disabled:to-slate-500 text-white rounded-lg transition-all disabled:cursor-not-allowed"
              >
                <Icon name="mdi:arrow-right" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Results Count & Sort -->
          <div class="flex items-center gap-2 sm:gap-4 flex-wrap w-full lg:w-auto">
            <!-- Results Count -->
            <div class="flex items-center gap-2 px-3 sm:px-4 py-2 bg-green-50 dark:bg-green-900/20 rounded-xl flex-1 lg:flex-none">
              <Icon name="mdi:check-circle" class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
              <span class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm whitespace-nowrap">
                Found <span class="font-bold text-slate-900 dark:text-white">{{ totalResults }}</span> results
              </span>
            </div>

            <!-- Sort Dropdown -->
            <div class="flex items-center gap-2 flex-1 lg:flex-none">
              <label class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap hidden sm:inline">Sort:</label>
              <select
                  v-model="sortBy"
                  @change="handleSort"
                  class="flex-1 lg:flex-none px-3 sm:px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
              >
                <option value="relevance">Relevance</option>
                <option value="latest">Newest First</option>
                <option value="oldest">Oldest First</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Global Loader -->
      <GlobalLoader v-if="isLoading" />

      <!-- Results Content -->
      <div v-else-if="hasResults" class="space-y-4 sm:space-y-6">

        <!-- Tabs -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-2xl sm:rounded-3xl p-1.5 sm:p-2 shadow-xl">
          <div class="flex gap-1.5 sm:gap-2 overflow-x-auto scrollbar-hide">
            <button
                @click="activeTab = 'all'"
                class="px-4 sm:px-6 py-2 sm:py-3 rounded-lg sm:rounded-xl font-semibold whitespace-nowrap transition-all duration-300 text-xs sm:text-base"
                :class="activeTab === 'all'
                ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
            >
              All ({{ totalResults }})
            </button>
            <button
                v-if="productCount > 0"
                @click="activeTab = 'products'"
                class="px-4 sm:px-6 py-2 sm:py-3 rounded-lg sm:rounded-xl font-semibold whitespace-nowrap transition-all duration-300 text-xs sm:text-base"
                :class="activeTab === 'products'
                ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
            >
              <Icon name="mdi:shopping" class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline-block mr-1" />
              Products ({{ productCount }})
            </button>
            <button
                v-if="categoryCount > 0"
                @click="activeTab = 'categories'"
                class="px-4 sm:px-6 py-2 sm:py-3 rounded-lg sm:rounded-xl font-semibold whitespace-nowrap transition-all duration-300 text-xs sm:text-base"
                :class="activeTab === 'categories'
                ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
            >
              <Icon name="mdi:view-grid" class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline-block mr-1" />
              Categories ({{ categoryCount }})
            </button>
            <button
                v-if="postCount > 0"
                @click="activeTab = 'posts'"
                class="px-4 sm:px-6 py-2 sm:py-3 rounded-lg sm:rounded-xl font-semibold whitespace-nowrap transition-all duration-300 text-xs sm:text-base"
                :class="activeTab === 'posts'
                ? 'bg-gradient-to-r from-orange-600 to-amber-600 text-white shadow-lg'
                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
            >
              <Icon name="mdi:post" class="w-3.5 h-3.5 sm:w-4 sm:h-4 inline-block mr-1" />
              Posts ({{ postCount }})
            </button>
          </div>
        </div>

        <!-- Results Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
          <div
              v-for="(result, index) in filteredResults"
              :key="`${result.type}-${result.url}-${index}`"
              class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 cursor-pointer group"
              @click="viewResult(result)"
          >
            <!-- Result Image/Icon -->
            <div class="relative w-full h-40 sm:h-48 bg-slate-100 dark:bg-slate-700 rounded-xl sm:rounded-2xl mb-3 sm:mb-4 overflow-hidden">
              <img
                  v-if="result.thumbnail || result.display_image"
                  :src="result.thumbnail || result.display_image"
                  :alt="result.name || result.title"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
              />
              <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <Icon :name="getResultIcon(result.type)" class="w-12 h-12 sm:w-16 sm:h-16 text-white" />
              </div>

              <!-- Type Badge -->
              <div class="absolute top-2 sm:top-3 right-2 sm:right-3 px-2 sm:px-3 py-1 rounded-full text-white text-xs font-bold shadow-lg uppercase" :class="getTypeBadgeClass(result.type)">
                {{ result.type || 'Product' }}
              </div>
            </div>

            <!-- Result Content -->
            <div>
              <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                {{ result.name || result.title }}
              </h3>

              <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mb-3 line-clamp-2">
                {{ result.description || result.excerpt || 'No description available' }}
              </p>

              <!-- Meta Info -->
              <div class="flex items-center flex-wrap gap-2 mb-3">
                <span v-if="result.sku" class="text-xs text-slate-500 dark:text-slate-400">SKU: {{ result.sku }}</span>
                <span v-if="result.category?.name" class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 text-xs font-semibold rounded-full">
                  {{ result.category.name }}
                </span>
                <span v-else-if="result.categories && result.categories.length" class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 text-xs font-semibold rounded-full">
                  {{ result.categories[0].name }}
                </span>
              </div>

              <!-- Price & Action -->
              <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
                <span v-if="result.price" class="text-lg sm:text-xl font-bold text-blue-600 dark:text-blue-400">
                  {{ result.price }}
                </span>
                <span v-else class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                  View Details
                </span>

                <button class="px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg sm:rounded-xl font-semibold transition-all duration-300 group-hover:scale-105">
                  <Icon name="mdi:arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 flex-wrap">
          <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 sm:px-4 py-2 bg-white/80 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-700 text-sm"
          >
            <Icon name="mdi:chevron-left" class="w-4 h-4 sm:w-5 sm:h-5" />
          </button>

          <button
              v-for="page in paginationRange"
              :key="page"
              @click="changePage(page)"
              class="px-3 sm:px-4 py-2 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 text-sm"
              :class="currentPage === page
              ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg'
              : 'bg-white/80 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'"
          >
            {{ page }}
          </button>

          <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 sm:px-4 py-2 bg-white/80 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-700 text-sm"
          >
            <Icon name="mdi:chevron-right" class="w-4 h-4 sm:w-5 sm:h-5" />
          </button>
        </div>
      </div>

      <!-- 🎨 Awesome No Results Fallback -->
      <div v-else class="flex items-center justify-center py-12 sm:py-16">
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-2xl sm:rounded-3xl p-8 sm:p-12 shadow-2xl text-center max-w-md mx-auto">

          <!-- Animated SVG Illustration -->
          <div class="relative w-48 h-48 sm:w-64 sm:h-64 mx-auto mb-6 sm:mb-8">
            <svg viewBox="0 0 200 200" class="w-full h-full animate-float">
              <!-- Background Circle -->
              <circle cx="100" cy="100" r="80" fill="url(#grad1)" opacity="0.1"/>
              <defs>
                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" />
                  <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1" />
                </linearGradient>
              </defs>

              <!-- Magnifying Glass -->
              <circle cx="80" cy="80" r="30" stroke="url(#grad1)" stroke-width="6" fill="none" class="animate-pulse"/>
              <line x1="102" y1="102" x2="130" y2="130" stroke="url(#grad1)" stroke-width="6" stroke-linecap="round" class="animate-pulse" style="animation-delay: 0.2s;"/>

              <!-- Question Marks -->
              <text x="75" y="90" font-size="24" fill="#3B82F6" opacity="0.6" class="animate-bounce">?</text>
              <text x="140" y="60" font-size="18" fill="#8B5CF6" opacity="0.5" class="animate-bounce" style="animation-delay: 0.3s;">?</text>
              <text x="50" y="140" font-size="18" fill="#8B5CF6" opacity="0.5" class="animate-bounce" style="animation-delay: 0.6s;">?</text>
            </svg>
          </div>

          <!-- Text Content -->
          <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mb-3 sm:mb-4">
            <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Oops! Nothing Found
            </span>
          </h2>

          <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mb-6 sm:mb-8 leading-relaxed">
            <span v-if="searchQuery" class="block mb-2">
              We couldn't find any results for <span class="font-bold text-slate-900 dark:text-white">"{{ searchQuery }}"</span>
            </span>
            <span class="block">Try using different keywords or check out our popular categories below</span>
          </p>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <button
                @click="goBack"
                class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:scale-105 text-sm sm:text-base"
            >
              <Icon name="mdi:arrow-left" class="w-4 h-4" />
              <span>Go Back</span>
            </button>

            <button
                @click="router.push('/categories')"
                class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-semibold transition-all duration-300 hover:scale-105 shadow-lg text-sm sm:text-base"
            >
              <Icon name="mdi:view-grid" class="w-4 h-4" />
              <span>Browse Categories</span>
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import GlobalLoader from '~/components/GlobalLoader.vue'

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()

const isLoading = useState('pageLoading', () => false)
const allResults = ref<any[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = ref(12)
const sortBy = ref('relevance')
const activeTab = ref('all')
const newSearchQuery = ref('')

const searchQuery = computed(() => route.query.q as string || '')
const categoryFilter = computed(() => route.query.category as string || '')
const filter = computed(() => route.query.filter as string || '')

const productResults = computed(() => allResults.value.filter(r => r.type === 'product'))
const categoryResults = computed(() => allResults.value.filter(r => r.type === 'category'))
const postResults = computed(() => allResults.value.filter(r => r.type === 'post'))

const productCount = computed(() => productResults.value.length)
const categoryCount = computed(() => categoryResults.value.length)
const postCount = computed(() => postResults.value.length)

const totalResults = computed(() => allResults.value.length)
const hasResults = computed(() => totalResults.value > 0)

const filteredResults = computed(() => {
  switch (activeTab.value) {
    case 'products': return productResults.value
    case 'categories': return categoryResults.value
    case 'posts': return postResults.value
    default: return allResults.value
  }
})

const paginationRange = computed(() => {
  const range = []
  const maxButtons = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxButtons / 2))
  let end = Math.min(totalPages.value, start + maxButtons - 1)

  if (end - start < maxButtons - 1) {
    start = Math.max(1, end - maxButtons + 1)
  }

  for (let i = start; i <= end; i++) {
    range.push(i)
  }

  return range
})

const performSearch = async () => {
  if (!searchQuery.value) {
    allResults.value = []
    return
  }

  isLoading.value = true

  try {
    const params: Record<string, any> = {
      search: searchQuery.value,
      page: currentPage.value,
      limit: perPage.value,
      sort: sortBy.value
    }

    if (categoryFilter.value) params.category = categoryFilter.value
    if (filter.value) params.filter = filter.value

    const response = await $fetch(`${config.public.apiBase}/search`, {
      method: 'GET',
      params,
      credentials: 'include'
    })

    if (response && response.data) {
      allResults.value = Array.isArray(response.data) ? response.data : []
      totalPages.value = response.meta?.last_page || 1
    } else {
      allResults.value = []
      totalPages.value = 1
    }

  } catch (error: any) {
    console.error('❌ Search error:', error)
    allResults.value = []
    totalPages.value = 1
  } finally {
    isLoading.value = false
  }
}

const performNewSearch = () => {
  if (!newSearchQuery.value.trim()) return
  router.push({ path: '/search', query: { q: newSearchQuery.value.trim() } })
}

const clearNewSearch = () => {
  newSearchQuery.value = ''
}

const getResultIcon = (type: string) => {
  const icons: Record<string, string> = {
    product: 'mdi:shopping',
    category: 'mdi:view-grid',
    post: 'mdi:post',
    default: 'mdi:file-document'
  }
  return icons[type] || icons.default
}

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    product: 'bg-gradient-to-r from-blue-600 to-purple-600',
    category: 'bg-gradient-to-r from-purple-600 to-pink-600',
    post: 'bg-gradient-to-r from-orange-600 to-amber-600',
    default: 'bg-gradient-to-r from-slate-600 to-slate-700'
  }
  return classes[type] || classes.default
}

const viewResult = (result: any) => {
  if (result.type === 'category') {
    router.push(`/category/${result.url}`)
  } else if (result.type === 'post') {
    router.push(`/blog/${result.url}`)
  } else {
    router.push(`/product/${result.url}`)
  }
}

const changePage = (page: number) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  performSearch()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleSort = () => {
  currentPage.value = 1
  performSearch()
}

const goBack = () => {
  router.back()
}

watch(() => route.query, () => {
  currentPage.value = 1
  activeTab.value = 'all'
  newSearchQuery.value = ''
  performSearch()
}, { deep: true })

onMounted(async () => {
  await performSearch()
})
</script>

<style scoped>
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
