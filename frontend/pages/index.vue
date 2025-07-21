<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100">

    <!-- Hero Section -->
    <section class="bg-white dark:bg-gray-900 py-20 px-6 text-center">
      <h1 class="text-4xl sm:text-5xl font-extrabold mb-4">
        Welcome to <span class="text-primary-600 dark:text-primary-400">Commernity</span>
      </h1>
      <p class="text-lg max-w-xl mx-auto text-gray-600 dark:text-gray-300">
        One platform to manage your MLM, Jobs, Marketing, eCommerce, Services, and Blog — all in one place.
      </p>
      <div class="mt-8">
        <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg transition shadow-lg hover:shadow-xl">
          Get Started
        </button>
      </div>
    </section>


    <!-- Why Choose Us Section -->
    <section class="bg-white dark:bg-gray-900 py-16 px-6">
      <h2 class="text-3xl font-bold text-center mb-10">Why Choose Commernity?</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">

        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded shadow hover:shadow-lg transition">
          <Icon name="mdi:rocket-launch" class="text-3xl mb-4 text-primary-600 dark:text-primary-400" />
          <h3 class="text-lg font-semibold mb-2">All-in-One Platform</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Manage your entire business—from jobs to eCommerce—all under one roof.
          </p>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded shadow hover:shadow-lg transition">
          <Icon name="mdi:shield-check" class="text-3xl mb-4 text-primary-600 dark:text-primary-400" />
          <h3 class="text-lg font-semibold mb-2">Secure & Reliable</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Your data is protected and our infrastructure boasts 99.9% uptime.
          </p>
        </div>

        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded shadow hover:shadow-lg transition">
          <Icon name="mdi:gift-outline" class="text-3xl mb-4 text-primary-600 dark:text-primary-400" />
          <h3 class="text-lg font-semibold mb-2">Exclusive Perks</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Subscribers enjoy early feature releases, referral bonuses, and loyalty rewards.
          </p>
        </div>

      </div>
    </section>



    <!-- 🧩 Features Section (Refactored to match card design) -->
    <section class="py-16 px-6">
      <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

        <!-- Job Portal -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:briefcase-outline" class="text-4xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Job Portal</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Post and apply for jobs in a modern job board experience.
          </p>
        </div>

        <!-- Services -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:toolbox-outline" class="text-4xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Services</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Manage and offer services with booking and tracking tools.
          </p>
        </div>

        <!-- Marketing Suite -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:bullhorn-outline" class="text-4xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Marketing Suite</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Automate campaigns, track leads, and boost engagement.
          </p>
        </div>

        <!-- eCommerce -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:cart-outline" class="text-4xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">eCommerce</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Sell products online with powerful inventory and cart system.
          </p>
        </div>

        <!-- Learning Hub -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:school-outline" class="text-4xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Learning Hub</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Access courses, tutorials, and skill-building paths for all users.
          </p>
        </div>

        <!-- Analytics Dashboard -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:chart-box-outline" class="text-4xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Analytics Dashboard</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Visualize key metrics with intuitive real-time dashboards.
          </p>
        </div>

      </div>
    </section>



    <!-- 🔢 App Stats Section -->
    <section class="bg-gray-100 dark:bg-gray-800 py-16 px-6 text-center">
      <h2 class="text-3xl font-bold mb-10">Why Teams Trust Commernity</h2>
      <div class="max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-6" id="stats">
        <div>
          <h3 class="text-3xl font-bold text-primary-600 dark:text-primary-400 counter">10K+</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Active Users</p>
        </div>
        <div>
          <h3 class="text-3xl font-bold text-primary-600 dark:text-primary-400 counter">99.9%</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Uptime</p>
        </div>
        <div>
          <h3 class="text-3xl font-bold text-primary-600 dark:text-primary-400 counter">500+</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Integrations</p>
        </div>
        <div>
          <h3 class="text-3xl font-bold text-primary-600 dark:text-primary-400 counter">120+</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Global Partners</p>
        </div>
      </div>
    </section>





    <!-- 🧭 Extended Services Section -->
    <section class="py-16 px-6">
      <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

        <!-- Support Center -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:lifebuoy" class="text-3xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Support Center</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Help articles, troubleshooting, and live support for customers.
          </p>
        </div>

        <!-- Affiliate Program -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:account-multiple-plus" class="text-3xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Affiliate Program</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Earn rewards by referring new users to Commernity.
          </p>
        </div>

        <!-- Knowledge Base -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
          <Icon name="mdi:book-open-variant" class="text-3xl mx-auto text-primary-600 dark:text-primary-400 mb-4" />
          <h3 class="font-semibold text-lg">Knowledge Base</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            Guides and documentation to help you succeed with every feature.
          </p>
        </div>

      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-white dark:bg-gray-900 py-20 px-6 text-center">
      <h2 class="text-3xl font-bold mb-4">Ready to grow your business?</h2>
      <p class="mb-6">Join thousands who trust Commernity to power their success.</p>
      <button class="bg-white text-blue-600 hover:bg-gray-100 px-6 py-3 rounded-lg font-semibold transition shadow hover:shadow-md">
        Create Free Account
      </button>
    </section>

  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import gsap from 'gsap'

onMounted(() => {
  gsap.from('#stats .counter', {
    textContent: 0,
    duration: 2,
    snap: { textContent: 1 },
    ease: 'power1.inOut',
    stagger: 0.3
  })
})
</script>