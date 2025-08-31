<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useRuntimeConfig, useSanctumFetch } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const route = useRoute()
const config = useRuntimeConfig()

const page = ref<any>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)

// -------------------- Fetch Page --------------------
const fetchPage = async () => {
  isLoading.value = true
  error.value = null
  page.value = null

  try {
    // full dynamic url (strip leading slash)
    const url = route.path.replace(/^\/+/, '')

    const res = await useSanctumFetch(`${config.public.apiBase}/pages`, {
      method: 'GET',
      params: { url }
    })

// check correct structure
    if (!res || !res.data) {
      error.value = 'Oops! Page does not exist.'
      page.value = null
    } else {
      page.value = res.data   // ✅ assign from res.data
      await nextTick()
      injectCustomCSS()
      executeCustomJS()
      initGSAPAnimations()
    }

  } catch (err: any) {
    console.error('Failed to fetch page:', err)
    error.value = 'Oops! Page does not exist.'
    page.value = null
  } finally {
    isLoading.value = false
  }
}

// -------------------- Watch route changes --------------------
watch(() => route.fullPath, fetchPage, { immediate: true })

// -------------------- Dynamic Sections --------------------
const getComponent = (type: string) => {
  const map: Record<string, string> = {
    hero: 'HeroSection',
    text: 'TextSection',
    image: 'ImageSection',
    swiper: 'SwiperSection',
  }
  return map[type] || 'div'
}

// -------------------- Dynamic CSS --------------------
const injectCustomCSS = () => {
  if (!page.value?.custom_css) return
  const existing = document.getElementById('dynamic-page-css')
  if (existing) existing.remove()
  const style = document.createElement('style')
  style.id = 'dynamic-page-css'
  style.innerHTML = page.value.custom_css
  document.head.appendChild(style)
}

// -------------------- Dynamic JS --------------------
const executeCustomJS = () => {
  if (!page.value?.custom_js) return
  try {
    new Function(page.value.custom_js)()
  } catch (e) {
    console.error('Error executing page JS:', e)
  }
}

// -------------------- GSAP Animations --------------------
const initGSAPAnimations = () => {
  if (!page.value?.sections) return
  gsap.utils.toArray('.animate-section').forEach((section) => {
    gsap.from(section, {
      y: 50,
      opacity: 0,
      duration: 1,
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
      },
    })
  })
}
</script>

<template>
  <div class="min-h-screen flex flex-col gap-1 mt-14">

    <!-- Loader -->
    <GlobalLoader v-if="isLoading" />

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

      <main class="max-w-7xl mx-auto px-4 space-y-12">
        <div v-if="page.content" class="prose dark:prose-invert" v-html="page.content"></div>

        <div v-if="page.sections" class="space-y-16">
          <component
              v-for="(section, index) in page.sections"
              :key="index"
              :is="getComponent(section.type)"
              class="animate-section"
              v-bind="section.props || {}"
          />
        </div>
      </main>
    </div>

  </div>
</template>

<style scoped>
.animate-section {
  transition: all 0.5s ease-in-out;
}
</style>
