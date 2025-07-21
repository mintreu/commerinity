<template>
  <div class="relative h-screen w-full bg-gray-100 dark:bg-gray-900 overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-cover bg-center w-full h-full"
         style="background-image: url('/images/bg-registration.jpeg');"></div>
    <div class="absolute inset-0 bg-black bg-opacity-50 dark:bg-opacity-60 backdrop-blur-sm"></div>

    <!-- Content -->
    <div class="relative z-10 flex items-center justify-center h-full px-4 max-w-full">
      <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8">

        <!-- Branding -->
        <div class="flex flex-col items-center mb-6">
          <img src="https://placehold.co/100x40?text=MyApp&font=montserrat" alt="Logo" class="h-12 w-auto mb-2" />
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Create Your Account</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Start your journey with just your mobile number.</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-5">
          <!-- Mobile -->
          <div>
            <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile Number</label>
            <div class="flex gap-2">
              <input
                  v-model="form.mobile"
                  :disabled="otpSent"
                  type="tel"
                  id="mobile"
                  required
                  placeholder="e.g. 017xxxxxxxx"
                  class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
              <button
                  v-if="!otpSent"
                  type="button"
                  @click="sendOtp"
                  class="bg-blue-600 text-white px-3 rounded-md text-sm hover:bg-blue-700 transition"
              >
                Send OTP
              </button>
            </div>
            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">We’ll send a 6-digit verification code.</p>
          </div>

          <!-- OTP -->
          <div v-if="otpSent">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OTP Code</label>
            <div class="flex gap-2 justify-between">
              <input
                  v-for="(digit, index) in otp"
                  :key="index"
                  maxlength="1"
                  type="text"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  class="w-10 h-10 text-center rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-lg text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  v-model="otp[index]"
                  @input="focusNext(index, $event)"
              />
            </div>
            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">Enter the code sent to your mobile.</p>
          </div>

          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
            <input
                v-model="form.name"
                type="text"
                id="name"
                required
                placeholder="e.g. John Doe"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email (Optional)</label>
            <input
                v-model="form.email"
                type="email"
                id="email"
                placeholder="e.g. you@example.com"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">We may send important updates to your email.</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <input
                v-model="form.password"
                type="password"
                id="password"
                required
                placeholder="At least 6 characters"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">Use at least 6 characters.</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
            <input
                v-model="form.confirmPassword"
                type="password"
                id="confirmPassword"
                required
                placeholder="Re-enter your password"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <!-- Referral -->
          <div>
            <label for="referral" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referral Code</label>
            <input
                v-model="form.referral"
                type="text"
                id="referral"
                :readonly="isReferralLocked"
                :placeholder="isReferralLocked ? '' : 'Optional — enter referral code'"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none"
                :class="{ 'bg-gray-100 dark:bg-gray-800': isReferralLocked }"
            />
            <p v-if="!isReferralLocked" class="text-xs mt-1 text-gray-500 dark:text-gray-400">Got a code? Enter to get benefits.</p>
          </div>

          <!-- Submit -->
          <button
              type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow"
          >
            Create Account
          </button>
        </form>

        <!-- Link to login -->
        <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Already have an account?
          <NuxtLink to="/auth/login" class="text-blue-600 hover:underline dark:text-blue-400">Login</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const form = ref({
  mobile: '',
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  referral: ''
})

const otp = ref(['', '', '', '', '', ''])
const otpSent = ref(false)
const isReferralLocked = ref(false)

const route = useRoute()

onMounted(() => {
  const refCode = route.query.referral || ''
  if (refCode) {
    form.value.referral = refCode
    isReferralLocked.value = true
  }
})

function sendOtp() {
  if (!form.value.mobile) return alert('Enter your mobile number first')
  otpSent.value = true
  console.log('OTP sent to:', form.value.mobile)
}

function focusNext(index, event) {
  if (event.inputType === 'deleteContentBackward') return
  const next = event.target.nextElementSibling
  if (next) next.focus()
}

function handleRegister() {
  const otpCode = otp.value.join('')
  if (otpSent.value && otpCode.length !== 6) {
    alert('Enter the full 6-digit OTP code')
    return
  }

  if (form.value.password !== form.value.confirmPassword) {
    alert('Passwords do not match')
    return
  }

  const payload = {
    ...form.value,
    otp: otpCode
  }

  console.log('Registering user:', payload)
  // TODO: API request
}
</script>
