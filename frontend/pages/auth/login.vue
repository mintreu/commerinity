<template>
  <!-- Desktop: Side-by-side layout, Mobile: Stacked layout -->
  <div class="min-h-screen w-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-indigo-900 dark:to-purple-900 flex relative overflow-hidden">

    <!-- Floating Background Elements (Global) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-gradient-to-br from-blue-400/12 to-indigo-400/12 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-gradient-to-br from-purple-400/12 to-pink-400/12 rounded-full blur-3xl opacity-50 animate-pulse"></div>
      <div ref="orb3" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-br from-emerald-400/8 to-cyan-400/8 rounded-full blur-2xl opacity-40 animate-pulse"></div>

      <!-- E-commerce floating elements -->
      <div v-for="i in 8" :key="i" class="absolute animate-float opacity-12" :style="getShoppingIconStyle(i)">
        <Icon :name="getShoppingIcon(i)" class="w-6 h-6 text-blue-500/30 dark:text-blue-400/20" />
      </div>
    </div>

    <!-- Left Side: E-commerce Platform Features (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative z-10 flex-col justify-center p-12">
      <!-- Background gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600/6 via-indigo-600/4 to-purple-600/6 backdrop-blur-sm"></div>

      <div class="relative z-10 max-w-lg">
        <!-- Brand Logo & Name -->
        <NuxtLink to="/" class="flex items-center gap-4 mb-12">
          <!--          <div class="logo-wrapper relative">-->
          <!--            <div class="logo-glow absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl blur-lg opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>-->
          <!--            <div class="logo-bg relative w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">-->
          <!--              <img src="/logo.png" loading="lazy" class="object-contain w-8 h-8 filter brightness-0 invert" alt="Logo" />-->
          <!--            </div>-->
          <!--          </div>-->
          <img
              src="/logo.png"
              loading="lazy"
              class="object-contain w-12 h-12"
              alt="Logo"
          />
          <div>
            <h1
                class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600
             dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent"
            >
              {{ companyName }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 font-medium">
              Your Shopping Destination
            </p>
          </div>
        </NuxtLink>


        <!-- Main Heading -->
        <h2 class="text-5xl xl:text-6xl font-bold mb-8 leading-tight">
          <span class="bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            Discover
          </span>
          <br>
          <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
            Amazing Products
          </span>
        </h2>

        <!-- Platform Description -->
        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 leading-relaxed">
          Shop from millions of quality products with great prices, fast delivery, and secure payment options across India.
        </p>

        <!-- Feature Grid -->
        <div class="grid grid-cols-1 gap-6 mb-12">
          <!-- Wide Product Range -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:store-24-hour" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Millions of Products</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Electronics, Fashion, Home & Garden, Books, Beauty and much more
              </p>
            </div>
          </div>

          <!-- Fast Delivery -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:truck-fast" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Lightning Fast Delivery</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Same-day delivery in major cities, next-day delivery across India
              </p>
            </div>
          </div>

          <!-- Secure Shopping -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:shield-check" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">100% Secure & Safe</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Secure payments, buyer protection, and hassle-free returns
              </p>
            </div>
          </div>
        </div>

        <!-- Benefits Showcase -->
        <div class="bg-gradient-to-r from-blue-500/8 to-indigo-500/8 dark:from-blue-400/8 dark:to-indigo-400/8 rounded-2xl p-6 mb-8 border border-blue-200/40 dark:border-blue-800/40">
          <h4 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <Icon name="mdi:star" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            Member Exclusive Benefits
          </h4>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="flex items-center gap-2">
              <Icon name="mdi:percent" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              <span class="text-slate-600 dark:text-slate-400">Extra Discounts</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:gift" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
              <span class="text-slate-600 dark:text-slate-400">Exclusive Deals</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:crown" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              <span class="text-slate-600 dark:text-slate-400">Priority Support</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:cash-multiple" class="w-4 h-4 text-orange-600 dark:text-orange-400" />
              <span class="text-slate-600 dark:text-slate-400">Cashback Rewards</span>
            </div>
          </div>
        </div>

        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
          <div class="flex items-center gap-2">
            <Icon name="mdi:shield-star" class="w-4 h-4" />
            <span>Trusted Platform</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:truck-delivery" class="w-4 h-4" />
            <span>Pan-India Delivery</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:account-heart" class="w-4 h-4" />
            <span>50L+ Happy Customers</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:medal" class="w-4 h-4" />
            <span>Quality Assured</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Login Form (Desktop) / Full Width (Mobile) -->
    <div class="w-full lg:w-1/2 xl:w-2/5 relative z-10 flex items-center justify-center p-4 lg:p-8">
      <!-- Form Container -->
      <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60 rounded-3xl shadow-2xl p-8 transition-all duration-500 hover:shadow-3xl hover:-translate-y-1">

          <!-- Mobile: Branding Section (shown only on mobile) -->
          <div class="lg:hidden text-center mb-8">
            <div class="flex items-center justify-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl transform transition-transform hover:scale-110 duration-300">
                <Icon name="mdi:shopping" class="w-8 h-8 text-white" />
              </div>
            </div>

            <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-3">
              Welcome Back
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Sign in to continue shopping
            </p>

            <!-- Register CTA (Mobile) -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
              <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">New to {{ companyName }}?</p>
              <NuxtLink
                  to="/auth/register"
                  class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold text-sm transition-colors group"
              >
                <span>Create Account</span>
                <Icon name="mdi:arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </NuxtLink>
              <p class="text-xs text-blue-600/70 dark:text-blue-400/70 mt-1">Shop as guest or create account for better experience!</p>
            </div>
          </div>

          <!-- Desktop: Simple Header -->
          <div class="hidden lg:block text-center mb-8">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-2">
              Sign In
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm">
              Continue to your shopping experience
            </p>
          </div>

          <!-- Mode Toggle -->
          <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1 mb-8">
            <button
                @click="switchMode('mobile')"
                :class="loginMode === 'mobile' ? activeModeClass : inactiveModeClass"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
            >
              <Icon name="mdi:cellphone" class="w-4 h-4" />
              <span>Mobile</span>
            </button>
            <button
                @click="switchMode('email')"
                :class="loginMode === 'email' ? activeModeClass : inactiveModeClass"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
            >
              <Icon name="mdi:email" class="w-4 h-4" />
              <span>Email</span>
            </button>
          </div>

          <!-- Mobile Login Form -->
          <form v-if="loginMode === 'mobile'" @submit.prevent="handleMobileSubmit" class="space-y-6">

            <!-- Mobile Number Field -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon name="mdi:cellphone" class="w-4 h-4" />
                <span>Mobile Number</span>
              </label>
              <div class="relative">
                <input
                    v-model="form.mobile"
                    type="tel"
                    required
                    placeholder="+91 9XXXXXXXXX"
                    class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.mobile }"
                />
                <Icon name="mdi:phone" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
              </div>
              <div v-if="errors.mobile" class="flex items-center gap-2 text-red-500 text-sm">
                <Icon name="mdi:alert-circle" class="w-4 h-4" />
                <span>{{ errors.mobile }}</span>
              </div>
            </div>

            <!-- OTP or Password Field -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon :name="useOtp ? 'mdi:shield-key' : 'mdi:key'" class="w-4 h-4" />
                <span>{{ useOtp ? 'OTP' : 'Password' }}</span>
              </label>

              <!-- OTP Section -->
              <div v-if="useOtp" class="space-y-4">
                <!-- Send OTP Button -->
                <div v-if="!otpSent">
                  <button
                      @click="sendOtp"
                      type="button"
                      :disabled="sendingOtp"
                      class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                  >
                    <Icon :name="sendingOtp ? 'mdi:loading' : 'mdi:send'" class="w-4 h-4" :class="{ 'animate-spin': sendingOtp }" />
                    <span>{{ sendingOtp ? 'Sending OTP...' : 'Send OTP' }}</span>
                  </button>
                </div>

                <!-- OTP Input -->
                <div v-else>
                  <div v-if="!otpVerified">
                    <!-- OTP Input Grid -->
                    <div class="flex gap-3 justify-center mb-4">
                      <input
                          v-for="(_, i) in 6"
                          :key="i"
                          v-model="otp[i]"
                          :ref="el => otpRefs[i] = el"
                          maxlength="1"
                          inputmode="numeric"
                          type="text"
                          class="w-12 h-12 text-center text-lg font-bold bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                          @input="onOtpInput(i)"
                          @keydown.backspace.prevent="onOtpBackspace(i)"
                      />
                    </div>

                    <!-- Verify OTP Button -->
                    <button
                        type="button"
                        @click="verifyOtp"
                        :disabled="verifyingOtp"
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    >
                      <Icon :name="verifyingOtp ? 'mdi:loading' : 'mdi:shield-check'" class="w-4 h-4" :class="{ 'animate-spin': verifyingOtp }" />
                      <span>{{ verifyingOtp ? 'Verifying...' : 'Verify OTP' }}</span>
                    </button>

                    <!-- Resend OTP -->
                    <div class="flex items-center justify-center mt-4 text-sm">
                      <div v-if="otpCountdown > 0" class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                        <Icon name="mdi:clock" class="w-4 h-4" />
                        <span>Resend OTP in {{ otpCountdown }}s</span>
                      </div>
                      <button
                          v-else
                          @click="resendOtp"
                          type="button"
                          class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors"
                      >
                        <Icon name="mdi:refresh" class="w-4 h-4" />
                        <span>Resend OTP</span>
                      </button>
                    </div>

                    <div v-if="errors.validated_otp" class="flex items-center gap-2 text-red-500 text-sm mt-3">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.validated_otp }}</span>
                    </div>
                  </div>

                  <!-- OTP Verified Success -->
                  <div v-else class="flex items-center justify-center gap-2 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-600 dark:text-emerald-400 font-semibold">
                    <Icon name="mdi:check-circle" class="w-5 h-5" />
                    <span>OTP Verified Successfully!</span>
                  </div>
                </div>
              </div>

              <!-- Password Field -->
              <div v-else class="relative">
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="Enter your password"
                    class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.password }"
                />
                <Icon name="mdi:key" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all"
                    tabindex="-1"
                >
                  <Icon :name="showPassword ? 'mdi:eye-off' : 'mdi:eye'" class="w-4 h-4" />
                </button>
              </div>
              <div v-if="errors.password" class="flex items-center gap-2 text-red-500 text-sm">
                <Icon name="mdi:alert-circle" class="w-4 h-4" />
                <span>{{ errors.password }}</span>
              </div>
            </div>

            <!-- Options -->
            <div class="flex items-center justify-between text-sm">
              <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                <input type="checkbox" v-model="form.remember" class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 focus:ring-2" />
                <span>Remember me</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                <input type="checkbox" v-model="useOtp" class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 focus:ring-2" />
                <span>Use OTP</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="(useOtp && !otpVerified)"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 group"
            >
              <Icon name="mdi:shopping" class="w-5 h-5 group-hover:scale-110 transition-transform" />
              <span>Continue Shopping</span>
            </button>

            <div v-if="errors.auth" class="flex items-center gap-2 text-red-500 text-sm p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ errors.auth }}</span>
            </div>
          </form>

          <!-- Email Login Form -->
          <form v-else @submit.prevent="handleEmailLogin" class="space-y-6">

            <!-- Email Field -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon name="mdi:email" class="w-4 h-4" />
                <span>Email Address</span>
              </label>
              <div class="relative">
                <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="you@example.com"
                    class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
                <Icon name="mdi:email" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
              </div>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon name="mdi:key" class="w-4 h-4" />
                <span>Password</span>
              </label>
              <div class="relative">
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="Enter your password"
                    class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
                <Icon name="mdi:key" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all"
                    tabindex="-1"
                >
                  <Icon :name="showPassword ? 'mdi:eye-off' : 'mdi:eye'" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center text-sm">
              <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                <input type="checkbox" v-model="form.remember" class="w-4 h-4 text-blue-600 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 focus:ring-2" />
                <span>Remember me</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 group"
            >
              <Icon name="mdi:shopping" class="w-5 h-5 group-hover:scale-110 transition-transform" />
              <span>Continue Shopping</span>
            </button>

            <div v-if="errors.auth" class="flex items-center gap-2 text-red-500 text-sm p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ errors.auth }}</span>
            </div>
          </form>

          <!-- Divider -->
          <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400">Quick Login</span>
            </div>
          </div>

          <!-- Social Login -->
          <button
              @click="loginWithGoogle"
              class="w-full flex items-center justify-center gap-3 px-6 py-3 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg group"
          >
            <Icon name="logos:google-icon" class="w-5 h-5 group-hover:scale-110 transition-transform" />
            <span>Continue with Google</span>
          </button>

          <!-- Guest Shopping Option -->
          <div class="mt-6 p-4 bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-700/50 dark:to-slate-600/50 rounded-xl border border-slate-200/60 dark:border-slate-600/60 text-center">
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Don't want to create an account?</p>
            <NuxtLink
                to="/shop"
                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 text-slate-700 dark:text-slate-300 rounded-lg font-semibold text-sm transition-all duration-300 hover:-translate-y-0.5 group"
            >
              <Icon name="mdi:shopping-outline" class="w-4 h-4 group-hover:scale-110 transition-transform" />
              <span>Continue as Guest</span>
            </NuxtLink>
          </div>

          <!-- Footer Links -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 text-sm">
            <!-- Forgot Password -->
            <NuxtLink
                to="/auth/forgot-password"
                class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors group"
            >
              <Icon name="mdi:help-circle" class="w-4 h-4 group-hover:scale-110 transition-transform" />
              <span>Forgot password?</span>
            </NuxtLink>

            <!-- Register Link (Desktop only, mobile shown at top) -->
            <NuxtLink
                to="/auth/register"
                class="hidden lg:inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-semibold transition-colors group"
            >
              <span>Create Account</span>
              <Icon name="mdi:account-plus" class="w-4 h-4 group-hover:scale-110 transition-transform" />
            </NuxtLink>
          </div>

        </div>

        <!-- Mobile: Shopping Benefits (Mobile Only) -->
        <div class="lg:hidden mt-6 space-y-4">
          <div class="text-center p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Why Shop with {{ companyName }}?</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="text-center">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                  <Icon name="mdi:truck-fast" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <p class="font-semibold text-slate-900 dark:text-white">Fast Delivery</p>
                <p class="text-slate-600 dark:text-slate-400 text-xs">Same-day & Next-day</p>
              </div>
              <div class="text-center">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                  <Icon name="mdi:shield-check" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <p class="font-semibold text-slate-900 dark:text-white">100% Secure</p>
                <p class="text-slate-600 dark:text-slate-400 text-xs">Safe & Protected</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRuntimeConfig, useSanctumFetch, useSanctum, useRoute } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// ✅ UPDATED: Use default layout with navbar and footer disabled
// Alternative approach - layout object with name
definePageMeta({
  layout: 'default',           // Nuxt's standard layout property
  layoutConfig: {              // Our custom config property
    navbar: { show: false },
    footer: { show: false },
    bottomNav: { show: false },
    scrollTop: { show: false },
    background: { effects: false, particles: false }
  }
})


// Composables
const router = useRouter()
const route = useRoute()
const config = useRuntimeConfig()
const { login } = useSanctum()
const { isLoggedIn } = useSanctum()

// Configuration
const companyName = config.public.companyName || 'MintReu'

/* Refs */
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()

/* State */
const showPassword = ref(false)
const loginMode = ref<'mobile' | 'email'>('mobile')
const useOtp = ref(false)
const otpSent = ref(false)
const otpVerified = ref(false)
const sendingOtp = ref(false)
const verifyingOtp = ref(false)
const otpCountdown = ref(0)
const countdownInterval = ref<NodeJS.Timeout | null>(null)
const otp = ref(['', '', '', '', '', ''])
const otpRefs = ref<HTMLInputElement[]>([])
const errors = ref<Record<string, string>>({})
const form = ref({
  mobile: '',
  email: '',
  password: '',
  remember: false
})

/* Computed */
const activeModeClass = computed(() => 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-lg')
const inactiveModeClass = computed(() => 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300')

/* Methods */
function getShoppingIconStyle(index: number) {
  const delay = index * 1.5
  const duration = 8 + (index % 3) * 2

  return {
    left: `${5 + (index * 12) % 85}%`,
    top: `${10 + (index * 11) % 80}%`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function getShoppingIcon(index: number) {
  const icons = ['mdi:cart', 'mdi:gift', 'mdi:tag', 'mdi:star', 'mdi:heart', 'mdi:diamond', 'mdi:percent', 'mdi:crown']
  return icons[index % icons.length]
}

function switchMode(mode: 'mobile' | 'email') {
  loginMode.value = mode
  useOtp.value = otpSent.value = otpVerified.value = false
  otp.value = ['', '', '', '', '', '']
  form.value = { mobile: '', email: '', password: '', remember: false }
  errors.value = {}
  clearOtpCountdown()
}

function validateIndianMobile(mobile: string): string | null {
  const cleaned = mobile.trim()
  if (!/^[6-9]\d{9}$/.test(cleaned)) return 'Enter a valid 10-digit Indian mobile number'
  return null
}

function startOtpCountdown() {
  otpCountdown.value = 60
  countdownInterval.value = setInterval(() => {
    if (otpCountdown.value > 0) otpCountdown.value--
    else clearOtpCountdown()
  }, 1000)
}

function clearOtpCountdown() {
  if (countdownInterval.value) clearInterval(countdownInterval.value)
  countdownInterval.value = null
}

async function sendOtp() {
  errors.value.mobile = validateIndianMobile(form.value.mobile) || ''
  if (errors.value.mobile) return

  sendingOtp.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { type: 'mobile', value: form.value.mobile.trim() }
    })
    otpSent.value = true
    startOtpCountdown()
    nextTick(() => otpRefs.value[0]?.focus())
  } catch {
    errors.value.validated_otp = 'Failed to send OTP'
  } finally {
    sendingOtp.value = false
  }
}

function resendOtp() {
  otp.value = ['', '', '', '', '', '']
  otpVerified.value = false
  sendOtp()
}

async function verifyOtp() {
  errors.value.validated_otp = ''
  const fullOtp = otp.value.join('')
  if (fullOtp.length !== 6) {
    errors.value.validated_otp = 'Enter 6-digit OTP'
    return
  }
  verifyingOtp.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { type: 'mobile', value: form.value.mobile.trim(), otp: fullOtp }
    })
    if (res.data?.valid) {
      otpVerified.value = true
      clearOtpCountdown()
    } else {
      errors.value.validated_otp = 'Invalid OTP'
    }
  } catch {
    errors.value.validated_otp = 'OTP verification failed'
  } finally {
    verifyingOtp.value = false
  }
}

async function handleMobileSubmit() {
  errors.value = {}
  const mobileError = validateIndianMobile(form.value.mobile)
  if (mobileError) {
    errors.value.mobile = mobileError
    return
  }

  const payload: any = {
    mobile: form.value.mobile.trim(),
    remember: form.value.remember,
    validated_otp: otpVerified.value
  }
  if (!useOtp.value) payload.password = form.value.password
  try {
    await login(payload)
    redirectAfterLogin()
  } catch (e: any) {
    const err = e?.data?.errors || {}
    errors.value.auth = err.mobile?.[0] || err.password?.[0] || e?.data?.message || 'Login failed'
  }
}

async function handleEmailLogin() {
  errors.value = {}
  try {
    await login({
      email: form.value.email.trim(),
      password: form.value.password,
      remember: form.value.remember
    })
    redirectAfterLogin()
  } catch (e: any) {
    const err = e?.data?.errors || {}
    errors.value.auth = err.email?.[0] || err.password?.[0] || e?.data?.message || 'Login failed'
  }
}

function onOtpInput(i: number) {
  if (otp.value[i].length === 1 && i < 5) nextTick(() => otpRefs.value[i + 1]?.focus())
}

function onOtpBackspace(i: number) {
  if (otp.value[i] === '' && i > 0) nextTick(() => otpRefs.value[i - 1]?.focus())
}

function redirectAfterLogin() {
  let target = '/dashboard'
  if (route.query.ref) {
    try {
      target = atob(route.query.ref as string)
    } catch (e) {
      console.error("Invalid ref param:", e)
    }
  }
  router.push(target)
}

function loginWithGoogle() {
  window.location.href = `${config.public.webBase}/auth/google/redirect`
}

/* Lifecycle */
onMounted(() => {
  if (isLoggedIn.value) {
    router.push('/dashboard')
  }

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value, orb3.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})

onBeforeUnmount(clearOtpCountdown)
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.1; }
  50% { transform: translateY(-10px) rotate(5deg); opacity: 0.15; }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}
</style>
