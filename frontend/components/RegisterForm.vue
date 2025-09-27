<template>
  <!-- Desktop: Side-by-side layout, Mobile: Stacked layout -->
  <div class="min-h-screen w-full flex relative overflow-hidden">

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

    <!-- Left Side: Platform Benefits & Welcome (Desktop Only) -->
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative z-10 flex-col justify-center p-12">
      <!-- Background gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-600/6 via-indigo-600/4 to-purple-600/6 backdrop-blur-sm"></div>

      <div class="relative z-10 max-w-lg">
        <!-- Brand Logo & Name -->
<!--        <div class="flex items-center gap-4 mb-12">-->
<!--          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl transform transition-transform hover:scale-105 duration-300">-->
<!--            <Icon name="mdi:shopping" class="w-8 h-8 text-white" />-->
<!--          </div>-->
<!--          <div>-->
<!--            <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">-->
<!--              {{ companyName }}-->
<!--            </h1>-->
<!--            <p class="text-slate-600 dark:text-slate-400 font-medium">Join Our Shopping Community</p>-->
<!--          </div>-->
<!--        </div>-->

        <!-- Welcome Message -->
        <h2 class="text-5xl xl:text-6xl font-bold mb-8 leading-tight">
          <span class="bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
            Start Your
          </span>
          <br>
          <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 dark:from-blue-400 dark:via-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
            Shopping Journey
          </span>
        </h2>

        <!-- Platform Description -->
        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 leading-relaxed">
          Create your account and unlock access to millions of products, exclusive deals, and personalized shopping experience.
        </p>

        <!-- Registration Benefits -->
        <div class="grid grid-cols-1 gap-6 mb-12">
          <!-- Exclusive Access -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:star-circle" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Member Exclusive Access</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Get early access to sales, exclusive member-only discounts and special offers
              </p>
            </div>
          </div>

          <!-- Personalized Experience -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:account-heart" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Personalized Experience</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Curated recommendations, wishlist, order tracking, and customized deals
              </p>
            </div>
          </div>

          <!-- Rewards & Benefits -->
          <div class="flex items-start gap-4 p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
              <Icon name="mdi:gift-outline" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-bold text-slate-900 dark:text-white mb-2">Rewards & Cashback</h3>
              <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                Earn points on every purchase, get cashback rewards, and unlock special benefits
              </p>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-gradient-to-r from-blue-500/8 to-indigo-500/8 dark:from-blue-400/8 dark:to-indigo-400/8 rounded-2xl p-6 mb-8 border border-blue-200/40 dark:border-blue-800/40">
          <h4 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <Icon name="mdi:chart-line" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            Join Millions of Happy Customers
          </h4>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="text-center">
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">50L+</p>
              <p class="text-slate-600 dark:text-slate-400">Active Users</p>
            </div>
            <div class="text-center">
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">10M+</p>
              <p class="text-slate-600 dark:text-slate-400">Products</p>
            </div>
          </div>
        </div>

        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400 flex-wrap">
          <div class="flex items-center gap-2">
            <Icon name="mdi:shield-check" class="w-4 h-4" />
            <span>100% Secure</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:clock-fast" class="w-4 h-4" />
            <span>2-Min Signup</span>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="mdi:email-check" class="w-4 h-4" />
            <span>Email Verified</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side: Registration Form (Desktop) / Full Width (Mobile) -->
    <div class="w-full lg:w-1/2 xl:w-2/5 relative z-10 flex items-center justify-center p-4 lg:p-8">
      <div class="w-full max-w-md">
        <!-- Registration Card -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-2xl border border-white/30 dark:border-slate-700/60 rounded-3xl shadow-2xl p-8 transition-all duration-500 hover:shadow-3xl hover:-translate-y-1">

          <!-- Mobile: Branding Section (shown only on mobile) -->
          <div class="lg:hidden text-center mb-8">
            <div class="flex items-center justify-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl transform transition-transform hover:scale-110 duration-300">
                <Icon name="mdi:shopping" class="w-8 h-8 text-white" />
              </div>
            </div>

            <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-3">
              Join {{ companyName }}
            </h1>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
              Create your account and start shopping
            </p>

            <!-- Login Link (Mobile) -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
              <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Already have an account?</p>
              <NuxtLink
                  to="/auth/login"
                  class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold text-sm transition-colors group"
              >
                <span>Sign In Instead</span>
                <Icon name="mdi:arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </NuxtLink>
            </div>
          </div>

          <!-- Desktop: Simple Header -->
          <div class="hidden lg:block text-center mb-8">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-2">
              Create Account
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm">
              Join our shopping community today
            </p>
          </div>

          <!-- Progress Steps -->
          <div class="flex items-center justify-center mb-8">
            <div class="flex items-center space-x-4">
              <div class="flex items-center">
                <div :class="step >= 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400'" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">
                  {{ step > 1 ? '✓' : '1' }}
                </div>
                <span class="ml-2 text-sm font-medium" :class="step >= 1 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'">
                  Verify
                </span>
              </div>

              <div class="w-8 h-0.5" :class="step >= 2 ? 'bg-blue-600' : 'bg-slate-200 dark:bg-slate-600'"></div>

              <div class="flex items-center">
                <div :class="step >= 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400'" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">
                  {{ step > 2 ? '✓' : '2' }}
                </div>
                <span class="ml-2 text-sm font-medium" :class="step >= 2 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'">
                  Profile
                </span>
              </div>

              <div class="w-8 h-0.5" :class="step >= 3 ? 'bg-blue-600' : 'bg-slate-200 dark:bg-slate-600'"></div>

              <div class="flex items-center">
                <div :class="step >= 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400'" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">
                  3
                </div>
                <span class="ml-2 text-sm font-medium" :class="step >= 3 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'">
                  Secure
                </span>
              </div>
            </div>
          </div>

          <!-- Registration Form -->
          <form @submit.prevent="handleRegister" class="space-y-6">

            <!-- STEP 1: Contact Verification -->
            <div v-if="step === 1" class="space-y-6">
              <!-- Contact Method Toggle -->
              <div class="flex bg-slate-100 dark:bg-slate-700 rounded-xl p-1 mb-6">
                <button
                    type="button"
                    @click="registrationMode = 'mobile'"
                    :class="registrationMode === 'mobile' ? activeModeClass : inactiveModeClass"
                    class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
                >
                  <Icon name="mdi:cellphone" class="w-4 h-4" />
                  <span>Mobile</span>
                </button>
                <button
                    type="button"
                    @click="registrationMode = 'email'"
                    :class="registrationMode === 'email' ? activeModeClass : inactiveModeClass"
                    class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all duration-300"
                >
                  <Icon name="mdi:email" class="w-4 h-4" />
                  <span>Email</span>
                </button>
              </div>

              <!-- Contact Input -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon :name="registrationMode === 'mobile' ? 'mdi:cellphone' : 'mdi:email'" class="w-4 h-4" />
                  <span>{{ registrationMode === 'mobile' ? 'Mobile Number' : 'Email Address' }}</span>
                </label>

                <div class="relative">
                  <input
                      v-if="registrationMode === 'mobile'"
                      v-model="form.mobile"
                      type="tel"
                      required
                      :disabled="otpSent"
                      placeholder="+91 9XXXXXXXXX"
                      @blur="validateContact"
                      class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': fieldError }"
                  />

                  <input
                      v-else
                      v-model="form.email"
                      type="email"
                      required
                      :disabled="otpSent"
                      placeholder="you@example.com"
                      @blur="validateContact"
                      class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': fieldError }"
                  />

                  <Icon :name="registrationMode === 'mobile' ? 'mdi:phone' : 'mdi:email'" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>

                <div v-if="fieldError" class="flex items-center gap-2 text-red-500 text-sm">
                  <Icon name="mdi:alert-circle" class="w-4 h-4" />
                  <span>{{ fieldError }}</span>
                </div>
              </div>

              <!-- Referral Code (Optional) -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:account-plus" class="w-4 h-4" />
                  <span>Referral Code</span>
                  <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">(Optional)</span>
                </label>
                <div class="relative">
                  <input
                      v-model="form.referral"
                      type="text"
                      placeholder="Enter referral code if you have one"
                      class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  />
                  <Icon name="mdi:gift" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
              </div>

              <!-- Send OTP Button -->
              <div v-if="canShowOtpButton && !otpSent" class="flex justify-center">
                <button
                    type="button"
                    @click="sendOtp"
                    :disabled="otpSending"
                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                >
                  <Icon :name="otpSending ? 'mdi:loading' : 'mdi:send'" class="w-5 h-5" :class="{ 'animate-spin': otpSending }" />
                  <span>{{ otpSending ? 'Sending OTP...' : 'Send Verification Code' }}</span>
                </button>
              </div>

              <!-- OTP Input Section -->
              <div v-if="otpSent && !otpVerified" class="space-y-4">
                <div class="text-center">
                  <h4 class="font-bold text-slate-900 dark:text-white text-lg mb-2">Enter Verification Code</h4>
                  <p class="text-sm text-slate-600 dark:text-slate-400">Code sent to your {{ registrationMode === 'mobile' ? 'mobile' : 'email' }}</p>
                </div>

                <!-- OTP Input Grid -->
                <div class="flex gap-3 justify-center mb-4">
                  <input
                      v-for="(_, i) in 6"
                      :key="i"
                      v-model="otp[i]"
                      maxlength="1"
                      inputmode="numeric"
                      type="text"
                      class="w-12 h-12 text-center text-lg font-bold bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                      @input="focusNext(i, $event)"
                      @keydown.backspace.prevent="focusBackspace(i, $event)"
                  />
                </div>

                <div v-if="otpError" class="flex items-center gap-2 text-red-500 text-sm justify-center bg-red-50 dark:bg-red-900/20 px-6 py-4 rounded-xl border border-red-200/60 dark:border-red-800/60">
                  <Icon name="mdi:alert-circle" class="w-5 h-5" />
                  <span class="font-medium">{{ otpError }}</span>
                </div>

                <!-- Verify Button -->
                <button
                    v-if="otp.join('').length === 6"
                    type="button"
                    @click="verifyOtp"
                    :disabled="verifyingOtp"
                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                >
                  <Icon :name="verifyingOtp ? 'mdi:loading' : 'mdi:shield-check'" class="w-5 h-5" :class="{ 'animate-spin': verifyingOtp }" />
                  <span>{{ verifyingOtp ? 'Verifying...' : 'Verify Code' }}</span>
                </button>

                <!-- Resend OTP -->
                <div class="flex items-center justify-center text-sm">
                  <div v-if="countdown > 0" class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                    <Icon name="mdi:clock" class="w-4 h-4" />
                    <span>Resend code in {{ countdown }}s</span>
                  </div>
                  <button
                      v-else
                      type="button"
                      @click="sendOtp"
                      class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors"
                  >
                    <Icon name="mdi:refresh" class="w-4 h-4" />
                    <span>Resend Code</span>
                  </button>
                </div>
              </div>

              <!-- OTP Verified Success -->
              <div v-if="otpVerified" class="text-center space-y-4">
                <div class="flex items-center justify-center gap-2 p-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-600 dark:text-emerald-400">
                  <Icon name="mdi:check-circle" class="w-6 h-6" />
                  <span class="font-bold text-lg">Verification Successful!</span>
                </div>

                <button
                    type="button"
                    @click="step++"
                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                >
                  <span>Continue to Profile</span>
                  <Icon name="mdi:arrow-right" class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- STEP 2: Personal Information -->
            <div v-if="step === 2" class="space-y-6">
              <div class="text-center mb-6">
                <h4 class="font-bold text-slate-900 dark:text-white text-lg">Personal Information</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400">Tell us a bit about yourself</p>
              </div>

              <!-- Full Name -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:account" class="w-4 h-4" />
                  <span>Full Name</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input
                      v-model="form.name"
                      type="text"
                      required
                      minlength="3"
                      maxlength="100"
                      placeholder="Enter your full name"
                      class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': !form.name && step > 2 }"
                  />
                  <Icon name="mdi:account" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
              </div>

              <!-- Gender -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:gender-male-female" class="w-4 h-4" />
                  <span>Gender</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <select
                      v-model="form.gender"
                      required
                      class="w-full px-4 py-3 pl-12 pr-4 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': !form.gender && step > 2 }"
                  >
                    <option value="">Select your gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                    <option value="prefer_not_to_say">Prefer not to say</option>
                  </select>
                  <Icon name="mdi:gender-male-female" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
              </div>

              <!-- Date of Birth -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:calendar" class="w-4 h-4" />
                  <span>Date of Birth</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input
                      v-model="form.dob"
                      type="date"
                      required
                      :max="maxDate"
                      class="w-full px-4 py-3 pl-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': !form.dob && step > 2 }"
                  />
                  <Icon name="mdi:calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                </div>
              </div>

              <!-- Continue Button -->
              <button
                  type="button"
                  @click="validateStepTwo"
                  :disabled="!form.name || !form.gender || !form.dob"
                  class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
              >
                <span>Continue to Security</span>
                <Icon name="mdi:arrow-right" class="w-5 h-5" />
              </button>
            </div>

            <!-- STEP 3: Password Security -->
            <div v-if="step === 3" class="space-y-6">
              <div class="text-center mb-6">
                <h4 class="font-bold text-slate-900 dark:text-white text-lg">Secure Your Account</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400">Create a strong password to protect your account</p>
              </div>

              <!-- Password -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:key" class="w-4 h-4" />
                  <span>Password</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      minlength="6"
                      placeholder="Create a strong password"
                      class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  />
                  <Icon name="mdi:key" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                  <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all"
                  >
                    <Icon :name="showPassword ? 'mdi:eye-off' : 'mdi:eye'" class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <!-- Confirm Password -->
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:key-variant" class="w-4 h-4" />
                  <span>Confirm Password</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input
                      v-model="form.confirmPassword"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      placeholder="Re-enter your password"
                      class="w-full px-4 py-3 pl-12 pr-12 bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': passwordError }"
                  />
                  <Icon name="mdi:key-variant" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 dark:text-slate-400" />
                  <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-600 transition-all"
                  >
                    <Icon :name="showConfirmPassword ? 'mdi:eye-off' : 'mdi:eye'" class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div v-if="passwordError" class="flex items-center gap-2 text-red-500 text-sm p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <Icon name="mdi:alert-circle" class="w-4 h-4" />
                <span>{{ passwordError }}</span>
              </div>

              <!-- Terms & Conditions -->
              <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <input
                    v-model="agreedToTerms"
                    type="checkbox"
                    required
                    class="w-5 h-5 text-blue-600 bg-white dark:bg-slate-700 border-2 border-slate-300 dark:border-slate-600 rounded focus:ring-blue-500 focus:ring-2 mt-0.5"
                />
                <div class="text-sm text-slate-600 dark:text-slate-400">
                  <p>I agree to the
                    <a href="/terms" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">Terms of Service</a>
                    and
                    <a href="/privacy" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">Privacy Policy</a>
                  </p>
                </div>
              </div>

              <!-- Create Account Button -->
              <button
                  type="submit"
                  :disabled="!agreedToTerms || registering"
                  class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 group text-lg"
              >
                <Icon :name="registering ? 'mdi:loading' : 'mdi:account-plus'" class="w-6 h-6 group-hover:scale-110 transition-transform" :class="{ 'animate-spin': registering }" />
                <span>{{ registering ? 'Creating Account...' : 'Create My Account' }}</span>
              </button>
            </div>
          </form>

          <!-- Footer Links -->
          <div class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
            <span>Already have an account?</span>
            <NuxtLink
                to="/auth/login"
                class="ml-2 inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors group"
            >
              <span>Sign in instead</span>
              <Icon name="mdi:arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </NuxtLink>
          </div>
        </div>

        <!-- Mobile: Benefits Summary (Mobile Only) -->
        <div class="lg:hidden mt-6 space-y-4">
          <div class="text-center p-6 bg-white/60 dark:bg-slate-800/60 backdrop-blur-xl rounded-2xl border border-white/20 dark:border-slate-700/50">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Join {{ companyName }} Today!</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="text-center">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                  <Icon name="mdi:star-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
                <p class="font-semibold text-slate-900 dark:text-white">Exclusive Deals</p>
                <p class="text-slate-600 dark:text-slate-400 text-xs">Member-only offers</p>
              </div>
              <div class="text-center">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mx-auto mb-2">
                  <Icon name="mdi:gift-outline" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <p class="font-semibold text-slate-900 dark:text-white">Rewards</p>
                <p class="text-slate-600 dark:text-slate-400 text-xs">Earn on every purchase</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter, useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// Props
const props = defineProps({
  redirectTo: {
    type: String,
    default: '/dashboard'
  }
})

// Composables
const { login } = useSanctum()
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { $toast } = useNuxtApp()

// Configuration
const companyName = config.public.companyName || 'MintReu'

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()

// State
const registrationMode = ref<'mobile' | 'email'>('mobile')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const step = ref(1)
const agreedToTerms = ref(false)
const registering = ref(false)

// Form data
const form = ref({
  mobile: '',
  email: '',
  name: '',
  gender: '',
  dob: '',
  password: '',
  confirmPassword: '',
  referral: ''
})

// OTP state
const otp = ref(['', '', '', '', '', ''])
const otpSent = ref(false)
const otpVerified = ref(false)
const otpSending = ref(false)
const verifyingOtp = ref(false)
const otpError = ref('')
const countdown = ref(60)
let countdownInterval: NodeJS.Timeout | null = null

// Validation
const fieldError = ref('')
const passwordError = ref('')
const canShowOtpButton = ref(false)

// Computed
const activeModeClass = computed(() => 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-lg')
const inactiveModeClass = computed(() => 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300')

const maxDate = computed(() => {
  const today = new Date()
  const maxDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate())
  return maxDate.toISOString().split('T')[0]
})

// Methods
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

async function validateContact() {
  const type = registrationMode.value
  const value = (type === 'mobile' ? form.value.mobile : form.value.email).trim()
  const mobileRegex = /^[6-9]\d{9}$/
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if ((type === 'mobile' && !mobileRegex.test(value)) || (type === 'email' && !emailRegex.test(value))) {
    fieldError.value = type === 'mobile' ? 'Enter a valid 10-digit Indian mobile' : 'Enter a valid email address'
    canShowOtpButton.value = false
    return
  }

  fieldError.value = ''
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/has_contact`, {
      method: 'POST',
      body: { type, value },
    })

    const exists = res.data?.exists
    if (exists) {
      fieldError.value = `${type === 'mobile' ? 'Mobile number' : 'Email'} already in use`
      canShowOtpButton.value = false
    } else {
      canShowOtpButton.value = true
    }
  } catch {
    fieldError.value = 'Failed to check contact'
    canShowOtpButton.value = false
  }
}

async function sendOtp() {
  otpError.value = ''
  otpVerified.value = false
  otpSending.value = true
  otp.value = ['', '', '', '', '', '']

  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { type: registrationMode.value, value: form.value[registrationMode.value].trim() }
    })

    otpSent.value = true
    canShowOtpButton.value = false
    countdown.value = 60

    countdownInterval = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(countdownInterval!)
        canShowOtpButton.value = true
        otpSending.value = false
      }
    }, 1000)

    if (response.demo && response.otp) {
      $toast.info({
        title: 'Demo Mode',
        message: `Use OTP: ${response.otp}`
      })
    }
  } catch {
    otpError.value = 'Failed to send OTP'
    otpSending.value = false
  }
}

function focusNext(index: number, event: any) {
  if (event.inputType !== 'deleteContentBackward') {
    const next = event.target.nextElementSibling
    if (next) next.focus()
  }
}

function focusBackspace(index: number, event: KeyboardEvent) {
  if (otp.value[index] === '' && index > 0) {
    const prev = event.target?.previousElementSibling as HTMLInputElement
    if (prev) {
      prev.focus()
      prev.select()
    }
  }
}

async function verifyOtp() {
  const code = otp.value.join('')
  if (code.length !== 6) {
    otpError.value = 'Enter a 6-digit OTP'
    return
  }

  verifyingOtp.value = true
  otpError.value = ''

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { type: registrationMode.value, value: form.value[registrationMode.value].trim(), otp: code }
    })

    if (res?.data?.valid === true) {
      otpVerified.value = true
      clearInterval(countdownInterval!)
    } else {
      otpError.value = 'Invalid OTP, please try again'
    }
  } catch (err: any) {
    otpError.value = err?.data?.message || 'OTP verification failed'
  } finally {
    verifyingOtp.value = false
  }
}

function validateStepTwo() {
  if (!form.value.name || !form.value.gender || !form.value.dob) return
  step.value++
}

async function handleRegister() {
  passwordError.value = ''

  if (form.value.password !== form.value.confirmPassword) {
    passwordError.value = 'Passwords do not match'
    return
  }
  if (form.value.password.length < 6) {
    passwordError.value = 'Password must be at least 6 characters'
    return
  }

  registering.value = true

  const payload = {
    name: form.value.name,
    gender: form.value.gender,
    dob: form.value.dob,
    password: form.value.password,
    password_confirmation: form.value.confirmPassword,
    referral: form.value.referral,
    type: registrationMode.value,
    otp: otp.value.join(''),
    ...(registrationMode.value === 'mobile' ? { mobile: form.value.mobile } : { email: form.value.email }),
  }

  try {
    await useSanctumFetch(`${config.public.apiBase}/register`, {
      method: 'POST',
      body: payload
    })

    // Auto login after successful registration
    await login({
      [registrationMode.value === 'mobile' ? 'mobile' : 'email']: form.value[registrationMode.value],
      password: form.value.password,
      remember: true
    })

    $toast.success({
      title: 'Welcome to ' + companyName + '!',
      message: 'Your account has been created successfully'
    })

    router.push(props.redirectTo)
  } catch (err: any) {
    passwordError.value = err?.data?.message || 'Registration failed'
  } finally {
    registering.value = false
  }
}

// Lifecycle
onMounted(() => {
  const refCode = route.query.referral || ''
  if (refCode) form.value.referral = refCode as string

  // Initialize GSAP animations
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
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
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
