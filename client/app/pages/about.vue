<script setup lang="ts">
/**
 * About Us Page
 * Premium static page showcasing company mission, values, and information
 */
definePageMeta({
  layout: 'public'
})

const config = useRuntimeConfig()
const appName = config.public.appName
const companyName = config.public.companyLegalName
const supportEmail = config.public.supportEmail
const supportPhone = config.public.supportPhone
const companyAddress = config.public.companyAddress
const tagline = config.public.tagline

useSeoMeta({
  title: `About Us - ${appName}`,
  description: `Learn about ${companyName}. Empowering entrepreneurs through innovative network marketing and e-commerce solutions.`
})

// Company values
const values = [
  {
    icon: 'i-lucide-heart-handshake',
    title: 'Trust & Integrity',
    description: 'Building lasting relationships through transparent and ethical business practices.',
    gradient: 'from-blue-500 to-cyan-500',
    bgColor: 'bg-blue-100 dark:bg-blue-900/30',
    textColor: 'text-blue-600 dark:text-blue-400'
  },
  {
    icon: 'i-lucide-sparkles',
    title: 'Quality Products',
    description: 'Curating only the best products that deliver real value to our customers.',
    gradient: 'from-emerald-500 to-green-500',
    bgColor: 'bg-emerald-100 dark:bg-emerald-900/30',
    textColor: 'text-emerald-600 dark:text-emerald-400'
  },
  {
    icon: 'i-lucide-users',
    title: 'Community First',
    description: 'Fostering a supportive community where everyone can thrive and grow together.',
    gradient: 'from-violet-500 to-purple-500',
    bgColor: 'bg-violet-100 dark:bg-violet-900/30',
    textColor: 'text-violet-600 dark:text-violet-400'
  },
  {
    icon: 'i-lucide-rocket',
    title: 'Innovation',
    description: 'Continuously evolving our platform with cutting-edge technology and features.',
    gradient: 'from-rose-500 to-pink-500',
    bgColor: 'bg-rose-100 dark:bg-rose-900/30',
    textColor: 'text-rose-600 dark:text-rose-400'
  }
]

// Stats
const stats = [
  { value: '10K+', label: 'Active Members', icon: 'i-lucide-users' },
  { value: '500+', label: 'Products', icon: 'i-lucide-package' },
  { value: '50+', label: 'Cities Served', icon: 'i-lucide-map-pin' },
  { value: '99%', label: 'Satisfaction', icon: 'i-lucide-star' }
]

// Team principles
const principles = [
  {
    icon: 'i-lucide-shield-check',
    title: 'Secure & Reliable',
    description: 'Enterprise-grade security protecting your data and transactions 24/7.'
  },
  {
    icon: 'i-lucide-zap',
    title: 'Fast & Efficient',
    description: 'Lightning-fast platform performance ensuring seamless user experience.'
  },
  {
    icon: 'i-lucide-headphones',
    title: 'Always Available',
    description: 'Dedicated support team ready to assist you whenever you need help.'
  }
]
</script>

<template>
  <div class="policy-page min-h-screen">
    <!-- Hero Section -->
    <div class="policy-hero bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 py-16 md:py-24 relative overflow-hidden">
      <!-- Background Decorations -->
      <div class="absolute inset-0 opacity-30">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-white rounded-full blur-3xl" />
        <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-pink-300 rounded-full blur-3xl" />
        <div class="absolute top-1/2 left-0 w-64 h-64 bg-blue-300 rounded-full blur-3xl" />
      </div>

      <UContainer class="relative z-10">
        <div class="text-center text-white">
          <div class="policy-badge inline-flex items-center px-5 py-2.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-6">
            <UIcon
              name="i-lucide-building-2"
              class="w-4 h-4 mr-2"
            />
            <span>About {{ appName }}</span>
          </div>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 tracking-tight">
            About Us
          </h1>
          <p class="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto leading-relaxed">
            {{ tagline }}
          </p>
        </div>
      </UContainer>
    </div>

    <!-- Stats Section -->
    <UContainer class="relative z-20 -mt-12">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="policy-quick-card"
        >
          <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center mx-auto mb-3">
            <UIcon
              :name="stat.icon"
              class="w-6 h-6 text-white"
            />
          </div>
          <div class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white">
            {{ stat.value }}
          </div>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ stat.label }}
          </p>
        </div>
      </div>
    </UContainer>

    <UContainer class="py-12 md:py-16">
      <!-- Mission Section -->
      <div class="glass-card p-6 md:p-10 lg:p-12 max-w-5xl mx-auto mb-12">
        <div class="grid md:grid-cols-2 gap-8 lg:gap-12 items-center">
          <div>
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm font-semibold mb-4">
              <UIcon
                name="i-lucide-target"
                class="w-4 h-4 mr-2"
              />
              Our Mission
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-6">
              Empowering Entrepreneurs Worldwide
            </h2>
            <div class="policy-intro !mb-0">
              <p class="!mb-4">
                At <strong>{{ appName }}</strong>, we believe in creating opportunities for everyone. Our platform combines the power of e-commerce with network marketing, enabling individuals to build sustainable businesses from anywhere.
              </p>
              <p class="!mb-0">
                We're committed to providing high-quality products, fair compensation plans, and the tools needed for success in the modern digital economy.
              </p>
            </div>
          </div>
          <div class="flex justify-center">
            <div class="relative">
              <div class="w-56 h-56 md:w-64 md:h-64 bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-500 rounded-full flex items-center justify-center shadow-2xl">
                <UIcon
                  name="i-lucide-target"
                  class="w-28 h-28 text-white"
                />
              </div>
              <!-- Floating elements -->
              <div class="absolute -top-4 -right-4 w-16 h-16 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl flex items-center justify-center shadow-lg animate-float-slow">
                <UIcon
                  name="i-lucide-trending-up"
                  class="w-8 h-8 text-white"
                />
              </div>
              <div class="absolute -bottom-4 -left-4 w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg animate-float-gentle">
                <UIcon
                  name="i-lucide-star"
                  class="w-7 h-7 text-white"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Values Section -->
      <div class="max-w-5xl mx-auto mb-12">
        <div class="text-center mb-10">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-4">
            <UIcon
              name="i-lucide-heart"
              class="w-4 h-4 mr-2"
            />
            Our Values
          </div>
          <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-4">
            What We Stand For
          </h2>
          <p class="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
            Our core values guide everything we do, from product selection to member support.
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="value in values"
            :key="value.title"
            class="glass-card p-6 text-center group hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
          >
            <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4', value.bgColor]">
              <UIcon
                :name="value.icon"
                :class="['w-8 h-8', value.textColor]"
              />
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">
              {{ value.title }}
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
              {{ value.description }}
            </p>
          </div>
        </div>
      </div>

      <!-- Vision Section -->
      <div class="glass-card p-6 md:p-10 lg:p-12 max-w-5xl mx-auto mb-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 rounded-full blur-3xl" />
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-full blur-3xl" />

        <div class="relative">
          <div class="text-center mb-8">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-sm font-semibold mb-4">
              <UIcon
                name="i-lucide-eye"
                class="w-4 h-4 mr-2"
              />
              Our Vision
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-4">
              Building the Future of Commerce
            </h2>
            <p class="text-slate-600 dark:text-slate-400 max-w-3xl mx-auto text-lg leading-relaxed">
              We envision a world where everyone has access to quality products and the opportunity to build their own business. Through innovative technology and a supportive community, we're making this vision a reality.
            </p>
          </div>

          <div class="grid md:grid-cols-3 gap-6 mt-10">
            <div
              v-for="principle in principles"
              :key="principle.title"
              class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50"
            >
              <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center shrink-0 shadow-lg">
                <UIcon
                  :name="principle.icon"
                  class="w-6 h-6 text-white"
                />
              </div>
              <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-1">
                  {{ principle.title }}
                </h4>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  {{ principle.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Company Information -->
      <div class="glass-card p-6 md:p-10 lg:p-12 max-w-5xl mx-auto">
        <div class="text-center mb-8">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold mb-4">
            <UIcon
              name="i-lucide-info"
              class="w-4 h-4 mr-2"
            />
            Company Information
          </div>
          <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white">
            Get in Touch
          </h2>
        </div>

        <div class="policy-contact">
          <div class="grid gap-6 sm:grid-cols-2">
            <div class="flex items-start gap-4">
              <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                <UIcon
                  name="i-lucide-building-2"
                  class="w-5 h-5"
                />
              </div>
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">
                  Legal Name
                </p>
                <p class="font-semibold text-slate-900 dark:text-white">
                  {{ companyName }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                <UIcon
                  name="i-lucide-mail"
                  class="w-5 h-5"
                />
              </div>
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">
                  Support Email
                </p>
                <a
                  :href="`mailto:${supportEmail}`"
                  class="font-semibold text-violet-600 dark:text-violet-400 hover:underline"
                >
                  {{ supportEmail }}
                </a>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                <UIcon
                  name="i-lucide-phone"
                  class="w-5 h-5"
                />
              </div>
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">
                  Support Phone
                </p>
                <a
                  :href="`tel:${supportPhone}`"
                  class="font-semibold text-violet-600 dark:text-violet-400 hover:underline"
                >
                  {{ supportPhone }}
                </a>
              </div>
            </div>

            <div
              v-if="companyAddress"
              class="flex items-start gap-4"
            >
              <div class="icon-box icon-box-sm icon-box-soft-primary rounded-lg">
                <UIcon
                  name="i-lucide-map-pin"
                  class="w-5 h-5"
                />
              </div>
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">
                  Registered Address
                </p>
                <p class="font-semibold text-slate-900 dark:text-white">
                  {{ companyAddress }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-10 text-center">
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            Ready to start your journey with us?
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <NuxtLink to="/shop">
              <UButton
                size="lg"
                class="px-8"
              >
                <UIcon
                  name="i-lucide-shopping-bag"
                  class="w-5 h-5 mr-2"
                />
                Browse Products
              </UButton>
            </NuxtLink>
            <NuxtLink to="/auth/register">
              <UButton
                size="lg"
                variant="outline"
                class="px-8"
              >
                <UIcon
                  name="i-lucide-user-plus"
                  class="w-5 h-5 mr-2"
                />
                Join Our Community
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
