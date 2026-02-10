<script setup lang="ts">
import { useRuntimeConfig } from '#app'
import FilamentContent from '~/components/FilamentContent.vue'

interface Category {
  id: number
  name: string
  slug: string
}

interface PostDetail {
  id: number
  title: string
  slug: string
  excerpt?: string | null
  content?: string | null
  cover_image?: string | null
  author_name?: string | null
  published_at?: string | null
  seo_title?: string | null
  seo_description?: string | null
  category?: Category | null
}

interface RelatedPost {
  id: number
  title: string
  slug: string
  excerpt?: string | null
  cover_image?: string | null
  published_at?: string | null
  category?: Category | null
}

const props = defineProps<{
  type: 'blog' | 'news'
}>()

const config = useRuntimeConfig()
const route = useRoute()

const post = ref<PostDetail | null>(null)
const related = ref<RelatedPost[]>([])
const status = ref<'idle' | 'loading' | 'error'>('idle')
const errorMessage = ref<string | null>(null)

const baseUrl = computed(() => `${config.public.apiBase}/api/${props.type === 'blog' ? 'blogs' : 'news'}`)
const slug = computed(() => String(route.params.slug || ''))

const fetchPost = async () => {
  status.value = 'loading'
  errorMessage.value = null

  try {
    const response = await useSanctumFetch<PostDetail>(`${baseUrl.value}/${slug.value}`)
    post.value = response
    status.value = 'idle'

    const relatedResponse = await useSanctumFetch<{ data: RelatedPost[] }>(
      `${baseUrl.value}/${slug.value}/related`
    )
    related.value = relatedResponse.data || []

    useSeoMeta({
      title: response.seo_title || response.title,
      description: response.seo_description || response.excerpt || '',
      ogTitle: response.seo_title || response.title,
      ogDescription: response.seo_description || response.excerpt || '',
      ogImage: response.cover_image || undefined
    })
  } catch (err: unknown) {
    status.value = 'error'
    errorMessage.value = err instanceof Error ? err.message : 'Unable to load content right now.'
  }
}

watch(() => route.params.slug, fetchPost)

onMounted(fetchPost)
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <section class="theme-gradient-brand text-white py-16">
      <div class="mx-auto max-w-4xl px-6 text-center space-y-4">
        <p class="text-sm uppercase tracking-[0.3em] text-white/70">
          {{ props.type === 'blog' ? 'Blog' : 'News' }}
        </p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight" v-if="post">
          {{ post.title }}
        </h1>
        <p v-if="post?.excerpt" class="text-lg text-white/80 max-w-3xl mx-auto">
          {{ post.excerpt }}
        </p>
      </div>
    </section>

    <section class="mx-auto max-w-4xl px-6 py-12 space-y-8">
      <div v-if="status === 'loading'" class="space-y-4 animate-pulse">
        <div class="h-64 bg-slate-200 dark:bg-slate-800 rounded-2xl" />
        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-3/4" />
        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/2" />
      </div>

      <div v-else-if="status === 'error'" class="text-center text-red-600 dark:text-red-400">
        {{ errorMessage }}
      </div>

      <div v-else-if="post" class="space-y-6">
        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
          <span v-if="post.category">{{ post.category.name }}</span>
          <span v-if="post.published_at">- {{ new Date(post.published_at).toLocaleDateString() }}</span>
          <span v-if="post.author_name">- {{ post.author_name }}</span>
        </div>

        <div v-if="post.cover_image" class="rounded-3xl overflow-hidden shadow-lg">
          <img :src="post.cover_image" :alt="post.title" class="w-full h-80 object-cover">
        </div>

        <FilamentContent v-if="post.content" :content="post.content" class="prose prose-slate dark:prose-invert max-w-none" />
      </div>

      <div v-if="related.length" class="pt-6 border-t border-slate-200 dark:border-slate-800">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Related</h3>
        <div class="grid gap-6 md:grid-cols-2">
          <NuxtLink
            v-for="item in related"
            :key="item.id"
            :to="`/${props.type === 'blog' ? 'blogs' : 'news'}/${item.slug}`"
            class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 hover:shadow-lg transition"
          >
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ item.category?.name || '' }}
            </p>
            <h4 class="text-lg font-semibold text-slate-900 dark:text-white mt-2">
              {{ item.title }}
            </h4>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 line-clamp-2">
              {{ item.excerpt || 'Read more...' }}
            </p>
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>


