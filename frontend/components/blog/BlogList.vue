<template>
  <div class="relative z-10 w-full">

    <!-- Elegant Hero Section with Category Info -->
    <section class="relative bg-gradient-to-br from-gray-900 via-slate-800 to-gray-900 text-white overflow-hidden">
      <!-- Background Image from Category -->
      <div v-if="categoryInfo?.banner || categoryInfo?.thumbnail" class="absolute inset-0">
        <img
            :src="categoryInfo.banner || categoryInfo.thumbnail"
            :alt="categoryInfo.name"
            class="w-full h-full object-cover opacity-20"
            loading="eager"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/90 to-gray-900/70"></div>
      </div>

      <!-- Decorative Elements -->
      <div v-else class="absolute inset-0 opacity-10">
        <div class="absolute inset-0"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px); background-size: 30px 30px;"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-20">
        <!-- Breadcrumb with Category Path -->
        <nav class="flex items-center gap-2 text-sm text-gray-300 mb-8 flex-wrap">
          <NuxtLink to="/" class="hover:text-white transition-colors">Home</NuxtLink>

          <!-- Show basePath link only when deeper than root -->
          <template v-if="categoryPath.length > 1">
            <Icon name="mdi:chevron-right" class="w-4 h-4" />
            <button
                @click="navigateToCategory(props.category)"
                class="hover:text-white transition-colors"
            >
              {{ breadcrumbLabel }}
            </button>
          </template>

          <template v-for="(cat, index) in categoryPath" :key="cat.url">
            <Icon name="mdi:chevron-right" class="w-4 h-4" />
            <button
                v-if="index < categoryPath.length - 1"
                @click="navigateToCategory(cat.url)"
                class="hover:text-white transition-colors"
            >
              {{ cat.name }}
            </button>
            <span v-else class="text-white">{{ cat.name }}</span>
          </template>
        </nav>

        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
          <!-- Category Thumbnail (if available) -->
          <div v-if="categoryInfo?.thumbnail" class="w-20 h-20 rounded-2xl overflow-hidden border-4 border-white/20 shadow-2xl flex-shrink-0">
            <img
                :src="categoryInfo.thumbnail"
                :alt="categoryInfo.name"
                class="w-full h-full object-cover"
                loading="lazy"
            />
          </div>

          <!-- Title & Description -->
          <div class="flex-1">
            <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 mb-4">
              <Icon name="mdi:folder-open" class="w-4 h-4 mr-2" />
              <span class="text-sm font-semibold">{{ childCategories.length }} Subcategories</span>
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-3 leading-tight">
              {{ categoryInfo?.name || title }}
            </h1>

            <p class="text-lg text-blue-100 max-w-3xl">
              {{ categoryInfo?.meta?.description || 'Explore curated articles and stories from this category' }}
            </p>
          </div>

          <!-- Stats Card -->
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-xl">
            <div class="grid grid-cols-2 gap-4 text-center">
              <div>
                <div class="text-2xl font-black text-yellow-300">{{ totalCount }}</div>
                <div class="text-xs font-medium text-blue-100">Articles</div>
              </div>
              <div>
                <div class="text-2xl font-black text-yellow-300">{{ categoryInfo?.views || 0 }}</div>
                <div class="text-xs font-medium text-blue-100">Views</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sticky Filter Bar -->
    <section class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-gray-200 dark:border-slate-700 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4">
        <div class="flex flex-col gap-4">
          <!-- Child Category Chips OR Back Button -->
          <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar pb-2">
            <!-- "All Categories" button - shown when at root -->
            <button
                v-if="categoryPath.length === 1"
                class="px-5 py-2.5 text-sm font-semibold rounded-full border-2 transition-all duration-200 whitespace-nowrap bg-gradient-to-r from-blue-600 to-purple-600 text-white border-transparent shadow-lg"
                disabled
            >
              <Icon name="mdi:view-grid" class="w-4 h-4 inline mr-1" />
              All Categories
            </button>

            <!-- "Back to Parent" button - shown when deeper than root -->
            <button
                v-if="categoryPath.length > 1"
                @click="navigateToCategory(categoryPath[categoryPath.length - 2].url)"
                class="px-5 py-2.5 text-sm font-semibold rounded-full border-2 transition-all duration-200 whitespace-nowrap bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md"
            >
              <Icon name="mdi:arrow-left" class="w-4 h-4 inline mr-1" />
              Back to {{ categoryPath[categoryPath.length - 2].name }}
            </button>

            <!-- Child Category Chips -->
            <button
                v-for="cat in childCategories"
                :key="cat.url"
                class="px-5 py-2.5 text-sm font-semibold rounded-full border-2 transition-all duration-200 whitespace-nowrap hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600"
                @click="selectChild(cat.url)"
            >
              <Icon name="mdi:folder" class="w-4 h-4 inline mr-1" />
              {{ cat.name }}
            </button>
          </div>

          <!-- Results Info -->
          <div class="flex items-center justify-between text-sm">
            <div class="text-gray-600 dark:text-gray-400">
              Showing <span class="font-semibold text-gray-900 dark:text-white">{{ pageCount }}</span> of <span class="font-semibold text-gray-900 dark:text-white">{{ totalCount }}</span> articles
            </div>
            <div class="text-gray-500 dark:text-gray-500">
              Page {{ currentPage }} of {{ totalPages }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Posts Grid -->
    <section class="py-12 px-4 lg:px-8 bg-gray-50 dark:bg-slate-900 min-h-screen">
      <div class="max-w-7xl mx-auto">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-20">
          <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>
          <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">Loading articles...</p>
        </div>

        <!-- Error -->
        <div v-else-if="loadError" class="text-center py-20">
          <div class="w-32 h-32 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-8">
            <Icon name="mdi:alert-circle" class="w-16 h-16 text-red-500" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Error Loading Articles</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-8">{{ loadError }}</p>
          <button
              @click="fetchPosts(1)"
              class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105"
          >
            <Icon name="mdi:refresh" class="w-5 h-5 inline mr-2" />
            Try Again
          </button>
        </div>

        <!-- No Results -->
        <div v-else-if="posts.length === 0" class="text-center py-20">
          <div class="w-32 h-32 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-8">
            <Icon name="mdi:file-document-outline" class="w-16 h-16 text-gray-400" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No Articles Found</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-8">No articles available in this category yet.</p>
          <button
              v-if="categoryPath.length > 1"
              @click="navigateToCategory(categoryPath[categoryPath.length - 2].url)"
              class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105"
          >
            <Icon name="mdi:arrow-left" class="w-5 h-5 inline mr-2" />
            Back to {{ categoryPath[categoryPath.length - 2].name }}
          </button>
        </div>

        <!-- Grid -->
        <template v-else>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <NuxtLink
                v-for="item in posts"
                :key="item.url"
                :to="`${basePath}/${item.url}`"
                class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-200 dark:border-slate-700 overflow-hidden"
            >
              <!-- Thumbnail -->
              <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 overflow-hidden">
                <img
                    :src="item.thumbnail"
                    :alt="item.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                <!-- Category Badge -->
                <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg">
                  {{ item.category?.name || 'General' }}
                </div>
              </div>

              <!-- Content -->
              <div class="p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  {{ item.name }}
                </h3>

                <!-- Author & Date -->
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                  <div class="flex items-center gap-2">
                    <img
                        v-if="item.author?.avatar"
                        :src="item.author.avatar"
                        :alt="item.author.name"
                        class="w-6 h-6 rounded-full"
                        loading="lazy"
                    />
                    <span class="font-medium">{{ item.author?.name || 'Anonymous' }}</span>
                  </div>
                  <span class="text-gray-400">•</span>
                  <time>{{ formatDate(item.updated_at) }}</time>
                </div>
              </div>
            </NuxtLink>
          </div>

          <!-- Enhanced Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button
                class="px-6 py-3 rounded-xl text-sm font-semibold border-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="currentPage <= 1
                  ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 border-gray-300 dark:border-gray-700'
                  : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 hover:text-blue-600 hover:shadow-md'"
                :disabled="currentPage <= 1"
                @click="goToPage(currentPage - 1)"
            >
              <Icon name="mdi:chevron-left" class="w-5 h-5 inline mr-1" />
              Previous
            </button>

            <div class="flex items-center gap-2">
              <button
                  v-for="p in paginationPages"
                  :key="p"
                  @click="goToPage(p)"
                  :class="[
                    'px-4 py-2 rounded-xl text-sm font-bold border-2 transition-all duration-200',
                    currentPage === p
                      ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white border-transparent shadow-lg'
                      : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 hover:text-blue-600 hover:shadow-md'
                  ]"
              >
                {{ p }}
              </button>
            </div>

            <button
                class="px-6 py-3 rounded-xl text-sm font-semibold border-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="currentPage >= totalPages
                  ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 border-gray-300 dark:border-gray-700'
                  : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-blue-500 hover:text-blue-600 hover:shadow-md'"
                :disabled="currentPage >= totalPages"
                @click="goToPage(currentPage + 1)"
            >
              Next
              <Icon name="mdi:chevron-right" class="w-5 h-5 inline ml-1" />
            </button>
          </div>
        </template>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useToast } from '#imports'

const props = withDefaults(defineProps<{
  title: string
  category: string
  basePath?: string
  breadcrumbLabel?: string
}>(), {
  basePath: '/blogs',
  breadcrumbLabel: 'Blog'
})

const config = useRuntimeConfig()
const toast = useToast()

// State
const currentCategory = ref(props.category)
const currentPage = ref(1)
const totalPages = ref(1)
const pageCount = ref(0)
const totalCount = ref(0)
const loading = ref(false)
const loadError = ref('')

const categoryInfo = ref<any>(null)
const childCategories = ref<any[]>([])
const categoryPath = ref<any[]>([])
const posts = ref<any[]>([])

// Computed
const paginationPages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

// Helper Functions
const formatDate = (iso: string) => {
  if (!iso) return 'N/A'
  const date = new Date(iso)
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(date)
}

// Fetch Functions
const fetchCategoryInfo = async (categorySlug: string) => {
  try {
    const res: any = await useSanctumFetch(`${config.public.apiBase}/categories/${categorySlug}`)

    if (res?.data) {
      categoryInfo.value = res.data
      childCategories.value = res.data.children || []

      if (!categoryPath.value.find(c => c.url === res.data.url)) {
        categoryPath.value.push({
          name: res.data.name,
          url: res.data.url
        })
      }
    } else {
      categoryInfo.value = null
      childCategories.value = []
    }
  } catch (error) {
    console.error('Error fetching category:', error)
    categoryInfo.value = null
    childCategories.value = []
  }
}

const fetchPosts = async (page = 1) => {
  try {
    loading.value = true
    loadError.value = ''

    const url = new URL(`${config.public.apiBase}/blogs`)
    url.searchParams.append('page', page.toString())
    url.searchParams.append('per_page', '12')
    url.searchParams.append('category', currentCategory.value)

    const res: any = await useSanctumFetch(url.toString())

    if (res?.data) {
      posts.value = res.data
      currentPage.value = res.meta?.current_page || page
      totalPages.value = res.meta?.last_page || 1
      pageCount.value = res.meta?.to || posts.value.length
      totalCount.value = res.meta?.total || posts.value.length
    } else {
      posts.value = []
      currentPage.value = 1
      totalPages.value = 1
      pageCount.value = 0
      totalCount.value = 0
    }
  } catch (error: any) {
    console.error('Error fetching posts:', error)
    loadError.value = error?.message || 'Failed to load articles. Please try again.'
    posts.value = []
    currentPage.value = 1
    totalPages.value = 1
    pageCount.value = 0
    totalCount.value = 0
  } finally {
    loading.value = false
  }
}

// Action Functions
const selectChild = async (categorySlug: string) => {
  currentCategory.value = categorySlug
  currentPage.value = 1

  await fetchCategoryInfo(categorySlug)
  await fetchPosts(1)

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const navigateToCategory = async (categorySlug: string) => {
  const index = categoryPath.value.findIndex(c => c.url === categorySlug)
  if (index !== -1) {
    categoryPath.value = categoryPath.value.slice(0, index + 1)
  }

  currentCategory.value = categorySlug
  currentPage.value = 1

  await fetchCategoryInfo(categorySlug)
  await fetchPosts(1)

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const goToPage = (page: number) => {
  if (page !== currentPage.value && page >= 1 && page <= totalPages.value) {
    fetchPosts(page)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

// Lifecycle
onMounted(async () => {
  categoryPath.value = []
  await fetchCategoryInfo(props.category)
  await fetchPosts(1)
})

watch(() => props.category, async () => {
  currentCategory.value = props.category
  currentPage.value = 1
  categoryPath.value = []
  await fetchCategoryInfo(props.category)
  await fetchPosts(1)
})
</script>

<style scoped>
.hide-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
