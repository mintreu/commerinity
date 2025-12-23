<script setup lang="ts">
/**
 * Categories Page - Demo Version
 * Displays all product categories
 */

definePageMeta({
  layout: 'default'
})

// Demo categories
const categories = ref([
  {
    id: 1,
    name: 'Health & Nutrition',
    slug: 'health-nutrition',
    description: 'Supplements, vitamins, and health essentials',
    image: 'https://placehold.co/400x300/8b5cf6/ffffff?text=Health',
    productCount: 45,
    icon: 'i-lucide-heart-pulse'
  },
  {
    id: 2,
    name: 'Wellness',
    slug: 'wellness',
    description: 'Holistic wellness and lifestyle products',
    image: 'https://placehold.co/400x300/10b981/ffffff?text=Wellness',
    productCount: 32,
    icon: 'i-lucide-leaf'
  },
  {
    id: 3,
    name: 'Beauty & Personal Care',
    slug: 'beauty-care',
    description: 'Skincare, haircare, and beauty essentials',
    image: 'https://placehold.co/400x300/ec4899/ffffff?text=Beauty',
    productCount: 56,
    icon: 'i-lucide-sparkles'
  },
  {
    id: 4,
    name: 'Fitness',
    slug: 'fitness',
    description: 'Sports nutrition and fitness supplements',
    image: 'https://placehold.co/400x300/f59e0b/ffffff?text=Fitness',
    productCount: 28,
    icon: 'i-lucide-dumbbell'
  },
  {
    id: 5,
    name: 'Home & Living',
    slug: 'home-living',
    description: 'Home care and lifestyle products',
    image: 'https://placehold.co/400x300/3b82f6/ffffff?text=Home',
    productCount: 38,
    icon: 'i-lucide-home'
  },
  {
    id: 6,
    name: 'Organic & Natural',
    slug: 'organic-natural',
    description: 'Certified organic and natural products',
    image: 'https://placehold.co/400x300/22c55e/ffffff?text=Organic',
    productCount: 24,
    icon: 'i-lucide-flower-2'
  }
])
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Shop by Category
          </h1>
          <p class="text-purple-100">
            Explore our wide range of product categories
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
            This is a demo page. Categories will be connected to the backend API.
          </p>
        </div>
      </div>

      <!-- Categories Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <NuxtLink
          v-for="category in categories"
          :key="category.id"
          :to="`/shop?category=${category.slug}`"
          class="glass-card overflow-hidden group hover:shadow-xl transition-all"
        >
          <!-- Image -->
          <div class="relative h-48 bg-slate-100 dark:bg-slate-800 overflow-hidden">
            <img
              :src="category.image"
              :alt="category.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
            <div class="absolute bottom-4 left-4 right-4 text-white">
              <h3 class="text-xl font-bold mb-1">
                {{ category.name }}
              </h3>
              <p class="text-sm text-white/80">
                {{ category.productCount }} Products
              </p>
            </div>
            <div class="absolute top-4 right-4 w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
              <UIcon
                :name="category.icon"
                class="w-6 h-6 text-white"
              />
            </div>
          </div>

          <!-- Content -->
          <div class="p-4">
            <p class="text-slate-600 dark:text-slate-400 text-sm line-clamp-2">
              {{ category.description }}
            </p>
            <div class="flex items-center justify-between mt-4">
              <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                Explore Category
              </span>
              <UIcon
                name="i-lucide-arrow-right"
                class="w-4 h-4 text-primary-600 dark:text-primary-400 group-hover:translate-x-1 transition-transform"
              />
            </div>
          </div>
        </NuxtLink>
      </div>

      <!-- All Products CTA -->
      <div class="mt-12 text-center">
        <div class="glass-card p-8 inline-block">
          <UIcon
            name="i-lucide-shopping-bag"
            class="w-12 h-12 mx-auto text-primary-500 mb-4"
          />
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
            View All Products
          </h3>
          <p class="text-slate-500 dark:text-slate-400 mb-4">
            Browse our complete collection
          </p>
          <NuxtLink to="/shop">
            <UButton
              color="primary"
              size="lg"
            >
              <UIcon
                name="i-lucide-grid"
                class="w-4 h-4 mr-2"
              />
              Browse All Products
            </UButton>
          </NuxtLink>
        </div>
      </div>
    </UContainer>
  </div>
</template>
