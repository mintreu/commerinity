<script setup lang="ts">
/**
 * Enhanced Product Carousel Component
 * Premium carousel with native-like sliding experience:
 * - Mobile: Auto-slide with touch override, resumes after inactivity
 * - Desktop: Full carousel with autoplay, navigation arrows
 * Optimized for PWA mobile-first experience
 */
import type { Product } from '~/types/catalog'

interface Props {
  products: Product[]
  title?: string
  subtitle?: string
  viewAllLink?: string
  loading?: boolean
  badgeText?: string
  badgeIcon?: string
  badgeColor?: 'amber' | 'emerald' | 'violet' | 'blue' | 'red'
  autoplay?: boolean
  autoplayInterval?: number
  showViewAllSlide?: boolean
  mobileAutoSlideDelay?: number // Delay before auto-slide resumes after user interaction
}

const props = withDefaults(defineProps<Props>(), {
  title: '',
  subtitle: '',
  viewAllLink: '',
  loading: false,
  badgeText: '',
  badgeIcon: 'i-lucide-star',
  badgeColor: 'amber',
  autoplay: true,
  autoplayInterval: 4000,
  showViewAllSlide: true,
  mobileAutoSlideDelay: 3000 // Resume auto-slide after 3s of no interaction
})

// Mobile slide state
const mobileActiveIndex = ref(0)
const mobileCarouselRef = ref<HTMLElement | null>(null)
const isSwiping = ref(false)
const swipeStartX = ref(0)
const swipeCurrentX = ref(0)
const swipeDelta = ref(0)
const userInteracting = ref(false)

// Mobile autoplay timers
let mobileAutoplayTimer: ReturnType<typeof setInterval> | null = null
let mobileResumeTimer: ReturnType<typeof setTimeout> | null = null

// Desktop carousel state
const desktopActiveIndex = ref(0)
const isHovered = ref(false)
let desktopAutoplayTimer: ReturnType<typeof setInterval> | null = null

// Mobile items to show (products + view all card)
const mobileItems = computed(() => [...props.products])

const totalMobileSlides = computed(() => {
  return props.showViewAllSlide && props.viewAllLink
    ? mobileItems.value.length + 1
    : mobileItems.value.length
})

// Desktop items per view
const itemsPerView = ref(5)

const updateItemsPerView = () => {
  if (typeof window === 'undefined') return
  const width = window.innerWidth
  if (width >= 1280) itemsPerView.value = 5
  else if (width >= 1024) itemsPerView.value = 4
  else if (width >= 768) itemsPerView.value = 3
  else itemsPerView.value = 2
}

const totalDesktopSlides = computed(() => {
  const productSlides = Math.ceil(props.products.length / itemsPerView.value)
  return props.showViewAllSlide && props.viewAllLink ? productSlides + 1 : productSlides
})

const getProductsForDesktopSlide = (slideIndex: number) => {
  const start = slideIndex * itemsPerView.value
  const end = start + itemsPerView.value
  return props.products.slice(start, end)
}

// ==========================================
// MOBILE AUTOPLAY (with user override)
// ==========================================

const startMobileAutoplay = () => {
  if (!props.autoplay || totalMobileSlides.value <= 1) return
  stopMobileAutoplay()

  mobileAutoplayTimer = setInterval(() => {
    if (!userInteracting.value) {
      // Auto advance - loop back to start
      mobileActiveIndex.value = (mobileActiveIndex.value + 1) % totalMobileSlides.value
    }
  }, props.autoplayInterval)
}

const stopMobileAutoplay = () => {
  if (mobileAutoplayTimer) {
    clearInterval(mobileAutoplayTimer)
    mobileAutoplayTimer = null
  }
}

const pauseMobileAutoplay = () => {
  userInteracting.value = true
  // Clear any pending resume timer
  if (mobileResumeTimer) {
    clearTimeout(mobileResumeTimer)
    mobileResumeTimer = null
  }
}

const scheduleMobileAutoplayResume = () => {
  // Clear any existing resume timer
  if (mobileResumeTimer) {
    clearTimeout(mobileResumeTimer)
  }

  // Schedule resume after delay
  mobileResumeTimer = setTimeout(() => {
    userInteracting.value = false
  }, props.mobileAutoSlideDelay)
}

// Mobile touch handlers for native-like swipe
const onTouchStart = (e: TouchEvent) => {
  isSwiping.value = true
  swipeStartX.value = e.touches[0].clientX
  swipeCurrentX.value = e.touches[0].clientX
  pauseMobileAutoplay() // Pause autoplay when user touches
}

const onTouchMove = (e: TouchEvent) => {
  if (!isSwiping.value) return
  swipeCurrentX.value = e.touches[0].clientX
  swipeDelta.value = swipeCurrentX.value - swipeStartX.value
}

const onTouchEnd = () => {
  if (!isSwiping.value) return
  isSwiping.value = false

  const threshold = 50 // Min swipe distance to trigger slide

  if (swipeDelta.value < -threshold && mobileActiveIndex.value < totalMobileSlides.value - 1) {
    // Swipe left - next slide
    mobileActiveIndex.value++
  } else if (swipeDelta.value > threshold && mobileActiveIndex.value > 0) {
    // Swipe right - prev slide
    mobileActiveIndex.value--
  }

  swipeDelta.value = 0
  scheduleMobileAutoplayResume() // Schedule resume after interaction ends
}

// Mobile navigation via dots
const goToMobileSlide = (index: number) => {
  pauseMobileAutoplay()
  mobileActiveIndex.value = Math.max(0, Math.min(index, totalMobileSlides.value - 1))
  scheduleMobileAutoplayResume()
}

// ==========================================
// DESKTOP AUTOPLAY
// ==========================================

const startDesktopAutoplay = () => {
  if (!props.autoplay || props.products.length <= itemsPerView.value) return
  stopDesktopAutoplay()
  desktopAutoplayTimer = setInterval(() => {
    if (!isHovered.value) {
      desktopActiveIndex.value = (desktopActiveIndex.value + 1) % totalDesktopSlides.value
    }
  }, props.autoplayInterval)
}

const stopDesktopAutoplay = () => {
  if (desktopAutoplayTimer) {
    clearInterval(desktopAutoplayTimer)
    desktopAutoplayTimer = null
  }
}

const nextDesktopSlide = () => {
  desktopActiveIndex.value = (desktopActiveIndex.value + 1) % totalDesktopSlides.value
}

const prevDesktopSlide = () => {
  desktopActiveIndex.value = desktopActiveIndex.value === 0
    ? totalDesktopSlides.value - 1
    : desktopActiveIndex.value - 1
}

const goToDesktopSlide = (index: number) => {
  desktopActiveIndex.value = index
}

// Calculate mobile slide transform with swipe offset
const mobileTransform = computed(() => {
  const cardWidth = 156 // w-[150px] + gap
  const baseTransform = mobileActiveIndex.value * cardWidth
  const swipeOffset = isSwiping.value ? -swipeDelta.value : 0
  return `translateX(-${baseTransform + swipeOffset}px)`
})

onMounted(() => {
  updateItemsPerView()
  startMobileAutoplay()
  startDesktopAutoplay()
  window.addEventListener('resize', updateItemsPerView)
})

onUnmounted(() => {
  stopMobileAutoplay()
  stopDesktopAutoplay()
  if (mobileResumeTimer) clearTimeout(mobileResumeTimer)
  window.removeEventListener('resize', updateItemsPerView)
})

// Badge color classes
const badgeColorClasses = computed(() => {
  const colors = {
    amber: 'from-amber-500/10 to-orange-500/10 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300',
    emerald: 'from-emerald-500/10 to-teal-500/10 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300',
    violet: 'from-violet-500/10 to-purple-500/10 border-violet-200 dark:border-violet-800 text-violet-700 dark:text-violet-300',
    blue: 'from-blue-500/10 to-cyan-500/10 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300',
    red: 'from-red-500/10 to-rose-500/10 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'
  }
  return colors[props.badgeColor]
})

const iconColorClass = computed(() => {
  const colors = {
    amber: 'text-amber-600 dark:text-amber-400',
    emerald: 'text-emerald-600 dark:text-emerald-400',
    violet: 'text-violet-600 dark:text-violet-400',
    blue: 'text-blue-600 dark:text-blue-400',
    red: 'text-red-600 dark:text-red-400'
  }
  return colors[props.badgeColor]
})

const gradientClass = computed(() => {
  const gradients = {
    amber: 'from-amber-500 via-orange-500 to-red-500',
    emerald: 'from-emerald-500 via-teal-500 to-cyan-500',
    violet: 'from-violet-500 via-purple-500 to-fuchsia-500',
    blue: 'from-blue-500 via-indigo-500 to-purple-500',
    red: 'from-red-500 via-rose-500 to-pink-500'
  }
  return gradients[props.badgeColor]
})

const dotColorClass = computed(() => {
  const colors = {
    amber: 'bg-amber-500',
    emerald: 'bg-emerald-500',
    violet: 'bg-violet-500',
    blue: 'bg-blue-500',
    red: 'bg-red-500'
  }
  return colors[props.badgeColor]
})
</script>

<template>
  <div class="relative">
    <!-- Header -->
    <div v-if="title || badgeText" class="flex items-center justify-between mb-4 md:mb-6">
      <div>
        <!-- Badge -->
        <div
          v-if="badgeText"
          :class="[
            'inline-flex items-center gap-2 px-3 py-1.5 md:px-5 md:py-2.5 rounded-full bg-gradient-to-r border backdrop-blur-sm mb-2 md:mb-3 text-xs md:text-sm',
            badgeColorClasses
          ]"
        >
          <UIcon :name="badgeIcon" :class="['w-4 h-4 md:w-5 md:h-5', iconColorClass]" />
          <span class="font-bold">{{ badgeText }}</span>
        </div>

        <!-- Title -->
        <h2 v-if="title" class="text-xl md:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white">
          {{ title }}
        </h2>

        <!-- Subtitle -->
        <p v-if="subtitle" class="text-sm md:text-base text-slate-600 dark:text-slate-400 mt-0.5">
          {{ subtitle }}
        </p>
      </div>

      <!-- View All + Navigation (Desktop) -->
      <div class="hidden md:flex items-center gap-3">
        <NuxtLink
          v-if="viewAllLink"
          :to="viewAllLink"
          class="inline-flex items-center gap-2 text-primary-600 dark:text-primary-400 font-bold hover:underline"
        >
          View All
          <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
        </NuxtLink>

        <!-- Navigation Arrows -->
        <div v-if="totalDesktopSlides > 1" class="flex items-center gap-2">
          <button
            class="w-10 h-10 rounded-full flex items-center justify-center border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-lg hover:shadow-xl transition-all"
            @click="prevDesktopSlide"
          >
            <UIcon name="i-lucide-chevron-left" class="w-5 h-5" />
          </button>
          <button
            class="w-10 h-10 rounded-full flex items-center justify-center border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-lg hover:shadow-xl transition-all"
            @click="nextDesktopSlide"
          >
            <UIcon name="i-lucide-chevron-right" class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Mobile View All Link -->
      <NuxtLink
        v-if="viewAllLink"
        :to="viewAllLink"
        class="md:hidden text-sm font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1"
      >
        All
        <UIcon name="i-lucide-chevron-right" class="w-4 h-4" />
      </NuxtLink>
    </div>

    <!-- MOBILE: Native-like Swipe Carousel with Auto-slide (< md) -->
    <div class="block md:hidden relative overflow-hidden">
      <!-- Carousel Track -->
      <div
        ref="mobileCarouselRef"
        class="touch-pan-y select-none"
        @touchstart="onTouchStart"
        @touchmove="onTouchMove"
        @touchend="onTouchEnd"
      >
        <!-- Loading Skeletons -->
        <div v-if="loading" class="flex gap-[6px]">
          <div v-for="i in 3" :key="i" class="shrink-0 w-[150px]">
            <div class="bg-white/80 dark:bg-slate-900/80 rounded-xl overflow-hidden animate-pulse">
              <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
              <div class="p-3 space-y-2">
                <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
                <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sliding Cards -->
        <div
          v-else
          class="flex gap-[6px] will-change-transform"
          :class="{ 'transition-transform duration-300 ease-out': !isSwiping }"
          :style="{ transform: mobileTransform }"
        >
          <!-- Product Cards -->
          <div
            v-for="product in mobileItems"
            :key="product.slug"
            class="shrink-0 w-[150px]"
          >
            <StoreProductCard :product="product" compact />
          </div>

          <!-- View All Slide Card -->
          <NuxtLink
            v-if="viewAllLink && showViewAllSlide"
            :to="viewAllLink"
            class="shrink-0 w-[150px]"
          >
            <div :class="['h-full min-h-[240px] flex items-center justify-center bg-gradient-to-br rounded-xl p-4 text-center text-white relative overflow-hidden group active:scale-95 transition-transform', gradientClass]">
              <!-- Shimmer Effect -->
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-shimmer" />

              <div class="relative z-10 flex flex-col items-center space-y-3">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                  <UIcon name="i-lucide-arrow-right" class="w-7 h-7" />
                </div>
                <span class="font-bold">View All</span>
                <span class="text-xs opacity-80">{{ products.length }}+ Products</span>
              </div>
            </div>
          </NuxtLink>
        </div>
      </div>

      <!-- Mobile Slide Indicators (Dots) with auto-play indicator -->
      <div class="flex justify-center items-center gap-1.5 mt-4">
        <!-- Progress bar under active dot (shows auto-slide progress) -->
        <template v-for="(_, index) in Math.min(totalMobileSlides, 7)" :key="index">
          <button
            :class="[
              'rounded-full transition-all duration-300 relative overflow-hidden',
              mobileActiveIndex === index
                ? `w-6 h-2 ${dotColorClass}`
                : 'w-2 h-2 bg-slate-300 dark:bg-slate-600'
            ]"
            @click="goToMobileSlide(index)"
          >
            <!-- Auto-slide progress indicator on active dot -->
            <div
              v-if="mobileActiveIndex === index && autoplay && !userInteracting"
              class="absolute inset-0 bg-white/30 origin-left animate-progress"
              :style="{ animationDuration: `${autoplayInterval}ms` }"
            />
          </button>
        </template>

        <!-- More indicator if many slides -->
        <span
          v-if="totalMobileSlides > 7"
          class="text-xs text-slate-400 ml-1"
        >
          +{{ totalMobileSlides - 7 }}
        </span>
      </div>
    </div>

    <!-- DESKTOP: Carousel with Slides (md+) -->
    <div
      class="hidden md:block relative overflow-hidden"
      @mouseenter="isHovered = true"
      @mouseleave="isHovered = false"
    >
      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-4 lg:grid-cols-5 gap-6">
        <div v-for="i in 5" :key="i" class="bg-white/80 dark:bg-slate-900/80 rounded-2xl overflow-hidden animate-pulse">
          <div class="aspect-square bg-slate-200 dark:bg-slate-700" />
          <div class="p-4 space-y-3">
            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
            <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-1/2" />
            <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3" />
          </div>
        </div>
      </div>

      <!-- Slides Container -->
      <div v-else class="relative">
        <div
          class="flex transition-transform duration-500 ease-out"
          :style="{ transform: `translateX(-${desktopActiveIndex * 100}%)` }"
        >
          <!-- Product Slides -->
          <div
            v-for="slideIndex in Math.ceil(products.length / itemsPerView)"
            :key="`slide-${slideIndex}`"
            class="w-full shrink-0"
          >
            <div class="grid grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-6">
              <div v-for="product in getProductsForDesktopSlide(slideIndex - 1)" :key="product.slug">
                <StoreProductCard :product="product" />
              </div>
            </div>
          </div>

          <!-- View All Slide -->
          <div v-if="showViewAllSlide && viewAllLink" class="w-full shrink-0">
            <NuxtLink :to="viewAllLink" class="block">
              <div :class="['min-h-[400px] flex items-center justify-center bg-gradient-to-br rounded-3xl p-8 text-center text-white relative overflow-hidden group shadow-2xl', gradientClass]">
                <!-- Animated Background -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000" />

                <!-- Floating Orbs -->
                <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl animate-pulse" />
                <div class="absolute bottom-10 right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s;" />

                <div class="relative z-10 flex flex-col items-center space-y-6">
                  <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                    <UIcon name="i-lucide-store" class="w-12 h-12" />
                  </div>
                  <h3 class="text-3xl lg:text-4xl font-black">Explore All Products</h3>
                  <p class="text-lg opacity-90 max-w-md">Browse our complete collection of premium products</p>
                  <div class="bg-white text-slate-900 font-bold px-8 py-4 rounded-xl text-lg hover:scale-105 hover:shadow-2xl transition-all inline-flex items-center gap-3">
                    <UIcon :name="badgeIcon" class="w-6 h-6" />
                    <span>View Collection</span>
                    <UIcon name="i-lucide-arrow-right" class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                  </div>
                </div>
              </div>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Desktop Dots Navigation -->
      <div v-if="totalDesktopSlides > 1 && !loading" class="flex justify-center gap-2 mt-6">
        <button
          v-for="index in totalDesktopSlides"
          :key="index"
          :class="[
            'w-2.5 h-2.5 rounded-full transition-all duration-300',
            desktopActiveIndex === index - 1
              ? `${dotColorClass} w-8`
              : 'bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'
          ]"
          @click="goToDesktopSlide(index - 1)"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 3s infinite;
}

@keyframes progress {
  0% {
    transform: scaleX(0);
  }
  100% {
    transform: scaleX(1);
  }
}

.animate-progress {
  animation: progress linear forwards;
}
</style>
