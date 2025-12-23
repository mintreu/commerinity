<script setup lang="ts">
/**
 * Shop Page - Demo Version
 * Displays product catalog with filters and categories
 */

definePageMeta({
  layout: 'default'
})

const { formatCurrency } = useBranding()

// Demo products
const products = ref([
  {
    id: 1,
    name: 'Premium Health Supplement',
    slug: 'premium-health-supplement',
    price: 149900,
    originalPrice: 199900,
    image: 'https://placehold.co/300x300/8b5cf6/ffffff?text=Product+1',
    category: 'Health',
    rating: 4.5,
    reviews: 128,
    inStock: true
  },
  {
    id: 2,
    name: 'Organic Wellness Kit',
    slug: 'organic-wellness-kit',
    price: 249900,
    originalPrice: 299900,
    image: 'https://placehold.co/300x300/d946ef/ffffff?text=Product+2',
    category: 'Wellness',
    rating: 4.8,
    reviews: 256,
    inStock: true
  },
  {
    id: 3,
    name: 'Beauty Care Bundle',
    slug: 'beauty-care-bundle',
    price: 99900,
    originalPrice: 149900,
    image: 'https://placehold.co/300x300/10b981/ffffff?text=Product+3',
    category: 'Beauty',
    rating: 4.3,
    reviews: 89,
    inStock: true
  },
  {
    id: 4,
    name: 'Fitness Pro Pack',
    slug: 'fitness-pro-pack',
    price: 349900,
    originalPrice: 449900,
    image: 'https://placehold.co/300x300/f59e0b/ffffff?text=Product+4',
    category: 'Fitness',
    rating: 4.7,
    reviews: 192,
    inStock: false
  },
  {
    id: 5,
    name: 'Daily Nutrition Combo',
    slug: 'daily-nutrition-combo',
    price: 179900,
    originalPrice: 219900,
    image: 'https://placehold.co/300x300/3b82f6/ffffff?text=Product+5',
    category: 'Health',
    rating: 4.6,
    reviews: 156,
    inStock: true
  },
  {
    id: 6,
    name: 'Herbal Care Set',
    slug: 'herbal-care-set',
    price: 129900,
    originalPrice: 169900,
    image: 'https://placehold.co/300x300/ec4899/ffffff?text=Product+6',
    category: 'Wellness',
    rating: 4.4,
    reviews: 112,
    inStock: true
  }
])

const categories = ['All', 'Health', 'Wellness', 'Beauty', 'Fitness']
const selectedCategory = ref('All')

const sortOptions = [
  { label: 'Popularity', value: 'popularity' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Newest First', value: 'newest' }
]
const selectedSort = ref('popularity')

const filteredProducts = computed(() => {
  if (selectedCategory.value === 'All') {
    return products.value
  }
  return products.value.filter(p => p.category === selectedCategory.value)
})

const formatPrice = (paisa: number) => {
  return formatCurrency(paisa / 100)
}

const getDiscount = (price: number, originalPrice: number) => {
  return Math.round(((originalPrice - price) / originalPrice) * 100)
}
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Shop Our Products
          </h1>
          <p class="text-purple-100">
            Premium quality products at unbeatable prices
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-8">
      <!-- Demo Notice -->
      <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
        <div class="flex items-center gap-3">
          <UIcon
            name="i-lucide-info"
            class="w-5 h-5 text-amber-600 dark:text-amber-400"
          />
          <p class="text-sm text-amber-700 dark:text-amber-300">
            This is a demo page. Products and functionality will be connected to the backend API.
          </p>
        </div>
      </div>

      <!-- Filters Row -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <!-- Categories -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="category in categories"
            :key="category"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-all',
              selectedCategory === category
                ? 'bg-primary-500 text-white'
                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
            ]"
            @click="selectedCategory = category"
          >
            {{ category }}
          </button>
        </div>

        <!-- Sort -->
        <USelect
          v-model="selectedSort"
          :items="sortOptions"
          class="w-48"
        />
      </div>

      <!-- Products Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="glass-card overflow-hidden group"
        >
          <!-- Image -->
          <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
            <div
              v-if="getDiscount(product.price, product.originalPrice) > 0"
              class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
            >
              {{ getDiscount(product.price, product.originalPrice) }}% OFF
            </div>
            <div
              v-if="!product.inStock"
              class="absolute inset-0 bg-black/50 flex items-center justify-center"
            >
              <span class="bg-slate-900 text-white px-4 py-2 rounded-lg font-medium">
                Out of Stock
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-4">
            <p class="text-xs text-primary-600 dark:text-primary-400 font-medium mb-1">
              {{ product.category }}
            </p>
            <h3 class="font-semibold text-slate-900 dark:text-white mb-2 line-clamp-2">
              {{ product.name }}
            </h3>

            <!-- Rating -->
            <div class="flex items-center gap-1 mb-2">
              <UIcon
                name="i-lucide-star"
                class="w-4 h-4 text-amber-500 fill-current"
              />
              <span class="text-sm text-slate-600 dark:text-slate-400">
                {{ product.rating }} ({{ product.reviews }})
              </span>
            </div>

            <!-- Price -->
            <div class="flex items-center gap-2 mb-3">
              <span class="text-lg font-bold text-slate-900 dark:text-white">
                {{ formatPrice(product.price) }}
              </span>
              <span class="text-sm text-slate-400 line-through">
                {{ formatPrice(product.originalPrice) }}
              </span>
            </div>

            <!-- Add to Cart -->
            <UButton
              block
              :disabled="!product.inStock"
              :color="product.inStock ? 'primary' : 'neutral'"
            >
              <UIcon
                name="i-lucide-shopping-cart"
                class="w-4 h-4 mr-2"
              />
              {{ product.inStock ? 'Add to Cart' : 'Notify Me' }}
            </UButton>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="filteredProducts.length === 0"
        class="text-center py-12"
      >
        <UIcon
          name="i-lucide-package-x"
          class="w-16 h-16 mx-auto text-slate-400 mb-4"
        />
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
          No products found
        </h3>
        <p class="text-slate-500 dark:text-slate-400">
          Try selecting a different category
        </p>
      </div>
    </UContainer>
  </div>
</template>
