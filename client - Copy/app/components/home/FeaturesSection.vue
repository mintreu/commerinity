<script setup lang="ts">
const features = [
  {
    title: 'Seamless Shopping',
    description: 'Advanced search, smart filtering, and personalized recommendations to help you find exactly what you need.',
    icon: 'i-lucide-shopping-cart',
    features: ['Smart product search', 'Personalized suggestions', 'One-click ordering'],
    gradient: 'from-violet-500 to-purple-600'
  },
  {
    title: 'Order Management',
    description: 'Track your orders in real-time from our warehouse to your doorstep with detailed status updates.',
    icon: 'i-lucide-package',
    features: ['Live order tracking', 'Digital invoices', 'Hassle-free returns'],
    gradient: 'from-blue-500 to-indigo-600'
  },
  {
    title: 'Personalized Profile',
    description: 'Manage your delivery addresses, payment methods, and wishlists seamlessly in your personal account.',
    icon: 'i-lucide-user-circle',
    features: ['Address book management', 'Multiple payment options', 'Wishlist sync'],
    gradient: 'from-pink-500 to-rose-600'
  },
  {
    title: 'Secure Transactions',
    description: 'Shop with peace of mind using our enterprise-grade security and multiple safe payment gateways.',
    icon: 'i-lucide-shield-check',
    features: ['SSL encryption', 'Secure checkout', 'Fraud protection'],
    gradient: 'from-emerald-500 to-green-600'
  },
  {
    title: 'Smart Notifications',
    description: 'Never miss a deal with real-time notifications about price drops, new arrivals, and order updates.',
    icon: 'i-lucide-bell',
    features: ['Price drop alerts', 'New arrival updates', 'Order status push'],
    gradient: 'from-amber-500 to-orange-600'
  },
  {
    title: 'Responsive Helpdesk',
    description: 'Get instant assistance from our dedicated support team through our integrated helpdesk system.',
    icon: 'i-lucide-headphones',
    features: ['24/7 ticket support', 'Live chat assistance', 'Extensive FAQ library'],
    gradient: 'from-cyan-500 to-blue-600'
  }
]
</script>

<template>
  <section class="py-16 md:py-24 section-gradient-secondary relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="hidden sm:block absolute inset-0 overflow-hidden pointer-events-none opacity-50">
      <div class="floating-orb floating-orb-accent orb-lg top-10 left-5" />
      <div class="floating-orb floating-orb-primary orb-md bottom-10 right-5" />
    </div>

    <UContainer class="relative z-10">
      <!-- Section Header -->
      <div class="text-center mb-12 md:mb-16">
        <div class="premium-badge mx-auto mb-4">
          <UIcon
            name="i-lucide-sparkles"
            class="w-4 h-4 mr-2"
          />
          <span>Premium Features</span>
        </div>

        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-4">
          <span class="text-slate-900 dark:text-white">Modern Platform</span>
          <span class="block gradient-text-secondary">Built for You</span>
        </h2>

        <p class="text-slate-600 dark:text-slate-300 text-base md:text-lg max-w-3xl mx-auto">
          Experience the most advanced and user-friendly
          <span class="font-bold text-violet-600 dark:text-violet-400">e-commerce platform</span>
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <div
          v-for="(feature, index) in features"
          :key="index"
          class="group feature-card-premium animate-list-item"
        >
          <!-- Large Background Icon -->
          <div class="hidden sm:block absolute top-0 right-0 w-24 h-24 md:w-32 md:h-32 opacity-5 dark:opacity-10 pointer-events-none">
            <UIcon
              :name="feature.icon"
              class="w-full h-full text-violet-600"
            />
          </div>

          <!-- Content -->
          <div class="relative z-10">
            <!-- Icon -->
            <div class="mb-4">
              <div
                class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center shadow-lg bg-gradient-to-br group-hover:scale-110 transition-transform duration-300"
                :class="feature.gradient"
              >
                <UIcon
                  :name="feature.icon"
                  class="w-6 h-6 sm:w-7 sm:h-7 text-white"
                />
              </div>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg md:text-xl font-bold text-slate-900 dark:text-white mb-2 leading-tight">
              {{ feature.title }}
            </h3>

            <!-- Description -->
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
              {{ feature.description }}
            </p>

            <!-- Features List -->
            <ul class="space-y-2">
              <li
                v-for="(item, idx) in feature.features"
                :key="idx"
                class="flex items-start text-xs sm:text-sm text-slate-500 dark:text-slate-400"
              >
                <UIcon
                  name="i-lucide-check-circle"
                  class="w-4 h-4 text-violet-500 mr-2 shrink-0 mt-0.5"
                />
                <span>{{ item }}</span>
              </li>
            </ul>
          </div>

          <!-- Bottom Accent Line -->
          <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 via-pink-500 to-blue-500 rounded-b-3xl opacity-0 group-hover:opacity-100 transition-opacity" />
        </div>
      </div>

      <!-- CTA Button -->
      <div class="text-center mt-12 md:mt-16">
        <NuxtLink to="/auth/register">
          <UButton
            size="xl"
            color="neutral"
            variant="solid"
            class="px-8 py-4 font-bold shadow-lg hover:shadow-xl transition-all duration-300"
          >
            <UIcon
              name="i-lucide-zap"
              class="w-5 h-5 mr-2"
            />
            Explore All Features
            <UIcon
              name="i-lucide-arrow-right"
              class="w-5 h-5 ml-2"
            />
          </UButton>
        </NuxtLink>

        <p class="text-sm text-slate-500 dark:text-slate-400 mt-4 flex items-center justify-center gap-2">
          <UIcon
            name="i-lucide-check-circle"
            class="w-4 h-4 text-violet-500"
          />
          All features included • No hidden costs
        </p>
      </div>
    </UContainer>
  </section>
</template>
