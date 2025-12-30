<script setup lang="ts">
/**
 * Smart Product Card Component
 * Adapts based on authentication status and user type:
 * - Guest/Regular: Shows pricing, rewards CTA
 * - Member/Promoter: Shows BV/PV, reward points (Affiliate benefits)
 * - Advisor/Mentor: Shows rewards but no BV/PV (company employees)
 */
import type { Product } from '~/types/catalog'
import { UserType } from '~/types/user'

interface Props {
  product: Product
  loading?: boolean
  compact?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  compact: false
})

// Auth-aware
const { isLoggedIn } = useSanctum()
const { isMember, isPromoter } = useUserType()

// Check if user can earn Affiliate benefits (only member and promoter)
const canEarnAffiliateBenefits = computed(() => isMember.value || isPromoter.value)

// Cart functionality
const { addToCart: addToCartComposable } = useCart()
const addingToCart = ref(false)
const toast = useToast()

const addToCart = async (event: Event) => {
  event.stopPropagation()
  if (!props.product.in_stock) return

  addingToCart.value = true

  try {
    await addToCartComposable(props.product.slug, 1)
    toast.add({
      title: 'Added to Cart',
      description: `${props.product.name} added to cart`,
      color: 'success',
      icon: 'i-lucide-shopping-cart'
    })
  } catch (error: unknown) {
    toast.add({
      title: 'Error',
      description: error instanceof Error ? error.message : 'Failed to add',
      color: 'error',
      icon: 'i-lucide-alert-circle'
    })
  } finally {
    addingToCart.value = false
  }
}

const viewProduct = () => {
  navigateTo(`/shop/${props.product.slug}`)
}

// Truncate short description
const truncatedDescription = computed(() => {
  if (!props.product.short_description) return null
  const text = props.product.short_description
  return text.length > 60 ? text.slice(0, 60) + '...' : text
})
</script>

<template>
  <div
    class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col"
    @click="viewProduct"
  >
    <!-- Image Section -->
    <div class="relative aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden">
      <img
        v-if="product.image?.url"
        :src="product.image.thumbnail || product.image.url"
        :alt="product.image.alt || product.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        loading="lazy"
      >
      <div v-else class="w-full h-full flex items-center justify-center">
        <UIcon name="i-lucide-package" class="w-16 h-16 text-slate-300 dark:text-slate-600" />
      </div>

      <!-- Out of Stock Overlay -->
      <div
        v-if="!product.in_stock"
        class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm"
      >
        <div class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-sm">
          Out of Stock
        </div>
      </div>

      <!-- Discount Badge (Flipkart Style - Top Left) -->
      <div
        v-if="product.discount_percent"
        class="absolute top-3 left-3 bg-gradient-to-r from-red-600 to-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-md shadow-lg"
      >
        {{ product.discount_percent }}% OFF
      </div>

      <!-- Sale Name Badge (if no discount but has sale name) -->
      <div
        v-else-if="product.sale_name"
        class="absolute top-3 left-3 bg-gradient-to-r from-amber-600 to-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-md shadow-lg"
      >
        {{ product.sale_name }}
      </div>

      <!-- Affiliate Badge - Only for Member/Promoter -->
      <div
        v-if="canEarnAffiliateBenefits && product.bv > 0"
        class="absolute top-3 right-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-xs font-bold px-2.5 py-1 rounded-md shadow-lg"
      >
        {{ product.bv }} BV
      </div>

      <!-- Guest Reward Badge -->
      <div
        v-else-if="!isLoggedIn && product.reward_points > 0"
        class="absolute top-3 right-3 bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white text-xs font-bold px-2.5 py-1 rounded-md shadow-lg"
      >
        🎁 Rewards
      </div>

      <!-- Quick View on Hover -->
      <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
        <div class="text-white font-semibold text-sm bg-white/20 backdrop-blur-md px-4 py-2 rounded-lg">
          Quick View
        </div>
      </div>
    </div>

    <!-- Content Section -->
    <div class="p-3 md:p-4 flex flex-col flex-1">
      <!-- Category -->
      <p
        v-if="product.category"
        class="text-xs text-primary-600 dark:text-primary-400 font-semibold mb-1 uppercase tracking-wide"
      >
        {{ product.category.name }}
      </p>

      <!-- Product Name -->
      <h3 class="font-bold text-slate-900 dark:text-white mb-1 line-clamp-2 text-sm md:text-base min-h-[2.5rem]">
        {{ product.name }}
      </h3>

      <!-- Short Description (Amazon/Flipkart style) -->
      <p
        v-if="truncatedDescription && !compact"
        class="text-xs text-slate-500 dark:text-slate-400 mb-2 line-clamp-2 hidden md:block"
      >
        {{ truncatedDescription }}
      </p>

      <!-- Pricing Section (Amazon/Flipkart Style) -->
      <div class="flex flex-wrap items-baseline gap-1.5 mb-2">
        <span class="text-lg md:text-xl font-black text-slate-900 dark:text-white">
          {{ product.price_formatted }}
        </span>
        <span
          v-if="product.original_price_formatted"
          class="text-xs md:text-sm text-slate-400 line-through"
        >
          {{ product.original_price_formatted }}
        </span>
        <span
          v-if="product.discount_percent"
          class="text-xs font-bold text-emerald-600 dark:text-emerald-400"
        >
          {{ product.discount_percent }}% off
        </span>
      </div>

      <!-- Affiliate Benefits - Only for Member/Promoter -->
      <div v-if="canEarnAffiliateBenefits" class="mt-auto">
        <!-- Reward Points -->
        <div
          v-if="product.reward_points > 0"
          class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold mb-2 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1.5 rounded-lg"
        >
          <UIcon name="i-lucide-gift" class="w-3.5 h-3.5" />
          Earn {{ product.reward_points }} points
        </div>

        <!-- BV/PV Info -->
        <div
          v-if="product.bv > 0 || product.pv > 0"
          class="flex gap-1.5 text-xs mb-2"
        >
          <span v-if="product.bv" class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded font-semibold">
            BV: {{ product.bv }}
          </span>
          <span v-if="product.pv" class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded font-semibold">
            PV: {{ product.pv }}
          </span>
        </div>
      </div>

      <!-- Guest: Encourage Sign In -->
      <div
        v-else-if="!isLoggedIn && product.reward_points > 0"
        class="text-xs text-purple-600 dark:text-purple-400 font-semibold mb-2 bg-purple-50 dark:bg-purple-900/20 px-2 py-1.5 rounded-lg mt-auto"
      >
        🎁 Sign in to earn rewards
      </div>

      <!-- Spacer for consistent card height -->
      <div v-else class="mt-auto" />

      <!-- Add to Cart Button -->
      <UButton
        block
        size="sm"
        :disabled="!product.in_stock || addingToCart || loading"
        :loading="addingToCart || loading"
        :color="product.in_stock ? 'primary' : 'neutral'"
        class="font-semibold mt-2"
        @click="addToCart"
      >
        <UIcon
          :name="product.in_stock ? 'i-lucide-shopping-cart' : 'i-lucide-ban'"
          class="w-4 h-4 mr-1.5"
        />
        {{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}
      </UButton>
    </div>
  </div>
</template>
