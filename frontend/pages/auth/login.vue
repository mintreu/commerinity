<template>
  <div class="relative h-screen w-full bg-gray-100 dark:bg-gray-900 overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/bg-login.jpeg');"></div>
    <div class="absolute inset-0 bg-black bg-opacity-50 dark:bg-opacity-60 backdrop-blur-sm"></div>

    <!-- Login Card -->
    <div class="relative z-10 flex items-center justify-center h-full px-4 max-w-full">
      <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8">

        <!-- Branding -->
        <div class="flex flex-col items-center mb-6">
          <img src="/logo.png" alt="Logo" class="h-12 w-auto mb-2" />
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Welcome to MyApp</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Simple. Secure. Seamless Login</p>
        </div>


        <!-- Toggle Between Email / Mobile -->
        <div class="flex justify-center gap-6 text-sm mb-6">
          <button :class="loginMode === 'mobile' ? activeTab : inactiveTab" @click="loginMode = 'mobile'">Mobile Login</button>
          <button :class="loginMode === 'email' ? activeTab : inactiveTab" @click="loginMode = 'email'">Email Login</button>
        </div>

        <!-- Mobile Login -->
        <form v-if="loginMode === 'mobile'" @submit.prevent="handleMobileLogin" class="space-y-5">
          <div>
            <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile Number</label>
            <input
                id="mobile"
                v-model="form.mobile"
                type="tel"
                required
                placeholder="+880 1XXXXXXXXX"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <div v-if="otpSent" class="space-y-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Enter OTP</label>
            <div class="flex gap-2 justify-between">
              <input v-for="(digit, i) in 6" :key="i" v-model="otp[i]" maxlength="1" class="w-10 h-10 text-center border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>

          <button
              type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow"
          >
            {{ otpSent ? 'Verify OTP' : 'Send OTP' }}
          </button>
        </form>

        <!-- Email Login -->
        <form v-else @submit.prevent="handleEmailLogin" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input
                v-model="form.email"
                type="email"
                id="email"
                required
                placeholder="you@example.com"
                class="mt-1 w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <div class="relative mt-1">
              <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  id="password"
                  required
                  placeholder="••••••••"
                  class="w-full px-4 py-2 pr-10 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
              <!-- Toggle Icon -->
              <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-2 flex items-center text-gray-500 dark:text-gray-400 hover:text-blue-600 focus:outline-none">
                <Icon :name="showPassword ? 'heroicons-outline:eye-off' : 'heroicons-outline:eye'" class="w-5 h-5" />
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center">
              <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 dark:bg-gray-800 focus:ring-blue-500" />
              <span class="ml-2 text-gray-700 dark:text-gray-300">Remember me</span>
            </label>
            <NuxtLink to="/auth/forgot-password" class="text-blue-600 hover:underline dark:text-blue-400">Forgot?</NuxtLink>
          </div>

          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow">
            Sign In
          </button>
        </form>

        <!-- Social Login -->
        <div class="mt-6">
          <button @click="loginWithGoogle" class="w-full flex items-center justify-center gap-3 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition text-sm font-medium text-gray-700 dark:text-gray-300">
            <Icon name="logos:google-icon" class="w-5 h-5" />
            Continue with Google
          </button>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Don't have an account?
          <NuxtLink to="/auth/register" class="text-blue-600 hover:underline dark:text-blue-400">Register</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref} from 'vue'
import {useRouter} from 'vue-router'
import {useSanctum} from "#imports";
const router = useRouter()
const {login} = useSanctum();

const showPassword = ref(false)
const loginMode = ref('mobile') // or 'email'
const otpSent = ref(false)
const otp = ref(['', '', '', '', '', ''])


const form = ref({
  mobile: '',
  otp: '',
  email: '',
  password: ''
})

const handleEmailLogin = () => {
  login(form.value)
}





function handleMobileLogin() {
  if (!otpSent.value) {
    otpSent.value = true
    // simulate sending OTP
    return
  }

  const code = otp.value.join('')
  if (code.length === 6) {
    console.log('Verifying OTP:', form.value.mobile, code)
    // Handle OTP verification here
  } else {
    alert('Enter 6-digit OTP')
  }
}



function loginWithGoogle() {
  console.log('Redirecting to Google Login...')
  // Add Google login logic or redirect
}

const activeTab = 'border-b-2 border-blue-600 font-semibold text-blue-600 dark:text-blue-400 pb-1'
const inactiveTab = 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-300 pb-1'
</script>
