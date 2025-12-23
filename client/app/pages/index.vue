<template>
  <div class="min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center px-4 py-12">
    <div class="mx-auto max-w-4xl text-center">
      <!-- Logo Icon -->
      <div class="w-24 h-24 mx-auto bg-gradient-to-br from-violet-600 to-fuchsia-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-violet-500/30 mb-8">
        <UIcon
          name="i-lucide-hexagon"
          class="w-12 h-12 text-white"
        />
      </div>

      <!-- Headline -->
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight">
        Welcome to <span class="gradient-text-primary">Commerinity Pro</span>
      </h1>
      <p class="mt-6 text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto text-balance">
        Your all-in-one platform for MLM and E-commerce success. Shop smart, earn more, grow together.
      </p>

      <!-- CTA Buttons -->
      <div class="mt-10 flex flex-wrap justify-center gap-4">
        <NuxtLink to="/auth/register">
          <UButton
            size="xl"
            color="primary"
            class="px-8"
          >
            Get Started
            <template #trailing>
              <UIcon name="i-lucide-arrow-right" />
            </template>
          </UButton>
        </NuxtLink>

        <NuxtLink to="/auth/login">
          <UButton
            size="xl"
            color="neutral"
            variant="soft"
            class="px-8"
          >
            Sign In
          </UButton>
        </NuxtLink>
      </div>

      <!-- Features Grid -->
      <div class="mt-20 grid gap-6 sm:grid-cols-3">
        <div class="surface-card p-6 hover:shadow-lg transition-shadow">
          <div class="icon-box-soft-primary icon-box-md mx-auto">
            <UIcon
              name="i-lucide-shopping-bag"
              class="w-6 h-6"
            />
          </div>
          <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">
            E-Commerce
          </h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Shop from a wide range of quality products at competitive prices
          </p>
        </div>

        <div class="surface-card p-6 hover:shadow-lg transition-shadow">
          <div class="icon-box-soft-secondary icon-box-md mx-auto">
            <UIcon
              name="i-lucide-users"
              class="w-6 h-6"
            />
          </div>
          <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">
            Build Network
          </h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Grow your team and earn commissions through our MLM system
          </p>
        </div>

        <div class="surface-card p-6 hover:shadow-lg transition-shadow">
          <div class="icon-box-soft-success icon-box-md mx-auto">
            <UIcon
              name="i-lucide-trending-up"
              class="w-6 h-6"
            />
          </div>
          <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">
            Earn Rewards
          </h3>
          <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
            Multiple earning opportunities with transparent tracking
          </p>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="mt-16 py-8 px-6 glass-card">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
          <div class="text-center">
            <div class="text-3xl sm:text-4xl font-bold gradient-text-primary">10K+</div>
            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Active Members</div>
          </div>
          <div class="text-center">
            <div class="text-3xl sm:text-4xl font-bold gradient-text-secondary">500+</div>
            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Products</div>
          </div>
          <div class="text-center">
            <div class="text-3xl sm:text-4xl font-bold gradient-text-success">₹50L+</div>
            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Paid Out</div>
          </div>
          <div class="text-center">
            <div class="text-3xl sm:text-4xl font-bold text-amber-600 dark:text-amber-400">4.8★</div>
            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rating</div>
          </div>
        </div>
      </div>

      <!-- Career CTA -->
      <div class="mt-12">
        <NuxtLink
          to="/career"
          class="inline-flex items-center gap-2 text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 font-medium transition-colors"
        >
          <UIcon name="i-lucide-briefcase" class="w-5 h-5" />
          <span>Looking for career opportunities? View open positions</span>
          <UIcon name="i-lucide-arrow-right" class="w-4 h-4" />
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'guest'
})
</script>
