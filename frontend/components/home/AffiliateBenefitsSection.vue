<template>
  <section class="w-full py-8 sm:py-12 md:py-16 lg:py-20 bg-gradient-to-b from-white via-purple-50/50 to-pink-50/30 dark:from-gray-950 dark:via-purple-950/30 dark:to-pink-950/20 relative overflow-hidden">

    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-50">
      <div class="absolute top-10 left-5 w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-full blur-3xl animate-float" />
      <div class="absolute bottom-10 right-5 w-40 h-40 sm:w-56 sm:h-56 md:w-80 md:h-80 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-full blur-3xl animate-float-delayed" />
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 relative z-10">

      <!-- Section Header -->
      <div ref="headerRef" class="text-center mb-6 sm:mb-8 md:mb-10 lg:mb-12">
        <!-- Badge -->
        <div class="inline-flex items-center px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border-2 border-purple-200 dark:border-purple-800 backdrop-blur-lg mb-3 sm:mb-4 shadow-lg hover:scale-105 transition-transform duration-300">
          <Icon name="mdi:gift-outline" class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-purple-600 dark:text-purple-400" />
          <span class="text-xs sm:text-sm font-bold text-purple-700 dark:text-purple-300 whitespace-nowrap">Exclusive Benefits</span>
        </div>

        <!-- Title -->
        <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-black mb-2 sm:mb-3 leading-tight px-2">
          <span class="text-gray-900 dark:text-white block mb-1">Why Join Our</span>
          <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 bg-clip-text text-transparent inline-block">
            Affiliate Network
          </span>
        </h2>

        <!-- Subtitle -->
        <p class="text-xs sm:text-sm md:text-base lg:text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed px-2">
          Unlock <span class="font-bold text-purple-600 dark:text-purple-400">exclusive benefits</span> and
          <span class="font-bold text-pink-600 dark:text-pink-400">maximize your earnings</span>
        </p>
      </div>

      <!-- Benefits Grid - Mobile Centered with Max Width -->
      <div ref="gridRef" class="w-full max-w-md sm:max-w-none mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-5 lg:gap-6">

          <div
              v-for="(benefit, index) in benefits"
              :key="index"
              class="benefit-card w-full group relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-xl sm:rounded-2xl lg:rounded-3xl p-4 sm:p-5 md:p-6 shadow-lg hover:shadow-xl hover:shadow-purple-500/20 dark:hover:shadow-purple-500/30 transition-all duration-300 sm:hover:-translate-y-1 sm:hover:scale-[1.02] border border-gray-200 dark:border-gray-700 hover:border-purple-500/50 overflow-hidden"
          >

            <!-- Animated Background Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/0 via-pink-500/0 to-purple-500/0 group-hover:from-purple-500/5 group-hover:via-pink-500/5 group-hover:to-purple-500/5 transition-all duration-500 rounded-xl sm:rounded-2xl lg:rounded-3xl" />

            <!-- Decorative Corner -->
            <div class="absolute top-0 right-0 w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-bl-[50px] sm:rounded-bl-[60px] group-hover:scale-110 transition-transform duration-500" />

            <!-- Shine Effect -->
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700" />
            </div>

            <div class="relative z-10 w-full h-full flex flex-col">

              <!-- Icon Container -->
              <div class="icon-container relative mb-3 sm:mb-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg transform sm:group-hover:scale-105 sm:group-hover:rotate-3 transition-all duration-500 relative">
                  <Icon :name="benefit.icon" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white" />

                  <!-- Pulsing Ring -->
                  <div class="absolute inset-0 rounded-xl border-2 border-purple-400 animate-ping opacity-0 group-hover:opacity-60" />
                </div>

                <!-- Floating Badge Number -->
                <div class="absolute -top-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center font-black text-white text-xs shadow-lg transform sm:group-hover:scale-110 transition-all duration-300">
                  {{ index + 1 }}
                </div>
              </div>

              <!-- Title -->
              <h3 class="text-base sm:text-lg md:text-xl font-black text-gray-900 dark:text-white mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300 leading-tight">
                {{ benefit.title }}
              </h3>

              <!-- Description -->
              <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3 flex-grow">
                {{ benefit.description }}
              </p>

              <!-- Features List -->
              <ul v-if="benefit.features" class="space-y-1.5 mb-3">
                <li
                    v-for="(feature, idx) in benefit.features"
                    :key="idx"
                    class="flex items-start text-xs sm:text-sm text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors duration-300"
                >
                  <Icon name="mdi:check-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-500 mr-2 flex-shrink-0 mt-0.5 sm:group-hover:scale-110 transition-transform duration-300" />
                  <span class="flex-1">{{ feature }}</span>
                </li>
              </ul>

              <!-- CTA Link -->
              <div class="inline-flex items-center text-xs sm:text-sm font-bold text-purple-600 dark:text-purple-400 group-hover:text-pink-600 dark:group-hover:text-pink-400 transition-colors duration-300 mt-auto">
                <span>Learn More</span>
                <Icon name="mdi:arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4 ml-1 sm:group-hover:translate-x-1 transition-transform duration-300" />
              </div>
            </div>

            <!-- Bottom Glow -->
            <div class="absolute bottom-0 left-0 right-0 h-0.5 sm:h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
          </div>

        </div>
      </div>

      <!-- Bottom CTA -->
      <div ref="ctaRef" class="text-center mt-8 sm:mt-10 md:mt-12 lg:mt-16">
        <NuxtLink
            to="/auth/register"
            class="inline-flex items-center px-5 sm:px-6 md:px-8 py-2.5 sm:py-3 md:py-4 bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 text-white font-black text-sm sm:text-base rounded-xl shadow-xl hover:shadow-2xl hover:shadow-purple-500/50 transition-all duration-300 transform hover:scale-105 active:scale-95 group"
        >
          <Icon name="mdi:rocket-launch" class="w-4 h-4 sm:w-5 sm:h-5 mr-2 group-hover:rotate-45 transition-transform duration-500" />
          <span>Start Earning Today</span>
          <Icon name="mdi:arrow-right" class="w-4 h-4 sm:w-5 sm:h-5 ml-2 group-hover:translate-x-1 transition-transform duration-300" />
        </NuxtLink>

        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-3 px-4">
          <Icon name="mdi:check-circle" class="inline w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-500 mr-1" />
          No credit card required • Free to join • Instant approval
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const headerRef = ref<HTMLElement | null>(null)
const gridRef = ref<HTMLElement | null>(null)
const ctaRef = ref<HTMLElement | null>(null)

const benefits = ref([
  {
    title: "Lifetime Commissions",
    description: "Earn recurring income from every customer you refer, even on repeat purchases. Your commission never expires and compounds over time.",
    icon: "mdi:infinity",
    features: [
      "30% recurring commission on all sales",
      "Earnings from customer lifetime value",
      "Passive income that grows"
    ]
  },
  {
    title: "Real-Time Dashboard",
    description: "Monitor your sales, clicks, conversions, and earnings in real-time with our advanced analytics dashboard powered by AI.",
    icon: "mdi:chart-timeline-variant",
    features: [
      "Live performance metrics",
      "Detailed conversion analytics",
      "Exportable reports"
    ]
  },
  {
    title: "Premium Marketing Kit",
    description: "Get access to professionally designed banners, product images, email templates, and promotional content that converts.",
    icon: "mdi:palette-swatch",
    features: [
      "HD banner collection (50+)",
      "Social media templates",
      "Email campaign kits"
    ]
  },
  {
    title: "Lightning Fast Payouts",
    description: "Receive your earnings quickly with multiple payout options including PayPal, bank transfer, cryptocurrency, and more.",
    icon: "mdi:cash-fast",
    features: [
      "Weekly automatic payouts",
      "$50 minimum threshold",
      "Multiple payment methods"
    ]
  },
  {
    title: "Expert Training Hub",
    description: "Access our comprehensive library of video tutorials, webinars, case studies, and proven success strategies from top affiliates.",
    icon: "mdi:school-outline",
    features: [
      "100+ video courses",
      "Weekly live webinars",
      "Private community access"
    ]
  },
  {
    title: "Priority Support",
    description: "Get help whenever you need it from our experienced affiliate support team available 24/7 via chat, email, and phone.",
    icon: "mdi:headset",
    features: [
      "24/7 dedicated support",
      "Personal account manager",
      "Priority ticket resolution"
    ]
  }
])

let observer: IntersectionObserver | null = null

onMounted(() => {
  if (!process.client) return

  const options = {
    root: null,
    threshold: 0.1,
    rootMargin: '0px'
  }

  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in')
        observer?.unobserve(entry.target)
      }
    })
  }, options)

  if (headerRef.value) observer.observe(headerRef.value)
  if (gridRef.value) observer.observe(gridRef.value)
  if (ctaRef.value) observer.observe(ctaRef.value)
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
    observer = null
  }
})
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(20px, -20px); }
}

@keyframes float-delayed {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(-20px, 20px); }
}

.animate-float {
  animation: float 15s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float-delayed 20s ease-in-out infinite;
}

/* Desktop entrance animations */
@media (min-width: 640px) {
  [ref="headerRef"],
  [ref="gridRef"],
  [ref="ctaRef"] {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
  }

  [ref="headerRef"].animate-in,
  [ref="gridRef"].animate-in,
  [ref="ctaRef"].animate-in {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Mobile - fade only, no transforms */
@media (max-width: 639px) {
  [ref="headerRef"],
  [ref="gridRef"],
  [ref="ctaRef"] {
    opacity: 0;
    transition: opacity 0.4s ease;
  }

  [ref="headerRef"].animate-in,
  [ref="gridRef"].animate-in,
  [ref="ctaRef"].animate-in {
    opacity: 1;
  }

  /* Disable floating animations on mobile */
  .animate-float,
  .animate-float-delayed {
    animation: none;
  }

  /* Disable all transforms on mobile */
  .benefit-card .icon-container > div,
  .benefit-card .icon-container .absolute {
    transform: none !important;
  }
}

.benefit-card {
  min-height: 280px;
}

@media (min-width: 640px) and (max-width: 1024px) {
  .benefit-card {
    min-height: 320px;
  }
}
</style>
