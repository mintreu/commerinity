<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">

    <!-- Enhanced Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
      <!-- Animated Background Elements -->
      <div class="absolute inset-0">
        <div class="hero-orb-1 absolute top-20 left-20 w-80 h-80 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-15 blur-3xl"></div>
        <div class="hero-orb-2 absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-20 blur-3xl"></div>
        <div class="hero-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-full opacity-10 blur-2xl"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 max-w-6xl mx-auto px-6 py-20">
        <div class="hero-content text-center space-y-8">
          <!-- Badge -->
          <div class="hero-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 backdrop-blur-sm">
            <Icon name="mdi:information" class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400" />
            <span class="font-semibold text-blue-700 dark:text-blue-300">Our Story</span>
          </div>

          <!-- Main Title -->
          <h1 class="hero-title text-4xl sm:text-6xl lg:text-7xl font-black leading-tight">
            <span class="block text-gray-900 dark:text-white">About</span>
            <span class="block bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">{{ companyName }}</span>
          </h1>

          <!-- Subtitle -->
          <p class="hero-subtitle text-lg sm:text-xl lg:text-2xl max-w-4xl mx-auto text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
            {{ companyName }} powers trusted commerce and innovation across communities. With a <span class="font-bold text-purple-600 dark:text-purple-400">privacy‑first approach</span> and transparent policies, we deliver <span class="font-bold text-blue-600 dark:text-blue-400">value</span>, <span class="font-bold text-emerald-600 dark:text-emerald-400">reliability</span>, and <span class="font-bold text-pink-600 dark:text-pink-400">respectful experiences</span> for every customer and partner.
          </p>

          <!-- Trust Indicators -->
          <div class="hero-stats grid grid-cols-2 sm:grid-cols-4 gap-6 pt-12 max-w-3xl mx-auto">
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 counter" data-target="99.9">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">% Uptime</div>
            </div>
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-purple-600 dark:text-purple-400 counter" data-target="50000">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">+ Customers</div>
            </div>
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 counter" data-target="24">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">/7 Support</div>
            </div>
            <div class="text-center">
              <div class="text-2xl sm:text-3xl font-bold text-pink-600 dark:text-pink-400 counter" data-target="100">0</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">% Secure</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="scroll-indicator absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <div class="w-6 h-10 border-2 border-gray-400 dark:border-gray-600 rounded-full flex justify-center">
          <div class="w-1 h-3 bg-gradient-to-b from-blue-600 to-transparent rounded-full mt-2"></div>
        </div>
      </div>
    </section>

    <!-- Enhanced Mission & Values -->
    <section class="mission-section py-20 px-6 bg-white dark:bg-gray-900 relative">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-200 dark:border-emerald-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:target" class="w-4 h-4 mr-2 text-emerald-600 dark:text-emerald-400" />
            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Mission & Values</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">What</span>
            <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent"> Drives Us</span>
          </h2>
        </div>

        <div class="values-grid grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(value, index) in missionValues" :key="value.title"
               class="value-card group relative bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

            <!-- Icon -->
            <div class="value-icon w-20 h-20 mb-6 rounded-2xl flex items-center justify-center text-3xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-6"
                 :style="{
                   background: `linear-gradient(135deg, ${value.color}20, ${value.color}40)`,
                   color: value.color,
                   boxShadow: `0 10px 30px ${value.color}20`
                 }">
              <Icon :name="value.icon" />
            </div>

            <!-- Content -->
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
              {{ value.title }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
              {{ value.description }}
            </p>

            <!-- Background Pattern -->
            <div class="absolute top-0 right-0 w-32 h-32 opacity-5 group-hover:opacity-10 transition-opacity duration-500">
              <Icon :name="value.icon" class="w-full h-full" :style="{ color: value.color }" />
            </div>

            <!-- Hover Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-3xl pointer-events-none"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced What We Do -->
    <section class="services-section py-20 px-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-950 dark:to-gray-900">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">What We</span><br>
            <span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Deliver</span>
          </h2>
        </div>

        <div class="services-grid grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div v-for="(service, index) in services" :key="service.title"
               class="service-card group relative bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

            <!-- Service Image/Icon -->
            <div class="service-visual relative mb-8 h-48 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-2xl flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform duration-500">
              <Icon :name="service.icon" class="text-6xl text-purple-600 dark:text-purple-400 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500" />
              <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>

            <!-- Content -->
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
              {{ service.title }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
              {{ service.description }}
            </p>

            <!-- Features List -->
            <ul class="space-y-2">
              <li v-for="feature in service.features" :key="feature" class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                <Icon name="mdi:check-circle" class="w-4 h-4 text-emerald-500 mr-3 flex-shrink-0" />
                {{ feature }}
              </li>
            </ul>

            <!-- Hover Border Effect -->
            <div class="absolute inset-0 rounded-3xl opacity-0 group-hover:opacity-100 transition-all duration-500 pointer-events-none"
                 style="box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.3);"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced How We Operate -->
    <section class="operations-section py-20 px-6 bg-white dark:bg-gray-900">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">How We</span><br>
            <span class="bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">Operate</span>
          </h2>
        </div>

        <div class="operations-grid grid grid-cols-1 md:grid-cols-2 gap-8">
          <div v-for="(operation, index) in operations" :key="operation.title"
               class="operation-card group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 border border-gray-200/50 dark:border-gray-700/50">

            <!-- Icon & Title -->
            <div class="flex items-center mb-6">
              <div class="operation-icon w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center text-white text-2xl mr-6 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                <Icon :name="operation.icon" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                {{ operation.title }}
              </h3>
            </div>

            <!-- Description -->
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
              {{ operation.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Leadership Team -->
    <section class="team-section py-20 px-6 bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 dark:from-purple-950/20 dark:via-pink-950/20 dark:to-blue-950/20">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <div class="section-badge inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-orange-500/10 to-red-500/10 border border-orange-200 dark:border-orange-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:account-group" class="w-4 h-4 mr-2 text-orange-600 dark:text-orange-400" />
            <span class="text-sm font-medium text-orange-700 dark:text-orange-300">Leadership</span>
          </div>
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Meet Our</span><br>
            <span class="bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">Leadership Team</span>
          </h2>
          <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
            The visionaries responsible for strategy, reliability, and customer trust.
          </p>
        </div>

        <div class="team-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="(leader, index) in leadership" :key="leader.title"
               class="team-card group relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-4 border border-white/50 dark:border-gray-700/50 text-center overflow-hidden">

            <!-- Profile Picture -->
            <div class="profile-container relative mb-6">
              <div class="profile-picture w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white text-2xl font-bold group-hover:scale-110 transition-transform duration-500">
                {{ leader.initials }}
              </div>
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-300">
              {{ leader.name }}
            </h3>
            <div class="text-sm font-semibold text-purple-600 dark:text-purple-400 mb-4">
              {{ leader.title }}
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-6">
              {{ leader.description }}
            </p>

            <!-- Skills -->
            <div class="flex flex-wrap justify-center gap-2">
              <span v-for="skill in leader.skills" :key="skill"
                    class="skill-tag px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs rounded-full">
                {{ skill }}
              </span>
            </div>

            <!-- Animated Background -->
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/5 to-pink-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl pointer-events-none"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Timeline/Milestones -->
    <section class="timeline-section py-20 px-6 bg-white dark:bg-gray-900 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5 dark:opacity-10">
        <div class="timeline-pattern w-full h-full" style="background-image: linear-gradient(45deg, currentColor 25%, transparent 25%), linear-gradient(-45deg, currentColor 25%, transparent 25%); background-size: 40px 40px;"></div>
      </div>

      <div class="relative z-10 max-w-4xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-5xl font-black mb-6">
            <span class="text-gray-900 dark:text-white">Our</span>
            <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent"> Journey</span>
          </h2>
        </div>

        <div class="timeline-container">
          <div v-for="(milestone, index) in milestones" :key="index"
               class="milestone-item group relative pl-12 pb-12 last:pb-0">

            <!-- Timeline Line -->
            <div class="absolute left-6 top-6 bottom-0 w-0.5 bg-gradient-to-b from-indigo-500 to-purple-500 last:hidden"
                 :class="index === milestones.length - 1 ? 'hidden' : ''"></div>

            <!-- Timeline Dot -->
            <div class="absolute left-3 top-3 w-6 h-6 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 shadow-lg group-hover:scale-125 transition-transform duration-300 flex items-center justify-center">
              <div class="w-2 h-2 bg-white rounded-full"></div>
            </div>

            <!-- Content -->
            <div class="milestone-content bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform group-hover:-translate-y-1 border border-gray-200/50 dark:border-gray-700/50">
              <div class="flex items-center mb-3">
                <Icon name="mdi:calendar" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 mr-3" />
                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ milestone.year }}</span>
              </div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ milestone.title }}</h3>
              <p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ milestone.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Social Proof/Stats -->
    <section class="stats-section py-20 px-6 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 relative overflow-hidden">
      <!-- Animated Background -->
      <div class="absolute inset-0">
        <div class="stats-particles">
          <div class="particle absolute w-2 h-2 bg-white rounded-full opacity-30"></div>
          <div class="particle absolute w-1 h-1 bg-yellow-300 rounded-full opacity-40"></div>
          <div class="particle absolute w-3 h-3 bg-pink-300 rounded-full opacity-20"></div>
        </div>
      </div>

      <div class="relative z-10 max-w-6xl mx-auto text-center text-white">
        <h2 class="text-3xl sm:text-5xl font-black mb-8">
          Numbers That <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-pink-300">Define Us</span>
        </h2>

        <div class="stats-grid grid grid-cols-2 md:grid-cols-4 gap-8 mt-16">
          <div class="stat-item transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black mb-2 counter" data-target="99.9">0</div>
            <div class="text-lg font-semibold text-yellow-200 mb-1">% Uptime</div>
            <div class="text-sm text-white/80">Guaranteed Reliability</div>
          </div>
          <div class="stat-item transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black mb-2">Trusted</div>
            <div class="text-lg font-semibold text-yellow-200 mb-1">Logistics</div>
            <div class="text-sm text-white/80">& Payment Partners</div>
          </div>
          <div class="stat-item transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black mb-2">100%</div>
            <div class="text-lg font-semibold text-yellow-200 mb-1">Clarity</div>
            <div class="text-sm text-white/80">In Policies & Docs</div>
          </div>
          <div class="stat-item transform hover:scale-110 transition-transform duration-300">
            <div class="stat-number text-4xl sm:text-6xl font-black mb-2">Secure</div>
            <div class="text-lg font-semibold text-yellow-200 mb-1">Data</div>
            <div class="text-sm text-white/80">& Wallet Protection</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Enhanced Call to Action -->
    <section class="cta-section py-24 px-6 bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 text-white relative overflow-hidden">
      <!-- Animated Background -->
      <div class="absolute inset-0">
        <div class="cta-particles">
          <div class="particle absolute w-2 h-2 bg-white rounded-full opacity-30 animate-pulse" style="top: 20%; left: 10%; animation-delay: 0s;"></div>
          <div class="particle absolute w-1 h-1 bg-blue-300 rounded-full opacity-40 animate-pulse" style="top: 60%; left: 80%; animation-delay: 1s;"></div>
          <div class="particle absolute w-3 h-3 bg-purple-300 rounded-full opacity-20 animate-pulse" style="top: 80%; left: 20%; animation-delay: 2s;"></div>
        </div>
      </div>

      <div class="relative z-10 max-w-5xl mx-auto text-center">
        <h2 class="cta-title text-4xl sm:text-6xl font-black mb-8 leading-tight">
          Questions? <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-400">We're Here</span><br>
          to Help
        </h2>

        <p class="cta-subtitle text-xl sm:text-2xl text-gray-300 mb-12 max-w-4xl mx-auto leading-relaxed">
          Reach out to our support team for policy guidance, order assistance, partnership inquiries, or anything else you need to know.
        </p>

        <div class="cta-actions flex flex-col sm:flex-row gap-6 justify-center items-center">
          <NuxtLink to="/contact"
                    class="cta-primary-btn group px-12 py-6 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 font-black text-xl rounded-2xl shadow-2xl hover:shadow-yellow-500/25 transition-all duration-300 transform hover:scale-105">
            <span class="relative z-10">Contact Us</span>
            <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
          </NuxtLink>

          <NuxtLink to="/policies"
                    class="cta-secondary-btn group px-12 py-6 bg-transparent border-2 border-white/30 text-white font-bold text-xl rounded-2xl hover:bg-white/10 transition-all duration-300 backdrop-blur-sm">
            <Icon name="mdi:file-document" class="inline w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
            View Policies
          </NuxtLink>
        </div>

        <!-- Trust Indicators -->
        <div class="trust-indicators flex flex-wrap justify-center items-center gap-8 mt-16 opacity-70">
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:shield-check" class="w-5 h-5 text-emerald-400 mr-2" />
            <span>Secure & Compliant</span>
          </div>
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:clock-fast" class="w-5 h-5 text-blue-400 mr-2" />
            <span>Quick Response</span>
          </div>
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:handshake" class="w-5 h-5 text-purple-400 mr-2" />
            <span>Transparent Process</span>
          </div>
          <div class="flex items-center text-gray-300">
            <Icon name="mdi:heart" class="w-5 h-5 text-pink-400 mr-2" />
            <span>Customer First</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

const config = useRuntimeConfig()
const companyName = config.public.companyName || 'Your Company'
const websiteName = config.public.websiteName || companyName

// Data
const missionValues = [
  {
    title: 'Customer Respect',
    description: 'Clear information, fair policies, and responsive support. We strive to earn trust in every step of the customer journey with transparency and integrity.',
    icon: 'mdi:account-heart',
    color: '#3b82f6' // blue-500
  },
  {
    title: 'Privacy‑First',
    description: 'Data is handled with the utmost care through transparent practices, minimal collection, secure storage, and fully compliant processing methods.',
    icon: 'mdi:shield-lock',
    color: '#10b981' // emerald-500
  },
  {
    title: 'Reliability',
    description: 'Robust operations with carefully vetted partners for shipping, payments, and verification to consistently deliver on our promises.',
    icon: 'mdi:check-circle',
    color: '#f59e0b' // amber-500
  }
]

const services = [
  {
    title: 'Commerce & Fulfillment',
    description: 'End‑to‑end order management with trusted third‑party logistics partners, providing accurate delivery timelines and complete transparency.',
    icon: 'mdi:package-variant',
    features: [
      'Real-time order tracking',
      'Vetted logistics partners',
      'Accurate delivery timelines',
      'Transparent shipping costs'
    ]
  },
  {
    title: 'Secure Payments & Wallet',
    description: 'PCI‑compliant payment processing with integrated wallet credits and secure bank withdrawals following the highest security standards.',
    icon: 'mdi:credit-card-secure',
    features: [
      'PCI-DSS compliant processing',
      'Instant wallet credits',
      'Secure bank withdrawals',
      'Multiple payment methods'
    ]
  },
  {
    title: 'Responsible Policies',
    description: 'Crystal clear terms for shipping, returns, and refunds with product‑specific eligibility and fair, consistent resolution processes.',
    icon: 'mdi:file-document-check',
    features: [
      'Clear return policies',
      'Fair refund processes',
      'Product-specific terms',
      'Consistent resolution'
    ]
  }
]

const operations = [
  {
    title: 'Shipping with Trusted Partners',
    description: 'Delivery is handled by carefully vetted third‑party carriers selected based on serviceability and proven route performance. We provide comprehensive tracking and clear status updates throughout the journey.',
    icon: 'mdi:truck-delivery'
  },
  {
    title: 'Returns & Refunds—Clarity First',
    description: 'Return eligibility is clearly defined on product pages before purchase. All approved refunds are processed to wallet credits first, with bank settlements typically completed within 15 business days.',
    icon: 'mdi:keyboard-return'
  },
  {
    title: 'Verification & Compliance',
    description: 'KYC and payout processes follow strict lawful verification standards with enterprise-grade encryption and compliant retention practices, balancing security with user convenience.',
    icon: 'mdi:shield-check'
  },
  {
    title: 'Support & Transparency',
    description: 'Comprehensive documentation and responsive customer service channels keep customers fully informed from initial purchase through delivery to post‑sale assistance.',
    icon: 'mdi:headset'
  }
]

const leadership = [
  {
    name: 'Chief Executive Officer',
    title: 'Head of Operations',
    description: 'Responsible for logistics optimization, SLA management, and maintaining the highest standards of partner quality and delivery performance.',
    initials: 'CEO',
    skills: ['Operations', 'Logistics', 'Strategy']
  },
  {
    name: 'Customer Success Director',
    title: 'Head of Customer Trust',
    description: 'Ensures policy clarity, fairness in all customer interactions, and maintains our commitment to respectful, supportive customer experiences.',
    initials: 'CSD',
    skills: ['Customer Success', 'Policy', 'Trust']
  },
  {
    name: 'Security & Compliance Officer',
    title: 'Head of Security & Compliance',
    description: 'Oversees data protection protocols, verification processes, and regular compliance audits to maintain the highest security standards.',
    initials: 'SCO',
    skills: ['Security', 'Compliance', 'Data Protection']
  }
]

const milestones = [
  {
    year: '2020',
    title: 'Company Foundation',
    description: 'Founded with a mission to bring clarity, respect, and transparency to everyday commerce experiences.'
  },
  {
    year: '2021',
    title: 'Logistics Partnership Expansion',
    description: 'Established partnerships with trusted logistics providers to significantly improve delivery coverage and reliability.'
  },
  {
    year: '2022',
    title: 'Wallet & Payment Innovation',
    description: 'Launched integrated wallet credits and secure withdrawal systems for faster refunds and improved customer experience.'
  },
  {
    year: '2023',
    title: 'Security Enhancement',
    description: 'Implemented enterprise-grade security protocols and achieved full PCI-DSS compliance for payment processing.'
  },
  {
    year: '2024',
    title: 'Scale & Growth',
    description: 'Expanded operations to serve 50,000+ customers while maintaining 99.9% uptime and exceptional service quality.'
  }
]

// Animations
onMounted(() => {
  if (process.client) {
    // Hero timeline animation
    const heroTl = gsap.timeline()

    heroTl.from('.hero-orb-1', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' })
        .from('.hero-orb-2', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' }, '-=1.5')
        .from('.hero-orb-3', { scale: 0, opacity: 0, duration: 2, ease: 'elastic.out(1, 0.5)' }, '-=1.5')
        .from('.hero-badge', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' })
        .from('.hero-title', { y: 100, opacity: 0, duration: 1.2, ease: 'back.out(1.7)' }, '-=0.8')
        .from('.hero-subtitle', { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' }, '-=0.6')
        .from('.hero-stats', { y: 30, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' }, '-=0.2')

    // Floating animations
    gsap.to('.hero-orb-1', {
      y: -30,
      rotation: 10,
      duration: 6,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    gsap.to('.hero-orb-2', {
      y: 40,
      rotation: -15,
      duration: 8,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    gsap.to('.hero-orb-3', {
      y: -20,
      rotation: 12,
      duration: 5,
      ease: 'power2.inOut',
      yoyo: true,
      repeat: -1
    })

    // Scroll-triggered animations
    const observerOptions = {
      start: 'top 80%',
      toggleActions: 'play none none reverse'
    }

    // Section badges
    gsap.fromTo('.section-badge',
        { y: 30, opacity: 0, scale: 0.8 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          duration: 0.8,
          ease: 'back.out(1.7)',
          scrollTrigger: {
            trigger: '.section-badge',
            ...observerOptions
          }
        }
    )

    // Mission values cards
    gsap.fromTo('.value-card',
        { y: 60, opacity: 0, scale: 0.9, rotateY: 10 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          rotateY: 0,
          duration: 0.8,
          ease: 'back.out(1.7)',
          stagger: 0.2,
          scrollTrigger: {
            trigger: '.values-grid',
            ...observerOptions
          }
        }
    )

    // Service cards
    gsap.fromTo('.service-card',
        { y: 80, opacity: 0, scale: 0.9 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          duration: 1,
          ease: 'back.out(1.7)',
          stagger: 0.3,
          scrollTrigger: {
            trigger: '.services-grid',
            ...observerOptions
          }
        }
    )

    // Operations cards
    gsap.fromTo('.operation-card',
        { x: -50, opacity: 0 },
        {
          x: 0,
          opacity: 1,
          duration: 0.8,
          ease: 'power2.out',
          stagger: 0.2,
          scrollTrigger: {
            trigger: '.operations-grid',
            ...observerOptions
          }
        }
    )

    // Team cards
    gsap.fromTo('.team-card',
        { y: 60, opacity: 0, scale: 0.8, rotateX: 15 },
        {
          y: 0,
          opacity: 1,
          scale: 1,
          rotateX: 0,
          duration: 0.8,
          ease: 'back.out(1.7)',
          stagger: 0.15,
          scrollTrigger: {
            trigger: '.team-grid',
            ...observerOptions
          }
        }
    )

    // Timeline milestones
    gsap.fromTo('.milestone-item',
        { x: -100, opacity: 0 },
        {
          x: 0,
          opacity: 1,
          duration: 0.8,
          ease: 'power2.out',
          stagger: 0.3,
          scrollTrigger: {
            trigger: '.timeline-container',
            ...observerOptions
          }
        }
    )

    // Stats counters
    const counters = document.querySelectorAll('.counter')
    counters.forEach(counter => {
      const target = parseFloat(counter.getAttribute('data-target') || counter.textContent || '0')

      gsap.fromTo(counter,
          { textContent: 0 },
          {
            textContent: target,
            duration: 2,
            ease: 'power2.out',
            snap: { textContent: target < 100 ? 0.1 : 1 },
            scrollTrigger: {
              trigger: counter,
              ...observerOptions
            }
          }
      )
    })

    // CTA section
    gsap.fromTo('.cta-title, .cta-subtitle, .cta-actions',
        { y: 40, opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 1,
          ease: 'back.out(1.7)',
          stagger: 0.3,
          scrollTrigger: {
            trigger: '.cta-section',
            ...observerOptions
          }
        }
    )

    // Particle animations
    gsap.to('.stats-particles .particle, .cta-particles .particle', {
      y: -100,
      opacity: 0,
      duration: 4,
      ease: 'power2.out',
      stagger: 0.8,
      repeat: -1,
      repeatDelay: 2
    })
  }
})
</script>

<style scoped>
/* Custom animations and effects */
.value-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.6s;
}

.value-card:hover::before {
  left: 100%;
}

.service-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #8b5cf6, #ec4899, #06b6d4);
  transform: scaleX(0);
  transition: transform 0.4s ease;
  border-radius: 1.5rem 1.5rem 0 0;
}

.service-card:hover::after {
  transform: scaleX(1);
}

.team-card::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(147, 51, 234, 0.3), transparent);
  border-radius: 1.5rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.team-card:hover::before {
  opacity: 1;
}

.cta-primary-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.cta-primary-btn:hover::before {
  left: 100%;
}

.milestone-content::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(to bottom, #6366f1, #8b5cf6);
  border-radius: 0 1rem 1rem 0;
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.milestone-item:hover .milestone-content::before {
  transform: scaleY(1);
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .service-card, .value-card, .team-card, .operation-card {
    padding: 1.5rem;
  }

  .hero-orb-2 {
    display: none;
  }

  .timeline-container .milestone-item {
    padding-left: 2rem;
  }
}

@media (max-width: 480px) {
  .hero-orb-1, .hero-orb-3 {
    width: 8rem;
    height: 8rem;
  }

  .service-visual {
    height: 8rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .value-card, .service-card, .team-card, .operation-card, .milestone-content {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Animation keyframes */
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
}

.floating {
  animation: float 4s ease-in-out infinite;
}
</style>
