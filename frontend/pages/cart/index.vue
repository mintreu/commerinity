<template>
  <div class="relative z-10 w-full">

    <!-- Custom Toast Component -->
    <Toast ref="toastRef" />

    <!-- Global Loader -->
    <GlobalLoader v-if="isLoading" />

    <!-- Main Content -->
    <div v-else class="relative z-10 w-full">

      <!-- Header Section -->
      <div class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-white/20 dark:border-slate-700/50">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm">
              <NuxtLink to="/" class="text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                Home
              </NuxtLink>
              <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
              <span class="font-semibold text-slate-900 dark:text-white">Shopping Cart</span>
            </div>

            <!-- Continue Shopping -->
            <NuxtLink
                to="/shop"
                class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl font-semibold text-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg group"
            >
              <Icon name="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
              <span>Continue Shopping</span>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Cart Header -->
      <div class="max-w-7xl mx-auto px-4 lg:px-8 pt-8 pb-6" ref="cartHeader">
        <div class="text-center">
          <div class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 dark:from-blue-400/10 dark:to-indigo-400/10 rounded-2xl border border-blue-200/50 dark:border-blue-800/50 mb-6">
            <Icon name="mdi:cart" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            <span class="text-lg font-bold text-blue-700 dark:text-blue-300">Shopping Cart</span>
          </div>

          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4">
            <span class="bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
              Your Cart
            </span>
          </h1>

          <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Review your selected items and complete your purchase
          </p>

          <!-- Cart Stats -->
          <div v-if="cartData?.summary" class="flex items-center justify-center gap-8 mt-8 text-center">
            <div class="flex items-center gap-2">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:package-variant" class="w-6 h-6 text-white" />
              </div>
              <div class="text-left">
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ cartData.summary.quantity || 0 }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Items</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:currency-inr" class="w-6 h-6 text-white" />
              </div>
              <div class="text-left">
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ cartData.summary.total || '₹0' }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">Total</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cart Content -->
      <div class="max-w-7xl mx-auto px-4 lg:px-8 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

          <!-- Cart Items Section -->
          <div class="lg:col-span-2 space-y-6" ref="cartItems">

            <!-- Cart Items List -->
            <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60 rounded-3xl shadow-2xl overflow-hidden">
              <div v-if="cartData?.items?.length" class="divide-y divide-slate-200/50 dark:divide-slate-700/50">
                <div
                    v-for="item in cartData.items"
                    :key="item.product_id"
                    class="cart-item p-6 hover:bg-slate-50/50 dark:hover:bg-slate-700/50 transition-all duration-300"
                >
                  <div class="flex flex-col sm:flex-row gap-6">
                    <!-- Product Image -->
                    <div class="flex-shrink-0">
                      <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-600 shadow-lg">
                        <img
                            :src="item.product.thumbnail"
                            :alt="item.product.name"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                            loading="lazy"
                        />
                        <!-- Stock Badge -->
                        <div class="absolute top-2 left-2">
                          <span class="px-2 py-1 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-xs font-bold rounded-lg shadow-lg">
                            In Stock
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Product Details -->
                    <div class="flex-1 min-w-0">
                      <div class="flex flex-col sm:flex-row justify-between h-full gap-4">
                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                          <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 line-clamp-2">
                            {{ item.product.name }}
                          </h3>
                          <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
                            <span class="font-semibold">SKU:</span> {{ item.product.sku }}
                          </p>

                          <!-- Price Info -->
                          <div class="flex items-baseline gap-3 mb-4">
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                              {{ item.total }}
                            </span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">
                              {{ item.quantity }} × {{ item.product.price }}
                            </span>
                          </div>

                          <!-- Mobile: Quantity & Actions -->
                          <div class="sm:hidden flex items-center justify-between pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-700 rounded-2xl p-2">
                              <button
                                  @click="decrement(item)"
                                  :disabled="item.quantity <= item.product.min_quantity"
                                  class="w-10 h-10 bg-white dark:bg-slate-600 rounded-xl flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-500 hover:text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                              >
                                <Icon name="mdi:minus" class="w-4 h-4" />
                              </button>

                              <input
                                  type="number"
                                  v-model.number="item.quantity"
                                  class="w-16 h-10 text-center border-0 bg-transparent font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 rounded-lg"
                                  :min="item.product.min_quantity"
                                  :max="item.product.max_quantity"
                                  @change="updateCartItem(item.product.sku, item.quantity)"
                              />

                              <button
                                  @click="increment(item)"
                                  :disabled="item.quantity >= item.product.max_quantity"
                                  class="w-10 h-10 bg-white dark:bg-slate-600 rounded-xl flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-500 hover:text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                              >
                                <Icon name="mdi:plus" class="w-4 h-4" />
                              </button>
                            </div>

                            <!-- Remove Button -->
                            <button
                                @click="removeItem(item.product.sku)"
                                class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-red-500/25 transition-all duration-300 hover:-translate-y-0.5"
                            >
                              <Icon name="mdi:delete" class="w-4 h-4" />
                              Remove
                            </button>
                          </div>
                        </div>

                        <!-- Desktop: Quantity & Actions -->
                        <div class="hidden sm:flex flex-col items-end justify-between">
                          <!-- Quantity Controls -->
                          <div class="flex items-center gap-3 bg-slate-100 dark:bg-slate-700 rounded-2xl p-2 mb-4">
                            <button
                                @click="decrement(item)"
                                :disabled="item.quantity <= item.product.min_quantity"
                                class="w-10 h-10 bg-white dark:bg-slate-600 rounded-xl flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-500 hover:text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                            >
                              <Icon name="mdi:minus" class="w-4 h-4" />
                            </button>

                            <input
                                type="number"
                                v-model.number="item.quantity"
                                class="w-16 h-10 text-center border-0 bg-transparent font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 rounded-lg"
                                :min="item.product.min_quantity"
                                :max="item.product.max_quantity"
                                @change="updateCartItem(item.product.sku, item.quantity)"
                            />

                            <button
                                @click="increment(item)"
                                :disabled="item.quantity >= item.product.max_quantity"
                                class="w-10 h-10 bg-white dark:bg-slate-600 rounded-xl flex items-center justify-center font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-500 hover:text-white transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                            >
                              <Icon name="mdi:plus" class="w-4 h-4" />
                            </button>
                          </div>

                          <!-- Remove Button -->
                          <button
                              @click="removeItem(item.product.sku)"
                              class="flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-red-500/25 transition-all duration-300 hover:-translate-y-0.5"
                          >
                            <Icon name="mdi:delete" class="w-4 h-4" />
                            Remove
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty Cart State -->
              <div v-else class="text-center py-20">
                <div class="w-32 h-32 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg">
                  <Icon name="mdi:cart-outline" class="w-16 h-16 text-slate-400 dark:text-slate-500" />
                </div>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white mb-4">Your cart is empty</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-8 max-w-md mx-auto">
                  Discover amazing products and start building your perfect collection
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                  <NuxtLink
                      to="/shop"
                      class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group"
                  >
                    <Icon name="mdi:shopping" class="w-6 h-6 group-hover:scale-110 transition-transform" />
                    <span>Start Shopping</span>
                  </NuxtLink>

                  <NuxtLink
                      to="/categories"
                      class="inline-flex items-center gap-2 px-6 py-3 bg-white/80 dark:bg-slate-700/80 hover:bg-white dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold border-2 border-slate-200/60 dark:border-slate-600/60 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300"
                  >
                    <Icon name="mdi:view-grid" class="w-4 h-4" />
                    <span>Browse Categories</span>
                  </NuxtLink>
                </div>
              </div>
            </div>

            <!-- Suggested Products -->
            <div v-if="suggestionProducts.length" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60 rounded-3xl shadow-2xl overflow-hidden" ref="suggestionsSection">
              <div class="p-6 border-b border-slate-200/50 dark:border-slate-700/50">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                  <Icon name="mdi:lightbulb" class="w-6 h-6 text-amber-500" />
                  You might also like
                </h2>
                <p class="text-slate-600 dark:text-slate-400 mt-1">
                  Products frequently bought together
                </p>
              </div>

              <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div
                      v-for="product in suggestionProducts.slice(0, 6)"
                      :key="product.sku"
                      class="group bg-white dark:bg-slate-700 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border border-slate-200/50 dark:border-slate-600/50"
                  >
                    <NuxtLink :to="`/product/${product.url}`" class="block">
                      <!-- Image -->
                      <div class="relative aspect-square bg-slate-100 dark:bg-slate-600 overflow-hidden">
                        <img
                            :src="product.thumbnail"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                            loading="lazy"
                        />
                        <!-- Quick View Overlay -->
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                          <span class="px-4 py-2 bg-white/90 text-slate-900 rounded-xl font-semibold shadow-lg">
                            Quick View
                          </span>
                        </div>
                      </div>

                      <!-- Content -->
                      <div class="p-4">
                        <h3 class="font-bold text-slate-900 dark:text-white mb-2 line-clamp-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors duration-300">
                          {{ product.name }}
                        </h3>
                        <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mb-2">
                          {{ product.price }}
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2">
                          {{ product.short_description }}
                        </p>
                      </div>
                    </NuxtLink>

                    <!-- Add to Cart -->
                    <div class="p-4 pt-0">
                      <AddToCartButton
                          :sku="product.sku"
                          :quantity="1"
                          class="w-full bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:shadow-emerald-500/25 transition-all duration-300 hover:-translate-y-0.5"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Checkout Sidebar -->
          <div class="lg:col-span-1" ref="checkoutSidebar">
            <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60 rounded-3xl shadow-2xl overflow-hidden sticky top-24">

              <!-- Order Summary Header -->
              <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
                <h2 class="text-2xl font-bold flex items-center gap-3">
                  <Icon name="mdi:receipt" class="w-6 h-6" />
                  Order Summary
                </h2>
              </div>

              <div class="p-6 space-y-6">

                <!-- Coupon Section -->
                <div class="space-y-4">
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <Icon name="mdi:ticket-percent" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    Apply Coupon
                  </h3>
                  <div class="flex gap-3">
                    <input
                        v-model="couponCode"
                        type="text"
                        placeholder="Enter coupon code"
                        class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    />
                    <button
                        @click="onApplyCoupon"
                        class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold rounded-xl shadow-lg hover:shadow-emerald-500/25 transition-all duration-300 hover:-translate-y-0.5"
                    >
                      Apply
                    </button>
                  </div>
                </div>

                <!-- Applied Offers -->
                <div v-if="applicableSales?.length" class="space-y-3">
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <Icon name="mdi:tag-heart" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    Applied Offers
                  </h3>
                  <div class="space-y-2">
                    <div
                        v-for="sale in applicableSales"
                        :key="sale.id"
                        class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl"
                    >
                      <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                      <span class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ sale.title }}</span>
                    </div>
                  </div>
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-4">
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <Icon name="mdi:calculator" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    Price Details
                  </h3>

                  <div class="space-y-3 text-sm">
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center py-2">
                      <span class="text-slate-700 dark:text-slate-300">
                        Subtotal ({{ cartData?.summary?.quantity || 0 }} items)
                      </span>
                      <span class="font-semibold text-slate-900 dark:text-white">
                        {{ cartData?.summary?.sub_total || '—' }}
                      </span>
                    </div>

                    <!-- Tax -->
                    <div class="flex justify-between items-center py-2">
                      <span class="text-slate-700 dark:text-slate-300">
                        Tax ({{ cartData?.summary?.tax_percentage || 0 }}%)
                      </span>
                      <span class="font-semibold text-slate-900 dark:text-white">
                        {{ cartData?.summary?.tax || '—' }}
                      </span>
                    </div>

                    <!-- Discount -->
                    <div class="flex justify-between items-center py-2">
                      <span class="text-slate-700 dark:text-slate-300">
                        Discount
                        <span v-if="cartData?.summary?.coupon_code" class="text-xs text-emerald-600 dark:text-emerald-400 ml-1">
                          ({{ cartData.summary.coupon_code }})
                        </span>
                      </span>
                      <span class="font-semibold" :class="(cartData?.summary?.discount && cartData.summary.discount !== '₹0.00') ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'">
                        -{{ cartData?.summary?.discount || '₹0.00' }}
                      </span>
                    </div>

                    <!-- Total -->
                    <div class="border-t border-slate-200 dark:border-slate-600 pt-4 mt-4">
                      <div class="flex justify-between items-center mb-3">
                        <span class="text-xl font-bold text-slate-900 dark:text-white">Total Payable</span>
                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                          {{ cartData?.summary?.total || '—' }}
                        </span>
                      </div>

                      <!-- Total in Words -->
                      <div v-if="cartData?.summary?.total" class="text-center">
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">
                          {{ totalInWords(cartData.summary.total) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Checkout Form -->
                <form @submit.prevent="submitOrder" class="space-y-6">

                  <!-- Guest Form -->
                  <div v-if="!isLoggedIn" class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                      <Icon name="mdi:account" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                      Customer Details
                    </h3>
                    <GuestCartForm v-model="guestForm" />
                  </div>

                  <!-- Logged In User Forms -->
                  <template v-if="isLoggedIn">
                    <!-- Billing Address -->
                    <div class="space-y-4">
                      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Icon name="mdi:home" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        Billing Address
                      </h3>

                      <div v-if="sortedUserAddresses.length" class="space-y-3">
                        <input
                            v-model="checkoutForm.billing.name"
                            type="text"
                            placeholder="Full Name"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                        <input
                            v-model="checkoutForm.billing.email"
                            type="email"
                            placeholder="Email Address"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                        <input
                            v-model="checkoutForm.billing.mobile"
                            type="text"
                            placeholder="Mobile Number"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        />
                        <select
                            v-model="checkoutForm.billing.address"
                            @change="onAddressSelect('billing', checkoutForm.billing.address)"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                          <option disabled value="">Select Address</option>
                          <option v-for="addr in sortedUserAddresses" :key="addr.uuid" :value="addr.uuid">
                            {{ addr.title }} - {{ addr.city }}, {{ addr.state.name }}
                          </option>
                        </select>
                      </div>

                      <div v-else class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-2">
                            <Icon name="mdi:information" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            <span class="text-sm text-blue-800 dark:text-blue-200">No billing addresses found</span>
                          </div>
                          <NuxtLink
                              to="/dashboard/account/address"
                              class="text-blue-600 dark:text-blue-400 font-semibold hover:text-blue-800 dark:hover:text-blue-200 transition-colors text-sm"
                          >
                            Add Address
                          </NuxtLink>
                        </div>
                      </div>
                    </div>

                    <!-- Shipping/Delivery Address -->
                    <div class="space-y-4">
                      <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                          <Icon name="mdi:truck" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                          Delivery Address
                        </h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                          <input
                              type="checkbox"
                              v-model="billingIsShipping"
                              class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-slate-800"
                          />
                          <span class="text-sm text-slate-700 dark:text-slate-300">Same as billing</span>
                        </label>
                      </div>

                      <div v-if="!billingIsShipping && sortedUserAddresses.length" class="space-y-3">
                        <input
                            v-model="checkoutForm.shipping.name"
                            type="text"
                            placeholder="Full Name"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        />
                        <input
                            v-model="checkoutForm.shipping.email"
                            type="email"
                            placeholder="Email Address"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        />
                        <input
                            v-model="checkoutForm.shipping.mobile"
                            type="text"
                            placeholder="Mobile Number"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        />
                        <select
                            v-model="checkoutForm.shipping.address"
                            @change="onAddressSelect('shipping', checkoutForm.shipping.address)"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        >
                          <option disabled value="">Select Address</option>
                          <option v-for="addr in sortedUserAddresses" :key="addr.uuid" :value="addr.uuid">
                            {{ addr.title }} - {{ addr.city }}, {{ addr.state.name }}
                          </option>
                        </select>
                      </div>
                    </div>
                  </template>

                  <!-- Additional Options -->
                  <div class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                      <Icon name="mdi:gift" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                      Additional Options
                    </h3>

                    <div class="space-y-4">
                      <!-- Gift Wrapping -->
                      <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            v-model="checkoutForm.gift"
                            class="w-5 h-5 text-purple-600 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 rounded focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-slate-800"
                        />
                        <div class="flex-1">
                          <span class="text-slate-900 dark:text-white font-medium">Gift wrapping</span>
                          <p class="text-sm text-slate-600 dark:text-slate-400">Beautiful gift wrap for special occasions</p>
                        </div>
                      </label>

                      <!-- Delivery Estimate -->
                      <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <div class="flex items-center gap-2">
                          <Icon name="mdi:truck-fast" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                          <div>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-200">Estimated Delivery:</span>
                            <span class="text-sm text-blue-700 dark:text-blue-300 ml-2">{{ estimatedDelivery }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Payment Method -->
                  <div class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                      <Icon name="mdi:credit-card" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                      Payment Method
                    </h3>

                    <div v-if="paymentProviders.length" class="space-y-3">
                      <label
                          v-for="provider in paymentProviders"
                          :key="provider.url"
                          class="flex items-center gap-4 p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                          :class="{ 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20': checkoutForm.payment === provider.url }"
                      >
                        <input
                            type="radio"
                            :value="provider.url"
                            v-model="checkoutForm.payment"
                            class="w-5 h-5 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-slate-800 bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600"
                        />
                        <img
                            :src="provider.thumbnail"
                            :alt="provider.name"
                            class="w-12 h-12 object-contain rounded-lg shadow-sm"
                        />
                        <div class="flex-1">
                          <span class="font-semibold text-slate-900 dark:text-white">{{ provider.name }}</span>
                          <p class="text-sm text-slate-600 dark:text-slate-400">Secure payment processing</p>
                        </div>
                      </label>
                    </div>

                    <div v-else class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                      <div class="flex items-center gap-2">
                        <Icon name="mdi:alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                        <span class="text-sm text-red-800 dark:text-red-200">No payment providers available</span>
                      </div>
                    </div>
                  </div>

                  <!-- Place Order Button -->
                  <button
                      type="submit"
                      :disabled="!cartData?.items?.length || !checkoutForm.payment"
                      class="w-full py-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 text-white font-bold text-xl rounded-2xl shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-3"
                  >
                    <Icon name="mdi:shopping" class="w-6 h-6" />
                    <span>Place Order</span>
                    <Icon name="mdi:arrow-right" class="w-6 h-6" />
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'
import { useCart } from '~/composables/useCart'
import AddToCartButton from '~/components/cart/AddToCartButton.vue'
import GlobalLoader from '~/components/GlobalLoader.vue'
import GuestCartForm from '~/components/cart/GuestCartForm.vue'

// Composables
const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()
// const { toast, setToastInstance } = useToast()
const { cartData, updateCartItem, removeItem, applyCoupon, fetchCart } = useCart()

/* initialise reusable toast object */
const toast = useToast()

// Refs for animations
const cartHeader = ref<HTMLElement>()
const cartItems = ref<HTMLElement>()
const checkoutSidebar = ref<HTMLElement>()
const suggestionsSection = ref<HTMLElement>()
const toastRef = ref<any>()

// State
const isLoading = useState('pageLoading', () => false)
const couponCode = ref('')
const billingIsShipping = ref(true)
const paymentProviders = ref<any[]>([])
const suggestionProducts = ref<any[]>([])

// Forms
const checkoutForm = reactive({
  billing: { name: '', email: '', mobile: '', address: '' },
  shipping: { name: '', email: '', mobile: '', address: '' },
  gift: false,
  payment: '',
})

const guestForm = ref<Record<string, any>>({})
const userAddresses = ref<any[]>([])

// Computed
const sortedUserAddresses = computed(() => [...(userAddresses.value || [])].sort((a, b) => a.priority - b.priority))
const estimatedDelivery = computed(() => (checkoutForm.shipping.address ? '3–5 business days' : '—'))

// Example placeholder for sales list
const applicableSales = ref<any[]>([
  { id: 1, title: 'Festive Discount - 10% off' },
  { id: 2, title: 'Free Delivery on orders above ₹200' }
])

// Methods
const onApplyCoupon = async () => {
  const code = couponCode.value.trim()
  if (!code) {
    toast.error({ title: 'Coupon', message: 'Enter a coupon code', timeout: 2500 })
    return
  }
  const { success, error } = await applyCoupon(code)
  if (success) {
    toast.success({ title: 'Coupon', message: 'Applied successfully', timeout: 2000 })
  } else {
    toast.error({ title: 'Coupon', message: error ?? 'Invalid or expired coupon', timeout: 3000 })
  }
}

const fetchSuggestionProducts = async () => {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/suggestions/get`)
    suggestionProducts.value = res?.data ?? []
  } catch (e) {
    console.error('Failed to load suggestion products', e)
  }
}

const fetchUserAddress = async () => {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/addresses`)
    userAddresses.value = res?.data || []
    const defaultHome = userAddresses.value.find(a => a.type?.toLowerCase() === 'home' && a.default)
    if (defaultHome) {
      checkoutForm.billing.address = defaultHome.uuid
      onAddressSelect('billing', defaultHome.uuid)
    }
  } catch (e) {
    toast.error({ title: 'Error', message: 'Failed fetching addresses', timeout: 3000 })
  }
}

const onAddressSelect = async (type: 'billing' | 'shipping', uuid: string) => {
  if (!uuid) return

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/addresses/${uuid}`)
    const addr = res?.data
    if (!addr) return

    checkoutForm[type].name = addr.person_name || ''
    checkoutForm[type].email = addr.person_email || ''
    checkoutForm[type].mobile = addr.person_mobile || ''
    checkoutForm[type].address = addr.uuid
  } catch (e) {
    toast.error({ title: 'Error', message: 'Failed fetching addresses', timeout: 3000 })
  }
}

const submitOrder = async () => {
  if (!checkoutForm.payment) {
    toast.error({ title: 'Payment', message: 'Select payment method', timeout: 2500 })
    return
  }

  let payload: Record<string, any>

  if (isLoggedIn.value) {
    payload = {
      billing_address: checkoutForm.billing.address,
      shipping_address: checkoutForm.shipping.address,
      gift: checkoutForm.gift,
      payment_provider: checkoutForm.payment,
      billing_is_shipping: billingIsShipping.value,
    }
  } else {
    if (!guestForm.value.customer_name || !guestForm.value.customer_email) {
      toast.error({ title: 'Guest Info', message: 'Please fill all required fields', timeout: 3000 })
      return
    }
    payload = {
      ...guestForm.value,
      gift: checkoutForm.gift,
      payment_provider: checkoutForm.payment,
      billing_is_shipping: true,
    }
  }

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/order/place`, {
      method: 'POST',
      body: payload,
    })
    if (res?.data?.success) {
      toast.success({ title: 'Order Placed', message: 'Redirecting…', timeout: 2000 })
      setTimeout(() => {
        window.location.href = res.data.checkout_url
      }, 1000)
    } else {
      toast.error({ title: 'Order', message: res?.data?.message ?? 'Order submission failed', timeout: 3000 })
    }
  } catch (e) {
    console.error('Submit order failed', e)
    toast.error({ title: 'Order', message: 'Something went wrong', timeout: 3000 })
  }
}

const increment = (item: any) => {
  if (item.quantity < item.product.max_quantity) {
    const newQty = item.quantity + 1
    item.quantity = newQty
    updateCartItem(item.product.sku, newQty)
  }
}

const decrement = (item: any) => {
  if (item.quantity > item.product.min_quantity) {
    const newQty = item.quantity - 1
    item.quantity = newQty
    updateCartItem(item.product.sku, newQty)
  }
}

const totalInWords = (amount: string): string => {
  try {
    const num = parseFloat(amount.replace(/[₹,]/g, ''))
    if (isNaN(num)) return ''
    return numToWords(num) + ' only'
  } catch {
    return ''
  }
}

const numToWords = (num: number): string => {
  const a = [
    '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
    'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen',
    'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
  ]
  const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety']

  if ((num = Math.floor(num)) === 0) return 'Zero Rupees'

  if (num < 20) return a[num] + ' Rupees'
  if (num < 100) return b[Math.floor(num / 10)] + (num % 10 ? ' ' + a[num % 10] : '') + ' Rupees'
  if (num < 1000) return a[Math.floor(num / 100)] + ' Hundred ' + (num % 100 ? numToWords(num % 100) : '') + ' Rupees'
  if (num < 100000) return numToWords(Math.floor(num / 1000)) + ' Thousand ' + (num % 1000 ? numToWords(num % 1000) : '') + ' Rupees'
  if (num < 10000000) return numToWords(Math.floor(num / 100000)) + ' Lakh ' + (num % 100000 ? numToWords(num % 100000) : '') + ' Rupees'
  return numToWords(Math.floor(num / 10000000)) + ' Crore ' + (num % 10000000 ? numToWords(num % 10000000) : '') + ' Rupees'
}

// Watchers
watch(billingIsShipping, val => {
  if (val) checkoutForm.shipping = { ...checkoutForm.billing }
})

watch(
    () => checkoutForm.billing,
    val => {
      if (billingIsShipping.value) checkoutForm.shipping = { ...val }
    },
    { deep: true }
)

// Lifecycle
onMounted(async () => {
  try {
    // Set up toast instance
    await nextTick()
    // if (toastRef.value) {
    //   setToastInstance(toastRef.value)
    // }

    await fetchCart()
    await fetchSuggestionProducts()
    if (isLoggedIn.value) await fetchUserAddress()

    const providerRes = await useSanctumFetch(`${config.public.apiBase}/integration/payment`)
    paymentProviders.value = providerRes?.data || []
    const defaultProvider = paymentProviders.value.find(p => p.default === 1)
    if (defaultProvider) checkoutForm.payment = defaultProvider.url
  } catch (e) {
    console.error('API fetch failed!', e)
  } finally {
    isLoading.value = false
  }
})

// SEO
useSeoMeta({
  title: 'Shopping Cart - Complete Your Purchase',
  description: 'Review your cart items and proceed to secure checkout with multiple payment options',
})
</script>

<style scoped>
/* Line clamp utilities */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Cart item hover effects */
.cart-item {
  position: relative;
}

.cart-item::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(59, 130, 246, 0.2), transparent);
  border-radius: 1.5rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.cart-item:hover::before {
  opacity: 1;
}

/* Enhanced backdrop blur for Webkit browsers */
@supports (backdrop-filter: blur(20px)) {
  .backdrop-blur-2xl {
    backdrop-filter: blur(20px);
  }
}

/* Focus states for accessibility */
.cart-item:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .cart-item {
    padding: 1rem;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
