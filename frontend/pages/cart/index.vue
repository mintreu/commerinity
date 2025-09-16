<template>
  <div class="w-full container mx-auto px-4 py-6 space-y-6 text-gray-900 dark:text-gray-100">
    <!-- Loader -->
    <GlobalLoader v-if="isLoading"/>

    <div v-else>
      <h1 class="text-2xl md:text-3xl font-semibold">My Cart</h1>

      <div class="flex flex-col md:flex-row gap-6">
        <!-- Cart Items + Summary -->
        <aside class="w-full md:w-2/3 space-y-6">
          <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-4">
            <h2 class="text-lg font-semibold">Items in My Cart</h2>
            <hr class="my-2 border-gray-300 dark:border-gray-600"/>

            <div v-if="cartData?.items?.length" class="space-y-4 h-96 overflow-y-scroll">
              <div
                  v-for="item in cartData.items"
                  :key="item.product_id"
                  class="flex items-center justify-between border-b pb-4 border-gray-200 dark:border-gray-700"
              >
                <img alt="" :src="item.product.thumbnail" class="w-20 h-20 object-cover rounded"/>
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
                        @change="updateCartItem(item.product.sku, item.quantity)"

                    />
                    <button @click="increment(item)" :disabled="item.quantity >= item.product.max_quantity">+</button>
                    <button @click="removeItem(item.product.sku)" class="text-red-500 text-sm ml-4">Remove</button>
                  </div>
                </div>
                <div class="text-right">
                  <div>{{ item.total }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.quantity }} × {{
                      item.product.price
                    }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else><p>Your cart is empty.</p></div>
          </div>

          <!-- Coupon + Summary Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl p-4 space-y-4">
            <!-- Coupon Input -->
            <div class="flex flex-col md:flex-row gap-4 items-center">
              <input
                  v-model="couponCode"
                  type="text"
                  placeholder="Enter coupon code"
                  class="input flex-grow bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 p-2"
              />
              <button
                  @click="applyCoupon"
                  class="bg-green-600 dark:bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700"
              >
                Apply
              </button>
            </div>

            <!-- Sales / Offers -->
            <div v-if="applicableSales?.length" class="space-y-2">
              <h3 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Applied Offers</h3>
              <ul class="space-y-1">
                <li
                    v-for="sale in applicableSales"
                    :key="sale.id"
                    class="flex items-center text-xs text-green-600 dark:text-green-400"
                >
                  <Icon name="mdi:check-circle" class="w-4 h-4 mr-1"/>
                  {{ sale.title }}
                </li>
              </ul>
            </div>

            <!-- Summary Breakdown -->
            <div class="space-y-2 text-sm">
              <!-- Subtotal -->
              <div class="flex justify-between">
                <span>Subtotal ({{ cartData?.summary?.quantity ?? 0 }} items)</span>
                <span>{{ cartData?.summary?.sub_total ?? '—' }}</span>
              </div>

              <!-- Tax -->
              <div class="flex justify-between">
                <span>Tax ({{ cartData?.summary?.tax_percentage ?? 0 }}%)</span>
                <span>{{ cartData?.summary?.tax ?? '—' }}</span>
              </div>

              <!-- Discount -->
              <div
                  class="flex justify-between"
                  :class="(cartData?.summary?.discount && cartData.summary.discount !== '₹0.00')
            ? 'text-green-600'
            : 'text-gray-500 dark:text-gray-400'"
              >
                <span>
                  Discount
                  <span v-if="cartData?.summary?.coupon_code">
                    (Coupon: {{ cartData?.summary?.coupon_code }})
                  </span>
                </span>
                              <span>
                  -{{ cartData?.summary?.discount ?? '₹0.00' }}
                </span>
              </div>


              <!-- Total -->
              <div class="flex justify-between font-bold text-lg border-t border-gray-300 dark:border-gray-700 pt-2">
                <span>Total Payable</span>
                <span>{{ cartData?.summary?.total ?? '—' }}</span>
              </div>
            </div>

            <!-- Total in Words -->
            <div v-if="cartData?.summary?.total" class="text-xs text-gray-500 dark:text-gray-400 italic">
              ({{ totalInWords(cartData.summary.total) }})
            </div>
          </div>


          <!-- Suggestion Products -->
          <section v-if="suggestionProducts.length">
            <h2 class="text-2xl font-bold mb-6">Users Also Buy</h2>
            <Swiper
                :slides-per-view="1"
                :space-between="20"
                :breakpoints="{ 640: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } }"
                class="my-6"
            >
              <SwiperSlide v-for="product in suggestionProducts" :key="product.sku">
                <div
                    class="border border-gray-200 dark:border-gray-700 rounded p-4 hover:shadow-lg transition bg-white dark:bg-gray-900 flex flex-col h-full"
                >
                  <NuxtLink :to="`/product/${product.url}`" class="flex-1 block">
                    <img :src="product.thumbnail" alt="" class="h-40 w-full object-cover rounded mb-4"/>
                    <h3 class="font-semibold mb-1">{{ product.name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ product.price }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">{{ product.short_description }}</p>
                  </NuxtLink>
                  <div class="mt-auto pt-2">
                    <AddToCartButton :sku="product.sku" :quantity="1"/>
                  </div>
                </div>
              </SwiperSlide>
            </Swiper>
          </section>
        </aside>

        <!-- Checkout Form -->
        <section
            class="w-full md:w-1/3 border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-800">
          <form @submit.prevent="submitOrder" class="space-y-6">
            <!-- Guest Info (if not logged in) -->
            <div v-if="!isLoggedIn">
              <GuestCartForm v-model="guestForm"/>
            </div>

            <!-- Billing -->
            <!-- Billing Info -->
            <fieldset v-if="isLoggedIn"
                      class="border rounded-md p-4 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
              <legend class="font-semibold text-gray-700 dark:text-gray-200">Billing Info</legend>

              <div v-if="sortedUserAddresses.length" class="grid gap-4 mt-2">
                <input
                    v-model="checkoutForm.billing.name"
                    type="text"
                    placeholder="Full Name"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                />
                <input
                    v-model="checkoutForm.billing.email"
                    type="email"
                    placeholder="Email"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                />
                <input
                    v-model="checkoutForm.billing.mobile"
                    type="text"
                    placeholder="Mobile"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                />
                <select
                    v-model="checkoutForm.billing.address"
                    @change="onAddressSelect('billing', checkoutForm.billing.address)"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                >
                  <option disabled value="">Select Address</option>
                  <option v-for="addr in sortedUserAddresses" :key="addr.uuid" :value="addr.uuid">
                    {{ addr.title }} - {{ addr.city }}, {{ addr.state.name }}
                  </option>
                </select>
              </div>

              <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                <span class="grow">No billing addresses found.</span>
                <NuxtLink
                    to="/dashboard/account/address"
                    class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400 font-medium hover:underline hover:text-indigo-500 dark:hover:text-indigo-300 transition"
                >
                  <Icon name="mdi:plus-circle-outline" class="w-4 h-4"/>
                  Add Address
                </NuxtLink>
              </div>
            </fieldset>

            <!-- Shipping Info -->
            <fieldset v-if="isLoggedIn"
                      class="border rounded-md p-4 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 mt-4">
              <legend class="font-semibold text-gray-700 dark:text-gray-200">Shipping Info</legend>

              <label class="inline-flex items-center space-x-2 text-gray-700 dark:text-gray-200">
                <input type="checkbox" v-model="billingIsShipping"
                       class="form-checkbox text-indigo-600 dark:text-indigo-400 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded"/>
                <span>Same as billing</span>
              </label>

              <div v-if="!billingIsShipping && sortedUserAddresses.length" class="grid gap-4 mt-2">
                <input
                    v-model="checkoutForm.shipping.name"
                    type="text"
                    placeholder="Full Name"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                />
                <input
                    v-model="checkoutForm.shipping.email"
                    type="email"
                    placeholder="Email"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                />
                <input
                    v-model="checkoutForm.shipping.mobile"
                    type="text"
                    placeholder="Mobile"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                />
                <select
                    v-model="checkoutForm.shipping.address"
                    @change="onAddressSelect('shipping', checkoutForm.shipping.address)"
                    class="input bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                >
                  <option disabled value="">Select Address</option>
                  <option v-for="addr in sortedUserAddresses" :key="addr.uuid" :value="addr.uuid">
                    {{ addr.title }} - {{ addr.city }}, {{ addr.state.name }}
                  </option>
                </select>
              </div>
            </fieldset>


            <!-- Gift & Estimate -->
            <fieldset class="border rounded-md p-4 border-gray-300 dark:border-gray-600">
              <legend class="font-semibold">Extras</legend>
              <label class="inline-flex items-center space-x-2">
                <input type="checkbox" v-model="checkoutForm.gift" class="form-checkbox"/>
                <span>Gift wrapping</span>
              </label>
              <p class="mt-2 text-sm">Estimated delivery: <strong>{{ estimatedDelivery }}</strong></p>
            </fieldset>

            <!-- Payment -->
            <fieldset class="border rounded-md p-4 border-gray-300 dark:border-gray-600">
              <legend class="font-semibold">Payment Method</legend>
              <div v-if="paymentProviders.length" class="flex flex-col gap-4 mt-2 max-h-60 overflow-y-auto">
                <label
                    v-for="provider in paymentProviders"
                    :key="provider.url"
                    class="flex items-center gap-4 border rounded-md p-2 cursor-pointer hover:shadow-md transition"
                    :class="{ 'ring-2 ring-blue-500': checkoutForm.payment === provider.url }"
                >
                  <input type="radio" :value="provider.url" v-model="checkoutForm.payment" class="form-radio"/>
                  <img :src="provider.thumbnail" :alt="provider.name" class="w-12 h-12 object-contain"/>
                  <span class="text-sm">{{ provider.name }}</span>
                </label>
              </div>
              <div v-else class="text-sm">No payment providers available.</div>
            </fieldset>

            <!-- ONE BUTTON for both guest & logged in -->
            <button type="submit" class="btn-primary w-full">Place Order</button>
          </form>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, reactive, computed, onMounted, watch} from 'vue'
import {useSanctum, useSanctumFetch, useRuntimeConfig, useToast} from '#imports'
import {Swiper, SwiperSlide} from 'swiper/vue'
import 'swiper/css'
import {useCart} from '~/composables/useCart'
import AddToCartButton from '~/components/cart/AddToCartButton.vue'
import GlobalLoader from '~/components/GlobalLoader.vue'
import GuestCartForm from '~/components/cart/GuestCartForm.vue'

const {isLoggedIn} = useSanctum()
const config = useRuntimeConfig()
const isLoading = useState('pageLoading', () => false)
const toast = useToast()

const {cartData, updateCartItem, removeItem, applyCoupon, fetchCart} = useCart()

const couponCode = ref('')
const billingIsShipping = ref(false)
const paymentProviders = ref<any[]>([])
const suggestionProducts = ref<any[]>([])

const checkoutForm = reactive({
  billing: {name: '', email: '', mobile: '', address: ''},
  shipping: {name: '', email: '', mobile: '', address: ''},
  gift: false,
  payment: '',
})

const guestForm = ref<Record<string, any>>({})

const estimatedDelivery = computed(() => (checkoutForm.shipping.address ? '3–5 business days' : '—'))

// --- Fetching data ---
onMounted(async () => {
  try {
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

async function fetchSuggestionProducts() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/products/suggestions/get`)
    suggestionProducts.value = res?.data ?? []
  } catch (e) {
    console.error('Failed to load suggestion products', e)
  }
}

const userAddresses = ref<any[]>([])
const sortedUserAddresses = computed(() => [...(userAddresses.value || [])].sort((a, b) => a.priority - b.priority))

async function fetchUserAddress() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/addresses`)
    userAddresses.value = res?.data || []
    const defaultHome = userAddresses.value.find(a => a.type?.toLowerCase() === 'home' && a.default)
    if (defaultHome) {
      checkoutForm.billing.address = defaultHome.uuid
      onAddressSelect('billing', defaultHome.uuid)
    }
  } catch (e) {
    toast.error({title: 'Error', message: '❌ Failed fetching addresses', timeout: 3000, position: 'topRight'})
  }
}

// function onAddressSelect(type: 'billing' | 'shipping', uuid: string) {
//   const addr = userAddresses.value.find(a => a.uuid === uuid)
//   if (!addr) return
//   const userInfo = cartData.value?.customer || {}
//   checkoutForm[type].name = userInfo.name || ''
//   checkoutForm[type].email = userInfo.email || ''
//   checkoutForm[type].mobile = userInfo.mobile || ''
//   checkoutForm[type].address = addr.uuid
// }


async function onAddressSelect(type: 'billing' | 'shipping', uuid: string) {
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
    toast.error({title: 'Error', message: '❌ Failed fetching address details', timeout: 3000})
  }
}


// Sync shipping = billing if checkbox checked
watch(billingIsShipping, val => {
  if (val) checkoutForm.shipping = {...checkoutForm.billing}
})
watch(
    () => checkoutForm.billing,
    val => {
      if (billingIsShipping.value) checkoutForm.shipping = {...val}
    },
    {deep: true}
)

// --- Submit order ---
async function submitOrder() {
  if (!checkoutForm.payment) {
    alert('Select payment method.')
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
      alert('Please fill all required guest fields')
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
      window.location.href = res.data.checkout_url
    } else {
      alert(res?.data?.message || 'Order submission failed')
    }
  } catch (e) {
    console.error('Submit order failed', e)
    alert('Something went wrong. Please try again.')
  }
}


// --- Increment & Decrement helpers ---
function increment(item: any) {
  if (item.quantity < item.product.max_quantity) {
    const newQty = item.quantity + 1
    item.quantity = newQty
    updateCartItem(item.product.sku, newQty)
  }
}

function decrement(item: any) {
  if (item.quantity > item.product.min_quantity) {
    const newQty = item.quantity - 1
    item.quantity = newQty
    updateCartItem(item.product.sku, newQty)
  }
}

// Example: convert "₹225.00" → "Two Hundred Twenty-Five Rupees Only"
function totalInWords(amount: string) {
  try {
    // Remove currency symbol and commas
    const num = parseFloat(amount.replace(/[₹,]/g, ''))
    if (isNaN(num)) return ''
    return numToWords(num) + ' only'
  } catch {
    return ''
  }
}

// Simple number to words (basic)
function numToWords(num: number): string {
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


// Example placeholder for sales list
const applicableSales = ref<any[]>([
  {id: 1, title: 'Festive Discount - 10% off'},
  {id: 2, title: 'Free Delivery on orders above ₹200'}
])


</script>
