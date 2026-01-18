<script setup lang="ts">
const config = useRuntimeConfig()
</script>

<template>
  <section class="hero-section flex flex-col items-center justify-center py-16 md:py-24 lg:py-32 relative">
    <!-- Floating Background Orbs -->
    <div class="floating-orb floating-orb-primary orb-lg top-[10%] -left-[100px] opacity-15" />
    <div class="floating-orb floating-orb-secondary orb-xl bottom-[10%] -right-[150px] opacity-15" />
    <div class="floating-orb floating-orb-accent orb-md top-[60%] left-[10%] opacity-10" />

    <!-- Hero Content -->
    <div class="hero-content w-full px-4 sm:px-6 md:px-8 lg:px-12 max-w-6xl mx-auto">
      <div class="flex flex-col items-center text-center space-y-6 lg:space-y-8">
        <!-- Brand Logo -->
        <div class="mb-4">
          <img
            src="/logo.png"
            alt="Mintreu Logo"
            class="h-16 sm:h-20 md:h-24 w-auto mx-auto"
          >
        </div>

        <!-- Premium Badge -->
        <div class="premium-badge">
          <UIcon name="i-lucide-sparkles" class="w-4 h-4 mr-2" />
          <span>All-in-One Business Platform</span>
        </div>

        <!-- Main Title -->
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-black leading-tight tracking-tight">
          <span class="block text-slate-900 dark:text-white">Transform Your</span>
          <span class="block gradient-text-animated">Business Dreams</span>
          <span class="block text-slate-900 dark:text-white">Into Reality</span>
        </h1>

        <!-- Subtitle -->
        <p class="max-w-3xl text-slate-600 dark:text-slate-300 font-medium text-base sm:text-lg md:text-xl leading-relaxed">
          The ultimate platform combining
          <span class="font-bold text-pink-600 dark:text-pink-400">E-Commerce</span>, and
          <span class="font-bold text-violet-600 dark:text-violet-400">Business Marketing</span>,
          <span class="font-bold text-blue-600 dark:text-blue-400">Career Opportunities</span>
          — all in one powerful ecosystem
        </p>

        <!-- CTA Buttons -->
<!--        <div class="flex flex-col sm:flex-row gap-4 pt-4">-->
        <div class="flex flex-col sm:flex-col gap-4 pt-4">
          <div class="flex gap-2 flex-row sm:flex-row px-2">
            <NuxtLink to="/auth/register">
              <UButton
                size="xl"
                color="primary"
                class="px-8 py-4 font-bold shadow-xl shadow-violet-500/25 hover:shadow-2xl hover:shadow-violet-500/40 hover:scale-105 active:scale-95 transition-all duration-300"
              >
                <UIcon name="i-lucide-rocket" class="w-5 h-5 mr-2" />
                Sign up
              </UButton>
            </NuxtLink>


            <NuxtLink to="/auth/login">
              <UButton
                size="xl"
                color="primary"
                class="px-8 py-4 font-bold shadow-xl shadow-violet-500/25 hover:shadow-2xl hover:shadow-violet-500/40 hover:scale-105 active:scale-95 transition-all duration-300"
              >
                <UIcon name="i-lucide-rocket" class="w-5 h-5 mr-2" />
                Sign in
              </UButton>
            </NuxtLink>
          </div>


          <NuxtLink to="/help">
            <UButton
              size="xl"
              color="neutral"
              variant="outline"
              class="px-8 py-4 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-300"
            >
              <UIcon name="i-lucide-play-circle" class="w-5 h-5 mr-2" />
              Learn More
            </UButton>
          </NuxtLink>
        </div>

        <!-- Trust Badges -->
        <div class="flex flex-wrap justify-center items-center gap-4 pt-6 text-sm text-slate-500 dark:text-slate-400">
          <div class="flex items-center gap-2">
            <UIcon name="i-lucide-shield-check" class="w-4 h-4 text-emerald-500" />
            <span>100% Secure</span>
          </div>
          <div class="hidden sm:block w-px h-4 bg-slate-300 dark:bg-slate-600" />
          <div class="flex items-center gap-2">
            <UIcon name="i-lucide-credit-card" class="w-4 h-4 text-violet-500" />
            <span>No Hidden Fees</span>
          </div>
          <div class="hidden sm:block w-px h-4 bg-slate-300 dark:bg-slate-600" />
          <div class="flex items-center gap-2">
            <UIcon name="i-lucide-headphones" class="w-4 h-4 text-blue-500" />
            <span>24/7 Support</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator absolute bottom-8 left-1/2 -translate-x-1/2 hidden md:flex">
      <div class="w-6 h-10 border-2 border-slate-400 dark:border-slate-600 rounded-full flex justify-center">
        <div class="w-1.5 h-3 bg-gradient-to-b from-violet-600 to-transparent rounded-full mt-2" />
      </div>
    </div>
  </section>
</template>
