<template>
  <!-- Enhanced Search Modal -->
  <teleport to="body">
    <transition name="search-modal">
      <div
          class="search-modal fixed inset-0 z-[100] flex items-start justify-center pt-20 px-4"
          @click="closeModal"
      >
        <!-- Modal Background -->
        <div class="modal-background absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <!-- Search Container -->
        <div
            class="search-container relative w-full max-w-2xl"
            @click.stop
            ref="searchContainer"
        >
          <!-- Search Box -->
          <div class="search-box bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-800/50 overflow-hidden">

            <!-- Search Header -->
            <div class="search-header p-6 border-b border-gray-200/50 dark:border-gray-700/50">
              <div class="flex items-center gap-4">
                <!-- Search Icon -->
                <div class="search-icon w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                  <Icon name="mdi:magnify" class="w-6 h-6 text-white" />
                </div>

                <!-- Search Input -->
                <div class="search-input-container flex-1 relative">
                  <input
                      v-model="searchQuery"
                      ref="searchInput"
                      type="text"
                      placeholder="Search products, categories, brands..."
                      class="search-input w-full px-4 py-3 bg-transparent text-xl font-semibold text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none"
                      @keyup.enter="performSearch"
                      @input="handleInput"
                  />

                  <!-- Search Actions -->
                  <div class="search-actions absolute right-0 top-1/2 transform -translate-y-1/2 flex items-center gap-2">
                    <!-- Clear Button -->
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="clear-btn w-8 h-8 bg-gray-100 dark:bg-gray-800 hover:bg-red-500 text-gray-500 dark:text-gray-400 hover:text-white rounded-lg flex items-center justify-center transition-all duration-300"
                        aria-label="Clear search"
                    >
                      <Icon name="mdi:close" class="w-4 h-4" />
                    </button>

                    <!-- Search Button -->
                    <button
                        @click="performSearch"
                        :disabled="!searchQuery.trim()"
                        class="search-btn px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-semibold rounded-xl transition-all duration-300 disabled:cursor-not-allowed"
                    >
                      <Icon name="mdi:arrow-right" class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <!-- Close Button -->
                <button
                    @click="closeModal"
                    class="close-btn w-12 h-12 bg-gray-100 dark:bg-gray-800 hover:bg-red-500 text-gray-600 dark:text-gray-400 hover:text-white rounded-2xl flex items-center justify-center transition-all duration-300 hover:scale-110"
                    aria-label="Close search"
                >
                  <Icon name="mdi:close" class="w-6 h-6" />
                </button>
              </div>
            </div>

            <!-- Search Content -->
            <div class="search-content max-h-96 overflow-y-auto" v-if="searchQuery.length > 0">

              <!-- Search Suggestions (Always show when typing) -->
              <div class="search-results p-6">
                <h3 class="results-header text-lg font-black text-gray-900 dark:text-white mb-4 flex items-center">
                  <Icon name="mdi:format-list-bulleted" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                  Search Suggestions
                </h3>

                <div class="results-grid space-y-3">
                  <!-- Search All Products -->
                  <div
                      class="result-item flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 rounded-2xl transition-all duration-300 cursor-pointer hover:scale-102"
                      @click="selectResult({ type: 'products', query: searchQuery })"
                  >
                    <div class="result-icon w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon name="mdi:shopping" class="w-6 h-6 text-white" />
                    </div>
                    <div class="result-info flex-1">
                      <h4 class="result-title font-bold text-gray-900 dark:text-white">{{ searchQuery }} Products</h4>
                      <p class="result-subtitle text-sm text-gray-600 dark:text-gray-400">Browse all related products</p>
                      <div class="result-tags mt-2 flex items-center gap-2">
                        <span class="tag px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-xs font-semibold rounded-full">Products</span>
                        <span class="tag px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-xs font-semibold rounded-full">Popular</span>
                      </div>
                    </div>
                    <Icon name="mdi:arrow-right" class="w-5 h-5 text-gray-400" />
                  </div>

                  <!-- Search in Categories -->
                  <div
                      v-for="category in topCategories"
                      :key="category.slug"
                      class="result-item flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/20 dark:hover:to-pink-900/20 rounded-2xl transition-all duration-300 cursor-pointer hover:scale-102"
                      @click="selectResult({ type: 'category', category: category.slug, query: searchQuery })"
                  >
                    <div class="result-icon w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon :name="category.icon" class="w-6 h-6 text-white" />
                    </div>
                    <div class="result-info flex-1">
                      <h4 class="result-title font-bold text-gray-900 dark:text-white">{{ searchQuery }} in {{ category.name }}</h4>
                      <p class="result-subtitle text-sm text-gray-600 dark:text-gray-400">{{ category.description }}</p>
                      <div class="result-tags mt-2 flex items-center gap-2">
                        <span class="tag px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 text-xs font-semibold rounded-full">{{ category.name }}</span>
                      </div>
                    </div>
                    <Icon name="mdi:arrow-right" class="w-5 h-5 text-gray-400" />
                  </div>

                  <!-- Search Deals -->
                  <div
                      class="result-item flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 dark:hover:from-orange-900/20 dark:hover:to-red-900/20 rounded-2xl transition-all duration-300 cursor-pointer hover:scale-102"
                      @click="selectResult({ type: 'deals', query: searchQuery })"
                  >
                    <div class="result-icon w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon name="mdi:tag" class="w-6 h-6 text-white" />
                    </div>
                    <div class="result-info flex-1">
                      <h4 class="result-title font-bold text-gray-900 dark:text-white">{{ searchQuery }} Deals</h4>
                      <p class="result-subtitle text-sm text-gray-600 dark:text-gray-400">Special offers and discounts</p>
                      <div class="result-tags mt-2 flex items-center gap-2">
                        <span class="tag px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 text-xs font-semibold rounded-full">Deals</span>
                        <span class="tag px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 text-xs font-semibold rounded-full">Discount</span>
                      </div>
                    </div>
                    <Icon name="mdi:arrow-right" class="w-5 h-5 text-gray-400" />
                  </div>

                  <!-- Search Blog -->
                  <div
                      class="result-item flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 dark:hover:from-green-900/20 dark:hover:to-emerald-900/20 rounded-2xl transition-all duration-300 cursor-pointer hover:scale-102"
                      @click="selectResult({ type: 'blog', query: searchQuery })"
                  >
                    <div class="result-icon w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon name="mdi:post" class="w-6 h-6 text-white" />
                    </div>
                    <div class="result-info flex-1">
                      <h4 class="result-title font-bold text-gray-900 dark:text-white">{{ searchQuery }} in Blog</h4>
                      <p class="result-subtitle text-sm text-gray-600 dark:text-gray-400">Articles and blog posts</p>
                      <div class="result-tags mt-2 flex items-center gap-2">
                        <span class="tag px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-semibold rounded-full">Blog</span>
                      </div>
                    </div>
                    <Icon name="mdi:arrow-right" class="w-5 h-5 text-gray-400" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Links -->
            <div v-else class="quick-links p-6">
              <h3 class="quick-links-header text-lg font-black text-gray-900 dark:text-white mb-4 flex items-center">
                <Icon name="mdi:flash" class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" />
                Quick Links
              </h3>

              <div class="links-grid grid grid-cols-1 sm:grid-cols-2 gap-3">
                <NuxtLink
                    v-for="(link, index) in quickLinks"
                    :key="`link-${index}`"
                    :to="link.to"
                    class="quick-link flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 rounded-2xl transition-all duration-300 hover:scale-102"
                    @click="closeModal"
                >
                  <div class="link-icon w-10 h-10 rounded-xl flex items-center justify-center" :class="link.iconBg">
                    <Icon :name="link.icon" class="w-5 h-5 text-white" />
                  </div>
                  <div class="link-info">
                    <h4 class="link-title font-bold text-gray-900 dark:text-white text-sm">{{ link.title }}</h4>
                    <p class="link-subtitle text-xs text-gray-600 dark:text-gray-400">{{ link.subtitle }}</p>
                  </div>
                </NuxtLink>
              </div>
            </div>

            <!-- Search Footer -->
            <div class="search-footer p-4 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50">
              <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                <div class="search-tips flex items-center gap-2">
                  <Icon name="mdi:information-outline" class="w-4 h-4" />
                  <span>Press <kbd class="kbd">Enter</kbd> to search</span>
                </div>
                <div class="search-shortcuts flex items-center gap-4">
                  <span class="flex items-center gap-1">
                    <kbd class="kbd">Esc</kbd> to close
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

// Composables
const router = useRouter()

// Props & Emits
const emit = defineEmits<{
  close: []
}>()

// State
const searchQuery = ref('')

// Refs
const searchContainer = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)

// Top Categories for search suggestions
const topCategories = [
  { name: 'Electronics', slug: 'electronics', icon: 'mdi:laptop', description: 'Electronic items matching your search' },
  { name: 'Fashion', slug: 'fashion', icon: 'mdi:tshirt-crew', description: 'Clothing and accessories' },
  { name: 'Groceries', slug: 'groceries', icon: 'mdi:cart', description: 'Food and daily essentials' }
]

// Quick Links Data
const quickLinks = [
  {
    to: '/categories',
    title: 'All Categories',
    subtitle: 'Browse all',
    icon: 'mdi:view-grid',
    iconBg: 'bg-gradient-to-r from-blue-500 to-indigo-500'
  },
  {
    to: '/blog',
    title: 'Blog',
    subtitle: 'Latest articles',
    icon: 'mdi:post',
    iconBg: 'bg-gradient-to-r from-purple-500 to-pink-500'
  },
  {
    to: '/deals',
    title: 'Special Deals',
    subtitle: 'Limited offers',
    icon: 'mdi:tag',
    iconBg: 'bg-gradient-to-r from-orange-500 to-red-500'
  },
  {
    to: '/brands',
    title: 'Top Brands',
    subtitle: 'Popular brands',
    icon: 'mdi:star',
    iconBg: 'bg-gradient-to-r from-yellow-500 to-orange-500'
  }
]

// Methods
const closeModal = () => {
  emit('close')
}

const clearSearch = () => {
  searchQuery.value = ''
  nextTick(() => searchInput.value?.focus())
}

const handleInput = () => {
  // Just for reactivity - suggestions show instantly
}

const performSearch = () => {
  if (!searchQuery.value.trim()) return

  // Navigate to full search page
  router.push({
    path: '/search',
    query: { q: searchQuery.value.trim() }
  })
  closeModal()
}

const selectResult = (result: any) => {
  const query = encodeURIComponent(result.query)

  switch (result.type) {
    case 'products':
      router.push(`/search?q=${query}`)
      break
    case 'category':
      router.push(`/search?q=${query}&category=${result.category}`)
      break
    case 'deals':
      router.push(`/search?q=${query}&filter=deals`)
      break
    case 'blog':
      router.push(`/search?q=${query}&filter=blog`)
      break
    default:
      router.push(`/search?q=${query}`)
  }

  closeModal()
}

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') closeModal()
}

// Lifecycle
onMounted(() => {
  nextTick(() => searchInput.value?.focus())
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
/* Keep all your original styles */
.search-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.search-container {
  animation: slideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.search-box {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.search-input {
  caret-color: #3b82f6;
}

.result-item:hover {
  transform: translateX(5px) scale(1.02);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.quick-link:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.kbd {
  @apply px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-xs font-semibold border border-gray-300 dark:border-gray-600;
}

.search-modal-enter-active,
.search-modal-leave-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-modal-enter-from,
.search-modal-leave-to {
  opacity: 0;
}

@media (max-width: 640px) {
  .search-container {
    margin: 0 1rem;
  }

  .links-grid {
    grid-template-columns: 1fr;
  }
}

.search-content {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175) transparent;
}

.search-content::-webkit-scrollbar {
  width: 6px;
}

.search-content::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175);
  border-radius: 3px;
}
</style>
