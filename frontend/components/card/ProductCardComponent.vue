<template>
  <div
      class="group relative flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200"
      :class="cardClass"
  >
    <!-- Badges -->
    <div class="absolute top-2 left-2 z-10 flex gap-2">
      <span v-if="badges && badges.length" v-for="(b, i) in badges" :key="`badge-${i}`" class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
            :class="badgeClass(b.type)">
        {{ b.text }}
      </span>
    </div>

    <!-- Wishlist / Quick actions -->
    <div class="absolute top-2 right-2 z-10 flex gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
      <button @click.stop="toggleWishlist" class="p-1.5 rounded-full bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 hover:scale-105 active:scale-95 shadow">
        <Icon :name="isWishlisted ? 'mdi:heart' : 'mdi:heart-outline'" class="w-4 h-4 text-rose-600" />
      </button>
      <button v-if="onQuickView" @click.stop="onQuickView(product)" class="p-1.5 rounded-full bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 hover:scale-105 active:scale-95 shadow">
        <Icon name="mdi:eye-outline" class="w-4 h-4" />
      </button>
    </div>

    <!-- Image -->
    <NuxtLink :to="productLink" class="block">
      <div class="relative w-full" :style="{ minHeight: minImageHeight }">
        <img
            :src="product.thumbnail"
            :alt="product.name"
            loading="lazy"
            class="w-full h-full object-contain transition-transform duration-300 ease-out transform group-hover:scale-[1.04] bg-white dark:bg-gray-900"
            :class="imageClass"
        />
        <!-- like / dislike quick feedback on hover for md+ -->
        <div class="hidden md:flex absolute bottom-2 right-2 gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
          <button @click.stop="feedback('like')" class="p-1 rounded-full bg-white/90 dark:bg-gray-800/90 shadow hover:scale-105 active:scale-95">
            <Icon name="mdi:thumb-up-outline" class="w-4 h-4 text-emerald-600" />
          </button>
          <button @click.stop="feedback('dislike')" class="p-1 rounded-full bg-white/90 dark:bg-gray-800/90 shadow hover:scale-105 active:scale-95">
            <Icon name="mdi:thumb-down-outline" class="w-4 h-4 text-rose-600" />
          </button>
        </div>
      </div>
    </NuxtLink>

    <!-- Content -->
    <div class="flex flex-col gap-1.5 p-3">
      <!-- Title -->
      <NuxtLink :to="productLink" class="hover:underline">
        <h3 class="text-sm md:text-base font-semibold leading-snug line-clamp-2">
          {{ product.name }}
        </h3>
      </NuxtLink>

      <!-- Rating + sales -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex">
            <Icon v-for="i in 5" :key="i" :name="i <= starInt ? 'mdi:star' : (i - rating < 1 && i - rating > 0 ? 'mdi:star-half-full' : 'mdi:star-outline')"
                  class="w-3.5 h-3.5 md:w-4 md:h-4"
                  :class="i <= rating ? 'text-amber-500' : 'text-gray-400 dark:text-gray-600'" />
          </div>
          <span class="ml-1 text-xs text-gray-500">({{ ratingCount }})</span>
        </div>
        <span v-if="sales" class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium hidden sm:inline">{{ sales }} sold</span>
      </div>

      <!-- Price row -->
      <div class="flex items-baseline gap-2">
        <span class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100">₹{{ priceMain }}</span>
        <span v-if="priceStrike" class="text-xs md:text-sm text-gray-400 line-through">₹{{ priceStrike }}</span>
        <span v-if="discountPct" class="text-[11px] md:text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ discountPct }}% off</span>
      </div>

      <!-- Short description (truncate on small) -->
      <p class="hidden sm:block text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
        {{ product.short_description }}
      </p>

      <!-- Actions -->
      <div class="mt-2 grid grid-cols-2 gap-2">
        <button
            @click.stop="addToCart"
            class="inline-flex items-center justify-center gap-1.5 text-xs md:text-sm font-semibold px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow active:scale-[0.99] transition"
        >
          <Icon name="mdi:cart-plus" class="w-4 h-4" />
          <span>Add</span>
        </button>

        <NuxtLink
            :to="productLink"
            class="inline-flex items-center justify-center gap-1.5 text-xs md:text-sm font-semibold px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
        >
          <Icon name="mdi:eye-outline" class="w-4 h-4" />
          <span>View</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Mobile quick feedback -->
    <div class="flex md:hidden items-center justify-between px-3 pb-3">
      <button @click.stop="feedback('like')" class="flex items-center gap-1 text-[11px] text-emerald-700 dark:text-emerald-400">
        <Icon name="mdi:thumb-up-outline" class="w-4 h-4" />
        <span>Like</span>
      </button>
      <button @click.stop="feedback('dislike')" class="flex items-center gap-1 text-[11px] text-rose-600">
        <Icon name="mdi:thumb-down-outline" class="w-4 h-4" />
        <span>Dislike</span>
      </button>
      <button @click.stop="toggleWishlist" class="flex items-center gap-1 text-[11px] text-rose-600">
        <Icon :name="isWishlisted ? 'mdi:heart' : 'mdi:heart-outline'" class="w-4 h-4" />
        <span>Wishlist</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

// Props: adapt to your product shape
const props = defineProps<{
  product: {
    url: string
    name: string
    sku: string
    thumbnail: string
    short_description?: string
    price: number | string
    mrp?: number | string
    view_count?: number
    rating?: number
    rating_count?: number
    sales?: number
    badges?: Array<{ text: string; type?: 'primary' | 'success' | 'warn' | 'danger' }>
  }
  dense?: boolean
  onQuickView?: (p: any) => void
}>()

const emit = defineEmits<{
  (e: 'add', sku: string): void
  (e: 'wishlist', sku: string, active: boolean): void
  (e: 'feedback', payload: { sku: string; action: 'like' | 'dislike' }): void
}>()

const router = useRouter()

const isWishlisted = ref(false)

const productLink = computed(() => `/product/${props.product.url}`)
const priceMain = computed(() => formatMoney(props.product.price))
const priceStrike = computed(() => props.product.mrp ? formatMoney(props.product.mrp) : null)
const discountPct = computed(() => {
  const mrp = toNum(props.product.mrp)
  const p = toNum(props.product.price)
  if (!mrp || !p || p >= mrp) return null
  return Math.round(((mrp - p) / mrp) * 100)
})
const rating = computed(() => Number(props.product.rating ?? 0))
const ratingCount = computed(() => Number(props.product.rating_count ?? 0))
const starInt = computed(() => Math.floor(rating.value))
const sales = computed(() => props.product.sales ? Number(props.product.sales) : 0)
const badges = computed(() => props.product.badges || [])

const minImageHeight = computed(() => props.dense ? '9rem' : '13rem')
const imageClass = computed(() => props.dense ? 'p-3' : 'p-4')
const cardClass = computed(() => props.dense ? 'min-h-[320px]' : 'min-h-[360px]')

// Methods
function toNum(x: any) {
  if (x == null) return 0
  if (typeof x === 'number') return x
  const s = String(x).replace(/[₹,\s]/g, '')
  const n = Number(s)
  return isNaN(n) ? 0 : n
}
function formatMoney(x: any) {
  const n = toNum(x)
  return n.toLocaleString('en-IN', { maximumFractionDigits: 0 })
}
function addToCart() {
  emit('add', props.product.sku)
}
function toggleWishlist() {
  isWishlisted.value = !isWishlisted.value
  emit('wishlist', props.product.sku, isWishlisted.value)
}
function feedback(action: 'like' | 'dislike') {
  emit('feedback', { sku: props.product.sku, action })
}
function badgeClass(t?: string) {
  switch (t) {
    case 'success': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
    case 'warn': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
    case 'danger': return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'
    default: return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
  }
}
</script>
