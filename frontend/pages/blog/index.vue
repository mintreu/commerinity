<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-950 py-10 px-4 md:px-10">
    <!-- Title -->
    <h1 class="text-4xl font-bold mb-10 text-center text-gray-900 dark:text-white tracking-tight">
      Explore Our Blog
    </h1>

    <!-- Featured Swiper Slider -->
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-200">Featured Stories</h2>
      <Swiper
          :slides-per-view="1"
          :space-between="20"
          :breakpoints="{
          640: { slidesPerView: 1 },
          768: { slidesPerView: 2 },
          1024: { slidesPerView: 3 }
        }"
          :pagination="{ clickable: true }"
          class="rounded-xl"
      >
        <SwiperSlide
            v-for="post in featuredPosts"
            :key="post.id"
            class="overflow-hidden rounded-xl shadow-lg group relative"
        >
          <img
              :src="post.image"
              alt=""
              class="h-56 w-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent p-6 flex flex-col justify-end">
            <h3 class="text-lg font-bold text-white">{{ post.title }}</h3>
            <p class="text-sm text-white/90 mt-1">{{ post.excerpt }}</p>
          </div>
        </SwiperSlide>
      </Swiper>
    </section>

    <!-- Blog Content with Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
      <!-- Categories Sidebar -->
      <!-- Mobile Toggle Button (Only visible on mobile) -->
      <div class="lg:hidden mb-4">
        <button
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-md"
            @click="showSidebar = !showSidebar"
        >
          <Icon name="mdi:menu" class="w-5 h-5" />
          <span>Browse Topics</span>
        </button>
      </div>

      <!-- Categories Sidebar -->
      <aside
          class="lg:col-span-1 w-full sticky top-28 self-start bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm"
          :class="{
    'hidden': !showSidebar,
    'block': showSidebar,
    'lg:block': true
  }"
      >
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-5">Explore Topics</h3>
          <ul class="space-y-1">
            <li
                v-for="category in categories"
                :key="category"
            >
              <button
                  type="button"
                  :class="[
            'w-full flex items-center px-3 py-2 rounded-md text-sm font-medium transition-all',
            selectedCategory === category
              ? 'bg-blue-600 text-white shadow'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
          ]"
                  @click="selectedCategory = category"
              >
                <span class="flex-1 text-left">{{ category }}</span>
              </button>
            </li>
          </ul>
        </div>
      </aside>



      <!-- Blog Posts Grid -->
      <section class="lg:col-span-3">
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-8 mb-12">
          <div
              v-for="post in paginatedPosts"
              :key="post.id"
              class="bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-xl transition overflow-hidden"
          >
            <img :src="post.image" alt="" class="h-44 w-full object-cover" />
            <div class="p-5">
              <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">{{ post.title }}</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ post.excerpt }}</p>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ post.date }} · {{ post.author }}
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center gap-2">
          <button
              v-for="page in totalPages"
              :key="page"
              @click="currentPage = page"
              :class="[
              'px-4 py-1 rounded text-sm border',
              currentPage === page
                ? 'bg-blue-600 text-white border-blue-600'
                : 'text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/pagination'

interface Post {
  id: number
  title: string
  excerpt: string
  image: string
  author: string
  date: string
}

const posts: Post[] = [
  {
    id: 1,
    title: 'Getting Started with Nuxt 3',
    excerpt: 'A beginner-friendly guide to building apps with Nuxt 3.',
    image: 'https://placehold.co/600x400?text=Nuxt+3',
    author: 'Jane Doe',
    date: 'July 15, 2025',
  },
  {
    id: 2,
    title: 'Tailwind CSS Tips for Developers',
    excerpt: 'Speed up your frontend workflow with these Tailwind hacks.',
    image: 'https://placehold.co/600x400?text=Tailwind+CSS',
    author: 'John Smith',
    date: 'July 13, 2025',
  },
  {
    id: 3,
    title: 'Animating with GSAP & Vue',
    excerpt: 'Create stunning animations easily with GSAP in Vue 3.',
    image: 'https://placehold.co/600x400?text=GSAP+Vue',
    author: 'Alice Green',
    date: 'July 10, 2025',
  },
  {
    id: 4,
    title: 'Using Dark Mode in Tailwind',
    excerpt: 'Enable seamless light/dark switching using Tailwind utilities.',
    image: 'https://placehold.co/600x400?text=Dark+Mode',
    author: 'David Kim',
    date: 'July 8, 2025',
  },
  {
    id: 5,
    title: 'Deploying Nuxt Apps to Vercel',
    excerpt: 'A step-by-step deployment guide with Git and Vercel.',
    image: 'https://placehold.co/600x400?text=Deploy+Nuxt',
    author: 'Sarah Chen',
    date: 'July 5, 2025',
  },
  {
    id: 6,
    title: 'Nuxt Middleware Best Practices',
    excerpt: 'Protect routes and enhance UX with proper middleware usage.',
    image: 'https://placehold.co/600x400?text=Middleware',
    author: 'Brian Lee',
    date: 'July 2, 2025',
  },
  {
    id: 7,
    title: 'Building Reusable Components',
    excerpt: 'How to design DRY and flexible Vue components.',
    image: 'https://placehold.co/600x400?text=Reusable+UI',
    author: 'Nora Watts',
    date: 'June 28, 2025',
  },
  {
    id: 8,
    title: 'Static Site Generation in Nuxt 3',
    excerpt: 'Unlock performance and SEO using static generation.',
    image: 'https://placehold.co/600x400?text=Static+Site',
    author: 'Isaac Brown',
    date: 'June 25, 2025',
  },
  {
    id: 9,
    title: 'Nuxt 3 Composables You Should Know',
    excerpt: 'Enhance your DX with composables like useFetch and useRoute.',
    image: 'https://placehold.co/600x400?text=Composables',
    author: 'Luna Yang',
    date: 'June 20, 2025',
  },
]

const categories = [
  'All',
  'Vue',
  'Nuxt',
  'Tailwind',
  'GSAP',
  'DevOps',
  'Design',
  'Performance',
  'UI/UX',
  'SEO',
]

const selectedCategory = ref('All')
const showSidebar = ref(false)

const featuredPosts = posts.slice(0, 3)
const perPage = 6
const currentPage = ref(1)

const paginatedPosts = computed(() =>
    posts.slice((currentPage.value - 1) * perPage, currentPage.value * perPage)
)

const totalPages = computed(() => Math.ceil(posts.length / perPage))
</script>
