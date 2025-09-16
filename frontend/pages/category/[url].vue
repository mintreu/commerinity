<template>
  <div class="min-h-screen bg-white dark:bg-gray-950 text-gray-800 dark:text-gray-100 py-8 px-4 md:px-12">
    <!-- Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- Error Section -->
    <section v-else-if="error" class="flex flex-col items-center justify-center min-h-[60vh] text-center px-6">
      <div class="text-red-600 dark:text-red-400 text-6xl mb-4">
        <Icon name="mdi:alert-circle-outline" />
      </div>
      <h2 class="text-2xl font-bold mb-2 text-red-700 dark:text-red-300">Category Unavailable</h2>
      <p class="text-sm text-red-600 dark:text-red-300 max-w-md mx-auto mb-4">
        We couldn’t load this category at the moment. It may have been removed or is temporarily unavailable.
        Please check the URL or try again later.
      </p>
      <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
        <strong>Error:</strong> {{ error.message }}
      </p>
      <NuxtLink to="/category">
        <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition shadow">
          Browse Other Categories
        </button>
      </NuxtLink>
    </section>

    <!-- Content -->
    <template v-else>
      <!-- Banner -->
      <section class="text-center mb-6">
        <div
            class="relative py-16 px-6 bg-center bg-cover bg-no-repeat rounded-xl overflow-hidden h-96"
            :style="`background-image: url(${category.banner})`"
        >
          <div class="absolute inset-0 bg-black/30"></div>
          <div class="relative z-10">
            <div class="inline-block mb-4">
              <img :src="category.thumbnail" alt="Thumbnail" class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-white shadow" />
            </div>
            <h1 class="text-3xl font-bold capitalize mb-2 text-white">{{ category.name }}</h1>
            <p class="text-sm text-gray-200">{{ category.views }} views</p>
          </div>
        </div>
      </section>

      <!-- Description -->
      <section class="text-center mb-12 max-w-2xl mx-auto">
        <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed">
          Discover the finest selection in <strong>{{ category.name }}</strong>. This category features exclusive listings curated to bring you unmatched value and quality.
        </p>
      </section>

      <!-- Subcategories -->
      <section v-if="category.children?.length" class="mb-16">
        <h2 class="text-2xl font-bold mb-6">Subcategories</h2>
        <Swiper
            :slides-per-view="1"
            :space-between="20"
            :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 }, 1280: { slidesPerView: 6 } }"
            class="my-6"
        >
          <SwiperSlide v-for="child in category.children" :key="child.url">
            <NuxtLink :to="`/category/${child.url}`" class="block hover:no-underline">
              <div class="bg-gray-100 dark:bg-gray-900 p-4 w-48 md:w-full md:h-60 rounded-lg shadow hover:shadow-lg transition hover:border-2 border-gray-400">
                <img :src="child.thumbnail" class="w-full h-32 object-cover rounded mb-4"  alt=""/>
                <h3 class="text-lg font-semibold">{{ child.name }}</h3>
                <p class="text-xs text-gray-500">{{ child.views }} views</p>
              </div>
            </NuxtLink>
          </SwiperSlide>
        </Swiper>
      </section>

      <div class="flex flex-row gap-6 items-center w-full">
        <span class="grow"> </span>
        <CartCounter />
      </div>

      <!-- Latest Products -->
      <section v-if="latestProducts.length">
          <h2 class="text-2xl grow font-bold mb-6 min-w-1/2">Latest Arrivals</h2>
        <Swiper
            :slides-per-view="1"
            :space-between="20"
            :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 6 } }"
            class="my-6"
        >
          <SwiperSlide v-for="product in latestProducts" :key="product.url">
            <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition">
              <NuxtLink :to="`/product/${product.url}`" class="block">
                <img
                    loading="lazy"
                    :src="product.thumbnail"
                    class="h-[250px] w-full object-contain rounded mb-4"
                    alt=""
                />
                <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                <p class="text-sm text-gray-500 mb-1">${{ product.price }}</p>
                <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
              </NuxtLink>

              <!-- ✅ Moved outside the NuxtLink so click works -->
              <AddToCartButton :sku="product.sku" :quantity="1" class="mt-2 w-full" />
            </div>
          </SwiperSlide>
        </Swiper>
      </section>


      <!-- All Products -->
      <section class="mb-16 w-full">
        <div class="flex flex-row items-center gap-4">
          <h2 class="text-2xl font-bold mb-6 grow">All Products</h2>
        </div>

        <div class="flex flex-col md:flex-row gap-6 mb-8">
          <!-- Sidebar filters ... (kept same) -->
          <aside class="w-full md:w-1/4 space-y-6">
              <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4 hidden md:block">Filters</h2>

                <div class="md:hidden flex justify-between items-center mb-4">
                  <h2 class="text-xl font-bold">Filters</h2>
                  <button @click="mobileFilterOpen = !mobileFilterOpen" class="flex items-center gap-2 text-blue-600 font-medium">
                    <Icon name="mdi:filter-variant" class="w-5 h-5" />
                    <span>{{ mobileFilterOpen ? 'Hide' : 'Show' }}</span>
                  </button>
                </div>

                <!--              Toggle this div only in mobile screen, in mobile screen default div hide or colapsed and dekstop default show-->
                <div :class="{'hidden': !mobileFilterOpen, 'block': mobileFilterOpen, 'md:block': true}">


                  <!-- Categories -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Categories</h3>
                    <ul class="space-y-1 text-sm">
                      <li><input type="checkbox" id="food-products" class="mr-2"><label for="food-products">Food Products</label></li>
                      <li><input type="checkbox" id="dry-fruit" class="mr-2"><label for="dry-fruit">Dry Fruit, Nut & Seed</label></li>
                      <li><input type="checkbox" id="dry-fruits" class="mr-2"><label for="dry-fruits">Dry Fruits</label></li>
                      <li><input type="checkbox" id="gift-pack" class="mr-2"><label for="gift-pack">Dry Fruits Gift Pack</label></li>
                      <li><input type="checkbox" id="seeds" class="mr-2"><label for="seeds">Edible Seed</label></li>
                      <li><input type="checkbox" id="mixed" class="mr-2"><label for="mixed">Mixed Dry Fruits</label></li>
                    </ul>
                  </div>

                  <!-- Price -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Price</h3>
                    <div class="flex items-center gap-2">
                      <input type="number" placeholder="Min" class="w-1/2 px-2 py-1 rounded border text-sm" />
                      <span class="text-sm">to</span>
                      <input type="number" placeholder="Max" class="w-1/2 px-2 py-1 rounded border text-sm" />
                    </div>
                  </div>

                  <!-- Brand -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Brand</h3>
                    <input type="text" placeholder="Search brand" class="w-full px-2 py-1 mb-2 rounded border text-sm" />
                    <div class="space-y-1 text-sm max-h-24 overflow-y-auto">
                      <label><input type="checkbox" class="mr-2"> Happilo</label><br />
                      <label><input type="checkbox" class="mr-2"> Nutraj</label><br />
                      <label><input type="checkbox" class="mr-2"> Farmley</label><br />
                      <!-- Add more -->
                    </div>
                  </div>

                  <!-- Customer Ratings -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Customer Ratings</h3>
                    <div class="space-y-1 text-sm">
                      <label><input type="checkbox" class="mr-2"> 4★ & above</label><br />
                      <label><input type="checkbox" class="mr-2"> 3★ & above</label><br />
                      <label><input type="checkbox" class="mr-2"> 2★ & above</label><br />
                      <label><input type="checkbox" class="mr-2"> 1★ & above</label>
                    </div>
                  </div>

                  <!-- Offers -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Offers</h3>
                    <div class="space-y-1 text-sm">
                      <label><input type="checkbox" class="mr-2"> Buy More, Save More</label><br />
                      <label><input type="checkbox" class="mr-2"> Special Price</label>
                    </div>
                  </div>

                  <!-- Discounts -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Discount</h3>
                    <div class="space-y-1 text-sm">
                      <label><input type="checkbox" class="mr-2"> 50% or more</label><br />
                      <label><input type="checkbox" class="mr-2"> 40% or more</label><br />
                      <label><input type="checkbox" class="mr-2"> 30% or more</label><br />
                      <label><input type="checkbox" class="mr-2"> 20% or more</label><br />
                      <label><input type="checkbox" class="mr-2"> 10% or more</label>
                    </div>
                  </div>

                  <!-- GST -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">GST Invoice</h3>
                    <label class="text-sm"><input type="checkbox" class="mr-2"> GST Invoice Available</label>
                  </div>

                  <!-- Availability -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Availability</h3>
                    <label class="text-sm"><input type="checkbox" class="mr-2"> Include Out of Stock</label>
                  </div>

                  <!-- Container Type -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Container Type</h3>
                    <div class="space-y-1 text-sm max-h-24 overflow-y-auto">
                      <label><input type="checkbox" class="mr-2"> Bag</label><br />
                      <label><input type="checkbox" class="mr-2"> Box</label><br />
                      <label><input type="checkbox" class="mr-2"> Bulk Pack</label><br />
                      <label><input type="checkbox" class="mr-2"> Can</label><br />
                      <label><input type="checkbox" class="mr-2"> Carton</label><br />
                      <label><input type="checkbox" class="mr-2"> Festive Gift Box</label>
                    </div>
                  </div>

                  <!-- Organic -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Organic</h3>
                    <label><input type="radio" name="organic" class="mr-2"> Yes</label><br />
                    <label><input type="radio" name="organic" class="mr-2"> No</label>
                  </div>

                  <!-- Type -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Type</h3>
                    <input type="text" placeholder="Search Type" class="w-full px-2 py-1 mb-2 rounded border text-sm" />
                    <div class="space-y-1 text-sm max-h-24 overflow-y-auto">
                      <label><input type="checkbox" class="mr-2"> Almonds</label><br />
                      <label><input type="checkbox" class="mr-2"> Alphalpha Seeds</label><br />
                      <label><input type="checkbox" class="mr-2"> Pumpkin Seeds</label><br />
                      <!-- More... -->
                    </div>
                  </div>

                  <!-- Pack of -->
                  <div class="mb-4">
                    <h3 class="font-semibold text-sm mb-2 uppercase">Pack Of</h3>
                    <div class="space-y-1 text-sm">
                      <label><input type="checkbox" class="mr-2"> 1</label><br />
                      <label><input type="checkbox" class="mr-2"> 2</label><br />
                      <label><input type="checkbox" class="mr-2"> 3</label><br />
                      <label><input type="checkbox" class="mr-2"> 4</label><br />
                      <label><input type="checkbox" class="mr-2"> 10 & Above</label>
                    </div>
                  </div>
                </div>

              </div>
          </aside>

          <!-- Products Grid -->
          <div class="w-full md:w-3/4">
            <div class="flex justify-between items-center mb-4">
              <h1 class="text-xl font-semibold">All Products</h1>
              <div class="relative w-full md:w-auto">
                <label class="sr-only" for="sort">Sort By</label>
                <select
                    id="sort"
                    v-model="selectedSort"
                    class="border border-gray-300 dark:border-gray-700 rounded px-4 py-2 text-sm bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 shadow-sm"
                >
                  <option v-for="sort in sortOptions" :key="sort.name" :value="sort">
                    {{ sort.name.replace(/([a-z])([A-Z])/g, '$1 $2').toUpperCase() }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <div v-for="product in allProducts" :key="product.url" class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-md transition">
                <NuxtLink :to="`/product/${product.url}`">
                  <div class="w-full h-52 mx-auto rounded mb-4">
                    <img loading="lazy" :src="product.thumbnail" class="h-full w-full object-contain rounded mb-4" alt="" />
                  </div>
                  <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500 mb-1">${{ product.price }}</p>
                  <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
                </NuxtLink>
                <AddToCartButton :sku="product.sku" :quantity="1" />
              </div>
            </div>

            <!-- Pagination -->
            <div class="text-center mt-8">
              <button
                  @click="loadNextPage"
                  :disabled="pagination.current_page >= pagination.last_page"
                  class="px-6 py-2 border rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition disabled:opacity-50"
              >
                Load More
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Top Products -->
      <section v-if="topProducts.length" class="mb-16">
        <h2 class="text-2xl font-bold mb-6">Top Products</h2>
        <Swiper
            :slides-per-view="1"
            :space-between="20"
            :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 }, 1280: { slidesPerView: 6 } }"
            class="my-6"
        >
          <SwiperSlide v-for="product in topProducts" :key="product.url">
            <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition">
              <NuxtLink :to="`/product/${product.url}`" class="block">
                <img loading="lazy" :src="product.thumbnail" class="h-[250px] w-full object-contain rounded mb-4" alt="" />
                <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                <p class="text-sm text-gray-500 mb-1">${{ product.price }}</p>
                <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
              </NuxtLink>

              <!-- ✅ Outside NuxtLink so click works -->
              <AddToCartButton :sku="product.sku" :quantity="1" class="mt-2 w-full" />
            </div>
          </SwiperSlide>

        </Swiper>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import GlobalLoader from "~/components/GlobalLoader.vue"
import AddToCartButton from "~/components/cart/AddToCartButton.vue"

const mobileFilterOpen = ref(false)
const route = useRoute()
const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()
const categoryUrl = route.params.url as string

const isLoading = useState('pageLoading', () => false)
const error = ref<any>(null)

const category = ref<any>({})
const topProducts = ref<any[]>([])
const latestProducts = ref<any[]>([])
const allProducts = ref<any[]>([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const sortOptions = ref<any[]>([])
const selectedSort = ref<any>(null)

async function fetchCategory(page = 1, sortKey = '') {
  isLoading.value = true
  error.value = null
  try {
    const res = await useSanctumFetch(
        `${config.public.apiBase}/categories/${categoryUrl}?page=${page}&sort=${sortKey}`
    )
    category.value = res.data || {}
    topProducts.value = res.top_products || []
    latestProducts.value = res.latest_products || []
    allProducts.value = res.all_products || []
    if (res.pagination) pagination.value = res.pagination
  } catch (e: any) {
    console.error('Category fetch error', e)
    error.value = e
  } finally {
    isLoading.value = false
  }
}

watch(selectedSort, () => fetchCategory(1, selectedSort.value?.key || ''))

const loadNextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    fetchCategory(pagination.value.current_page + 1, selectedSort.value?.key || '')
  }
}

onMounted(async () => {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/sorts/get`)
    sortOptions.value = res || []
    selectedSort.value = sortOptions.value[0] || {}
    await fetchCategory()
  } catch (e) {
    console.error('Sort options fetch failed', e)
  } finally {
    isLoading.value = false
  }
})
</script>
