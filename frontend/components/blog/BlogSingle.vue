<template>
  <div class="max-w-5xl mx-auto px-4 py-10 bg-white dark:bg-slate-900 rounded-xl shadow-lg transition-colors duration-300">

    <div class="mb-4 flex items-center gap-3 text-gray-700 dark:text-gray-400 text-sm">
      <NuxtLink to="/" class="hover:underline">Home</NuxtLink>
      <span>/</span>
      <NuxtLink :to="`/blog/category/${post.category.url}`" class="hover:underline">{{ post.category.name }}</NuxtLink>
      <span>/</span>
      <span class="font-semibold truncate">{{ post.title }}</span>
    </div>

    <h1 class="text-4xl font-extrabold mb-6 text-gray-900 dark:text-white leading-tight">
      {{ post.title }}
    </h1>

    <div class="mb-4 flex items-center gap-4 text-gray-500 dark:text-gray-400 text-sm">
      <div>By {{ post.author.name }}</div>
      <div>Updated on {{ new Date(post.updated_at).toLocaleDateString() }}</div>
    </div>

    <img
        v-if="post.thumbnail"
        :src="post.thumbnail"
        alt="thumbnail"
        class="w-full h-64 object-cover rounded-lg mb-8"
    />

    <div class="prose max-w-none dark:prose-invert mb-10 whitespace-pre-wrap" v-html="post.description"></div>

    <!-- Related Posts -->
    <section v-if="relatedPosts.length > 0">
      <h2 class="text-2xl font-bold mb-6 dark:text-white">Related Posts</h2>
      <div class="grid sm:grid-cols-1 md:grid-cols-2 gap-6">
        <div
            v-for="related in relatedPosts"
            :key="related.id"
            class="cursor-pointer bg-gray-100 dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition p-4"
            @click="goToPost(related.url)"
        >
          <h3 class="font-semibold text-gray-900 dark:text-white">{{ related.title }}</h3>
          <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">{{ related.excerpt }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSanctumFetch, useRuntimeConfig } from '#imports'

interface Post {
  id: number
  url: string
  title: string
  excerpt: string
  description: string
  image?: string
  thumbnail?: string
  author: {
    id: number
    name: string
  }
  updated_at: string
  category: {
    id: number
    name: string
    url: string
  }
}

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()

const post = ref<Post | null>(null)
const relatedPosts = ref<Post[]>([])

const postUrl = computed(() => route.params.url as string)

const fetchPost = async () => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/blogs/${postUrl.value}`)
    if (response.data?.data) {
      post.value = response.data.data
      fetchRelatedPosts()
    }
  } catch (e) {
    console.error('Failed to load post:', e)
  }
}

const fetchRelatedPosts = async () => {
  if (!post.value) return
  try {
    const url = new URL(`${config.public.apiBase}/blogs`)
    url.searchParams.append('category', post.value.category.url)
    url.searchParams.append('exclude', post.value.url) // exclude current post
    url.searchParams.append('per_page', '6')
    const response = await useSanctumFetch(url.toString())
    if (response.data?.data) {
      relatedPosts.value = response.data.data.map((p: any) => ({
        id: p.id || p.url,
        url: p.url,
        title: p.name,
        excerpt: p.short_description || '',
        updated_at: p.updated_at
      }))
    }
  } catch (e) {
    console.error('Failed to fetch related posts:', e)
  }
}

const goToPost = (url: string) => {
  router.push(`/blog/${url}`)
}

onMounted(fetchPost)

watch(postUrl, () => {
  fetchPost()
})
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  overflow: hidden;
}
</style>
