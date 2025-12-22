<template>
  <section class="w-full py-8 sm:py-12 md:py-16 lg:py-20 bg-gradient-to-b from-white via-purple-50/30 to-white dark:from-gray-950 dark:via-purple-900/20 dark:to-gray-950 relative overflow-hidden">

    <!-- Animated Background Elements - Hidden on mobile -->
    <div class="hidden sm:block absolute inset-0 overflow-hidden pointer-events-none opacity-50">
      <div class="absolute top-10 left-5 w-64 h-64 bg-gradient-to-br from-purple-500/20 to-blue-500/20 rounded-full blur-3xl animate-float" />
      <div class="absolute bottom-10 right-5 w-80 h-80 bg-gradient-to-br from-pink-500/20 to-purple-500/20 rounded-full blur-3xl animate-float-delayed" />
    </div>

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 lg:px-12 relative z-10">

      <!-- Section Header - Mobile optimized -->
      <div ref="headerRef" class="text-center mb-6 sm:mb-10 md:mb-12 lg:mb-16">
        <!-- Badge -->
        <div class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-lg mb-3 sm:mb-4 md:mb-6">
          <Icon name="mdi:star-circle" class="w-4 h-4 mr-1.5 text-purple-600 dark:text-purple-400" />
          <span class="text-xs sm:text-sm font-bold text-purple-700 dark:text-purple-300">Premium Features</span>
        </div>

        <!-- Title -->
        <h2 class="text-xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-2 sm:mb-4 leading-tight">
          <span class="text-gray-900 dark:text-white block">Powerful Features</span>
          <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 bg-clip-text text-transparent">
            Built for Success
          </span>
        </h2>

        <!-- Subtitle -->
        <p class="text-sm sm:text-base md:text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
          Everything you need to grow your business, all in one <span class="font-bold text-purple-600 dark:text-purple-400">powerful platform</span>
        </p>
      </div>

      <!-- Features Grid - Mobile optimized -->
      <div ref="gridRef" class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 md:gap-6 lg:gap-8">

        <div
            v-for="(feature, index) in features"
            :key="index"
            class="feature-card group relative bg-white dark:bg-gray-800 backdrop-blur-xl rounded-xl sm:rounded-2xl p-4 sm:p-6 md:p-8 shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-gray-700 hover:border-purple-500/50 overflow-hidden"
        >

          <!-- Large Background Icon - Simplified for mobile -->
          <div class="hidden sm:block absolute top-0 right-0 w-24 h-24 md:w-32 md:h-32 opacity-5 dark:opacity-10 pointer-events-none">
            <Icon :name="feature.icon" class="w-full h-full text-purple-600" />
          </div>

          <!-- Animated Background Gradient - Desktop only -->
          <div class="hidden sm:block absolute inset-0 bg-gradient-to-br from-purple-500/0 to-blue-500/0 group-hover:from-purple-500/5 group-hover:to-blue-500/5 transition-all duration-500 rounded-2xl" />

          <div class="relative z-10">

            <!-- Icon Container - Smaller on mobile -->
            <div class="mb-3 sm:mb-4">
              <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                <Icon :name="feature.icon" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white" />
              </div>
            </div>

            <!-- Title - Smaller on mobile -->
            <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 dark:text-white mb-2 leading-tight">
              {{ feature.title }}
            </h3>

            <!-- Description - Smaller on mobile -->
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
              {{ feature.description }}
            </p>

            <!-- Features List - Compact on mobile -->
            <ul class="space-y-1.5 sm:space-y-2">
              <li
                  v-for="(item, idx) in feature.features"
                  :key="idx"
                  class="flex items-start text-xs sm:text-sm text-gray-500 dark:text-gray-400"
              >
                <Icon name="mdi:check-circle" class="w-3.5 h-3.5 text-purple-500 mr-1.5 flex-shrink-0 mt-0.5" />
                <span>{{ item }}</span>
              </li>
            </ul>

            <!-- Bottom Accent Line - Desktop only -->
            <div class="hidden sm:block absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-b-2xl" />
          </div>
        </div>
      </div>

      <!-- Bottom CTA - Mobile optimized -->
      <div ref="ctaRef" class="text-center mt-8 sm:mt-12 md:mt-16">
        <NuxtLink
            to="/"
            class="inline-flex items-center px-5 sm:px-8 py-2.5 sm:py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-sm sm:text-base rounded-lg sm:rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
        >
          <Icon name="mdi:lightning-bolt" class="w-4 h-4 sm:w-5 sm:h-5 mr-2" />
          <span>Explore All Features</span>
          <Icon name="mdi:arrow-right" class="w-4 h-4 sm:w-5 sm:h-5 ml-2" />
        </NuxtLink>

        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-3 px-4">
          <Icon name="mdi:check-circle" class="inline w-3.5 h-3.5 text-purple-500 mr-1" />
          All features included • No hidden costs
        </p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const headerRef = ref<HTMLElement | null>(null)
const gridRef = ref<HTMLElement | null>(null)
const ctaRef = ref<HTMLElement | null>(null)

const features = ref([
  {
    title: "Smart Affiliate System",
    description: "Advanced affiliate marketing platform with real-time tracking, commission calculations, and comprehensive team management tools.",
    icon: "mdi:account-network",
    features: [
      "Real-time analytics dashboard",
      "Automated commission payouts",
      "Multi-tier team structure"
    ]
  },
  {
    title: "Job Portal Excellence",
    description: "Modern job board with AI-powered candidate matching, skill assessments, and seamless application management system.",
    icon: "mdi:briefcase-outline",
    features: [
      "AI-powered job matching",
      "Integrated video interviews",
      "Skill assessment tools"
    ]
  },
  {
    title: "eCommerce Powerhouse",
    description: "Complete online store solution with inventory management, multi-payment gateway integration, and advanced sales analytics.",
    icon: "mdi:cart-outline",
    features: [
      "Multi-vendor marketplace",
      "Mobile shopping app",
      "Real-time inventory sync"
    ]
  },
  {
    title: "Marketing Automation",
    description: "Sophisticated marketing suite with email campaign management, social media scheduling, and intelligent lead nurturing.",
    icon: "mdi:bullhorn-outline",
    features: [
      "Email campaign builder",
      "Social media scheduler",
      "Advanced lead scoring"
    ]
  },
  {
    title: "Service Management",
    description: "Professional service booking platform with calendar synchronization, comprehensive client management, and automated billing.",
    icon: "mdi:toolbox-outline",
    features: [
      "Online appointment booking",
      "Two-way calendar sync",
      "Recurring billing automation"
    ]
  },
  {
    title: "Analytics Dashboard",
    description: "Comprehensive business intelligence platform with real-time reporting, customizable dashboards, and actionable data insights.",
    icon: "mdi:chart-line",
    features: [
      "Real-time performance metrics",
      "Custom report builder",
      "Multi-format data export"
    ]
  }
])

onMounted(() => {
  if (process.client) {
    const options = {
      root: null,
      threshold: 0.1,
      rootMargin: '0px'
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in')
          observer.unobserve(entry.target)
        }
      })
    }, options)

    if (headerRef.value) observer.observe(headerRef.value)
    if (gridRef.value) observer.observe(gridRef.value)
    if (ctaRef.value) observer.observe(ctaRef.value)
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

/* Entrance animations */
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

/* Mobile optimizations */
@media (max-width: 640px) {
  .feature-card {
    /* Disable hover effects on mobile */
    transform: none !important;
  }

  .feature-card:hover {
    transform: none !important;
  }
}

/* Desktop hover effects */
@media (min-width: 641px) {
  .feature-card:hover {
    transform: translateY(-8px) scale(1.02);
  }
}
</style>
