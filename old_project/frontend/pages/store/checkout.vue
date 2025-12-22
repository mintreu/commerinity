<template>
  <div class="min-h-screen w-full px-6 py-10 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
    <div class="container mx-auto space-y-10">

      <h1 class="text-3xl font-bold text-center">Your Cart & Checkout</h1>

      <!-- CART LIST -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Cart Items</h2>
        <div v-if="cart.length" class="space-y-6">
          <div v-for="item in cart" :key="item.id" class="flex flex-col md:flex-row justify-between gap-4 items-center border-b pb-4">
            <div class="flex-1">
              <h3 class="font-semibold text-lg">{{ item.name }}</h3>
              <p class="text-gray-600 dark:text-gray-400 text-sm">Unit Price: ${{ item.price }}</p>
              <p class="text-gray-600 dark:text-gray-400 text-sm">Total: ${{ item.price * item.quantity }}</p>
            </div>
            <div class="flex items-center gap-2">
              <button @click="decreaseQty(item)" class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">-</button>
              <span>{{ item.quantity }}</span>
              <button @click="increaseQty(item)" class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">+</button>
              <button @click="removeItem(item.id)" class="ml-4 text-red-500">🗑️</button>
            </div>
          </div>
        </div>
        <div v-else class="text-gray-500 dark:text-gray-400">Your cart is empty.</div>
      </div>

      <!-- CHECKOUT FORM -->
      <div class="flex flex-col md:flex-row   lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 w-full md:w-1/2 space-y-6">

          <!-- SHIPPING -->
          <details open class="p-6 rounded shadow bg-white dark:bg-gray-800 group">
            <summary class="flex justify-between items-center text-lg font-semibold cursor-pointer">
              Shipping Address
            </summary>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <input v-model="form.name" placeholder="Full Name" class="input" />
              <input v-model="form.phone" placeholder="Phone Number" class="input" />
              <input v-model="form.city" placeholder="City" class="input" />
              <input v-model="form.zip" placeholder="ZIP Code" class="input" />
              <input v-model="form.address" placeholder="Street Address" class="input md:col-span-2" />
            </div>
          </details>

          <!-- BILLING -->
          <details class="p-6 rounded shadow bg-white dark:bg-gray-800 group">
            <summary class="flex justify-between items-center text-lg font-semibold cursor-pointer">
              Billing Address
            </summary>
            <div class="mt-4 space-y-2">
              <label class="flex gap-2 items-center">
                <input type="checkbox" v-model="sameAsShipping" />
                <span>Same as shipping</span>
              </label>
              <div v-if="!sameAsShipping" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input v-model="form.billingName" placeholder="Full Name" class="input" />
                <input v-model="form.billingPhone" placeholder="Phone Number" class="input" />
                <input v-model="form.billingCity" placeholder="City" class="input" />
                <input v-model="form.billingZip" placeholder="ZIP Code" class="input" />
                <input v-model="form.billingAddress" placeholder="Street Address" class="input md:col-span-2" />
              </div>
            </div>
          </details>

          <!-- PAYMENT -->
          <details class="p-6 rounded shadow bg-white dark:bg-gray-800 group">
            <summary class="flex justify-between items-center text-lg font-semibold cursor-pointer">
              Payment Method
            </summary>
            <div class="mt-4 space-y-3">
              <label class="flex items-center gap-2">
                <input type="radio" v-model="form.payment" value="wallet" class="radio" />
                <span>Wallet</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" v-model="form.payment" value="card" class="radio" />
                <span>Card</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" v-model="form.payment" value="cod" class="radio" />
                <span>Cash on Delivery</span>
              </label>
            </div>
          </details>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input v-model="form.promo" placeholder="Promo Code" class="input" />
            <textarea v-model="form.note" placeholder="Order Notes" rows="3" class="input resize-none md:col-span-2"></textarea>
          </div>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="bg-white w-full md:w-1/2 dark:bg-gray-800 p-6 rounded shadow space-y-5 h-fit sticky top-6">
          <h2 class="text-xl font-semibold">Order Summary</h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span>Subtotal</span>
              <span>${{ subTotal }}</span>
            </div>
            <div class="flex justify-between text-green-500">
              <span>Savings</span>
              <span>-${{ discount }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tax</span>
              <span>${{ tax }}</span>
            </div>
            <div class="flex justify-between">
              <span>Total</span>
              <span class="font-semibold text-lg">${{ total }}</span>
            </div>
          </div>

          <button
              @click="placeOrder"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded transition font-medium"
          >
            Place Order
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const sameAsShipping = ref(true)

const form = ref({
  name: '',
  phone: '',
  city: '',
  zip: '',
  address: '',
  billingName: '',
  billingPhone: '',
  billingCity: '',
  billingZip: '',
  billingAddress: '',
  promo: '',
  note: '',
  payment: 'wallet'
})

const cart = ref([])

const fetchCart = () => {
  cart.value = [
    { id: 1, name: 'Laravel Ebook', quantity: 2, price: 20 },
    { id: 2, name: 'NuxtJS T-shirt', quantity: 1, price: 35 },
  ]
}

const subTotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
)

const discount = computed(() => Math.floor(subTotal.value * 0.1))
const tax = computed(() => Math.floor((subTotal.value - discount.value) * 0.05))
const total = computed(() => subTotal.value - discount.value + tax.value)

const increaseQty = (item) => {
  item.quantity += 1
}
const decreaseQty = (item) => {
  if (item.quantity > 1) item.quantity -= 1
}
const removeItem = (id) => {
  cart.value = cart.value.filter(i => i.id !== id)
}

const placeOrder = () => {
  if (!form.value.name || !form.value.address || !form.value.phone) {
    alert('Please complete required fields.')
    return
  }

  console.log('Order Placed:', {
    form: form.value,
    cart: cart.value,
    total: total.value
  })
  alert('Order placed successfully!')
}

onMounted(fetchCart)
</script>

<style scoped>
.input {
  @apply w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white;
}
.radio {
  @apply accent-blue-600;
}
</style>
