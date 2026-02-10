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
  published_at?: string | null
  category?: Category | null
}

const config = useRuntimeConfig()

const blogs = ref<PostItem[]>([])
const news = ref<PostItem[]>([])
const status = ref<'idle' | 'loading' | 'error'>('idle')
const errorMessage = ref<string | null>(null)

const fetchLatest = async () => {
  status.value = 'loading'
  errorMessage.value = null

  try {
    const [blogResponse, newsResponse] = await Promise.all([
      useSanctumFetch<{ data: PostItem[] }>(`${config.public.apiBase}/api/blogs?per_page=3`),
      useSanctumFetch<{ data: PostItem[] }>(`${config.public.apiBase}/api/news?per_page=3`)
    ])

    blogs.value = blogResponse.data || []
    news.value = newsResponse.data || []
    status.value = 'idle'
  } catch (err: unknown) {
    status.value = 'error'
    errorMessage.value = err instanceof Error ? err.message : 'Unable to load updates right now.'
  }
}

onMounted(fetchLatest)
</script>

<template>
  <section class="mt-12 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-900/60 p-8 backdrop-blur">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Latest updates</p>
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">From the blog and newsroom</h3>
      </div>
      <div class="flex items-center gap-4 text-sm">
        <NuxtLink to="/blogs" class="theme-text-primary font-semibold hover:underline">View blog</NuxtLink>
        <NuxtLink to="/news" class="theme-text-primary font-semibold hover:underline">View news</NuxtLink>
      </div>
    </div>

    <div v-if="status === 'loading'" class="mt-8 grid gap-6 md:grid-cols-2">
      <div v-for="i in 2" :key="i" class="space-y-4 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 bg-white dark:bg-slate-900 p-6 animate-pulse">
        <div class="h-4 w-1/2 rounded bg-slate-200 dark:bg-slate-800" />
        <div class="h-6 w-3/4 rounded bg-slate-200 dark:bg-slate-800" />
        <div class="h-4 w-full rounded bg-slate-200 dark:bg-slate-800" />
      </div>
    </div>

    <div v-else-if="status === 'error'" class="mt-6 text-sm text-red-600 dark:text-red-400">
      {{ errorMessage }}
    </div>

    <div v-else class="mt-8 grid gap-6 md:grid-cols-2">
      <div>
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">Blog</h4>
          <NuxtLink to="/blogs" class="text-xs font-semibold theme-text-primary hover:underline">All posts</NuxtLink>
        </div>
        <div v-if="blogs.length" class="mt-4 space-y-4">
          <NuxtLink
            v-for="post in blogs"
            :key="post.id"
            :to="`/blogs/${post.slug}`"
            class="group block rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition hover:-translate-y-0.5 hover:shadow-lg"
          >
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
              {{ post.category?.name || 'Stories' }}
            </p>
            <h5 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white group-hover:underline">
              {{ post.title }}
            </h5>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 line-clamp-2">
              {{ post.excerpt || 'Read the full story.' }}
            </p>
          </NuxtLink>
        </div>
        <p v-else class="mt-4 text-sm text-slate-500 dark:text-slate-400">
          {{ getEmptyStateMessage('general', 'No blog posts available.') }}
        </p>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">News</h4>
          <NuxtLink to="/news" class="text-xs font-semibold theme-text-primary hover:underline">All updates</NuxtLink>
        </div>
        <div v-if="news.length" class="mt-4 space-y-4">
          <NuxtLink
            v-for="post in news"
            :key="post.id"
            :to="`/news/${post.slug}`"
            class="group block rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition hover:-translate-y-0.5 hover:shadow-lg"
          >
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
              {{ post.category?.name || 'Updates' }}
            </p>
            <h5 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white group-hover:underline">
              {{ post.title }}
            </h5>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 line-clamp-2">
              {{ post.excerpt || 'Read the full update.' }}
            </p>
          </NuxtLink>
        </div>
        <p v-else class="mt-4 text-sm text-slate-500 dark:text-slate-400">
          {{ getEmptyStateMessage('general', 'No news available.') }}
        </p>
      </div>
    </div>
  </section>
</template>
