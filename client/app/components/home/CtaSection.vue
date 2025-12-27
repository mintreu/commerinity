<script setup lang="ts">
const config = useRuntimeConfig()
</script>

<template>
  <section class="py-16 md:py-24 relative overflow-hidden">
    <!-- Background Orbs -->
    <div class="floating-orb floating-orb-primary orb-lg top-0 right-0 opacity-10" />
    <div class="floating-orb floating-orb-secondary orb-md bottom-0 left-0 opacity-10" />

    <UContainer class="relative z-10">
      <div class="glass-colored rounded-3xl p-8 md:p-12 lg:p-16 text-center relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-violet-500/20 to-transparent rounded-br-full" />
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-gradient-to-tl from-pink-500/20 to-transparent rounded-tl-full" />

        <!-- Content -->
        <div class="relative z-10 max-w-3xl mx-auto">
          <!-- Icon -->
          <div class="w-16 h-16 md:w-20 md:h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center shadow-xl shadow-violet-500/30 icon-float">
            <UIcon name="i-lucide-rocket" class="w-8 h-8 md:w-10 md:h-10 text-white" />
          </div>

          <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black mb-4 text-slate-900 dark:text-white">
            Ready to Start Your
            <span class="gradient-text-primary">Success Journey</span>?
          </h2>

          <p class="text-slate-600 dark:text-slate-300 text-base md:text-lg mb-8 max-w-2xl mx-auto">
            Join thousands of successful entrepreneurs who are already earning with our platform.
            Your financial freedom is just one click away.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <NuxtLink to="/auth/register">
              <UButton
                size="xl"
                color="primary"
                class="px-10 py-4 font-bold shadow-xl shadow-violet-500/25 hover:shadow-2xl hover:shadow-violet-500/40 hover:scale-105 transition-all duration-300"
              >
                <UIcon name="i-lucide-user-plus" class="w-5 h-5 mr-2" />
                Create Free Account
              </UButton>
            </NuxtLink>

            <NuxtLink to="/contact">
              <UButton
                size="xl"
                color="neutral"
                variant="outline"
                class="px-10 py-4 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-300"
              >
                <UIcon name="i-lucide-message-circle" class="w-5 h-5 mr-2" />
                Contact Us
              </UButton>
            </NuxtLink>
          </div>

          <!-- Trust Indicators -->
          <div class="mt-8 flex flex-wrap justify-center items-center gap-6 text-sm text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-2">
              <UIcon name="i-lucide-check-circle" class="w-4 h-4 text-emerald-500" />
              <span>Free to join</span>
            </div>
            <div class="flex items-center gap-2">
              <UIcon name="i-lucide-check-circle" class="w-4 h-4 text-emerald-500" />
              <span>No credit card required</span>
            </div>
            <div class="flex items-center gap-2">
              <UIcon name="i-lucide-check-circle" class="w-4 h-4 text-emerald-500" />
              <span>Instant activation</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Career CTA -->
      <div class="mt-12 text-center">
        <div class="glass-card p-6 md:p-8 rounded-2xl inline-block">
          <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
              <UIcon name="i-lucide-briefcase" class="w-6 h-6 text-white" />
            </div>
            <div class="text-center sm:text-left">
              <h3 class="font-bold text-slate-900 dark:text-white">Looking for Career Opportunities?</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Explore open positions and join our growing team</p>
            </div>
            <NuxtLink to="/career">
              <UButton
                color="primary"
                variant="soft"
                class="font-semibold"
              >
                View Positions
                <UIcon name="i-lucide-arrow-right" class="w-4 h-4 ml-1" />
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>
    </UContainer>
  </section>
</template>
