<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">

    <!-- Hero Section with Optional Carousel -->
    <section class="relative h-[500px] md:h-[600px] overflow-hidden py-8 md:py-12">
      <div v-if="heroSlides.length > 1">
        <!-- Multiple slides - Use Swiper -->
        <Swiper
            :modules="[SwiperAutoplay, SwiperNavigation, SwiperPagination]"
            :slides-per-view="1"
            :autoplay="{ delay: 5000 }"
            :pagination="{ clickable: true }"
            :navigation="true"
            class="h-full w-full"
        >
          <SwiperSlide v-for="slide in heroSlides" :key="slide.id">
            <div class="relative h-full w-full flex items-center justify-center bg-gradient-to-r from-blue-600 to-purple-700">
              <!-- Background Image (if provided) -->
              <div
                  v-if="slide.image"
                  class="absolute inset-0 bg-cover bg-center"
                  :style="{ backgroundImage: `url(${slide.image})` }"
              >
                <div class="absolute inset-0 bg-black/40"></div>
              </div>

              <!-- Content -->
              <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-7xl font-bold mb-4 drop-shadow-lg">
                  {{ slide.title }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 drop-shadow-md">
                  {{ slide.subtitle }}
                </p>
                <NuxtLink
                    :to="firstCategoryLink"
                    class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-orange-500 to-red-600 text-white font-bold text-lg rounded-full hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                >
                  {{ slide.cta || 'Start Shopping' }}
                  <Icon name="mdi:arrow-right" class="w-5 h-5" />
                </NuxtLink>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>
      </div>

      <div v-else>
        <!-- Single slide - No Swiper needed -->
        <div class="relative h-full w-full flex items-center justify-center bg-gradient-to-r from-blue-600 to-purple-700 py-5 lg:py-10">
          <div
              v-if="heroSlides[0]?.image"
              class="absolute inset-0 bg-cover bg-center"
              :style="{ backgroundImage: `url(${heroSlides[0].image})` }"
          >
            <div class="absolute inset-0 bg-black/40"></div>
          </div>

          <div class="relative z-10 text-center text-white px-4 max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-7xl font-bold mb-4 drop-shadow-lg">
              {{ heroSlides[0]?.title || 'Welcome to Our Store' }}
            </h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90 drop-shadow-md">
              {{ heroSlides[0]?.subtitle || 'Your one-stop shop for everything!' }}
            </p>
            <NuxtLink
                :to="firstCategoryLink"
                class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-orange-500 to-red-600 text-white font-bold text-lg rounded-full hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
            >
              {{ heroSlides[0]?.cta || 'Start Shopping' }}
              <Icon name="mdi:arrow-right" class="w-5 h-5" />
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Floating Stats -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 hidden lg:flex gap-8 z-20">
        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 text-center text-white">
          <div class="text-2xl font-bold">1M+</div>
          <div class="text-sm opacity-80">Happy Customers</div>
        </div>
        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 text-center text-white">
          <div class="text-2xl font-bold">50K+</div>
          <div class="text-sm opacity-80">Products</div>
        </div>
        <div class="bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 text-center text-white">
          <div class="text-2xl font-bold">24/7</div>
          <div class="text-sm opacity-80">Support</div>
        </div>
      </div>
    </section>

    <!-- Quick Categories Grid -->
    <section class="py-12 md:py-16 bg-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-10 md:mb-12">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
            Shop by Category
          </h2>
          <NuxtLink
              to="/categories"
              class="text-blue-600 dark:text-blue-400 hover:text-blue-800 font-medium flex items-center gap-2"
          >
            View All
            <Icon name="mdi:arrow-right" class="w-4 h-4" />
          </NuxtLink>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
          <NuxtLink
              v-for="category in quickCategories"
              :key="category.url"
              :to="`/category/${category.url}`"
              class="group"
          >
            <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-md hover:shadow-xl transition-all duration-300 group-hover:scale-105 border border-gray-100 dark:border-gray-600">
              <!-- Image -->
              <div class="aspect-square mb-4 overflow-hidden rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-600 dark:to-gray-500">
                <img
                    :src="category.image"
                    :alt="category.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                />
              </div>

              <!-- Content -->
              <div class="text-center">
                <h3 class="font-semibold text-sm md:text-base text-gray-900 dark:text-white mb-1 line-clamp-2">
                  {{ category.name }}
                </h3>
                <p class="text-xs md:text-sm text-green-600 dark:text-green-400 font-medium">
                  From ₹{{ category.starting_from_price }}
                </p>
              </div>
            </div>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Flash Deals Banner -->
    <section class="py-10 md:py-14 px-4 md:px-12">
      <div class="max-w-7xl mx-auto">
        <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white p-8 md:p-10 rounded-2xl flex flex-col md:flex-row justify-between items-center shadow-lg">
          <div class="mb-6 md:mb-0 text-center md:text-left">
            <div class="flex items-center gap-2 mb-3 justify-center md:justify-start">
              <Icon name="mdi:flash" class="w-6 h-6 text-yellow-300" />
              <span class="text-sm font-medium bg-yellow-400 text-black px-2 py-1 rounded-full">LIMITED TIME</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold mb-2">Flash Sale - Up to 70% Off!</h2>
            <p class="text-red-100">Don't miss out on these incredible deals</p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3">
            <!--                :to="firstCategoryLink"-->
            <NuxtLink
            :to="`/store/flash-deals`"
                class="bg-white text-red-600 px-6 py-3 rounded-xl font-semibold hover:bg-red-50 transition-all duration-200 flex items-center gap-2"
            >
              <Icon name="mdi:fire" class="w-5 h-5" />
              Shop Deals
            </NuxtLink>
            <div class="bg-black/20 backdrop-blur-md rounded-xl px-4 py-3 text-center">
              <div class="text-xs text-red-100">Ends in</div>
              <div class="font-bold">23:45:12</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section v-if="featuredProducts.length" class="py-12 md:py-16 bg-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-10 md:mb-12">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center">
              <Icon name="mdi:star" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                Featured Products
              </h2>
              <p class="text-gray-600 dark:text-gray-400">Hand-picked favorites just for you</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
          <div
              v-for="product in featuredProducts.slice(0, 10)"
              :key="product.id"
              class="bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:scale-105 border border-gray-100 dark:border-gray-600 group"
          >
            <div class="relative aspect-square bg-gray-100 dark:bg-gray-600 overflow-hidden">
              <img
                  :src="product.thumbnail"
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                  loading="lazy"
              />
              <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-bold">
                -{{ Math.floor(Math.random() * 30 + 10) }}%
              </div>
            </div>

            <div class="p-3">
              <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-2 line-clamp-2">
                {{ product.name }}
              </h3>
              <div class="flex justify-between items-end">
                <div>
                  <p class="text-lg font-bold text-green-600 dark:text-green-400">
                    ₹{{ product.price }}
                  </p>
                  <div class="flex items-center gap-1 mt-1">
                    <Icon name="mdi:star" class="w-3 h-3 text-yellow-400" />
                    <span class="text-xs text-gray-600 dark:text-gray-400">4.5</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Category Showcase Sections -->
    <section
        v-for="(category, index) in categorySections"
        :key="category.url"
        class="py-12 md:py-16"
        :class="index % 2 === 0 ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800'"
    >
      <div class="max-w-7xl mx-auto px-4">

        <!-- Section Header -->
        <div class="flex justify-between items-center mb-8 md:mb-10">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
              <Icon name="mdi:store" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h2 class="text-xl md:text-3xl font-bold text-gray-900 dark:text-white">
                Best of {{ category.name }}
              </h2>
              <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base">
                {{ category.children.length }} categories available
              </p>
            </div>
          </div>

          <NuxtLink
              :to="`/category/${category.url}`"
              class="hidden md:flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-semibold rounded-xl border border-blue-200 dark:border-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200 shadow-sm"
          >
            View All
            <Icon name="mdi:arrow-right" class="w-4 h-4" />
          </NuxtLink>
        </div>

        <!-- Products Grid -->
        <div class="bg-white dark:bg-gray-700 rounded-2xl p-4 md:p-6 shadow-lg border border-gray-100 dark:border-gray-600">
          <Swiper
              :slides-per-view="2"
              :space-between="16"
              :breakpoints="{
              640: { slidesPerView: 3, spaceBetween: 20 },
              768: { slidesPerView: 4, spaceBetween: 24 },
              1024: { slidesPerView: 5, spaceBetween: 24 },
              1280: { slidesPerView: 6, spaceBetween: 24 }
            }"
              class="category-swiper"
          >
            <SwiperSlide v-for="child in category.children" :key="child.url">
              <NuxtLink :to="`/category/${child.url}`" class="group block">
                <div class="bg-white dark:bg-gray-600 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 group-hover:scale-105 border border-gray-100 dark:border-gray-500">

                  <!-- Image Container -->
                  <div class="relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-500 dark:to-gray-400 overflow-hidden">
                    <img
                        :src="child.image"
                        :alt="child.name"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        loading="lazy"
                    />

                    <!-- Discount Badge -->
                    <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-bold">
                      UP TO {{ Math.floor(Math.random() * 40 + 20) }}% OFF
                    </div>

                    <!-- Quick View Button -->
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <button class="bg-white text-gray-900 px-4 py-2 rounded-lg font-medium shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                        Quick View
                      </button>
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="p-3 md:p-4">
                    <h3 class="font-semibold text-sm md:text-base text-gray-900 dark:text-white mb-2 line-clamp-2 leading-tight">
                      {{ child.name }}
                    </h3>

                    <div class="flex justify-between items-end">
                      <div>
                        <p class="text-lg md:text-xl font-bold text-green-600 dark:text-green-400">
                          ₹{{ child.starting_from_price }}
                        </p>
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 line-through">
                          ₹{{ Math.floor(child.starting_from_price * 1.4) }}
                        </p>
                      </div>

                      <!-- Rating -->
                      <div class="flex items-center gap-1">
                        <Icon name="mdi:star" class="w-3 h-3 text-yellow-400" />
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                          {{ (Math.random() * 2 + 3).toFixed(1) }}
                        </span>
                      </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="mt-2 flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                      <Icon name="mdi:truck-fast" class="w-3 h-3" />
                      <span>Free Delivery</span>
                    </div>
                  </div>
                </div>
              </NuxtLink>
            </SwiperSlide>
          </Swiper>

          <!-- Mobile View All Button -->
          <div class="mt-6 md:hidden">
            <NuxtLink
                :to="`/category/${category.url}`"
                class="w-full flex items-center justify-center gap-2 py-3 text-blue-600 dark:text-blue-400 font-semibold border-2 border-blue-200 dark:border-blue-600 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200"
            >
              View All {{ category.name }}
              <Icon name="mdi:arrow-right" class="w-4 h-4" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-16 md:py-20 bg-white dark:bg-gray-800">
      <div class="max-w-7xl mx-auto px-4">

        <!-- Section Header -->
        <div class="text-center mb-14 md:mb-16">
          <h2 class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            Why Choose Us?
          </h2>
          <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl mx-auto">
            We're committed to providing you the best shopping experience
          </p>
        </div>

        <!-- Trust Features -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
          <div
              v-for="feature in trustFeatures"
              :key="feature.title"
              class="text-center p-6 rounded-2xl bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 hover:shadow-lg transition-all duration-300 group"
          >
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
              <Icon :name="feature.icon" class="w-8 h-8 text-white" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
              {{ feature.title }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFetch } from '#app'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

const SwiperAutoplay = Autoplay
const SwiperNavigation = Navigation
const SwiperPagination = Pagination

const config = useRuntimeConfig()

// Props
const props = defineProps({
  pageType: {
    type: String,
    default: 'home' // 'home' | 'categories'
  }
})

// Data
const categorySections = ref([])
const featuredProducts = ref([])

// Hero slides (can be single or multiple)
const heroSlides = ref([
  {
    id: 1,
    title: 'Welcome to Our Premium Store',
    subtitle: 'Discover amazing products with unbeatable deals!',
    cta: 'Start Shopping',
    image: null // Add image URL here for background
  }
])

// Computed property for first category link
const firstCategoryLink = computed(() => {
  if (categorySections.value.length === 0) {
    return '/categories' // Fallback to categories page
  }

  // Try to get first child category (more specific)
  const firstParent = categorySections.value[0]
  if (firstParent.children && firstParent.children.length > 0) {
    return `/category/${firstParent.children[0].url}`
  }

  // Fallback to first parent category
  return `/category/${firstParent.url}`
})

// Quick categories (flattened from all categories)
const quickCategories = computed(() => {
  return categorySections.value
      .flatMap(parent => parent.children.slice(0, 4))
      .slice(0, 12)
})

// Trust features
const trustFeatures = [
  {
    icon: 'mdi:truck-fast',
    title: 'Fast Delivery',
    description: 'Free same-day delivery on orders above ₹500'
  },
  {
    icon: 'mdi:shield-check',
    title: '100% Secure',
    description: 'Your payments and data are completely safe'
  },
  {
    icon: 'mdi:refresh',
    title: 'Easy Returns',
    description: '7-day return policy with no questions asked'
  },
  {
    icon: 'mdi:headset',
    title: '24/7 Support',
    description: 'Round the clock customer support'
  }
]

// API Calls
const loadStoreData = async () => {
  try {
    // Load categories with products
    const { data: categoryData } = await useFetch(`${config.public.apiBase}/categories/with-products`)
    if (categoryData.value) {
      categorySections.value = categoryData.value
    }

    // Load featured products if available
    const { data: productsData } = await useFetch(`${config.public.apiBase}/products/suggestions/get`)
    if (productsData.value) {
      featuredProducts.value = productsData.value
    }
  } catch (error) {
    console.error('Failed to load store data:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadStoreData()
})

// SEO
const seoTitle = props.pageType === 'categories' ? 'All Categories' : 'Premium Online Store'
const seoDescription = 'Discover amazing products with great deals and fast delivery'

useSeoMeta({
  title: seoTitle,
  description: seoDescription,
  ogTitle: seoTitle,
  ogDescription: seoDescription,
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Swiper Navigation Buttons Styling */
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
  color: #4f46e5;
  background: rgba(255, 255, 255, 0.9);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  backdrop-filter: blur(10px);
}

:deep(.swiper-button-next:after),
:deep(.swiper-button-prev:after) {
  font-size: 16px;
  font-weight: bold;
}

:deep(.swiper-pagination-bullet) {
  background: rgba(255, 255, 255, 0.5);
  opacity: 1;
}

:deep(.swiper-pagination-bullet-active) {
  background: white;
}
</style>
