<script setup lang="ts">
/**
 * Category Showcase Section for Homepage
 * Fetches top-level categories from /api/catalog/categories
 * Shows beautiful category cards with thumbnails
 */

interface CategoryImage {
  url: string
  thumbnail?: string
  alt?: string
}

interface HomepageCategory {
  id: number
  name: string
  slug: string
  description?: string | null
  thumbnail: string | null
  product_count: number
  children_count?: number
  image?: CategoryImage | null
}

interface CategoriesResponse {
  success: boolean
  data: HomepageCategory[]
}

const config = useRuntimeConfig()
const sanctumFetch = useSanctumFetch()

const { data: categoriesResponse, status } = await useFetch<CategoriesResponse>(
  `${config.public.apiBase}/api/catalog/categories`,
  {
    $fetch: sanctumFetch,
    lazy: true,
    server: false
  }
)

// Show top-level categories limited to 6
const topCategories = computed(() => {
  const items = categoriesResponse.value?.data || []
  return items.slice(0, 6)
})
</script>

<template>
  <section class="py-16 md:py-24 section-gradient-primary relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-40">
      <div class="floating-orb floating-orb-accent orb-xl top-10 left-1/4" />
      <div class="floating-orb floating-orb-primary orb-md bottom-10 right-1/3" />
    </div>

    <UContainer class="relative z-10">
      <!-- Section Header -->
      <div class="text-center mb-12 md:mb-16">
        <div class="premium-badge mx-auto mb-4">
          <UIcon
            name="i-lucide-layers"
            class="w-4 h-4 mr-2"
          />
          <span>Shop by Category</span>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-4">
          <span class="text-slate-900 dark:text-white">Browse</span>
          <span class="block gradient-text-secondary">Categories</span>
        </h2>

        <p class="text-slate-600 dark:text-slate-300 text-base md:text-lg max-w-3xl mx-auto">
          Find exactly what you're looking for in our organized product categories
        </p>
      </div>

      <!-- Loading State -->
      <div
        v-if="status === 'pending'"
        class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6"
      >
        <div
          v-for="i in 6"
          :key="i"
          class="aspect-[4/3] bg-white/80 dark:bg-slate-900/80 rounded-2xl overflow-hidden animate-pulse"
        >
          <div class="h-full bg-slate-200 dark:bg-slate-700" />
        </div>
      </div>

      <!-- Categories Grid -->
      <div
        v-else-if="topCategories.length"
        class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6"
      >
        <NuxtLink
          v-for="category in topCategories"
          :key="category.id"
          :to="`/category/${category.slug}`"
          class="group relative aspect-[4/3] bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-[1.02]"
        >
          <!-- Background Image -->
          <img
            v-if="category.thumbnail || category.image?.url"
            :src="category.image?.url || category.thumbnail || ''"
            :alt="category.name"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            loading="lazy"
          >
          <div
            v-else
            class="absolute inset-0 bg-gradient-to-br from-violet-500 to-pink-500"
          />

          <!-- Overlay Gradient -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent" />

          <!-- Content -->
          <div class="absolute inset-0 p-4 md:p-6 flex flex-col justify-end">
            <!-- Category Name -->
            <h3 class="text-lg md:text-xl font-bold text-white mb-1 group-hover:translate-x-1 transition-transform">
              {{ category.name }}
            </h3>

            <!-- Product Count -->
            <p class="text-sm text-white/80 flex items-center gap-1">
              <UIcon
                name="i-lucide-package"
                class="w-4 h-4"
              />
              {{ category.product_count || 0 }} products
            </p>

            <!-- Children Count -->
            <p
              v-if="category.children_count && category.children_count > 0"
              class="text-xs text-white/60 mt-1 flex items-center gap-1"
            >
              <UIcon
                name="i-lucide-folder"
                class="w-3 h-3"
              />
              {{ category.children_count }} subcategories
            </p>

            <!-- Hover Arrow -->
            <div class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
              <UIcon
                name="i-lucide-arrow-right"
                class="w-5 h-5 text-white group-hover:translate-x-0.5 transition-transform"
              />
            </div>
          </div>

          <!-- Premium Corner Accent -->
          <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-violet-500/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
        </NuxtLink>
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="text-center py-12"
      >
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
          <UIcon
            name="i-lucide-layers"
            class="w-10 h-10 text-slate-400"
          />
        </div>
        <p class="text-slate-500 dark:text-slate-400 mb-4">
          Categories coming soon!
        </p>
        <NuxtLink to="/shop">
          <UButton color="primary">
            Browse All Products
          </UButton>
        </NuxtLink>
      </div>

      <!-- View All Categories Link -->
      <div
        v-if="topCategories.length"
        class="text-center mt-10"
      >
        <NuxtLink
          to="/shop"
          class="inline-flex items-center gap-2 text-violet-600 dark:text-violet-400 hover:text-pink-600 dark:hover:text-pink-400 font-semibold transition-colors"
        >
          View All Categories
          <UIcon
            name="i-lucide-arrow-right"
            class="w-5 h-5"
          />
        </NuxtLink>
      </div>
    </UContainer>
  </section>
</template>
