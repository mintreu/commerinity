<template>
  <div class="min-h-screen bg-white dark:bg-gray-950 text-gray-800 dark:text-gray-100 py-8 px-4 md:px-12">
    <!-- Loading -->
    <div v-if="pending" class="text-center py-20">
      <p class="text-lg">Loading category data...</p>
    </div>

    <!-- Error Section - Full Height 404 Style -->
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
      <!-- Banner Header -->
      <section class="text-center mb-6">
        <div
          class="relative py-16 px-6 bg-center bg-cover bg-no-repeat rounded-xl overflow-hidden"
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
          Discover the finest selection in <strong>{{ category.name }}</strong>. This category features exclusive listings curated to bring you unmatched value and quality. Explore subcategories, trending products, and enjoy special subscriber perks.
        </p>
      </section>

      <!-- Subcategories Slider -->
      <section v-if="category.children?.length" class="mb-16">
        <h2 class="text-2xl font-bold mb-6">Subcategories</h2>
        <Swiper
          :slides-per-view="1"
          :space-between="20"
          :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }"
          class="my-6"
        >
          <SwiperSlide v-for="child in category.children" :key="child.url">
            <NuxtLink :to="`/category/${child.url}`" class="block hover:no-underline">
              <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg shadow hover:shadow-lg transition">
                <img :src="child.thumbnail" class="w-full h-32 object-cover rounded mb-4" />
                <h3 class="text-lg font-semibold">{{ child.name }}</h3>
                <p class="text-xs text-gray-500">{{ child.views }} views</p>
                <div v-if="child.meta?.keywords?.length" class="mt-2 text-sm text-primary-600 dark:text-primary-400">
                  <span v-for="kw in child.meta.keywords" :key="kw" class="mr-2">#{{ kw }}</span>
                </div>
              </div>
            </NuxtLink>
          </SwiperSlide>
        </Swiper>
      </section>

      <!-- Latest Products -->
      <section v-if="latestProducts.length">
        <h2 class="text-2xl font-bold mb-6">Latest Arrivals</h2>
        <Swiper
            :slides-per-view="1"
            :space-between="20"
            :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }"
            class="my-6"
        >
          <SwiperSlide v-for="product in latestProducts" :key="product.url">
            <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition">
              <img :src="product.thumbnail" class="h-40 w-full object-cover rounded mb-4" />
              <h3 class="font-semibold mb-1">{{ product.name }}</h3>
              <p class="text-sm text-gray-500 mb-1">${{ product.price }}</p>
              <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
              <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add to Cart</button>
            </div>
          </SwiperSlide>
        </Swiper>
      </section>


















<!--      Main Product Card Grid display section-->
      <!-- Main Product Card Grid Display Section -->
      <section class="mb-16 w-full">
        <h2 class="text-2xl font-bold mb-6">All Products</h2>

        <!-- Filters (optional future use) -->
        <div class="flex flex-col md:flex-row gap-6 mb-8">
          <!-- Left Sidebar Filters -->
          <!-- Filters Sidebar -->
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



          <div class="w-full md:w-3/4 ">
            <div class="flex justify-center flex-row px-2">

              <h1 class="grow text-xl font-semibold ">All Products</h1>

              <!-- Sorting Dropdown -->
              <div class="flex justify-end mb-4">
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

            </div>





            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="i in 12" :key="i" class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-md transition">
                <div class="w-full h-40 bg-gray-200 dark:bg-gray-800 rounded mb-4"></div>
                <h3 class="font-semibold mb-1">Product Name {{ i }}</h3>
                <p class="text-sm text-gray-500 mb-1">$99.99</p>
                <p class="text-xs text-gray-400 mb-2">Short description of the product goes here.</p>
                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">Add to Cart</button>
              </div>
            </div>
          </div>


        </div>

        <!-- Pagination Placeholder -->
        <div class="text-center mt-8">
          <button class="px-6 py-2 border rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition">Load More</button>
        </div>
      </section>

<!--      Main Product Card Grid display section-->
















      <!-- Top Products -->
      <section v-if="topProducts.length" class="mb-16">
        <h2 class="text-2xl font-bold mb-6">Top Products</h2>
        <Swiper
          :slides-per-view="1"
          :space-between="20"
          :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }"
          class="my-6"
        >
          <SwiperSlide v-for="product in topProducts" :key="product.url">
            <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition">
              <img :src="product.thumbnail" class="h-40 w-full object-cover rounded mb-4" />
              <h3 class="font-semibold mb-1">{{ product.name }}</h3>
              <p class="text-sm text-gray-500 mb-1">${{ product.price }}</p>
              <p class="text-xs text-gray-400 mb-2">{{ product.short_description }}</p>
              <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add to Cart</button>
            </div>
          </SwiperSlide>
        </Swiper>
      </section>




      <!-- View All Products CTA -->
<section class="relative py-16 px-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl mt-12 overflow-hidden">
  <div class="text-center max-w-2xl mx-auto z-10 relative">
    <h2 class="text-3xl font-bold mb-4">See More of {{ category.name }}</h2>
    <p class="text-lg mb-6">
      Explore the full collection of products available in this category. Discover great deals, curated items, and hidden gems just waiting for you.
    </p>
    <NuxtLink :to="`/category/${category.url}/products`">
      <button class="bg-white text-indigo-700 font-semibold px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition">
        View All Products
      </button>
    </NuxtLink>
  </div>
</section>


    </template>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
const mobileFilterOpen = ref(false);
const route = useRoute()
const categoryUrl = route.params.url as string

const { data, pending, error } = await useFetch(() => `http://localhost:8000/api/categories/${categoryUrl}`)

const category = data?.value?.data || {}
const topProducts = data?.value?.top_products || []
const latestProducts = data?.value?.latest_products || []

const sortOptions = ref([]);
const selectedSort = ref(null);

// Fetch sort options from API
onMounted(async () => {
  try {
    const res = await fetch('http://localhost:8000/api/products/sorts/get');
    sortOptions.value = await res.json();
    selectedSort.value = sortOptions.value[0]; // Default selection
  } catch (e) {
    console.error("Failed to fetch sort options", e);
  }
});


</script>