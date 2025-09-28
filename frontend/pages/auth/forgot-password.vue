<template>
  <!-- Desktop: Side-by-side layout, Mobile: Stacked layout -->
  <div class="min-h-screen w-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-indigo-900 dark:to-purple-900 flex relative overflow-hidden">

    <!-- Floating Background Elements (Global) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-gradient-to-br from-blue-400/12 to-indigo-400/12 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-gradient-to-br from-purple-400/12 to-pink-400/12 rounded-full blur-3xl opacity-50 animate-pulse"></div>
      <div ref="orb3" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-br from-emerald-400/8 to-cyan-400/8 rounded-full blur-2xl opacity-40 animate-pulse"></div>

      <!-- Security floating elements -->
      <div v-for="i in 8" :key="i" class="absolute animate-float opacity-12" :style="getSecurityIconStyle(i)">
        <Icon :name="getSecurityIcon(i)" class="w-6 h-6 text-blue-500/30 dark:text-blue-400/20" />
      </div>
    </div>

    <!-- Left Side: Password Recovery Help (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative z-10 flex-col justify-center p-12">
      <!-- Background gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600/6 via-indigo-600/4 to-purple-600/6 backdrop-blur-sm"></div>

      <div class="relative z-10 max-w-lg">
        <!-- Brand Logo & Name -->
<!--        <div class="flex items-center gap-4 mb-12">-->
<!--          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl transform transition-transform hover:scale-105 duration-300">-->
<!--            <Icon name="mdi:shield-key" class="w-8 h-8 text-white" />-->
<!--          </div>-->
<!--          <div>-->
<!--            <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">-->
<!--              {{ companyName }}-->
<!--            </h1>-->
<!--            <p class="text-slate-600 dark:text-slate-400 font-medium">Account Recovery</p>-->
<!--          </div>-->
<!--        </div>-->

        <!-- Main Heading -->
        <h2 class="text-5xl xl:text-6xl font-bold mb-8 leading-tight">
          <span class="bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            Don't Worry,
          </span>
          <br>
          <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
            We'll Help You
          </span>
        </h2>

        <!-- Platform Description -->
        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 leading-relaxed">
          Forgot your password? No problem! We'll help you reset it quickly and securely so you can get back to shopping.
        </p>

        <!-- Recovery Process Steps -->
        <div class="grid grid-cols-1 gap-6 mb-12">
          <!-- Step 1 -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:numeric-1" class="w-6 h-6 text-white font-bold" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Enter Your Contact</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Provide your registered mobile number or email address
              </p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:numeric-2" class="w-6 h-6 text-white font-bold" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Verify Your Identity</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Enter the 6-digit verification code sent to your contact
              </p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:numeric-3" class="w-6 h-6 text-white font-bold" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Create New Password</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Set a strong new password to secure your account
              </p>
            </div>
          </div>
        </div>

        <!-- Security Assurance -->
        <div class="bg-gradient-to-r from-blue-500/8 to-indigo-500/8 dark:from-blue-400/8 dark:to-indigo-400/8 rounded-2xl p-6 mb-8 border border-blue-200/40 dark:border-blue-800/40">
          <h4 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <Icon name="mdi:shield-check" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            Your Security Matters
          </h4>
          <div class="grid grid-cols-1 gap-3 text-sm">
            <div class="flex items-center gap-2">
              <Icon name="mdi:lock" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
              <span class="text-slate-600 dark:text-slate-400">End-to-end encrypted process</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:timer-sand" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
              <span class="text-slate-600 dark:text-slate-400">OTP expires in 10 minutes</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:eye-off" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
              <span class="text-slate-600 dark:text-slate-400">Your data stays private</span>
            </div>
          </div>
        </div>

        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
          <div class="flex items-center gap-2">
            <Icon name="mdi:shield-star" class="w-4 h-4" />
            <span>Bank-Grade Security</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:clock-fast" class="w-4 h-4" />
            <span>Instant Recovery</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:account-heart" class="w-4 h-4" />
            <span>24/7 Support</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Recovery Form (Desktop) / Full Width (Mobile) -->
    <div class="w-full lg:w-1/2 xl:w-2/5 relative z-10 flex items-center justify-center p-4 lg:p-8">
      <!-- Form Container -->
      <div class="w-full max-w-md">
        <!-- Recovery Card -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60 rounded-3xl shadow-2xl p-8 transition-all duration-500 hover:shadow-3xl hover:-translate-y-1">

          <!-- Mobile: Branding Section (shown only on mobile) -->
          <div class="lg:hidden text-center mb-8">
            <div class="flex items-center justify-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl transform transition-transform hover:scale-110 duration-300">
                <Icon name="mdi:shield-key" class="w-8 h-8 text-white" />
              </div>
            </div>

            <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-3">
              Password Recovery
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Let's get you back into your account
            </p>
          </div>

          <!-- Desktop: Simple Header -->
          <div class="hidden lg:block text-center mb-8">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-2">
              Reset Password
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm">
              {{ getStepDescription() }}
            </p>
          </div>

          <!-- Step Indicator -->
          <div class="flex items-center justify-center gap-4 mb-8">
            <div v-for="stepNum in 3" :key="stepNum" class="flex items-center">
              <div
                  class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                  :class="step >= stepNum ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg' : 'bg-slate-200 dark:bg-slate-600 text-slate-400'"
              >
                <Icon v-if="step > stepNum" name="mdi:check" class="w-4 h-4" />
                <span v-else>{{ stepNum }}</span>
              </div>
              <div v-if="stepNum < 3" class="w-12 h-1 mx-2 rounded-full transition-all duration-300"
                   :class="step > stepNum ? 'bg-gradient-to-r from-blue-600 to-indigo-600' : 'bg-slate-200 dark:bg-slate-600'"></div>
            </div>
          </div>

          <!-- Contact Type Toggle (Step 1 Only) -->
          <div v-if="step === 1" class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1 mb-8">
            <button
                @click="switchMode('mobile')"
                :class="contactType === 'mobile' ? activeModeClass : inactiveModeClass"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
            >
              <Icon name="mdi:cellphone" class="w-4 h-4" />
              <span>Mobile</span>
            </button>
            <button
                @click="switchMode('email')"
                :class="contactType === 'email' ? activeModeClass : inactiveModeClass"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
            >
              <Icon name="mdi:email" class="w-4 h-4" />
              <span>Email</span>
            </button>
          </div>

          <!-- Step 1: Contact Input -->
          <form v-if="step === 1" @submit.prevent="sendOtp" class="space-y-6">
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon :name="contactType === 'mobile' ? 'mdi:cellphone' : 'mdi:email'" class="w-4 h-4" />
                <span>{{ contactType === 'mobile' ? 'Mobile Number' : 'Email Address' }}</span>
              </label>
              <div class="relative">
                <input
                    v-model="form[contactType]"
                    :type="contactType === 'mobile' ? 'tel' : 'email'"
                    required
                    :placeholder="contactType === 'mobile' ? '+91 9XXXXXXXXX' : 'you@example.com'"
                    class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.contact }"
                />
                <Icon :name="contactType === 'mobile' ? 'mdi:phone' : 'mdi:email'" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
              </div>
              <div v-if="errors.contact" class="flex items-center gap-2 text-red-500 text-sm">
                <Icon name="mdi:alert-circle" class="w-4 h-4" />
                <span>{{ errors.contact }}</span>
              </div>
            </div>

            <button
                type="submit"
                :disabled="isLoading"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 group"
            >
              <Icon :name="isLoading ? 'mdi:loading' : 'mdi:send'" class="w-5 h-5" :class="{ 'animate-spin': isLoading }" />
              <span>{{ isLoading ? 'Sending OTP...' : 'Send Recovery Code' }}</span>
            </button>
          </form>

          <!-- Step 2: OTP Verification -->
          <form v-else-if="step === 2" @submit.prevent="verifyOtp" class="space-y-6">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <Icon name="mdi:message-text" class="w-8 h-8 text-white" />
              </div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Verification Code Sent</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Enter the 6-digit code sent to <br>
                <span class="font-semibold">{{ maskContact() }}</span>
              </p>
            </div>

            <!-- OTP Input Grid -->
            <div class="flex gap-3 justify-center mb-6">
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

            <button
                type="submit"
                :disabled="isLoading || otp.join('').length < 6"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 group"
            >
              <Icon :name="isLoading ? 'mdi:loading' : 'mdi:shield-check'" class="w-5 h-5" :class="{ 'animate-spin': isLoading }" />
              <span>{{ isLoading ? 'Verifying...' : 'Verify Code' }}</span>
            </button>

            <!-- Resend OTP -->
            <div class="flex items-center justify-center text-sm">
              <div v-if="otpCountdown > 0" class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <Icon name="mdi:clock" class="w-4 h-4" />
                <span>Resend code in {{ otpCountdown }}s</span>
              </div>
              <button
                  v-else
                  @click="resendOtp"
                  type="button"
                  class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors"
              >
                <Icon name="mdi:refresh" class="w-4 h-4" />
                <span>Resend Code</span>
              </button>
            </div>

            <div v-if="errors.otp" class="flex items-center gap-2 text-red-500 text-sm p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ errors.otp }}</span>
            </div>
          </form>

          <!-- Step 3: New Password -->
          <form v-else-if="step === 3" @submit.prevent="resetPassword" class="space-y-6">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <Icon name="mdi:lock-reset" class="w-8 h-8 text-white" />
              </div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Create New Password</h3>
              <p class="text-sm text-slate-600 dark:text-slate-400">
                Choose a strong password to secure your account
              </p>
            </div>

            <!-- New Password -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon name="mdi:key" class="w-4 h-4" />
                <span>New Password</span>
              </label>
              <div class="relative">
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    minlength="6"
                    placeholder="Enter new password"
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

            <!-- Confirm Password -->
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <Icon name="mdi:key-check" class="w-4 h-4" />
                <span>Confirm Password</span>
              </label>
              <div class="relative">
                <input
                    v-model="form.confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    minlength="6"
                    placeholder="Confirm new password"
                    class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.confirmPassword }"
                />
                <Icon name="mdi:key-check" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all"
                    tabindex="-1"
                >
                  <Icon :name="showConfirmPassword ? 'mdi:eye-off' : 'mdi:eye'" class="w-4 h-4" />
                </button>
              </div>
              <div v-if="errors.confirmPassword" class="flex items-center gap-2 text-red-500 text-sm">
                <Icon name="mdi:alert-circle" class="w-4 h-4" />
                <span>{{ errors.confirmPassword }}</span>
              </div>
            </div>

            <!-- Password Strength Indicator -->
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Password Strength</span>
                <span class="text-xs px-2 py-1 rounded-lg font-semibold" :class="getPasswordStrengthClass()">{{ getPasswordStrength() }}</span>
              </div>
              <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2 overflow-hidden">
                <div class="h-full transition-all duration-500" :class="getPasswordStrengthBarClass()" :style="{ width: getPasswordStrengthWidth() }"></div>
              </div>
            </div>

            <button
                type="submit"
                :disabled="isLoading || !isPasswordValid()"
                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 group"
            >
              <Icon :name="isLoading ? 'mdi:loading' : 'mdi:check-bold'" class="w-5 h-5" :class="{ 'animate-spin': isLoading }" />
              <span>{{ isLoading ? 'Updating Password...' : 'Update Password' }}</span>
            </button>

            <div v-if="errors.reset" class="flex items-center gap-2 text-red-500 text-sm p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
              <Icon name="mdi:alert-circle" class="w-4 h-4" />
              <span>{{ errors.reset }}</span>
            </div>
          </form>

          <!-- Back to Login Link -->
          <div class="mt-8 text-center">
            <NuxtLink
                to="/auth/login"
                class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold text-sm transition-colors group"
            >
              <Icon name="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
              <span>Back to Login</span>
            </NuxtLink>
          </div>

        </div>

        <!-- Mobile: Recovery Steps (Mobile Only) -->
        <div class="lg:hidden mt-6 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50">
          <h3 class="font-bold text-slate-900 dark:text-white mb-4 text-center">Recovery Process</h3>
          <div class="space-y-3 text-sm">
            <div class="flex items-center gap-3">
              <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                <Icon name="mdi:numeric-1" class="w-3 h-3 text-blue-600 dark:text-blue-400" />
              </div>
              <span class="text-slate-600 dark:text-slate-400">Enter your contact information</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-6 h-6 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                <Icon name="mdi:numeric-2" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
              </div>
              <span class="text-slate-600 dark:text-slate-400">Verify with OTP code</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-6 h-6 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                <Icon name="mdi:numeric-3" class="w-3 h-3 text-purple-600 dark:text-purple-400" />
              </div>
              <span class="text-slate-600 dark:text-slate-400">Set your new password</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <Teleport to="body">
      <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeSuccessModal">
        <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
          <!-- Success Header -->
          <div class="flex items-center gap-6 p-8 border-b border-green-200/50 dark:border-green-800/50">
            <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-3xl flex items-center justify-center shadow-2xl">
              <Icon name="mdi:check-circle" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">Password Reset Complete!</h3>
              <p class="text-slate-500 dark:text-slate-400 mt-1">You can now sign in</p>
            </div>
          </div>

          <!-- Success Body -->
          <div class="p-8">
            <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
              Your password has been successfully updated. You can now sign in to your account with your new password.
            </p>

            <button
                @click="redirectToLogin"
                class="w-full px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
              Continue to Login
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useRouter, useRuntimeConfig, useSanctumFetch } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Meta
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
const config = useRuntimeConfig()
const { $toast } = useNuxtApp()

// Configuration
const companyName = config.public.companyName || 'MintReu'

/* Refs */
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()

/* State */
const contactType = ref<'mobile' | 'email'>('mobile')
const step = ref(1)
const isLoading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const showSuccessModal = ref(false)
const otpCountdown = ref(0)
const countdownInterval = ref<NodeJS.Timeout | null>(null)

const otp = ref(['', '', '', '', '', ''])
const otpRefs = ref<HTMLInputElement[]>([])

const form = ref({
  mobile: '',
  email: '',
  password: '',
  confirmPassword: ''
})

const errors = ref<Record<string, string>>({})

/* Computed */
const activeModeClass = computed(() => 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-lg')
const inactiveModeClass = computed(() => 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300')

/* Methods */
function getSecurityIconStyle(index: number) {
  const delay = index * 1.5
  const duration = 8 + (index % 3) * 2

  return {
    left: `${5 + (index * 12) % 85}%`,
    top: `${10 + (index * 11) % 80}%`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function getSecurityIcon(index: number) {
  const icons = ['mdi:shield-check', 'mdi:key', 'mdi:lock', 'mdi:account-check', 'mdi:shield-key', 'mdi:security', 'mdi:verified', 'mdi:lock-check']
  return icons[index % icons.length]
}

function getStepDescription() {
  switch (step.value) {
    case 1: return 'Enter your registered contact information'
    case 2: return 'Verify your identity with the code sent'
    case 3: return 'Create a strong new password'
    default: return 'Follow the steps to recover your account'
  }
}

function switchMode(mode: 'mobile' | 'email') {
  contactType.value = mode
  clearForm()
}

function clearForm() {
  otp.value = ['', '', '', '', '', '']
  form.value = { mobile: '', email: '', password: '', confirmPassword: '' }
  errors.value = {}
  clearOtpCountdown()
}

function maskContact() {
  const contact = form.value[contactType.value]
  if (contactType.value === 'mobile') {
    return contact.replace(/(\d{2})\d{6}(\d{2})/, '$1******$2')
  } else {
    return contact.replace(/(.{2}).*(@.*)/, '$1***$2')
  }
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

function validateContact(contact: string): string | null {
  if (contactType.value === 'mobile') {
    const cleaned = contact.trim()
    if (!/^[6-9]\d{9}$/.test(cleaned)) return 'Enter a valid 10-digit Indian mobile number'
  } else {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact)) return 'Enter a valid email address'
  }
  return null
}

async function sendOtp() {
  const contact = form.value[contactType.value].trim()
  const contactError = validateContact(contact)

  if (contactError) {
    errors.value.contact = contactError
    return
  }

  isLoading.value = true
  errors.value = {}

  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: {
        type: contactType.value,
        value: contact,
        purpose: 'password_reset'
      }
    })

    step.value = 2
    startOtpCountdown()

    if ($toast && $toast.success) {
      $toast.success('Recovery code sent', `Check your ${contactType.value} for the verification code`)
    }

    nextTick(() => otpRefs.value[0]?.focus())
  } catch (err: any) {
    errors.value.contact = err?.data?.message || 'Failed to send recovery code'
    if ($toast && $toast.error) {
      $toast.error('Send failed', errors.value.contact)
    }
  } finally {
    isLoading.value = false
  }
}

function resendOtp() {
  clearOtpCountdown()
  otp.value = ['', '', '', '', '', '']
  sendOtp()
}

function onOtpInput(i: number) {
  if (otp.value[i].length === 1 && i < 5) {
    nextTick(() => otpRefs.value[i + 1]?.focus())
  }
}

function onOtpBackspace(i: number) {
  if (otp.value[i] === '' && i > 0) {
    nextTick(() => otpRefs.value[i - 1]?.focus())
  }
}

async function verifyOtp() {
  const fullOtp = otp.value.join('')
  if (fullOtp.length !== 6) {
    errors.value.otp = 'Please enter the complete 6-digit code'
    return
  }

  isLoading.value = true
  errors.value = {}

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: {
        type: contactType.value,
        value: form.value[contactType.value].trim(),
        otp: fullOtp,
        purpose: 'password_reset'
      }
    })

    if (res.data?.valid) {
      step.value = 3
      clearOtpCountdown()

      if ($toast && $toast.success) {
        $toast.success('Verification successful', 'Now create your new password')
      }
    } else {
      errors.value.otp = 'Invalid verification code'
    }
  } catch (err: any) {
    errors.value.otp = err?.data?.message || 'Verification failed'
    if ($toast && $toast.error) {
      $toast.error('Verification failed', errors.value.otp)
    }
  } finally {
    isLoading.value = false
  }
}

function getPasswordStrength(): string {
  const password = form.value.password
  if (!password) return 'Enter password'
  if (password.length < 6) return 'Too weak'
  if (password.length < 8) return 'Weak'
  if (password.length >= 8 && /(?=.*[a-z])(?=.*[A-Z])/.test(password)) return 'Strong'
  if (password.length >= 10 && /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/.test(password)) return 'Very strong'
  return 'Fair'
}

function getPasswordStrengthClass(): string {
  const strength = getPasswordStrength()
  switch (strength) {
    case 'Too weak': return 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
    case 'Weak': return 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400'
    case 'Fair': return 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400'
    case 'Strong': return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400'
    case 'Very strong': return 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'
    default: return 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'
  }
}

function getPasswordStrengthBarClass(): string {
  const strength = getPasswordStrength()
  switch (strength) {
    case 'Too weak': return 'bg-red-500'
    case 'Weak': return 'bg-orange-500'
    case 'Fair': return 'bg-yellow-500'
    case 'Strong': return 'bg-emerald-500'
    case 'Very strong': return 'bg-green-500'
    default: return 'bg-slate-300'
  }
}

function getPasswordStrengthWidth(): string {
  const strength = getPasswordStrength()
  switch (strength) {
    case 'Too weak': return '20%'
    case 'Weak': return '40%'
    case 'Fair': return '60%'
    case 'Strong': return '80%'
    case 'Very strong': return '100%'
    default: return '0%'
  }
}

function isPasswordValid(): boolean {
  const password = form.value.password
  const confirmPassword = form.value.confirmPassword
  return password.length >= 6 && password === confirmPassword
}

async function resetPassword() {
  const password = form.value.password.trim()
  const confirmPassword = form.value.confirmPassword.trim()

  if (password.length < 6) {
    errors.value.password = 'Password must be at least 6 characters'
    return
  }

  if (password !== confirmPassword) {
    errors.value.confirmPassword = 'Passwords do not match'
    return
  }

  isLoading.value = true
  errors.value = {}

  try {
    await useSanctumFetch(`${config.public.apiBase}/auth/reset-password`, {
      method: 'POST',
      body: {
        type: contactType.value,
        value: form.value[contactType.value].trim(),
        otp: otp.value.join(''),
        password,
        password_confirmation: confirmPassword
      }
    })

    showSuccessModal.value = true

    if ($toast && $toast.success) {
      $toast.success('Password updated!', 'Your password has been reset successfully')
    }
  } catch (err: any) {
    errors.value.reset = err?.data?.message || 'Failed to reset password'
    if ($toast && $toast.error) {
      $toast.error('Reset failed', errors.value.reset)
    }
  } finally {
    isLoading.value = false
  }
}

function closeSuccessModal() {
  showSuccessModal.value = false
}

function redirectToLogin() {
  closeSuccessModal()
  router.push('/auth/login')
}

/* Lifecycle */
onMounted(() => {
  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value, orb3.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})

onBeforeUnmount(() => {
  clearOtpCountdown()
})
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
