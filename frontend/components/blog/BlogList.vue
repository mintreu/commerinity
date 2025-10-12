<template>
  <div class="relative z-10 w-full">

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 text-white overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px); background-size: 30px 30px;"></div>
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-24">
        <div class="text-center">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 mb-6">
            <Icon name="mdi:newspaper-variant-outline" class="w-5 h-5 mr-2" />
            <span class="font-semibold">Browse {{ title }}</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6">
            <span class="block">Discover Our Latest</span>
            <span class="block text-yellow-300">{{ title }}</span>
          </h1>

          <p class="text-xl lg:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Explore stories organized by topics. Filter quickly and find what matters most.
          </p>

          <!-- Search Bar -->
          <div class="max-w-2xl mx-auto mb-12">
            <div class="relative">
              <input
                  type="text"
                  v-model="search"
                  placeholder="Search {{ title.toLowerCase() }}..."
                  class="w-full px-6 py-4 pl-14 pr-20 rounded-2xl bg-white/95 backdrop-blur-sm text-gray-900 placeholder-gray-500 text-lg font-medium shadow-xl focus:ring-4 focus:ring-white/25 focus:outline-none transition-all duration-300"
                  @input="onSearchInput"
              />
              <Icon name="mdi:magnify" class="absolute left-5 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" />
              <button
                  class="absolute right-3 top-1/2 -translate-y-1/2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2 rounded-xl font-bold hover:shadow-lg transition-all duration-300"
                  @click="applySearch"
              >
                Search
              </button>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300">{{ childCategories.length }}</div>
              <div class="text-sm font-medium text-blue-100">Topics</div>
            </div>
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300">{{ totalCount }}</div>
              <div class="text-sm font-medium text-blue-100">Posts</div>
            </div>
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300">SEO</div>
              <div class="text-sm font-medium text-blue-100">Optimized</div>
            </div>
            <div class="text-center">
              <div class="text-3xl sm:text-4xl font-black text-yellow-300">100%</div>
              <div class="text-sm font-medium text-blue-100">Responsive</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sticky Filter Bar -->
    <section class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-gray-200 dark:border-slate-700 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4">
        <div class="flex flex-col gap-4">
          <!-- Child Category Chips -->
          <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar">
            <button
                class="px-4 py-2 text-sm rounded-full border transition-all"
                :class="activeChild === '' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-slate-700'"
                @click="selectChild('')"
            >
              All
            </button>

            <button
                v-for="cat in childCategories"
                :key="cat.url"
                class="px-4 py-2 text-sm rounded-full border transition-all whitespace-nowrap"
                :class="activeChild === cat.url ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-slate-700'"
                @click="selectChild(cat.url)"
            >
              {{ cat.name }}
            </button>
          </div>

          <!-- Results Count -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing page {{ currentPage }} of {{ totalPages }} • {{ pageCount }} items
          </div>
        </div>
      </div>
    </section>

    <!-- Posts Grid -->
    <section class="py-12 px-4 lg:px-8 bg-gray-50 dark:bg-slate-900">
      <div class="max-w-7xl mx-auto">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-16">
          <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-600 dark:text-gray-400">Loading {{ title.toLowerCase() }}...</p>
        </div>

        <!-- Error -->
        <div v-else-if="loadError" class="text-center py-16">
          <div class="w-32 h-32 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-8">
            <Icon name="mdi:alert-circle" class="w-16 h-16 text-red-500" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Error Loading {{ title }}</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-8">{{ loadError }}</p>
          <button @click="fetchPosts(1)" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
            Try Again
          </button>
        </div>

        <!-- Grid -->
        <template v-else>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <NuxtLink
                v-for="item in posts"
                :key="item.url"
                :to="`${linkBase}/${item.url}`"
                class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-200 dark:border-slate-700 overflow-hidden"
            >
              <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-slate-700 dark:to-slate-600 overflow-hidden">
                <img
                    :src="item.thumbnail"
                    :alt="item.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold bg-blue-600 text-white">
                  {{ item.category?.name || 'General' }}
                </div>
              </div>
              <div class="p-5">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                  {{ item.name }}
                </h3>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(item.updated_at) }}
                </div>
              </div>
            </NuxtLink>
          </div>

          <!-- Pagination -->
          <div class="flex justify-center gap-2 mt-10">
            <button
                class="px-4 py-1 rounded text-sm border text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 disabled:opacity-50"
                :disabled="currentPage <= 1"
                @click="goToPage(currentPage - 1)"
            >
              Prev
            </button>
            <button
                v-for="p in totalPages"
                :key="p"
                @click="goToPage(p)"
                :class="[
                'px-4 py-1 rounded text-sm border',
                currentPage === p
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'
              ]"
            >
              {{ p }}
            </button>
            <button
                class="px-4 py-1 rounded text-sm border text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 disabled:opacity-50"
                :disabled="currentPage >= totalPages"
                @click="goToPage(currentPage + 1)"
            >
              Next
            </button>
          </div>
        </template>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'

const props = withDefaults(defineProps<{
  title: string
  category: string
  linkBase?: string
}>(), {
  linkBase: '/blog'
})

const config = useRuntimeConfig()

// UI State
const search = ref('')
const activeChild = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const pageCount = ref(0)
const totalCount = ref(0)

const childCategories = ref([])
const posts = ref([])

const formatDate = (iso: string) => new Date(iso).toLocaleDateString()

const fetchChildCategories = async () => {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/categories/${props.category}`, {
      credentials: 'include'
    })
    if (res?.data?.data?.children) {
      childCategories.value = res.data.children
    } else {
      childCategories.value = []
    }
  } catch {
    childCategories.value = []
  }
}

const buildCategoryParam = computed(() => activeChild.value || props.category)

const fetchPosts = async (page = 1) => {
  try {
    const url = new URL(`${config.public.apiBase}/blogs`)
    url.searchParams.append('page', page.toString())
    url.searchParams.append('per_page', '12')
    url.searchParams.append('category', buildCategoryParam.value)
    if (search.value.trim()) url.searchParams.append('search', search.value.trim())

    const res = await useSanctumFetch(url.toString(), { credentials: 'include' })

    if (res?.data) {
      posts.value = res.data
      currentPage.value = res.data.meta?.current_page || page
      totalPages.value = res.data.meta?.last_page || 1
      pageCount.value = res.data.meta?.to || posts.value.length
      totalCount.value = res.data.meta?.total || posts.value.length
    } else {
      posts.value = []
      currentPage.value = 1
      totalPages.value = 1
      pageCount.value = 0
      totalCount.value = 0
    }
  } catch (e) {
    posts.value = []
    currentPage.value = 1
    totalPages.value = 1
    pageCount.value = 0
    totalCount.value = 0
  }
}

const selectChild = (slug: string) => {
  activeChild.value = slug
  fetchPosts(1)
}

const onSearchInput = () => {
  // No debounce needed here; trigger on button click
}

const applySearch = () => {
  fetchPosts(1)
}

const goToPage = (page: number) => {
  if (page !== currentPage.value) {
    fetchPosts(page)
  }
}

onMounted(async () => {
  await fetchChildCategories()
  await fetchPosts(1)
})

watch(() => props.category, async () => {
  activeChild.value = ''
  await fetchChildCategories()
  await fetchPosts(1)
})
</script>

<style scoped>
.hide-scrollbar {
  scrollbar-width: none;
}
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.group:hover > .group-hover\:translate-x-1 {
  transform: translateX(0.25rem);
  transition-duration: 200ms;
}

.group:hover > .group-hover\:scale-110 {
  transform: scale(1.10);
  transition-duration: 500ms;
}
</style>
