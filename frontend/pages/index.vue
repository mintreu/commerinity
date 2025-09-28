<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">

    <!-- Hero Section with Enhanced Animations -->
    <section class="relative min-h-screen flex items-center justify-center px-6 py-20 overflow-hidden">
      <!-- Animated Background Elements -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="floating-orb absolute top-20 left-20 w-64 h-64 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-20 blur-3xl"></div>
        <div class="floating-orb-2 absolute bottom-20 right-20 w-80 h-80 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-20 blur-3xl"></div>
        <div class="floating-orb-3 absolute top-1/2 left-1/3 w-48 h-48 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-15 blur-2xl"></div>
      </div>

      <div class="relative z-10 text-center max-w-6xl mx-auto">
        <!-- Main Hero Content -->
        <div class="hero-content space-y-8">
          <div class="hero-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-200 dark:border-purple-800 backdrop-blur-sm">
            <Icon name="mdi:rocket-launch" class="w-4 h-4 mr-2 text-purple-600 dark:text-purple-400" />
            <span class="text-sm font-medium text-purple-700 dark:text-purple-300">All-in-One Business Platform</span>
          </div>

          <h1 class="hero-title text-4xl sm:text-6xl lg:text-7xl font-black leading-tight">
            <span class="block text-gray-900 dark:text-white">Transform Your</span>
            <span class="block bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 bg-clip-text text-transparent">Business Dreams</span>
            <span class="block text-gray-900 dark:text-white">Into Reality</span>
          </h1>

          <p class="hero-subtitle text-lg sm:text-xl lg:text-2xl max-w-4xl mx-auto text-gray-600 dark:text-gray-300 font-medium">
            The ultimate platform combining <span class="font-bold text-purple-600 dark:text-purple-400">Affiliation</span>,
            <span class="font-bold text-blue-600 dark:text-blue-400">jobs</span>,
            <span class="font-bold text-pink-600 dark:text-pink-400">eCommerce</span>, and
            <span class="font-bold text-emerald-600 dark:text-emerald-400">Marketing</span>
            in one powerful ecosystem.
          </p>

          <div class="hero-cta flex flex-col sm:flex-row gap-4 justify-center items-center pt-8">
            <button class="cta-primary group relative px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-2xl shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-105">
              <span class="relative z-10">Start Your Journey</span>
              <div class="absolute inset-0 bg-gradient-to-r from-purple-700 to-pink-700 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>

            <button class="cta-secondary group px-8 py-4 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold rounded-2xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-300 backdrop-blur-sm">
              <Icon name="mdi:play-circle" class="inline w-5 h-5 mr-2 group-hover:text-purple-600 transition-colors duration-300" />
              Learn More
            </button>
          </div>

          <!-- Trust Indicators -->
          <div class="hero-stats pt-12 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-2xl mx-auto">
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-purple-600 dark:text-purple-400 counter" data-target="15">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Happy Users</div>
            </div>
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 counter" data-target="99.9">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">% Uptime</div>
            </div>
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-pink-600 dark:text-pink-400 counter" data-target="1000">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">+ Features</div>
            </div>
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 counter" data-target="24">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">/7 Support</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="scroll-indicator absolute bottom-8 left-1/2 transform -translate-x-1/2">
        <div class="w-6 h-10 border-2 border-gray-400 dark:border-gray-600 rounded-full flex justify-center">
          <div class="w-1 h-3 bg-gradient-to-b from-purple-600 to-transparent rounded-full mt-2 animate-bounce"></div>
        </div>
      </div>
    </section>

    <!-- Featured Products Section -->
    <section class="products-section py-20 px-6 bg-white dark:bg-gray-900 relative">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:store" class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" />
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Featured Products</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Discover Amazing</span><br>
            <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Products & Services</span>
          </h2>
          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">Explore our curated collection of premium products and services designed to accelerate your success</p>
        </div>

        <!-- Products Swiper -->
        <div class="products-swiper relative">
          <Swiper
              :modules="[SwiperAutoplay, SwiperEffectCoverflow, SwiperPagination]"
              :slides-per-view="1"
              :space-between="30"
              :centered-slides="true"
              :autoplay="{
              delay: 4000,
              disableOnInteraction: false,
            }"
              :effect="'coverflow'"
              :coverflow-effect="{
              rotate: 15,
              stretch: 0,
              depth: 100,
              modifier: 1,
              slideShadows: true,
            }"
              :pagination="{
              clickable: true,
              dynamicBullets: true,
            }"
              :breakpoints="{
              640: { slidesPerView: 2 },
              1024: { slidesPerView: 3 },
            }"
              class="pb-12"
          >
            <SwiperSlide v-for="(product, index) in featuredProducts" :key="index">
              <div class="product-card group relative bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden transform hover:scale-105 p-8 h-96">
                <!-- Product Icon/Image -->
                <div class="relative mb-6">
                  <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center group-hover:rotate-6 transition-transform duration-500">
                    <Icon :name="product.icon" class="w-10 h-10 text-white" />
                  </div>
                  <div class="absolute -top-2 -right-2 w-6 h-6 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full animate-pulse"></div>
                </div>

                <!-- Product Info -->
                <div class="space-y-4">
                  <h3 class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
                    {{ product.title }}
                  </h3>
                  <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ product.description }}
                  </p>
                  <div class="flex items-center justify-between pt-4">
                    <div class="text-3xl font-black text-purple-600 dark:text-purple-400">
                      {{ product.price }}
                    </div>
                    <div class="flex items-center space-x-1">
                      <Icon name="mdi:star" class="w-4 h-4 text-yellow-400" />
                      <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ product.rating }}</span>
                    </div>
                  </div>
                </div>

                <!-- Hover Effects -->
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600/10 to-pink-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
                <div class="absolute bottom-4 left-8 right-8 transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                  <button class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl hover:shadow-lg transition-shadow duration-300">
                    View Details
                  </button>
                </div>
              </div>
            </SwiperSlide>
          </Swiper>
        </div>

        <!-- Store CTA -->
        <div class="text-center mt-16">
          <NuxtLink to="/store" class="store-cta inline-flex items-center px-12 py-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-xl rounded-2xl shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105 group">
            <Icon name="mdi:storefront" class="w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
            Explore Full Store
            <Icon name="mdi:arrow-right" class="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Enhanced Features Grid -->
    <section class="features-section py-20 px-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-950 dark:to-gray-900">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Powerful Features</span><br>
            <span class="bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent">Built for Success</span>
          </h2>
        </div>

        <div class="features-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="(feature, index) in enhancedFeatures" :key="index"
               class="feature-card group relative bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 overflow-hidden">

            <!-- Background Pattern -->
            <div class="absolute top-0 right-0 w-32 h-32 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
              <Icon :name="feature.icon" class="w-full h-full text-purple-600" />
            </div>

            <!-- Icon -->
            <div class="relative mb-6">
              <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500">
                <Icon :name="feature.icon" class="w-8 h-8 text-white" />
              </div>
            </div>

            <!-- Content -->
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
              {{ feature.title }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
              {{ feature.description }}
            </p>

            <!-- Features List -->
            <ul class="space-y-2">
              <li v-for="item in feature.features" :key="item" class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-500 mr-3" />
                {{ item }}
              </li>
            </ul>

            <!-- Hover Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/5 to-pink-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Social Proof & Testimonials -->
    <section class="testimonials-section py-20 px-6 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Trusted by</span><br>
            <span class="bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">Industry Leaders</span>
          </h2>
        </div>

        <!-- Testimonials Swiper -->
        <Swiper
            :modules="[SwiperAutoplay]"
            :slides-per-view="1"
            :space-between="30"
            :autoplay="{
            delay: 5000,
            disableOnInteraction: false,
          }"
            :breakpoints="{
            768: { slidesPerView: 2 },
            1024: { slidesPerView: 3 },
          }"
            class="testimonials-swiper"
        >
          <SwiperSlide v-for="(testimonial, index) in testimonials" :key="index">
            <div class="testimonial-card bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-800 dark:to-purple-900/20 rounded-3xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 h-80 flex flex-col justify-between">
              <!-- Quote -->
              <div class="flex-1">
                <Icon name="mdi:format-quote-open" class="w-8 h-8 text-purple-400 mb-4" />
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6 italic">
                  "{{ testimonial.quote }}"
                </p>
              </div>

              <!-- Author -->
              <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                  {{ testimonial.author.charAt(0) }}
                </div>
                <div class="ml-4">
                  <h4 class="font-bold text-gray-900 dark:text-white">{{ testimonial.author }}</h4>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ testimonial.role }}</p>
                </div>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>
      </div>
    </section>

    <!-- Enhanced Stats Section -->
    <section class="stats-section py-20 px-6 bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 relative overflow-hidden">
      <!-- Animated Background -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-full h-full bg-black/20"></div>
        <div class="stats-bg-pattern absolute inset-0 opacity-10"></div>
      </div>

      <div class="relative z-10 max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black text-white mb-6">
            Numbers That Speak<br>
            <span class="text-yellow-300">Our Success</span>
          </h2>
        </div>

        <div id="enhanced-stats" class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="stat-item text-center transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black text-white counter mb-2" data-target="15">0</div>
            <div class="text-lg font-medium text-yellow-200">Active Users</div>
            <div class="text-sm text-white/80">Growing Daily</div>
          </div>
          <div class="stat-item text-center transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black text-white">99.9<span class="text-2xl">%</span></div>
            <div class="text-lg font-medium text-yellow-200">Uptime</div>
            <div class="text-sm text-white/80">Guaranteed</div>
          </div>
          <div class="stat-item text-center transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black text-white counter" data-target="1000">0</div>
            <div class="text-lg font-medium text-yellow-200">Features</div>
            <div class="text-sm text-white/80">And Growing</div>
          </div>
          <div class="stat-item text-center transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black text-white counter" data-target="2">0</div>
            <div class="text-lg font-medium text-yellow-200">Countries</div>
            <div class="text-sm text-white/80">Worldwide</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Final CTA Section -->
    <section class="final-cta py-24 px-6 bg-gradient-to-br from-gray-900 via-purple-900 to-pink-900 text-white relative overflow-hidden">
      <!-- Animated Particles -->
      <div class="particles-container absolute inset-0">
        <div class="particle absolute w-2 h-2 bg-white rounded-full opacity-30"></div>
        <div class="particle absolute w-1 h-1 bg-purple-300 rounded-full opacity-40"></div>
        <div class="particle absolute w-3 h-3 bg-pink-300 rounded-full opacity-20"></div>
      </div>

      <div class="relative z-10 max-w-4xl mx-auto text-center">
        <h2 class="text-4xl sm:text-6xl font-black mb-8">
          Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-400">Transform</span><br>
          Your Business?
        </h2>
        <p class="text-xl sm:text-2xl text-gray-300 mb-12 max-w-3xl mx-auto leading-relaxed">
          Join thousands of successful entrepreneurs who have already transformed their business with {{ websiteName }}. Your journey to success starts now.
        </p>

        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
          <button class="final-cta-btn group px-12 py-6 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 font-black text-xl rounded-2xl shadow-2xl hover:shadow-yellow-500/25 transition-all duration-300 transform hover:scale-105">
            <span class="relative z-10">Start Free Today</span>
            <Icon name="mdi:rocket" class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
          </button>

          <div class="flex items-center text-gray-300">
            <Icon name="mdi:check-circle" class="w-5 h-5 text-emerald-400 mr-2" />
            <span>No credit card required</span>
          </div>
        </div>

        <!-- Trust Badges -->
        <div class="flex flex-wrap justify-center items-center gap-8 mt-16 opacity-60">
          <div class="flex items-center">
            <Icon name="mdi:shield-check" class="w-6 h-6 text-emerald-400 mr-2" />
            <span>Enterprise Security</span>
          </div>
          <div class="flex items-center">
            <Icon name="mdi:support" class="w-6 h-6 text-blue-400 mr-2" />
            <span>24/7 Support</span>
          </div>
          <div class="flex items-center">
            <Icon name="mdi:medal" class="w-6 h-6 text-yellow-400 mr-2" />
            <span>Award Winning</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectCoverflow, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/effect-coverflow'
import 'swiper/css/pagination'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

const config = useRuntimeConfig()
const websiteName = config.public.websiteName

// Swiper modules
const SwiperAutoplay = Autoplay
const SwiperEffectCoverflow = EffectCoverflow
const SwiperPagination = Pagination

// Data
const featuredProducts = ref([
  {
    title: "Business Starter Pack",
    description: "Everything you need to launch your business with MLM tools, job portal access, and basic marketing suite.",
    price: "$49/mo",
    rating: "4.9",
    icon: "mdi:rocket-launch"
  },
  {
    title: "eCommerce Pro Suite",
    description: "Complete eCommerce solution with inventory management, payment processing, and advanced analytics.",
    price: "$99/mo",
    rating: "4.8",
    icon: "mdi:store"
  },
  {
    title: "Marketing Mastery",
    description: "Advanced marketing automation, lead generation, and campaign management tools for serious marketers.",
    price: "$149/mo",
    rating: "4.9",
    icon: "mdi:chart-line"
  },
  {
    title: "Enterprise Solution",
    description: "Full-scale business management with unlimited features, priority support, and custom integrations.",
    price: "$299/mo",
    rating: "5.0",
    icon: "mdi:office-building"
  },
  {
    title: "Learning Platform",
    description: "Comprehensive educational resources, courses, and skill development programs for continuous growth.",
    price: "$79/mo",
    rating: "4.7",
    icon: "mdi:school"
  }
])

const enhancedFeatures = ref([
  {
    title: "Smart MLM System",
    description: "Advanced multi-level marketing platform with real-time tracking, commission calculations, and team management.",
    icon: "mdi:account-network",
    features: ["Real-time Analytics", "Automated Payouts", "Team Visualization"]
  },
  {
    title: "Job Portal Excellence",
    description: "Modern job board with AI-powered matching, skill assessments, and seamless application management.",
    icon: "mdi:briefcase",
    features: ["AI Job Matching", "Video Interviews", "Skill Testing"]
  },
  {
    title: "eCommerce Powerhouse",
    description: "Complete online store solution with inventory management, multi-payment gateways, and sales analytics.",
    icon: "mdi:cart",
    features: ["Multi-vendor Support", "Mobile App", "Inventory Tracking"]
  },
  {
    title: "Marketing Automation",
    description: "Sophisticated marketing tools for email campaigns, social media management, and lead nurturing.",
    icon: "mdi:bullhorn",
    features: ["Email Campaigns", "Social Scheduling", "Lead Scoring"]
  },
  {
    title: "Service Management",
    description: "Professional service booking system with calendar integration, client management, and billing.",
    icon: "mdi:toolbox",
    features: ["Online Booking", "Calendar Sync", "Automated Billing"]
  },
  {
    title: "Analytics Dashboard",
    description: "Comprehensive business intelligence with real-time reporting, custom dashboards, and data insights.",
    icon: "mdi:chart-box",
    features: ["Real-time Reports", "Custom Dashboards", "Data Export"]
  }
])

const testimonials = ref([
  {
    quote: "This platform revolutionized our business operations. The MLM system alone increased our team productivity by 300% and the integrated analytics help us make better decisions daily.",
    author: "Sarah Johnson",
    role: "CEO, TechStart Inc"
  },
  {
    quote: "The job portal feature helped us find top talent quickly. The AI matching system saved us countless hours in recruitment and the candidates quality improved dramatically.",
    author: "Michael Chen",
    role: "HR Director, Innovation Labs"
  },
  {
    quote: "Moving our eCommerce to this platform was the best decision we made. Sales increased by 250% in just 3 months thanks to the powerful marketing automation tools.",
    author: "Emma Rodriguez",
    role: "Founder, Style Hub"
  },
  {
    quote: "The all-in-one approach eliminated the need for multiple tools. We now manage everything from one dashboard - it's incredibly efficient and cost-effective.",
    author: "David Kim",
    role: "Operations Manager, Growth Co"
  }
])

onMounted(() => {
  if (process.client) {
    // Hero animations
    const tl = gsap.timeline()

    tl.from('.floating-orb', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' })
        .from('.floating-orb-2', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' }, '-=1.5')
        .from('.floating-orb-3', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' }, '-=1.5')
        .from('.hero-badge', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' })
        .from('.hero-title', { y: 100, opacity: 0, duration: 1.2, ease: 'back.out(1.7)' }, '-=0.8')
        .from('.hero-subtitle', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' }, '-=0.6')
        .from('.hero-cta', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' }, '-=0.4')
        .from('.hero-stats', { y: 30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' }, '-=0.2')

    // Floating animation for orbs
    gsap.to('.floating-orb', {
      y: -30,
      rotation: 10,
      duration: 4,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    gsap.to('.floating-orb-2', {
      y: 30,
      rotation: -10,
      duration: 5,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    gsap.to('.floating-orb-3', {
      y: -20,
      rotation: 15,
      duration: 3,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    // Scroll-triggered animations
    gsap.fromTo('.section-badge',
        { y: 50, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 1,
          ease: 'back.out(1.7)',
          scrollTrigger: {
            trigger: '.section-badge',
            start: 'top 80%',
            toggleActions: 'play none none reverse'
          }
        }
    )

    // Feature cards animation
    gsap.fromTo('.feature-card',
        { y: 100, opacity: 0, scale: 0.8 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          duration: 1,
          ease: 'back.out(1.7)',
          stagger: 0.2,
          scrollTrigger: {
            trigger: '.features-grid',
            start: 'top 80%',
            toggleActions: 'play none none reverse'
          }
        }
    )

    // Product cards animation
    gsap.fromTo('.products-swiper',
        { y: 100, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 1.2,
          ease: 'back.out(1.7)',
          scrollTrigger: {
            trigger: '.products-section',
            start: 'top 80%',
            toggleActions: 'play none none reverse'
          }
        }
    )

    // Stats counter animation
    const counters = document.querySelectorAll('.counter')
    counters.forEach(counter => {
      const target = parseInt(counter.getAttribute('data-target') || counter.textContent || '0')

      gsap.fromTo(counter,
          { textContent: 0 },
          {
            textContent: target,
            duration: 2,
            ease: 'power2.out',
            snap: { textContent: 1 },
            scrollTrigger: {
              trigger: counter,
              start: 'top 80%',
              toggleActions: 'play none none reverse'
            }
          }
      )
    })

    // Particle animation
    gsap.to('.particle', {
      y: -100,
      opacity: 0,
      duration: 3,
      ease: 'power2.out',
      stagger: 0.5,
      repeat: -1
    })

    // Scroll indicator animation
    gsap.to('.scroll-indicator', {
      y: 10,
      duration: 1.5,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    // Testimonials animation
    gsap.fromTo('.testimonial-card',
        { y: 50, opacity: 0, rotateY: 15 },
        {
          y: 0,
          opacity: 1,
          rotateY: 0,
          duration: 1,
          ease: 'back.out(1.7)',
          stagger: 0.3,
          scrollTrigger: {
            trigger: '.testimonials-section',
            start: 'top 80%',
            toggleActions: 'play none none reverse'
          }
        }
    )
  }
})
</script>

<style scoped>
/* Custom animations and effects */
.hero-content {
  animation: fadeInUp 1s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cta-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.cta-primary:hover::before {
  left: 100%;
}

.product-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(147, 51, 234, 0.1), rgba(219, 39, 119, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 1.5rem;
}

.product-card:hover::before {
  opacity: 1;
}

.feature-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #8b5cf6, #ec4899, #06b6d4);
  transform: scaleX(0);
  transition: transform 0.3s ease;
  border-radius: 1.5rem 1.5rem 0 0;
}

.feature-card:hover::after {
  transform: scaleX(1);
}

.stats-bg-pattern {
  background-image: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px);
  background-size: 30px 30px;
  animation: movePattern 20s linear infinite;
}

@keyframes movePattern {
  0% { transform: translate(0, 0); }
  100% { transform: translate(30px, 30px); }
}

.particles-container .particle {
  animation: floatUp 4s ease-in infinite;
}

.particles-container .particle:nth-child(1) {
  top: 80%;
  left: 20%;
  animation-delay: 0s;
}

.particles-container .particle:nth-child(2) {
  top: 70%;
  left: 60%;
  animation-delay: 1.5s;
}

.particles-container .particle:nth-child(3) {
  top: 85%;
  left: 80%;
  animation-delay: 3s;
}

@keyframes floatUp {
  0% {
    transform: translateY(0) rotate(0deg);
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    transform: translateY(-100px) rotate(360deg);
    opacity: 0;
  }
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .product-card {
    height: auto;
    min-height: 350px;
  }

  .feature-card {
    padding: 1.5rem;
  }
}

/* Responsive visibility controls for mobile-first design */
@media (max-width: 768px) {
  .floating-orb-2 {
    display: none;
  }

  .stats-section .grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .floating-orb,
  .floating-orb-3 {
    width: 8rem;
    height: 8rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .product-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }

  .feature-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
  }
}
</style>
