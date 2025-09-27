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

              <!-- Loading State -->
              <div v-if="isLoading" class="loading-state p-8 text-center">
                <div class="loading-spinner w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mx-auto mb-4 animate-spin flex items-center justify-center">
                  <Icon name="mdi:loading" class="w-6 h-6 text-white animate-spin" />
                </div>
                <p class="text-gray-600 dark:text-gray-400 font-semibold">Searching...</p>
              </div>

              <!-- Search Results -->
              <div v-else-if="searchResults.length > 0" class="search-results p-6">
                <h3 class="results-header text-lg font-black text-gray-900 dark:text-white mb-4 flex items-center">
                  <Icon name="mdi:format-list-bulleted" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                  Search Results ({{ searchResults.length }})
                </h3>

                <div class="results-grid space-y-3">
                  <div
                      v-for="(result, index) in searchResults"
                      :key="index"
                      class="result-item flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20 rounded-2xl transition-all duration-300 cursor-pointer hover:scale-102"
                      @click="selectResult(result)"
                  >
                    <!-- Result Icon -->
                    <div class="result-icon w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                      <Icon :name="result.icon" class="w-6 h-6 text-white" />
                    </div>

                    <!-- Result Info -->
                    <div class="result-info flex-1">
                      <h4 class="result-title font-bold text-gray-900 dark:text-white">{{ result.title }}</h4>
                      <p class="result-subtitle text-sm text-gray-600 dark:text-gray-400">{{ result.subtitle }}</p>
                      <div class="result-tags mt-2 flex items-center gap-2">
                        <span
                            v-for="tag in result.tags"
                            :key="tag"
                            class="tag px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-xs font-semibold rounded-full"
                        >
                          {{ tag }}
                        </span>
                      </div>
                    </div>

                    <!-- Result Arrow -->
                    <Icon name="mdi:arrow-right" class="w-5 h-5 text-gray-400" />
                  </div>
                </div>
              </div>

              <!-- No Results -->
              <div v-else class="no-results p-8 text-center">
                <div class="no-results-icon w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full mx-auto mb-4 flex items-center justify-center">
                  <Icon name="mdi:magnify-remove-outline" class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">No results found</h3>
                <p class="text-gray-600 dark:text-gray-400">Try different keywords or browse our categories</p>
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
                    :key="index"
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

// GSAP imports (client-side only)
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props & Emits
const emit = defineEmits<{
  close: []
}>()

// State
const searchQuery = ref('')
const isLoading = ref(false)
const searchResults = ref<any[]>([])

// Refs
const searchContainer = ref<HTMLElement>()
const searchInput = ref<HTMLInputElement>()

let gsapContext: any = null
let debounceTimer: number | null = null

// Quick Links Data
const quickLinks = [
  {
    to: '/categories/electronics',
    title: 'Electronics',
    subtitle: 'Latest gadgets',
    icon: 'mdi:laptop',
    iconBg: 'bg-gradient-to-r from-blue-500 to-indigo-500'
  },
  {
    to: '/categories/fashion',
    title: 'Fashion',
    subtitle: 'Trendy clothing',
    icon: 'mdi:tshirt-crew',
    iconBg: 'bg-gradient-to-r from-purple-500 to-pink-500'
  },
  {
    to: '/categories/home',
    title: 'Home & Living',
    subtitle: 'Furniture & decor',
    icon: 'mdi:home',
    iconBg: 'bg-gradient-to-r from-green-500 to-emerald-500'
  },
  {
    to: '/categories/beauty',
    title: 'Beauty',
    subtitle: 'Cosmetics & care',
    icon: 'mdi:lipstick',
    iconBg: 'bg-gradient-to-r from-pink-500 to-rose-500'
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
  if (gsap && searchContainer.value) {
    gsap.to(searchContainer.value, {
      opacity: 0,
      y: -20,
      scale: 0.95,
      duration: 0.3,
      onComplete: () => emit('close')
    })
  } else {
    emit('close')
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  searchResults.value = []
  searchInput.value?.focus()
}

const handleInput = () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  debounceTimer = setTimeout(() => {
    if (searchQuery.value.length > 2) {
      performSearch()
    } else {
      searchResults.value = []
    }
  }, 300)
}

const performSearch = async () => {
  if (!searchQuery.value.trim()) return

  isLoading.value = true

  try {
    // Simulate API call - replace with your actual search API
    await new Promise(resolve => setTimeout(resolve, 800))

    // Mock search results
    searchResults.value = [
      {
        title: `${searchQuery.value} Products`,
        subtitle: 'Browse all related products',
        icon: 'mdi:shopping',
        tags: ['Products', 'Popular'],
        type: 'products',
        query: searchQuery.value
      },
      {
        title: `${searchQuery.value} in Electronics`,
        subtitle: 'Electronic items matching your search',
        icon: 'mdi:laptop',
        tags: ['Electronics', 'Tech'],
        type: 'category',
        category: 'electronics'
      },
      {
        title: `${searchQuery.value} Deals`,
        subtitle: 'Special offers and discounts',
        icon: 'mdi:tag',
        tags: ['Deals', 'Discount'],
        type: 'deals',
        query: searchQuery.value
      }
    ]

  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
  } finally {
    isLoading.value = false
  }
}

const selectResult = (result: any) => {
  // Handle result selection - navigate to appropriate page
  console.log('Selected result:', result)

  // Example navigation based on result type
  switch (result.type) {
    case 'products':
      navigateTo(`/search?q=${encodeURIComponent(result.query)}`)
      break
    case 'category':
      navigateTo(`/categories/${result.category}?q=${encodeURIComponent(result.query)}`)
      break
    case 'deals':
      navigateTo(`/deals?q=${encodeURIComponent(result.query)}`)
      break
    default:
      navigateTo(`/search?q=${encodeURIComponent(searchQuery.value)}`)
  }

  closeModal()
}

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closeModal()
  }
}

// Initialize animations
const initializeAnimations = () => {
  if (!process.client || !gsap || !searchContainer.value) return

  gsapContext = gsap.context(() => {
    // Entrance animation
    gsap.fromTo(searchContainer.value,
        { opacity: 0, y: -50, scale: 0.9 },
        {
          opacity: 1,
          y: 0,
          scale: 1,
          duration: 0.5,
          ease: 'back.out(1.7)'
        }
    )
  })
}

// Lifecycle
onMounted(() => {
  // Focus search input
  nextTick(() => {
    searchInput.value?.focus()
  })

  // Add keyboard listener
  document.addEventListener('keydown', handleKeydown)

  // Initialize animations
  setTimeout(() => {
    initializeAnimations()
  }, 50)
})

onUnmounted(() => {
  // Clean up
  document.removeEventListener('keydown', handleKeydown)

  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  if (gsapContext) {
    gsapContext.kill()
  }
})
</script>

<style scoped>
/* Performance optimizations */
.will-change-transform {
  will-change: transform;
}

/* Search Modal */
.search-modal {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Search Container */
.search-container {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.search-box {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Search Input */
.search-input {
  caret-color: #3b82f6;
}

.search-input:focus {
  transform: scale(1.02);
  transition: transform 0.3s ease;
}

/* Buttons */
.search-btn,
.clear-btn,
.close-btn {
  position: relative;
  overflow: hidden;
}

.search-btn::before,
.clear-btn::before,
.close-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.search-btn:hover::before,
.clear-btn:hover::before,
.close-btn:hover::before {
  opacity: 1;
}

/* Result Items */
.result-item:hover {
  transform: translateX(5px) scale(1.02);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Quick Links */
.quick-link:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Keyboard shortcuts */
.kbd {
  @apply px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-xs font-semibold border border-gray-300 dark:border-gray-600;
}

/* Loading spinner */
.loading-spinner::before {
  content: '';
  position: absolute;
  inset: -2px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6, #3b82f6);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  z-index: -1;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Transitions */
.search-modal-enter-active,
.search-modal-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-modal-enter-from,
.search-modal-leave-to {
  opacity: 0;
}

.search-modal-enter-from .search-container,
.search-modal-leave-to .search-container {
  opacity: 0;
  transform: translateY(-50px) scale(0.9);
}

/* Responsive design */
@media (max-width: 640px) {
  .search-container {
    margin: 0 1rem;
  }

  .links-grid {
    grid-template-columns: 1fr;
  }

  .search-header {
    flex-direction: column;
    gap: 1rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .search-box {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Focus states */
.search-input:focus,
.search-btn:focus-visible,
.clear-btn:focus-visible,
.close-btn:focus-visible,
.result-item:focus-visible,
.quick-link:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Smooth scrolling */
.search-content {
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175) transparent;
}

.search-content::-webkit-scrollbar {
  width: 6px;
}

.search-content::-webkit-scrollbar-track {
  background: transparent;
}

.search-content::-webkit-scrollbar-thumb {
  background-color: rgb(156 163 175);
  border-radius: 3px;
}

.search-content::-webkit-scrollbar-thumb:hover {
  background-color: rgb(107 114 128);
}
</style>
