<script setup lang="ts">
/**
 * Categories Page - Flipkart/Amazon Style Multi-Level Categories
 * Displays hierarchical categories with parent -> children -> grandchildren structure
 * Only shows categories that have products
 */

definePageMeta({
  layout: 'public'
})

useSeoMeta({
  title: 'Shop by Category - Commerinity Pro',
  description: 'Browse all product categories. Find electronics, fashion, home, beauty, health products and more.'
})

interface Category {
  name: string
  slug: string
  product_count: number
  total_products: number
  thumbnail?: string | null
  children?: Category[]
}

const config = useRuntimeConfig()

// Fetch categories from API
const { data: categoriesResponse, status, error } = await useFetch<{
  success: boolean
  data: Category[]
}>(`${config.public.apiBase}/api/catalog/categories`, {
  lazy: true,
  server: false
})

const categories = computed(() => categoriesResponse.value?.data || [])

// Expanded state for mobile accordion
const expandedCategories = ref<Set<string>>(new Set())

const toggleExpanded = (slug: string) => {
  if (expandedCategories.value.has(slug)) {
    expandedCategories.value.delete(slug)
  } else {
    expandedCategories.value.add(slug)
  }
}

const isExpanded = (slug: string) => expandedCategories.value.has(slug)

// Get category gradient colors based on name
const categoryColors: Record<string, string> = {
  electronic: 'from-blue-500 to-cyan-500',
  mobile: 'from-blue-600 to-indigo-500',
  fashion: 'from-pink-500 to-rose-500',
  men: 'from-slate-600 to-slate-700',
  women: 'from-pink-400 to-rose-400',
  home: 'from-amber-500 to-orange-500',
  kitchen: 'from-orange-500 to-red-500',
  beauty: 'from-fuchsia-500 to-purple-500',
  health: 'from-emerald-500 to-green-500',
  grocery: 'from-lime-500 to-green-500',
  spice: 'from-orange-600 to-red-500',
  sports: 'from-red-500 to-orange-500',
  toys: 'from-yellow-500 to-amber-500',
  books: 'from-indigo-500 to-blue-500',
  baby: 'from-pink-400 to-rose-400',
  pet: 'from-teal-500 to-cyan-500',
  automotive: 'from-slate-500 to-gray-600',
  office: 'from-sky-500 to-blue-500',
  music: 'from-violet-500 to-purple-500',
  medicine: 'from-red-500 to-pink-500',
  ayurvedic: 'from-green-600 to-emerald-500'
}

const getCategoryGradient = (name: string): string => {
  const lowerName = name.toLowerCase()
  for (const [key, gradient] of Object.entries(categoryColors)) {
    if (lowerName.includes(key)) return gradient
  }
  return 'from-violet-500 to-fuchsia-500'
}

// Icons mapping
const getCategoryIcon = (name: string): string => {
  const iconMap: Record<string, string> = {
    electronic: 'i-lucide-smartphone',
    mobile: 'i-lucide-smartphone',
    case: 'i-lucide-shield',
    cover: 'i-lucide-shield',
    computer: 'i-lucide-laptop',
    tv: 'i-lucide-tv',
    fashion: 'i-lucide-shirt',
    men: 'i-lucide-user',
    women: 'i-lucide-user',
    kid: 'i-lucide-baby',
    home: 'i-lucide-home',
    kitchen: 'i-lucide-utensils',
    furniture: 'i-lucide-sofa',
    book: 'i-lucide-book-open',
    sport: 'i-lucide-dumbbell',
    fitness: 'i-lucide-activity',
    beauty: 'i-lucide-sparkles',
    skin: 'i-lucide-droplet',
    hair: 'i-lucide-scissors',
    toy: 'i-lucide-gamepad-2',
    game: 'i-lucide-gamepad-2',
    automotive: 'i-lucide-car',
    baby: 'i-lucide-baby',
    grocery: 'i-lucide-shopping-basket',
    food: 'i-lucide-utensils',
    spice: 'i-lucide-flame',
    masala: 'i-lucide-flame',
    pet: 'i-lucide-paw-print',
    office: 'i-lucide-briefcase',
    industrial: 'i-lucide-factory',
    art: 'i-lucide-palette',
    craft: 'i-lucide-scissors',
    software: 'i-lucide-code',
    music: 'i-lucide-music',
    health: 'i-lucide-heart-pulse',
    medicine: 'i-lucide-pill',
    ayurvedic: 'i-lucide-leaf',
    herbal: 'i-lucide-leaf',
    vitamin: 'i-lucide-pill',
    oral: 'i-lucide-smile',
    personal: 'i-lucide-user'
  }

  const lowerName = name.toLowerCase()
  for (const [key, icon] of Object.entries(iconMap)) {
    if (lowerName.includes(key)) return icon
  }
  return 'i-lucide-package'
}

// Check if a category has nested children (grandchildren)
const hasGrandchildren = (category: Category): boolean => {
  return category.children?.some(child => child.children && child.children.length > 0) ?? false
}

// Get all leaf categories for display
const getLeafCategories = (category: Category): Category[] => {
  const leaves: Category[] = []

  const traverse = (cat: Category) => {
    if (!cat.children || cat.children.length === 0) {
      leaves.push(cat)
    } else {
      // If has direct products, include itself
      if (cat.product_count > 0) {
        leaves.push(cat)
      }
      cat.children.forEach(traverse)
    }
  }

  category.children?.forEach(traverse)
  return leaves
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 py-8 md:py-12">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-2xl md:text-4xl font-black mb-2">
            Shop by Category
          </h1>
          <p class="text-purple-100 text-sm md:text-base">
            Browse {{ categories.length }} categories with quality products
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-6 md:py-10">
      <!-- Loading State -->
      <div
        v-if="status === 'pending'"
        class="space-y-4"
      >
        <div
          v-for="i in 4"
          :key="i"
          class="bg-white dark:bg-slate-900 rounded-2xl p-6 animate-pulse"
        >
          <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-xl" />
            <div class="h-6 w-40 bg-slate-200 dark:bg-slate-700 rounded" />
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div
              v-for="j in 6"
              :key="j"
              class="h-20 bg-slate-200 dark:bg-slate-700 rounded-lg"
            />
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="text-center py-12"
      >
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
          <UIcon
            name="i-lucide-alert-circle"
            class="w-10 h-10 text-red-500"
          />
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
          Failed to Load
        </h3>
        <p class="text-slate-500 mb-4">
          {{ error.message }}
        </p>
        <UButton
          color="primary"
          @click="$router.go(0)"
        >
          Try Again
        </UButton>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="categories.length === 0"
        class="text-center py-12"
      >
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
          <UIcon
            name="i-lucide-folder-open"
            class="w-10 h-10 text-slate-400"
          />
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
          No Categories
        </h3>
        <p class="text-slate-500 mb-4">
          No categories with products found
        </p>
        <NuxtLink to="/shop">
          <UButton color="primary">Browse All Products</UButton>
        </NuxtLink>
      </div>

      <!-- Categories - Flipkart/Amazon Style -->
      <div
        v-else
        class="space-y-6"
      >
        <!-- Each Parent Category Section -->
        <div
          v-for="parent in categories"
          :key="parent.slug"
          class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden"
        >
          <!-- Parent Category Header -->
          <div class="flex items-center justify-between p-4 md:p-5 border-b border-slate-100 dark:border-slate-800">
            <NuxtLink
              :to="`/category/${parent.slug}`"
              class="flex items-center gap-4 flex-1 group"
            >
              <!-- Icon with Gradient Background -->
              <div
                class="w-12 h-12 md:w-14 md:h-14 rounded-xl flex items-center justify-center shadow-lg bg-gradient-to-br shrink-0"
                :class="getCategoryGradient(parent.name)"
              >
                <UIcon
                  :name="getCategoryIcon(parent.name)"
                  class="w-6 h-6 md:w-7 md:h-7 text-white"
                />
              </div>

              <div class="min-w-0">
                <h2 class="text-lg md:text-xl font-bold text-slate-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors truncate">
                  {{ parent.name }}
                </h2>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
                  {{ parent.total_products }} products
                </p>
              </div>
            </NuxtLink>

            <!-- Mobile Toggle Button -->
            <button
              v-if="parent.children && parent.children.length > 0"
              class="md:hidden p-2 text-slate-500 hover:text-violet-600 transition-colors"
              @click="toggleExpanded(parent.slug)"
            >
              <UIcon
                :name="isExpanded(parent.slug) ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
                class="w-5 h-5"
              />
            </button>

            <!-- Desktop View All Link -->
            <NuxtLink
              :to="`/category/${parent.slug}`"
              class="hidden md:flex items-center gap-2 text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors"
            >
              <span class="text-sm font-semibold">View All</span>
              <UIcon
                name="i-lucide-chevron-right"
                class="w-5 h-5"
              />
            </NuxtLink>
          </div>

          <!-- Children Categories Grid -->
          <div
            v-if="parent.children && parent.children.length > 0"
            class="p-4 md:p-5 bg-slate-50/50 dark:bg-slate-800/30"
            :class="{ 'hidden md:block': !isExpanded(parent.slug) }"
          >
            <!-- If category has grandchildren, show grouped -->
            <div
              v-if="hasGrandchildren(parent)"
              class="space-y-6"
            >
              <div
                v-for="child in parent.children"
                :key="child.slug"
              >
                <!-- Child Category Header -->
                <NuxtLink
                  :to="`/category/${child.slug}`"
                  class="flex items-center gap-2 mb-3 group"
                >
                  <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                    {{ child.name }}
                  </h3>
                  <span class="text-xs text-slate-400">({{ child.total_products }})</span>
                  <UIcon
                    name="i-lucide-chevron-right"
                    class="w-4 h-4 text-slate-400 group-hover:text-violet-600 transition-colors"
                  />
                </NuxtLink>

                <!-- Grandchildren Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                  <NuxtLink
                    v-for="grandchild in child.children"
                    :key="grandchild.slug"
                    :to="`/category/${grandchild.slug}`"
                    class="group/item flex flex-col items-center p-3 md:p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-md transition-all duration-300"
                  >
                    <!-- Grandchild Icon -->
                    <div
                      class="w-10 h-10 md:w-12 md:h-12 rounded-lg flex items-center justify-center mb-2 bg-gradient-to-br opacity-80 group-hover/item:opacity-100 group-hover/item:scale-110 transition-all"
                      :class="getCategoryGradient(grandchild.name)"
                    >
                      <UIcon
                        :name="getCategoryIcon(grandchild.name)"
                        class="w-5 h-5 md:w-6 md:h-6 text-white"
                      />
                    </div>

                    <!-- Grandchild Name -->
                    <span class="text-xs md:text-sm font-medium text-slate-700 dark:text-slate-300 text-center line-clamp-2 group-hover/item:text-violet-600 dark:group-hover/item:text-violet-400 transition-colors">
                      {{ grandchild.name }}
                    </span>

                    <!-- Product Count -->
                    <span class="text-[10px] md:text-xs text-slate-400 dark:text-slate-500 mt-1">
                      {{ grandchild.product_count }} items
                    </span>
                  </NuxtLink>
                </div>
              </div>
            </div>

            <!-- Simple children grid (no grandchildren) -->
            <div
              v-else
              class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3"
            >
              <NuxtLink
                v-for="child in parent.children"
                :key="child.slug"
                :to="`/category/${child.slug}`"
                class="group flex flex-col items-center p-3 md:p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-md transition-all duration-300"
              >
                <!-- Child Icon -->
                <div
                  class="w-10 h-10 md:w-12 md:h-12 rounded-lg flex items-center justify-center mb-2 bg-gradient-to-br opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all"
                  :class="getCategoryGradient(child.name)"
                >
                  <UIcon
                    :name="getCategoryIcon(child.name)"
                    class="w-5 h-5 md:w-6 md:h-6 text-white"
                  />
                </div>

                <!-- Child Name -->
                <span class="text-xs md:text-sm font-medium text-slate-700 dark:text-slate-300 text-center line-clamp-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                  {{ child.name }}
                </span>

                <!-- Product Count -->
                <span class="text-[10px] md:text-xs text-slate-400 dark:text-slate-500 mt-1">
                  {{ child.total_products }} items
                </span>
              </NuxtLink>
            </div>
          </div>

          <!-- No Children - Direct link to category -->
          <div
            v-else
            class="p-4 bg-slate-50/50 dark:bg-slate-800/30"
          >
            <NuxtLink
              :to="`/category/${parent.slug}`"
              class="inline-flex items-center gap-2 text-sm text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors"
            >
              Browse all {{ parent.name }} products
              <UIcon
                name="i-lucide-arrow-right"
                class="w-4 h-4"
              />
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Quick Navigation CTA -->
      <div class="mt-10 bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-2xl p-6 md:p-8 text-white text-center">
        <h3 class="text-xl md:text-2xl font-bold mb-2">
          Can't find what you're looking for?
        </h3>
        <p class="text-purple-100 mb-4">
          Browse all products or use search to find specific items
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <NuxtLink to="/shop">
            <UButton
              color="white"
              size="lg"
              class="w-full sm:w-auto"
            >
              <UIcon
                name="i-lucide-grid-3x3"
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
