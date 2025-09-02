<script setup lang="ts">
import {ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import {useRuntimeConfig, useSanctumFetch} from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

const route = useRoute()
const config = useRuntimeConfig()

const page = ref<any>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)


import {useHead} from '#app'

useHead({
  link: [
    {
      rel: 'stylesheet',
      href: '/_nuxt/assets/css/tiptap.css'
    }
  ]
})

// -------------------- Fetch Page --------------------
const fetchPage = async () => {
  isLoading.value = true
  error.value = null
  page.value = null

  try {
    const url = route.path.replace(/^\/+/, '')

    const res = await useSanctumFetch(`${config.public.apiBase}/pages`, {
      method: 'GET',
      params: {url}
    })

    if (!res || !res.data) {
      error.value = 'Oops! Page does not exist.'
      page.value = null
    } else {
      page.value = res.data
    }

  } catch (err: any) {
    console.error('Failed to fetch page:', err)
    error.value = 'Oops! Page does not exist.'
    page.value = null
  } finally {
    isLoading.value = false
  }
}

// Watch for route changes
watch(() => route.fullPath, fetchPage, {immediate: true})
</script>

<template>
  <div class="min-h-screen flex flex-col gap-1 mt-14">

    <!-- Loader -->
    <GlobalLoader v-if="isLoading"/>

    <!-- Error Page -->
    <section v-else-if="error" class="h-full grow flex flex-col justify-center items-center text-center">
      <div class="my-10">
        <h1 class="text-6xl font-bold mb-4">404</h1>
        <p class="text-xl mb-6">{{ error }}</p>
        <nuxt-link to="/" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded">
          Go Home
        </nuxt-link>
      </div>
    </section>

    <!-- Page Content -->
    <div v-else>
      <header class="max-w-7xl mx-auto px-4 py-12 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ page.title }}</h1>
        <p v-if="page.meta?.description" class="text-lg md:text-xl text-gray-600 dark:text-gray-400">
          {{ page.meta.description }}
        </p>
      </header>


      <!--      <main class="max-w-7xl mx-auto px-4 space-y-12">-->
      <!--        <div class="tiptap-frontend-wrapper mx-auto w-full h-auto overflow-y-scroll overflow-x-hidden rounded-b-md prosemirror-w-5xl">-->
      <!--          <div class="tiptap-frontend-content">-->
      <!--            <div v-if="page.content" v-html="page.content"></div>-->
      <!--          </div>-->
      <!--        </div>-->
      <!--      </main>-->


      <main class="max-w-7xl h-full mx-auto px-4 space-y-12 tiptap-editor">

        <div v-if="page.content"
             class="tiptap-prosemirror-wrapper mx-auto w-full h-full overflow-hidden rounded-b-md prosemirror-w-5xl">
          <div class="tiptap-content min-h-full">
            <div v-if="page.content" v-html="page.content"></div>
          </div>
        </div>


      </main>
    </div>

  </div>
</template>

