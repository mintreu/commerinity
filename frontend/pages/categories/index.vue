<template>
  <div class="relative z-10 w-full">

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 text-white overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px); background-size: 30px 30px;"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-24">
        <div ref="heroContent" class="text-center">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 mb-6">
            <Icon name="mdi:view-grid" class="w-5 h-5 mr-2" />
            <span class="font-semibold">Browse Categories</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6">
            <span class="block">Discover Amazing</span>
            <span class="block text-yellow-300">Product Categories</span>
          </h1>

          <p class="text-xl lg:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Explore our vast collection of products organized into convenient categories. Find exactly what you're looking for with ease.
          </p>

          <!-- Search Bar -->
          <div class="max-w-2xl mx-auto mb-12">
            <div class="relative">
              <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Search categories or products..."
                  class="w-full px-6 py-4 pl-14 pr-20 rounded-2xl bg-white/95 backdrop-blur-sm text-gray-900 placeholder-gray-500 text-lg font-medium shadow-xl focus:ring-4 focus:ring-white/25 focus:outline-none transition-all duration-300"
                  @input="handleSearch"
              />
              <Icon name="mdi:magnify" class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" />
              <button class="absolute right-3 top-1/2 -translate-y-1/2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2 rounded-xl font-bold hover:shadow-lg transition-all duration-300">
                Search
              </button>
            </div>
          </div>

          <!-- Quick Stats -->
          <div ref="quickStats" class="grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300" ref="categoryCounter">{{ categorySections.length }}</div>
              <div class="text-sm font-medium text-blue-100">Categories</div>
            </div>
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300" ref="productCounter">{{ totalProducts }}+</div>
              <div class="text-sm font-medium text-blue-100">Products</div>
            </div>
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300">100+</div>
              <div class="text-sm font-medium text-blue-100">Brands</div>
            </div>
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300">24/7</div>
              <div class="text-sm font-medium text-blue-100">Support</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filter Bar -->
    <section class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-gray-200 dark:border-slate-700 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <!-- View Toggle -->
          <div class="flex items-center gap-2 bg-gray-100 dark:bg-slate-800 rounded-lg p-1">
            <button
                @click="viewMode = 'grid'"
                :class="viewMode === 'grid' ? 'bg-white dark:bg-slate-700 shadow-sm' : ''"
                class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 transition-all duration-200"
            >
              <Icon name="mdi:view-grid" class="w-4 h-4" />
              <span class="hidden sm:inline">Grid</span>
            </button>
            <button
                @click="viewMode = 'list'"
                :class="viewMode === 'list' ? 'bg-white dark:bg-slate-700 shadow-sm' : ''"
                class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 transition-all duration-200"
            >
              <Icon name="mdi:view-list" class="w-4 h-4" />
              <span class="hidden sm:inline">List</span>
            </button>
          </div>

          <!-- Sort Options -->
          <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
            <select
                v-model="sortBy"
                class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="name">Name A-Z</option>
              <option value="name-desc">Name Z-A</option>
              <option value="products">Most Products</option>
              <option value="popular">Most Popular</option>
            </select>
          </div>

          <!-- Results Count -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing {{ filteredCategories.length }} of {{ categorySections.length }} categories
          </div>
        </div>
      </div>
    </section>

    <!-- Main Categories Display -->
    <section class="py-12 px-4 lg:px-8 bg-gray-50 dark:bg-slate-900">
      <div class="max-w-7xl mx-auto" ref="categoriesSection">

        <!-- Loading State -->
        <div v-if="pending" class="text-center py-16">
          <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-600 dark:text-gray-400">Loading categories...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-16">
          <div class="w-32 h-32 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-8">
            <Icon name="mdi:alert-circle" class="w-16 h-16 text-red-500" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Error Loading Categories</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-8">{{ error }}</p>
          <button @click="refresh()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200">
            Try Again
          </button>
        </div>

        <!-- Categories Display -->
        <template v-else-if="categorySections.length > 0">
          <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">All Categories</h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              {{ filteredCategories.length }} categories found
            </div>
          </div>

          <!-- Grid View -->
          <div v-if="viewMode === 'grid'" ref="categoriesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div
                v-for="category in filteredCategories"
                :key="category.url"
                class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-200 dark:border-slate-700 overflow-hidden"
            >
              <NuxtLink :to="`/category/${category.url}`" class="block">
                <!-- Category Image -->
                <div class="relative aspect-square bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 overflow-hidden">
                  <img
                      :src="category.thumbnail"
                      :alt="category.name"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                      loading="lazy"
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                  <!-- Product Count Badge -->
                  <div class="absolute top-3 right-3 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold">
                    {{ category.children?.length || 0 }} items
                  </div>

                  <!-- Quick Actions -->
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="bg-white/90 text-gray-900 px-6 py-2 rounded-lg font-bold shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                      View Category
                    </div>
                  </div>
                </div>

                <!-- Category Info -->
                <div class="p-6">
                  <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                    {{ category.name }}
                  </h3>

                  <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-4">
                    <span>{{ category.children?.length || 0 }} subcategories</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                      From ₹{{ formatPrice(getStartingPrice(category)) }}
                    </span>
                  </div>

                  <!-- Subcategories Preview -->
                  <div v-if="category.children?.length" class="flex flex-wrap gap-2 mb-4">
                    <span
                        v-for="child in category.children.slice(0, 3)"
                        :key="child.url"
                        class="px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 text-xs rounded-full"
                    >
                      {{ child.name }}
                    </span>
                    <span v-if="category.children.length > 3" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs rounded-full">
                      +{{ category.children.length - 3 }} more
                    </span>
                  </div>

                  <!-- Action Button -->
                  <div class="flex items-center justify-between">
                    <div class="flex items-center text-yellow-500">
                      <Icon name="mdi:star" class="w-4 h-4" />
                      <span class="text-sm font-medium ml-1">4.{{ Math.floor(Math.random() * 9) + 1 }}</span>
                    </div>

                    <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-semibold">
                      <span>Explore</span>
                      <Icon name="mdi:arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200" />
                    </div>
                  </div>
                </div>
              </NuxtLink>
            </div>
          </div>

          <!-- List View -->
          <div v-else ref="categoriesList" class="space-y-4">
            <div
                v-for="category in filteredCategories"
                :key="category.url"
                class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-slate-700 overflow-hidden"
            >
              <NuxtLink :to="`/category/${category.url}`" class="block">
                <div class="flex items-center p-6">
                  <!-- Category Image -->
                  <div class="flex-shrink-0 w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 rounded-xl overflow-hidden mr-6">
                    <img
                        :src="category.thumbnail"
                        :alt="category.name"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        loading="lazy"
                    />
                  </div>

                  <!-- Category Info -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between">
                      <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                          {{ category.name }}
                        </h3>

                        <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400 mb-3">
                          <span class="flex items-center gap-1">
                            <Icon name="mdi:folder-multiple" class="w-4 h-4" />
                            {{ category.children?.length || 0 }} subcategories
                          </span>
                          <span class="flex items-center gap-1">
                            <Icon name="mdi:currency-inr" class="w-4 h-4" />
                            Starting from ₹{{ formatPrice(getStartingPrice(category)) }}
                          </span>
                          <span class="flex items-center gap-1 text-yellow-500">
                            <Icon name="mdi:star" class="w-4 h-4" />
                            4.{{ Math.floor(Math.random() * 9) + 1 }}
                          </span>
                        </div>

                        <!-- Subcategories -->
                        <div v-if="category.children?.length" class="flex flex-wrap gap-2">
                          <span
                              v-for="child in category.children.slice(0, 5)"
                              :key="child.url"
                              class="px-3 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 text-xs rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-200"
                          >
                            {{ child.name }}
                          </span>
                          <span v-if="category.children.length > 5" class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs rounded-full font-semibold">
                            +{{ category.children.length - 5 }} more
                          </span>
                        </div>
                      </div>

                      <!-- Action Area -->
                      <div class="flex items-center gap-4 ml-6">
                        <div class="text-right">
                          <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            From ₹{{ formatPrice(getStartingPrice(category)) }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">Best prices</div>
                        </div>

                        <Icon name="mdi:arrow-right" class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform duration-200" />
                      </div>
                    </div>
                  </div>
                </div>
              </NuxtLink>
            </div>
          </div>
        </template>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-8">
            <Icon name="mdi:folder-search" class="w-16 h-16 text-gray-400 dark:text-gray-500" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No categories found</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
            {{ searchQuery ? 'Try adjusting your search terms.' : 'No categories available at the moment.' }}
          </p>
          <button v-if="searchQuery" @click="clearSearch" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors duration-200">
            Clear Search
          </button>
        </div>
      </div>
    </section>

    <!-- Newsletter Subscription -->
    <section class="bg-gradient-to-r from-purple-600 to-pink-600 text-white py-16">
      <div class="max-w-4xl mx-auto text-center px-4 lg:px-8">
        <h2 class="text-3xl font-bold mb-4">Stay Updated with New Categories</h2>
        <p class="text-lg text-purple-100 mb-8">Be the first to know when we add new product categories and exclusive deals.</p>

        <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
          <input
              type="email"
              placeholder="Enter your email"
              class="flex-1 px-6 py-3 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-4 focus:ring-white/25"
          />
          <button class="px-8 py-3 bg-white text-purple-600 font-bold rounded-xl hover:bg-gray-100 transition-colors duration-200">
            Subscribe
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'

// Types
interface ChildCategory {
  name: string
  url: string
  image: string
  starting_from_price: number
}

interface ParentCategory {
  name: string
  url: string
  thumbnail: string
  children: ChildCategory[]
}

const config = useRuntimeConfig()

// Refs for animations
const heroContent = ref<HTMLElement>()
const quickStats = ref<HTMLElement>()
const categoriesSection = ref<HTMLElement>()
const categoriesGrid = ref<HTMLElement>()
const categoriesList = ref<HTMLElement>()
const categoryCounter = ref<HTMLElement>()
const productCounter = ref<HTMLElement>()

// Reactive state
const viewMode = ref<'grid' | 'list'>('grid')
const sortBy = ref('name')
const searchQuery = ref('')

// Fetch categories data using useLazyFetch for better loading states
const { data: categorySections, pending, error, refresh } = await useLazyFetch<ParentCategory[]>(
    `${config.public.apiBase}/categories/with-products`,
    {
      key: 'categories-page-data',
      credentials: 'include',
      default: () => [],
      server: true
    }
)

// Computed properties
const totalProducts = computed(() => {
  if (!categorySections.value) return 0
  return categorySections.value.reduce((sum, category) => sum + (category.children?.length || 0), 0)
})

const filteredCategories = computed(() => {
  if (!categorySections.value) return []

  let filtered = [...categorySections.value]

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(category =>
        category.name.toLowerCase().includes(query) ||
        category.children?.some(child => child.name.toLowerCase().includes(query))
    )
  }

  // Sort
  switch (sortBy.value) {
    case 'name':
      filtered.sort((a, b) => a.name.localeCompare(b.name))
      break
    case 'name-desc':
      filtered.sort((a, b) => b.name.localeCompare(a.name))
      break
    case 'products':
      filtered.sort((a, b) => (b.children?.length || 0) - (a.children?.length || 0))
      break
    case 'popular':
      filtered.sort(() => Math.random() - 0.5)
      break
  }

  return filtered
})

// Methods
const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-IN').format(price)
}

const getStartingPrice = (category: ParentCategory): number => {
  if (category.children?.length) {
    const prices = category.children.map(child => child.starting_from_price).filter(Boolean)
    return prices.length > 0 ? Math.min(...prices) : 999
  }
  return 999
}

const handleSearch = () => {
  // Search is reactive through the computed property
}

const clearSearch = () => {
  searchQuery.value = ''
}

// GSAP Animations (simplified since tools are disabled)
let gsap: any = null
let ctx: any = null

const initAnimations = () => {
  if (typeof window !== 'undefined' && gsap) {
    // Animation code would go here
  }
}

onMounted(() => {
  // Load GSAP if needed
  if (typeof window !== 'undefined') {
    import('gsap').then(module => {
      gsap = module.default
      initAnimations()
    })
  }
})

onUnmounted(() => {
  if (ctx) {
    ctx.revert()
  }
})

// SEO
useSeoMeta({
  title: 'Browse Categories - Find Everything You Need',
  description: 'Explore our comprehensive collection of product categories. From electronics to fashion, find exactly what you\'re looking for with our organized category structure.',
})
</script>

<style scoped>
.category-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.category-card:hover {
  transform: translateY(-8px) scale(1.02);
}

@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
