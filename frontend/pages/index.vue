<template>
  <div class="w-full mx-auto pt-16 md:pt-20 bg-gradient-to-br from-gray-50 via-purple-50 to-pink-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">

    <!-- Hero Section -->
    <section class="hero-section w-full mx-auto flex flex-col items-center justify-center py-12 sm:py-16 md:py-20 lg:py-24 min-h-[calc(100vh-4rem)] md:min-h-[calc(100vh-5rem)] relative">
      <div class="w-full  px-4 sm:px-6 md:px-8 lg:px-12 relative z-10">
        <div class="hero-content flex flex-col items-center text-center space-y-4 sm:space-y-6 lg:space-y-8">

          <!-- Badge -->
          <div class="hero-badge inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm">
            <Icon name="mdi:rocket-launch" class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5 sm:mr-2 text-purple-600 dark:text-purple-400" />
            <span class="text-xs sm:text-sm font-medium text-purple-700 dark:text-purple-300 whitespace-nowrap">All-in-One Platform</span>
          </div>

          <!-- Title -->
          <h1 class="hero-title w-full font-black leading-tight text-2xl sm:text-3xl md:text-5xl lg:text-6xl xl:text-7xl">
            <span class="block text-gray-900 dark:text-white">Transform Your</span>
            <span class="block bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 bg-clip-text text-transparent">Business Dreams</span>
            <span class="block text-gray-900 dark:text-white">Into Reality</span>
          </h1>

          <!-- Subtitle -->
          <p class="hero-subtitle w-full max-w-4xl text-gray-600 dark:text-gray-300 font-medium text-sm sm:text-base md:text-lg lg:text-xl leading-relaxed">
            The ultimate platform combining
            <span class="font-bold text-purple-600 dark:text-purple-400">Affiliation</span>,
            <span class="font-bold text-blue-600 dark:text-blue-400">Jobs</span>,
            <span class="font-bold text-pink-600 dark:text-pink-400">eCommerce</span>, and
            <span class="font-bold text-purple-600 dark:text-purple-400">Marketing</span>
          </p>

          <!-- CTA Buttons -->
          <div class="hero-cta w-full flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center pt-4 sm:pt-6">
            <NuxtLink
                to="/auth/register"
                class="w-full sm:w-auto group px-6 py-3 sm:px-8 sm:py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-sm sm:text-base rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95"
            >
              Start Your Journey
            </NuxtLink>

            <NuxtLink
                to="/about"
                class="w-full sm:w-auto group px-6 py-3 sm:px-8 sm:py-4 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold text-sm sm:text-base rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all duration-300"
            >
              <Icon name="mdi:play-circle" class="inline w-4 h-4 sm:w-5 sm:h-5 mr-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors" />
              Learn More
            </NuxtLink>
          </div>

          <!-- Stats -->
          <div class="hero-stats w-full max-w-2xl grid grid-cols-2 gap-4 sm:gap-6 pt-8 sm:pt-12">
            <div class="flex flex-col items-center">
              <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-purple-600 dark:text-purple-400 counter" data-target="15">0</div>
              <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Happy Users</div>
            </div>
            <div class="flex flex-col items-center">
              <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-blue-600 dark:text-blue-400 counter" data-target="99.9">0</div>
              <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">% Uptime</div>
            </div>
            <div class="flex flex-col items-center">
              <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-pink-600 dark:text-pink-400 counter" data-target="1000">0</div>
              <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">+ Features</div>
            </div>
            <div class="flex flex-col items-center">
              <div class="text-2xl sm:text-3xl lg:text-4xl font-black text-purple-600 dark:text-purple-400 counter" data-target="24">0</div>
              <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">/7 Support</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="scroll-indicator hidden sm:block mt-8 lg:mt-12">
        <div class="w-6 h-10 border-2 border-gray-400 dark:border-gray-600 rounded-full flex justify-center">
          <div class="w-1 h-3 bg-gradient-to-b from-purple-600 to-transparent rounded-full mt-2 animate-bounce" />
        </div>
      </div>
    </section>

    <!-- Component Sections -->
    <AffiliateBenefitsSection />
    <BestSaleProducts />

    <FeaturedProductsSection />

    <div class=" overflow-x-hidden mx-auto">
      <EnhancedFeaturesSection />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import BestSaleProducts from '~/components/store/BestSaleProducts.vue'
import FeaturedProductsSection from '~/components/store/TreandingProductsSection.vue'
import AffiliateBenefitsSection from '~/components/home/AffiliateBenefitsSection.vue'
import EnhancedFeaturesSection from '~/components/home/EnhancedFeaturesSection.vue'

if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

onMounted(() => {
  if (process.client) {
    // Hero Section Animation
    const tl = gsap.timeline()
    tl.from('.hero-badge', {
      y: 50,
      opacity: 0,
      duration: 1,
      ease: 'back.out(1.7)'
    })
        .from('.hero-title', {
          y: 100,
          opacity: 0,
          duration: 1.2,
          ease: 'back.out(1.7)'
        }, '-=0.8')
        .from('.hero-subtitle', {
          y: 50,
          opacity: 0,
          duration: 1,
          ease: 'back.out(1.7)'
        }, '-=0.6')
        .from('.hero-cta', {
          y: 50,
          opacity: 0,
          duration: 1,
          ease: 'back.out(1.7)'
        }, '-=0.4')
        .from('.hero-stats', {
          y: 30,
          opacity: 0,
          duration: 0.8,
          ease: 'back.out(1.7)'
        }, '-=0.2')

    // Counter Animation
    const counters = document.querySelectorAll('.counter')
    counters.forEach(counter => {
      const target = parseFloat(counter.getAttribute('data-target') || '0')
      gsap.fromTo(
          counter,
          { textContent: 0 },
          {
            textContent: target,
            duration: 2,
            ease: 'power2.out',
            snap: { textContent: target >= 10 ? 1 : 0.1 },
            scrollTrigger: {
              trigger: counter,
              start: 'top 80%',
              once: true
            }
          }
      )
    })

    // Scroll Indicator Animation
    gsap.to('.scroll-indicator', {
      y: 10,
      duration: 1.5,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })
  }
})
</script>

<style scoped>
/* Hero Background Orbs */
.hero-section::before,
.hero-section::after {
  content: '';
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  pointer-events: none;
  z-index: 0;
  animation: float 8s ease-in-out infinite;
}

.hero-section::before {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  top: 10%;
  left: -100px;
}

.hero-section::after {
  width: 500px;
  height: 500px;
  background: linear-gradient(135deg, #3b82f6, #ec4899);
  bottom: 10%;
  right: -150px;
  animation-delay: -4s;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
  }
  33% {
    transform: translate(30px, -30px) rotate(15deg);
  }
  66% {
    transform: translate(-20px, 20px) rotate(-10deg);
  }
}

/* Mobile Optimizations */
@media (max-width: 640px) {
  .hero-section::before,
  .hero-section::after {
    opacity: 0.08;
  }
}
</style>
