<script setup lang="ts">
import { getEmptyStateMessage } from '~/utils/api-error'

interface Category {
  id: number
  name: string
  slug: string
}

interface PostItem {
  id: number
  title: string
  slug: string
  excerpt?: string | null
  cover_image?: string | null
  author_name?: string | null
  published_at?: string | null
  category?: Category | null
}

interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const props = defineProps<{
  type: 'blog' | 'news'
  title: string
  subtitle: string
  heroLabel: string
}>()

const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()

const categories = ref<Category[]>([])
const posts = ref<PostItem[]>([])
const meta = ref<PaginationMeta | null>(null)
const status = ref<'idle' | 'loading' | 'error'>('idle')
const errorMessage = ref<string | null>(null)

const search = ref<string>((route.query.search as string) || '')
const activeCategory = ref<string>((route.query.category as string) || '')
const currentPage = ref<number>(Number(route.query.page || 1))

const baseUrl = computed(() => `${config.public.apiBase}/api/${props.type === 'blog' ? 'blogs' : 'news'}`)

const fetchCategories = async () => {
  try {
    const response = await useSanctumFetch<{ data: Category[] }>(`${baseUrl.value}/categories`)
    categories.value = response.data || []
  } catch {
    categories.value = []
  }
}

const fetchPosts = async () => {
  status.value = 'loading'
  errorMessage.value = null

  try {
    const params = new URLSearchParams()
    params.set('per_page', '9')
    params.set('page', String(currentPage.value))
    if (activeCategory.value) params.set('category', activeCategory.value)
    if (search.value) params.set('search', search.value)

    const response = await useSanctumFetch<{ data: PostItem[]; meta: PaginationMeta }>(
      `${baseUrl.value}?${params.toString()}`
    )

    posts.value = response.data || []
    meta.value = response.meta || null
    status.value = 'idle'
  } catch (err: unknown) {
    status.value = 'error'
    errorMessage.value = err instanceof Error ? err.message : 'Unable to load content right now.'
  }
}

const updateRoute = () => {
  router.replace({
    query: {
      ...route.query,
      category: activeCategory.value || undefined,
      search: search.value || undefined,
      page: currentPage.value > 1 ? String(currentPage.value) : undefined
    }
  })
}

watch([activeCategory, search, currentPage], () => {
  updateRoute()
  fetchPosts()
})

watch(() => route.query, () => {
  activeCategory.value = (route.query.category as string) || ''
  search.value = (route.query.search as string) || ''
  currentPage.value = Number(route.query.page || 1)
})

onMounted(async () => {
  await Promise.all([fetchCategories(), fetchPosts()])
})

const pageCount = computed(() => meta.value?.last_page || 1)
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <section class="theme-gradient-brand text-white py-16">
      <div class="mx-auto max-w-6xl px-6 text-center space-y-4">
        <p class="text-sm uppercase tracking-[0.3em] text-white/70">{{ heroLabel }}</p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight">{{ title }}</h1>
        <p class="text-lg text-white/80 max-w-3xl mx-auto">{{ subtitle }}</p>
      </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 py-12 space-y-10">
      <div class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
          <button
            class="px-4 py-2 rounded-full text-sm font-semibold border transition"
            :class="activeCategory === '' ? 'theme-bg-primary text-white border-transparent' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
            @click="activeCategory = ''"
          >
            All
          </button>
          <button
            v-for="category in categories"
            :key="category.id"
            class="px-4 py-2 rounded-full text-sm font-semibold border transition"
            :class="activeCategory === category.slug ? 'theme-bg-primary text-white border-transparent' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
            @click="activeCategory = category.slug"
          >
            {{ category.name }}
          </button>
        </div>

        <UInput
          v-model="search"
          icon="i-lucide-search"
          placeholder="Search..."
          class="w-full lg:w-72"
        />
      </div>

      <div v-if="status === 'loading'" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="i in 6"
          :key="i"
          class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 animate-pulse"
        >
          <div class="h-40 bg-slate-200 dark:bg-slate-800 rounded-xl" />
          <div class="mt-4 h-4 bg-slate-200 dark:bg-slate-800 rounded w-3/4" />
          <div class="mt-2 h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/2" />
        </div>
      </div>

      <div v-else-if="status === 'error'" class="text-center text-red-600 dark:text-red-400">
        {{ errorMessage }}
      </div>

      <div v-else-if="posts.length === 0" class="text-center text-slate-600 dark:text-slate-300">
        {{ getEmptyStateMessage(props.type === 'blog' ? 'general' : 'general', 'No posts found.') }}
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <NuxtLink
          v-for="post in posts"
          :key="post.id"
          :to="`/${props.type === 'blog' ? 'blogs' : 'news'}/${post.slug}`"
          class="group rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-lg transition hover:-translate-y-1 hover:shadow-xl"
        >
          <div class="h-44 bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <img
              v-if="post.cover_image"
              :src="post.cover_image"
              :alt="post.title"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            >
            <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
              <UIcon name="i-lucide-image" class="w-12 h-12" />
            </div>
          </div>
          <div class="p-6 space-y-3">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <span v-if="post.category">{{ post.category.name }}</span>
              <span v-if="post.published_at">- {{ new Date(post.published_at).toLocaleDateString() }}</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
              {{ post.title }}
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-3">
              {{ post.excerpt || 'Read more...' }}
            </p>
          </div>
        </NuxtLink>
      </div>

      <div v-if="pageCount > 1" class="flex justify-center">
        <UPagination v-model="currentPage" :page-count="pageCount" :max="5" />
      </div>
    </section>
  </div>
</template>
