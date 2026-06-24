<script setup lang="ts">
/**
 * Categories Page - Premium Storefront Category Browser
 * lg: Big card horizontal slider per parent category
 * sm/md: Grid layout with accordion
 */

import { getContextualApiError, getEmptyStateMessage } from '~/utils/api-error'

definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const companyName = config.public.companyName;
useSeoMeta({
  title: 'Shop by Category -'. companyName,
  description: 'Browse all product categories. Find electronics, fashion, home, beauty, health products and more.'
})

interface Category {
  name: string
  slug: string
  product_count: number
  total_products?: number
  thumbnail?: string | null
  children?: Category[]
}


const categoriesResponse = ref<{ success: boolean, data: Category[] } | null>(null)
const status = ref<'pending' | 'success' | 'error'>('pending')
const errorMessage = ref<string | null>(null)

const categories = computed(() => categoriesResponse.value?.data || [])

const totalProducts = computed(() => {
  return categories.value.reduce((sum, cat) => sum + (cat.product_count || 0), 0)
})

const loadCategories = async () => {
  status.value = 'pending'
  errorMessage.value = null
  try {
    categoriesResponse.value = await useSanctumFetch(`${config.public.apiBase}/api/catalog/categories`)
    status.value = 'success'
  } catch (err) {
    errorMessage.value = getContextualApiError(err, 'categories').message
    status.value = 'error'
  }
}

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

onMounted(() => {
  loadCategories()
})

// Slider scroll state per parent
const sliderRefs = ref<Record<string, HTMLElement | null>>({})

const scrollSlider = (slug: string, direction: 'left' | 'right') => {
  const el = sliderRefs.value[slug]
  if (!el) return
  const scrollAmount = el.clientWidth * 0.7
  el.scrollBy({ left: direction === 'right' ? scrollAmount : -scrollAmount, behavior: 'smooth' })
}

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

// Flatten all browsable children (children + grandchildren) for the slider
const getSliderItems = (parent: Category): Category[] => {
  if (!parent.children?.length) return []
  const items: Category[] = []
  for (const child of parent.children) {
    if (child.children?.length) {
      items.push(...child.children)
    } else {
      items.push(child)
    }
  }
  return items
}

const hasGrandchildren = (category: Category): boolean => {
  return category.children?.some(child => child.children && child.children.length > 0) ?? false
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Premium Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-violet-950/80 to-slate-950">
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_-10%,rgba(139,92,246,0.3),transparent)]" />
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_80%,rgba(217,70,239,0.1),transparent_50%)]" />
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;" />
      </div>

      <UContainer class="relative z-10 py-10 md:py-16 lg:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
          <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
              <NuxtLink to="/" class="hover:text-white transition-colors">Home</NuxtLink>
              <UIcon name="i-lucide-chevron-right" class="w-3 h-3" />
              <span class="text-violet-300">Categories</span>
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight text-white">
              Shop by
              <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent">Category</span>
            </h1>
            <p class="mt-3 text-sm md:text-base text-slate-400 max-w-lg">
              Explore our complete collection organized by category. Find exactly what you need.
            </p>
          </div>
          <div v-if="categories.length" class="flex items-center gap-3">
            <div class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 backdrop-blur-sm px-4 py-2.5">
              <UIcon name="i-lucide-layers" class="w-4 h-4 text-violet-400" />
              <span class="text-sm font-medium text-slate-300">{{ categories.length }} Categories</span>
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 backdrop-blur-sm px-4 py-2.5">
              <UIcon name="i-lucide-package" class="w-4 h-4 text-fuchsia-400" />
              <span class="text-sm font-medium text-slate-300">{{ totalProducts }} Products</span>
            </div>
          </div>
        </div>

        <!-- Quick Jump Pills -->
        <div v-if="categories.length" class="mt-8 flex flex-wrap gap-2">
          <a
            v-for="parent in categories"
            :key="parent.slug"
            :href="`#cat-${parent.slug}`"
            class="inline-flex items-center gap-2 rounded-xl border border-white/[0.08] bg-white/[0.04] backdrop-blur-sm px-3.5 py-2 text-xs font-medium text-slate-300 hover:bg-white/[0.1] hover:text-white hover:border-violet-400/40 transition-all duration-300"
          >
            <UIcon :name="getCategoryIcon(parent.name)" class="w-3.5 h-3.5 text-violet-400" />
            {{ parent.name }}
          </a>
        </div>
      </UContainer>
      <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent" />
    </section>

    <!-- Main Content -->
    <UContainer class="py-8 md:py-12">
      <!-- Loading -->
      <div v-if="status === 'pending'" class="space-y-10">
        <div v-for="i in 3" :key="i">
          <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 bg-slate-200 dark:bg-slate-700 rounded-2xl animate-pulse" />
            <div>
              <div class="h-6 w-40 bg-slate-200 dark:bg-slate-700 rounded mb-2 animate-pulse" />
              <div class="h-3 w-24 bg-slate-200 dark:bg-slate-700 rounded animate-pulse" />
            </div>
          </div>
          <div class="flex gap-5 overflow-hidden">
            <div v-for="j in 5" :key="j" class="flex-shrink-0 w-52 animate-pulse">
              <div class="aspect-[3/4] bg-slate-200 dark:bg-slate-700 rounded-2xl" />
            </div>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="errorMessage" class="text-center py-16">
        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
          <UIcon name="i-lucide-alert-circle" class="w-10 h-10 text-red-500" />
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Failed to Load Categories</h3>
        <p class="text-slate-500 mb-6">{{ errorMessage }}</p>
        <UButton color="primary" @click="loadCategories">
          <UIcon name="i-lucide-refresh-cw" class="w-4 h-4 mr-2" />
          Try Again
        </UButton>
      </div>

      <!-- Empty -->
      <div v-else-if="categories.length === 0" class="text-center py-16">
        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
          <UIcon name="i-lucide-folder-open" class="w-10 h-10 text-slate-400" />
        </div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No Categories Yet</h3>
        <p class="text-slate-500 mb-6">{{ getEmptyStateMessage('categories') }}</p>
        <NuxtLink to="/shop/products">
          <UButton color="primary">Browse All Products</UButton>
        </NuxtLink>
      </div>

      <!-- Category Sections -->
      <div v-else class="space-y-12 lg:space-y-16">
        <section
          v-for="parent in categories"
          :key="parent.slug"
          :id="`cat-${parent.slug}`"
          class="scroll-mt-24"
        >
          <!-- Section Header -->
          <div class="flex items-center justify-between mb-5 lg:mb-6">
            <NuxtLink
              :to="`/category/${parent.slug}`"
              class="flex items-center gap-4 group min-w-0"
            >
              <div
                class="w-12 h-12 lg:w-14 lg:h-14 rounded-2xl flex items-center justify-center shadow-lg bg-gradient-to-br shrink-0 group-hover:scale-110 transition-transform duration-300"
                :class="getCategoryGradient(parent.name)"
              >
                <UIcon :name="getCategoryIcon(parent.name)" class="w-6 h-6 lg:w-7 lg:h-7 text-white" />
              </div>
              <div class="min-w-0">
                <h2 class="text-xl lg:text-2xl font-bold text-slate-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors truncate">
                  {{ parent.name }}
                </h2>
                <div class="flex items-center gap-3 mt-0.5">
                  <span class="text-sm text-slate-500 dark:text-slate-400">{{ parent.product_count }} products</span>
                  <span v-if="parent.children?.length" class="text-xs text-slate-400 dark:text-slate-500">{{ parent.children.length }} subcategories</span>
                </div>
              </div>
            </NuxtLink>

            <div class="flex items-center gap-2">
              <!-- Slider Arrows (lg only) -->
              <button
                v-if="parent.children?.length"
                class="hidden lg:flex w-10 h-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:text-violet-600 hover:border-violet-300 dark:hover:border-violet-600 transition-all"
                @click="scrollSlider(parent.slug, 'left')"
              >
                <UIcon name="i-lucide-chevron-left" class="w-5 h-5" />
              </button>
              <button
                v-if="parent.children?.length"
                class="hidden lg:flex w-10 h-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:text-violet-600 hover:border-violet-300 dark:hover:border-violet-600 transition-all"
                @click="scrollSlider(parent.slug, 'right')"
              >
                <UIcon name="i-lucide-chevron-right" class="w-5 h-5" />
              </button>

              <!-- Mobile Toggle -->
              <button
                v-if="parent.children && parent.children.length > 0"
                class="lg:hidden p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-all"
                @click="toggleExpanded(parent.slug)"
              >
                <UIcon
                  :name="isExpanded(parent.slug) ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
                  class="w-5 h-5"
                />
              </button>

              <!-- View All -->
              <NuxtLink
                :to="`/category/${parent.slug}`"
                class="hidden md:flex items-center gap-2 rounded-xl bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400 hover:bg-violet-100 dark:hover:bg-violet-900/30 px-4 py-2.5 transition-colors"
              >
                <span class="text-sm font-semibold">View All</span>
                <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
              </NuxtLink>
            </div>
          </div>

          <!-- ========================================
               LG: Big Card Horizontal Slider
               ======================================== -->
          <div
            v-if="parent.children && parent.children.length > 0"
            class="hidden lg:block"
          >
            <div
              :ref="(el: any) => { if (el) sliderRefs[parent.slug] = el as HTMLElement }"
              class="flex gap-5 overflow-x-auto pb-4 scrollbar-hide snap-x snap-mandatory"
            >
              <NuxtLink
                v-for="item in getSliderItems(parent)"
                :key="item.slug"
                :to="`/category/${item.slug}`"
                class="group flex-shrink-0 w-52 snap-start"
              >
                <div class="relative rounded-2xl overflow-hidden border border-slate-200/80 dark:border-slate-700/50 bg-white dark:bg-slate-900 hover:shadow-xl hover:border-violet-300 dark:hover:border-violet-600 hover:-translate-y-1 transition-all duration-300">
                  <!-- Image / Gradient Background -->
                  <div class="aspect-[3/4] relative overflow-hidden">
                    <img
                      v-if="item.thumbnail"
                      :src="item.thumbnail"
                      :alt="item.name"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                      loading="lazy"
                    >
                    <div
                      v-else
                      class="w-full h-full bg-gradient-to-br flex items-center justify-center"
                      :class="getCategoryGradient(item.name)"
                    >
                      <UIcon :name="getCategoryIcon(item.name)" class="w-16 h-16 text-white/30" />
                    </div>

                    <!-- Overlay gradient -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

                    <!-- Product count badge -->
                    <div class="absolute top-3 right-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm rounded-full px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                      {{ item.product_count }} items
                    </div>

                    <!-- Bottom info -->
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                      <h3 class="text-base font-bold text-white leading-snug line-clamp-2 group-hover:text-violet-200 transition-colors">
                        {{ item.name }}
                      </h3>
                      <div class="mt-2 flex items-center gap-1.5 text-xs font-medium text-white/70 group-hover:text-white transition-colors">
                        <span>Shop now</span>
                        <UIcon name="i-lucide-arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                      </div>
                    </div>
                  </div>
                </div>
              </NuxtLink>

              <!-- View All Card -->
              <NuxtLink
                :to="`/category/${parent.slug}`"
                class="group flex-shrink-0 w-52 snap-start"
              >
                <div class="aspect-[3/4] rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col items-center justify-center gap-3 hover:border-violet-400 dark:hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-all duration-300">
                  <div class="w-14 h-14 rounded-2xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <UIcon name="i-lucide-arrow-right" class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                  </div>
                  <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">View All</span>
                  <span class="text-xs text-slate-400">{{ parent.product_count }} products</span>
                </div>
              </NuxtLink>
            </div>
          </div>

          <!-- No children on lg -->
          <div
            v-else
            class="hidden lg:block"
          >
            <NuxtLink
              :to="`/category/${parent.slug}`"
              class="group inline-flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-4 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow-lg transition-all duration-300"
            >
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br group-hover:scale-110 transition-transform"
                :class="getCategoryGradient(parent.name)"
              >
                <UIcon :name="getCategoryIcon(parent.name)" class="w-5 h-5 text-white" />
              </div>
              <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                Browse all {{ parent.name }} products
              </span>
              <UIcon name="i-lucide-arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-violet-500 group-hover:translate-x-0.5 transition-all" />
            </NuxtLink>
          </div>

          <!-- ========================================
               SM/MD: Grid with Accordion (below lg)
               ======================================== -->
          <div
            v-if="parent.children && parent.children.length > 0"
            class="lg:hidden"
            :class="{ 'hidden': !isExpanded(parent.slug) }"
          >
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 md:p-5">
              <!-- Grouped grandchildren -->
              <div v-if="hasGrandchildren(parent)" class="space-y-6">
                <div v-for="child in parent.children" :key="child.slug">
                  <NuxtLink :to="`/category/${child.slug}`" class="flex items-center gap-2 mb-3 group">
                    <div
                      class="w-6 h-6 rounded-md flex items-center justify-center bg-gradient-to-br"
                      :class="getCategoryGradient(child.name)"
                    >
                      <UIcon :name="getCategoryIcon(child.name)" class="w-3 h-3 text-white" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                      {{ child.name }}
                    </h3>
                    <span class="text-xs text-slate-400">({{ child.product_count }})</span>
                  </NuxtLink>
                  <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5">
                    <NuxtLink
                      v-for="gc in child.children"
                      :key="gc.slug"
                      :to="`/category/${gc.slug}`"
                      class="group/gc flex flex-col items-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700/50 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow transition-all"
                    >
                      <div
                        v-if="gc.thumbnail"
                        class="w-10 h-10 rounded-lg overflow-hidden mb-2"
                      >
                        <img :src="gc.thumbnail" :alt="gc.name" class="w-full h-full object-cover" loading="lazy">
                      </div>
                      <div
                        v-else
                        class="w-10 h-10 rounded-lg flex items-center justify-center mb-2 bg-gradient-to-br group-hover/gc:scale-110 transition-transform"
                        :class="getCategoryGradient(gc.name)"
                      >
                        <UIcon :name="getCategoryIcon(gc.name)" class="w-4 h-4 text-white" />
                      </div>
                      <span class="text-[11px] font-medium text-slate-600 dark:text-slate-400 text-center line-clamp-2 leading-tight">{{ gc.name }}</span>
                      <span class="text-[10px] text-slate-400 mt-0.5">{{ gc.product_count }}</span>
                    </NuxtLink>
                  </div>
                </div>
              </div>

              <!-- Simple children -->
              <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5">
                <NuxtLink
                  v-for="child in parent.children"
                  :key="child.slug"
                  :to="`/category/${child.slug}`"
                  class="group flex flex-col items-center p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700/50 hover:border-violet-300 dark:hover:border-violet-600 hover:shadow transition-all"
                >
                  <div
                    v-if="child.thumbnail"
                    class="w-10 h-10 rounded-lg overflow-hidden mb-2"
                  >
                    <img :src="child.thumbnail" :alt="child.name" class="w-full h-full object-cover" loading="lazy">
                  </div>
                  <div
                    v-else
                    class="w-10 h-10 rounded-lg flex items-center justify-center mb-2 bg-gradient-to-br group-hover:scale-110 transition-transform"
                    :class="getCategoryGradient(child.name)"
                  >
                    <UIcon :name="getCategoryIcon(child.name)" class="w-4 h-4 text-white" />
                  </div>
                  <span class="text-[11px] font-medium text-slate-600 dark:text-slate-400 text-center line-clamp-2 leading-tight">{{ child.name }}</span>
                  <span class="text-[10px] text-slate-400 mt-0.5">{{ child.product_count }}</span>
                </NuxtLink>
              </div>
            </div>
          </div>

          <!-- No children (mobile) -->
          <div
            v-if="!parent.children?.length"
            class="lg:hidden"
          >
            <NuxtLink
              :to="`/category/${parent.slug}`"
              class="inline-flex items-center gap-2 text-sm font-medium text-violet-600 dark:text-violet-400 hover:text-violet-700 transition-colors"
            >
              Browse all {{ parent.name }} products
              <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
            </NuxtLink>
          </div>
        </section>
      </div>

      <!-- Bottom CTA -->
      <div class="mt-12 lg:mt-16 relative overflow-hidden rounded-2xl">
        <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600" />
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent_60%)]" />
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;" />

        <div class="relative z-10 p-8 md:p-12 text-center">
          <div class="w-14 h-14 mx-auto mb-5 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center">
            <UIcon name="i-lucide-search" class="w-7 h-7 text-white" />
          </div>
          <h3 class="text-xl md:text-2xl font-bold text-white mb-2">
            Can't find what you're looking for?
          </h3>
          <p class="text-purple-100 text-sm md:text-base mb-6 max-w-md mx-auto">
            Browse our complete product catalog or search for specific items
          </p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <NuxtLink to="/shop/products">
              <UButton size="lg" color="white" class="w-full sm:w-auto font-semibold px-6">
                <UIcon name="i-lucide-shopping-bag" class="w-4 h-4 mr-2" />
                Browse All Products
              </UButton>
            </NuxtLink>
            <NuxtLink to="/shop/deals">
              <UButton size="lg" color="neutral" variant="outline" class="w-full sm:w-auto font-semibold px-6 text-white border-white/30 hover:bg-white/10">
                <UIcon name="i-lucide-percent" class="w-4 h-4 mr-2" />
                View Deals
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
</style>
