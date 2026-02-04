<template>
  <div class="min-h-screen w-full flex relative overflow-hidden">
    <!-- Left Side: Platform Features (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative z-10 flex-col justify-center p-12">
      <!-- Background gradient overlay -->
      <div
        class="absolute inset-0 bg-gradient-to-br from-blue-600/6 via-indigo-600/4 to-purple-600/6 backdrop-blur-sm"
      />

      <div class="relative z-10 max-w-lg">
        <!-- Brand Logo & Name -->
<!--        <NuxtLink-->
<!--          to="/"-->
<!--          class="flex items-center gap-4 mb-12"-->
<!--        >-->
<!--          <div-->
<!--            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl transform transition-transform hover:scale-105 duration-300"-->
<!--          >-->
<!--            <UIcon-->
<!--              name="i-lucide-hexagon"-->
<!--              class="w-8 h-8 text-white"-->
<!--            />-->
<!--          </div>-->
<!--          <div>-->
<!--            <h1 class="gradient-text-primary text-3xl font-bold">-->
<!--              {{ config.public.appName }}-->
<!--            </h1>-->
<!--            <p class="text-slate-600 dark:text-slate-400 font-medium">Your Shopping Destination</p>-->
<!--          </div>-->
<!--        </NuxtLink>-->

        <!-- Main Heading -->
        <h2 class="text-5xl xl:text-6xl font-bold mb-8 leading-tight">
          <span class="gradient-text-primary">
            Welcome
          </span>
          <br>
          <span class="gradient-text-accent">
            Back!
          </span>
        </h2>

        <!-- Description -->
        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 leading-relaxed">
          Sign in to access your account, track orders, and continue your shopping journey with exclusive deals.
        </p>

        <!-- Features -->
        <div class="grid grid-cols-1 gap-6 mb-12">
          <div class="feature-card">
            <div class="icon-box-primary">
              <UIcon
                name="i-lucide-shopping-bag"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">
                Track Your Orders
              </h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Real-time order tracking and delivery updates
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="icon-box-success">
              <UIcon
                name="i-lucide-heart"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">
                Saved Wishlists
              </h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Access your favorite products and personalized recommendations
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="icon-box-purple">
              <UIcon
                name="i-lucide-zap"
                class="w-6 h-6 text-white"
              />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">
                Exclusive Deals
              </h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Member-only discounts and early access to sales
              </p>
            </div>
          </div>
        </div>

        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-shield-check"
              class="w-4 h-4"
            />
            <span>100% Secure</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-clock"
              class="w-4 h-4"
            />
            <span>Quick Sign In</span>
          </div>
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-check-circle"
              class="w-4 h-4"
            />
            <span>Verified Platform</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 xl:w-2/5 relative z-10 flex items-center justify-center p-4 lg:p-8">
      <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="glass-card p-8">
          <!-- Mobile: Branding (shown only on mobile) -->
          <div class="lg:hidden text-center mb-8">
            <div class="flex items-center justify-center mb-6">
              <div
                class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl"
              >
                <UIcon
                  name="i-lucide-hexagon"
                  class="w-8 h-8 text-white"
                />
              </div>
            </div>
            <h1 class="gradient-text-primary text-3xl font-bold mb-3">
              Sign In
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Access your account and continue shopping
            </p>
          </div>

          <!-- Desktop: Simple Header -->
          <div class="hidden lg:block text-center mb-8">
            <h2 class="gradient-text-primary text-2xl font-bold mb-2">
              Sign In
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm">
              Welcome back to your account
            </p>
          </div>

          <!-- Login Form -->
          <form
            class="space-y-6"
            @submit.prevent="handleLogin"
          >
            <!-- Login Method Toggle -->
            <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1">
              <button
                type="button"
                :class="loginMethod === 'mobile' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
                @click="loginMethod = 'mobile'"
              >
                <UIcon
                  name="i-lucide-smartphone"
                  class="w-4 h-4"
                />
                <span>Mobile</span>
              </button>
              <button
                type="button"
                :class="loginMethod === 'email' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
                @click="loginMethod = 'email'"
              >
                <UIcon
                  name="i-lucide-mail"
                  class="w-4 h-4"
                />
                <span>Email</span>
              </button>
            </div>

            <!-- Mobile Login -->
            <template v-if="loginMethod === 'mobile'">
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <UIcon
                    name="i-lucide-smartphone"
                    class="w-4 h-4"
                  />
                  <span>Mobile Number</span>
                </label>
                <div class="relative">
                  <input
                    v-model="form.mobile"
                    type="tel"
                    required
                    placeholder="+91 9XXXXXXXXX"
                    class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  >
                  <UIcon
                    name="i-lucide-phone"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                </div>
              </div>

              <!-- Auth Method Toggle -->
              <div class="flex gap-2">
                <button
                  type="button"
                  :class="authMethod === 'password' ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-400'"
                  class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all"
                  @click="authMethod = 'password'"
                >
                  Password
                </button>
                <button
                  type="button"
                  :class="authMethod === 'otp' ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-400'"
                  class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all"
                  @click="authMethod = 'otp'"
                >
                  OTP
                </button>
              </div>

              <!-- Password Field -->
              <div
                v-if="authMethod === 'password'"
                class="space-y-2"
              >
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <UIcon
                    name="i-lucide-lock"
                    class="w-4 h-4"
                  />
                  <span>Password</span>
                </label>
                <div class="relative">
                  <input
                    v-model="form.password"
                    :type="showMobilePassword ? 'text' : 'password'"
                    required
                    placeholder="Enter your password"
                    class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  >
                  <UIcon
                    name="i-lucide-key"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                  <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
                    @click="showMobilePassword = !showMobilePassword"
                  >
                    <UIcon
                      :name="showMobilePassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                      class="w-5 h-5"
                    />
                  </button>
                </div>
              </div>

              <!-- OTP Section -->
              <template v-if="authMethod === 'otp'">
                <div
                  v-if="!otpSent"
                  class="space-y-2"
                >
                  <button
                    type="button"
                    :disabled="sendingOtp"
                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    @click="handleSendOtp"
                  >
                    <UIcon
                      :name="sendingOtp ? 'i-lucide-loader-2' : 'i-lucide-send'"
                      :class="{ 'animate-spin': sendingOtp }"
                      class="w-5 h-5"
                    />
                    <span>{{ sendingOtp ? 'Sending...' : 'Send OTP' }}</span>
                  </button>
                </div>

                <div
                  v-else
                  class="space-y-4"
                >
                  <div class="text-center">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                      OTP sent to your mobile
                    </p>
                  </div>

                  <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                      <UIcon
                        name="i-lucide-shield-check"
                        class="w-4 h-4"
                      />
                      <span>Enter OTP</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.otp"
                        type="text"
                        maxlength="6"
                        required
                        placeholder="123456"
                        class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      >
                      <UIcon
                        name="i-lucide-hash"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
                      />
                    </div>
                  </div>

                  <button
                    type="button"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors"
                    @click="handleResendOtp"
                  >
                    Resend OTP
                  </button>
                </div>
              </template>
            </template>

            <!-- Email Login -->
            <template v-else>
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <UIcon
                    name="i-lucide-mail"
                    class="w-4 h-4"
                  />
                  <span>Email Address</span>
                </label>
                <div class="relative">
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="you@example.com"
                    class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  >
                  <UIcon
                    name="i-lucide-at-sign"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <UIcon
                    name="i-lucide-lock"
                    class="w-4 h-4"
                  />
                  <span>Password</span>
                </label>
                <div class="relative">
                  <input
                    v-model="form.password"
                    :type="showEmailPassword ? 'text' : 'password'"
                    required
                    placeholder="Enter your password"
                    class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  >
                  <UIcon
                    name="i-lucide-key"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400"
                  />
                  <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
                    @click="showEmailPassword = !showEmailPassword"
                  >
                    <UIcon
                      :name="showEmailPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                      class="w-5 h-5"
                    />
                  </button>
                </div>
              </div>
            </template>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-sm">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-600 focus:ring-blue-500"
                >
                <span class="text-slate-700 dark:text-slate-300">Remember me</span>
              </label>
              <NuxtLink
                to="/auth/forgot-password"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors"
              >
                Forgot password?
              </NuxtLink>
            </div>

            <!-- Error Alert -->
            <div
              v-if="error"
              class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200/60 dark:border-red-800/60 rounded-xl text-red-600 dark:text-red-400 text-sm"
            >
              <UIcon
                name="i-lucide-alert-circle"
                class="w-5 h-5 flex-shrink-0"
              />
              <p>{{ error }}</p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
              <UIcon
                :name="loading ? 'i-lucide-loader-2' : 'i-lucide-log-in'"
                :class="{ 'animate-spin': loading }"
                class="w-5 h-5"
              />
              <span>{{ loading ? 'Signing In...' : 'Sign In' }}</span>
            </button>
          </form>

          <!-- Register Link -->
          <div class="mt-8 text-center">
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Don't have an account?
              <NuxtLink
                to="/auth/register"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors"
              >
                Create Account
              </NuxtLink>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'guest',
  middleware: '$guest'
})

const { login, user, refreshUser } = useSanctum()
const router = useRouter()
const config = useRuntimeConfig()

const loginMethod = ref<'mobile' | 'email'>('mobile')
const authMethod = ref<'password' | 'otp'>('password')
const otpSent = ref(false)
const sendingOtp = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const showMobilePassword = ref(false)
const showEmailPassword = ref(false)

const form = reactive({
  mobile: '',
  email: '',
  password: '',
  otp: '',
  remember: false
})

const handleSendOtp = async () => {
  if (!form.mobile) {
    error.value = 'Please enter your mobile number'
    return
  }

  sendingOtp.value = true
  error.value = null

  try {
    const response = await $fetch(`${config.public.apiBase}/api/auth/send-otp`, {
      method: 'POST',
      body: {
        type: 'mobile',
        value: form.mobile
      }
    })

    otpSent.value = true

    if (response.demo && response.otp) {
      useToast().add({
        title: 'OTP Sent',
        description: `Demo OTP: ${response.otp}`,
        color: 'green'
      })
    } else {
      useToast().add({
        title: 'OTP Sent',
        description: 'Check your mobile for the OTP',
        color: 'green'
      })
    }
  } catch (err: unknown) {
    const fetchError = err as { data?: { message?: string } }
    error.value = fetchError.data?.message || 'Failed to send OTP'
  } finally {
    sendingOtp.value = false
  }
}

const handleResendOtp = () => {
  otpSent.value = false
  form.otp = ''
  handleSendOtp()
}

const handleLogin = async () => {
  loading.value = true
  error.value = null

  try {
    interface LoginCredentials {
      mobile?: string
      email?: string
      password?: string
      otp?: string
    }

    const credentials: LoginCredentials = {}

    if (loginMethod.value === 'mobile') {
      credentials.mobile = form.mobile
      if (authMethod.value === 'password') {
        credentials.password = form.password
      } else {
        credentials.otp = form.otp
      }
    } else {
      credentials.email = form.email
      credentials.password = form.password
    }

    await login(credentials)
    await refreshUser()

    // Check for intended redirect URL (stored before auth redirect)
    const { getAndClearRedirectUrl } = useRedirectUrl()
    const intendedUrl = getAndClearRedirectUrl()

    const typedUser = user.value as any
    if (!typedUser || typedUser.onboarded !== true) {
      await router.push('/onboarding')
      return
    }
    if (intendedUrl) {
      await router.push(intendedUrl)
      return
    }
    const { getDashboardRoute } = useUserType()
    await router.push(getDashboardRoute())
  } catch (err: unknown) {
    // Handle different error response formats
    const fetchError = err as {
      data?: { message?: string }
      response?: { _data?: { message?: string } }
      message?: string
    }

    // Try to extract error message from various possible locations
    error.value = fetchError.data?.message
      || fetchError.response?._data?.message
      || fetchError.message
      || 'Login failed. Please check your credentials.'

    console.error('Login error:', err)
  } finally {
    loading.value = false
  }
}
</script>
