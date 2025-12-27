<script setup lang="ts">
definePageMeta({
  layout: 'guest'
})

const config = useRuntimeConfig()
const appName = config.public.appName
const companyName = config.public.companyLegalName
</script>

<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 py-16">
      <UContainer>
        <div class="text-center text-white">
          <h1 class="text-4xl md:text-5xl font-bold mb-4">
            About Us
          </h1>
          <p class="text-xl text-white/80 max-w-2xl mx-auto">
            Empowering entrepreneurs through innovative network marketing and e-commerce solutions
          </p>
        </div>
      </UContainer>
    </div>

    <UContainer class="py-12">
      <!-- Mission Section -->
      <div class="glass-card p-8 mb-8">
        <div class="grid md:grid-cols-2 gap-8 items-center">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
              Our Mission
            </h2>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-4">
              At {{ appName }}, we believe in creating opportunities for everyone. Our platform combines the power of e-commerce with network marketing, enabling individuals to build sustainable businesses from anywhere.
            </p>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
              We're committed to providing high-quality products, fair compensation plans, and the tools needed for success in the modern digital economy.
            </p>
          </div>
          <div class="flex justify-center">
            <div class="w-48 h-48 bg-gradient-to-br from-violet-500 to-fuchsia-500 rounded-full flex items-center justify-center">
              <UIcon
                name="i-lucide-target"
                class="w-24 h-24 text-white"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Values Section -->
      <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="glass-card p-6 text-center">
          <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <UIcon
              name="i-lucide-heart-handshake"
              class="w-8 h-8 text-blue-600 dark:text-blue-400"
            />
          </div>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
            Trust & Integrity
          </h3>
          <p class="text-slate-600 dark:text-slate-400">
            Building lasting relationships through transparent and ethical business practices.
          </p>
        </div>

        <div class="glass-card p-6 text-center">
          <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <UIcon
              name="i-lucide-sparkles"
              class="w-8 h-8 text-green-600 dark:text-green-400"
            />
          </div>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
            Quality Products
          </h3>
          <p class="text-slate-600 dark:text-slate-400">
            Curating only the best products that deliver real value to our customers.
          </p>
        </div>

        <div class="glass-card p-6 text-center">
          <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <UIcon
              name="i-lucide-users"
              class="w-8 h-8 text-purple-600 dark:text-purple-400"
            />
          </div>
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">
            Community First
          </h3>
          <p class="text-slate-600 dark:text-slate-400">
            Fostering a supportive community where everyone can thrive and grow together.
          </p>
        </div>
      </div>

      <!-- Company Info -->
      <div class="glass-card p-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
          Company Information
        </h2>
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <h4 class="font-semibold text-slate-900 dark:text-white mb-2">
              Legal Name
            </h4>
            <p class="text-slate-600 dark:text-slate-400">
              {{ companyName }}
            </p>
          </div>
          <div>
            <h4 class="font-semibold text-slate-900 dark:text-white mb-2">
              Registered Address
            </h4>
            <p class="text-slate-600 dark:text-slate-400">
              123 Business Park, Suite 456<br>
              New Delhi, India - 110001
            </p>
          </div>
          <div>
            <h4 class="font-semibold text-slate-900 dark:text-white mb-2">
              Support Email
            </h4>
            <p class="text-slate-600 dark:text-slate-400">
              {{ config.public.supportEmail }}
            </p>
          </div>
          <div>
            <h4 class="font-semibold text-slate-900 dark:text-white mb-2">
              Support Phone
            </h4>
            <p class="text-slate-600 dark:text-slate-400">
              {{ config.public.supportPhone }}
            </p>
          </div>
        </div>
      </div>
    </UContainer>
  </div>
</template>
