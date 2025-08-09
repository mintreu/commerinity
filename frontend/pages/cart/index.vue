<template>
  <div class="w-full container mx-auto px-4 py-6 space-y-6 text-gray-900 dark:text-gray-100">
    <h1 class="text-2xl md:text-3xl font-semibold">My Cart</h1>

    <div class="flex flex-col md:flex-row gap-6">
      <!-- Cart Items + Summary -->
      <aside class="w-full md:w-2/3 space-y-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-4">
          <h2 class="text-lg font-semibold">Items in My Cart</h2>
          <hr class="my-2 border-gray-300 dark:border-gray-600" />

          <div v-if="cartData?.items?.length" class="space-y-4">
            <div
                v-for="item in cartData.items"
                :key="item.product_id"
                class="flex items-center justify-between border-b pb-4 border-gray-200 dark:border-gray-700"
            >
              <img :src="item.product.thumbnail" class="w-20 h-20 object-cover rounded" />
              <div class="flex-1 px-4">
                <h3 class="font-medium">{{ item.product.name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.product.sku }}</p>
                <div class="flex items-center mt-2 space-x-2">
                  <button @click="decrement(item)" :disabled="item.quantity <= item.product.min_quantity">−</button>
                  <input
                      type="number"
                      v-model.number="item.quantity"
                      class="w-12 text-center border rounded bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600"
                      :min="item.product.min_quantity"
                      :max="item.product.max_quantity"
                  />
                  <button @click="increment(item)" :disabled="item.quantity >= item.product.max_quantity">+</button>
                  <button @click="removeItem(item)" class="text-red-500 text-sm ml-4">Remove</button>
                </div>
              </div>
              <div class="text-right">
                <div>{{ item.total }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.quantity }} × {{ item.product.price }}</div>
              </div>
            </div>
          </div>
          <div v-else><p>Your cart is empty.</p></div>
        </div>

        <!-- Coupon -->
        <div class="flex flex-col md:flex-row gap-4 items-center">
          <input v-model="couponCode" type="text" placeholder="Apply coupon" class="input flex-grow bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100" />
          <button @click="applyCoupon" class="bg-green-600 dark:bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">
            Apply
          </button>
        </div>

        <!-- Summary -->
        <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-4 space-y-1 text-sm">
          <div>Subtotal: {{ cartData?.summary?.sub_total ?? '—' }}</div>
          <div>Tax: {{ cartData?.summary?.tax ?? '—' }}</div>
          <div v-if="cartData?.summary?.coupon_applied">Discount: {{ cartData?.summary?.discount }}</div>
          <div class="font-bold">Total: {{ cartData?.summary?.total ?? '—' }}</div>
        </div>

        <!-- Suggestion Products -->
        <section v-if="suggestionProducts.length">
          <h2 class="text-2xl font-bold mb-6">User Also Buy</h2>
          <Swiper
              :slides-per-view="1"
              :space-between="20"
              :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }"
              class="my-6"
          >
            <SwiperSlide v-for="product in suggestionProducts" :key="product.url">
              <NuxtLink :to="`/product/${product.url}`">
                <div class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition bg-white dark:bg-gray-900">
                  <img :src="product.thumbnail" class="h-40 w-full object-cover rounded mb-4" />
                  <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ product.price }}</p>
                  <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">{{ product.short_description }}</p>
                  <button
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                      :disabled="addToCartLoading === product.sku"
                      @click.stop.prevent="addToCart(product)"
                  >
                    <span v-if="addToCartLoading !== product.sku">Add to Cart</span>
                    <span v-else>Adding...</span>
                  </button>

                </div>
              </NuxtLink>
            </SwiperSlide>
          </Swiper>
        </section>


      </aside>


      <!-- Checkout Form -->
      <section v-if="isLoggedIn" class="w-full md:w-1/3 border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-800">
        <form @submit.prevent="submitOrder" class="space-y-6">
          <!-- Billing -->
          <fieldset class="border rounded-md p-4 border-gray-300 dark:border-gray-600">
            <legend class="font-semibold">Billing Info</legend>
            <div class="grid gap-4 mt-2">
              <input v-model="checkoutForm.billing.name" type="text" placeholder="Full Name" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600" />
              <input v-model="checkoutForm.billing.email" type="email" placeholder="Email" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600" />
              <input v-model="checkoutForm.billing.mobile" type="text" placeholder="Mobile" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600" />
              <select v-model="checkoutForm.billing.address" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                <option disabled value="">Select Address</option>
                <option value="addr_1">Address 1</option>
              </select>
            </div>
          </fieldset>

          <!-- Shipping -->
          <fieldset class="border rounded-md p-4 border-gray-300 dark:border-gray-600">
            <legend class="font-semibold">Shipping Info</legend>
            <label class="inline-flex items-center space-x-2">
              <input type="checkbox" v-model="billingIsShipping" class="form-checkbox" />
              <span>Same as billing</span>
            </label>
            <div v-if="!billingIsShipping" class="grid gap-4 mt-2">
              <input v-model="checkoutForm.shipping.name" type="text" placeholder="Full Name" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600" />
              <input v-model="checkoutForm.shipping.email" type="email" placeholder="Email" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600" />
              <input v-model="checkoutForm.shipping.mobile" type="text" placeholder="Mobile" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600" />
              <select v-model="checkoutForm.shipping.address" class="input bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                <option disabled value="">Select Address</option>
                <option value="addr_1">Address 1</option>
              </select>
            </div>
          </fieldset>

          <!-- Gift & Estimate -->
          <fieldset class="border rounded-md p-4 border-gray-300 dark:border-gray-600">
            <legend class="font-semibold">Extras</legend>
            <label class="inline-flex items-center space-x-2">
              <input type="checkbox" v-model="checkoutForm.gift" class="form-checkbox" />
              <span>Gift wrapping</span>
            </label>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              Estimated delivery: <strong>{{ estimatedDelivery }}</strong>
            </p>
          </fieldset>

          <!-- Payment -->
          <fieldset class="border rounded-md p-4 border-gray-300 dark:border-gray-600">
            <legend class="font-semibold">Payment Method</legend>
            <div v-if="paymentProviders.length" class="flex flex-col gap-4 mt-2 max-h-60 overflow-y-auto">
              <label
                  v-for="provider in paymentProviders"
                  :key="provider.url"
                  class="flex items-center gap-4 border rounded-md p-2 cursor-pointer hover:shadow-md transition border-gray-300 dark:border-gray-600"
                  :class="{ 'ring-2 ring-blue-500': checkoutForm.payment === provider.url }"
              >
                <input
                    type="radio"
                    :value="provider.url"
                    v-model="checkoutForm.payment"
                    class="form-radio"
                    :checked="checkoutForm.payment === '' && provider.default === 1"
                />
                <img :src="provider.thumbnail" :alt="provider.name" class="w-12 h-12 object-contain" />
                <span class="text-sm">{{ provider.name }}</span>
              </label>
            </div>
            <div v-else class="text-sm text-gray-500 dark:text-gray-400">No payment providers available.</div>
          </fieldset>

          <button type="submit" class="btn-primary w-full">Submit Order</button>
        </form>
      </section>

      <!-- Show Register Form For Guest For Proceed to Checkout-->
      <section v-else class="w-full md:w-1/3 h-full border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-800">
        <RegisterFormComponent redirectTo="/cart" />
        <button
            disabled
            class=" w-full bg-gray-300 text-gray-500 cursor-not-allowed px-4 py-2 rounded font-semibold dark:bg-gray-700 dark:text-gray-400"
        >
          Checkout
        </button>

      </section>

    </div>
  </div>
</template>



<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig, useCookie } from '#imports'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import RegisterFormComponent from "~/components/RegisterFormComponent.vue";

const { isLoggedIn } = useSanctum()
const config = useRuntimeConfig()

const suggestionProducts = ref<any[]>([])
const cartData = ref<any>({ items: [], summary: {}, customer: {} })
const couponCode = ref('')
const billingIsShipping = ref(false)
const paymentProviders = ref<any[]>([])
const addToCartLoading = ref<string | null>(null)

const guestId = useCookie('guest_id')
const guestToken = useCookie('guest_token')
const guestTokenExpires = useCookie('guest_token_expires')

const checkoutForm = reactive({
  billing: { name: '', email: '', mobile: '', address: '' },
  shipping: { name: '', email: '', mobile: '', address: '' },
  gift: false,
  payment: '',
})

const estimatedDelivery = computed(() =>
    checkoutForm.shipping.address ? '3–5 business days' : '—'
)

// ✅ Consistent guest headers for all cart actions
const getGuestHeaders = () => {
  if (!isLoggedIn.value && guestId.value && guestToken.value) {
    return {
      'x-guest-id': guestId.value,
      'x-guest-token': guestToken.value
    }
  }
  return {}
}

watch(billingIsShipping, (val) => {
  if (val) checkoutForm.shipping = { ...checkoutForm.billing }
})
watch(() => checkoutForm.billing, (val) => {
  if (billingIsShipping.value) checkoutForm.shipping = { ...val }
}, { deep: true })

onMounted(async () => {
  await validateOrInitGuest()
  await fetchCart()
  await fetchSuggestionProducts()

  const providerRes = await useSanctumFetch(`${config.public.apiBase}/provider/payment`)
  paymentProviders.value = providerRes?.data || []

  const defaultProvider = paymentProviders.value.find(p => p.default === 1)
  if (defaultProvider) checkoutForm.payment = defaultProvider.url
})

async function validateOrInitGuest() {
  const expired = guestTokenExpires.value && (Date.now() > new Date(guestTokenExpires.value).getTime())
  const missing = !guestId.value || !guestToken.value

  if (isLoggedIn.value) return

  if (expired || missing) {
    try {
      const res = await useSanctumFetch(`${config.public.apiBase}/cart/guest-credential`, {
        method: 'POST'
      })

      const data = res?.data || {}
      guestId.value = data.guest_id
      guestToken.value = data.guest_token
      guestTokenExpires.value = data.expires_at

      console.info('[✓] Guest credentials set:', {
        guestId: guestId.value,
        expiresAt: guestTokenExpires.value
      })
    } catch (e) {
      console.error('[✘] Failed to initialize guest credentials', e)
    }
  }
}

async function fetchSuggestionProducts() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/suggestions/get`, {
      method: 'GET'
    })
    suggestionProducts.value = res?.data ?? []
  } catch (error) {
    console.error('[✘] Failed to load suggestion products', error)
  }
}

async function fetchCart() {
  const res = await useSanctumFetch(`${config.public.apiBase}/cart`, {
    method: 'GET',
    headers: getGuestHeaders()
  })
  cartData.value = res?.data || {}
}

async function addToCart(product: any) {
  if (addToCartLoading.value === product.sku) return

  try {
    addToCartLoading.value = product.sku
    const res =  await useSanctumFetch(`${config.public.apiBase}/cart/add/${product.sku}`, {
      method: 'POST',
      headers: getGuestHeaders(),
      body: { quantity: 1 }
    })
    cartData.value = res?.data || {}
  } catch (error) {
    console.error('[✘] Failed to add product to cart', error)
  } finally {
    addToCartLoading.value = null
  }
}

async function updateCartItem(sku: string, quantity: number) {
  const res = await useSanctumFetch(`${config.public.apiBase}/cart/update/${sku}`, {
    method: 'POST',
    headers: getGuestHeaders(),
    body: { quantity }
  })
  cartData.value = res?.data || {}
}

function increment(item: any) {
  if (item.quantity < item.product.max_quantity) {
    item.quantity++
    updateCartItem(item.product.sku, item.quantity)
  }
}

function decrement(item: any) {
  if (item.quantity > item.product.min_quantity) {
    item.quantity--
    updateCartItem(item.product.sku, item.quantity)
  }
}

async function removeItem(item: any) {
  const res = await useSanctumFetch(`${config.public.apiBase}/cart/remove/${item.product.sku}`, {
    method: 'DELETE',
    headers: getGuestHeaders()
  })
  cartData.value = res?.data || {}
}

async function applyCoupon() {
  const res = await useSanctumFetch(`${config.public.apiBase}/cart/coupon/${couponCode.value}`, {
    method: 'POST',
    headers: getGuestHeaders()
  })
  cartData.value = res?.data || {}
}

function submitOrder() {
  if (!checkoutForm.payment) {
    alert('Select payment method.')
    return
  }
  console.log('Submitting order with:', checkoutForm)
}
</script>
