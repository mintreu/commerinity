<script setup lang="ts">
const benefits = [
  {
    title: 'Premium Selection',
    description: 'Explore our curated catalog of high-quality products from top brands and trusted suppliers worldwide.',
    icon: 'i-lucide-award',
    features: ['Curated products', 'Top brands', 'Guaranteed quality'],
    gradient: 'from-violet-500 to-purple-600'
  },
  {
    title: 'Fast & Secure Delivery',
    description: 'Enjoy reliable and speedy shipping to your doorstep with real-time tracking for every order.',
    icon: 'i-lucide-truck',
    features: ['Real-time tracking', 'Global shipping', 'Secure packaging'],
    gradient: 'from-blue-500 to-cyan-600'
  },
  {
    title: 'Exclusive Deals',
    description: 'Get access to member-only discounts, early sales, and special offers on your favorite items.',
    icon: 'i-lucide-tag',
    features: ['Member discounts', 'Early access', 'Special offers'],
    gradient: 'from-pink-500 to-rose-600'
  },
  {
    title: 'Secure Payments',
    description: 'Shop with confidence using our encrypted and safe payment gateway supporting multiple methods.',
    icon: 'i-lucide-shield-check',
    features: ['PCI compliant', 'Multiple methods', 'Encrypted data'],
    gradient: 'from-amber-500 to-orange-600'
  },
  {
    title: 'Hassle-Free Returns',
    description: 'Not satisfied? Our easy return policy ensures a stress-free experience for all your purchases.',
    icon: 'i-lucide-refresh-cw',
    features: ['Easy returns', 'Quick refunds', 'Customer protection'],
    gradient: 'from-emerald-500 to-green-600'
  },
  {
    title: 'Dedicated Support',
    description: 'Our customer success team is available 24/7 to assist you with any questions or concerns.',
    icon: 'i-lucide-headphones',
    features: ['24/7 availability', 'Expert assistance', 'Quick response'],
    gradient: 'from-indigo-500 to-violet-600'
  }
]
</script>

<template>
  <section class="py-16 md:py-24 section-gradient-primary relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-50">
      <div class="floating-orb floating-orb-primary orb-md top-10 left-5" />
      <div class="floating-orb floating-orb-secondary orb-lg bottom-10 right-5" />
    </div>

    <UContainer class="relative z-10">
      <!-- Section Header -->
      <div class="text-center mb-12 md:mb-16">
        <div class="premium-badge mx-auto mb-4">
          <UIcon
            name="i-lucide-sparkles"
            class="w-4 h-4 mr-2"
          />
          <span>Exclusive Benefits</span>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-4">
          <span class="text-slate-900 dark:text-white">Why Shop With</span>
          <span class="block gradient-text-primary">VRIDDHI VIKASH</span>
        </h2>

        <p class="text-slate-600 dark:text-slate-300 text-base md:text-lg max-w-3xl mx-auto">
          Experience the <span class="font-bold text-violet-600 dark:text-violet-400">ultimate convenience</span> and
          <span class="font-bold text-pink-600 dark:text-pink-400">unbeatable value</span>
        </p>
      </div>

      <!-- Benefits Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <div
          v-for="(benefit, index) in benefits"
          :key="index"
          class="group feature-card-premium shine-effect animate-list-item"
        >
          <!-- Decorative Corner -->
          <div class="decorative-corner" />

          <!-- Content -->
          <div class="relative z-10">
            <!-- Icon -->
            <div class="relative mb-5">
              <div
                class="w-14 h-14 md:w-16 md:h-16 rounded-2xl flex items-center justify-center shadow-lg bg-gradient-to-br group-hover:scale-110 transition-transform duration-500"
                :class="benefit.gradient"
              >
                <UIcon
                  :name="benefit.icon"
                  class="w-7 h-7 md:w-8 md:h-8 text-white"
                />
                <!-- Pulse Ring on Hover -->
                <div class="pulse-ring text-white/30 group-hover:opacity-100 opacity-0" />
              </div>
              <!-- Badge Number -->
              <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center font-bold text-white text-xs shadow-lg">
                {{ index + 1 }}
              </div>
            </div>

            <!-- Title -->
            <h3 class="text-lg md:text-xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
              {{ benefit.title }}
            </h3>

            <!-- Description -->
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
              {{ benefit.description }}
            </p>

            <!-- Features List -->
            <ul class="space-y-2">
              <li
                v-for="(feature, idx) in benefit.features"
                :key="idx"
                class="flex items-center text-sm text-slate-500 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300 transition-colors"
              >
                <UIcon
                  name="i-lucide-check-circle"
                  class="w-4 h-4 text-emerald-500 mr-2 shrink-0"
                />
                <span>{{ feature }}</span>
              </li>
            </ul>

            <!-- Learn More Link -->
            <div class="mt-5 flex items-center text-sm font-semibold text-violet-600 dark:text-violet-400 group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors">
              <span>Learn More</span>
              <UIcon
                name="i-lucide-arrow-right"
                class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"
              />
            </div>
          </div>

          <!-- Bottom Accent Line -->
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 via-pink-500 to-violet-500 rounded-b-3xl opacity-0 group-hover:opacity-100 transition-opacity" />
        </div>
      </div>

      <!-- CTA Button -->
      <div class="text-center mt-12 md:mt-16">
        <NuxtLink to="/shop">
          <UButton
            size="xl"
            color="primary"
            class="px-8 py-4 font-bold shadow-xl shadow-violet-500/25 hover:shadow-2xl hover:shadow-violet-500/40 hover:scale-105 transition-all duration-300"
          >
            <UIcon
              name="i-lucide-shopping-bag"
              class="w-5 h-5 mr-2"
            />
            Start Shopping Now
            <UIcon
              name="i-lucide-arrow-right"
              class="w-5 h-5 ml-2"
            />
          </UButton>
        </NuxtLink>

        <p class="text-sm text-slate-500 dark:text-slate-400 mt-4 flex items-center justify-center gap-2">
          <UIcon
            name="i-lucide-check-circle"
            class="w-4 h-4 text-emerald-500"
          />
          Fast Shipping • Verified Products • Secure Payments
        </p>
      </div>
    </UContainer>
  </section>
</template>
