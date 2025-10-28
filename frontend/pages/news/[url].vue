<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-900">
    <GlobalLoader v-if="isLoading" />

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-6"></div>
        <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">Loading article...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen px-4">
      <div class="text-center max-w-md">
        <div class="w-32 h-32 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-8">
          <Icon name="mdi:alert-circle" class="w-16 h-16 text-red-500" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Article Not Found</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">{{ error }}</p>
        <NuxtLink
            to="/news"
            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105"
        >
          <Icon name="mdi:arrow-left" class="w-5 h-5 mr-2" />
          Back to Articles
        </NuxtLink>
      </div>
    </div>

    <!-- Article Content -->
    <article v-else-if="post" class="relative">

      <!-- Hero Section -->
      <section class="relative bg-gradient-to-br from-gray-900 via-slate-800 to-gray-900 text-white overflow-hidden">
        <div class="absolute inset-0">
          <img
              :src="post.banner || post.thumbnail"
              :alt="post.name"
              class="w-full h-full object-cover opacity-30"
              loading="eager"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/80 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-24">
          <!-- Breadcrumb -->
          <nav class="flex items-center gap-2 text-sm text-gray-300 mb-8">
            <NuxtLink to="/" class="hover:text-white transition-colors">Home</NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4" />
            <NuxtLink to="/news" class="hover:text-white transition-colors">News</NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4" />
            <span class="text-white truncate max-w-xs sm:max-w-md">{{ post.name }}</span>
          </nav>

          <!-- Category Badge -->
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-bold mb-6 shadow-lg">
            <Icon name="mdi:tag" class="w-4 h-4 mr-2" />
            {{ post.category?.name || 'General' }}
          </div>

          <!-- Title -->
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-6 leading-tight max-w-4xl">
            {{ post.name }}
          </h1>

          <!-- Meta Info -->
          <div class="flex flex-wrap items-center gap-6 text-gray-300">
            <div class="flex items-center gap-3">
              <img
                  v-if="post.author?.avatar"
                  :src="post.author.avatar"
                  :alt="post.author.name"
                  class="w-12 h-12 rounded-full border-2 border-white/20"
                  loading="lazy"
              />
              <div>
                <p class="text-sm text-gray-400">Written by</p>
                <p class="font-semibold text-white">{{ post.author?.name || 'Anonymous' }}</p>
              </div>
            </div>

            <div class="hidden sm:block w-px h-12 bg-white/20"></div>

            <div>
              <p class="text-sm text-gray-400">Published on</p>
              <time class="font-semibold text-white">{{ formatDate(post.updated_at) }}</time>
            </div>

            <div class="hidden sm:block w-px h-12 bg-white/20"></div>

            <div>
              <p class="text-sm text-gray-400">Reading time</p>
              <p class="font-semibold text-white">{{ readingTime }} min read</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Main Content with Sidebar -->
      <section class="py-12 px-4 lg:px-8">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Main Article (2/3 width) -->
            <div class="lg:col-span-2 space-y-8">
              <!-- Featured Image -->
              <div v-if="post.thumbnail && post.thumbnail !== post.banner" class="rounded-3xl overflow-hidden shadow-2xl">
                <img
                    :src="post.thumbnail"
                    :alt="post.name"
                    class="w-full h-auto"
                    loading="lazy"
                />
              </div>

              <!-- Article Content -->
              <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-gray-200 dark:border-slate-700 p-8 lg:p-12">
                <div class="prose prose-lg dark:prose-invert max-w-none">
<!--                  <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap text-lg">-->
<!--                    {{ post.description }}-->
<!--                  </div>-->

                  <div
                      class="text-gray-700 dark:text-gray-300 leading-relaxed text-lg"
                      v-html="post.description"
                  ></div>

                </div>
              </div>

              <!-- Share Section -->
              <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-slate-800 dark:to-slate-700 rounded-3xl p-6 border border-gray-200 dark:border-slate-600 shadow-lg">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                  <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Share:</span>
                    <button
                        @click="shareOnTwitter"
                        class="p-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition-all duration-200 transform hover:scale-110 shadow-md"
                        title="Share on Twitter"
                    >
                      <Icon name="mdi:twitter" class="w-5 h-5" />
                    </button>
                    <button
                        @click="shareOnFacebook"
                        class="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all duration-200 transform hover:scale-110 shadow-md"
                        title="Share on Facebook"
                    >
                      <Icon name="mdi:facebook" class="w-5 h-5" />
                    </button>
                    <button
                        @click="shareOnLinkedIn"
                        class="p-3 bg-blue-700 hover:bg-blue-800 text-white rounded-xl transition-all duration-200 transform hover:scale-110 shadow-md"
                        title="Share on LinkedIn"
                    >
                      <Icon name="mdi:linkedin" class="w-5 h-5" />
                    </button>
                    <button
                        @click="copyLink"
                        class="p-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl transition-all duration-200 transform hover:scale-110 shadow-md"
                        title="Copy Link"
                    >
                      <Icon name="mdi:link-variant" class="w-5 h-5" />
                    </button>
                  </div>

                  <NuxtLink
                      to="/news"
                      class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105"
                  >
                    <Icon name="mdi:arrow-left" class="w-5 h-5 mr-2" />
                    Back to Articles
                  </NuxtLink>
                </div>
              </div>

              <!-- Author Card -->
              <div v-if="post.author" class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-slate-800 dark:to-slate-700 rounded-3xl p-8 border border-gray-200 dark:border-slate-600 shadow-lg">
                <div class="flex items-start gap-6">
                  <img
                      v-if="post.author.avatar"
                      :src="post.author.avatar"
                      :alt="post.author.name"
                      class="w-20 h-20 rounded-full border-4 border-white dark:border-slate-600 shadow-lg flex-shrink-0"
                      loading="lazy"
                  />
                  <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">About the Author</h3>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ post.author.name }}</p>
                    <p class="text-gray-600 dark:text-gray-400">{{ post.author.email }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sidebar (1/3 width) -->
            <aside class="lg:col-span-1 space-y-8">
              <!-- Related Posts Widget -->
              <div v-if="relatedPosts && relatedPosts.length > 0" class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-gray-200 dark:border-slate-700 p-6 sticky top-4">
                <div class="flex items-center gap-2 mb-6">
                  <Icon name="mdi:newspaper-variant-multiple" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white">Related Articles</h3>
                </div>

                <!-- Mobile & Tablet: Swiper -->
                <div class="block lg:hidden mb-6">
                  <Swiper
                      :slides-per-view="1"
                      :space-between="16"
                      :breakpoints="{
                        640: { slidesPerView: 2 },
                        1024: { slidesPerView: 1 }
                      }"
                      class="related-sidebar-swiper"
                  >
                    <SwiperSlide v-for="relatedPost in relatedPosts" :key="relatedPost.url">
                      <NuxtLink
                          :to="`/news/${relatedPost.url}`"
                          class="group block bg-gray-50 dark:bg-slate-700 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-slate-600"
                      >
                        <div class="relative h-32 overflow-hidden">
                          <img
                              :src="relatedPost.thumbnail"
                              :alt="relatedPost.name"
                              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                              loading="lazy"
                          />
                          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                        <div class="p-4">
                          <h4 class="font-bold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ relatedPost.name }}
                          </h4>
                          <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <img
                                v-if="relatedPost.author?.avatar"
                                :src="relatedPost.author.avatar"
                                :alt="relatedPost.author.name"
                                class="w-5 h-5 rounded-full"
                                loading="lazy"
                            />
                            <span class="truncate">{{ relatedPost.author?.name || 'Anonymous' }}</span>
                          </div>
                        </div>
                      </NuxtLink>
                    </SwiperSlide>
                  </Swiper>
                </div>

                <!-- Desktop: List -->
                <div class="hidden lg:block space-y-4 max-h-[800px] overflow-y-auto scrollbar-thin scrollbar-thumb-blue-600 scrollbar-track-gray-200 dark:scrollbar-track-slate-700">
                  <NuxtLink
                      v-for="relatedPost in relatedPosts"
                      :key="relatedPost.url"
                      :to="`/news/${relatedPost.url}`"
                      class="group block bg-gray-50 dark:bg-slate-700 rounded-2xl overflow-hidden hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-slate-600"
                  >
                    <div class="relative h-32 overflow-hidden">
                      <img
                          :src="relatedPost.thumbnail"
                          :alt="relatedPost.name"
                          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                          loading="lazy"
                      />
                      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                    <div class="p-4">
                      <h4 class="font-bold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        {{ relatedPost.name }}
                      </h4>
                      <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <img
                            v-if="relatedPost.author?.avatar"
                            :src="relatedPost.author.avatar"
                            :alt="relatedPost.author.name"
                            class="w-5 h-5 rounded-full"
                            loading="lazy"
                        />
                        <span class="truncate">{{ relatedPost.author?.name || 'Anonymous' }}</span>
                      </div>
                    </div>
                  </NuxtLink>
                </div>

                <!-- View All Button -->
                <NuxtLink
                    :to="`/news?category=${post.category?.url || ''}`"
                    class="mt-6 w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105"
                >
                  View All
                  <Icon name="mdi:arrow-right" class="w-5 h-5 ml-2" />
                </NuxtLink>
              </div>

              <!-- Category Widget -->
              <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-gray-200 dark:border-slate-700 p-6">
                <div class="flex items-center gap-2 mb-4">
                  <Icon name="mdi:folder" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white">Category</h3>
                </div>
                <NuxtLink
                    v-if="post.category"
                    :to="`/news?category=${post.category.url}`"
                    class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-slate-700 dark:to-slate-600 rounded-xl hover:shadow-md transition-all duration-300 border border-gray-200 dark:border-slate-600"
                >
                  <img
                      v-if="post.category.thumbnail"
                      :src="post.category.thumbnail"
                      :alt="post.category.name"
                      class="w-12 h-12 rounded-lg object-cover"
                      loading="lazy"
                  />
                  <div class="flex-1">
                    <p class="font-bold text-gray-900 dark:text-white">{{ post.category.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Explore more articles</p>
                  </div>
                  <Icon name="mdi:chevron-right" class="w-5 h-5 text-gray-400" />
                </NuxtLink>
              </div>
            </aside>
          </div>
        </div>
      </section>
    </article>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, useRuntimeConfig, useSanctumFetch, useToast } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const toast = useToast()

const isLoading = useState('pageLoading', () => true)

// State
const post = ref<any>(null)
const relatedPosts = ref<any[]>([])
const loading = ref(true)
const error = ref('')

// Computed
const readingTime = computed(() => {
  if (!post.value?.description) return 0
  const words = post.value.description.split(/\s+/).length
  return Math.ceil(words / 200)
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

// Fetch Post - FIXED: Using 'url' instead of 'uuid'
const fetchPost = async () => {
  try {
    loading.value = true
    error.value = ''

    const url = route.params.url as string
    const res: any = await useSanctumFetch(`${config.public.apiBase}/blogs/${url}`)

    if (res?.data) {
      post.value = res.data
      relatedPosts.value = res.related_posts || []

      useHead({
        title: post.value.name,
        meta: [
          { name: 'description', content: post.value.description?.substring(0, 160) || post.value.name },
          { property: 'og:title', content: post.value.name },
          { property: 'og:description', content: post.value.description?.substring(0, 160) || post.value.name },
          { property: 'og:image', content: post.value.thumbnail },
          { name: 'twitter:card', content: 'summary_large_image' },
        ]
      })
    } else {
      error.value = 'Article not found. It may have been removed or the URL is incorrect.'
      router.push('/news')
    }
  } catch (err: any) {
    console.error('Error fetching post:', err)
    error.value = err?.message || 'Failed to load article. Please try again later.'
  } finally {
    loading.value = false
    isLoading.value = false
  }
}

// Share Functions
const shareOnTwitter = () => {
  const url = encodeURIComponent(window.location.href)
  const text = encodeURIComponent(post.value?.name || '')
  window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank')
}

const shareOnFacebook = () => {
  const url = encodeURIComponent(window.location.href)
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank')
}

const shareOnLinkedIn = () => {
  const url = encodeURIComponent(window.location.href)
  window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank')
}

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href)
    toast.success({
      title: 'Link Copied!',
      message: 'Article link copied to clipboard'
    })
  } catch (err) {
    toast.error({
      title: 'Copy Failed',
      message: 'Could not copy link to clipboard'
    })
  }
}

onMounted(() => {
  fetchPost()
})
</script>

<style scoped>
.prose {
  @apply text-base;
}

.prose p {
  @apply mb-4 leading-relaxed;
}

.prose h1, .prose h2, .prose h3, .prose h4 {
  @apply font-bold text-gray-900 dark:text-white mt-8 mb-4;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-thumb-blue-600::-webkit-scrollbar-thumb {
  @apply bg-blue-600 rounded-full;
}

.scrollbar-track-gray-200::-webkit-scrollbar-track {
  @apply bg-gray-200 rounded-full;
}

.dark .scrollbar-track-slate-700::-webkit-scrollbar-track {
  @apply bg-slate-700 rounded-full;
}

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500;
}
</style>
