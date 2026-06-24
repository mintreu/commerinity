<script setup lang="ts">
import type { GlobalSearchResult } from '~/composables/useGlobalSearch'

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { search } = useGlobalSearch()

type SearchTab = 'all' | 'products' | 'blogs' | 'news'

const query = ref((route.query.q as string) || '')
const activeTab = ref<SearchTab>('all')
const loading = ref(false)
const searched = ref(false)
const error = ref<string | null>(null)

const results = ref<GlobalSearchResult>({
  products: [],
  blogs: [],
  news: []
})

const totals = ref({
  products: 0,
  blogs: 0,
  news: 0,
  all: 0
})

const pageTitle = computed(() => {
  if (!query.value.trim()) return 'Search'
  return `Search results for "${query.value.trim()}"`
})

useSeoMeta({
  title: computed(() => `${pageTitle.value} | ${config.public.companyName}`),
  description: computed(() => query.value.trim()
    ? `Find products, blogs, and news related to ${query.value.trim()}.`
    : `Search products, blogs, and news on ${config.public.companyName}.`)
})

const runSearch = async (value: string) => {
  const q = value.trim()
  if (q.length < 2) {
    searched.value = false
    results.value = { products: [], blogs: [], news: [] }
    totals.value = { products: 0, blogs: 0, news: 0, all: 0 }
    error.value = null
    return
  }

  loading.value = true
  error.value = null

  try {
    const data = await search(q, 12)
    results.value = data.results
    totals.value = data.totals
    searched.value = true
  } catch (err: unknown) {
    error.value = err instanceof Error ? err.message : 'Failed to load search results.'
    searched.value = true
  } finally {
    loading.value = false
  }
}

const submitSearch = async () => {
  const q = query.value.trim()
  await router.push({
    path: '/search',
    query: q ? { q } : {}
  })
}

watch(
  () => route.query.q,
  (value) => {
    query.value = (value as string) || ''
    runSearch(query.value)
  },
  { immediate: true }
)

const products = computed(() => results.value.products)
const blogs = computed(() => results.value.blogs)
const news = computed(() => results.value.news)

const tabItems = computed(() => {
  return [
    { key: 'all', label: `All (${totals.value.all})` },
    { key: 'products', label: `Products (${totals.value.products})` },
    { key: 'blogs', label: `Blogs (${totals.value.blogs})` },
    { key: 'news', label: `News (${totals.value.news})` }
  ]
})

const hasAnyResult = computed(() => totals.value.all > 0)

const sectionItems = computed(() => {
  if (activeTab.value === 'products') return products.value
  if (activeTab.value === 'blogs') return blogs.value
  if (activeTab.value === 'news') return news.value
  return []
})

const resolveDate = (value?: string | null) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString()
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <section class="theme-gradient-brand py-14 text-white">
      <div class="mx-auto max-w-6xl px-6">
        <h1 class="text-3xl md:text-4xl font-black">
          Global Search
        </h1>
        <p class="mt-2 text-white/80">
          Search products, blogs, and news from one place.
        </p>

        <form
          class="mt-6"
          @submit.prevent="submitSearch"
        >
          <div class="rounded-2xl bg-white/95 p-2 shadow-xl">
            <div class="flex flex-col gap-2 md:flex-row">
              <input
                v-model="query"
                type="text"
                placeholder="Search products, blogs, news..."
                class="flex-1 rounded-xl border border-slate-200 px-4 py-3 text-slate-800 outline-none focus:border-violet-500"
              >
              <button
                type="submit"
                class="rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-6 py-3 font-semibold text-white"
              >
                Search
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 py-10">
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4 dark:border-slate-800">
        <button
          v-for="tab in tabItems"
          :key="tab.key"
          class="rounded-full border px-4 py-2 text-sm font-semibold transition"
          :class="activeTab === tab.key
            ? 'border-transparent bg-violet-600 text-white'
            : 'border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'"
          @click="activeTab = tab.key as SearchTab"
        >
          {{ tab.label }}
        </button>
      </div>

      <div
        v-if="loading"
        class="mt-8 grid gap-4 md:grid-cols-2"
      >
        <div
          v-for="i in 6"
          :key="i"
          class="h-28 rounded-2xl bg-slate-200/80 dark:bg-slate-800/70 animate-pulse"
        />
      </div>

      <div
        v-else-if="error"
        class="mt-8 rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
      >
        {{ error }}
      </div>

      <div
        v-else-if="searched && !hasAnyResult"
        class="mt-8 rounded-2xl border border-slate-200 bg-white px-6 py-8 text-center text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
      >
        No result found for "{{ query.trim() }}".
      </div>

      <template v-else-if="searched">
        <div
          v-if="activeTab === 'all'"
          class="mt-8 space-y-10"
        >
          <div
            v-if="products.length"
            class="space-y-4"
          >
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
              Products
            </h2>
            <div class="space-y-3">
              <NuxtLink
                v-for="item in products"
                :key="`product-${item.id}`"
                :to="item.url"
                class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
              >
                <img
                  v-if="item.thumbnail"
                  :src="item.thumbnail"
                  :alt="item.title"
                  class="h-20 w-20 rounded-xl object-cover"
                >
                <div
                  v-else
                  class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800"
                >
                  <UIcon
                    name="i-lucide-package"
                    class="h-8 w-8 text-slate-400"
                  />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="line-clamp-1 text-base font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                  <p class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ item.excerpt || item.sku || 'View product details' }}</p>
                  <p class="mt-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ item.price_formatted }}</p>
                </div>
              </NuxtLink>
            </div>
          </div>

          <div
            v-if="blogs.length"
            class="space-y-4"
          >
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
              Blogs
            </h2>
            <div class="space-y-3">
              <NuxtLink
                v-for="item in blogs"
                :key="`blog-${item.id}`"
                :to="item.url"
                class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
              >
                <img
                  v-if="item.thumbnail"
                  :src="item.thumbnail"
                  :alt="item.title"
                  class="h-20 w-20 rounded-xl object-cover"
                >
                <div
                  v-else
                  class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800"
                >
                  <UIcon
                    name="i-lucide-newspaper"
                    class="h-8 w-8 text-slate-400"
                  />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="line-clamp-1 text-base font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                  <p class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ item.excerpt || 'Read full blog' }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ resolveDate(item.published_at) }}</p>
                </div>
              </NuxtLink>
            </div>
          </div>

          <div
            v-if="news.length"
            class="space-y-4"
          >
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
              News
            </h2>
            <div class="space-y-3">
              <NuxtLink
                v-for="item in news"
                :key="`news-${item.id}`"
                :to="item.url"
                class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
              >
                <img
                  v-if="item.thumbnail"
                  :src="item.thumbnail"
                  :alt="item.title"
                  class="h-20 w-20 rounded-xl object-cover"
                >
                <div
                  v-else
                  class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800"
                >
                  <UIcon
                    name="i-lucide-megaphone"
                    class="h-8 w-8 text-slate-400"
                  />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="line-clamp-1 text-base font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
                  <p class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ item.excerpt || 'Read full news' }}</p>
                  <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ resolveDate(item.published_at) }}</p>
                </div>
              </NuxtLink>
            </div>
          </div>
        </div>

        <div
          v-else
          class="mt-8 space-y-3"
        >
          <NuxtLink
            v-for="item in sectionItems"
            :key="`${item.type}-${item.id}`"
            :to="item.url"
            class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
          >
            <img
              v-if="item.thumbnail"
              :src="item.thumbnail"
              :alt="item.title"
              class="h-20 w-20 rounded-xl object-cover"
            >
            <div
              v-else
              class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800"
            >
              <UIcon
                :name="item.type === 'product' ? 'i-lucide-package' : item.type === 'blog' ? 'i-lucide-newspaper' : 'i-lucide-megaphone'"
                class="h-8 w-8 text-slate-400"
              />
            </div>
            <div class="min-w-0 flex-1">
              <p class="line-clamp-1 text-base font-semibold text-slate-900 dark:text-white">{{ item.title }}</p>
              <p class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300">{{ item.excerpt || 'Open details' }}</p>
              <p
                v-if="item.type === 'product'"
                class="mt-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400"
              >{{ item.price_formatted }}</p>
              <p
                v-else
                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
              >{{ resolveDate(item.published_at) }}</p>
            </div>
          </NuxtLink>
        </div>
      </template>
    </section>
  </div>
</template>
