<script setup lang="ts">
/**
 * Shopping Cart Page
 * Full cart management with quantity controls, order summary, and checkout flow
 */

definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const toast = useToast()
const { isLoggedIn } = useSanctum()

// Cart composable
const {
  cart,
  cartItems,
  cartCount,
  cartTotalFormatted,
  isCartLoading,
  fetchCart,
  updateQuantity,
  removeFromCart,
  clearCart
} = useCart()

// Local state
const updatingItem = ref<string | null>(null)
const removingItem = ref<string | null>(null)

// Fetch cart on mount
onMounted(() => {
  fetchCart()
})

// Quantity handlers
const incrementQuantity = async (item: { product_slug: string; quantity: number }) => {
  if (item.quantity >= 10) return // Max limit

  updatingItem.value = item.product_slug
  await updateQuantity(item.product_slug, item.quantity + 1)
  updatingItem.value = null
}

const decrementQuantity = async (item: { product_slug: string; quantity: number }) => {
  if (item.quantity <= 1) return // Min limit

  updatingItem.value = item.product_slug
  await updateQuantity(item.product_slug, item.quantity - 1)
  updatingItem.value = null
}

const handleRemoveItem = async (productSlug: string) => {
  removingItem.value = productSlug
  await removeFromCart(productSlug)
  removingItem.value = null
}

const handleClearCart = async () => {
  if (!confirm('Are you sure you want to clear your cart?')) return
  await clearCart()
  toast.add({
    title: 'Cart Cleared',
    description: 'All items have been removed from your cart',
    color: 'neutral',
    icon: 'i-lucide-trash-2'
  })
}

// Checkout modal state
const showCheckoutModal = ref(false)
const cartTotal = computed(() => cart.value?.total || 0)
const cartTotalAmount = computed(() => cart.value?.total_amount || 0)

// Proceed to checkout
const proceedToCheckout = () => {
  if (!isLoggedIn.value) {
    navigateTo('/auth/login?redirect=/cart')
    return
  }

  if (cartCount.value === 0) {
    toast.add({
      title: 'Cart is Empty',
      description: 'Add items to your cart before checking out',
      color: 'warning'
    })
    return
  }

  showCheckoutModal.value = true
}

const handleCheckoutSuccess = async () => {
  await fetchCart()
  toast.add({
    title: 'Order Placed',
    description: 'Your order has been placed successfully',
    color: 'success',
    icon: 'i-lucide-check-circle'
  })
}

// SEO
useComprehensiveSeo({
  title: 'Shopping Cart',
  description: 'Review your cart items and proceed to checkout',
  type: 'website'
})
</script>

<template>
  <div class="min-h-screen py-8">
    <UContainer>
      <!-- Header Section -->
      <div class="mb-8">
        <!-- Breadcrumb with Cart Icon -->
        <div class="flex items-center justify-between mb-6">
          <nav class="flex items-center gap-2 text-sm">
            <NuxtLink
              to="/"
              class="text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
            >
              Home
            </NuxtLink>
            <UIcon name="i-lucide-chevron-right" class="w-4 h-4 text-slate-400" />
            <span class="font-semibold text-slate-900 dark:text-white">Shopping Cart</span>
          </nav>

          <NuxtLink
            to="/shop"
            class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl font-semibold text-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg group"
          >
            <UIcon name="i-lucide-arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
            <span>Continue Shopping</span>
          </NuxtLink>
        </div>

        <!-- Cart Title -->
        <div class="text-center">
          <div class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-violet-500/10 to-fuchsia-500/10 dark:from-violet-400/10 dark:to-fuchsia-400/10 rounded-2xl border border-violet-200/50 dark:border-violet-800/50 mb-6">
            <UIcon name="i-lucide-shopping-cart" class="w-6 h-6 text-violet-600 dark:text-violet-400" />
            <span class="text-lg font-bold text-violet-700 dark:text-violet-300">Shopping Cart</span>
          </div>

          <h1 class="text-3xl md:text-4xl lg:text-5xl font-black mb-4">
            <span class="bg-gradient-to-r from-slate-900 via-violet-600 to-fuchsia-600 dark:from-white dark:via-violet-400 dark:to-fuchsia-400 bg-clip-text text-transparent">
              Your Cart
            </span>
          </h1>

          <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Review your selected items and complete your purchase
          </p>

          <!-- Cart Stats -->
          <div v-if="cartCount > 0" class="flex items-center justify-center gap-8 mt-8 text-center">
            <div class="flex items-center gap-2">
              <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-fuchsia-600 rounded-2xl flex items-center justify-center shadow-lg">
                <UIcon name="i-lucide-package" class="w-6 h-6 text-white" />
              </div>
              <div class="text-left">
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ cartCount }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Items</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                <UIcon name="i-lucide-indian-rupee" class="w-6 h-6 text-white" />
              </div>
              <div class="text-left">
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ cartTotalFormatted }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Total</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isCartLoading" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-3xl shadow-xl overflow-hidden">
            <div v-for="i in 3" :key="i" class="p-6 border-b border-slate-200/50 dark:border-slate-700/50 last:border-b-0 animate-pulse">
              <div class="flex gap-6">
                <div class="w-24 h-24 bg-slate-200 dark:bg-slate-700 rounded-2xl" />
                <div class="flex-1 space-y-3">
                  <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded w-3/4" />
                  <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/4" />
                  <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="lg:col-span-1">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-3xl shadow-xl p-6 animate-pulse">
            <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/2 mb-6" />
            <div class="space-y-4">
              <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded" />
              <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded" />
              <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded" />
              <div class="h-12 bg-slate-200 dark:bg-slate-700 rounded mt-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Cart Content -->
      <div v-else-if="cartItems.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-3xl shadow-xl overflow-hidden">
            <div
              v-for="item in cartItems"
              :key="item.product_slug"
              class="p-6 border-b border-slate-200/50 dark:border-slate-700/50 last:border-b-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors"
            >
              <div class="flex flex-col sm:flex-row gap-6">
                <!-- Product Image -->
                <NuxtLink :to="`/shop/${item.product_slug}`" class="shrink-0">
                  <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 shadow-lg group">
                    <img
                      v-if="item.image"
                      :src="item.image"
                      :alt="item.name"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      loading="lazy"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <UIcon name="i-lucide-package" class="w-12 h-12 text-slate-300 dark:text-slate-600" />
                    </div>
                  </div>
                </NuxtLink>

                <!-- Product Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-col sm:flex-row justify-between h-full gap-4">
                    <!-- Product Info -->
                    <div class="flex-1 min-w-0">
                      <NuxtLink :to="`/shop/${item.product_slug}`">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                          {{ item.name }}
                        </h3>
                      </NuxtLink>
                      <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                        <span class="font-medium">SKU:</span> {{ item.sku }}
                      </p>

                      <!-- Price Info -->
                      <div class="flex items-baseline gap-3 mb-4">
                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                          {{ item.subtotal_formatted }}
                        </span>
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                          {{ item.quantity }} x {{ item.price_formatted }}
                        </span>
                      </div>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between gap-4">
                      <!-- Quantity Controls -->
                      <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
                        <button
                          :disabled="item.quantity <= 1 || updatingItem === item.product_slug"
                          class="w-10 h-10 bg-white dark:bg-slate-600 rounded-lg flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 hover:bg-violet-500 hover:text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                          @click="decrementQuantity(item)"
                        >
                          <UIcon name="i-lucide-minus" class="w-4 h-4" />
                        </button>

                        <span class="w-10 text-center font-bold text-slate-900 dark:text-white">
                          {{ updatingItem === item.product_slug ? '...' : item.quantity }}
                        </span>

                        <button
                          :disabled="item.quantity >= 10 || updatingItem === item.product_slug"
                          class="w-10 h-10 bg-white dark:bg-slate-600 rounded-lg flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 hover:bg-violet-500 hover:text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                          @click="incrementQuantity(item)"
                        >
                          <UIcon name="i-lucide-plus" class="w-4 h-4" />
                        </button>
                      </div>

                      <!-- Remove Button -->
                      <button
                        :disabled="removingItem === item.product_slug"
                        class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-red-500/25 transition-all duration-300 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="handleRemoveItem(item.product_slug)"
                      >
                        <UIcon
                          :name="removingItem === item.product_slug ? 'i-lucide-loader-2' : 'i-lucide-trash-2'"
                          :class="{ 'animate-spin': removingItem === item.product_slug }"
                          class="w-4 h-4"
                        />
                        <span class="hidden sm:inline">Remove</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Clear Cart Button -->
          <div class="flex justify-end">
            <button
              class="flex items-center gap-2 px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl font-semibold transition-all duration-300"
              @click="handleClearCart"
            >
              <UIcon name="i-lucide-trash-2" class="w-4 h-4" />
              Clear Cart
            </button>
          </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-3xl shadow-xl overflow-hidden sticky top-24">
            <!-- Header -->
            <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white p-6">
              <h2 class="text-xl font-bold flex items-center gap-3">
                <UIcon name="i-lucide-receipt" class="w-6 h-6" />
                Order Summary
              </h2>
            </div>

            <div class="p-6 space-y-6">
              <!-- Price Details -->
              <div class="space-y-3">
                <div class="flex justify-between items-center py-2">
                  <span class="text-slate-700 dark:text-slate-300">
                    Subtotal ({{ cartCount }} items)
                  </span>
                  <span class="font-semibold text-slate-900 dark:text-white">
                    {{ cartTotalFormatted }}
                  </span>
                </div>

                <div class="flex justify-between items-center py-2">
                  <span class="text-slate-700 dark:text-slate-300">Shipping</span>
                  <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                    Calculated at checkout
                  </span>
                </div>

                <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                  <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-slate-900 dark:text-white">Total</span>
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                      {{ cartTotalFormatted }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Checkout Button -->
              <UButton
                block
                size="lg"
                class="bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 hover:from-violet-700 hover:via-fuchsia-700 hover:to-pink-700 text-white font-bold text-lg py-4 rounded-2xl shadow-2xl hover:shadow-violet-500/25 transition-all duration-300 hover:-translate-y-1"
                @click="proceedToCheckout"
              >
                <UIcon name="i-lucide-credit-card" class="w-5 h-5 mr-2" />
                Proceed to Checkout
                <UIcon name="i-lucide-arrow-right" class="w-5 h-5 ml-2" />
              </UButton>

              <!-- Checkout Modal -->
              <CheckoutModal
                v-model:open="showCheckoutModal"
                title="Complete Your Order"
                :amount="cartTotalAmount"
                :amount-formatted="cartTotalFormatted"
                description="Shopping Cart Checkout"
                checkout-endpoint="/api/cart/checkout"
                @success="handleCheckoutSuccess"
              />

              <!-- Continue Shopping Link (Mobile) -->
              <NuxtLink
                to="/shop"
                class="sm:hidden flex items-center justify-center gap-2 px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:bg-slate-200 dark:hover:bg-slate-600"
              >
                <UIcon name="i-lucide-arrow-left" class="w-4 h-4" />
                Continue Shopping
              </NuxtLink>

              <!-- Secure Checkout Badge -->
              <div class="flex items-center justify-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <UIcon name="i-lucide-shield-check" class="w-4 h-4 text-emerald-500" />
                <span>Secure checkout</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty Cart State -->
      <div v-else class="text-center py-16">
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-3xl shadow-xl max-w-lg mx-auto p-12">
          <div class="w-32 h-32 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg">
            <UIcon name="i-lucide-shopping-cart" class="w-16 h-16 text-slate-400 dark:text-slate-500" />
          </div>
          <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Your cart is empty</h3>
          <p class="text-slate-600 dark:text-slate-400 mb-8 max-w-md mx-auto">
            Discover amazing products and start building your perfect collection
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <NuxtLink to="/shop">
              <UButton
                size="lg"
                class="bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white font-bold"
              >
                <UIcon name="i-lucide-shopping-bag" class="w-5 h-5 mr-2" />
                Start Shopping
              </UButton>
            </NuxtLink>

            <NuxtLink to="/categories">
              <UButton
                variant="outline"
                size="lg"
                class="font-semibold"
              >
                <UIcon name="i-lucide-grid-3x3" class="w-5 h-5 mr-2" />
                Browse Categories
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
