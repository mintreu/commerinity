<script setup lang="ts">
/**
 * Simple Product Carousel Component
 * Clean horizontal scroll carousel for mobile & category displays
 * No autoplay, no slides - just smooth horizontal scroll with snap
 */
import type { Product } from '~/types/catalog'

interface Props {
  products: Product[]
  title?: string
  subtitle?: string
  viewAllLink?: string
  loading?: boolean
  compact?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  subtitle: '',
  viewAllLink: '',
  loading: false,
  compact: false
})

// Carousel ref for scroll control
const carouselRef = ref<HTMLElement | null>(null)
const canScrollLeft = ref(false)
const canScrollRight = ref(true)

// Check scroll position
const checkScroll = () => {
  if (!carouselRef.value) return
  const { scrollLeft, scrollWidth, clientWidth } = carouselRef.value
  canScrollLeft.value = scrollLeft > 10
  canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 10
}

// Scroll controls
const scrollLeft = () => {
  if (!carouselRef.value) return
  const cardWidth = props.compact ? 160 : 200
  carouselRef.value.scrollBy({ left: -cardWidth * 2, behavior: 'smooth' })
}

const scrollRight = () => {
  if (!carouselRef.value) return
  const cardWidth = props.compact ? 160 : 200
  carouselRef.value.scrollBy({ left: cardWidth * 2, behavior: 'smooth' })
}

onMounted(() => {
  checkScroll()
  carouselRef.value?.addEventListener('scroll', checkScroll, { passive: true })
})

onUnmounted(() => {
  carouselRef.value?.removeEventListener('scroll', checkScroll)
})
</script>

<template>
  <div class="relative">
    <!-- Header -->
    <div v-if="title" class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-lg md:text-xl font-bold text-slate-900 dark:text-white">
          {{ title }}
        </h3>
        <p v-if="subtitle" class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
          {{ subtitle }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <!-- View All Link -->
        <NuxtLink
          v-if="viewAllLink"
          :to="viewAllLink"
          class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1"
        >
          View All
          <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
        </NuxtLink>

        <!-- Navigation Arrows (Desktop) -->
        <div class="hidden sm:flex items-center gap-1">
          <button
            :class="[
              'w-8 h-8 rounded-full flex items-center justify-center border transition-all',
              canScrollLeft
                ? 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'
                : 'bg-slate-100 dark:bg-slate-800/50 border-slate-100 dark:border-slate-800 text-slate-300 dark:text-slate-600 cursor-not-allowed'
            ]"
            :disabled="!canScrollLeft"
            @click="scrollLeft"
          >
            <UIcon name="i-lucide-chevron-left" class="w-4 h-4" />
          </button>
          <button
            :class="[
              'w-8 h-8 rounded-full flex items-center justify-center border transition-all',
              canScrollRight
                ? 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200'
                : 'bg-slate-100 dark:bg-slate-800/50 border-slate-100 dark:border-slate-800 text-slate-300 dark:text-slate-600 cursor-not-allowed'
            ]"
            :disabled="!canScrollRight"
            @click="scrollRight"
          >
            <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Carousel Container -->
    <div class="relative">
      <!-- Scroll Container -->
      <div
        ref="carouselRef"
        :class="[
          'flex gap-3 md:gap-4 overflow-x-auto scrollbar-hide pb-2 -mx-4 px-4 snap-x snap-mandatory scroll-smooth',
          compact ? 'gap-2 md:gap-3' : ''
        ]"
      >
        <!-- Loading Skeletons -->
        <template v-if="loading">
          <div
            v-for="i in 6"
            :key="i"
            :class="[
              'shrink-0 snap-start',
              compact ? 'w-[140px] md:w-[160px]' : 'w-[160px] md:w-[200px]'
            ]"
          >
            <div class="bg-white/80 dark:bg-slate-900/80 rounded-xl overflow-hidden animate-pulse">
              <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
              <div class="p-3 space-y-2">
                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
              </div>
            </div>
          </div>
        </template>

        <!-- Products -->
        <template v-else>
          <div
            v-for="product in products"
            :key="product.slug"
            :class="[
              'shrink-0 snap-start',
              compact ? 'w-[140px] md:w-[160px]' : 'w-[160px] md:w-[200px]'
            ]"
          >
            <StoreProductCard :product="product" :compact="compact" />
          </div>

          <!-- View All Card -->
          <NuxtLink
            v-if="viewAllLink && products.length >= 4"
            :to="viewAllLink"
            :class="[
              'shrink-0 snap-start',
              compact ? 'w-[140px] md:w-[160px]' : 'w-[160px] md:w-[200px]'
            ]"
          >
            <div class="h-full min-h-[220px] flex items-center justify-center bg-gradient-to-br from-primary-500 to-violet-600 rounded-xl p-4 text-center text-white relative overflow-hidden group">
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700" />
              <div class="relative z-10 flex flex-col items-center space-y-2">
                <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                  <UIcon name="i-lucide-arrow-right" class="w-5 h-5" />
                </div>
                <span class="font-bold text-sm">View All</span>
                <span class="text-xs opacity-80">{{ products.length }}+ items</span>
              </div>
            </div>
          </NuxtLink>
        </template>
      </div>

      <!-- Edge Fade Indicators -->
      <div
        v-if="canScrollLeft"
        class="absolute left-0 top-0 bottom-2 w-8 bg-gradient-to-r from-white dark:from-slate-900 to-transparent pointer-events-none hidden sm:block"
      />
      <div
        v-if="canScrollRight"
        class="absolute right-0 top-0 bottom-2 w-8 bg-gradient-to-l from-white dark:from-slate-900 to-transparent pointer-events-none hidden sm:block"
      />
    </div>

    <!-- Mobile View All Button (when no inline card) -->
    <div v-if="viewAllLink && products.length < 4" class="mt-4 text-center sm:hidden">
      <UButton :to="viewAllLink" variant="soft" color="primary" size="sm">
        View All Products
        <UIcon name="i-lucide-arrow-right" class="w-4 h-4 ml-1" />
      </UButton>
    </div>
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
